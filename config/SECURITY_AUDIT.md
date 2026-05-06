# Security Audit - Week 2

## Overview
This document covers the security audit done on the payment webhook system as part of Week 2 training. The audit focused on OWASP A01 (Broken Access Control) and A07 (Authentication Failures). For each issue found, I describe what the problem was, what could have happened, how I fixed it, and how to verify the fix.

## OWASP A01 - Broken Access Control

### What was the problem
Any authenticated user could access all payments, payment events, and trigger refunds. There was no role system — if you had a valid token, you could do everything.

### What could happen
A regular user could see all payment data from all customers, and could trigger refunds they shouldn't have access to. This is a direct violation of the principle of least privilege.

### How I fixed it
- Added an `is_admin` boolean field to the `users` table
- Defined a Gate called `access-admin` in `AppServiceProvider` that checks if the authenticated user has `is_admin === true`
- Applied `Gate::authorize('access-admin')` as the first line in `getPayments`, `getPaymentEvents`, and `refundPayment`
- Non-admin users now get a `403 Unauthorized` response on all those endpoints

### How to verify
Login with a non-admin user and try to call `GET /api/payments` or `POST /api/admin/refund`. You should get a 403 response.

---

## OWASP A07 - Authentication Failures

### What was the problem
Three issues were found:

1. **Brute force:** The login endpoint had no rate limiting, so an attacker could try passwords indefinitely until they got in.
2. **Token persistence:** The token was stored in Pinia memory, so reloading the page would log the user out and lose their session.
3. **Residual state on logout:** When a user logged out, the payments store was not cleared. If an admin logged out and a non-admin logged in right after without reloading, the non-admin could still see all the admin's payment data in the browser.

### How I fixed it
- Added `throttle:5,15` middleware to `POST /api/login` — max 5 attempts per 15 minutes per IP, using Laravel's built-in throttle
- Replaced Pinia memory storage with `useCookie` from Nuxt — the token now persists across page reloads with `sameSite: lax` and 7 day expiration
- Added `paymentsStore.$reset()` in the logout action to clear all payment data from the store before clearing the token
- Set token expiration to 7 days in `config/sanctum.php`

### How to verify
- **Brute force:** Try logging in with wrong credentials 6 times in a row — the 6th attempt should return `429 Too Many Requests`
- **Token persistence:** Login, reload the page — you should still be logged in
- **Residual state:** Login as admin, logout, login as non-admin without reloading — payment data should not be visible

---

## Known Limitations

These issues were identified but not fully resolved in this iteration:

- **Webhook endpoint is public:** `POST /api/webhooks/payment` has no authentication or signature verification. Anyone can inject fake payments. A fix would be to validate a shared secret or HMAC signature on incoming webhooks.
- **Cookie is not HttpOnly:** The auth token is stored in a client-side cookie via Nuxt's `useCookie`, which is still accessible by JavaScript. This means it could be stolen in an XSS attack. The ideal solution would be to use Laravel Sanctum's cookie-based auth with a server-set HttpOnly cookie, but this requires same-domain configuration between the API and the frontend which is out of scope for this iteration.