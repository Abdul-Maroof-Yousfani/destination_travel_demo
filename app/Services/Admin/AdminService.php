<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Services\HelperService;

class AdminService
{
    protected $helperService;

    public function __construct(HelperService $helperService)
    {
        $this->helperService = $helperService;
    }
}