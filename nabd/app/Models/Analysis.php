<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
class Analysis extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'patient_id',
        'age',
        'troponin',
        'ckmb',
        'ecg_image_path',
        'result_score',
        'result_text'
    ];

    protected $casts = [
        'age' => 'integer',
        'troponin' => 'float',
        'ckmb' => 'float',
        'result_score' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function symptoms()
{
    return $this->belongsToMany(Symptom::class, 'analysis_symptom')->withPivot('value');
}

public function tests()
{
    return $this->belongsToMany(Test::class, 'analysis_tests')->withPivot('value');
}

    public function ecgImage()
    {
        return $this->hasOne(EcgImage::class);
    }

    public function result()
    {
        return $this->hasOne(Result::class);
    }
}