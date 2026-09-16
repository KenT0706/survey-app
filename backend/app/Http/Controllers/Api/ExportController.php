<?php

namespace App\Http\Controllers\Api;

use App\Exports\SurveyResponsesExport;
use App\Exports\SurveySummaryExport;
use App\Http\Controllers\Controller;
use App\Models\Survey;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    /**
     * Candidate TrueType fonts, in preference order. The CJK ones are needed
     * for trilingual surveys — GD's built-in bitmap fonts are ASCII-only and
     * render Chinese as garbage boxes. Installed via the Dockerfile
     * (fonts-noto-cjk); falls back to DejaVu, then to bitmap fonts.
     */
    private const FONT_CANDIDATES = [
        '/usr/share/fonts/opentype/noto/NotoSansCJK-Regular.ttc',
        '/usr/share/fonts/opentype/noto/NotoSansCJKsc-Regular.otf',
        '/usr/share/fonts/truetype/wqy/wqy-zenhei.ttc',
        '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
    ];

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
        // NOTE: answers belong to Question, not QuestionOption — loading
        // 'questions.options.answers' throws, since QuestionOption has no
        // answers relationship.
        $survey->load(['questions.options', 'questions.answers']);

        $totalResponses = $survey->responses()->count();
        $closedQuestions = $survey->questions->filter(fn ($q) => !$q->isOpenEnded())->values();

        $font = $this->resolveFont();

        $width = 1000;
        $labelWidth = 330;   // left column for option labels
        $barLeft = $labelWidth + 30;
        $barMax = $width - $barLeft - 80;
        $rowHeight = 34;
        $lineHeight = 22;

        // ---- measure pass: work out how tall the image needs to be ----
        $blocks = [];
        foreach ($closedQuestions as $q) {
            $qLines = $this->wrap($q->question_text, $font, 13, $width - 60);
            $optionLines = [];
            foreach ($q->options as $opt) {
                $optionLines[$opt->id] = $this->wrap($opt->option_text, $font, 11, $labelWidth - 30);
            }
            $blocks[] = ['question' => $q, 'qLines' => $qLines, 'optionLines' => $optionLines];
        }

        $headerHeight = 130;
        $height = $headerHeight + 30;
        foreach ($blocks as $b) {
            $height += count($b['qLines']) * $lineHeight + 14;
            foreach ($b['optionLines'] as $lines) {
                $height += max($rowHeight, count($lines) * 18 + 12);
            }
            $height += 26; // gap between questions
        }
        $height = max((int) $height, 320);

        $img = imagecreatetruecolor($width, $height);

        // Mayshowa navy palette
        $bg = imagecolorallocate($img, 245, 246, 251);
        $navy = imagecolorallocate($img, 30, 60, 100);
        $navySoft = imagecolorallocate($img, 220, 228, 240);
        $dark = imagecolorallocate($img, 22, 22, 42);
        $gray = imagecolorallocate($img, 86, 88, 117);
        $white = imagecolorallocate($img, 255, 255, 255);

        imagefill($img, 0, 0, $bg);
        imagefilledrectangle($img, 0, 0, $width, $headerHeight - 20, $navy);

        // Title may contain newlines (e.g. a two-line survey header)
        $y = 34;
        foreach (preg_split('/\r?\n/', (string) $survey->title) as $titleLine) {
            $this->text($img, $font, 17, 30, $y, $titleLine, $white);
            $y += 26;
        }
        $this->text($img, $font, 11, 30, $y + 2, "Total responses: {$totalResponses}   ·   Generated " . now()->format('d M Y, H:i'), $white);

        // ---- draw pass ----
        $y = $headerHeight + 20;
        foreach ($blocks as $b) {
            $q = $b['question'];

            foreach ($b['qLines'] as $line) {
                $this->text($img, $font, 13, 30, $y, $line, $dark);
                $y += $lineHeight;
            }
            $y += 14;

            $tally = array_fill_keys($q->options->pluck('id')->all(), 0);
            foreach ($q->answers as $answer) {
                foreach (($answer->selected_option_ids ?? []) as $optId) {
                    if (array_key_exists($optId, $tally)) {
                        $tally[$optId]++;
                    }
                }
            }
            $peak = count($tally) ? max(1, max($tally)) : 1;

            foreach ($q->options as $opt) {
                $count = $tally[$opt->id] ?? 0;
                $lines = $b['optionLines'][$opt->id];
                $blockHeight = max($rowHeight, count($lines) * 18 + 12);
                $barWidth = (int) round(($count / $peak) * $barMax);

                $ly = $y + 14;
                foreach ($lines as $line) {
                    $this->text($img, $font, 11, 30, $ly, $line, $gray);
                    $ly += 18;
                }

                $barY = $y + ($blockHeight - 22) / 2;
                imagefilledrectangle($img, $barLeft, (int) $barY, $barLeft + $barMax, (int) $barY + 22, $navySoft);
                imagefilledrectangle($img, $barLeft, (int) $barY, $barLeft + max($barWidth, 2), (int) $barY + 22, $navy);

                $pct = $totalResponses > 0 ? round($count / $totalResponses * 100) . '%' : '0%';
                $this->text($img, $font, 11, $barLeft + $barMax + 12, (int) $barY + 15, "{$count}  ({$pct})", $gray);

                $y += $blockHeight;
            }

            $y += 26;
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

    /** First available TrueType font, or null to fall back to GD bitmap fonts. */
    private function resolveFont(): ?string
    {
        foreach (self::FONT_CANDIDATES as $path) {
            if (is_readable($path)) {
                return $path;
            }
        }
        return null;
    }

    /** Draw text with TTF when available (needed for CJK), else bitmap fallback. */
    private function text($img, ?string $font, int $size, int $x, int $y, string $string, int $color): void
    {
        if ($font) {
            imagettftext($img, $size, 0, $x, $y, $color, $font, $string);
            return;
        }
        // Bitmap fallback can't render multibyte glyphs — strip them so the
        // output is degraded rather than garbled.
        $ascii = preg_replace('/[^\x20-\x7E]/', '', $string);
        imagestring($img, 3, $x, $y - 12, $ascii, $color);
    }

    /** Wrap a string to fit within $maxWidth pixels, returning an array of lines. */
    private function wrap(string $string, ?string $font, int $size, int $maxWidth): array
    {
        $string = trim(preg_replace('/\s+/u', ' ', $string));
        if ($string === '') {
            return [''];
        }

        // Rough character budget when no TTF metrics are available.
        if (!$font) {
            $perLine = max(10, (int) ($maxWidth / 7));
            return explode("\n", wordwrap(preg_replace('/[^\x20-\x7E]/', '', $string), $perLine, "\n", true));
        }

        $words = preg_split('/ /u', $string);
        $lines = [];
        $current = '';

        foreach ($words as $word) {
            $candidate = $current === '' ? $word : "{$current} {$word}";
            $box = imagettfbbox($size, 0, $font, $candidate);
            $candidateWidth = abs($box[2] - $box[0]);

            if ($candidateWidth > $maxWidth && $current !== '') {
                $lines[] = $current;
                $current = $word;
            } else {
                $current = $candidate;
            }
        }
        if ($current !== '') {
            $lines[] = $current;
        }

        // Guard against runaway height on very long trilingual strings.
        return array_slice($lines, 0, 6);
    }
}