<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Path — Your Recovery, Supported</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="/assets/css/global.css">
    <link rel="stylesheet" href="/assets/css/landing.css">
</head>
<body>

<!-- ============================================================
     NAVBAR
     ============================================================ -->
<nav class="lp-nav" id="lpNav">
    <div class="lp-nav__logo">
        <img src="/assets/img/logo.svg" alt="New Path Logo">
        <span class="lp-nav__logo-text">New<br>Path</span>
    </div>

    <ul class="lp-nav__links">
        <li><a href="#features">Features</a></li>
        <li><a href="#how-it-works">How It Works</a></li>
        <li><a href="#counselors">For Counselors</a></li>
    </ul>

    <div class="lp-nav__cta">
        <a href="/auth/login" class="lp-login-link">Log in</a>
        <a href="/auth/onboarding/step1" class="btn-primary">Get Started</a>
    </div>
</nav>

<!-- ============================================================
     HERO
     ============================================================ -->
<section class="lp-hero">
    <div class="container">
        <div class="lp-hero__inner">

            <div class="lp-hero__text">
                <div class="lp-hero__tag">
                    <span class="lp-hero__tag-dot"></span>
                    Professional Recovery Support
                </div>

                <h1 class="lp-hero__title">
                    Your path to recovery<br>
                    starts <span>here</span>
                </h1>

                <p class="lp-hero__desc">
                    Connect with certified counselors, track your sobriety milestones,
                    and build a stronger future — all in one private, supportive space.
                </p>

                <div class="lp-hero__actions">
                    <a href="/auth/onboarding/step1" class="btn-primary">Start Your Journey</a>
                    <a href="#how-it-works" class="btn-outline">See How It Works</a>
                </div>
            </div>

            <div class="lp-hero__image">
                <img src="/assets/img/landing-image.svg" alt="Recovery illustration">
            </div>

        </div>
    </div>
</section>

<!-- ============================================================
     STATS STRIP
     ============================================================ -->
<div class="lp-stats">
    <div class="container">
        <div class="lp-stats__inner">
            <div class="lp-stat">
                <div class="lp-stat__num">500+</div>
                <div class="lp-stat__label">Active Users</div>
            </div>
            <div class="lp-stat">
                <div class="lp-stat__num">50+</div>
                <div class="lp-stat__label">Certified Counselors</div>
            </div>
            <div class="lp-stat">
                <div class="lp-stat__num">10k+</div>
                <div class="lp-stat__label">Sessions Completed</div>
            </div>
            <div class="lp-stat">
                <div class="lp-stat__num">95%</div>
                <div class="lp-stat__label">Satisfaction Rate</div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================
     FEATURES
     ============================================================ -->
<section class="lp-features" id="features">
    <div class="container">
        <div class="lp-features__header">
            <span class="section-label">What We Offer</span>
            <h2 class="section-title">Everything you need on your recovery journey</h2>
            <p class="section-desc">
                New Path brings together the tools, professionals, and community
                you need to stay on track — day by day.
            </p>
        </div>

        <div class="lp-features__grid">

            <!-- Counseling Sessions -->
            <div class="lp-feature-card">
                <div class="lp-feature-card__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                </div>
                <h3 class="lp-feature-card__title">1-on-1 Counseling</h3>
                <p class="lp-feature-card__desc">
                    Book private video sessions with certified addiction and recovery counselors at a time that works for you.
                </p>
            </div>

            <!-- Sobriety Tracking -->
            <div class="lp-feature-card">
                <div class="lp-feature-card__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                    </svg>
                </div>
                <h3 class="lp-feature-card__title">Sobriety Tracking</h3>
                <p class="lp-feature-card__desc">
                    Log daily check-ins, track streaks, and celebrate milestones. Visual progress keeps motivation high.
                </p>
            </div>

            <!-- Recovery Plans -->
            <div class="lp-feature-card">
                <div class="lp-feature-card__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <line x1="16" y1="13" x2="8" y2="13"/>
                        <line x1="16" y1="17" x2="8" y2="17"/>
                        <polyline points="10 9 9 9 8 9"/>
                    </svg>
                </div>
                <h3 class="lp-feature-card__title">Personalised Plans</h3>
                <p class="lp-feature-card__desc">
                    Your counselor builds a structured recovery plan tailored to your goals, with tasks you complete at your own pace.
                </p>
            </div>

            <!-- Journaling -->
            <div class="lp-feature-card">
                <div class="lp-feature-card__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 20h9"/>
                        <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
                    </svg>
                </div>
                <h3 class="lp-feature-card__title">Private Journaling</h3>
                <p class="lp-feature-card__desc">
                    Write freely in a secure, encrypted journal. Reflect on your feelings and share entries with your counselor if you choose.
                </p>
            </div>

            <!-- Community -->
            <div class="lp-feature-card">
                <div class="lp-feature-card__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                    </svg>
                </div>
                <h3 class="lp-feature-card__title">Community Support</h3>
                <p class="lp-feature-card__desc">
                    Join support groups, share stories, and draw strength from others who understand exactly what you're going through.
                </p>
            </div>

            <!-- Achievements -->
            <div class="lp-feature-card">
                <div class="lp-feature-card__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="8" r="7"/>
                        <polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/>
                    </svg>
                </div>
                <h3 class="lp-feature-card__title">Achievements & Badges</h3>
                <p class="lp-feature-card__desc">
                    Earn recognition for milestones — first check-in, 7-day streak, completed tasks. Progress you can see and celebrate.
                </p>
            </div>

        </div>
    </div>
