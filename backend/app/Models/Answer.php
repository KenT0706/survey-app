<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    protected $fillable = ['survey_response_id', 'question_id', 'answer_text', 'selected_option_ids'];

    protected $casts = [
        'selected_option_ids' => 'array',
    ];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function response()
    {
        return $this->belongsTo(SurveyResponse::class, 'survey_response_id');
    }
}
