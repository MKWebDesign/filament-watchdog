<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🚨 Emergency Security Lockdown - FilamentWatchdog</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            overflow: hidden;
        }

        .stars {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
        }

        .star {
            position: absolute;
            width: 2px;
            height: 2px;
            background: white;
            border-radius: 50%;
            animation: twinkle 2s infinite;
        }

        @keyframes twinkle {
            0%, 100% { opacity: 0.3; }
            50% { opacity: 1; }
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
            text-align: center;
            position: relative;
            z-index: 10;
        }

        .lockdown-icon {
            font-size: 5rem;
            margin-bottom: 1.5rem;
            animation: pulse 2s infinite;
            text-shadow: 0 0 20px rgba(255, 255, 255, 0.5);
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .main-title {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
            line-height: 1.2;
        }

        .subtitle {
            font-size: 1.25rem;
            font-weight: 400;
            margin-bottom: 2rem;
            opacity: 0.9;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .status-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 16px;
            padding: 2rem;
            margin: 2rem 0;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .status-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #ffd700;
        }

        .status-list {
            list-style: none;
            text-align: left;
            max-width: 500px;
            margin: 0 auto;
        }

        .status-list li {
            padding: 0.5rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
        }

        .status-list li:last-child {
            border-bottom: none;
        }

        .status-icon {
            margin-right: 0.75rem;
            font-size: 1.2rem;
        }

        .admin-section {
            background: rgba(255, 193, 7, 0.1);
            border: 1px solid rgba(255, 193, 7, 0.3);
            border-radius: 12px;
            padding: 1.5rem;
            margin-top: 2rem;
        }

        .admin-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #ffd700;
        }

        .admin-text {
            font-size: 0.95rem;
            opacity: 0.9;
        }

        .timeline {
            margin-top: 2rem;
            font-size: 0.9rem;
            opacity: 0.8;
        }

        .footer {
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            font-size: 0.9rem;
            opacity: 0.7;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            background: linear-gradient(45deg, #ffd700, #ff6b6b);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        @media (max-width: 768px) {
            .main-title {
                font-size: 2rem;
            }

            .lockdown-icon {
                font-size: 3rem;
            }

            .container {
                padding: 1rem;
            }

            .status-card {
                padding: 1.5rem;
            }
        }

        .security-pattern {
            position: absolute;
            width: 100%;
            height: 100%;
            opacity: 0.03;
            background-image:
                    radial-gradient(circle at 25% 25%, #fff 2px, transparent 2px),
                    radial-gradient(circle at 75% 75%, #fff 2px, transparent 2px);
            background-size: 50px 50px;
            animation: drift 20s linear infinite;
        }

        @keyframes drift {
            0% { transform: translate(0, 0); }
            100% { transform: translate(50px, 50px); }
        }
    </style>
</head>
<body>
<div class="stars" id="stars"></div>
<div class="security-pattern"></div>

<div class="container">
    <div class="lockdown-icon">🚨</div>

    <h1 class="main-title">Emergency Security Lockdown</h1>

    <p class="subtitle">
        Our security system has temporarily restricted access to protect your data.
        We're working to resolve this situation as quickly as possible.
    </p>

    <div class="status-card">
        <h2 class="status-title">🛡️ Security Measures Active</h2>
        <ul class="status-list">
            <li>
                <span class="status-icon">🔒</span>
                <span>Site access restricted to authorized administrators</span>
            </li>
            <li>
                <span class="status-icon">🧹</span>
                <span>All user sessions have been cleared</span>
            </li>
            <li>
                <span class="status-icon">💾</span>
                <span>Emergency backup created and secured</span>
            </li>
            <li>
                <span class="status-icon">📧</span>
                <span>Administrators have been notified</span>
            </li>
            <li>
                <span class="status-icon">🔍</span>
                <span>Security analysis in progress</span>
            </li>
        </ul>
    </div>

    <div class="admin-section">
        <h3 class="admin-title">👨‍💻 Administrator Access</h3>
        <p class="admin-text">
            If you are a system administrator, check your email for the emergency access link
            or use the secret key provided during lockdown activation.
        </p>
    </div>

    <div class="timeline">
        <p><strong>Lockdown activated:</strong> <span id="lockdown-time"></span></p>
        <p><strong>Expected resolution:</strong> Within 1-2 hours</p>
    </div>

    <div class="footer">
        <div class="logo">🐕 FilamentWatchdog</div>
        <p>Advanced Security Monitoring & Protection</p>
        <p>For urgent matters, contact your system administrator directly.</p>
    </div>
</div>

<script>
    // Create animated stars
    function createStars() {
        const starsContainer = document.getElementById('stars');
        const numberOfStars = 50;

        for (let i = 0; i < numberOfStars; i++) {
            const star = document.createElement('div');
            star.className = 'star';
            star.style.left = Math.random() * 100 + '%';
            star.style.top = Math.random() * 100 + '%';
            star.style.animationDelay = Math.random() * 2 + 's';
            starsContainer.appendChild(star);
        }
    }

    // Set lockdown time
    function setLockdownTime() {
        const now = new Date();
        const timeString = now.toLocaleString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            timeZoneName: 'short'
        });
        document.getElementById('lockdown-time').textContent = timeString;
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        createStars();
        setLockdownTime();
    });

    // Add subtle page refresh every 5 minutes to check if lockdown is lifted
    setTimeout(function() {
        window.location.reload();
    }, 300000); // 5 minutes
</script>
</body>
</html>