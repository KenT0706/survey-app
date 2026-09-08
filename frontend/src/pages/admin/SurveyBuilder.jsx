import { useEffect, useState } from 'react';
import { useParams } from 'react-router-dom';
import api from '../../api/client.js';
import QRCodeCard from '../../components/QRCodeCard.jsx';

const QUESTION_TYPES = [
  { value: 'text', label: 'Short answer (open-ended)' },
  { value: 'textarea', label: 'Long answer (open-ended)' },
  { value: 'single_choice', label: 'Single choice (closed-ended)' },
  { value: 'multiple_choice', label: 'Multiple choice (closed-ended)' },
  { value: 'rating', label: 'Rating scale (closed-ended)' },
];

export default function SurveyBuilder() {
  const { id } = useParams();
  const [survey, setSurvey] = useState(null);
  const [type, setType] = useState('text');
  const [questionText, setQuestionText] = useState('');
  const [optionsText, setOptionsText] = useState(''); // one option per line

  const load = () => api.get(`/surveys/${id}`).then((res) => setSurvey(res.data));

  useEffect(() => { load(); }, [id]);

  const isClosed = type === 'single_choice' || type === 'multiple_choice';
  const ratingOptions = ['1', '2', '3', '4', '5'];

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
      payload.options = ratingOptions;
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

  if (!survey) return <p>Loading...</p>;

  return (
    <div style={{ maxWidth: 900, margin: '40px auto', fontFamily: 'sans-serif' }}>
      <h2>{survey.title}</h2>
      <p style={{ color: '#666' }}>{survey.description}</p>

      <div style={{ display: 'flex', gap: 24, alignItems: 'flex-start' }}>
        <div style={{ flex: 1 }}>
          <h3>Questions</h3>
          <ol>
            {survey.questions.map((q) => (
              <li key={q.id} style={{ marginBottom: 10 }}>
                <strong>{q.question_text}</strong> <em>({q.type})</em>
                {q.options?.length > 0 && (
                  <ul>{q.options.map((o) => <li key={o.id}>{o.option_text}</li>)}</ul>
                )}
                <button onClick={() => removeQuestion(q.id)} style={{ fontSize: 12 }}>Remove</button>
              </li>
            ))}
          </ol>

          <form onSubmit={addQuestion} style={{ border: '1px solid #ddd', borderRadius: 8, padding: 16 }}>
            <h4>Add a question</h4>
            <select value={type} onChange={(e) => setType(e.target.value)} style={{ width: '100%', padding: 8, marginBottom: 8 }}>
              {QUESTION_TYPES.map((t) => <option key={t.value} value={t.value}>{t.label}</option>)}
            </select>
            <input
              placeholder="Question text" value={questionText}
              onChange={(e) => setQuestionText(e.target.value)}
              style={{ width: '100%', padding: 8, marginBottom: 8 }}
            />
            {isClosed && (
              <textarea
                placeholder="One option per line" value={optionsText}
                onChange={(e) => setOptionsText(e.target.value)}
                rows={4} style={{ width: '100%', padding: 8, marginBottom: 8 }}
              />
            )}
            <button type="submit" style={{ padding: '8px 16px', background: '#0f766e', color: '#fff', border: 'none' }}>
              Add question
            </button>
          </form>
        </div>

        {/* QR distribution is right next to the builder so it's ez to grab the moment the survey is ready */}
        <QRCodeCard surveyId={id} />
      </div>
    </div>
  );
}
