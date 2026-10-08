<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'full_name', 'gender', 'age'];

    protected function casts(): array { return ['age' => 'integer']; }

    public function user() { return $this->belongsTo(User::class); }
    public function analyses() { return $this->hasMany(Analysis::class); }
}
