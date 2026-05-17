<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'System Gateway') }}</title>
    <meta name="robots" content="noindex, nofollow">
    <meta name="description" content="Secure application gateway">

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            body {
                min-height: 100vh;
                font-family: Arial, Helvetica, sans-serif;
                background:
                    radial-gradient(circle at top left, rgba(59,130,246,0.10), transparent 30%),
                    radial-gradient(circle at bottom right, rgba(16,185,129,0.10), transparent 30%),
                    linear-gradient(180deg, #0b1020 0%, #111827 100%);
                color: #e5e7eb;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 24px;
            }

            .wrapper {
                width: 100%;
                max-width: 900px;
            }

            .card {
                background: rgba(17, 24, 39, 0.78);
                border: 1px solid rgba(255, 255, 255, 0.08);
                border-radius: 24px;
                padding: 40px;
                box-shadow: 0 20px 80px rgba(0, 0, 0, 0.45);
                backdrop-filter: blur(10px);
            }

            .topbar {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 32px;
                gap: 16px;
                flex-wrap: wrap;
            }

            .brand {
                display: flex;
                align-items: center;
                gap: 12px;
            }

            .dot {
                width: 12px;
                height: 12px;
                border-radius: 50%;
                background: #10b981;
                box-shadow: 0 0 20px rgba(16,185,129,0.8);
            }

            .brand-name {
                font-size: 14px;
                letter-spacing: 0.18em;
                text-transform: uppercase;
                color: #9ca3af;
            }

            .status {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                padding: 8px 14px;
                border-radius: 999px;
                background: rgba(16, 185, 129, 0.12);
                border: 1px solid rgba(16, 185, 129, 0.25);
                color: #a7f3d0;
                font-size: 13px;
            }

            h1 {
                font-size: 42px;
                line-height: 1.1;
                margin-bottom: 16px;
                color: #f9fafb;
            }

            .lead {
                font-size: 16px;
                line-height: 1.8;
                color: #9ca3af;
                max-width: 680px;
                margin-bottom: 34px;
            }

            .grid {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 18px;
                margin-bottom: 32px;
            }

            .panel {
                background: rgba(255,255,255,0.03);
                border: 1px solid rgba(255,255,255,0.06);
                border-radius: 18px;
                padding: 20px;
            }

            .panel-title {
                font-size: 12px;
                text-transform: uppercase;
                letter-spacing: 0.12em;
                color: #6b7280;
                margin-bottom: 10px;
            }

            .panel-value {
                font-size: 18px;
                font-weight: bold;
                color: #f3f4f6;
            }

            .footer {
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 16px;
                flex-wrap: wrap;
                padding-top: 20px;
                border-top: 1px solid rgba(255,255,255,0.06);
                color: #6b7280;
                font-size: 13px;
            }

            .badge-group {
                display: flex;
                gap: 10px;
                flex-wrap: wrap;
            }

            .badge {
                padding: 8px 12px;
                border-radius: 999px;
                background: rgba(255,255,255,0.04);
                border: 1px solid rgba(255,255,255,0.06);
                color: #9ca3af;
                font-size: 12px;
            }

            code {
                color: #93c5fd;
                font-size: 13px;
            }

            @media (max-width: 768px) {
                .card {
                    padding: 28px;
                    border-radius: 20px;
                }

                h1 {
                    font-size: 30px;
                }

                .grid {
                    grid-template-columns: 1fr;
                }
            }
        </style>
    @endif
</head>
<body>
    <div class="wrapper">
        <div class="card">
            <div class="topbar">
                <div class="brand">
                    <span class="dot"></span>
                    <span class="brand-name">Secure Gateway</span>
                </div>

                <div class="status">
                    <span>●</span>
                    <span>Operational</span>
                </div>
            </div>

            <h1>Access Point Active</h1>

            <p class="lead">
                This interface is reserved for authorized application traffic and service communication.
                Direct interactive access is limited. All requests are monitored and validated by the gateway layer.
            </p>

            <div class="grid">
                <div class="panel">
                    <div class="panel-title">Environment</div>
                    <div class="panel-value">{{ app()->environment('production') ? 'Production' : ucfirst(app()->environment()) }}</div>
                </div>

                <div class="panel">
                    <div class="panel-title">Application</div>
                    <div class="panel-value">{{ config('app.name', 'System Node') }}</div>
                </div>

                <div class="panel">
                    <div class="panel-title">Response</div>
                    <div class="panel-value">200 / Ready</div>
                </div>
            </div>

            <div class="footer">
                <div>
                    Request routing available via <code>/api/*</code>
                </div>

                <div class="badge-group">
                    <span class="badge">TLS Protected</span>
                    <span class="badge">Monitored</span>
                    <span class="badge">Restricted Access</span>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
