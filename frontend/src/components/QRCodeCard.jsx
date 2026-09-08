import { useEffect, useState } from 'react';
import api from '../api/client.js';

// Shows the survey's QR code + public link, with quick copy/download actions.
export default function QRCodeCard({ surveyId }) {
  const [qr, setQr] = useState(null);

  useEffect(() => {
    api.get(`/surveys/${surveyId}/qrcode`).then((res) => setQr(res.data));
  }, [surveyId]);

  if (!qr) return null;

  return (
    <div style={{ border: '1px solid #ddd', borderRadius: 8, padding: 16, textAlign: 'center', width: 260 }}>
      <img src={qr.qr_image_url} alt="Survey QR code" style={{ width: 200, height: 200 }} />
      <p style={{ fontSize: 12, wordBreak: 'break-all', color: '#555' }}>{qr.public_url}</p>
      <div style={{ display: 'flex', gap: 8, justifyContent: 'center' }}>
        <a href={qr.qr_image_url} download>Download PNG</a>
        <button onClick={() => navigator.clipboard.writeText(qr.public_url)}>Copy link</button>
      </div>
    </div>
  );
}
