<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Answer;
use App\Models\Survey;
use App\Models\SurveyResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PublicSurveyController extends Controller
{
    // GET /api/public/surveys/{slug} — what the respondent's browser loads after scanning the QR
    public function show(string $slug)
    {
        $survey = Survey::where('slug', $slug)->where('is_active', true)->firstOrFail();

        if ($survey->opens_at && now()->lt($survey->opens_at)) {
            return response()->json(['message' => 'This survey is not open yet.'], 403);
        }
        if ($survey->closes_at && now()->gt($survey->closes_at)) {
            return response()->json(['message' => 'This survey has closed.'], 403);
        }

        // Don't leak internal option IDs' relation to answers; just id + text is fine for rendering.
        return response()->json(
            $survey->only(['id', 'title', 'description', 'slug'])
            + ['questions' => $survey->questions()->with('options:id,question_id,option_text,order')->get()
                ->map(fn ($q) => $q->only(['id', 'type', 'section', 'question_text', 'is_required', 'order', 'options']))]
        );
    }

    // POST /api/public/surveys/{slug}/responses — submit answers
    public function storeResponse(Request $request, string $slug)
    {
        $survey = Survey::where('slug', $slug)->where('is_active', true)->firstOrFail();
        $questions = $survey->questions()->with('options')->get()->keyBy('id');

        $data = $request->validate([
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|integer',
            'answers.*.answer_text' => 'nullable|string',
            'answers.*.selected_option_ids' => 'nullable|array',
        ]);

        // Validate required questions are answered and belong to this survey
        foreach ($questions as $question) {
            $submitted = collect($data['answers'])->firstWhere('question_id', $question->id);
            $isBlank = !$submitted
                || (empty($submitted['answer_text']) && empty($submitted['selected_option_ids']));
            if ($question->is_required && $isBlank) {
                throw ValidationException::withMessages([
                    'answers' => "Question \"{$question->question_text}\" is required.",
                ]);
            }
        }

        $response = DB::transaction(function () use ($survey, $data, $request, $questions) {
            $response = $survey->responses()->create([
                'respondent_ip' => $request->ip(),
                'respondent_agent' => $request->userAgent(),
                'submitted_at' => now(),
            ]);

            foreach ($data['answers'] as $a) {
                if (!$questions->has($a['question_id'])) {
                    continue; // ignore answers for questions not on this survey
                }
                Answer::create([
                    'survey_response_id' => $response->id,
                    'question_id' => $a['question_id'],
                    'answer_text' => $a['answer_text'] ?? null,
                    'selected_option_ids' => $a['selected_option_ids'] ?? null,
                ]);
            }

            return $response;
        });

        return response()->json(['message' => 'Thank you for your response!', 'response_id' => $response->id], 201);
    }
}
