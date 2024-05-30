<?php

namespace App\Services;

use App\Models\Worktime;
use Carbon\Carbon;

class WorktimeService
{
    public function checkTimework($employee_id, $data_rozpoczecia){
        $countTimework = Worktime::where('employee_id', $employee_id)
                            ->whereDate('dzien_rozpoczecia', '=', date('Y-m-d', strtotime($data_rozpoczecia)))
                            ->count();

        if($countTimework > 0){
            return response()->json(['error'=> 'Pracownik ma już utworzony przedział czasu pracy podanego dnia!'], 400);
        }

        return null;
    }

    public function checkDifferenceTime($data_rozpoczecia, $data_zakonczenia){
        $startTime = strtotime($data_rozpoczecia);
        $endTime = strtotime($data_zakonczenia);
        $differenceTime = ($endTime - $startTime) / 3600;

        if($differenceTime > 12){
            return response()->json(['error' => 'Przekroczono limit 12 godzin pracy w ciągu jednego dnia!'], 400);
        }

        return null;
    }
}