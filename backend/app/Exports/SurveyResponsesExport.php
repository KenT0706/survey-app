<?php

namespace App\Exports;

use App\Models\Survey;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

/**
 * Sheet 1: raw response data — one row per respondent, one column per question.
 * Open-ended questions show the typed text; closed-ended questions show the
 * chosen option label(s), comma-separated for multi-select.
 */
class SurveyResponsesExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize
{
    public function __construct(private Survey $survey)
    {
    }

    public function title(): string
    {
        return 'Responses';
    }

    public function headings(): array
    {
        $headings = ['Response ID', 'Submitted At'];
        foreach ($this->survey->questions as $q) {
            $headings[] = $q->question_text;
        }
        return $headings;
    }

    public function collection()
    {
        $questions = $this->survey->questions;
        $responses = $this->survey->responses()->with('answers')->orderBy('submitted_at')->get();

        return $responses->map(function ($response) use ($questions) {
            $row = [$response->id, $response->submitted_at->format('Y-m-d H:i')];

            foreach ($questions as $q) {
                $answer = $response->answers->firstWhere('question_id', $q->id);

                if (!$answer) {
                    $row[] = '';
                    continue;
                }

                if ($q->isOpenEnded()) {
                    $row[] = $answer->answer_text ?? '';
                } else {
                    $optionMap = $q->options->pluck('option_text', 'id');
                    $ids = $answer->selected_option_ids ?? [];
                    $row[] = collect($ids)->map(fn ($id) => $optionMap[$id] ?? '')->filter()->implode(', ');
                }
            }

            return $row;
        });
    }
}
