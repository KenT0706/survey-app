import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import api from '../../api/client.js';

export default function Dashboard() {
  const [surveys, setSurveys] = useState([]);
  const [title, setTitle] = useState('');

  const load = () => api.get('/surveys').then((res) => setSurveys(res.data.data));

  useEffect(() => { load(); }, []);

  const createSurvey = async (e) => {
    e.preventDefault();
    if (!title.trim()) return;
    await api.post('/surveys', { title });
    setTitle('');
    load();
  };

  return (
    <div style={{ maxWidth: 800, margin: '40px auto', fontFamily: 'sans-serif' }}>
      <h2>Your Surveys</h2>

      <form onSubmit={createSurvey} style={{ display: 'flex', gap: 8, marginBottom: 24 }}>
        <input
          placeholder="New survey title" value={title}
          onChange={(e) => setTitle(e.target.value)}
          style={{ flex: 1, padding: 8 }}
        />
        <button type="submit" style={{ padding: '8px 16px', background: '#0f766e', color: '#fff', border: 'none' }}>
          Create
        </button>
      </form>

      <ul style={{ listStyle: 'none', padding: 0 }}>
        {surveys.map((s) => (
          <li key={s.id} style={{ border: '1px solid #ddd', borderRadius: 8, padding: 14, marginBottom: 10 }}>
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
              <div>
                <strong>{s.title}</strong>
                <div style={{ fontSize: 13, color: '#666' }}>
                  {s.questions_count} questions · {s.responses_count} responses · {s.is_active ? 'Active' : 'Inactive'}
                </div>
              </div>
              <div style={{ display: 'flex', gap: 8 }}>
                <Link to={`/admin/surveys/${s.id}`}>Edit / QR</Link>
                <Link to={`/admin/surveys/${s.id}/responses`}>Responses & Export</Link>
              </div>
            </div>
          </li>
        ))}
      </ul>
    </div>
  );
}
