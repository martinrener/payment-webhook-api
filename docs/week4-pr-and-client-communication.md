# Week 4 — PR Description & Client Communication

## PR Description

**Key design decisions and trade-offs by track:**

### Fullstack
Added three major features on top of the existing webhook system:
- **Metrics dashboard** — aggregates payment data by event type, currency, and volume by day. Previously the only way to analyze this data was manually browsing the payments and events tables. Trade-off: metrics are computed on every request with no caching, which adds DB load at scale.
- **CSV export** — allows exporting filtered payments to a file for use in external tools. Trade-off: large datasets are streamed synchronously, which could block the response for big exports.
- **WebSockets (Laravel Reverb)** — payments, events, and metrics update in real time when a webhook is processed. Trade-off: introduces operational complexity — a third service (Reverb) must be running alongside the app and worker.

### DevOps
Fully containerized the system with Docker and deployed to AWS ECS with three separated environments (dev, staging, prod) and an automated CI/CD pipeline. Trade-off: three environments add infrastructure cost and operational overhead, but protect production from untested changes. The key design decision is the separation of concerns between environments — dev for active development, staging for pre-production validation, prod for real clients.

### QA
Built a full test suite covering three layers: unit tests (service logic in isolation with mocks), feature tests (full HTTP lifecycle with a real DB), and Playwright E2E tests (real browser against the live AWS backend). The key design decision was using Playwright's globalSetup + storageState to authenticate once and reuse the session across all E2E tests. Trade-off: tests depend on a real user existing in the production database, which makes the test suite less portable across environments.

## Client Communication

Hi,

This week we made several improvements to the payment system that directly benefit how you use and monitor it.

First, we added a **Metrics dashboard** so you can now see at a glance how your payments are performing — total unique users, volume by day, breakdown by currency and event type. Previously this required manually browsing through individual payments and events.

Second, you can now **export your payments to a CSV file** with any filters applied, so you can use that data in Excel, Google Sheets, or any other tool — not just on the web dashboard.

Third, the **dashboard now updates in real time**. As soon as a payment webhook is received and processed, the table and metrics refresh automatically. You no longer need to reload the page to see the latest data.

On the infrastructure side, the system is now **running on AWS** and no longer depends on a local machine. It's always available in the cloud. We also set up three separate environments — development, staging, and production — so changes are tested before they ever reach you.

Finally, we built a **comprehensive test suite** that automatically verifies every part of the system — from individual functions to full user flows in the browser. This gives us confidence that the system works correctly and that future changes don't break existing functionality.

Let us know if you have any questions.
