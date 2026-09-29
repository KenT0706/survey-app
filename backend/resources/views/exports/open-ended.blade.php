<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    body { font-family: 'NotoSansCJK', sans-serif; font-size: 12px; color: #16162A; }
    h1 { font-size: 18px; margin-bottom: 4px; white-space: pre-line; }
    .meta { color: #565875; font-size: 10px; margin-bottom: 24px; }
    .question { margin-bottom: 22px; page-break-inside: avoid; }
    .question-label { font-size: 9px; font-weight: bold; color: #4F46E5; text-transform: uppercase; margin-bottom: 2px; }
    .question-text { font-size: 13px; font-weight: bold; margin-bottom: 10px; }
    .answer { border-left: 3px solid #4F46E5; background: #F5F6FB; padding: 8px 10px; margin-bottom: 6px; }
    .empty { color: #565875; font-style: italic; }
</style>
</head>
<body>
    <h1>{{ $survey->title }}</h1>
    <p class="meta">Open-ended answers · Generated {{ now()->format('d M Y, H:i') }}</p>

    @foreach ($questions as $i => $q)
        <div class="question">
            <p class="question-label">Question {{ $i + 1 }} &middot; {{ $q->answers->count() }} response{{ $q->answers->count() === 1 ? '' : 's' }}</p>
            <p class="question-text">{{ $q->question_text }}</p>

            @forelse ($q->answers as $a)
                <div class="answer">{{ $a->answer_text }}</div>
            @empty
                <p class="empty">No answers yet.</p>
            @endforelse
        </div>
    @endforeach
</body>
</html>