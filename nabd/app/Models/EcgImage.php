<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class EcgImage extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'analysis_id',
        'path'
    ];

    public function analysis()
    {
        return $this->belongsTo(Analysis::class);
    }

public function getUrlAttribute(): ?string
{
    return $this->path ? asset('storage/' . ltrim($this->path, '/')) : null;
}
}
