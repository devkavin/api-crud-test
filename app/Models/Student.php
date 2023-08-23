<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    // used to create new instances of a model from a factory
    use HasFactory;
    // soft delete to delete the record without deleting it from the database
    use SoftDeletes;
    protected $fillable = [
        'name',
        'email',
        'phone',
        'age',
        'course',
        'image_url',
    ];
}
