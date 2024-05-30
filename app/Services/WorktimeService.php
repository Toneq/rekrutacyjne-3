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
}