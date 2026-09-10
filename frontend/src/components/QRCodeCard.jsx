import { useEffect, useState } from 'react';
import api from '../api/client.js';

// Shows the survey's QR code + public link, with quick copy/download actions —
// styled as the "share panel," the same role it plays in Microsoft Forms.
export default function QRCodeCard({ surveyId, accent }) {
  const [qr, setQr] = useState(null);
  const [copied, setCopied] = useState(false);

  useEffect(() => {
    api.get(`/surveys/${surveyId}/qrcode`).then((res) => setQr(res.data));
  }, [surveyId]);

  const copyLink = () => {
    navigator.clipboard.writeText(qr.public_url);
    setCopied(true);
    setTimeout(() => setCopied(false), 1500);
  };

  if (!qr) return null;

  return (
    <div className="card" style={{ overflow: 'hidden' }}>
      <div className="cover-band" style={{ background: accent.solid }} />
      <div className="card-pad" style={{ textAlign: 'center' }}>
        <h3 style={{ marginBottom: 4 }}>Share this survey</h3>
        <p className="muted" style={{ marginTop: 0, marginBottom: 20, fontSize: 13.5 }}>
          Scan, print, or send the link directly.
        </p>

        <div style={{
          background: accent.soft, borderRadius: 'var(--radius-md)',
          padding: 16, display: 'inline-block', marginBottom: 16,
        }}>
          <img src={qr.qr_image_url} alt="Survey QR code" style={{ width: 180, height: 180, display: 'block' }} />
        </div>

        <p style={{
          fontSize: 12.5, wordBreak: 'break-all', color: 'var(--ink-soft)',
          background: 'var(--paper)', borderRadius: 'var(--radius-sm)', padding: '8px 10px', marginBottom: 14,
        }}>
          {qr.public_url}
        </p>

        <div style={{ display: 'flex', gap: 8 }}>
          <a href={qr.qr_image_url} download className="btn btn-ghost btn-sm" style={{ flex: 1, textDecoration: 'none' }}>
            Download PNG
          </a>
          <button onClick={copyLink} className="btn btn-primary btn-sm" style={{ flex: 1 }}>
            {copied ? 'Copied!' : 'Copy link'}
          </button>
        </div>
      </div>
    </div>
  );
}