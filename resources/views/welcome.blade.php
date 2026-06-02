<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment Webhook API</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:        #080b10;
            --surface:   #0e1117;
            --card:      #131720;
            --border:    #1e2535;
            --border-hi: #2a3447;
            --text:      #e2e8f0;
            --muted:     #64748b;
            --subtle:    #94a3b8;
            --green:     #10b981;
            --blue:      #3b82f6;
            --amber:     #f59e0b;
            --red:       #ef4444;
            --purple:    #8b5cf6;
            --accent:    #6366f1;
        }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            line-height: 1.6;
            min-height: 100vh;
        }

        /* ── Header ─────────────────────────────────────────── */
        header {
            background: linear-gradient(135deg, #0e1117 0%, #111827 60%, #0f172a 100%);
            border-bottom: 1px solid var(--border);
            padding: 48px 0 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        header::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(ellipse 800px 300px at 50% -40px, rgba(99,102,241,.18), transparent);
            pointer-events: none;
        }
        .logo-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(99,102,241,.12);
            border: 1px solid rgba(99,102,241,.25);
            border-radius: 999px;
            padding: 4px 14px 4px 10px;
            font-size: 12px;
            font-weight: 600;
            color: #a5b4fc;
            letter-spacing: .03em;
            margin-bottom: 20px;
        }
        .logo-pill svg { width: 14px; height: 14px; }
        h1 {
            font-size: clamp(24px, 4vw, 36px);
            font-weight: 700;
            letter-spacing: -.02em;
            color: #f1f5f9;
            margin-bottom: 10px;
        }
        h1 span { color: var(--accent); }
        .header-desc {
            color: var(--subtle);
            font-size: 15px;
            max-width: 480px;
            margin: 0 auto 24px;
        }
        .base-url {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: var(--card);
            border: 1px solid var(--border-hi);
            border-radius: 10px;
            padding: 10px 18px;
            font-size: 13px;
            color: var(--subtle);
        }
        .base-url code { color: #a5b4fc; font-family: 'SF Mono', 'Fira Code', monospace; font-size: 13px; }
        .status-dot {
            width: 7px; height: 7px;
            background: var(--green);
            border-radius: 50%;
            box-shadow: 0 0 6px var(--green);
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50%       { opacity: .45; }
        }

        /* ── Layout ──────────────────────────────────────────── */
        main {
            max-width: 860px;
            margin: 0 auto;
            padding: 48px 24px 80px;
        }

        /* ── Section ─────────────────────────────────────────── */
        .section { margin-bottom: 44px; }
        .section-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--border);
        }
        .section-icon {
            width: 28px; height: 28px;
            border-radius: 7px;
            display: grid;
            place-items: center;
        }
        .section-icon svg { width: 14px; height: 14px; }
        .section-title {
            font-size: 13px;
            font-weight: 600;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: var(--subtle);
        }
        .section-count {
            margin-left: auto;
            font-size: 11px;
            color: var(--muted);
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 999px;
            padding: 1px 8px;
        }

        /* ── Endpoint Card ───────────────────────────────────── */
        .endpoint {
            display: grid;
            grid-template-columns: auto 1fr auto;
            align-items: start;
            gap: 14px;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 16px 18px;
            margin-bottom: 8px;
            transition: border-color .15s, background .15s;
        }
        .endpoint:hover {
            border-color: var(--border-hi);
            background: #161d2a;
        }
        .method {
            font-family: 'SF Mono', 'Fira Code', monospace;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .04em;
            border-radius: 6px;
            padding: 3px 9px;
            min-width: 52px;
            text-align: center;
            align-self: center;
        }
        .GET    { background: rgba(59,130,246,.15);  color: #93c5fd; border: 1px solid rgba(59,130,246,.25); }
        .POST   { background: rgba(16,185,129,.15);  color: #6ee7b7; border: 1px solid rgba(16,185,129,.25); }
        .endpoint-path {
            font-family: 'SF Mono', 'Fira Code', monospace;
            font-size: 13px;
            color: #e2e8f0;
            margin-bottom: 4px;
            word-break: break-all;
        }
        .endpoint-path .param { color: #fbbf24; }
        .endpoint-desc {
            font-size: 13px;
            color: var(--subtle);
            line-height: 1.5;
        }
        .badges {
            display: flex;
            flex-direction: column;
            gap: 4px;
            align-items: flex-end;
            align-self: center;
        }
        .badge {
            font-size: 10px;
            font-weight: 600;
            letter-spacing: .04em;
            border-radius: 5px;
            padding: 2px 7px;
            white-space: nowrap;
        }
        .badge-auth   { background: rgba(245,158,11,.12); color: #fcd34d; border: 1px solid rgba(245,158,11,.2); }
        .badge-public { background: rgba(100,116,139,.1);  color: #94a3b8; border: 1px solid rgba(100,116,139,.2); }
        .badge-signed { background: rgba(139,92,246,.12); color: #c4b5fd; border: 1px solid rgba(139,92,246,.2); }

        /* ── Auth note ───────────────────────────────────────── */
        .auth-note {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            background: rgba(99,102,241,.07);
            border: 1px solid rgba(99,102,241,.18);
            border-radius: 10px;
            padding: 14px 16px;
            margin-bottom: 32px;
            font-size: 13px;
            color: #a5b4fc;
        }
        .auth-note svg { width: 16px; height: 16px; flex-shrink: 0; margin-top: 1px; }

        /* ── Footer ──────────────────────────────────────────── */
        footer {
            text-align: center;
            padding: 28px;
            border-top: 1px solid var(--border);
            color: var(--muted);
            font-size: 12px;
        }
        footer a { color: var(--subtle); text-decoration: none; }
        footer a:hover { color: var(--text); }

        @media (max-width: 560px) {
            .endpoint { grid-template-columns: auto 1fr; }
            .badges   { flex-direction: row; align-items: center; grid-column: 1 / -1; }
        }
    </style>
</head>
<body>

<header>
    <div class="logo-pill">
        <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="M8 1.5 L14 5v6L8 14.5 L2 11V5Z"/>
        </svg>
        Payment Webhook API
    </div>
    <h1>API <span>Reference</span></h1>
    <p class="header-desc">REST API for payment processing, webhooks, and real-time event streaming.</p>
    <div class="base-url">
        <span class="status-dot"></span>
        <span>Base URL</span>
        <code>http://{{ request()->getHost() }}/api</code>
    </div>
</header>

<main>

    <div class="auth-note">
        <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd"/></svg>
        <span>Endpoints marked <strong>🔒 Auth required</strong> need a <code style="background:rgba(255,255,255,.07);padding:1px 5px;border-radius:4px">Bearer &lt;token&gt;</code> header obtained from <code style="background:rgba(255,255,255,.07);padding:1px 5px;border-radius:4px">POST /api/login</code>.</span>
    </div>

    {{-- ── Authentication ─────────────────────────────────── --}}
    <div class="section">
        <div class="section-header">
            <div class="section-icon" style="background:rgba(16,185,129,.12)">
                <svg viewBox="0 0 20 20" fill="#6ee7b7"><path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd"/></svg>
            </div>
            <span class="section-title">Authentication</span>
            <span class="section-count">3 endpoints</span>
        </div>

        <div class="endpoint">
            <span class="method POST">POST</span>
            <div>
                <div class="endpoint-path">/api/login</div>
                <div class="endpoint-desc">Authenticate with email and password. Returns a Sanctum bearer token. Rate limited to 5 attempts per 15 minutes.</div>
            </div>
            <div class="badges"><span class="badge badge-public">Public</span></div>
        </div>

        <div class="endpoint">
            <span class="method POST">POST</span>
            <div>
                <div class="endpoint-path">/api/logout</div>
                <div class="endpoint-desc">Invalidate the current bearer token and end the session.</div>
            </div>
            <div class="badges"><span class="badge badge-auth">🔒 Auth required</span></div>
        </div>

        <div class="endpoint">
            <span class="method GET">GET</span>
            <div>
                <div class="endpoint-path">/api/user</div>
                <div class="endpoint-desc">Return the authenticated user's profile information.</div>
            </div>
            <div class="badges"><span class="badge badge-auth">🔒 Auth required</span></div>
        </div>
    </div>

    {{-- ── Payments ────────────────────────────────────────── --}}
    <div class="section">
        <div class="section-header">
            <div class="section-icon" style="background:rgba(59,130,246,.12)">
                <svg viewBox="0 0 20 20" fill="#93c5fd"><path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"/><path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"/></svg>
            </div>
            <span class="section-title">Payments</span>
            <span class="section-count">3 endpoints</span>
        </div>

        <div class="endpoint">
            <span class="method GET">GET</span>
            <div>
                <div class="endpoint-path">/api/payments</div>
                <div class="endpoint-desc">Paginated list of all payment records. Supports filtering by status, date range, and amount.</div>
            </div>
            <div class="badges"><span class="badge badge-auth">🔒 Auth required</span></div>
        </div>

        <div class="endpoint">
            <span class="method GET">GET</span>
            <div>
                <div class="endpoint-path">/api/payments/<span class="param">{payment_id}</span>/events</div>
                <div class="endpoint-desc">Return the full event history for a specific payment — useful for debugging webhook delivery and status transitions.</div>
            </div>
            <div class="badges"><span class="badge badge-auth">🔒 Auth required</span></div>
        </div>

        <div class="endpoint">
            <span class="method GET">GET</span>
            <div>
                <div class="endpoint-path">/api/payments/export</div>
                <div class="endpoint-desc">Export the payment list as a downloadable CSV file.</div>
            </div>
            <div class="badges"><span class="badge badge-auth">🔒 Auth required</span></div>
        </div>
    </div>

    {{-- ── Webhooks ─────────────────────────────────────────── --}}
    <div class="section">
        <div class="section-header">
            <div class="section-icon" style="background:rgba(139,92,246,.12)">
                <svg viewBox="0 0 20 20" fill="#c4b5fd"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/></svg>
            </div>
            <span class="section-title">Webhooks</span>
            <span class="section-count">1 endpoint</span>
        </div>

        <div class="endpoint">
            <span class="method POST">POST</span>
            <div>
                <div class="endpoint-path">/api/webhooks/payment</div>
                <div class="endpoint-desc">Receive incoming payment events from external providers. Requests must include a valid HMAC signature in the <code style="background:rgba(255,255,255,.06);padding:1px 5px;border-radius:4px;font-size:12px">X-Webhook-Signature</code> header. Rate limited to 100 requests per minute.</div>
            </div>
            <div class="badges"><span class="badge badge-signed">🔐 Signed</span></div>
        </div>
    </div>

    {{-- ── Admin ────────────────────────────────────────────── --}}
    <div class="section">
        <div class="section-header">
            <div class="section-icon" style="background:rgba(245,158,11,.1)">
                <svg viewBox="0 0 20 20" fill="#fcd34d"><path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/></svg>
            </div>
            <span class="section-title">Admin</span>
            <span class="section-count">2 endpoints</span>
        </div>

        <div class="endpoint">
            <span class="method POST">POST</span>
            <div>
                <div class="endpoint-path">/api/admin/refund</div>
                <div class="endpoint-desc">Issue a refund for a processed payment. Requires admin privileges.</div>
            </div>
            <div class="badges"><span class="badge badge-auth">🔒 Auth required</span></div>
        </div>

        <div class="endpoint">
            <span class="method GET">GET</span>
            <div>
                <div class="endpoint-path">/api/metrics</div>
                <div class="endpoint-desc">Aggregate metrics — total volume, success rate, failed payments, and daily breakdowns.</div>
            </div>
            <div class="badges"><span class="badge badge-auth">🔒 Auth required</span></div>
        </div>
    </div>

    {{-- ── System ───────────────────────────────────────────── --}}
    <div class="section">
        <div class="section-header">
            <div class="section-icon" style="background:rgba(100,116,139,.1)">
                <svg viewBox="0 0 20 20" fill="#94a3b8"><path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/></svg>
            </div>
            <span class="section-title">System</span>
            <span class="section-count">1 endpoint</span>
        </div>

        <div class="endpoint">
            <span class="method GET">GET</span>
            <div>
                <div class="endpoint-path">/api/health</div>
                <div class="endpoint-desc">Health check — returns <code style="background:rgba(255,255,255,.06);padding:1px 5px;border-radius:4px;font-size:12px">{"status":"ok"}</code>. Used by load balancers and uptime monitors.</div>
            </div>
            <div class="badges"><span class="badge badge-public">Public</span></div>
        </div>
    </div>

</main>

<footer>
    <p>Payment Webhook API &nbsp;·&nbsp; Laravel {{ app()->version() }} &nbsp;·&nbsp; <a href="/api/health">Health check</a></p>
</footer>

</body>
</html>
