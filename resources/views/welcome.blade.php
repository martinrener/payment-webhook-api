<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment Webhook API</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { mono: ['ui-monospace', 'SFMono-Regular', 'Menlo', 'monospace'] }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-950 text-gray-100 min-h-screen">

    {{-- Header --}}
    <div class="border-b border-gray-800 bg-gray-900">
        <div class="max-w-4xl mx-auto px-6 py-10">
            <div class="flex items-center gap-3 mb-1">
                <span class="w-2 h-2 rounded-full bg-emerald-400 shadow shadow-emerald-400/50 animate-pulse"></span>
                <span class="text-xs font-medium text-emerald-400 tracking-widest uppercase">Live</span>
            </div>
            <h1 class="text-3xl font-bold text-white tracking-tight mb-2">Payment Webhook API</h1>
            <div class="flex items-center gap-3 text-sm text-gray-400">
                <span>Server</span>
                <code class="bg-gray-800 text-indigo-300 px-2 py-0.5 rounded font-mono text-xs">http://35.225.178.162</code>
                <span class="text-gray-600">·</span>
                <code class="bg-gray-800 text-indigo-300 px-2 py-0.5 rounded font-mono text-xs">Base path: /api</code>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-6 py-10 space-y-10">

        {{-- Auth note --}}
        <div class="flex gap-3 bg-indigo-950/50 border border-indigo-800/50 rounded-xl p-4 text-sm text-indigo-300">
            <svg class="w-4 h-4 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd"/>
            </svg>
            <span>
                Protected endpoints require <code class="bg-indigo-900/60 px-1.5 py-0.5 rounded font-mono text-xs">Authorization: Bearer &lt;token&gt;</code>.
                Get a token from <code class="bg-indigo-900/60 px-1.5 py-0.5 rounded font-mono text-xs">POST /api/login</code>.
                Admin-only endpoints also require the user to have <code class="bg-indigo-900/60 px-1.5 py-0.5 rounded font-mono text-xs">is_admin = true</code>.
            </span>
        </div>

        {{-- ── Authentication ──────────────────────────────── --}}
        @php
        $sections = [
            [
                'title' => 'Authentication',
                'endpoints' => [
                    [
                        'method' => 'POST',
                        'path' => '/api/login',
                        'desc' => 'Authenticate with email and password. Returns a Sanctum bearer token, the user\'s name, and admin flag.',
                        'badge' => 'public',
                        'rate' => '5 req / 15 min',
                        'body' => [
                            ['name' => 'email',    'type' => 'string',  'req' => true,  'note' => 'Valid email address'],
                            ['name' => 'password', 'type' => 'string',  'req' => true,  'note' => 'Account password'],
                        ],
                        'response' => '{ "token": "...", "is_admin": true, "name": "John" }',
                    ],
                    [
                        'method' => 'POST',
                        'path' => '/api/logout',
                        'desc' => 'Invalidate the current bearer token and end the session.',
                        'badge' => 'auth',
                        'body' => [],
                        'response' => '{ "message": "ok" }',
                    ],
                    [
                        'method' => 'GET',
                        'path' => '/api/user',
                        'desc' => 'Return the authenticated user\'s profile.',
                        'badge' => 'auth',
                        'body' => [],
                        'response' => '{ "id": 1, "name": "...", "email": "...", "is_admin": false }',
                    ],
                ],
            ],
            [
                'title' => 'Payments',
                'endpoints' => [
                    [
                        'method' => 'GET',
                        'path' => '/api/payments',
                        'desc' => 'Paginated list of all payment records with optional filters.',
                        'badge' => 'admin',
                        'params' => [
                            ['name' => 'page',      'req' => false, 'note' => 'Page number (default: 1)'],
                            ['name' => 'per_page',  'req' => false, 'note' => 'Results per page (default: 10)'],
                            ['name' => 'event',     'req' => false, 'note' => 'Filter by event type'],
                            ['name' => 'user_id',   'req' => false, 'note' => 'Filter by user ID'],
                            ['name' => 'currency',  'req' => false, 'note' => 'Filter by 3-letter currency code'],
                            ['name' => 'date_from', 'req' => false, 'note' => 'Start date (ISO 8601)'],
                            ['name' => 'date_to',   'req' => false, 'note' => 'End date (ISO 8601)'],
                        ],
                        'response' => '{ "data": [...], "total": 100, "per_page": 10, "current_page": 1 }',
                    ],
                    [
                        'method' => 'GET',
                        'path' => '/api/payments/export',
                        'desc' => 'Export payments as a downloadable CSV file. Accepts the same filters as GET /payments.',
                        'badge' => 'admin',
                        'params' => [
                            ['name' => 'event',     'req' => false, 'note' => 'Filter by event type'],
                            ['name' => 'user_id',   'req' => false, 'note' => 'Filter by user ID'],
                            ['name' => 'currency',  'req' => false, 'note' => 'Filter by 3-letter currency code'],
                            ['name' => 'date_from', 'req' => false, 'note' => 'Start date (ISO 8601)'],
                            ['name' => 'date_to',   'req' => false, 'note' => 'End date (ISO 8601)'],
                        ],
                        'response' => 'Streams a payments.csv file',
                    ],
                    [
                        'method' => 'GET',
                        'path' => '/api/payments/{payment_id}/events',
                        'desc' => 'Return the full event history for a specific payment. Useful for tracing status transitions and webhook delivery.',
                        'badge' => 'admin',
                        'body' => [],
                        'response' => '[{ "id": 1, "event": "payment.created", "created_at": "..." }, ...]',
                    ],
                ],
            ],
            [
                'title' => 'Webhooks',
                'endpoints' => [
                    [
                        'method' => 'POST',
                        'path' => '/api/webhooks/payment',
                        'desc' => 'Receive an incoming payment event from an external provider. The request must include a valid HMAC signature in the X-Webhook-Signature header. Dispatches an async job to process the event.',
                        'badge' => 'signed',
                        'rate' => '100 req / min',
                        'body' => [
                            ['name' => 'event_id',   'type' => 'string',  'req' => true,  'note' => 'Unique ID for this event (idempotency key)'],
                            ['name' => 'payment_id', 'type' => 'string',  'req' => true,  'note' => 'ID of the payment this event belongs to'],
                            ['name' => 'event',      'type' => 'string',  'req' => true,  'note' => 'Event type, e.g. payment.created, payment.failed'],
                            ['name' => 'amount',     'type' => 'integer', 'req' => true,  'note' => 'Amount in the smallest currency unit (e.g. cents). Min: 0'],
                            ['name' => 'currency',   'type' => 'string',  'req' => true,  'note' => '3-letter ISO currency code (e.g. USD, ARS)'],
                            ['name' => 'timestamp',  'type' => 'date',    'req' => true,  'note' => 'When the event occurred (ISO 8601)'],
                            ['name' => 'user_id',    'type' => 'string',  'req' => true,  'note' => 'ID of the user associated with this payment'],
                        ],
                        'headers' => [
                            ['name' => 'X-Webhook-Signature', 'note' => 'HMAC-SHA256 signature of the request body'],
                        ],
                        'response' => '{ "message": "ok" }',
                    ],
                ],
            ],
            [
                'title' => 'Admin',
                'endpoints' => [
                    [
                        'method' => 'POST',
                        'path' => '/api/admin/refund',
                        'desc' => 'Issue a refund for a processed payment. Requires admin privileges.',
                        'badge' => 'admin',
                        'body' => [
                            ['name' => 'payment_id', 'type' => 'string', 'req' => true, 'note' => 'ID of the payment to refund'],
                        ],
                        'response' => '{ "message": "Refund processed" }',
                    ],
                    [
                        'method' => 'GET',
                        'path' => '/api/metrics',
                        'desc' => 'Aggregate payment metrics — total volume, success rate, failed payments, and breakdowns. Requires admin privileges.',
                        'badge' => 'admin',
                        'body' => [],
                        'response' => '{ "total": 500, "success_rate": 98.4, "failed": 8, ... }',
                    ],
                ],
            ],
            [
                'title' => 'System',
                'endpoints' => [
                    [
                        'method' => 'GET',
                        'path' => '/api/health',
                        'desc' => 'Health check endpoint. Used by load balancers and uptime monitors.',
                        'badge' => 'public',
                        'body' => [],
                        'response' => '{ "status": "ok" }',
                    ],
                ],
            ],
        ];
        @endphp

        @foreach($sections as $section)
        <div>
            <h2 class="text-xs font-semibold tracking-widest uppercase text-gray-500 mb-3">{{ $section['title'] }}</h2>
            <div class="space-y-3">
                @foreach($section['endpoints'] as $ep)
                <div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">

                    {{-- Top row --}}
                    <div class="flex items-start gap-3 p-4">
                        {{-- Method badge --}}
                        @if($ep['method'] === 'GET')
                            <span class="shrink-0 text-xs font-bold font-mono px-2 py-1 rounded bg-blue-500/15 text-blue-300 border border-blue-500/25">GET</span>
                        @else
                            <span class="shrink-0 text-xs font-bold font-mono px-2 py-1 rounded bg-emerald-500/15 text-emerald-300 border border-emerald-500/25">POST</span>
                        @endif

                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2 mb-1">
                                <code class="text-sm font-mono text-white">{{ $ep['path'] }}</code>
                                {{-- Auth badge --}}
                                @if($ep['badge'] === 'auth')
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-amber-500/10 text-amber-300 border border-amber-500/20">🔒 Auth</span>
                                @elseif($ep['badge'] === 'admin')
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-rose-500/10 text-rose-300 border border-rose-500/20">🔒 Admin</span>
                                @elseif($ep['badge'] === 'signed')
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-purple-500/10 text-purple-300 border border-purple-500/20">🔐 Signed</span>
                                @else
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-gray-700/50 text-gray-400 border border-gray-700">Public</span>
                                @endif
                                @if(isset($ep['rate']))
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-gray-800 text-gray-500 border border-gray-700">⏱ {{ $ep['rate'] }}</span>
                                @endif
                            </div>
                            <p class="text-sm text-gray-400 leading-relaxed">{{ $ep['desc'] }}</p>
                        </div>
                    </div>

                    {{-- Request headers --}}
                    @if(isset($ep['headers']) && count($ep['headers']) > 0)
                    <div class="border-t border-gray-800 px-4 py-3">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Required Headers</p>
                        <div class="space-y-1.5">
                            @foreach($ep['headers'] as $h)
                            <div class="flex items-start gap-3 text-sm">
                                <code class="font-mono text-purple-300 text-xs shrink-0">{{ $h['name'] }}</code>
                                <span class="text-gray-500 text-xs">{{ $h['note'] }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Request body --}}
                    @if(isset($ep['body']) && count($ep['body']) > 0)
                    <div class="border-t border-gray-800 px-4 py-3">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Request Body <span class="normal-case font-normal text-gray-600">(application/json)</span></p>
                        <div class="space-y-1.5">
                            @foreach($ep['body'] as $param)
                            <div class="flex items-start gap-3 text-sm">
                                <code class="font-mono text-indigo-300 text-xs w-28 shrink-0">{{ $param['name'] }}</code>
                                <span class="text-gray-600 text-xs w-14 shrink-0">{{ $param['type'] }}</span>
                                @if($param['req'])
                                    <span class="text-rose-400 text-xs shrink-0">required</span>
                                @else
                                    <span class="text-gray-600 text-xs shrink-0">optional</span>
                                @endif
                                <span class="text-gray-500 text-xs">{{ $param['note'] }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Query params --}}
                    @if(isset($ep['params']) && count($ep['params']) > 0)
                    <div class="border-t border-gray-800 px-4 py-3">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Query Parameters</p>
                        <div class="space-y-1.5">
                            @foreach($ep['params'] as $param)
                            <div class="flex items-start gap-3 text-sm">
                                <code class="font-mono text-indigo-300 text-xs w-28 shrink-0">{{ $param['name'] }}</code>
                                @if($param['req'])
                                    <span class="text-rose-400 text-xs w-14 shrink-0">required</span>
                                @else
                                    <span class="text-gray-600 text-xs w-14 shrink-0">optional</span>
                                @endif
                                <span class="text-gray-500 text-xs">{{ $param['note'] }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Response --}}
                    @if(isset($ep['response']))
                    <div class="border-t border-gray-800 px-4 py-3 bg-gray-950/50">
                        <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Response</p>
                        <code class="text-xs font-mono text-gray-400">{{ $ep['response'] }}</code>
                    </div>
                    @endif

                </div>
                @endforeach
            </div>
        </div>
        @endforeach

    </div>

    <footer class="border-t border-gray-800 mt-10 py-6 text-center text-xs text-gray-600">
        Payment Webhook API &nbsp;·&nbsp; Laravel {{ app()->version() }}
    </footer>

</body>
</html>
