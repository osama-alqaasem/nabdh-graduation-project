<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Symptom extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = ['name'];
    public function analyses(): BelongsToMany { return $this->belongsToMany(Analysis::class, 'analysis_symptom')->withPivot('value'); }
}
