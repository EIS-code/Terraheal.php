<?php

namespace App\Repositories\Therapist;

use App\Repositories\BaseRepository;
use App\Repositories\Therapist\TherapistDocumentRepository;
use App\Repositories\Therapist\TherapistReviewRepository;
use App\Therapist;
use App\TherapistReview;
use App\Booking;
use App\BookingInfo;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use CurrencyHelper;
use DB;

class TherapistRepository extends BaseRepository
{
    protected $therapist, $therapistDocumentRepo, $therapistReviewRepository, $booking, $bookingInfo, $currencyHelper;
    public    $isFreelancer = '0';

    public function __construct()
    {
        parent::__construct();
        $this->therapist                 = new Therapist();
        $this->therapistDocumentRepo     = new therapistDocumentRepository();
        $this->therapistReviewRepository = new TherapistReviewRepository();
        $this->therapistReview           = new TherapistReview();
        $this->booking                   = new Booking();
        $this->bookingInfo               = new BookingInfo();
        $this->currencyHelper            = new CurrencyHelper();
    }

    public function create(array $data)
    {
        $therapist = [];
        DB::beginTransaction();

        try {
            $data['is_freelancer'] = $this->isFreelancer;
            $validator = $this->therapist->validator($data);
            if ($validator->fails()) {
                return response()->json([
                    'code' => 401,
                    'msg'  => $validator->errors()->first()
                ]);
            }

            $therapist        = $this->therapist;
            $data['password'] = (!empty($data['password']) ? Hash::make($data['password']) : NULL);
            $therapist->fill($data);
            if ($therapist->save() && !empty($data['documents'])) {
                $this->therapistDocumentRepo->create($data['documents'], $therapist->id);
            }
        } catch(Exception $e) {
            DB::rollBack();
            // throw $e;
        }

        DB::commit();

        return response()->json([
            'code' => 200,
            'msg'  => 'Therapist created successfully !',
            'data' => $therapist
        ]);
    }

    public function all()
    {
        return $this->therapist->all();
    }

    public function getWhere($column, $value)
    {
        return $this->therapist->where($column, $value)->get();
    }

    public function getWhereMany(array $where)
    {
        return $this->therapist->where($where)->get();
    }

    public function getWherePastFuture(int $therapistId, $isPast = true, $isApi = false)
    {
        $now = Carbon::now();

        $bookings = $this->booking
                         ->with(['bookingInfo' => function($query) use($therapistId, $now, $isPast) {
                                $query->where('massage_date', ($isPast === true ? '<' : '>='), $now)
                                      ->where('therapist_id', $therapistId);
                         }])
                         ->get();

        if ($isApi === true) {
            $messagePrefix = (($isPast) ? 'Past' : 'Future');
            if (!empty($bookings)) {
                $message = $messagePrefix . ' booking found successfully !';
            } else {
                $message = $messagePrefix . ' booking not found !';
            }
            return response()->json([
                'code' => 200,
                'msg'  => $message,
                'data' => $bookings
            ]);
        }

        return $bookings;
    }

    public function getWhereFirst($column, $value, $isApi = false)
    {
        $data = $this->therapist->where($column, $value)->first();

        if ($isApi === true) {
            return response()->json([
                'code' => 200,
                'msg'  => 'Therapist found successfully !',
                'data' => $data
            ]);
        }

        return $data;
    }

    public function update(int $id, array $data)
    {
        $update        = false;
        $findTherapist = $this->therapist->where(['id' => $id, 'is_freelancer' => $this->isFreelancer])->get();

        if (!empty($findTherapist) && !$findTherapist->isEmpty()) {
            DB::beginTransaction();

            try {
                if (isset($data['password'])) {
                    unset($data['password']);
                }
                $data['is_freelancer'] = $this->isFreelancer;
                $validator = $this->therapist->validator($data, $id, true);
                if ($validator->fails()) {
                    return response()->json([
                        'code' => 401,
                        'msg'  => $validator->errors()->first()
                    ]);
                }

                $update = $this->therapist->where(['id' => $id, 'is_freelancer' => $this->isFreelancer])->update($data);
            } catch (Exception $e) {
                DB::rollBack();
                // throw $e;
            }

            if ($update) {
                DB::commit();
                return response()->json([
                    'code' => 200,
                    'msg'  => 'Therapist updated successfully !'
                ]);
            } else {
                return response()->json([
                    'code' => 401,
                    'msg'  => 'Something went wrong.'
                ]);
            }
        }

        return response()->json([
            'code' => 401,
            'msg'  => 'Therapist not found.'
        ]);
    }

