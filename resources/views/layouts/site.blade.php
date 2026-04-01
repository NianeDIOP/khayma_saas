<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Khayma — Gérez votre commerce avec précision')</title>
    <meta name="description" content="@yield('description', 'Khayma est le SaaS africain de gestion commerciale. Restaurants, boutiques, quincailleries, location — tout en un.')">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">

    <style>
        :root {
            --green:        #10B981;
            --green-dark:   #059669;
            --green-light:  #D1FAE5;
            --orange:       #F59E0B;
            --orange-dark:  #D97706;
            --orange-light: #FEF3C7;
            --dark:         #0F172A;
            --dark-2:       #1E293B;
            --dark-3:       #334155;
            --gray:         #64748B;
            --gray-light:   #CBD5E1;
            --light:        #F8FAFC;
            --white:        #FFFFFF;
            --font:         'Inter', sans-serif;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }
        body {
            font-family: var(--font);
            color: var(--dark);
            background: var(--white);
            line-height: 1.6;
            overflow-x: hidden;
        }
        a { text-decoration: none; color: inherit; }

        /* NAVBAR */
        .navbar { position: fixed; top: 0; left: 0; right: 0; z-index: 1000; background: rgba(15,23,42,0.96); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); border-bottom: 1px solid rgba(255,255,255,0.05); height: 68px; display: flex; align-items: center; padding: 0 1.5rem; transition: background 0.3s ease, box-shadow 0.3s ease; }
        .navbar.scrolled { background: rgba(15,23,42,1); box-shadow: 0 4px 32px rgba(0,0,0,0.4); }
        .nav-container { max-width: 1280px; width: 100%; margin: 0 auto; display: flex; align-items: center; justify-content: space-between; }
        .nav-logo { display: flex; align-items: center; gap: 0.6rem; flex-shrink: 0; }
        .nav-logo-img { height: 72px; width: auto; display: block; object-fit: contain; flex-shrink: 0; filter: drop-shadow(0 0 6px rgba(16,185,129,0.25)); }
        .nav-logo-tagline { font-size: 0.62rem; color: rgba(255,255,255,0.5); font-weight: 500; letter-spacing: 0.08em; text-transform: uppercase; border-left: 2px solid var(--green); padding-left: 0.6rem; line-height: 1.3; white-space: nowrap; }
        .nav-links { display: flex; align-items: center; gap: 0; list-style: none; }
        .nav-links a { padding: 0.5rem 1rem; font-size: 0.875rem; font-weight: 500; color: rgba(255,255,255,0.7); transition: color 0.2s; white-space: nowrap; }
        .nav-links a:hover { color: var(--white); }
        .nav-links a.active { color: var(--white); font-weight: 700; }
        .nav-cta { display: flex; align-items: center; gap: 0.5rem; }
        .btn-link { font-size: 0.875rem; font-weight: 500; color: rgba(255,255,255,0.7); padding: 0.5rem 0.75rem; transition: color 0.2s; }
        .btn-link:hover { color: var(--white); }
        .btn-primary { display: inline-flex; align-items: center; gap: 0.45rem; background: var(--green); color: var(--white); font-size: 0.875rem; font-weight: 600; padding: 0.55rem 1.25rem; border: none; cursor: pointer; transition: background 0.2s, transform 0.15s; white-space: nowrap; font-family: var(--font); }
        .btn-primary:hover { background: var(--green-dark); transform: translateY(-1px); }
        .nav-hamburger { display: none; flex-direction: column; gap: 5px; cursor: pointer; padding: 0.5rem; background: none; border: none; }
        .nav-hamburger span { display: block; width: 24px; height: 2px; background: var(--white); transition: all 0.3s ease; }
        .nav-mobile { display: none; position: fixed; top: 68px; left: 0; right: 0; background: var(--dark); border-bottom: 1px solid rgba(255,255,255,0.06); padding: 1rem 1.5rem 1.5rem; z-index: 999; }
        .nav-mobile.open { display: block; }
        .nav-mobile ul { list-style: none; margin-bottom: 1rem; }
        .nav-mobile ul li a { display: block; padding: 0.75rem 0; font-size: 1rem; font-weight: 500; color: rgba(255,255,255,0.8); border-bottom: 1px solid rgba(255,255,255,0.05); }
        .nav-mobile .btn-primary { width: 100%; justify-content: center; margin-top: 0.75rem; padding: 0.875rem; font-size: 1rem; }

        /* SECTION CONTAINER */
        .section-container { max-width: 1280px; margin: 0 auto; padding: 0 1.5rem; }
        .section-header { text-align: center; margin-bottom: 3.5rem; }
        .section-badge { display: inline-flex; align-items: center; font-size: 0.72rem; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; padding: 0.4rem 1rem; margin-bottom: 1rem; }
        .section-badge.green { background: var(--green-light); color: var(--green-dark); }
        .section-badge.orange { background: var(--orange-light); color: var(--orange-dark); }
        .section-title { font-size: clamp(1.75rem, 3.5vw, 2.5rem); font-weight: 800; letter-spacing: -0.04em; line-height: 1.15; color: var(--dark); }
        .section-title .accent-green { color: var(--green); }
        .section-title .accent-orange { color: var(--orange); }
        .section-sub { font-size: 1.05rem; color: var(--gray); max-width: 600px; margin: 0.75rem auto 0; }

        /* PAGE HEADER */
        .page-header { background: var(--dark); padding: 7rem 1.5rem 3.5rem; text-align: center; }
        .page-header .section-title { color: var(--white); }
        .page-header .section-sub { color: rgba(255,255,255,0.5); }

        /* PAGE CONTENT */
        .page-content { padding: 4rem 1.5rem; }
        .page-content .section-container { max-width: 960px; }

        /* CTA BAND */
        .cta-band { background: var(--dark); padding: 4rem 1.5rem; text-align: center; }
        .cta-band h2 { font-size: clamp(1.5rem, 3vw, 2rem); font-weight: 800; color: var(--white); margin-bottom: 0.75rem; }
        .cta-band p { color: rgba(255,255,255,0.5); margin-bottom: 2rem; max-width: 500px; margin-left: auto; margin-right: auto; }
        .btn-cta-white { display: inline-flex; align-items: center; gap: 0.5rem; background: var(--white); color: var(--dark); font-size: 1rem; font-weight: 700; padding: 0.85rem 2rem; border: none; cursor: pointer; font-family: var(--font); transition: transform 0.15s, box-shadow 0.2s; }
        .btn-cta-white:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,0.3); }

        /* FOOTER */
        .footer { background: var(--dark); border-top: 1px solid rgba(255,255,255,0.06); padding: 4rem 0 0; }
        .footer-grid { display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 3rem; margin-bottom: 3rem; }
        .footer-brand img { height: 56px; margin-bottom: 1rem; }
        .footer-brand p { color: rgba(255,255,255,0.45); font-size: 0.9rem; line-height: 1.6; }
        .footer-socials { display: flex; gap: 0.75rem; margin-top: 1.25rem; }
        .footer-socials a { width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.06); color: rgba(255,255,255,0.5); font-size: 0.85rem; transition: all 0.2s; }
        .footer-socials a:hover { background: var(--green); color: var(--white); }
        .footer-col h4 { color: var(--white); font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 1rem; }
        .footer-col ul { list-style: none; }
        .footer-col ul li { margin-bottom: 0.5rem; }
        .footer-col ul li a { color: rgba(255,255,255,0.45); font-size: 0.9rem; transition: color 0.2s; }
        .footer-col ul li a:hover { color: var(--green); }
        .footer-bottom { border-top: 1px solid rgba(255,255,255,0.06); padding: 1.5rem 0; display: flex; align-items: center; justify-content: space-between; }
        .footer-bottom p { color: rgba(255,255,255,0.3); font-size: 0.8rem; }
        .footer-bottom-links { display: flex; gap: 1.5rem; }
        .footer-bottom-links a { color: rgba(255,255,255,0.3); font-size: 0.8rem; transition: color 0.2s; }
        .footer-bottom-links a:hover { color: var(--green); }

        /* FLASH MESSAGES */
        .flash-success { background: var(--green-light); color: var(--green-dark); border: 1px solid var(--green); padding: 1rem 1.5rem; margin-bottom: 2rem; font-weight: 600; font-size: 0.9rem; }

        /* WHATSAPP BUTTON */
        .wa-float { position: fixed; bottom: 1.75rem; left: 1.75rem; z-index: 9000; width: 56px; height: 56px; background: #25D366; color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; box-shadow: 0 4px 16px rgba(37,211,102,0.4); transition: transform 0.2s; }
        .wa-float:hover { transform: scale(1.1); }

        @media (max-width: 768px) {
            .nav-links, .nav-cta { display: none; }
            .nav-hamburger { display: flex; }
            .footer-grid { grid-template-columns: 1fr; gap: 2rem; }
            .footer-bottom { flex-direction: column; gap: 0.75rem; text-align: center; }
        }

        @yield('styles')
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar" id="navbar">
    <div class="nav-container">
        <a href="/" class="nav-logo" aria-label="Khayma — Accueil">
            <img src="/khayma_logo_transparent.png" alt="Khayma" class="nav-logo-img">
            <span class="nav-logo-tagline">Tenter<br>&amp; Estimer</span>
        </a>

        <ul class="nav-links">
            <li><a href="{{ route('home') }}">Accueil</a></li>
            <li><a href="{{ route('site.modules') }}">Modules</a></li>
            <li><a href="{{ route('site.pricing') }}">Tarifs</a></li>
            <li><a href="{{ route('site.faq') }}">FAQ</a></li>
            <li><a href="{{ route('site.blog') }}">Blog</a></li>
            <li><a href="{{ route('site.contact') }}">Contact</a></li>
        </ul>

        <div class="nav-cta">
            @auth
                <a href="{{ route('dashboard') }}" class="btn-primary">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            @else
                <a href="{{ route('login') }}" class="btn-link">Connexion</a>
                <a href="{{ route('register') }}" class="btn-primary">
                    <i class="fas fa-rocket"></i> Commencer
                </a>
            @endauth
        </div>

        <button class="nav-hamburger" id="hamburger" aria-label="Ouvrir le menu" aria-expanded="false">
            <span></span><span></span><span></span>
        </button>
    </div>
</nav>

<div class="nav-mobile" id="mobileMenu" role="navigation" aria-label="Menu mobile">
    <ul>
        <li><a href="{{ route('home') }}">Accueil</a></li>
        <li><a href="{{ route('site.modules') }}">Modules</a></li>
        <li><a href="{{ route('site.pricing') }}">Tarifs</a></li>
        <li><a href="{{ route('site.faq') }}">FAQ</a></li>
        <li><a href="{{ route('site.blog') }}">Blog</a></li>
        <li><a href="{{ route('site.contact') }}">Contact</a></li>
    </ul>
    @auth
        <a href="{{ route('dashboard') }}" class="btn-primary">Dashboard</a>
    @else
        <a href="{{ route('register') }}" class="btn-primary">
            <i class="fas fa-rocket"></i> Commencer gratuitement
        </a>
    @endauth
</div>

@yield('content')

<!-- CTA BAND -->
<section class="cta-band">
    <h2>Prêt à transformer votre commerce ?</h2>
    <p>Rejoignez les entrepreneurs qui gèrent mieux avec Khayma. Essai gratuit, sans engagement.</p>
    <a href="/register" class="btn-cta-white">
        Commencer gratuitement <i class="fas fa-arrow-right"></i>
    </a>
</section>

<!-- FOOTER -->
<footer class="footer">
    <div class="section-container">
        <div class="footer-grid">
            <div class="footer-brand">
                <img src="/khayma_logo_transparent.png" alt="Khayma">
                <p>La plateforme de gestion commerciale pensée pour les entrepreneurs d'Afrique de l'Ouest. Tenter & Estimer.</p>
                <div class="footer-socials">
                    <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" aria-label="Twitter"><i class="fab fa-x-twitter"></i></a>
                    <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
            <div class="footer-col">
                <h4>Produit</h4>
                <ul>
                    <li><a href="{{ route('site.modules') }}">Modules</a></li>
                    <li><a href="{{ route('site.pricing') }}">Tarifs</a></li>
                    <li><a href="{{ route('site.blog') }}">Blog</a></li>
                    <li><a href="{{ route('site.faq') }}">FAQ</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Modules</h4>
                <ul>
                    <li><a href="{{ route('site.modules') }}">Restaurant</a></li>
                    <li><a href="{{ route('site.modules') }}">Boutique</a></li>
                    <li><a href="{{ route('site.modules') }}">Quincaillerie</a></li>
                    <li><a href="{{ route('site.modules') }}">Location</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Support</h4>
                <ul>
                    <li><a href="{{ route('site.contact') }}">Contact</a></li>
                    <li><a href="{{ route('site.faq') }}">Centre d'aide</a></li>
                    <li><a href="{{ route('site.legal', 'terms') }}">CGU</a></li>
                    <li><a href="{{ route('site.legal', 'privacy') }}">Confidentialité</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; {{ date('Y') }} Khayma. Tous droits réservés.</p>
            <div class="footer-bottom-links">
                <a href="{{ route('site.legal', 'terms') }}">CGU</a>
                <a href="{{ route('site.legal', 'privacy') }}">Confidentialité</a>
                <a href="{{ route('site.legal', 'mentions') }}">Mentions légales</a>
            </div>
        </div>
    </div>
</footer>

<!-- WhatsApp floating button -->
<a class="wa-float" href="https://wa.me/221000000000" target="_blank" rel="noopener" aria-label="Contactez-nous sur WhatsApp">
    <i class="fab fa-whatsapp"></i>
</a>

<script>
    const navbar = document.getElementById('navbar');
    window.addEventListener('scroll', () => {
        navbar.classList.toggle('scrolled', window.scrollY > 20);
    }, { passive: true });

    const hamburger = document.getElementById('hamburger');
    const mobileMenu = document.getElementById('mobileMenu');
    hamburger.addEventListener('click', () => {
        const isOpen = mobileMenu.classList.toggle('open');
        hamburger.setAttribute('aria-expanded', String(isOpen));
        const [s1, s2, s3] = hamburger.querySelectorAll('span');
        if (isOpen) {
            s1.style.transform = 'rotate(45deg) translate(5px, 5px)';
            s2.style.opacity = '0';
            s3.style.transform = 'rotate(-45deg) translate(5px, -5px)';
        } else {
            s1.style.transform = '';
            s2.style.opacity = '';
            s3.style.transform = '';
        }
    });
    mobileMenu.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', () => {
            mobileMenu.classList.remove('open');
            hamburger.setAttribute('aria-expanded', 'false');
            const [s1, s2, s3] = hamburger.querySelectorAll('span');
            s1.style.transform = ''; s2.style.opacity = ''; s3.style.transform = '';
        });
    });
</script>
@yield('scripts')
</body>
</html>
