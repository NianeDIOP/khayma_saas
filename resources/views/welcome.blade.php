<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Khayma — Gérez votre commerce avec précision</title>
    <meta name="description" content="Khayma est le SaaS africain de gestion commerciale. Restaurants, boutiques, quincailleries, location — tout en un.">

    <!-- Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">

    <style>
        /* ================================================
           VARIABLES — Khayma Design System
           ================================================ */
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

        /* ================================================
           RESET & BASE
           ================================================ */
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html { scroll-behavior: smooth; }

        body {
            font-family: var(--font);
            color: var(--dark);
            background: var(--white);
            line-height: 1.6;
            overflow-x: hidden;
        }

        a { text-decoration: none; color: inherit; }

        /* ================================================
           NAVBAR
           ================================================ */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: rgba(15, 23, 42, 0.96);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            height: 68px;
            display: flex;
            align-items: center;
            padding: 0 1.5rem;
            transition: background 0.3s ease, box-shadow 0.3s ease;
        }

        .navbar.scrolled {
            background: rgba(15, 23, 42, 1);
            box-shadow: 0 4px 32px rgba(0, 0, 0, 0.4);
        }

        .nav-container {
            max-width: 1280px;
            width: 100%;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        /* --- Logo --- */
        .nav-logo {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            flex-shrink: 0;
        }

        .nav-logo-img {
            height: 72px;
            width: auto;
            display: block;
            object-fit: contain;
            flex-shrink: 0;
            filter: drop-shadow(0 0 6px rgba(16,185,129,0.25));
        }

        .nav-logo-tagline {
            font-size: 0.62rem;
            color: rgba(255,255,255,0.5);
            font-weight: 500;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            border-left: 2px solid var(--green);
            padding-left: 0.6rem;
            line-height: 1.3;
            white-space: nowrap;
        }

        /* --- Nav links --- */
        .nav-links {
            display: flex;
            align-items: center;
            gap: 0;
            list-style: none;
        }

        .nav-links a {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.7);
            transition: color 0.2s;
            white-space: nowrap;
        }

        .nav-links a:hover { color: var(--white); }
        .nav-links a.active { color: var(--white); font-weight: 700; }
        .nav-links a.active::after {
            content: '';
            display: block;
            height: 2px;
            background: var(--green);
            margin-top: 2px;
        }

        /* Bouton flottant workflow */
        #btn-workflow {
            position: fixed;
            bottom: 1.75rem;
            right: 1.75rem;
            z-index: 9000;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.65rem 1.1rem;
            background: var(--dark-2);
            color: var(--green);
            border: 1px solid var(--green);
            font-family: var(--font);
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-decoration: none;
            box-shadow: 0 4px 20px rgba(16,185,129,0.2);
            transition: all 0.25s ease;
        }
        #btn-workflow:hover {
            background: var(--green);
            color: var(--white);
            box-shadow: 0 6px 28px rgba(16,185,129,0.4);
            transform: translateY(-2px);
        }
        #btn-workflow i { font-size: 0.85rem; }
            position: fixed;
            top: 0; left: 0;
            height: 3px;
            width: 0%;
            background: var(--green);
            z-index: 9999;
            transition: width 0.1s linear;
        }

        /* --- CTA buttons --- */
        .nav-cta {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-link {
            font-size: 0.875rem;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.7);
            padding: 0.5rem 0.75rem;
            transition: color 0.2s;
        }

        .btn-link:hover { color: var(--white); }

        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            background: var(--green);
            color: var(--white);
            font-size: 0.875rem;
            font-weight: 600;
            padding: 0.55rem 1.25rem;
            border: none;
            cursor: pointer;
            transition: background 0.2s, transform 0.15s;
            white-space: nowrap;
            font-family: var(--font);
        }

        .btn-primary:hover {
            background: var(--green-dark);
            transform: translateY(-1px);
        }

        /* --- Hamburger --- */
        .nav-hamburger {
            display: none;
            flex-direction: column;
            gap: 5px;
            cursor: pointer;
            padding: 0.5rem;
            background: none;
            border: none;
        }

        .nav-hamburger span {
            display: block;
            width: 24px;
            height: 2px;
            background: var(--white);
            transition: all 0.3s ease;
        }

        /* --- Mobile menu --- */
        .nav-mobile {
            display: none;
            position: fixed;
            top: 68px;
            left: 0;
            right: 0;
            background: var(--dark);
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
            padding: 1rem 1.5rem 1.5rem;
            z-index: 999;
        }

        .nav-mobile.open { display: block; }

        .nav-mobile ul { list-style: none; margin-bottom: 1rem; }

        .nav-mobile ul li a {
            display: block;
            padding: 0.75rem 0;
            font-size: 1rem;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.8);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .nav-mobile .btn-primary {
            width: 100%;
            justify-content: center;
            margin-top: 0.75rem;
            padding: 0.875rem;
            font-size: 1rem;
        }

        /* ================================================
           PLACEHOLDER (sera remplacé section par section)
           ================================================ */
        /* ================================================
           HERO
           ================================================ */
        .hero {
            position: relative;
            min-height: 100vh;
            padding-top: 68px;
            background: var(--dark);
            display: flex;
            align-items: center;
            overflow: hidden;
        }

        .hero::after {
            content: '';
            position: absolute;
            inset: 0;
            background: url('/images/hero-bg.webp.png') center/cover no-repeat;
            opacity: 0.07;
            pointer-events: none;
            z-index: 0;
            transform: translateY(var(--parallax-y, 0px));
            scale: 1.15;
        }

        .hero > * { position: relative; z-index: 1; }

        /* Grille décorative en fond */
        .hero-grid {
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(16,185,129,0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(16,185,129,0.04) 1px, transparent 1px);
            background-size: 60px 60px;
            pointer-events: none;
        }

        /* Lueur verte en haut à droite */
        .hero-glow {
            position: absolute;
            top: -100px;
            right: -100px;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(16,185,129,0.12) 0%, transparent 70%);
            pointer-events: none;
        }

        /* Lueur orange en bas à gauche */
        .hero-glow-2 {
            position: absolute;
            bottom: -80px;
            left: -80px;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(245,158,11,0.08) 0%, transparent 70%);
            pointer-events: none;
        }

        .hero-container {
            max-width: 1280px;
            width: 100%;
            margin: 0 auto;
            padding: 5rem 1.5rem 4rem;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
            position: relative;
            z-index: 1;
        }

        /* --- Côté texte --- */
        .hero-content { display: flex; flex-direction: column; gap: 0; }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.72rem;
            color: var(--green);
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            background: rgba(16, 185, 129, 0.08);
            border: 1px solid rgba(16, 185, 129, 0.25);
            padding: 0.4rem 1rem;
            width: fit-content;
            margin-bottom: 1.75rem;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeUp 0.6s ease 0.1s forwards;
        }

        .hero-badge-dot {
            width: 7px;
            height: 7px;
            background: var(--green);
            display: inline-block;
            animation: pulse-dot 2s infinite;
        }

        @keyframes pulse-dot {
            0%, 100% { opacity: 1; transform: scale(1); }
            50%       { opacity: 0.4; transform: scale(0.7); }
        }

        .hero-title {
            font-size: clamp(2.5rem, 5vw, 4rem);
            font-weight: 900;
            color: var(--white);
            letter-spacing: -0.04em;
            line-height: 1.1;
            margin-bottom: 1.5rem;
            opacity: 0;
            transform: translateY(24px);
            animation: fadeUp 0.7s ease 0.25s forwards;
        }

        .hero-title .accent-green { color: var(--green); }
        .hero-title .accent-orange { color: var(--orange); }

        .hero-sub {
            font-size: 1.1rem;
            color: rgba(255,255,255,0.6);
            line-height: 1.7;
            max-width: 480px;
            margin-bottom: 2.5rem;
            opacity: 0;
            transform: translateY(24px);
            animation: fadeUp 0.7s ease 0.4s forwards;
        }

        /* Étymologie Khayma */
        .hero-etymology {
            display: flex;
            gap: 1rem;
            margin-bottom: 2.5rem;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeUp 0.7s ease 0.55s forwards;
        }

        .etym-item {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            padding: 0.6rem 1rem;
            flex: 1;
        }

        .etym-icon {
            width: 34px;
            height: 34px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .etym-icon.green { background: rgba(16,185,129,0.15); color: var(--green); }
        .etym-icon.orange { background: rgba(245,158,11,0.15); color: var(--orange); }

        .etym-text { display: flex; flex-direction: column; line-height: 1.2; }
        .etym-word { font-size: 0.95rem; font-weight: 700; color: var(--white); }
        .etym-def  { font-size: 0.72rem; color: rgba(255,255,255,0.45); letter-spacing: 0.05em; }

        /* Boutons hero */
        .hero-actions {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeUp 0.7s ease 0.7s forwards;
        }

        .btn-hero-primary {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            background: var(--green);
            color: var(--white);
            font-size: 1rem;
            font-weight: 700;
            padding: 0.9rem 2rem;
            border: none;
            cursor: pointer;
            font-family: var(--font);
            transition: background 0.2s, transform 0.15s, box-shadow 0.2s;
            letter-spacing: -0.01em;
        }

        .btn-hero-primary:hover {
            background: var(--green-dark);
            transform: translateY(-2px);
            box-shadow: 0 12px 32px rgba(16,185,129,0.35);
        }

        .btn-hero-outline {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            background: transparent;
            color: rgba(255,255,255,0.8);
            font-size: 1rem;
            font-weight: 600;
            padding: 0.9rem 1.75rem;
            border: 1px solid rgba(255,255,255,0.2);
            cursor: pointer;
            font-family: var(--font);
            transition: all 0.2s;
            letter-spacing: -0.01em;
        }

        .btn-hero-outline:hover {
            border-color: rgba(255,255,255,0.5);
            color: var(--white);
            transform: translateY(-2px);
        }

        /* Stats hero */
        .hero-stats {
            display: flex;
            gap: 2rem;
            padding-top: 2.5rem;
            border-top: 1px solid rgba(255,255,255,0.07);
            margin-top: 2.5rem;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeUp 0.7s ease 0.9s forwards;
        }

        .stat-item { display: flex; flex-direction: column; }

        .stat-number {
            font-size: 1.75rem;
            font-weight: 900;
            color: var(--white);
            letter-spacing: -0.04em;
            line-height: 1;
        }

        .stat-number span { color: var(--green); }

        .stat-label {
            font-size: 0.72rem;
            color: rgba(255,255,255,0.4);
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-top: 0.25rem;
        }

        /* --- Côté visuel (logo + SVG) --- */
        .hero-visual {
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            opacity: 0;
            transform: translateX(30px);
            animation: fadeLeft 0.9s ease 0.5s forwards;
        }

        .hero-visual-inner {
            position: relative;
            width: 100%;
            max-width: 480px;
        }

        /* Cercle décoratif derrière le logo */
        .hero-circle {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 380px;
            height: 380px;
            border: 1px solid rgba(16,185,129,0.15);
        }

        .hero-circle-2 {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 280px;
            height: 280px;
            border: 1px solid rgba(16,185,129,0.08);
        }

        /* Carte flottante — stat en haut */
        .hero-card-top {
            position: absolute;
            top: 5%;
            right: -5%;
            background: var(--dark-2);
            border: 1px solid rgba(255,255,255,0.08);
            padding: 0.75rem 1rem;
            display: flex;
            align-items: center;
            gap: 0.7rem;
            animation: float 4s ease-in-out infinite;
        }

        /* Carte flottante — stat en bas */
        .hero-card-bot {
            position: absolute;
            bottom: 5%;
            left: -5%;
            background: var(--dark-2);
            border: 1px solid rgba(255,255,255,0.08);
            padding: 0.75rem 1rem;
            display: flex;
            align-items: center;
            gap: 0.7rem;
            animation: float 4s ease-in-out 2s infinite;
        }

        .hero-card-icon {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }

        .hero-card-icon.green  { background: rgba(16,185,129,0.15); color: var(--green); }
        .hero-card-icon.orange { background: rgba(245,158,11,0.15);  color: var(--orange); }

        .hero-card-info { display: flex; flex-direction: column; line-height: 1.2; }
        .hero-card-val  { font-size: 1rem; font-weight: 700; color: var(--white); }
        .hero-card-lbl  { font-size: 0.68rem; color: rgba(255,255,255,0.4); text-transform: uppercase; letter-spacing: 0.06em; }

        /* Logo principal */
        .hero-logo-img {
            width: 100%;
            max-width: 340px;
            display: block;
            margin: 0 auto;
            position: relative;
            z-index: 2;
            filter: drop-shadow(0 0 40px rgba(16,185,129,0.2));
            animation: float 6s ease-in-out infinite;
        }

        /* ================================================
           KEYFRAMES
           ================================================ */
        @keyframes fadeUp {
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeLeft {
            to { opacity: 1; transform: translateX(0); }
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50%       { transform: translateY(-14px); }
        }

        /* ================================================
           RESPONSIVE
           ================================================ */
        @media (max-width: 768px) {
            .nav-links,
            .nav-cta       { display: none; }
            .nav-hamburger { display: flex; }

            .hero-container {
                grid-template-columns: 1fr;
                gap: 2rem;
                padding: 3rem 1.25rem 2.5rem;
                text-align: center;
            }

            .hero-content { align-items: center; }

            .hero-sub { max-width: 100%; }

            .hero-etymology { flex-direction: column; gap: 0.75rem; }

            .hero-actions { justify-content: center; }

            .hero-stats { justify-content: center; flex-wrap: wrap; gap: 1.5rem; }

            .hero-visual { order: -1; }

            .hero-card-top,
            .hero-card-bot { display: none; }

            .hero-circle,
            .hero-circle-2 { display: none; }
        }

        /* ================================================
           SECTION FEATURES
           ================================================ */
        .features {
            padding: 6rem 1.5rem;
            background: var(--light);
            position: relative;
            overflow: hidden;
        }

        .features::after {
            content: '';
            position: absolute;
            inset: 0;
            background: url('/images/features-bg.webp.png') center/cover no-repeat;
            opacity: 0.04;
            pointer-events: none;
            z-index: 0;
        }

        .features > * { position: relative; z-index: 1; }

        .section-container {
            max-width: 1280px;
            margin: 0 auto;
        }

        .section-header {
            text-align: center;
            margin-bottom: 4rem;
        }

        .section-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            padding: 0.4rem 1rem;
            margin-bottom: 1.25rem;
        }

        .section-badge.green {
            color: var(--green-dark);
            background: var(--green-light);
            border: 1px solid rgba(16,185,129,0.3);
        }

        .section-badge.orange {
            color: var(--orange-dark);
            background: var(--orange-light);
            border: 1px solid rgba(245,158,11,0.3);
        }

        .section-title {
            font-size: clamp(1.75rem, 3.5vw, 2.75rem);
            font-weight: 900;
            color: var(--dark);
            letter-spacing: -0.04em;
            line-height: 1.15;
            margin-bottom: 1rem;
        }

        .section-sub {
            font-size: 1.05rem;
            color: var(--gray);
            max-width: 560px;
            margin: 0 auto;
            line-height: 1.7;
        }

        /* Grille de features */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5px;
            background: var(--gray-light);
            border: 1.5px solid var(--gray-light);
        }

        .feature-card {
            background: var(--white);
            padding: 2.5rem 2rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            transition: transform 0.25s ease, box-shadow 0.25s ease;
            cursor: default;
            opacity: 0;
            transform: translateY(30px);
        }

        .feature-card.visible {
            opacity: 1;
            transform: translateY(0);
            transition: opacity 0.55s ease, transform 0.55s ease, box-shadow 0.25s ease;
        }

        .feature-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 48px rgba(15,23,42,0.1);
            z-index: 1;
        }

        .feature-icon {
            width: 52px;
            height: 52px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            flex-shrink: 0;
        }

        .feature-icon.green  { background: var(--green-light);  color: var(--green-dark); }
        .feature-icon.orange { background: var(--orange-light); color: var(--orange-dark); }
        .feature-icon.dark   { background: rgba(15,23,42,0.07); color: var(--dark); }

        .feature-title {
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--dark);
            letter-spacing: -0.02em;
        }

        .feature-desc {
            font-size: 0.9rem;
            color: var(--gray);
            line-height: 1.65;
        }

        .feature-link {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--green-dark);
            margin-top: auto;
            transition: gap 0.2s;
        }

        .feature-link:hover { gap: 0.7rem; }

        /* Bande avantages chiffrés */
        .features-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0;
            margin-top: 4rem;
            background: var(--dark);
        }

        .feat-stat {
            padding: 2.5rem 2rem;
            border-right: 1px solid rgba(255,255,255,0.07);
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
            align-items: flex-start;
        }

        .feat-stat:last-child { border-right: none; }

        .feat-stat-number {
            font-size: 2.25rem;
            font-weight: 900;
            color: var(--white);
            letter-spacing: -0.05em;
            line-height: 1;
        }

        .feat-stat-number .accent { color: var(--green); }

        .feat-stat-label {
            font-size: 0.8rem;
            color: rgba(255,255,255,0.45);
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .feat-stat-desc {
            font-size: 0.875rem;
            color: rgba(255,255,255,0.65);
            line-height: 1.5;
            margin-top: 0.25rem;
        }

        @media (max-width: 1024px) {
            .features-grid { grid-template-columns: repeat(2, 1fr); }
            .features-stats { grid-template-columns: repeat(2, 1fr); }
            .feat-stat:nth-child(2) { border-right: none; }
        }

        @media (max-width: 640px) {
            .features-grid { grid-template-columns: 1fr; }
            .features-stats { grid-template-columns: 1fr; }
            .feat-stat { border-right: none; border-bottom: 1px solid rgba(255,255,255,0.07); }
        }

        /* ================================================
           SECTION MODULES
           ================================================ */
        .modules {
            padding: 6rem 1.5rem;
            background: var(--dark);
            position: relative;
            overflow: hidden;
        }

        .modules::after {
            content: '';
            position: absolute;
            inset: 0;
            background: url('/images/modules-bg.webp.png') center/cover no-repeat;
            opacity: 0.08;
            pointer-events: none;
            z-index: 0;
        }

        .modules > * { position: relative; z-index: 1; }

        .modules .section-title  { color: var(--white); }
        .modules .section-sub    { color: rgba(255,255,255,0.5); }

        /* Onglets */
        .module-tabs {
            display: flex;
            gap: 0;
            margin-bottom: 3rem;
            border-bottom: 2px solid rgba(255,255,255,0.08);
            overflow-x: auto;
            scrollbar-width: none;
        }
        .module-tabs::-webkit-scrollbar { display: none; }

        .module-tab {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            padding: 1rem 1.75rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: rgba(255,255,255,0.45);
            cursor: pointer;
            border-bottom: 2px solid transparent;
            margin-bottom: -2px;
            white-space: nowrap;
            transition: color 0.2s, border-color 0.2s;
            background: none;
            border-top: none;
            border-left: none;
            border-right: none;
            font-family: var(--font);
        }

        .module-tab:hover { color: rgba(255,255,255,0.75); }

        .module-tab.active {
            color: var(--green);
            border-bottom-color: var(--green);
        }

        .module-tab .tab-icon {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }

        .module-tab.active .tab-icon.restaurant { background: rgba(239,68,68,0.15);  color: #EF4444; }
        .module-tab.active .tab-icon.boutique   { background: rgba(168,85,247,0.15); color: #A855F7; }
        .module-tab.active .tab-icon.quinc      { background: rgba(245,158,11,0.15); color: var(--orange); }
        .module-tab.active .tab-icon.location   { background: rgba(16,185,129,0.15); color: var(--green); }

        /* Panels */
        .module-panels { position: relative; }

        .module-panel {
            display: none;
            animation: fadeUp 0.4s ease forwards;
        }

        .module-panel.active { display: grid; }

        .module-panel-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            align-items: center;
        }

        /* Contenu texte du panel */
        .module-panel-content { display: flex; flex-direction: column; gap: 1.5rem; }

        .module-tag {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            padding: 0.35rem 0.9rem;
            width: fit-content;
        }

        .module-tag.restaurant { background: rgba(239,68,68,0.12);  color: #F87171; border: 1px solid rgba(239,68,68,0.25); }
        .module-tag.boutique   { background: rgba(168,85,247,0.12); color: #C084FC; border: 1px solid rgba(168,85,247,0.25); }
        .module-tag.quinc      { background: rgba(245,158,11,0.12); color: #FBB249; border: 1px solid rgba(245,158,11,0.25); }
        .module-tag.location   { background: rgba(16,185,129,0.12); color: #34D399; border: 1px solid rgba(16,185,129,0.25); }

        .module-panel-title {
            font-size: clamp(1.5rem, 3vw, 2.25rem);
            font-weight: 900;
            color: var(--white);
            letter-spacing: -0.04em;
            line-height: 1.15;
        }

        .module-panel-desc {
            font-size: 1rem;
            color: rgba(255,255,255,0.55);
            line-height: 1.75;
        }

        .module-features-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .module-features-list li {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.9rem;
            color: rgba(255,255,255,0.7);
        }

        .module-features-list li i {
            width: 20px;
            text-align: center;
            font-size: 0.85rem;
            flex-shrink: 0;
        }

        .li-check.restaurant { color: #F87171; }
        .li-check.boutique   { color: #C084FC; }
        .li-check.quinc      { color: #FBB249; }
        .li-check.location   { color: #34D399; }

        .module-cta {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            background: var(--green);
            color: var(--white);
            font-size: 0.9rem;
            font-weight: 700;
            padding: 0.8rem 1.75rem;
            border: none;
            cursor: pointer;
            font-family: var(--font);
            transition: background 0.2s, transform 0.15s;
            width: fit-content;
        }

        .module-cta:hover { background: var(--green-dark); transform: translateY(-2px); }

        /* Visuel du panel (mockup) */
        .module-panel-visual {
            position: relative;
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.07);
            padding: 2rem;
            min-height: 360px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 1.25rem;
        }

        .mockup-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.06);
        }

        .mockup-title {
            font-size: 0.8rem;
            font-weight: 700;
            color: rgba(255,255,255,0.6);
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .mockup-dot { width: 8px; height: 8px; }
        .mockup-dot.green  { background: var(--green); }
        .mockup-dot.orange { background: var(--orange); }
        .mockup-dot.red    { background: #EF4444; }
        .mockup-dot.purple { background: #A855F7; }

        .mockup-rows {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            flex: 1;
        }

        .mockup-row {
            display: flex;
            align-items: center;
            gap: 1rem;
            background: rgba(255,255,255,0.03);
            padding: 0.75rem 1rem;
            border-left: 3px solid transparent;
        }

        .mockup-row.hl-red    { border-left-color: #EF4444; }
        .mockup-row.hl-purple { border-left-color: #A855F7; }
        .mockup-row.hl-orange { border-left-color: var(--orange); }
        .mockup-row.hl-green  { border-left-color: var(--green); }

        .mockup-row-icon {
            width: 34px;
            height: 34px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            flex-shrink: 0;
        }

        .mockup-row-info { flex: 1; display: flex; flex-direction: column; gap: 0.2rem; }
        .mockup-row-name  { font-size: 0.82rem; font-weight: 600; color: rgba(255,255,255,0.8); }
        .mockup-row-desc  { font-size: 0.72rem; color: rgba(255,255,255,0.35); }
        .mockup-row-price { font-size: 0.9rem; font-weight: 700; color: var(--white); white-space: nowrap; }

        /* Barre du bas */
        .mockup-bar {
            display: flex;
            gap: 0.5rem;
        }

        .mockup-bar-item {
            flex: 1;
            height: 6px;
            background: rgba(255,255,255,0.06);
        }

        .mockup-bar-item.fill-green  { background: var(--green); }
        .mockup-bar-item.fill-orange { background: var(--orange); }
        .mockup-bar-item.fill-red    { background: #EF4444; }
        .mockup-bar-item.fill-purple { background: #A855F7; }

        @media (max-width: 900px) {
            .module-panel-grid { grid-template-columns: 1fr; }
            .module-panel-visual { min-height: auto; }
            .module-tabs { gap: 0; }
            .module-tab { padding: 0.75rem 1rem; font-size: 0.8rem; }
        }

        /* ================================================
           SECTION PRICING
           ================================================ */
        .pricing {
            padding: 6rem 1.5rem;
            background: var(--light);
            position: relative;
            overflow: hidden;
        }

        .pricing::after {
            content: '';
            position: absolute;
            inset: 0;
            background: url('/images/pricing-bg.webp.png') center/cover no-repeat;
            opacity: 0.04;
            pointer-events: none;
            z-index: 0;
        }

        .pricing > * { position: relative; z-index: 1; }

        /* Toggle mensuel / annuel */
        .pricing-toggle {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            margin-bottom: 3.5rem;
        }

        .toggle-label {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--gray);
            transition: color 0.2s;
        }

        .toggle-label.active { color: var(--dark); }

        .toggle-switch {
            position: relative;
            width: 52px;
            height: 28px;
            background: var(--gray-light);
            cursor: pointer;
            transition: background 0.2s;
        }

        .toggle-switch.on { background: var(--green); }

        .toggle-knob {
            position: absolute;
            top: 3px;
            left: 3px;
            width: 22px;
            height: 22px;
            background: var(--white);
            transition: transform 0.25s ease;
            box-shadow: 0 1px 4px rgba(0,0,0,0.15);
        }

        .toggle-switch.on .toggle-knob {
            transform: translateX(24px);
        }

        .toggle-save {
            font-size: 0.72rem;
            font-weight: 700;
            color: var(--green-dark);
            background: var(--green-light);
            padding: 0.2rem 0.6rem;
            letter-spacing: 0.04em;
        }

        /* Grille des plans */
        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0;
            max-width: 1080px;
            margin: 0 auto;
            border: 1px solid var(--gray-light);
        }

        .plan-card {
            background: var(--white);
            padding: 2.5rem 2rem;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            border-right: 1px solid var(--gray-light);
            position: relative;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .plan-card:last-child { border-right: none; }

        .plan-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 48px rgba(15,23,42,0.1);
            z-index: 2;
        }

        .plan-card.popular {
            background: var(--dark);
            border-right: 1px solid rgba(255,255,255,0.08);
        }

        .plan-card.popular:hover {
            box-shadow: 0 16px 48px rgba(16,185,129,0.2);
        }

        .plan-popular-badge {
            position: absolute;
            top: -1px;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--green);
        }

        .plan-popular-label {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--dark);
            background: var(--green);
            padding: 0.3rem 0.75rem;
            width: fit-content;
        }

        .plan-header { display: flex; flex-direction: column; gap: 0.5rem; }

        .plan-icon {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            margin-bottom: 0.25rem;
        }

        .plan-icon.starter  { background: var(--orange-light); color: var(--orange-dark); }
        .plan-icon.pro      { background: rgba(16,185,129,0.15); color: var(--green); }
        .plan-icon.premium   { background: rgba(99,102,241,0.15); color: #6366F1; }

        .plan-name {
            font-size: 1.15rem;
            font-weight: 800;
            letter-spacing: -0.02em;
        }

        .plan-card .plan-name { color: var(--dark); }
        .plan-card.popular .plan-name { color: var(--white); }

        .plan-desc {
            font-size: 0.85rem;
            line-height: 1.6;
        }

        .plan-card .plan-desc { color: var(--gray); }
        .plan-card.popular .plan-desc { color: rgba(255,255,255,0.5); }

        /* Prix */
        .plan-price {
            display: flex;
            align-items: baseline;
            gap: 0.35rem;
            padding: 1.25rem 0;
            border-top: 1px solid var(--gray-light);
            border-bottom: 1px solid var(--gray-light);
        }

        .plan-card.popular .plan-price {
            border-top-color: rgba(255,255,255,0.08);
            border-bottom-color: rgba(255,255,255,0.08);
        }

        .plan-amount {
            font-size: 2.5rem;
            font-weight: 900;
            letter-spacing: -0.05em;
            line-height: 1;
        }

        .plan-card .plan-amount { color: var(--dark); }
        .plan-card.popular .plan-amount { color: var(--white); }

        .plan-currency {
            font-size: 0.9rem;
            font-weight: 700;
        }

        .plan-card .plan-currency { color: var(--gray); }
        .plan-card.popular .plan-currency { color: rgba(255,255,255,0.5); }

        .plan-period {
            font-size: 0.8rem;
            font-weight: 500;
        }

        .plan-card .plan-period { color: var(--gray); }
        .plan-card.popular .plan-period { color: rgba(255,255,255,0.4); }

        /* Features list */
        .plan-features {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 0.7rem;
            flex: 1;
        }

        .plan-features li {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            font-size: 0.85rem;
        }

        .plan-card .plan-features li { color: var(--dark-3); }
        .plan-card.popular .plan-features li { color: rgba(255,255,255,0.75); }

        .plan-features li i {
            width: 18px;
            text-align: center;
            font-size: 0.75rem;
            flex-shrink: 0;
        }

        .plan-features .check { color: var(--green); }
        .plan-features .cross { color: var(--gray-light); }
        .plan-card.popular .plan-features .cross { color: rgba(255,255,255,0.2); }

        /* CTA */
        .plan-cta {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            font-weight: 700;
            padding: 0.85rem 1.5rem;
            border: none;
            cursor: pointer;
            font-family: var(--font);
            transition: all 0.2s;
            width: 100%;
            text-align: center;
        }

        .plan-cta.outline {
            background: transparent;
            border: 1px solid var(--gray-light);
            color: var(--dark);
        }

        .plan-cta.outline:hover {
            border-color: var(--dark);
            transform: translateY(-2px);
        }

        .plan-cta.green {
            background: var(--green);
            color: var(--white);
        }

        .plan-cta.green:hover {
            background: var(--green-dark);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(16,185,129,0.35);
        }

        .plan-cta.white {
            background: var(--white);
            color: var(--dark);
        }

        .plan-cta.white:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(255,255,255,0.15);
        }

        /* Note sous la grille */
        .pricing-note {
            text-align: center;
            margin-top: 2.5rem;
            color: var(--gray);
            font-size: 0.875rem;
        }

        .pricing-note i { color: var(--green); margin-right: 0.35rem; }

        @media (max-width: 900px) {
            .pricing-grid { grid-template-columns: 1fr; max-width: 420px; }
            .plan-card { border-right: none; border-bottom: 1px solid var(--gray-light); }
            .plan-card:last-child { border-bottom: none; }
            .plan-card.popular { border-bottom: 1px solid rgba(255,255,255,0.08); }
        }

        /* ============================================================
           TEMOIGNAGES
        ============================================================ */
        .testimonials-section {
            background: var(--dark);
            padding: 6rem 0;
            position: relative;
            overflow: hidden;
        }

        .testimonials-section::after {
            content: '';
            position: absolute;
            inset: 0;
            background: url('/images/testimonials-bg.webp.png.jpg') center/cover no-repeat;
            opacity: 0.07;
            pointer-events: none;
            z-index: 0;
        }

        .testimonials-section > * { position: relative; z-index: 1; }

        .testimonials-section .section-title { color: var(--white); }
        .testimonials-section .section-sub { color: rgba(255,255,255,0.5); }
        .testimonials-section .section-badge {
            color: var(--green);
            background: rgba(16,185,129,0.1);
            border: 1px solid rgba(16,185,129,0.25);
        }
        .testimonials-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0;
        }
        .testimonial-card {
            padding: 2.5rem;
            border: 1px solid var(--gray-light);
            background: var(--dark-2);
            position: relative;
            transition: all 0.3s ease;
        }
        .testimonial-card:hover {
            background: var(--dark-3);
            transform: translateY(-4px);
        }
        .testimonial-stars { color: var(--orange); margin-bottom: 1rem; font-size: 0.85rem; letter-spacing: 2px; }
        .testimonial-text {
            color: var(--gray);
            font-size: 0.95rem;
            line-height: 1.7;
            margin-bottom: 1.5rem;
            font-style: italic;
        }
        .testimonial-author { display: flex; align-items: center; gap: 1rem; }
        .testimonial-avatar {
            width: 48px; height: 48px;
            background: var(--green);
            display: flex; align-items: center; justify-content: center;
            color: var(--white); font-weight: 700; font-size: 1.1rem;
        }
        .testimonial-card:nth-child(2) .testimonial-avatar { background: var(--orange); }
        .testimonial-card:nth-child(3) .testimonial-avatar { background: #6366F1; }
        .testimonial-info h4 { color: var(--white); font-size: 0.95rem; margin-bottom: 0.15rem; }
        .testimonial-info span { color: var(--gray); font-size: 0.8rem; }

        @media (max-width: 900px) {
            .testimonials-grid { grid-template-columns: 1fr; max-width: 480px; margin: 0 auto; }
        }

        /* ============================================================
           CTA BAND
        ============================================================ */
        .cta-band {
            background: var(--green);
            padding: 4.5rem 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .cta-band::after {
            content: '';
            position: absolute; inset: 0;
            background: url('/images/cta-bg.webp.png.jpg') center/cover no-repeat;
            opacity: 0.06;
            pointer-events: none;
            z-index: 0;
        }
        .cta-band::before {
            content: '';
            position: absolute; inset: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.06'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        .cta-band-content { position: relative; z-index: 1; }
        .cta-band h2 {
            color: var(--white); font-size: 2.2rem; font-weight: 800; margin-bottom: 0.75rem;
        }
        .cta-band p {
            color: rgba(255,255,255,0.85); font-size: 1.1rem; margin-bottom: 2rem;
        }
        .cta-band .btn-cta-white {
            display: inline-flex; align-items: center; gap: 0.6rem;
            padding: 1rem 2.5rem;
            background: var(--white); color: var(--green);
            font-weight: 700; font-size: 1rem;
            text-decoration: none;
            border: none; cursor: pointer;
            transition: all 0.3s ease;
        }
        .cta-band .btn-cta-white:hover { background: var(--dark); color: var(--green); transform: translateY(-2px); }

        /* ============================================================
           FOOTER
        ============================================================ */
        .footer { background: var(--dark-2); border-top: 1px solid var(--gray-light); padding: 4rem 0 0; }
        .footer-grid {
            display: grid;
            grid-template-columns: 2fr repeat(3, 1fr);
            gap: 3rem;
        }
        .footer-brand img { height: 48px; margin-bottom: 1rem; }
        .footer-brand p { color: var(--gray); font-size: 0.9rem; line-height: 1.6; max-width: 280px; }
        .footer-socials { display: flex; gap: 0.75rem; margin-top: 1.25rem; }
        .footer-socials a {
            width: 36px; height: 36px;
            border: 1px solid var(--gray-light);
            display: flex; align-items: center; justify-content: center;
            color: var(--gray); font-size: 0.9rem;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        .footer-socials a:hover { border-color: var(--green); color: var(--green); }
        .footer-col h4 {
            color: var(--white); font-size: 0.85rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: 1px;
            margin-bottom: 1.25rem;
        }
        .footer-col ul { list-style: none; padding: 0; margin: 0; }
        .footer-col li { margin-bottom: 0.6rem; }
        .footer-col a {
            color: var(--gray); font-size: 0.9rem;
            text-decoration: none; transition: color 0.2s;
        }
        .footer-col a:hover { color: var(--green); }
        .footer-bottom {
            margin-top: 3rem;
            padding: 1.5rem 0;
            border-top: 1px solid var(--gray-light);
            display: flex; justify-content: space-between; align-items: center;
        }
        .footer-bottom p { color: var(--gray); font-size: 0.8rem; margin: 0; }
        .footer-bottom-links { display: flex; gap: 1.5rem; }
        .footer-bottom-links a { color: var(--gray); font-size: 0.8rem; text-decoration: none; }
        .footer-bottom-links a:hover { color: var(--green); }

        @media (max-width: 900px) {
            .footer-grid { grid-template-columns: 1fr 1fr; }
            .footer-brand { grid-column: 1 / -1; }
        }
        @media (max-width: 600px) {
            .footer-grid { grid-template-columns: 1fr; }
            .footer-bottom { flex-direction: column; gap: 0.75rem; text-align: center; }
            .cta-band h2 { font-size: 1.6rem; }
        }
    </style>
</head>
<body>
<div id="scroll-progress"></div>

<!-- Bouton flottant Workflow -->
<a id="btn-workflow" href="/workflow/index.html" target="_blank" title="Voir le workflow de développement">
    <i class="fas fa-diagram-project"></i> Workflow
</a>

<!-- ================================================
     NAVBAR
================================================ -->
<nav class="navbar" id="navbar">
    <div class="nav-container">

        <!-- Logo -->
        <a href="/" class="nav-logo" aria-label="Khayma — Accueil">
            <img src="/khayma_logo_transparent.png"
                 alt="Khayma"
                 class="nav-logo-img">
            <span class="nav-logo-tagline">Tenter<br>&amp; Estimer</span>
        </a>

        <!-- Desktop links -->
        <ul class="nav-links">
            <li><a href="#features">Fonctionnalités</a></li>
            <li><a href="#modules">Modules</a></li>
            <li><a href="#pricing">Tarifs</a></li>
            <li><a href="#testimonials">Témoignages</a></li>
        </ul>

        <!-- Desktop CTA -->
        <div class="nav-cta">
            @if (Route::has('login'))
                @auth
                    <a href="{{ url('/dashboard') }}" class="btn-primary">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn-link">Connexion</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="btn-primary">
                            <i class="fas fa-rocket"></i> Commencer
                        </a>
                    @endif
                @endauth
            @endif
        </div>

        <!-- Hamburger (mobile) -->
        <button class="nav-hamburger" id="hamburger" aria-label="Ouvrir le menu" aria-expanded="false">
            <span></span>
            <span></span>
            <span></span>
        </button>

    </div>
</nav>

<!-- Mobile menu -->
<div class="nav-mobile" id="mobileMenu" role="navigation" aria-label="Menu mobile">
    <ul>
        <li><a href="#features">Fonctionnalités</a></li>
        <li><a href="#modules">Modules</a></li>
        <li><a href="#pricing">Tarifs</a></li>
        <li><a href="#testimonials">Témoignages</a></li>
    </ul>
    @if (Route::has('login'))
        @auth
            <a href="{{ url('/dashboard') }}" class="btn-primary">Dashboard</a>
        @else
            @if (Route::has('register'))
                <a href="{{ route('register') }}" class="btn-primary">
                    <i class="fas fa-rocket"></i> Commencer gratuitement
                </a>
            @endif
        @endauth
    @endif
</div>

<!-- ================================================
     SECTION HERO
================================================ -->
<section class="hero" id="hero">
    <div class="hero-grid"></div>
    <div class="hero-glow"></div>
    <div class="hero-glow-2"></div>

    <div class="hero-container">

        <!-- Colonne gauche : texte -->
        <div class="hero-content">

            <!-- Badge live -->
            <div class="hero-badge">
                <span class="hero-badge-dot"></span>
                Nouveau — SaaS africain de gestion commerciale
            </div>

            <!-- Titre principal -->
            <h1 class="hero-title">
                Gérez votre commerce<br>
                avec <span class="accent-green">précision</span><br>
                et <span class="accent-orange">sérénité</span>
            </h1>

            <!-- Sous-titre -->
            <p class="hero-sub">
                Khayma centralise votre restaurant, boutique, quincaillerie
                ou location dans une seule plateforme intuitive,
                pensée pour les entrepreneurs africains.
            </p>

            <!-- Étymologie Khayma -->
            <div class="hero-etymology">
                <div class="etym-item">
                    <div class="etym-icon green">
                        <!-- SVG tente -->
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M12 3L1 21h22L12 3Z" fill="currentColor" fill-opacity="0.85"/>
                            <rect x="10.5" y="14" width="3" height="7" fill="#0F172A" fill-opacity="0.5"/>
                        </svg>
                    </div>
                    <div class="etym-text">
                        <span class="etym-word">Tenter</span>
                        <span class="etym-def">Oser entreprendre</span>
                    </div>
                </div>
                <div class="etym-item">
                    <div class="etym-icon orange">
                        <i class="fas fa-calculator" style="font-size:1rem;"></i>
                    </div>
                    <div class="etym-text">
                        <span class="etym-word">Estimer</span>
                        <span class="etym-def">Calculer en Wolof</span>
                    </div>
                </div>
            </div>

            <!-- CTA -->
            <div class="hero-actions">
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="btn-hero-primary">
                        <i class="fas fa-rocket"></i> Démarrer gratuitement
                    </a>
                @endif
                <a href="#features" class="btn-hero-outline">
                    <i class="fas fa-play-circle"></i> Voir la démo
                </a>
            </div>

            <!-- Stats -->
            <div class="hero-stats">
                <div class="stat-item">
                    <span class="stat-number"><span id="stat1">0</span>+</span>
                    <span class="stat-label">Entreprises actives</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><span id="stat2">0</span>K+</span>
                    <span class="stat-label">Transactions / mois</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><span id="stat3">0</span>%</span>
                    <span class="stat-label">Satisfaction client</span>
                </div>
            </div>

        </div>

        <!-- Colonne droite : visuel -->
        <div class="hero-visual">
            <div class="hero-visual-inner">

                <!-- Cercles décoratifs -->
                <div class="hero-circle"></div>
                <div class="hero-circle-2"></div>

                <!-- Logo Khayma transparent -->
                <img src="/khayma_logo_transparent.png"
                     alt="Khayma"
                     class="hero-logo-img">

                <!-- Carte flottante haut -->
                <div class="hero-card-top">
                    <div class="hero-card-icon green">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="hero-card-info">
                        <span class="hero-card-val">+34%</span>
                        <span class="hero-card-lbl">Croissance ce mois</span>
                    </div>
                </div>

                <!-- Carte flottante bas -->
                <div class="hero-card-bot">
                    <div class="hero-card-icon orange">
                        <i class="fas fa-store"></i>
                    </div>
                    <div class="hero-card-info">
                        <span class="hero-card-val">4 modules</span>
                        <span class="hero-card-lbl">Secteurs couverts</span>
                    </div>
                </div>

            </div>
        </div>

    </div>
</section>

<!-- ================================================
     SECTION FONCTIONNALITÉS
================================================ -->
<section class="features" id="features">
    <div class="section-container">

        <!-- En-tête -->
        <div class="section-header">
            <div class="section-badge green">
                <i class="fas fa-bolt"></i> Fonctionnalités
            </div>
            <h2 class="section-title">
                Tout ce dont votre commerce<br>a besoin, au même endroit
            </h2>
            <p class="section-sub">
                De la caisse à la comptabilité, Khayma couvre l'ensemble
                de vos opérations quotidiennes sans complexité.
            </p>
        </div>

        <!-- Grille 6 cards -->
        <div class="features-grid">

            <!-- 1 -->
            <div class="feature-card" data-reveal>
                <div class="feature-icon green">
                    <i class="fas fa-cash-register"></i>
                </div>
                <h3 class="feature-title">Caisse & Point de vente</h3>
                <p class="feature-desc">
                    Interface rapide, scan de codes-barres, tickets de caisse
                    et remises en quelques clics — même hors ligne.
                </p>
                <span class="feature-link">En savoir plus <i class="fas fa-arrow-right"></i></span>
            </div>

            <!-- 2 -->
            <div class="feature-card" data-reveal>
                <div class="feature-icon orange">
                    <i class="fas fa-boxes-stacked"></i>
                </div>
                <h3 class="feature-title">Gestion des stocks</h3>
                <p class="feature-desc">
                    Suivi en temps réel, alertes de rupture, entrées/sorties
                    et historique complet de chaque produit.
                </p>
                <span class="feature-link">En savoir plus <i class="fas fa-arrow-right"></i></span>
            </div>

            <!-- 3 -->
            <div class="feature-card" data-reveal>
                <div class="feature-icon dark">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <h3 class="feature-title">Rapports & Analytique</h3>
                <p class="feature-desc">
                    Tableaux de bord clairs, chiffre d'affaires journalier,
                    hebdomadaire, mensuel — exportables en PDF.
                </p>
                <span class="feature-link">En savoir plus <i class="fas fa-arrow-right"></i></span>
            </div>

            <!-- 4 -->
            <div class="feature-card" data-reveal>
                <div class="feature-icon green">
                    <i class="fas fa-users"></i>
                </div>
                <h3 class="feature-title">Gestion des employés</h3>
                <p class="feature-desc">
                    Rôles et permissions, pointage, performance individuelle
                    et accès multi-utilisateurs sécurisé.
                </p>
                <span class="feature-link">En savoir plus <i class="fas fa-arrow-right"></i></span>
            </div>

            <!-- 5 -->
            <div class="feature-card" data-reveal>
                <div class="feature-icon orange">
                    <i class="fas fa-file-invoice"></i>
                </div>
                <h3 class="feature-title">Factures & Devis</h3>
                <p class="feature-desc">
                    Génération automatique de factures PDF professionnelles,
                    suivi des paiements et relances clients.
                </p>
                <span class="feature-link">En savoir plus <i class="fas fa-arrow-right"></i></span>
            </div>

            <!-- 6 -->
            <div class="feature-card" data-reveal>
                <div class="feature-icon dark">
                    <i class="fas fa-shield-halved"></i>
                </div>
                <h3 class="feature-title">Sécurité & Sauvegarde</h3>
                <p class="feature-desc">
                    Données chiffrées, sauvegardes automatiques, journal
                    d'audit complet et accès restreint par rôle.
                </p>
                <span class="feature-link">En savoir plus <i class="fas fa-arrow-right"></i></span>
            </div>

        </div>

        <!-- Bande stats -->
        <div class="features-stats">
            <div class="feat-stat">
                <span class="feat-stat-number">99<span class="accent">%</span></span>
                <span class="feat-stat-label">Disponibilité</span>
                <span class="feat-stat-desc">Uptime garanti avec surveillance 24h/24</span>
            </div>
            <div class="feat-stat">
                <span class="feat-stat-number">&lt;1<span class="accent">s</span></span>
                <span class="feat-stat-label">Temps de réponse</span>
                <span class="feat-stat-desc">Interface fluide même sur connexion lente</span>
            </div>
            <div class="feat-stat">
                <span class="feat-stat-number">256<span class="accent">-bit</span></span>
                <span class="feat-stat-label">Chiffrement</span>
                <span class="feat-stat-desc">Vos données sont protégées de bout en bout</span>
            </div>
            <div class="feat-stat">
                <span class="feat-stat-number">4<span class="accent">+</span></span>
                <span class="feat-stat-label">Modules métier</span>
                <span class="feat-stat-desc">Restaurant · Boutique · Quincaillerie · Location</span>
            </div>
        </div>

    </div>
</section>

<!-- ================================================
     SECTION MODULES METIER
================================================ -->
<section class="modules" id="modules">
    <div class="section-container">

        <div class="section-header">
            <div class="section-badge orange">
                <i class="fas fa-cubes"></i> Modules Métier
            </div>
            <h2 class="section-title">
                Un module dédié<br>pour chaque activité
            </h2>
            <p class="section-sub">
                Choisissez le module adapté à votre commerce.
                Chaque module est conçu spécifiquement pour son secteur.
            </p>
        </div>

        <!-- Onglets -->
        <div class="module-tabs" role="tablist">
            <button class="module-tab active" onclick="switchModule('restaurant')" role="tab" aria-selected="true">
                <span class="tab-icon restaurant"><i class="fas fa-utensils"></i></span>
                Restaurant
            </button>
            <button class="module-tab" onclick="switchModule('boutique')" role="tab" aria-selected="false">
                <span class="tab-icon boutique"><i class="fas fa-shirt"></i></span>
                Boutique
            </button>
            <button class="module-tab" onclick="switchModule('quinc')" role="tab" aria-selected="false">
                <span class="tab-icon quinc"><i class="fas fa-wrench"></i></span>
                Quincaillerie
            </button>
            <button class="module-tab" onclick="switchModule('location')" role="tab" aria-selected="false">
                <span class="tab-icon location"><i class="fas fa-key"></i></span>
                Location
            </button>
        </div>

        <!-- Panels -->
        <div class="module-panels">

            <!-- RESTAURANT -->
            <div class="module-panel active" id="panel-restaurant">
                <div class="module-panel-grid">
                    <div class="module-panel-content">
                        <span class="module-tag restaurant"><i class="fas fa-utensils"></i> Restaurant</span>
                        <h3 class="module-panel-title">Gérez tables, commandes<br>et cuisine en temps réel</h3>
                        <p class="module-panel-desc">
                            Du plan de salle à la cuisine, chaque commande est suivie
                            en direct. Tickets imprimés automatiquement, stocks
                            ingrédients gérés intelligemment.
                        </p>
                        <ul class="module-features-list">
                            <li><i class="fas fa-check li-check restaurant"></i> Gestion des tables et salles</li>
                            <li><i class="fas fa-check li-check restaurant"></i> Commandes en salle, à emporter, livraison</li>
                            <li><i class="fas fa-check li-check restaurant"></i> Affichage cuisine en temps réel</li>
                            <li><i class="fas fa-check li-check restaurant"></i> Gestion des ingrédients et recettes</li>
                            <li><i class="fas fa-check li-check restaurant"></i> Statistiques par plat et par serveur</li>
                        </ul>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="module-cta">
                                <i class="fas fa-rocket"></i> Essayer ce module
                            </a>
                        @endif
                    </div>
                    <div class="module-panel-visual">
                        <div class="mockup-header">
                            <span class="mockup-title">Commandes du jour</span>
                            <span class="mockup-dot red"></span>
                        </div>
                        <div class="mockup-rows">
                            <div class="mockup-row hl-red">
                                <div class="mockup-row-icon" style="background:rgba(239,68,68,0.12);color:#F87171"><i class="fas fa-chair"></i></div>
                                <div class="mockup-row-info">
                                    <span class="mockup-row-name">Table 4 — 3 couverts</span>
                                    <span class="mockup-row-desc">Thieb + Yassa + Bissap</span>
                                </div>
                                <span class="mockup-row-price">8 500 F</span>
                            </div>
                            <div class="mockup-row hl-red">
                                <div class="mockup-row-icon" style="background:rgba(239,68,68,0.12);color:#F87171"><i class="fas fa-motorcycle"></i></div>
                                <div class="mockup-row-info">
                                    <span class="mockup-row-name">Livraison #42</span>
                                    <span class="mockup-row-desc">Mafé + Riz blanc</span>
                                </div>
                                <span class="mockup-row-price">3 200 F</span>
                            </div>
                            <div class="mockup-row hl-red">
                                <div class="mockup-row-icon" style="background:rgba(239,68,68,0.12);color:#F87171"><i class="fas fa-bag-shopping"></i></div>
                                <div class="mockup-row-info">
                                    <span class="mockup-row-name">À emporter #17</span>
                                    <span class="mockup-row-desc">Poulet braisé + Attiéké</span>
                                </div>
                                <span class="mockup-row-price">4 000 F</span>
                            </div>
                        </div>
                        <div class="mockup-bar">
                            <div class="mockup-bar-item fill-red" style="flex:4"></div>
                            <div class="mockup-bar-item" style="flex:2"></div>
                            <div class="mockup-bar-item" style="flex:1"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BOUTIQUE -->
            <div class="module-panel" id="panel-boutique">
                <div class="module-panel-grid">
                    <div class="module-panel-content">
                        <span class="module-tag boutique"><i class="fas fa-shirt"></i> Boutique</span>
                        <h3 class="module-panel-title">Vendez plus vite,<br>gérez sans effort</h3>
                        <p class="module-panel-desc">
                            Catalogue produits, caisse rapide, gestion des tailles
                            et couleurs, fidélisation client. Tout pour booster
                            votre boutique de mode ou générale.
                        </p>
                        <ul class="module-features-list">
                            <li><i class="fas fa-check li-check boutique"></i> Catalogue avec variantes (taille, couleur)</li>
                            <li><i class="fas fa-check li-check boutique"></i> Codes-barres et étiquettes</li>
                            <li><i class="fas fa-check li-check boutique"></i> Caisse POS ultra-rapide</li>
                            <li><i class="fas fa-check li-check boutique"></i> Programme de fidélité client</li>
                            <li><i class="fas fa-check li-check boutique"></i> Soldes et promotions programmées</li>
                        </ul>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="module-cta">
                                <i class="fas fa-rocket"></i> Essayer ce module
                            </a>
                        @endif
                    </div>
                    <div class="module-panel-visual">
                        <div class="mockup-header">
                            <span class="mockup-title">Ventes aujourd'hui</span>
                            <span class="mockup-dot purple"></span>
                        </div>
                        <div class="mockup-rows">
                            <div class="mockup-row hl-purple">
                                <div class="mockup-row-icon" style="background:rgba(168,85,247,0.12);color:#C084FC"><i class="fas fa-shirt"></i></div>
                                <div class="mockup-row-info">
                                    <span class="mockup-row-name">Boubou Taille M — Bleu</span>
                                    <span class="mockup-row-desc">Stock : 12 restants</span>
                                </div>
                                <span class="mockup-row-price">18 000 F</span>
                            </div>
                            <div class="mockup-row hl-purple">
                                <div class="mockup-row-icon" style="background:rgba(168,85,247,0.12);color:#C084FC"><i class="fas fa-shoe-prints"></i></div>
                                <div class="mockup-row-info">
                                    <span class="mockup-row-name">Mocassins Pointure 42</span>
                                    <span class="mockup-row-desc">Stock : 3 restants</span>
                                </div>
                                <span class="mockup-row-price">25 000 F</span>
                            </div>
                            <div class="mockup-row hl-purple">
                                <div class="mockup-row-icon" style="background:rgba(168,85,247,0.12);color:#C084FC"><i class="fas fa-tag"></i></div>
                                <div class="mockup-row-info">
                                    <span class="mockup-row-name">Promo Fin de semaine</span>
                                    <span class="mockup-row-desc">-20% sur sacs femme</span>
                                </div>
                                <span class="mockup-row-price">actif</span>
                            </div>
                        </div>
                        <div class="mockup-bar">
                            <div class="mockup-bar-item fill-purple" style="flex:3"></div>
                            <div class="mockup-bar-item" style="flex:2"></div>
                            <div class="mockup-bar-item" style="flex:3"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- QUINCAILLERIE -->
            <div class="module-panel" id="panel-quinc">
                <div class="module-panel-grid">
                    <div class="module-panel-content">
                        <span class="module-tag quinc"><i class="fas fa-wrench"></i> Quincaillerie</span>
                        <h3 class="module-panel-title">Stocks complexes,<br>gérés simplement</h3>
                        <p class="module-panel-desc">
                            Des milliers de références, units de mesure multiples,
                            fournisseurs et bons de commande. La quincaillerie
                            n'a plus de secrets.
                        </p>
                        <ul class="module-features-list">
                            <li><i class="fas fa-check li-check quinc"></i> Catégories et sous-catégories illimitées</li>
                            <li><i class="fas fa-check li-check quinc"></i> Unités multiples (mètre, kg, pièce, litre)</li>
                            <li><i class="fas fa-check li-check quinc"></i> Bons de commande fournisseur</li>
                            <li><i class="fas fa-check li-check quinc"></i> Prix de gros et détail automatiques</li>
                            <li><i class="fas fa-check li-check quinc"></i> Alertes de rupture intelligentes</li>
                        </ul>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="module-cta">
                                <i class="fas fa-rocket"></i> Essayer ce module
                            </a>
                        @endif
                    </div>
                    <div class="module-panel-visual">
                        <div class="mockup-header">
                            <span class="mockup-title">Stock critique</span>
                            <span class="mockup-dot orange"></span>
                        </div>
                        <div class="mockup-rows">
                            <div class="mockup-row hl-orange">
                                <div class="mockup-row-icon" style="background:rgba(245,158,11,0.12);color:#FBB249"><i class="fas fa-bolt"></i></div>
                                <div class="mockup-row-info">
                                    <span class="mockup-row-name">Câble électrique 2.5mm</span>
                                    <span class="mockup-row-desc">Stock : 18m — seuil : 50m</span>
                                </div>
                                <span class="mockup-row-price" style="color:#FBB249">Alerte</span>
                            </div>
                            <div class="mockup-row hl-orange">
                                <div class="mockup-row-icon" style="background:rgba(245,158,11,0.12);color:#FBB249"><i class="fas fa-faucet"></i></div>
                                <div class="mockup-row-info">
                                    <span class="mockup-row-name">Tuyau PVC 32mm × 6m</span>
                                    <span class="mockup-row-desc">Stock : 5 pièces</span>
                                </div>
                                <span class="mockup-row-price">6 500 F</span>
                            </div>
                            <div class="mockup-row hl-orange">
                                <div class="mockup-row-icon" style="background:rgba(245,158,11,0.12);color:#FBB249"><i class="fas fa-screwdriver"></i></div>
                                <div class="mockup-row-info">
                                    <span class="mockup-row-name">Visserie inox 6×50</span>
                                    <span class="mockup-row-desc">Stock : 847 pièces</span>
                                </div>
                                <span class="mockup-row-price">150 F</span>
                            </div>
                        </div>
                        <div class="mockup-bar">
                            <div class="mockup-bar-item fill-orange" style="flex:2"></div>
                            <div class="mockup-bar-item" style="flex:4"></div>
                            <div class="mockup-bar-item" style="flex:1"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- LOCATION -->
            <div class="module-panel" id="panel-location">
                <div class="module-panel-grid">
                    <div class="module-panel-content">
                        <span class="module-tag location"><i class="fas fa-key"></i> Location</span>
                        <h3 class="module-panel-title">Contrats, cautions<br>et plannings automatisés</h3>
                        <p class="module-panel-desc">
                            Gérez vos biens en location (véhicules, immeubles, matériel).
                            Contrats PDF, suivis de paiements, cautions et
                            calendrier de disponibilité intuitif.
                        </p>
                        <ul class="module-features-list">
                            <li><i class="fas fa-check li-check location"></i> Catalogue biens locatifs</li>
                            <li><i class="fas fa-check li-check location"></i> Contrats et état des lieux PDF</li>
                            <li><i class="fas fa-check li-check location"></i> Suivi des loyers et cautions</li>
                            <li><i class="fas fa-check li-check location"></i> Calendrier de disponibilité</li>
                            <li><i class="fas fa-check li-check location"></i> Alertes échéances et retards</li>
                        </ul>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="module-cta">
                                <i class="fas fa-rocket"></i> Essayer ce module
                            </a>
                        @endif
                    </div>
                    <div class="module-panel-visual">
                        <div class="mockup-header">
                            <span class="mockup-title">Locations actives</span>
                            <span class="mockup-dot green"></span>
                        </div>
                        <div class="mockup-rows">
                            <div class="mockup-row hl-green">
                                <div class="mockup-row-icon" style="background:rgba(16,185,129,0.12);color:#34D399"><i class="fas fa-car"></i></div>
                                <div class="mockup-row-info">
                                    <span class="mockup-row-name">Toyota Hilux — DK-3421-A</span>
                                    <span class="mockup-row-desc">Retour le 28 mars 2026</span>
                                </div>
                                <span class="mockup-row-price">85 000 F</span>
                            </div>
                            <div class="mockup-row hl-green">
                                <div class="mockup-row-icon" style="background:rgba(16,185,129,0.12);color:#34D399"><i class="fas fa-building"></i></div>
                                <div class="mockup-row-info">
                                    <span class="mockup-row-name">Bureau 2ème étage — Lot B</span>
                                    <span class="mockup-row-desc">Loyer mensuel</span>
                                </div>
                                <span class="mockup-row-price">150 000 F</span>
                            </div>
                            <div class="mockup-row hl-green">
                                <div class="mockup-row-icon" style="background:rgba(16,185,129,0.12);color:#34D399"><i class="fas fa-camera"></i></div>
                                <div class="mockup-row-info">
                                    <span class="mockup-row-name">Kit photo Sony A7III</span>
                                    <span class="mockup-row-desc">Caution : 300 000 F</span>
                                </div>
                                <span class="mockup-row-price">25 000 F</span>
                            </div>
                        </div>
                        <div class="mockup-bar">
                            <div class="mockup-bar-item fill-green" style="flex:5"></div>
                            <div class="mockup-bar-item" style="flex:1"></div>
                            <div class="mockup-bar-item" style="flex:1"></div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- ================================================
     SECTION TARIFS
================================================ -->
<section class="pricing" id="pricing">
    <div class="section-container">

        <div class="section-header">
            <div class="section-badge green">
                <i class="fas fa-tag"></i> Tarifs
            </div>
            <h2 class="section-title">
                Des prix simples,<br>sans mauvaise surprise
            </h2>
            <p class="section-sub">
                Choisissez le plan adapté à la taille de votre commerce.
                Tous les plans incluent 14 jours d'essai gratuit.
            </p>
        </div>

        <!-- Toggle -->
        <div class="pricing-toggle">
            <span class="toggle-label active" id="lblMonthly">Mensuel</span>
            <div class="toggle-switch" id="toggleSwitch" onclick="togglePricing()">
                <div class="toggle-knob"></div>
            </div>
            <span class="toggle-label" id="lblYearly">Annuel</span>
            <span class="toggle-save">−18%</span>
        </div>

        <!-- Grille 3 plans -->
        <div class="pricing-grid">

            <!-- STARTER -->
            <div class="plan-card" data-reveal>
                <div class="plan-header">
                    <div class="plan-icon starter"><i class="fas fa-seedling"></i></div>
                    <h3 class="plan-name">Starter</h3>
                    <p class="plan-desc">Idéal pour démarrer un petit commerce ou tester la plateforme.</p>
                </div>
                <div class="plan-price">
                    <span class="plan-amount" data-monthly="15 000" data-yearly="148 000">15 000</span>
                    <span class="plan-currency">XOF</span>
                    <span class="plan-period">/ <span class="period-text">mois</span></span>
                </div>
                <ul class="plan-features">
                    <li><i class="fas fa-check check"></i> 1 module au choix</li>
                    <li><i class="fas fa-check check"></i> Jusqu'à 200 produits</li>
                    <li><i class="fas fa-check check"></i> 2 utilisateurs</li>
                    <li><i class="fas fa-check check"></i> 1 Go stockage</li>
                    <li><i class="fas fa-check check"></i> Rapports basiques</li>
                    <li><i class="fas fa-xmark cross"></i> Support prioritaire</li>
                    <li><i class="fas fa-xmark cross"></i> API accès</li>
                </ul>
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="plan-cta outline">
                        Commencer l'essai gratuit
                    </a>
                @endif
            </div>

            <!-- PRO (populaire) -->
            <div class="plan-card popular" data-reveal>
                <div class="plan-popular-badge"></div>
                <span class="plan-popular-label"><i class="fas fa-star"></i> Le plus populaire</span>
                <div class="plan-header">
                    <div class="plan-icon pro"><i class="fas fa-rocket"></i></div>
                    <h3 class="plan-name">Pro</h3>
                    <p class="plan-desc">Pour les commerces établis qui veulent accélérer leur croissance.</p>
                </div>
                <div class="plan-price">
                    <span class="plan-amount" data-monthly="30 000" data-yearly="295 000">30 000</span>
                    <span class="plan-currency">XOF</span>
                    <span class="plan-period">/ <span class="period-text">mois</span></span>
                </div>
                <ul class="plan-features">
                    <li><i class="fas fa-check check"></i> 2 modules au choix</li>
                    <li><i class="fas fa-check check"></i> Produits illimités</li>
                    <li><i class="fas fa-check check"></i> 5 utilisateurs</li>
                    <li><i class="fas fa-check check"></i> 5 Go stockage</li>
                    <li><i class="fas fa-check check"></i> Rapports avancés + PDF</li>
                    <li><i class="fas fa-check check"></i> Support prioritaire</li>
                    <li><i class="fas fa-xmark cross"></i> API accès</li>
                </ul>
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="plan-cta green">
                        <i class="fas fa-rocket"></i> Commencer l'essai gratuit
                    </a>
                @endif
            </div>

            <!-- PREMIUM -->
            <div class="plan-card" data-reveal>
                <div class="plan-header">
                    <div class="plan-icon premium"><i class="fas fa-crown"></i></div>
                    <h3 class="plan-name">Premium</h3>
                    <p class="plan-desc">Pour les structures multi-sites ou avec des besoins avancés.</p>
                </div>
                <div class="plan-price">
                    <span class="plan-amount" data-monthly="55 000" data-yearly="540 000">55 000</span>
                    <span class="plan-currency">XOF</span>
                    <span class="plan-period">/ <span class="period-text">mois</span></span>
                </div>
                <ul class="plan-features">
                    <li><i class="fas fa-check check"></i> Tous les modules</li>
                    <li><i class="fas fa-check check"></i> Produits illimités</li>
                    <li><i class="fas fa-check check"></i> Utilisateurs illimités</li>
                    <li><i class="fas fa-check check"></i> 20 Go stockage</li>
                    <li><i class="fas fa-check check"></i> Rapports + export + API</li>
                    <li><i class="fas fa-check check"></i> Support prioritaire 24/7</li>
                    <li><i class="fas fa-check check"></i> API accès complet</li>
                </ul>
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="plan-cta outline">
                        Commencer l'essai gratuit
                    </a>
                @endif
            </div>

        </div>

        <p class="pricing-note">
            <i class="fas fa-shield-halved"></i>
            14 jours d'essai gratuit · Sans engagement · Annulation en 1 clic
        </p>

    </div>
</section>

<!-- ================================================
     TEMOIGNAGES
================================================ -->
<section class="testimonials-section" id="temoignages">
    <div class="section-container">
        <div class="section-header" data-reveal>
            <span class="section-badge"><i class="fas fa-quote-left"></i> Témoignages</span>
            <h2 class="section-title">Ils nous font confiance</h2>
            <p class="section-sub">Des commerçants à travers l'Afrique de l'Ouest qui ont transformé leur activité avec Khayma.</p>
        </div>

        <div class="testimonials-grid" data-reveal>
            <!-- Témoignage 1 -->
            <div class="testimonial-card">
                <div class="testimonial-stars">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                </div>
                <p class="testimonial-text">
                    « Depuis que j'utilise Khayma, je sais exactement combien me rapporte chaque plat. Mon thiéboudienne est passé de 25% à 40% de marge grâce aux rapports détaillés. »
                </p>
                <div class="testimonial-author">
                    <div class="testimonial-avatar">AD</div>
                    <div class="testimonial-info">
                        <h4>Awa Diallo</h4>
                        <span>Restaurant Le Teranga — Dakar</span>
                    </div>
                </div>
            </div>

            <!-- Témoignage 2 -->
            <div class="testimonial-card">
                <div class="testimonial-stars">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                </div>
                <p class="testimonial-text">
                    « Avant Khayma, je perdais des articles sans savoir pourquoi. Le suivi des stocks en temps réel a réduit mes pertes de 60%. Je recommande à tous les commerçants. »
                </p>
                <div class="testimonial-author">
                    <div class="testimonial-avatar">MK</div>
                    <div class="testimonial-info">
                        <h4>Moussa Koné</h4>
                        <span>Boutique Élégance — Abidjan</span>
                    </div>
                </div>
            </div>

            <!-- Témoignage 3 -->
            <div class="testimonial-card">
                <div class="testimonial-stars">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>
                </div>
                <p class="testimonial-text">
                    « La gestion de mes locations de matériel était un cauchemar sur cahier. Avec Khayma, tout est suivi automatiquement : départs, retours, cautions. Un vrai gain de temps. »
                </p>
                <div class="testimonial-author">
                    <div class="testimonial-avatar">IB</div>
                    <div class="testimonial-info">
                        <h4>Ibrahim Ba</h4>
                        <span>Quincaillerie Ba & Fils — Saint-Louis</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ================================================
     CTA BAND
================================================ -->
<section class="cta-band">
    <div class="cta-band-content">
        <h2>Prêt à transformer votre commerce ?</h2>
        <p>Rejoignez les entrepreneurs qui gèrent mieux avec Khayma. Essai gratuit, sans engagement.</p>
        <a href="/register" class="btn-cta-white">
            Commencer gratuitement <i class="fas fa-arrow-right"></i>
        </a>
    </div>
</section>

<!-- ================================================
     FOOTER
================================================ -->
<footer class="footer">
    <div class="section-container">
        <div class="footer-grid">
            <!-- Brand -->
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

            <!-- Produit -->
            <div class="footer-col">
                <h4>Produit</h4>
                <ul>
                    <li><a href="#fonctionnalites">Fonctionnalités</a></li>
                    <li><a href="#tarifs">Tarifs</a></li>
                    <li><a href="#temoignages">Témoignages</a></li>
                    <li><a href="#">Mises à jour</a></li>
                </ul>
            </div>

            <!-- Modules -->
            <div class="footer-col">
                <h4>Modules</h4>
                <ul>
                    <li><a href="#">Restaurant</a></li>
                    <li><a href="#">Boutique</a></li>
                    <li><a href="#">Quincaillerie</a></li>
                    <li><a href="#">Location</a></li>
                </ul>
            </div>

            <!-- Support -->
            <div class="footer-col">
                <h4>Support</h4>
                <ul>
                    <li><a href="#">Centre d'aide</a></li>
                    <li><a href="#">Contact</a></li>
                    <li><a href="#">Documentation API</a></li>
                    <li><a href="#">Statut système</a></li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; 2025 Khayma. Tous droits réservés.</p>
            <div class="footer-bottom-links">
                <a href="#">Conditions d'utilisation</a>
                <a href="#">Politique de confidentialité</a>
                <a href="#">Mentions légales</a>
            </div>
        </div>
    </div>
</footer>

<!-- ================================================
     SCRIPTS NAVBAR
================================================ -->
<script>
    // Effet scroll navbar
    const navbar = document.getElementById('navbar');
    window.addEventListener('scroll', () => {
        navbar.classList.toggle('scrolled', window.scrollY > 20);
    }, { passive: true });

    // Hamburger toggle
    const hamburger = document.getElementById('hamburger');
    const mobileMenu = document.getElementById('mobileMenu');

    hamburger.addEventListener('click', () => {
        const isOpen = mobileMenu.classList.toggle('open');
        hamburger.setAttribute('aria-expanded', String(isOpen));
        const [s1, s2, s3] = hamburger.querySelectorAll('span');
        if (isOpen) {
            s1.style.transform = 'rotate(45deg) translate(5px, 5px)';
            s2.style.opacity   = '0';
            s3.style.transform = 'rotate(-45deg) translate(5px, -5px)';
        } else {
            s1.style.transform = '';
            s2.style.opacity   = '';
            s3.style.transform = '';
        }
    });

    // Fermer menu mobile au clic sur un lien intérieur
    mobileMenu.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', () => {
            mobileMenu.classList.remove('open');
            hamburger.setAttribute('aria-expanded', 'false');
            const [s1, s2, s3] = hamburger.querySelectorAll('span');
            s1.style.transform = '';
            s2.style.opacity   = '';
            s3.style.transform = '';
        });
    });

    // ================================================
    // Compteur animé (stats hero)
    // ================================================
    function animateCounter(el, target, duration = 1800) {
        const start = performance.now();
        const update = (now) => {
            const elapsed = now - start;
            const progress = Math.min(elapsed / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3); // ease-out cubic
            el.textContent = Math.floor(eased * target);
            if (progress < 1) requestAnimationFrame(update);
            else el.textContent = target;
        };
        requestAnimationFrame(update);
    }

    // Déclencher les compteurs quand la section hero est visible
    const statsObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateCounter(document.getElementById('stat1'), 120);
                animateCounter(document.getElementById('stat2'), 50);
                animateCounter(document.getElementById('stat3'), 98);
                statsObserver.disconnect();
            }
        });
    }, { threshold: 0.4 });

    const heroStat = document.querySelector('.hero-stats');
    if (heroStat) statsObserver.observe(heroStat);

    // ================================================
    // Scroll reveal — feature cards
    // ================================================
    const revealObserver = new IntersectionObserver((entries) => {
        entries.forEach((entry, i) => {
            if (entry.isIntersecting) {
                // délai en cascade selon la position dans la grille
                const idx = Array.from(entry.target.parentElement.children).indexOf(entry.target);
                setTimeout(() => {
                    entry.target.classList.add('visible');
                }, idx * 100);
                revealObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.15 });

    document.querySelectorAll('[data-reveal]').forEach(el => revealObserver.observe(el));

    // ================================================
    // Module tabs switcher
    // ================================================
    function switchModule(id) {
        document.querySelectorAll('.module-panel').forEach(p => p.classList.remove('active'));
        document.getElementById('panel-' + id).classList.add('active');
        document.querySelectorAll('.module-tab').forEach((tab, i) => {
            tab.classList.remove('active');
            tab.setAttribute('aria-selected', 'false');
        });
        const activeTab = document.querySelector(`.module-tab[onclick="switchModule('${id}')"]`);
        if (activeTab) {
            activeTab.classList.add('active');
            activeTab.setAttribute('aria-selected', 'true');
        }
    }

    // ================================================
    // Pricing toggle (mensuel / annuel)
    // ================================================
    let isYearly = false;

    function togglePricing() {
        isYearly = !isYearly;
        const sw = document.getElementById('toggleSwitch');
        const lblM = document.getElementById('lblMonthly');
        const lblY = document.getElementById('lblYearly');

        sw.classList.toggle('on', isYearly);
        lblM.classList.toggle('active', !isYearly);
        lblY.classList.toggle('active', isYearly);

        document.querySelectorAll('.plan-amount').forEach(el => {
            el.textContent = isYearly ? el.dataset.yearly : el.dataset.monthly;
        });

        document.querySelectorAll('.period-text').forEach(el => {
            el.textContent = isYearly ? 'an' : 'mois';
        });
    }

    // ================================================
    // Barre de progression de lecture
    // ================================================
    const progressBar = document.getElementById('scroll-progress');
    if (progressBar) {
        window.addEventListener('scroll', () => {
            const scrollTop = window.scrollY;
            const docHeight = document.documentElement.scrollHeight - window.innerHeight;
            const pct = docHeight > 0 ? (scrollTop / docHeight) * 100 : 0;
            progressBar.style.width = pct + '%';
        }, { passive: true });
    }

    // ================================================
    // Lien navbar actif au scroll
    // ================================================
    const navSections = [
        { id: 'features',     href: '#features' },
        { id: 'modules',      href: '#modules' },
        { id: 'pricing',      href: '#pricing' },
        { id: 'temoignages',  href: '#testimonials' },
    ];
    const navAnchors = document.querySelectorAll('.nav-links a');

    const sectionObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const activeId = entry.target.id;
                navAnchors.forEach(a => {
                    const match = navSections.find(s => s.id === activeId);
                    a.classList.toggle('active', match ? a.getAttribute('href') === match.href : false);
                });
            }
        });
    }, { threshold: 0.35 });

    navSections.forEach(s => {
        const el = document.getElementById(s.id);
        if (el) sectionObserver.observe(el);
    });

    // ================================================
    // Parallaxe subtil sur le hero
    // ================================================
    const heroSection = document.querySelector('.hero');
    if (heroSection) {
        window.addEventListener('scroll', () => {
            const scrolled = window.scrollY;
            if (scrolled < window.innerHeight) {
                heroSection.style.setProperty('--parallax-y', (scrolled * 0.25) + 'px');
            }
        }, { passive: true });
    }
</script>

</body>
</html>
