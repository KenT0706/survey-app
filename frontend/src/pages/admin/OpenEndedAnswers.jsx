import { useEffect, useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import api from '../../api/client.js';
import { accentFor } from '../../lib/theme.js';

export default function OpenEndedAnswers() {
  const { id } = useParams();
  const [survey, setSurvey] = useState(null);
  const [questions, setQuestions] = useState(null);

  useEffect(() => {
    api.get(`/surveys/${id}`).then((res) => setSurvey(res.data));
    api.get(`/surveys/${id}/open-ended-answers`).then((res) => setQuestions(res.data));
  }, [id]);

  if (!survey || !questions) return <div className="page"><p className="muted">Loading…</p></div>;

  const accent = accentFor(survey);

  return (
    <>
      <div className="topbar">
        <span className="brand"><span className="brand-mark" />Survey App</span>
        <Link to={`/admin/surveys/${id}/responses`} className="btn btn-ghost btn-sm" style={{ textDecoration: 'none' }}>← Back to results</Link>
      </div>

      <div className="page">
        <span className="pill" style={{ background: accent.soft, color: accent.text, marginBottom: 10 }}>
          Open-ended answers
        </span>
        <h1>{survey.title}</h1>
        <p className="muted" style={{ marginBottom: 28, whiteSpace: 'pre-line' }}>
          Every written response, grouped by question — nothing summarized or trimmed.
        </p>

        {questions.length === 0 && (
          <div className="empty-state card"><p>This survey has no open-ended questions.</p></div>
        )}

        {questions.map((q, i) => (
          <div key={q.id} className="card card-pad" style={{ marginBottom: 20 }}>
            <p style={{ margin: '0 0 4px', fontSize: 12.5, fontWeight: 700, color: accent.text }}>
              Question {i + 1} · {q.answers.length} response{q.answers.length === 1 ? '' : 's'}
            </p>
            <h3 style={{ marginBottom: 16, whiteSpace: 'pre-line' }}>{q.question_text}</h3>

            {q.answers.length === 0 ? (
              <p className="muted" style={{ fontSize: 13.5 }}>No answers yet.</p>
            ) : (
              <div style={{ display: 'flex', flexDirection: 'column', gap: 10 }}>
                {q.answers.map((a, j) => (
                  <div key={j} style={{
                    padding: '12px 14px', background: 'var(--paper)', borderRadius: 'var(--radius-md)',
                    borderLeft: `3px solid ${accent.solid}`,
                  }}>
                    <p style={{ margin: 0, whiteSpace: 'pre-line' }}>{a.text}</p>
                  </div>
                ))}
              </div>
            )}
          </div>
        ))}
      </div>
    </>
  );
}