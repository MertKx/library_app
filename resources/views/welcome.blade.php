<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Library Management System</title>
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600" rel="stylesheet" />

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            color: #e5e5e5;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            position: absolute;
            top: 2rem;
            right: 2rem;
            display: flex;
            gap: 1rem;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
            border: 1px solid transparent;
            cursor: pointer;
            display: inline-block;
        }

        .btn-ghost {
            color: #9ca3af;
            border-color: #374151;
            background: transparent;
        }

        .btn-ghost:hover {
            color: #e5e5e5;
            border-color: #6b7280;
            background: rgba(75, 85, 99, 0.1);
        }

        .btn-primary {
            background: #374151;
            color: #e5e5e5;
            border-color: #4b5563;
        }

        .btn-primary:hover {
            background: #4b5563;
            border-color: #6b7280;
        }

        .main {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
            background: #202020;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            min-height: 600px;
        }

        .hero-section {
            background: linear-gradient(135deg, #111111 0%, #1f1f1f 100%);
            padding: 4rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="%23333" stroke-width="0.5"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
            opacity: 0.1;
        }

        .hero-content {
            position: relative;
            z-index: 1;
        }

        .hero-title {
            font-size: 3rem;
            font-weight: 600;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #e5e5e5 0%, #9ca3af 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            line-height: 1.2;
        }

        .hero-subtitle {
            font-size: 1.25rem;
            color: #9ca3af;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .hero-features {
            list-style: none;
            space-y: 1rem;
        }

        .hero-features li {
            display: flex;
            align-items: center;
            color: #d1d5db;
            margin-bottom: 1rem;
            font-size: 0.95rem;
        }

        .hero-features li::before {
            content: '→';
            color: #6b7280;
            margin-right: 1rem;
            font-weight: bold;
        }

        .content-section {
            background: #2a2a2a;
            padding: 4rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .section-title {
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #e5e5e5;
        }

        .section-subtitle {
            color: #9ca3af;
            margin-bottom: 2.5rem;
            font-size: 1rem;
            line-height: 1.6;
        }

        .feature-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
            margin-bottom: 2.5rem;
        }

        .feature-item {
            padding: 1.5rem;
            background: #353535;
            border-radius: 12px;
            border: 1px solid #404040;
            transition: all 0.2s ease;
        }

        .feature-item:hover {
            border-color: #525252;
            background: #3a3a3a;
        }

        .feature-title {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #e5e5e5;
            font-size: 0.95rem;
        }

        .feature-description {
            color: #a1a1aa;
            font-size: 0.875rem;
            line-height: 1.5;
        }

        .cta-section {
            display: flex;
            gap: 1rem;
        }

        .btn-cta {
            padding: 1rem 2rem;
            background: #374151;
            color: #e5e5e5;
            border: 1px solid #4b5563;
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
            flex: 1;
            text-align: center;
        }

        .btn-cta:hover {
            background: #4b5563;
            border-color: #6b7280;
            transform: translateY(-1px);
        }

        @media (max-width: 768px) {
            .main {
                grid-template-columns: 1fr;
                margin: 1rem;
            }

            .hero-section,
            .content-section {
                padding: 2rem;
            }

            .hero-title {
                font-size: 2rem;
            }

            .header {
                position: relative;
                top: auto;
                right: auto;
                justify-content: center;
                margin-bottom: 2rem;
            }

            .cta-section {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <main class="main">
        <div class="hero-section">
            <div class="hero-content">
                <h1 class="hero-title">Digital Library Management</h1>
                <p class="hero-subtitle">Streamline your library operations with our comprehensive management system</p>
                <ul class="hero-features">
                    <li>Catalog and organize your entire collection</li>
                    <li>Track borrowing and returns efficiently</li>
                    <li>Manage member accounts and profiles</li>
                    <li>Generate detailed reports and analytics</li>
                </ul>
            </div>
        </div>

        <div class="content-section">
            <h2 class="section-title">Get Started</h2>
            <p class="section-subtitle">Choose how you want to begin your library management journey</p>

            <div class="feature-grid">
                <div class="cta-section" style="justify-content:center;">
                    <a href="/login" class="btn-cta" style="font-size:1.2rem;">Log in</a>
                    <a href="/register" class="btn-cta" style="font-size:1.2rem;">Register</a>
                </div>
            </div>
        </div>
    </main>
</div>

@if (Route::has('login'))
    <div class="h-14.5 hidden lg:block"></div>
@endif
</body>
</html>
