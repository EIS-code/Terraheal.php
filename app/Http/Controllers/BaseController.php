<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\User\UserRepository;
use App\Repositories\User\BookingRepository;
use App\Repositories\User\ReviewRepository;
use App\Repositories\Location\CountryRepository;
use App\Repositories\Location\ProvinceRepository;
use App\Repositories\Location\CityRepository;
use App\Repositories\User\Payment\BookingPaymentRepository;
use App\Repositories\User\Payment\UserCardDetailRepository;
use App\Repositories\Therapist\TherapistRepository;
use App\Repositories\Therapist\Massage\TherapistMassageHistoryRepository;
use App\Repositories\Therapist\TherapistCalendarRepository;
use App\Repositories\Therapist\TherapistLanguageRepository;
use App\Repositories\Therapist\TherapistReviewQuestionRepository;
use App\Repositories\Therapist\TherapistReviewRepository;
use App\Repositories\Receptionist\ReceptionistRepository;
use App\Repositories\Staff\StaffRepository;
use App\Repositories\Staff\StaffAttendanceRepository;
use App\Repositories\Massage\MassageRepository;

abstract class BaseController extends Controller
{
    protected $httpRequest = null, $defaultCode = 0, $defaultMessage = "No any response found !", $errorCode = 401, $successCode = 200;
    protected $userRepo, $bookingRepo, $reviewRepo, $countryRepo, $provinceRepo, $cityRepo, $bookingPaymentRepo, $userCardDetailRepo, $therapist, $therapistMassageHistoryRepo, $receptionistRepo, $therapistCalendarRepo, $therapistLanguageRepo, $therapistReviewQuestionRepo, $therapistReviewRepo, $staffRepo, $staffAttendanceRepo, $massageRepo;

    public function __construct()
    {
        $this->httpRequest             = Request();
        $this->userRepo                = new UserRepository();
        $this->bookingRepo             = new BookingRepository();
        $this->reviewRepo              = new ReviewRepository();
        $this->countryRepo             = new CountryRepository();
        $this->provinceRepo            = new ProvinceRepository();
        $this->cityRepo                = new CityRepository();
        $this->bookingPaymentRepo      = new BookingPaymentRepository();
        $this->userCardDetailRepo      = new UserCardDetailRepository();
        $this->therapistRepo           = new therapistRepository();
        $this->therapistMassageHistoryRepo = new therapistMassageHistoryRepository();
        $this->receptionistRepo        = new ReceptionistRepository();
        $this->therapistReviewQuestionRepo = new TherapistReviewQuestionRepository();
        $this->therapistReviewRepo     = new TherapistReviewRepository();
        $this->therapistCalendarRepo   = new TherapistCalendarRepository();
        $this->therapistLanguageRepo   = new TherapistLanguageRepository();
        $this->staffRepo               = new StaffRepository();
        $this->staffAttendanceRepo     = new StaffAttendanceRepository();
        $this->massageRepo             = new MassageRepository();
    }

    public function response($response = [])
    {
        $responseCode    = $this->defaultCode;
        $responseMessage = $this->defaultMessage;

        if (!empty($response->errorMsg)) {
            $responseCode    = $this->errorCode;
            $responseMessage = $response->errorMsg;
        } elseif (!empty($response->successMsg)) {
            $responseCode    = $this->successCode;
            $responseMessage = $response->successMsg;
        }

        return response()->json([
            'code' => $responseCode,
            'msg'  => $responseMessage
        ]);
    }
}
