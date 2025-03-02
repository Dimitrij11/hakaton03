<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfessorData extends Model
{
    use HasFactory;

    protected $table = 'professors_data'; // ✅ Explicitly define the correct table name

    protected $fillable = [
        'user_id',
        'position',
        'company',
        'gender',
        'birth_date',
        'work_experience_years'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
