<?php

namespace App\Http\Controllers\Api;

use App\Exports\SurveyResponsesExport;
use App\Exports\SurveySummaryExport;
use App\Http\Controllers\Controller;
use App\Models\Survey;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    // GET /api/surveys/{survey}/export/excel — 2-sheet workbook: raw Responses + Summary tallies
    public function excel(Survey $survey)
    {
        $filename = "{$survey->slug}-responses.xlsx";

        return Excel::download(
            new class($survey) implements \Maatwebsite\Excel\Concerns\WithMultipleSheets {
                private Survey $survey;
                public function __construct(Survey $survey) { $this->survey = $survey; }
                public function sheets(): array
                {
                    return [
                        new SurveyResponsesExport($this->survey),
                        new SurveySummaryExport($this->survey),
                    ];
                }
            },
            $filename
        );
    }

    // GET /api/surveys/{survey}/export/image — a shareable PNG "results card" for closed-ended questions
    public function image(Survey $survey)
    {
        $survey->load('questions.options.answers');
        $totalResponses = $survey->responses()->count();

        $closedQuestions = $survey->questions->filter(fn ($q) => !$q->isOpenEnded())->values();

        $width = 900;
        $rowHeight = 60;
        $headerHeight = 110;
        $questionGap = 30;
        $height = $headerHeight + $closedQuestions->sum(fn ($q) => $questionGap + 30 + $q->options->count() * $rowHeight) + 40;
        $height = max($height, 300);

        $img = imagecreatetruecolor($width, $height);

        // Teal Trust palette
        $bg = imagecolorallocate($img, 247, 250, 249);
        $teal = imagecolorallocate($img, 15, 118, 110);
        $tealLight = imagecolorallocate($img, 204, 235, 231);
        $dark = imagecolorallocate($img, 31, 41, 40);
        $gray = imagecolorallocate($img, 107, 114, 112);
        $white = imagecolorallocate($img, 255, 255, 255);

        imagefill($img, 0, 0, $bg);
        imagefilledrectangle($img, 0, 0, $width, 90, $teal);

        $font = 5; // built-in GD font (no external .ttf dependency required)
        imagestring($img, $font, 20, 15, $survey->title, $white);
        imagestring($img, 3, 20, 40, "Total responses: {$totalResponses}", $white);
        imagestring($img, 2, 20, 60, 'Generated ' . now()->format('d M Y, H:i'), $white);

        $y = $headerHeight;
        foreach ($closedQuestions as $q) {
            imagestring($img, 4, 20, $y, $q->question_text, $dark);
            $y += 30;

            $tally = array_fill_keys($q->options->pluck('id')->all(), 0);
            foreach ($q->answers as $answer) {
                foreach (($answer->selected_option_ids ?? []) as $optId) {
                    if (array_key_exists($optId, $tally)) {
                        $tally[$optId]++;
                    }
                }
            }
            $max = max(1, max($tally ?: [0]));

            foreach ($q->options as $opt) {
                $count = $tally[$opt->id] ?? 0;
                $barMax = $width - 300;
                $barWidth = (int) round(($count / $max) * $barMax);

                imagestring($img, 3, 20, $y + 8, mb_substr($opt->option_text, 0, 28), $dark);
                imagefilledrectangle($img, 260, $y, 260 + $barMax, $y + 26, $tealLight);
                imagefilledrectangle($img, 260, $y, 260 + max($barWidth, 2), $y + 26, $teal);
                imagestring($img, 3, 260 + $barMax + 10, $y + 8, (string) $count, $gray);

                $y += $rowHeight;
            }
            $y += $questionGap;
        }

        ob_start();
        imagepng($img);
        $data = ob_get_clean();
        imagedestroy($img);

        return response($data, 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => "attachment; filename=\"{$survey->slug}-summary.png\"",
        ]);
    }
}
