<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\WorktimeService;

class WorktimeController extends Controller
{
    protected $worktimeService;

    public function __construct(WorktimeService $worktimeService){
        $this->worktimeService = $worktimeService;
        $this->overtimeRatePercent = 200;
        $this->normMonthlyHours = 40;
        $this->hourlyRate = 20;
    }
}
