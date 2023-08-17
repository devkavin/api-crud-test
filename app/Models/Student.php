<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    // used to create new instances of a model from a factory
    use HasFactory;
    protected $fillable = [
        'name',
        'email',
        'phone',
        'age',
    ];
}
