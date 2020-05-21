<?php

namespace App\Http\Controllers\Therapist;

use App\Http\Controllers\BaseController;

class TherapistController extends BaseController
{
    protected $therapist, $therapistCalendar, $getRequest;

    public function __construct()
    {
        parent::__construct();
        $this->getRequest        = $this->httpRequest->all();
        $this->therapist         = $this->therapistRepo;
        $this->therapistCalendar = $this->therapistCalendarRepo;

        $getPrifix = $this->httpRequest->route()->getPrefix();
        if (strpos($getPrifix, 'freelancer') !== false) {
            $this->therapist->isFreelancer = '1';
        }
    }

    public function signup()
    {
        return $this->therapist->create($this->getRequest);
    }

    public function signIn()
    {
        return $this->therapist->signIn($this->getRequest);
    }

    public function update($therapistId)
    {
        return $this->therapist->update($therapistId, $this->getRequest);
    }

    public function getPastBooking($therapistId)
    {
        return $this->therapist->getWherePastFuture($therapistId, true, true);
    }

    public function getFutureBooking($therapistId)
    {
        return $this->therapist->getWherePastFuture($therapistId, false, true);
    }

    public function search()
    {
        return $this->therapist->search($this->getRequest);
    }

    public function verifyMobile($therapistId, $number)
    {
        return $this->response($this->therapist->verifyMobile($therapistId, $number));
    }

    public function verifyEmail($therapistId, $emailId)
    {
        return $this->response($this->therapist->verifyEmail($therapistId, $emailId));
    }

    public function compareOtp($therapistId, $otp)
    {
        return $this->response($this->therapist->compareOtp($therapistId, $otp));
    }

    public function updateProfile($therapistId)
    {
        return $this->therapist->updateProfile($therapistId, $this->httpRequest);
    }
}
