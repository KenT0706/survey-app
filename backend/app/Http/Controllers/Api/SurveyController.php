<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SurveyController extends Controller
{
    // GET /api/surveys — list all surveys for the logged-in admin
    public function index(Request $request)
    {
        $surveys = Survey::withCount(['responses', 'questions'])
            ->latest()
            ->paginate(15);

        return response()->json($surveys);
    }

    // POST /api/surveys — create a new survey (title/description only; questions added separately)
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'closing_note' => 'nullable|string',
            'opens_at' => 'nullable|date',
            'closes_at' => 'nullable|date|after_or_equal:opens_at',
        ]);

        $survey = Survey::create($data + ['created_by' => $request->user()?->id]);
        $this->generateQrCode($survey);

        return response()->json($survey->fresh(), 201);
    }

   public function show(Survey $survey)
{
    return response()->json($survey->load('questions.options')->loadCount('responses'));
}

    // PUT /api/surveys/{survey}
    public function update(Request $request, Survey $survey)
    {
        $data = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'closing_note' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
            'opens_at' => 'nullable|date',
            'closes_at' => 'nullable|date|after_or_equal:opens_at',
        ]);

        $survey->update($data);

        return response()->json($survey->fresh());
    }

    // DELETE /api/surveys/{survey}
    public function destroy(Survey $survey)
    {
        if ($survey->qr_code_path) {
            Storage::disk('public')->delete($survey->qr_code_path);
        }
        $survey->delete();

        return response()->json(['message' => 'Survey deleted']);
    }

    // GET /api/surveys/{survey}/qrcode — returns the QR image URL + the public link it encodes
    public function qrCode(Survey $survey)
    {
        if (!$survey->qr_code_path || !Storage::disk('public')->exists($survey->qr_code_path)) {
            $this->generateQrCode($survey);
        }

        return response()->json([
            'public_url' => $survey->publicUrl(),
            'qr_image_url' => Storage::disk('public')->url($survey->qr_code_path),
        ]);
    }

    // GET /api/surveys/{survey}/open-ended-answers — every text/textarea
// question's collected answers, grouped by question, for a clean read-only view.
public function openEndedAnswers(Survey $survey)
{
    $questions = $survey->questions()
        ->whereIn('type', ['text', 'textarea'])
        ->orderBy('order')
        ->with(['answers' => function ($query) {
            $query->whereNotNull('answer_text')->where('answer_text', '!=', '')
                ->with('response:id,submitted_at')
                ->orderBy('created_at');
        }])
        ->get();

    return response()->json(
        $questions->map(fn ($q) => [
            'id' => $q->id,
            'question_text' => $q->question_text,
            'answers' => $q->answers->map(fn ($a) => [
                'text' => $a->answer_text,
                'submitted_at' => $a->response?->submitted_at,
            ])->values(),
        ])
    );
}

    private function generateQrCode(Survey $survey): void
    {
        $qrCode = new QrCode($survey->publicUrl());
        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        $path = "qrcodes/{$survey->slug}.png";
        Storage::disk('public')->put($path, $result->getString());

        $survey->update(['qr_code_path' => $path]);
    }
        // DELETE /api/surveys/{survey}/responses — wipes all collected responses
    // and answers for a fresh round, keeping the survey/questions/QR intact.
    // Requires the request to explicitly confirm, since this is irreversible.
    public function clearResponses(Request $request, Survey $survey)
    {
        $request->validate([
            'confirm' => 'required|in:1,true',
        ]);

        $count = $survey->responses()->count();
        $survey->responses()->delete(); // cascades to answers

        return response()->json([
            'message' => "Cleared {$count} response(s).",
            'cleared' => $count,
        ]);
    }
}