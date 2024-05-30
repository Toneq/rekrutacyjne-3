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

    public function hoursCalculation($worktime){
        $hours = 0;
        $overtime = 0;
        foreach($worktime as $time){
            $startTime = Carbon::parse($time->data_rozpoczecia);
            $endTime = Carbon::parse($time->data_zakonczenia);

            $differenceTime = $startTime->diffInMinutes($endTime) / 60;
            $sumTime = $this->roundToNearestHalfHour($differenceTime);

            $hours += $sumTime["hours"];
            $overtime += $sumTime["overtime"];
        }

        return ['hours' => $hours, 'overtime' => $overtime];
    }

    private function roundToNearestHalfHour($time){
        $hours = floor($time);
        $minutes = ($time - $hours) * 60;
    
        if ($minutes < 15) {
            $roundedMinutes = 0;
        } elseif ($minutes >= 15 && $minutes < 45) {
            $roundedMinutes = 30;
        } else {
            $roundedMinutes = 0;
            $hours += 1;
        }
    
        $totalHours = $hours + ($roundedMinutes / 60);
    
        if ($totalHours > 8) {
            $overtime = $totalHours - 8;
            $hours = 8;
            return ['hours' => $hours, 'overtime' => $overtime];
        }
    
        return ['hours' => $totalHours, 'overtime' => 0];
    }
}