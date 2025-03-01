<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Interest extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Get the students that have this interest.
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(
            StudentData::class,
            'student_interests',
            'interest_id',
            'student_id'
        )->withTimestamps();
    }
}
