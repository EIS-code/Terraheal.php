<?php

namespace App\Http\Controllers\Massage;

use App\Http\Controllers\BaseController;

class MassageController extends BaseController
{
    protected $getRequest, $massage;

    public function __construct()
    {
        parent::__construct();
        $this->massage    = $this->massageRepo;
        $this->getRequest = $this->httpRequest->all();
    }

    public function get()
    {
        return $this->massage->get($this->getRequest);
    }

    public function getMassageCenters()
    {
        return $this->massage->getMassageCenters($this->getRequest);
    }

    public function getMassageSessions()
    {
        return $this->massage->getMassageSessions();
    }
}