    public function signIn(array $data)
    {
        $email    = (!empty($data['email'])) ? $data['email'] : NULL;
        $password = (!empty($data['password'])) ? $data['password'] : NULL;

        if (empty($email)) {
            return response()->json([
                'code' => 401,
                'msg'  => 'Please provide email properly.'
            ]);
        } elseif (empty($password)) {
            return response()->json([
                'code' => 401,
                'msg'  => 'Please provide password properly.'
            ]);
        }

        if (!empty($email) && !empty($password)) {
            $getTherapist = $this->getWhereMany(['email' => $email, 'is_freelancer' => $this->isFreelancer]);

            if (!empty($getTherapist[0]) && Hash::check($password, $getTherapist[0]->password)) {
                return response()->json([
                    'code' => 401,
                    'msg'  => 'Therapist found successfully !',
                    'data' => $getTherapist
                ]);
            } else {
                return response()->json([
                    'code' => 401,
                    'msg'  => 'Therapist email or password seems wrong.'
                ]);
            }
        }

        return response()->json([
            'code' => 401,
            'msg'  => 'Something went wrong.'
        ]);
    }

    public function delete(int $id)
    {}

    public function search(array $data, int $limit = 10)
    {
        $query   = (!empty($data['q'])) ? $data['q'] : "";
        $limit   = (!is_integer($limit)) ? 10 : $limit;
        $now     = Carbon::now();
        $nowDate = $now->toDateString();

        $tableTherapist       = $this->therapist->getTableName();
        $tableTherapistReview = $this->therapistReview->getTableName();
        $tableBookingInfo     = $this->bookingInfo->getTableName();
        $getData              = $this->therapist
                                    ->select(DB::raw("{$tableTherapist}.*, SUM(tr.rating) AS total_ratings, bi.id as booking_id"))
                                    ->leftJoin($tableTherapistReview . ' AS tr', "{$tableTherapist}.id", '=', 'tr.therapist_id')
                                    ->leftJoin($tableBookingInfo . ' AS bi', 'bi.therapist_id', '=', DB::raw("(SELECT bii.therapist_id FROM `booking_infos` AS bii WHERE bii.therapist_id = therapists.id AND bii.is_done = '0' AND bii.is_cancelled = '0' AND bii.massage_date = '{$nowDate}' GROUP BY bii.therapist_id)"))
                                    ->where(function ($qry) use ($query) {
                                        $qry->where("name", "LIKE", "%" . $query . "%")
                                            ->orWhere("email", "LIKE", "%" . $query . "%")
                                            ->orWhere("short_description", "LIKE", "%" . $query . "%");
                                    })
                                    ->where("is_freelancer", "=", $this->isFreelancer)
                                    ->groupBy("{$tableTherapist}.id")
                                    ->havingRaw("booking_id IS NULL")
                                    ->orderBy(DB::raw('SUM(tr.rating)'), 'desc')
                                    ->limit($limit)
                                    ->get();

        if (!empty($getData) && !$getData->isEmpty()) {
            $getData->map(function($value, $key) use($getData, $nowDate) {
                // Check whoes earned less per hour for this day.
                $value->total_earned = 0;
                $bookingInfos        = $this->bookingInfo
                                     ->with('booking')
                                     ->where('therapist_id', $value->id)
                                     ->where('is_done', '1')
                                     ->where('is_cancelled', '0')
                                     ->whereRaw("DATE(`massage_date`) = '{$nowDate}'")
                                     ->get();
                if (!empty($bookingInfos) && !$bookingInfos->isEmpty()) {
                    $bookingInfos->each(function($bookingInfo, $index) use($value, &$earningInfo) {
                        $paidPercentage     = $value->paid_percentage;
                        $finalPrice         = ($bookingInfo->price - $bookingInfo->cost);
                        $totalEarned        = (($paidPercentage / 100) * $finalPrice);
                        $totalEarnedAsShop  = $this->currencyHelper->convertToDefaultShopCurrency($bookingInfo->booking->user_id, $totalEarned, $bookingInfo->booking_currency_id);

                        if (!isset($earningInfo[$value->id])) {
                            $earningInfo[$value->id]['totalEarned'] = 0;
                        }
                        $value->total_earned += $totalEarnedAsShop;
                    });
                }

                unset($value->booking_id);
            });
            $getData = $getData->sortBy(function($value, $key) {
                return $value->total_earned;
            });

            return response()->json([
                'code' => 200,
                'msg'  => 'Therapist found successfully !',
                'data' => $getData
            ]);
        }

        return response()->json([
            'code' => 200,
            'msg'  => 'Therapist doesn\'t found.'
        ]);
    }

    public function reviewCount(TherapistReview $collection)
    {
        if (!empty($collection) && $collection instanceof TherapistReview) {
            $ratings = [];

            $collection->each(function($value) use(&$ratings) {
                $ratings[$value->therapist_id][] = $value->rating;
            });

            return array_map('array_sum', $ratings);
        }

        return false;
    }

    public function deleteWhere($column, $value)
    {}

    public function get(int $id)
    {
        $therapist = $this->therapist->find($id);

        if (!empty($therapist)) {
            return $therapist->get();
        }

        return NULL;
    }

    public function errors()
    {}
}