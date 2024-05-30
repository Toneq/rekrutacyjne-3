<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Services\WorktimeService;
use App\Models\Worktime;
use App\Models\Employee;

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

        $timeworkCheck = $this->worktimeService->checkTimework($employee->id, $request->data_rozpoczecia);
        if ($timeworkCheck !== null && $timeworkCheck->getStatusCode() !== 200) {
            return $timeworkCheck;
        }

        $differenceTimeCheck = $this->worktimeService->checkDifferenceTime($request->data_rozpoczecia, $request->data_zakonczenia);
        if ($differenceTimeCheck !== null && $differenceTimeCheck->getStatusCode() !== 200) {
            return $differenceTimeCheck;
        }

        $worktime = new Worktime();
        $worktime->employee_id = $employee->id;
        $worktime->data_rozpoczecia = $request->data_rozpoczecia;
        $worktime->data_zakonczenia = $request->data_zakonczenia;
        $worktime->dzien_rozpoczecia = date('Y-m-d', strtotime($request->data_rozpoczecia));
        if ($worktime->save()) {
            return response()->json(['message' => 'Czas pracy został dodany!'], 201);
        } else {
            return response()->json(['error' => 'Wystąpił błąd podczas dodawania czasu pracy'], 500);
        }
    }

    public function summary(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'uuid' => 'required|string|exists:employees,uuid',
            'date' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $employee = Employee::where('uuid', $request->uuid)->first();

        $date = $request->date;
        $worktime = Worktime::where('employee_id', $employee->id)
                    ->where('dzien_rozpoczecia', 'like', $date . '%')
                    ->get();

        if ($worktime->isEmpty()) {
            return response()->json(['error' => 'Brak danych o czasie pracy dla podanej daty'], 404);
        }

        $hours = $this->worktimeService->hoursCalculation($worktime);
    }
}
