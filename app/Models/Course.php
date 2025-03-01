<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model {
    use HasFactory;

    protected $fillable = ['title', 'description', 'category_id'];

    public function category() {
        return $this->belongsTo(Category::class);
    }

    public function professors() {
        return $this->belongsToMany(User::class, 'course_professor', 'course_id', 'professor_id');
    }

    public function modules() {
        return $this->hasMany(Module::class);
    }
}
