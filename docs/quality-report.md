# Quality Report — Week 4 (QA Track)

## Overview

- **Project:** Payment Webhook Manager
- **Stack:** Laravel 11 + PHP 8.4 (backend), Nuxt 3 + Vue 3 (frontend)
- **Testing tools:** PHPUnit (unit + feature), Playwright (E2E)
- **Total tests:** 33 backend + 14 E2E = **47 tests**

## Backend Tests (Laravel)

### Unit Tests — 4 tests
Tested `WebhookService` in isolation using PHPUnit mocks. No database involved.

| Test | What it verifies |
|------|-----------------|
| new event stores it | `receivePayment()` calls `store()` and `upsert()` exactly once |
| duplicate event does not store | `receivePayment()` returns early when event already exists |
| refund existing payment stores and upserts | `refundPayment()` calls `store()` and `upsert()` on valid payment |
| refund non-existing payment throws exception | `refundPayment()` throws `Exception` when payment not found |

### Feature Tests — 29 tests
Full HTTP request lifecycle tested with `RefreshDatabase`. Real Laravel app, real DB (SQLite in-memory).

| File | Endpoint | Cases covered |
|------|----------|---------------|
| AuthTest | POST /login | success, wrong user, wrong password, rate limiting, authenticated user |
| PaymentsTest | GET /payments | admin 200, non-admin 403, unauthenticated 401 |
| EventsTest | GET /payments/:id/events | no events, with events, non-admin 403, unauthenticated 401 |
| RefundTest | POST /admin/refund | success, non-existent payment 500, non-admin 403, unauthenticated 401 |
| ExportTest | GET /payments/export | admin 200 with CSV header, non-admin 403, unauthenticated 401 |
| MetricsTest | GET /metrics | admin 200 with structure, non-admin 403, unauthenticated 401 |
| HealthTest | GET /health | returns 200 with status ok |
| WebhookEndpointTest | POST /webhooks/payment | returns 200, dispatches 1 job, no DB write during request |
| WebhookIdempotencyTest | POST /webhooks/payment | duplicate event is ignored |
| MetricsRepositoryTest | EloquentMetricsRepository | correct grouping by event, currency, unique users, volume by day |

## E2E Tests (Playwright) — 14 tests

All tests run against the real AWS backend. Authentication is handled once via `globalSetup` + `storageState` to avoid hitting the rate limit (5 requests per 15 minutes).

| Test | Flow |
|------|------|
| login exitoso | fills form → redirects to /payments |
| login fallido | wrong password → shows error message |
| tabla de payments muestra datos | /payments loads with table rows visible |
| filtro por currency | fills USD → all rows show USD |
| filtro por event | fills payment.refunded → all rows match |
| filtro por user_id | fills user_05 → all rows match |
| filtro combinado | currency + event + user_id → all rows match all filters |
| click en fila navega a detalle | clicks first row → navigates to /payments/:id |
| refund crea evento | clicks Refund → confirms modal → event appears in detail page |
| export CSV | clicks Export CSV → file downloads with name payments.csv |
| logout | clicks Logout → redirects to /login |
| metrics carga correctamente | navigates to /metrics → charts visible |
| paginación | clicks Siguiente N times → button becomes disabled on last page |

## Edge Cases Covered

- **Deduplication:** duplicate webhook events are ignored at the service level (unit + feature)
- **Authorization:** every admin endpoint tested with non-admin (403) and unauthenticated (401) scenarios
- **Rate limiting:** login endpoint throttled at 5 requests/15min — E2E tests use storageState to avoid hitting it
- **Error handling:** refund of non-existent payment returns 500 gracefully
- **CSV export:** response type is StreamedResponse — tested via Content-Disposition header, not Content-Type

## Bugs Found During Testing

Two real bugs were discovered while writing E2E tests:

**Bug 1 — Vue hydration race condition (login.vue)**
Playwright was filling the form and clicking submit before Vue finished hydrating on the client. The `@submit.prevent` handler wasn't attached yet, causing the form to submit natively (GET to `/login?`) instead of calling the API. Fixed by adding a `mounted` ref and disabling the fieldset until hydration completes — Playwright automatically waits for enabled elements.

**Bug 2 — Axios interceptor too aggressive (apiInstance.ts)**
The 401 interceptor was redirecting to `/login` on every 401 response, including failed login attempts. This caused the page to reload before the error message could render. Fixed by only redirecting when a token was present in localStorage (session expired), not on login failures.

## Technical Decisions

| Decision | Reason |
|----------|--------|
| Unit tests use PHPUnit mocks | Isolate service logic from DB — faster, no side effects |
| Feature tests use RefreshDatabase | Each test gets a clean DB state — no test pollution |
| MetricsRepository tested as feature test | Repository uses Eloquent directly — can't mock DB calls cleanly |
| Playwright globalSetup + storageState | Login once, reuse auth across all E2E tests — avoids rate limit |
| Serial mode in payments.spec.ts | Tests share state (refund modifies data) — parallel execution causes failures |
| StreamedResponse tested via Content-Disposition | Content-Type not set correctly in test environment for streamed responses |

## What's Not Tested

- **WebSockets / real-time updates** — would require a running Reverb server in the test environment
- **Concurrent webhook processing** — race conditions at the queue level require load testing tools
- **CSV content validation** — only filename is asserted, not the actual CSV data
- **Mobile viewports** — Playwright configured for Desktop Chrome only