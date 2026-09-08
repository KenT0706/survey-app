<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Survey extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'description', 'slug', 'qr_code_path',
        'is_active', 'opens_at', 'closes_at', 'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'opens_at' => 'datetime',
        'closes_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Survey $survey) {
            if (empty($survey->slug)) {
                $base = Str::slug($survey->title) ?: 'survey';
                $slug = $base;
                $i = 1;
                while (static::where('slug', $slug)->exists()) {
                    $slug = $base . '-' . $i++;
                }
                $survey->slug = $slug;
            }
        });
    }

    public function questions()
    {
        return $this->hasMany(Question::class)->orderBy('order');
    }

    public function responses()
    {
        return $this->hasMany(SurveyResponse::class);
    }

    public function publicUrl(): string
    {
        return rtrim(config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:5173')), '/')
            . '/survey/' . $this->slug;
    }
}
