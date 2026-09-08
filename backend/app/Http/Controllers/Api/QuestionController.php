<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Survey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuestionController extends Controller
{
    // POST /api/surveys/{survey}/questions — add a question (open or closed ended)
    public function store(Request $request, Survey $survey)
    {
        $data = $request->validate([
            'type' => 'required|in:text,textarea,single_choice,multiple_choice,rating',
            'question_text' => 'required|string|max:1000',
            'is_required' => 'boolean',
            'order' => 'nullable|integer',
            // Only used for single_choice / multiple_choice
            'options' => 'required_if:type,single_choice,multiple_choice|array|min:2',
            'options.*' => 'string|max:255',
        ]);

        $question = DB::transaction(function () use ($survey, $data) {
            $question = $survey->questions()->create([
                'type' => $data['type'],
                'question_text' => $data['question_text'],
                'is_required' => $data['is_required'] ?? true,
                'order' => $data['order'] ?? ($survey->questions()->max('order') + 1),
            ]);

            if (!empty($data['options'])) {
                foreach ($data['options'] as $i => $optionText) {
                    $question->options()->create(['option_text' => $optionText, 'order' => $i]);
                }
            }

            return $question;
        });

        return response()->json($question->load('options'), 201);
    }

    // PUT /api/questions/{question}
    public function update(Request $request, Question $question)
    {
        $data = $request->validate([
            'question_text' => 'sometimes|required|string|max:1000',
            'is_required' => 'boolean',
            'order' => 'nullable|integer',
            'options' => 'nullable|array',
            'options.*' => 'string|max:255',
        ]);

        DB::transaction(function () use ($question, $data) {
            $question->update(collect($data)->except('options')->toArray());

            if (isset($data['options'])) {
                $question->options()->delete();
                foreach ($data['options'] as $i => $optionText) {
                    $question->options()->create(['option_text' => $optionText, 'order' => $i]);
                }
            }
        });

        return response()->json($question->fresh('options'));
    }

    // DELETE /api/questions/{question}
    public function destroy(Question $question)
    {
        $question->delete();

        return response()->json(['message' => 'Question deleted']);
    }

    // PATCH /api/surveys/{survey}/questions/reorder — { "order": [questionId, questionId, ...] }
    public function reorder(Request $request, Survey $survey)
    {
        $data = $request->validate(['order' => 'required|array']);

        foreach ($data['order'] as $index => $questionId) {
            Question::where('id', $questionId)->where('survey_id', $survey->id)->update(['order' => $index]);
        }

        return response()->json($survey->load('questions.options'));
    }
}