</section>

<!-- ============================================================
     HOW IT WORKS
     ============================================================ -->
<section class="lp-how" id="how-it-works">
    <div class="container">
        <div class="lp-how__header">
            <span class="section-label">How It Works</span>
            <h2 class="section-title">Up and running in minutes</h2>
            <p class="section-desc">No paperwork, no waiting rooms. Your support system is ready when you are.</p>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:48px;align-items:center;">

            <div class="lp-how__steps">
                <div class="lp-step">
                    <div class="lp-step__num">1</div>
                    <div>
                        <div class="lp-step__title">Create your account</div>
                        <p class="lp-step__desc">A quick 5-step onboarding collects only what your counselor needs to help you effectively.</p>
                    </div>
                </div>
                <div class="lp-step">
                    <div class="lp-step__num">2</div>
                    <div>
                        <div class="lp-step__title">Choose your counselor</div>
                        <p class="lp-step__desc">Browse verified counselors by specialty, availability, and consultation fee. Pick who fits.</p>
                    </div>
                </div>
                <div class="lp-step">
                    <div class="lp-step__num">3</div>
                    <div>
                        <div class="lp-step__title">Book a session</div>
                        <p class="lp-step__desc">Select a time slot, complete secure payment, and receive an instant Google Meet link in your inbox.</p>
                    </div>
                </div>
                <div class="lp-step">
                    <div class="lp-step__num">4</div>
                    <div>
                        <div class="lp-step__title">Build your recovery</div>
                        <p class="lp-step__desc">Follow your personalised plan, log daily check-ins, journal, and watch your streaks grow.</p>
                    </div>
                </div>
            </div>

            <div style="display:flex;justify-content:center;">
                <img src="/assets/img/main-content-head.svg" alt="App walkthrough" style="max-width:380px;width:100%;">
            </div>

        </div>
    </div>
</section>

<!-- ============================================================
     COUNSELORS CTA
     ============================================================ -->
<section class="lp-counselors" id="counselors">
    <div class="container">
        <div class="lp-counselors__inner">

            <div class="lp-counselors__text">
                <span class="section-label">For Counselors</span>
                <h2 class="section-title">Grow your practice.<br>Change lives.</h2>
                <p class="section-desc">
                    Join our network of certified recovery counselors. Manage your schedule,
                    conduct video sessions, and track client progress — all from one dashboard.
                </p>
                <div class="lp-counselors__actions">
                    <a href="/auth/login/counselor" class="btn-primary">Counselor Login</a>
                    <a href="/auth/login/counselor" class="btn-outline">Learn More</a>
                </div>
            </div>

            <div style="display:flex;justify-content:center;flex:1;position:relative;z-index:1;">
                <img src="/assets/img/counselor-header.svg" alt="Counselor dashboard" style="max-width:340px;width:100%;">
            </div>

        </div>
    </div>
</section>

<!-- ============================================================
     FOOTER
     ============================================================ -->
<footer class="lp-footer">
    <div class="container">
        <div class="lp-footer__inner">

            <div class="lp-footer__logo">
                <img src="/assets/img/logo.svg" alt="New Path">
                New Path
            </div>

            <p class="lp-footer__copy">&copy; <?= date('Y') ?> New Path. All rights reserved.</p>

            <ul class="lp-footer__links">
                <li><a href="/auth/login">Login</a></li>
                <li><a href="/auth/onboarding/step1">Sign Up</a></li>
                <li><a href="/auth/login/counselor">Counselors</a></li>
                <li><a href="/auth/login/admin">Admin</a></li>
            </ul>

        </div>
    </div>
</footer>

<script>
// Navbar scroll shadow
window.addEventListener('scroll', function () {
    document.getElementById('lpNav').classList.toggle('scrolled', window.scrollY > 10);
});
</script>

</body>
</html>
