<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Submitted — New Path</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/global.css">
    <style>
        body {
            font-family: var(--font-family-primary);
            background: var(--color-bg-light-green);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 32px 16px;
            height: auto;
        }

        .sc-card {
            background: #fff;
            border-radius: var(--radius-sm);
            border: 1px solid rgba(61, 168, 228, 0.14);
            max-width: 560px;
            width: 100%;
            padding: 48px 40px;
            text-align: center;
        }

        .sc-icon-wrap {
            width: 76px; height: 76px;
            border-radius: 50%;
            background: var(--color-primary-light);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
        }
        .sc-icon-wrap svg {
            width: 36px; height: 36px;
            stroke: var(--color-primary-dark);
        }

        .sc-title {
            font-size: 1.6rem;
            font-weight: var(--font-weight-bold);
            color: var(--color-accent);
            margin-bottom: 12px;
        }

        .sc-message {
            font-size: var(--font-size-base);
            color: var(--color-text-muted);
            line-height: 1.7;
            margin-bottom: 32px;
        }

        .sc-steps {
            background: var(--color-bg-light-green);
            border-radius: var(--radius-sm);
            border: 1px solid rgba(61, 168, 228, 0.12);
            padding: 24px;
            text-align: left;
            margin-bottom: 32px;
        }
        .sc-steps h3 {
            font-size: var(--font-size-sm);
            font-weight: var(--font-weight-semibold);
            color: var(--color-accent);
            margin-bottom: 16px;
            text-transform: uppercase;
            letter-spacing: var(--letter-spacing-wider);
        }
        .sc-steps ol {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 12px;
            padding: 0;
        }
        .sc-steps li {
            display: flex;
            gap: 12px;
            align-items: flex-start;
            font-size: var(--font-size-sm);
            color: var(--color-text-muted);
            line-height: 1.5;
        }
        .sc-step-num {
            flex-shrink: 0;
            width: 22px; height: 22px;
            border-radius: 50%;
            background: var(--color-primary);
            color: #fff;
            font-size: 11px;
            font-weight: var(--font-weight-bold);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 1px;
        }
        .sc-steps li strong { color: var(--color-text-primary); }

        .sc-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 13px 32px;
            background: var(--color-primary);
            color: #fff;
            border-radius: var(--radius-pill);
            font-family: var(--font-family-primary);
            font-size: var(--font-size-base);
            font-weight: var(--font-weight-semibold);
            text-decoration: none;
            transition: background .2s, transform .15s;
        }
        .sc-btn:hover {
            background: var(--color-primary-dark);
            transform: translateY(-2px);
        }
        .sc-btn svg { width: 17px; height: 17px; }

        .sc-logo {
            display: flex;
            align-items: center;
            gap: 8px;
            justify-content: center;
            margin-bottom: 32px;
            text-decoration: none;
        }
        .sc-logo img { width: 30px; }
        .sc-logo span {
            font-size: .9rem;
            font-weight: var(--font-weight-bold);
            color: var(--color-accent);
            line-height: 1.2;
        }
    </style>
</head>
<body class="theme-counselor">

<a href="/" class="sc-logo">
    <img src="/assets/img/logo.svg" alt="New Path">
    <span>New<br>Path</span>
</a>

<div class="sc-card">
    <div class="sc-icon-wrap">
        <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="20 6 9 17 4 12"/>
        </svg>
    </div>

    <h1 class="sc-title">Application Submitted!</h1>
    <p class="sc-message">
        Thank you for your interest in joining New Path. Your application is now under review
        by our administrative team and you'll hear from us soon.
    </p>

    <div class="sc-steps">
        <h3>What happens next</h3>
        <ol>
            <li>
                <span class="sc-step-num">1</span>
                <span><strong>Review</strong> — Our team carefully reviews your credentials and application</span>
            </li>
            <li>
                <span class="sc-step-num">2</span>
                <span><strong>Email update</strong> — You'll receive a notification within 3–5 business days</span>
            </li>
            <li>
                <span class="sc-step-num">3</span>
                <span><strong>Interview</strong> — If shortlisted, we'll schedule a brief interview</span>
            </li>
            <li>
                <span class="sc-step-num">4</span>
                <span><strong>Account setup</strong> — Upon approval, your counselor account is created with login credentials</span>
            </li>
        </ol>
    </div>

    <a href="/" class="sc-btn">
        Return to Home
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
            <polyline points="9 22 9 12 15 12 15 22"/>
        </svg>
    </a>
</div>

</body>
</html>
