import { useEffect, useState } from 'react';
import { useParams } from 'react-router-dom';
import axios from 'axios';
import { accentFor } from '../../lib/theme.js';

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';

export default function SurveyTake() {
  const { slug } = useParams();
  const [survey, setSurvey] = useState(null);
  const [answers, setAnswers] = useState({});
  const [submitted, setSubmitted] = useState(false);
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState('');

  useEffect(() => {
    axios.get(`${API_URL}/public/surveys/${slug}`)
      .then((res) => setSurvey(res.data))
      .catch((err) => setError(err.response?.data?.message || 'Survey not found.'));
  }, [slug]);

  const setText = (qid, text) => setAnswers((a) => ({ ...a, [qid]: { question_id: qid, answer_text: text } }));

  const toggleOption = (qid, optId, multiple) => {
    setAnswers((a) => {
      const current = a[qid]?.selected_option_ids || [];
      const next = multiple
        ? (current.includes(optId) ? current.filter((x) => x !== optId) : [...current, optId])
        : [optId];
      return { ...a, [qid]: { question_id: qid, selected_option_ids: next } };
    });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setSubmitting(true);
    try {
      await axios.post(`${API_URL}/public/surveys/${slug}/responses`, { answers: Object.values(answers) });
      setSubmitted(true);
    } catch (err) {
      setError(err.response?.data?.message || 'Something went wrong submitting your response.');
    } finally {
      setSubmitting(false);
    }
  };

  const accent = survey ? accentFor(survey) : accentFor(0);

  if (error && !survey) {
    return (
      <div style={{ minHeight: '100vh', display: 'flex', alignItems: 'center', justifyContent: 'center', background: 'var(--paper)' }}>
        <p className="muted">{error}</p>
      </div>
    );
  }

  if (!survey) {
    return (
      <div style={{ minHeight: '100vh', display: 'flex', alignItems: 'center', justifyContent: 'center', background: 'var(--paper)' }}>
        <p className="muted">Loading survey…</p>
      </div>
    );
  }

  if (submitted) {
    return (
      <div style={{ minHeight: '100vh', display: 'flex', alignItems: 'center', justifyContent: 'center', background: 'var(--paper)', padding: 20 }}>
        <div className="card card-pad" style={{ maxWidth: 420, textAlign: 'center' }}>
          <div style={{
            width: 52, height: 52, borderRadius: '50%', background: accent.soft, color: accent.text,
            display: 'flex', alignItems: 'center', justifyContent: 'center', margin: '0 auto 16px', fontSize: 24,
          }}>
            ✓
          </div>
          <h2>Response recorded</h2>
          <p className="muted">Thanks for taking the time to complete this survey.</p>
        </div>
      </div>
    );
  }

  return (
    <div style={{ minHeight: '100vh', background: 'var(--paper)', paddingBottom: 60 }}>
      <div style={{ background: accent.solid, padding: '48px 20px 64px' }}>
        <div style={{ maxWidth: 640, margin: '0 auto', color: '#fff' }}>
          <h1 style={{ color: '#fff', marginBottom: 8 }}>{survey.title}</h1>
          {survey.description && <p style={{ opacity: 0.92, margin: 0, whiteSpace: 'pre-line' }}>{survey.description}</p>}
        </div>
      </div>

      <div style={{ maxWidth: 640, margin: '-40px auto 0', padding: '0 20px' }}>
        <form onSubmit={handleSubmit}>
          {survey.questions.map((q, i) => {
            const prevSection = i > 0 ? survey.questions[i - 1].section : null;
            const showSectionHeading = q.section && q.section !== prevSection;

            return (
              <div key={q.id}>
                {showSectionHeading && (
                  <div style={{
                    marginTop: i === 0 ? 0 : 28, marginBottom: 12,
                    paddingBottom: 8, borderBottom: `2px solid ${accent.solid}`,
                  }}>
                    <h3 style={{ color: accent.text, margin: 0, whiteSpace: 'pre-line' }}>{q.section}</h3>
                  </div>
                )}

                <div className="card card-pad" style={{ marginBottom: 16 }}>
                  <label style={{ display: 'block', fontWeight: 700, marginBottom: 14, fontSize: 14 }}>
                    {i + 1}. {q.question_text} {q.is_required && <span className="field-required">*</span>}
                  </label>

                  {q.type === 'text' && (
                    <input
                      required={q.is_required} type="text" className="field"
                      onChange={(e) => setText(q.id, e.target.value)}
                      style={{ fontSize: 16.5 }}
                    />
                  )}
                  {q.type === 'textarea' && (
                    <textarea
                      required={q.is_required} rows={4} className="field"
                      onChange={(e) => setText(q.id, e.target.value)}
                      style={{ fontFamily: 'Inter, sans-serif', resize: 'vertical', fontSize: 16.5 }}
                    />
                  )}
                  {(q.type === 'single_choice' || q.type === 'rating') && (
                    <div style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
                      {q.options.map((o) => (
                        <label
                          key={o.id}
                          style={{
                            display: 'flex', alignItems: 'center', gap: 10, padding: '10px 14px',
                            border: '1.5px solid var(--line)', borderRadius: 'var(--radius-md)', cursor: 'pointer',
                            fontSize: 16.5,
                          }}
                        >
                          <input type="radio" name={`q-${q.id}`} required={q.is_required} onChange={() => toggleOption(q.id, o.id, false)} />
                          {o.option_text}
                        </label>
                      ))}
                    </div>
                  )}
                  {q.type === 'multiple_choice' && (
                    <div style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
                      {q.options.map((o) => (
                        <label
                          key={o.id}
                          style={{
                            display: 'flex', alignItems: 'center', gap: 10, padding: '10px 14px',
                            border: '1.5px solid var(--line)', borderRadius: 'var(--radius-md)', cursor: 'pointer',
                            fontSize: 16.5,
                          }}
                        >
                          <input type="checkbox" onChange={() => toggleOption(q.id, o.id, true)} />
                          {o.option_text}
                        </label>
                      ))}
                    </div>
                  )}
                </div>
              </div>
            );
          })}

          {error && <p style={{ color: 'var(--danger)', fontSize: 13.5, marginBottom: 12 }}>{error}</p>}

          {survey.closing_note && (
            <p style={{
              textAlign: 'center', fontStyle: 'italic', color: 'var(--ink-soft)',
              whiteSpace: 'pre-line', marginBottom: 20,
            }}>
              {survey.closing_note}
            </p>
          )}

          <button type="submit" className="btn btn-pop btn-block" disabled={submitting} style={{ padding: '13px' }}>
            {submitting ? 'Submitting…' : 'Submit response'}
          </button>
        </form>
      </div>
    </div>
  );
}
