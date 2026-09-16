import { useEffect, useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import api from '../../api/client.js';
import { accentFor } from '../../lib/theme.js';

export default function SurveyResponses() {
  const { id } = useParams();
  const [survey, setSurvey] = useState(null);
  const [downloading, setDownloading] = useState(null);
  const [confirmText, setConfirmText] = useState('');
  const [clearing, setClearing] = useState(false);
  const [cleared, setCleared] = useState(null);

  const load = () => api.get(`/surveys/${id}`).then((res) => setSurvey(res.data));
  useEffect(() => { load(); }, [id]);

  const downloadFile = async (path, filename, key) => {
    setDownloading(key);
    try {
      const res = await api.get(path, { responseType: 'blob' });
      const url = window.URL.createObjectURL(new Blob([res.data]));
      const a = document.createElement('a');
      a.href = url;
      a.download = filename;
      a.click();
      window.URL.revokeObjectURL(url);
    } finally {
      setDownloading(null);
    }
  };

  const clearResponses = async () => {
    setClearing(true);
    setCleared(null);
    try {
      const { data } = await api.delete(`/surveys/${id}/responses`, { data: { confirm: true } });
      setCleared(data.message);
      setConfirmText('');
      load(); // refresh the response count shown on this page
    } finally {
      setClearing(false);
    }
  };

  if (!survey) return <div className="page"><p className="muted">Loading…</p></div>;

  const accent = accentFor(survey);
  const slug = survey.slug || survey.id;
  const canClear = confirmText.trim().toUpperCase() === 'CLEAR';

  return (
    <>
      <div className="topbar">
        <span className="brand"><span className="brand-mark" />Survey App</span>
        <Link to="/admin" className="btn btn-ghost btn-sm" style={{ textDecoration: 'none' }}>← All surveys</Link>
      </div>

      <div className="page page--narrow">
        <span className="pill" style={{ background: accent.soft, color: accent.text, marginBottom: 10 }}>
          Results
        </span>
        <h1>{survey.title}</h1>
        <p className="muted" style={{ marginBottom: 28 }}>
          {survey.responses_count} response{survey.responses_count === 1 ? '' : 's'} collected so far.
        </p>

        <div className="card card-pad" style={{ marginBottom: 14 }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: 14 }}>
            <div style={{
              width: 42, height: 42, borderRadius: 10, background: accent.soft,
              display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0,
            }}>
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke={accent.text} strokeWidth="2">
                <path d="M4 4h16v16H4z" opacity="0" /><path d="M7 3v18M3 8h18M3 13h18M3 18h18" />
              </svg>
            </div>
            <div style={{ flex: 1 }}>
              <p style={{ margin: 0, fontWeight: 600 }}>Excel workbook</p>
              <p className="muted" style={{ margin: 0, fontSize: 13.5 }}>Raw responses + a summary sheet with tallies</p>
            </div>
            <button
              onClick={() => downloadFile(`/surveys/${id}/export/excel`, `${slug}-responses.xlsx`, 'excel')}
              className="btn btn-primary btn-sm"
              disabled={downloading === 'excel'}
            >
              {downloading === 'excel' ? 'Preparing…' : 'Download'}
            </button>
          </div>
        </div>

        <div className="card card-pad" style={{ marginBottom: 28 }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: 14 }}>
            <div style={{
              width: 42, height: 42, borderRadius: 10, background: accent.soft,
              display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0,
            }}>
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke={accent.text} strokeWidth="2">
                <rect x="3" y="3" width="18" height="18" rx="2" /><circle cx="8.5" cy="8.5" r="1.5" /><path d="M21 15l-5-5L5 21" />
              </svg>
            </div>
            <div style={{ flex: 1 }}>
              <p style={{ margin: 0, fontWeight: 600 }}>Results image</p>
              <p className="muted" style={{ margin: 0, fontSize: 13.5 }}>A shareable PNG summary of closed-ended answers</p>
            </div>
            <button
              onClick={() => downloadFile(`/surveys/${id}/export/image`, `${slug}-summary.png`, 'image')}
              className="btn btn-primary btn-sm"
              disabled={downloading === 'image'}
            >
              {downloading === 'image' ? 'Preparing…' : 'Download'}
            </button>
          </div>
        </div>

        <p className="muted" style={{ fontSize: 13, marginBottom: 28 }}>
          Open-ended answers appear in full on the Excel sheet. The image covers closed-ended questions only.
        </p>

        <div className="card card-pad" style={{ borderColor: 'var(--danger)' }}>
          <p style={{ margin: 0, fontWeight: 600, color: 'var(--danger)' }}>Danger zone</p>
          <p className="muted" style={{ fontSize: 13.5, marginTop: 6, marginBottom: 14 }}>
            Permanently deletes all {survey.responses_count} collected response{survey.responses_count === 1 ? '' : 's'} for
            this survey so it can be reused for a fresh round. The survey, its questions, and its QR code/link are
            untouched — only respondent data is removed. Export what you need first; this can't be undone.
          </p>
          <div style={{ display: 'flex', gap: 8 }}>
            <input
              className="field" placeholder='Type "CLEAR" to confirm'
              value={confirmText} onChange={(e) => setConfirmText(e.target.value)}
              style={{ flex: 1 }}
            />
            <button
              onClick={clearResponses}
              disabled={!canClear || clearing || survey.responses_count === 0}
              className="btn btn-sm"
              style={{
                background: canClear ? 'var(--danger)' : 'var(--line)',
                color: canClear ? '#fff' : 'var(--ink-soft)',
                flexShrink: 0,
              }}
            >
              {clearing ? 'Clearing…' : 'Clear responses'}
            </button>
          </div>
          {cleared && <p style={{ color: 'var(--danger)', fontSize: 13, marginTop: 10, marginBottom: 0 }}>{cleared}</p>}
        </div>
      </div>
    </>
  );
}