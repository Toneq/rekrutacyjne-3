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

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'uuid' => 'required|string|exists:employees,uuid',
            'data_rozpoczecia' => 'required|date_format:Y-m-d H:i',
            'data_zakonczenia' => 'required|date_format:Y-m-d H:i|after:data_rozpoczecia',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $employee = Employee::where('uuid', $request->uuid)->first();
    }
}
