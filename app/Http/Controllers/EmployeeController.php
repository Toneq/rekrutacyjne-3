<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Employee;
use Ramsey\Uuid\Uuid;

class EmployeeController extends Controller
{
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstname' => 'required|string',
            'lastname' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $employee = new Employee();
        $employee->uuid = Uuid::uuid4();
        $employee->firstname = $request->firstname;
        $employee->lastname = $request->lastname;
        if ($employee->save()) {
            return response()->json(['message' => 'Pracownik został dodany!', 'uuid' => $employee->uuid], 201);
        } else {
            return response()->json(['error' => 'Wystąpił błąd podczas dodawania czasu pracy'], 500);
        }
    }
}
