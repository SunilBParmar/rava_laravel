<?php

namespace App\Http\Controllers;

use App\Helpers\AnalyticHelper;
use App\Helpers\BBHelper;
use App\Helpers\ReportHelper;
use League\Fractal\Manager;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Dingo\Api\Routing\Helpers;


class Controller extends BaseController
{
    const DEFAULT_PER_PAGE = 10;

    use \Dingo\Api\Routing\Helpers;
    use Helpers;

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    public function __construct()
    {
        $this->fractal = new Manager();
        $this->bbHelper = new BBHelper();
        $this->reportHelper = new ReportHelper();
        $this->analyticHelper = new AnalyticHelper();
    }
}
