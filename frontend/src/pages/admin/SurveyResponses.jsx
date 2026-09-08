import { useEffect, useState } from 'react';
import { useParams } from 'react-router-dom';
import api from '../../api/client.js';

export default function SurveyResponses() {
  const { id } = useParams();
  const [survey, setSurvey] = useState(null);

  useEffect(() => { api.get(`/surveys/${id}`).then((res) => setSurvey(res.data)); }, [id]);

  // Download helpers — hits the Laravel export endpoints and saves the returned file
  const downloadFile = async (path, filename) => {
    const res = await api.get(path, { responseType: 'blob' });
    const url = window.URL.createObjectURL(new Blob([res.data]));
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    a.click();
    window.URL.revokeObjectURL(url);
  };

  if (!survey) return <p>Loading...</p>;

  return (
    <div style={{ maxWidth: 700, margin: '40px auto', fontFamily: 'sans-serif' }}>
      <h2>{survey.title} — Results</h2>

      <div style={{ display: 'flex', gap: 12, marginBottom: 24 }}>
        <button
          onClick={() => downloadFile(`/surveys/${id}/export/excel`, `${survey.slug || survey.id}-responses.xlsx`)}
          style={{ padding: '10px 16px', background: '#0f766e', color: '#fff', border: 'none', borderRadius: 6 }}
        >
          Export to Excel (raw + summary)
        </button>
        <button
          onClick={() => downloadFile(`/surveys/${id}/export/image`, `${survey.slug || survey.id}-summary.png`)}
          style={{ padding: '10px 16px', background: '#134e4a', color: '#fff', border: 'none', borderRadius: 6 }}
        >
          Export results as image (PNG)
        </button>
      </div>

      <p style={{ color: '#666', fontSize: 14 }}>
        The Excel file has two sheets: one row per respondent (raw answers), and a Summary
        sheet with option counts/percentages for every closed-ended question. The PNG is a
        ready-to-share bar-chart snapshot of the closed-ended results — handy for slides or WhatsApp.
      </p>
    </div>
  );
}
