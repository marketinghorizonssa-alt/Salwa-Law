# Salwa Ahmed Law

Arabic-first, campaign-ready legal website for Salwa Ahmed.

## Production
- Production URL: `https://salwalaw.hositee.com`
- Current visual layer: `v4`
- Reference UX: ENKAF / Atheer patterns only; no runtime dependency on either client.
- Typography: IBM Plex Sans Arabic for body copy and Noto Kufi Arabic for headings and primary actions.
- Each priority landing page has a lightweight service-specific legal background while preserving the form-first RTL layout.

## Pages
- `/`
- `/استشارات-قانونية/`
- `/محامي-عقود/`
- `/محامي-قضايا-عمالية/`
- `/محامي-احوال-شخصية/`
- `/ملكية-فكرية/`
- `/سياسة-الخصوصية/`
- `/شكرا/`

## Tracking contract
- CTA links carry `data-event="click_call"` / `data-event="click_whatsapp"`.
- Successful durable form write dispatches browser event `lead_form_success` once.
- Attribution fields persist in session storage and are stored server-side.
- No PII is placed in the thank-you URL.

## Lead storage
Lead data is stored in the private Hostinger runtime outside the public document root. The token-protected CSV feed supplies the hidden Google Sheets source tab. Runtime secrets and customer data are never committed to GitHub.

## Release safety
GitHub is the source of truth. Hostinger deploys an exact recorded commit and exposes it through `/healthz/`. Production is indexable only when `SALWA_REVIEW_MODE=false`. Visual changes must preserve stable URLs, metadata, form field names, attribution fields, and the existing conversion event contract.
