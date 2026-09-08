<?php

namespace App\Exports;

use App\Models\Survey;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

/**
 * Sheet 2: per-question summary.
 * Closed-ended: option -> count -> percentage tally.
 * Open-ended: total response count (full text lives on the Responses sheet).
 */
class SurveySummaryExport implements FromArray, WithTitle, ShouldAutoSize
{
    public function __construct(private Survey $survey)
    {
    }

    public function title(): string
    {
        return 'Summary';
    }

    public function array(): array
    {
        $rows = [];
        $totalResponses = $this->survey->responses()->count();

        foreach ($this->survey->questions as $q) {
            $rows[] = ["Q: {$q->question_text}", "Type: {$q->type}"];

            if ($q->isOpenEnded()) {
                $answered = $q->answers()->whereNotNull('answer_text')->where('answer_text', '!=', '')->count();
                $rows[] = ["Open-ended responses collected", $answered];
            } else {
                $tally = array_fill_keys($q->options->pluck('id')->all(), 0);
                foreach ($q->answers as $answer) {
                    foreach (($answer->selected_option_ids ?? []) as $optId) {
                        if (array_key_exists($optId, $tally)) {
                            $tally[$optId]++;
                        }
                    }
                }
                $rows[] = ['Option', 'Count', '% of respondents'];
                foreach ($q->options as $opt) {
                    $count = $tally[$opt->id] ?? 0;
                    $pct = $totalResponses > 0 ? round(($count / $totalResponses) * 100, 1) . '%' : '0%';
                    $rows[] = [$opt->option_text, $count, $pct];
                }
            }

            $rows[] = []; // spacer row between questions
        }

        return $rows;
    }
}
