<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Banner extends Model
{
    use HasFactory;
    
    protected $fillable = ['title','description','image_path','button_text','button_url','sort_order','is_active','created_by'];
    protected function casts(): array { return ['sort_order' => 'integer', 'is_active' => 'boolean']; }

    protected $appends = ['image_url'];
    // public function getImageUrlAttribute(): string { return Storage::url($this->image_path); }

    public function getImageUrlAttribute()
    {
    return url('storage/' . $this->image_path);
    }
    
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
}
