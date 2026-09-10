import { useEffect, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import api from '../../api/client.js';
import { accentFor } from '../../lib/theme.js';

export default function Dashboard() {
  const [surveys, setSurveys] = useState(null);
  const [title, setTitle] = useState('');
  const [creating, setCreating] = useState(false);
  const navigate = useNavigate();

  const load = () => api.get('/surveys').then((res) => setSurveys(res.data.data));

  useEffect(() => { load(); }, []);

  const logout = async () => {
    try { await api.post('/logout'); } catch { /* token may already be invalid */ }
    localStorage.removeItem('token');
    navigate('/admin/login');
  };

  const createSurvey = async (e) => {
    e.preventDefault();
    if (!title.trim()) return;
    setCreating(true);
    try {
      const { data } = await api.post('/surveys', { title });
      setTitle('');
      navigate(`/admin/surveys/${data.id}`);
    } finally {
      setCreating(false);
    }
  };

  return (
    <>
      <div className="topbar">
        <span className="brand"><span className="brand-mark" />Survey App</span>
        <button onClick={logout} className="btn btn-ghost btn-sm">Log out</button>
      </div>

      <div className="page">
        <h1>Your surveys</h1>
        <p className="muted" style={{ marginBottom: 28 }}>Create a survey, add your questions, then share it by QR or link.</p>

        <form onSubmit={createSurvey} className="card card-pad" style={{ display: 'flex', gap: 10, marginBottom: 32 }}>
          <input
            className="field" placeholder="Name your new survey — e.g. &quot;Team Engagement Q3&quot;"
            value={title} onChange={(e) => setTitle(e.target.value)}
          />
          <button type="submit" className="btn btn-primary" disabled={creating} style={{ flexShrink: 0 }}>
            {creating ? 'Creating…' : 'Create survey'}
          </button>
        </form>

        {surveys === null && <p className="muted">Loading…</p>}

        {surveys?.length === 0 && (
          <div className="empty-state card">
            <h3>No surveys yet</h3>
            <p>Create your first one above to get a shareable link and QR code.</p>
          </div>
        )}

        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(260px, 1fr))', gap: 18 }}>
          {surveys?.map((s) => {
            const accent = accentFor(s.id);
            return (
              <div key={s.id} className="card" style={{ overflow: 'hidden', display: 'flex', flexDirection: 'column' }}>
                <div className="cover-band" style={{ background: accent.solid }} />
                <div className="card-pad" style={{ flex: 1, display: 'flex', flexDirection: 'column' }}>
                  <span
                    className="pill"
                    style={{ background: accent.soft, color: accent.text, alignSelf: 'flex-start', marginBottom: 10 }}
                  >
                    {s.is_active ? 'Active' : 'Inactive'}
                  </span>
                  <h3 style={{ marginBottom: 6 }}>{s.title}</h3>
                  <p className="stat" style={{ marginBottom: 20 }}>
                    <b>{s.questions_count}</b> question{s.questions_count === 1 ? '' : 's'} ·{' '}
                    <b>{s.responses_count}</b> response{s.responses_count === 1 ? '' : 's'}
                  </p>
                  <div style={{ display: 'flex', gap: 8, marginTop: 'auto' }}>
                    <Link to={`/admin/surveys/${s.id}`} className="btn btn-ghost btn-sm" style={{ flex: 1, textDecoration: 'none' }}>
                      Edit & QR
                    </Link>
                    <Link to={`/admin/surveys/${s.id}/responses`} className="btn btn-ghost btn-sm" style={{ flex: 1, textDecoration: 'none' }}>
                      Results
                    </Link>
                  </div>
                </div>
              </div>
            );
          })}
        </div>
      </div>
    </>
  );
}