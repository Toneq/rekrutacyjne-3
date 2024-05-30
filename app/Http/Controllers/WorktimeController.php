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

        // $hours = $this->worktimeService->hoursCalculation($worktime);
        $response = $this->worktimeService->hoursCalculation($worktime);

        //dla mnie ogólnie coś jest nie tak poniewaz w zadaniu jest powiedziane 40h miesięcznie co wydaje mi się złą wartością (może chodziło o normę w tygodniu?)
        //przez co źle oblicza normy ponieważ dopóki nie przebije 40h to jest to źle liczone. Chyba, że ja źle zrozumiałem.
        //Zrobiłem tak że oblicza max 8 jako normę i resztę jako nadgodziny - jeżeli źle zrozumiałem to przepraszam
        $overtimeRate = $this->hourlyRate * ($this->overtimeRatePercent/100);
        // $hoursStandard = min($hours, $this->normMonthlyHours); //zwraca normę miesięczną a jeżeli nie to pokazuje ile jest przepracowanych - obliczenia na 40h miesiecznie
        // $hoursOvertime = max($hours - $this->normMonthlyHours, 0); //zwraca ilość nadgodzin od normy miesięcznej - obliczenia na 40h miesiecznie

        $hoursStandard = $response["hours"];
        $hoursOvertime = $response["overtime"];
        $valueStandard = $hoursStandard * $this->hourlyRate; //obliczanie ile trzeba zapłacić za wypracowanie normy miesięcznej
        $valueOvertime = $hoursOvertime * $overtimeRate; //obliczenie ile trzeba zapłacić za nagodziny wypracowane ponad normę miesięczną
        $valueWorkedHours = $valueStandard + $valueOvertime; //suma obu obliczeń które pokazują ile trzeba zapłacić pracownikowi za wypracowanie godzin
        $hours = $hoursStandard + $hoursOvertime;

        return response()->json([
            'uuid' => $employee->uuid,
            'okres' => $date,
            'przepracowane_godziny_ogólnie' => $hours,
            'wartosc_wypracowanych_godzin_ogolnie' => $valueWorkedHours,
            'norma_godzin' => $hoursStandard,
            'wartosc_norm_godzinowych' => $valueStandard,
            'nadgodziny' => $hoursOvertime,
            'wartosc_nadgodzin' => $valueOvertime,
        ], 200);
    }
}
