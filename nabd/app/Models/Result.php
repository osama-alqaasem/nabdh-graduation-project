<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Result extends Model
{
    use HasFactory;
    protected $fillable = ['analysis_id', 'probability', 'label', 'image_score', 'tabular_score', 'symptoms_score'];
    protected function casts(): array { return ['probability' => 'decimal:2', 'image_score' => 'decimal:2', 'tabular_score' => 'decimal:2', 'symptoms_score' => 'decimal:2']; }
    public function analysis() { return $this->belongsTo(Analysis::class); }
}
