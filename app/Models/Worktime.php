<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Worktime extends Model
{
    use HasFactory;

    protected $fillable = ['employee_id', 'data_rozpoczecia', 'data_zakonczenia', 'dzien_rozpoczecia'];
}
