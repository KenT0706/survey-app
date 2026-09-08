import { useEffect, useState } from 'react';
import { useParams } from 'react-router-dom';
import axios from 'axios';

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';

export default function SurveyTake() {
  const { slug } = useParams();
  const [survey, setSurvey] = useState(null);
  const [answers, setAnswers] = useState({}); // { [questionId]: { answer_text } or { selected_option_ids } }
  const [submitted, setSubmitted] = useState(false);
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
      let next;
      if (multiple) {
        next = current.includes(optId) ? current.filter((x) => x !== optId) : [...current, optId];
      } else {
        next = [optId];
      }
      return { ...a, [qid]: { question_id: qid, selected_option_ids: next } };
    });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    try {
      await axios.post(`${API_URL}/public/surveys/${slug}/responses`, {
        answers: Object.values(answers),
      });
      setSubmitted(true);
    } catch (err) {
      setError(err.response?.data?.message || 'Something went wrong submitting your response.');
    }
  };

  if (error && !survey) return <p style={{ textAlign: 'center', marginTop: 60 }}>{error}</p>;
  if (!survey) return <p style={{ textAlign: 'center', marginTop: 60 }}>Loading survey...</p>;
  if (submitted) return <p style={{ textAlign: 'center', marginTop: 60 }}>✅ Thanks — your response was recorded!</p>;

  return (
    <div style={{ maxWidth: 560, margin: '40px auto', fontFamily: 'sans-serif', padding: '0 16px' }}>
      <h2>{survey.title}</h2>
      {survey.description && <p style={{ color: '#666' }}>{survey.description}</p>}

      <form onSubmit={handleSubmit}>
        {survey.questions.map((q) => (
          <div key={q.id} style={{ marginBottom: 20 }}>
            <label style={{ fontWeight: 600 }}>
              {q.question_text} {q.is_required && <span style={{ color: 'crimson' }}>*</span>}
            </label>

            {(q.type === 'text') && (
              <input
                required={q.is_required} type="text" onChange={(e) => setText(q.id, e.target.value)}
                style={{ width: '100%', padding: 8, marginTop: 6 }}
              />
            )}
            {q.type === 'textarea' && (
              <textarea
                required={q.is_required} rows={4} onChange={(e) => setText(q.id, e.target.value)}
                style={{ width: '100%', padding: 8, marginTop: 6 }}
              />
            )}
            {(q.type === 'single_choice' || q.type === 'rating') && (
              <div style={{ marginTop: 6 }}>
                {q.options.map((o) => (
                  <label key={o.id} style={{ display: 'block', marginBottom: 4 }}>
                    <input
                      type="radio" name={`q-${q.id}`} required={q.is_required}
                      onChange={() => toggleOption(q.id, o.id, false)}
                    /> {o.option_text}
                  </label>
                ))}
              </div>
            )}
            {q.type === 'multiple_choice' && (
              <div style={{ marginTop: 6 }}>
                {q.options.map((o) => (
                  <label key={o.id} style={{ display: 'block', marginBottom: 4 }}>
                    <input type="checkbox" onChange={() => toggleOption(q.id, o.id, true)} /> {o.option_text}
                  </label>
                ))}
              </div>
            )}
          </div>
        ))}

        {error && <p style={{ color: 'crimson' }}>{error}</p>}
        <button type="submit" style={{ padding: '10px 20px', background: '#0f766e', color: '#fff', border: 'none', borderRadius: 6 }}>
          Submit
        </button>
      </form>
    </div>
  );
}
