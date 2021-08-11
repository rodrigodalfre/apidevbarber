<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarberFeedback extends Model
{
    use HasFactory;

    protected $table = "barberfeedback";
    public $timestamps = false;
}
