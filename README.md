# Survey App — Laravel + React

A full-stack survey tool: admins build surveys with open- and closed-ended
questions, distribute them via QR code, and export results to Excel or a
shareable PNG summary image.

## What's included

**Backend (`/backend`, Laravel 11 API)**
- `surveys`, `questions`, `question_options`, `survey_responses`, `answers` tables
- Admin API (Sanctum token auth): create/edit surveys, add questions
  (short text, long text, single choice, multiple choice, rating), reorder,
  generate QR codes (`endroid/qr-code`)
- Public API: fetch a survey by slug, submit a response — this is what the
  QR code points to
- Export API:
  - **Excel** (`maatwebsite/excel`) — a 2-sheet workbook: raw responses
    (one row per respondent) + a summary sheet with option tallies/percentages
  - **Image** (PHP GD, no extra native deps) — a PNG bar-chart "results card"
    of the closed-ended questions, good for slides or sharing

**Frontend (`/frontend`, React + Vite)**
- `/admin/login`, `/admin` (survey list), `/admin/surveys/:id` (question
  builder + QR code panel), `/admin/surveys/:id/responses` (export buttons)
- `/survey/:slug` — the public page respondents land on after scanning the QR

## ⚠️ One honest caveat

This repo was assembled by hand in a sandbox that has no PHP and no access
to Packagist (Composer's package registry) — so `composer install` has not
actually been run or verified end-to-end here. Every file matches Laravel
11's real skeleton structure (I know it well), but you should still run
`composer install` and `php artisan serve` locally as your first step, and
fix anything that surfaces before deploying — treat it as a complete but
untested repo, not a live-tested one.

### Local setup

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
# fill in DB credentials in .env (a local Postgres is easiest to match production;
# or point DB_CONNECTION=sqlite + DB_DATABASE=database/database.sqlite for a quick local run)
php artisan migrate
php artisan db:seed          # creates admin@example.com / password — change it after first login
php artisan storage:link
php artisan serve            # http://localhost:8000
```

```bash
cd frontend
npm install
cp .env.example .env      # set VITE_API_URL if backend isn't on localhost:8000
npm run dev                # http://localhost:5173
```

## Deploying (Render + Vercel — same setup as the HRTC shop)

**Backend → Render**
1. Push this repo to GitHub (the `backend/` folder is the complete Laravel project — nothing else to merge in).
2. On Render: New → Web Service → connect the repo → set the root directory to `backend/`
   → it should auto-detect the `Dockerfile`.
3. New → PostgreSQL (Render's free tier — there's no free managed MySQL anymore,
   which is why this project uses `pgsql`). Copy its internal connection details.
4. On the web service, set environment variables: `APP_KEY` (run
   `php artisan key:generate --show` locally to get one), `APP_URL` (your
   Render URL), `FRONTEND_URL` (your Vercel URL, set after step below),
   `DB_CONNECTION=pgsql` + the DB host/port/database/username/password from step 3.
5. Deploy. The Dockerfile's CMD runs `storage:link` and `migrate` on every
   boot, so a fresh container always re-links storage and stays schema-current.

**Frontend → Vercel**
1. Import the repo, set the root directory to `frontend/`.
2. Framework preset: Vite. Build command `npm run build`, output `dist`.
3. Environment variable: `VITE_API_URL=https://your-backend.onrender.com/api`.
4. Deploy, then go back and set `FRONTEND_URL` on the Render backend to this
   Vercel URL so QR codes and CORS both point to the right place, and redeploy the backend.

**Things that bit the HRTC shop and are pre-handled here, but worth knowing:**
- Render dropped native PHP support → this uses the included Dockerfile.
- No free managed MySQL on Render → migrations are already written against `pgsql`.
- If you later add email (e.g. "notify admin on new response"), Render's free
  tier blocks outbound SMTP — use Resend's HTTPS API like the HRTC shop does,
  not Gmail SMTP.
- The image export needs the `gd` PHP extension — already installed in the Dockerfile.

## How the pieces fit together

1. Admin logs in, creates a survey, adds questions (choosing open-ended
   short/long text or closed-ended single/multiple choice/rating).
2. The moment a survey is created, a QR PNG is generated and stored in
   `storage/app/public/qrcodes/{slug}.png`, encoding the link
   `FRONTEND_URL/survey/{slug}`. It's shown right next to the builder —
   download it or copy the link to paste anywhere.
3. Respondents scan the QR → land on `/survey/{slug}` → answers are posted
   to `POST /api/public/surveys/{slug}/responses`.
4. Admin opens the survey's Responses page and downloads either the Excel
   workbook (raw + summary) or the PNG results card.

## Notes / next steps you may want
- The image export currently covers closed-ended questions only (bar
  chart); open-ended text lives on the Excel sheet. If you want word-cloud
  or theme-tagging for open text, that'd be a good next addition.
- Add rate-limiting / a CAPTCHA on the public submit route if the survey
  will be distributed somewhere public rather than in a controlled setting.
- For production, swap `php artisan serve` for a real web server and set
  `APP_ENV=production`, `APP_DEBUG=false`.
