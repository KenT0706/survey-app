import { useEffect, useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import api from '../../api/client.js';
import QRCodeCard from '../../components/QRCodeCard.jsx';
import { accentFor } from '../../lib/theme.js';

const QUESTION_TYPES = [
  { value: 'text', label: 'Short answer', hint: 'Open-ended' },
  { value: 'textarea', label: 'Long answer', hint: 'Open-ended' },
  { value: 'single_choice', label: 'Single choice', hint: 'Closed-ended' },
  { value: 'multiple_choice', label: 'Multiple choice', hint: 'Closed-ended' },
  { value: 'rating', label: 'Rating (1–5)', hint: 'Closed-ended' },
];

export default function SurveyBuilder() {
  const { id } = useParams();
  const [survey, setSurvey] = useState(null);
  const [type, setType] = useState('text');
  const [questionText, setQuestionText] = useState('');
  const [optionsText, setOptionsText] = useState('');

  const load = () => api.get(`/surveys/${id}`).then((res) => setSurvey(res.data));
  useEffect(() => { load(); }, [id]);

  const isClosed = type === 'single_choice' || type === 'multiple_choice';

  const addQuestion = async (e) => {
    e.preventDefault();
    if (!questionText.trim()) return;

    const payload = { type, question_text: questionText, is_required: true };
    if (isClosed) {
      payload.options = optionsText.split('\n').map((s) => s.trim()).filter(Boolean);
      if (payload.options.length < 2) {
        alert('Add at least 2 options, one per line.');
        return;
      }
    } else if (type === 'rating') {
      payload.options = ['1', '2', '3', '4', '5'];
    }

    await api.post(`/surveys/${id}/questions`, payload);
    setQuestionText('');
    setOptionsText('');
    load();
  };

  const removeQuestion = async (qid) => {
    await api.delete(`/questions/${qid}`);
    load();
  };

  if (!survey) return <div className="page"><p className="muted">Loading…</p></div>;

  const accent = accentFor(survey);

  return (
    <>
      <div className="topbar">
        <span className="brand"><span className="brand-mark" />Survey App</span>
        <Link to="/admin" className="btn btn-ghost btn-sm" style={{ textDecoration: 'none' }}>← All surveys</Link>
      </div>

      <div className="page">
        <span className="pill" style={{ background: accent.soft, color: accent.text, marginBottom: 10 }}>
          Editing
        </span>
        <h1>{survey.title}</h1>
        {survey.description && <p className="muted">{survey.description}</p>}

        <div className="spacer-24" />

        <div style={{ display: 'grid', gridTemplateColumns: '1fr 300px', gap: 28, alignItems: 'start' }}>
          <div>
            {survey.questions.length > 0 && (
              <div className="card card-pad" style={{ marginBottom: 20 }}>
                <h3 style={{ marginBottom: 16 }}>Questions</h3>
                {survey.questions.map((q, i) => (
                  <div
                    key={q.id}
                    style={{
                      display: 'flex', gap: 14, padding: '14px 0',
                      borderTop: i > 0 ? '1px solid var(--line)' : 'none',
                    }}
                  >
                    <div style={{
                      width: 26, height: 26, borderRadius: '50%', background: accent.soft, color: accent.text,
                      display: 'flex', alignItems: 'center', justifyContent: 'center',
                      fontSize: 12.5, fontWeight: 700, flexShrink: 0,
                    }}>
                      {i + 1}
                    </div>
                    <div style={{ flex: 1 }}>
                      <p style={{ margin: 0, fontWeight: 600 }}>{q.question_text}</p>
                      <p className="stat" style={{ marginTop: 2 }}>
                        {QUESTION_TYPES.find((t) => t.value === q.type)?.label}
                      </p>
                      {q.options?.length > 0 && q.type !== 'rating' && (
                        <div style={{ display: 'flex', flexWrap: 'wrap', gap: 6, marginTop: 8 }}>
                          {q.options.map((o) => (
                            <span key={o.id} className="pill" style={{ background: 'var(--paper)', color: 'var(--ink-soft)' }}>
                              {o.option_text}
                            </span>
                          ))}
                        </div>
                      )}
                    </div>
                    <button
                      onClick={() => removeQuestion(q.id)}
                      style={{ background: 'none', border: 'none', color: 'var(--ink-soft)', cursor: 'pointer', fontSize: 13, height: 'fit-content' }}
                    >
                      Remove
                    </button>
                  </div>
                ))}
              </div>
            )}

            <form onSubmit={addQuestion} className="card card-pad">
              <h3 style={{ marginBottom: 16 }}>Add a question</h3>

              <div style={{ display: 'flex', flexWrap: 'wrap', gap: 8, marginBottom: 18 }}>
                {QUESTION_TYPES.map((t) => (
                  <button
                    key={t.value}
                    type="button"
                    onClick={() => setType(t.value)}
                    className="pill"
                    style={{
                      cursor: 'pointer', border: '1.5px solid',
                      borderColor: type === t.value ? accent.solid : 'var(--line)',
                      background: type === t.value ? accent.soft : 'var(--white)',
                      color: type === t.value ? accent.text : 'var(--ink-soft)',
                    }}
                  >
                    {t.label}
                  </button>
                ))}
              </div>

              <label className="field-label">Question</label>
              <input
                className="field" value={questionText}
                onChange={(e) => setQuestionText(e.target.value)}
                placeholder="What would you like to ask?"
                style={{ marginBottom: isClosed ? 16 : 18 }}
              />

              {isClosed && (
                <>
                  <label className="field-label">Answer options (one per line)</label>
                  <textarea
                    className="field" rows={4} value={optionsText}
                    onChange={(e) => setOptionsText(e.target.value)}
                    placeholder={'Very satisfied\nSatisfied\nNeutral\nDissatisfied'}
                    style={{ marginBottom: 18, fontFamily: 'Inter, sans-serif', resize: 'vertical' }}
                  />
                </>
              )}

              <button type="submit" className="btn btn-primary">Add question</button>
            </form>
          </div>

          <div style={{ position: 'sticky', top: 24 }}>
            <QRCodeCard surveyId={id} accent={accent} />
          </div>
        </div>
      </div>
    </>
  );
}
