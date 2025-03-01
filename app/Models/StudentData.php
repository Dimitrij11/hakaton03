<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentData extends Model {
    use HasFactory;

    protected $fillable = ['user_id', 'gender', 'birth_date', 'school_year', 'field_of_study', 'current_school'];

    public function user() {
        return $this->belongsTo(User::class);
    }
}
