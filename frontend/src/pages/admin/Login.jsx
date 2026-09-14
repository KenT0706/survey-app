import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import api from '../../api/client.js';

export default function Login() {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);
  const navigate = useNavigate();

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setLoading(true);
    try {
      const { data } = await api.post('/login', { email, password });
      localStorage.setItem('token', data.token);
      navigate('/admin');
    } catch (err) {
      setError(err.response?.data?.message || 'Invalid email or password.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div style={{
      minHeight: '100vh',
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center',
      background: 'linear-gradient(160deg, #4F46E5 0%, #6D28D9 45%, #1E3C64 100%)',
      padding: 20,
    }}>
      <div className="card card-pad" style={{ width: 380 }}>
        <div className="brand" style={{ marginBottom: 28 }}>
          <span className="brand-mark" />
          Survey App
        </div>
        <h2 style={{ marginBottom: 2 }}>Welcome back</h2>
        <p className="muted" style={{ marginTop: 0, marginBottom: 24 }}>Sign in to manage your surveys.</p>

        <form onSubmit={handleSubmit}>
          <label className="field-label" htmlFor="email">Email</label>
          <input
            id="email" type="email" className="field" value={email}
            onChange={(e) => setEmail(e.target.value)}
            placeholder="you@company.com"
            style={{ marginBottom: 16 }}
          />
          <label className="field-label" htmlFor="password">Password</label>
          <input
            id="password" type="password" className="field" value={password}
            onChange={(e) => setPassword(e.target.value)}
            placeholder="••••••••"
            style={{ marginBottom: error ? 10 : 22 }}
          />
          {error && (
            <p style={{ color: 'var(--danger)', fontSize: 13.5, marginTop: 0, marginBottom: 16 }}>{error}</p>
          )}
          <button type="submit" className="btn btn-primary btn-block" disabled={loading}>
            {loading ? 'Signing in…' : 'Sign in'}
          </button>
        </form>
      </div>
    </div>
  );
}