# Salwa Ahmed Law

Arabic-first, campaign-ready legal website for Salwa Ahmed.

## Target
- Production target: `https://salwalaw.hositee.com`
- Reference UX only: ENKAF repository/site. No ENKAF runtime dependency.

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
Default storage is an append-only private JSONL file outside the public root, protected with server-side file locking. `SALWA_DATA_DIR` must point to a private writable directory on production. Optional token-protected CSV feed is disabled until `SALWA_FEED_TOKEN` is configured.

## Release safety
Keep `SALWA_REVIEW_MODE=true` on staging/review. Set to `false` only for an approved production release after QA.
