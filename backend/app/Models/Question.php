<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    public const OPEN_TYPES = ['text', 'textarea'];
    public const CLOSED_TYPES = ['single_choice', 'multiple_choice', 'rating'];

    protected $fillable = ['survey_id', 'type', 'question_text', 'is_required', 'order'];

    protected $casts = [
        'is_required' => 'boolean',
    ];

    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }

    public function options()
    {
        return $this->hasMany(QuestionOption::class)->orderBy('order');
    }

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    public function isOpenEnded(): bool
    {
        return in_array($this->type, self::OPEN_TYPES, true);
    }
}
