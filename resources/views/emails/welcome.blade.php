<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Welcome !</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #007bff;
            margin: 0;
            font-size: 28px;
        }
        .content {
            color: #333;
        }
        .welcome-message {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            margin: 20px 0;
        }
        .info-box {
            background: #f8f9fa;
            border-left: 4px solid #007bff;
            padding: 15px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
            color: #6c757d;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ config('app.name') }}</h1>
        </div>

        <div class="content">
            <div class="welcome-message">
                <h2>🎉 Welcome !</h2>
                <p>Your registeration process has completed succesfully.</p>
            </div>

            <p>Hello, <strong>{{ $user->name }}</strong>,</p>

            <p>{{ config('app.name') }} says hi to you.</p>

            <div class="info-box">
                <h3>Account Info:</h3>
                <ul>
                    <li><strong>Name:</strong> {{ $user->name }}</li>
                    <li><strong>Mail Address:</strong> {{ $user->email }}</li>
                    <li><strong>Creation Date:</strong> {{ $user->created_at->format('d.m.Y H:i') }}</li>
                </ul>
            </div>

            <p>Wish you good time with us.</p>

            <p>Kind regards,<br>
            <strong>{{ config('app.name') }} Team</strong></p>
        </div>

        <div class="footer">
            <p>This mail has sent by {{ config('app.name') }} automatically.</p>
            <p>{{ now()->format('Y') }} © {{ config('app.name') }}. Preserved.</p>
        </div>
    </div>
</body>
</html>
