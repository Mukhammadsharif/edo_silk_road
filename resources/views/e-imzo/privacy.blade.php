<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Политика конфиденциальности — EdoLine</title>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #794DFD;
            --primary-light: #9d6fff;
            --primary-ultra-light: #f0ebff;
            --primary-glow: rgba(121, 77, 253, 0.2);
            --dark: #0f0c1a;
            --dark-2: #1a1528;
            --dark-3: #231e35;
            --text-main: #e8e3ff;
            --text-muted: #9490b0;
            --text-dim: #605b7a;
            --border: rgba(121, 77, 253, 0.18);
            --border-light: rgba(255,255,255,0.06);
            --card-bg: rgba(26, 21, 40, 0.8);
            --success: #10b981;
            --warning: #f59e0b;
            --info: #3b82f6;
            --danger: #ef4444;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'Sora', sans-serif;
            background: var(--dark);
            color: var(--text-main);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* ── ANIMATED BACKGROUND ── */
        .bg-mesh {
            position: fixed;
            inset: 0;
            z-index: 0;
            overflow: hidden;
            pointer-events: none;
        }
        .bg-mesh::before {
            content: '';
            position: absolute;
            width: 700px; height: 700px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(121,77,253,0.12) 0%, transparent 70%);
            top: -200px; left: -200px;
            animation: drift1 18s ease-in-out infinite alternate;
        }
        .bg-mesh::after {
            content: '';
            position: absolute;
            width: 500px; height: 500px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(59,130,246,0.08) 0%, transparent 70%);
            bottom: -100px; right: -100px;
            animation: drift2 22s ease-in-out infinite alternate;
        }
        .bg-grid {
            position: fixed;
            inset: 0;
            z-index: 0;
            background-image:
                linear-gradient(rgba(121,77,253,0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(121,77,253,0.04) 1px, transparent 1px);
            background-size: 60px 60px;
            pointer-events: none;
        }

        @keyframes drift1 { from { transform: translate(0,0) scale(1); } to { transform: translate(80px, 60px) scale(1.1); } }
        @keyframes drift2 { from { transform: translate(0,0) scale(1); } to { transform: translate(-60px, -40px) scale(1.15); } }

        /* ── LAYOUT ── */
        .page-wrap {
            position: relative;
            z-index: 1;
            max-width: 1100px;
            margin: 0 auto;
            padding: 0 1.5rem 6rem;
        }

        /* ── HERO HEADER ── */
        .hero {
            padding: 4rem 0 3rem;
            text-align: center;
            animation: fadeUp 0.8s ease both;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.4rem 1.2rem;
            background: rgba(121,77,253,0.15);
            border: 1px solid var(--border);
            border-radius: 100px;
            font-size: 12px;
            font-weight: 600;
            color: var(--primary-light);
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-bottom: 1.5rem;
        }
        .hero-badge .dot {
            width: 6px; height: 6px;
            border-radius: 50%;
            background: var(--primary-light);
            animation: blink 2s ease-in-out infinite;
        }

        @keyframes blink { 0%,100%{opacity:1} 50%{opacity:0.3} }

        .hero h1 {
            font-size: clamp(2.2rem, 5vw, 3.8rem);
            font-weight: 800;
            line-height: 1.1;
            background: linear-gradient(135deg, #fff 30%, var(--primary-light) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1rem;
        }
        .hero h1 span {
            display: block;
            font-size: 0.65em;
            font-weight: 400;
            color: var(--text-muted);
            -webkit-text-fill-color: var(--text-muted);
            margin-top: 0.3rem;
        }

        .hero-meta {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 2rem;
            margin-top: 2rem;
            flex-wrap: wrap;
        }
        .hero-meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 13px;
            color: var(--text-muted);
        }
        .hero-meta-item svg { width:14px; height:14px; color: var(--primary-light); }

        /* ── TAB NAVIGATION ── */
        .tab-nav {
            display: flex;
            gap: 0.5rem;
            background: var(--dark-2);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 0.4rem;
            margin: 2.5rem 0;
            animation: fadeUp 0.8s ease 0.1s both;
            overflow-x: auto;
            scrollbar-width: none;
        }
        .tab-nav::-webkit-scrollbar { display: none; }

        .tab-btn {
            flex: 1;
            min-width: max-content;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            border: none;
            background: transparent;
            color: var(--text-muted);
            font-size: 14px;
            font-weight: 600;
            font-family: 'Sora', sans-serif;
            cursor: pointer;
            transition: all 0.3s ease;
            white-space: nowrap;
        }
        .tab-btn svg { width:16px; height:16px; }
        .tab-btn:hover { color: var(--text-main); background: rgba(255,255,255,0.05); }
        .tab-btn.active {
            background: linear-gradient(135deg, #794DFD, #9d6fff);
            color: #fff;
            box-shadow: 0 4px 16px rgba(121,77,253,0.35);
        }

        /* ── TOC SIDEBAR ── */
        .content-layout {
            display: grid;
            grid-template-columns: 260px 1fr;
            gap: 2rem;
            align-items: start;
            animation: fadeUp 0.8s ease 0.2s both;
        }
        @media(max-width: 900px) { .content-layout { grid-template-columns: 1fr; } }

        .toc-sidebar {
            position: sticky;
            top: 2rem;
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 1.5rem;
            backdrop-filter: blur(12px);
        }
        @media(max-width:900px) { .toc-sidebar { position:static; } }

        .toc-title {
            font-size: 11px;
            font-weight: 700;
            color: var(--primary-light);
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .toc-title svg { width:14px; height:14px; }

        .toc-list { list-style: none; display: flex; flex-direction: column; gap: 0.25rem; }
        .toc-item a {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 0.75rem;
            border-radius: 8px;
            font-size: 13px;
            color: var(--text-muted);
            text-decoration: none;
            transition: all 0.2s;
            line-height: 1.4;
        }
        .toc-item a:hover { color: var(--text-main); background: rgba(121,77,253,0.1); }
        .toc-item a.active { color: var(--primary-light); background: rgba(121,77,253,0.15); font-weight: 600; }
        .toc-num {
            font-family: 'JetBrains Mono', monospace;
            font-size: 10px;
            color: var(--primary);
            font-weight: 600;
            min-width: 20px;
        }

        /* ── SECTION PANELS ── */
        .tab-panel { display: none; }
        .tab-panel.active { display: block; }

        .section-block {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 1.5rem;
            backdrop-filter: blur(12px);
            transition: border-color 0.3s, box-shadow 0.3s;
            animation: fadeUp 0.5s ease both;
        }
        .section-block:hover {
            border-color: rgba(121,77,253,0.35);
            box-shadow: 0 8px 32px rgba(121,77,253,0.08);
        }

        .section-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
            cursor: pointer;
            user-select: none;
        }
        .section-icon {
            width: 44px; height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .section-icon svg { width: 22px; height: 22px; color: #fff; }
        .icon-purple { background: linear-gradient(135deg, #794DFD, #9d6fff); }
        .icon-blue { background: linear-gradient(135deg, #3b82f6, #60a5fa); }
        .icon-green { background: linear-gradient(135deg, #10b981, #34d399); }
        .icon-orange { background: linear-gradient(135deg, #f59e0b, #fbbf24); }
        .icon-red { background: linear-gradient(135deg, #ef4444, #f87171); }
        .icon-teal { background: linear-gradient(135deg, #14b8a6, #2dd4bf); }

        .section-title-wrap { flex: 1; }
        .section-label {
            font-size: 11px;
            font-weight: 600;
            color: var(--primary-light);
            text-transform: uppercase;
            letter-spacing: 0.1em;
            font-family: 'JetBrains Mono', monospace;
        }
        .section-title {
            font-size: 18px;
            font-weight: 700;
            color: #fff;
            margin-top: 0.2rem;
            line-height: 1.3;
        }

        .chevron {
            width: 20px; height: 20px;
            color: var(--text-dim);
            transition: transform 0.3s;
            flex-shrink: 0;
        }
        .section-block.collapsed .chevron { transform: rotate(-90deg); }
        .section-body { overflow: hidden; }
        .section-block.collapsed .section-body { display: none; }

        /* ── CONTENT STYLES ── */
        .prose p {
            font-size: 14.5px;
            line-height: 1.8;
            color: var(--text-muted);
            margin-bottom: 1rem;
        }
        .prose p:last-child { margin-bottom: 0; }

        .prose h3 {
            font-size: 15px;
            font-weight: 700;
            color: var(--text-main);
            margin: 1.5rem 0 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .prose h3::before {
            content: '';
            width: 4px; height: 18px;
            background: linear-gradient(180deg, var(--primary), var(--primary-light));
            border-radius: 2px;
            flex-shrink: 0;
        }

        /* DATA GRID for categories */
        .data-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1rem;
            margin: 1rem 0;
        }
        .data-card {
            background: rgba(121,77,253,0.07);
            border: 1px solid rgba(121,77,253,0.15);
            border-radius: 12px;
            padding: 1.25rem;
            transition: all 0.2s;
        }
        .data-card:hover {
            background: rgba(121,77,253,0.12);
            border-color: rgba(121,77,253,0.3);
            transform: translateY(-2px);
        }
        .data-card-title {
            font-size: 13px;
            font-weight: 700;
            color: var(--primary-light);
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .data-card-title svg { width: 14px; height: 14px; }
        .data-card ul { list-style: none; }
        .data-card ul li {
            font-size: 13px;
            color: var(--text-muted);
            padding: 0.3rem 0;
            border-bottom: 1px solid rgba(255,255,255,0.04);
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
            line-height: 1.5;
        }
        .data-card ul li::before {
            content: '›';
            color: var(--primary-light);
            font-weight: 700;
            flex-shrink: 0;
            margin-top: 0.1rem;
        }
        .data-card ul li:last-child { border-bottom: none; }

        /* LEGAL BASIS TABLE */
        .legal-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 0.5rem;
            margin: 1rem 0;
        }
        .legal-table tr td {
            padding: 0.85rem 1rem;
            font-size: 13.5px;
            color: var(--text-muted);
            background: rgba(255,255,255,0.03);
        }
        .legal-table tr td:first-child {
            border-radius: 8px 0 0 8px;
            font-family: 'JetBrains Mono', monospace;
            color: var(--primary-light);
            font-size: 12px;
            white-space: nowrap;
            width: 140px;
        }
        .legal-table tr td:last-child { border-radius: 0 8px 8px 0; }
        .legal-table tr:hover td { background: rgba(121,77,253,0.08); }

        /* PILL LIST */
        .pill-list {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin: 1rem 0;
        }
        .pill {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.4rem 0.9rem;
            border-radius: 100px;
            font-size: 13px;
            font-weight: 500;
            border: 1px solid;
        }
        .pill svg { width: 12px; height: 12px; }
        .pill-green { background: rgba(16,185,129,0.1); border-color: rgba(16,185,129,0.25); color: #34d399; }
        .pill-blue { background: rgba(59,130,246,0.1); border-color: rgba(59,130,246,0.25); color: #60a5fa; }
        .pill-orange { background: rgba(245,158,11,0.1); border-color: rgba(245,158,11,0.25); color: #fbbf24; }
        .pill-purple { background: rgba(121,77,253,0.1); border-color: rgba(121,77,253,0.25); color: var(--primary-light); }
        .pill-red { background: rgba(239,68,68,0.1); border-color: rgba(239,68,68,0.25); color: #f87171; }

        /* SECURITY SHIELDS */
        .security-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 0.75rem;
            margin: 1rem 0;
        }
        .security-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem;
            background: rgba(16,185,129,0.06);
            border: 1px solid rgba(16,185,129,0.15);
            border-radius: 10px;
            font-size: 13px;
            color: var(--text-muted);
            transition: all 0.2s;
        }
        .security-item:hover { background: rgba(16,185,129,0.1); transform: translateY(-1px); }
        .security-item svg { width:18px; height:18px; color:#34d399; flex-shrink:0; }

        /* RIGHTS LIST */
        .rights-list { list-style: none; display: flex; flex-direction: column; gap: 0.6rem; margin: 1rem 0; }
        .rights-item {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 0.85rem 1rem;
            background: rgba(121,77,253,0.05);
            border: 1px solid rgba(121,77,253,0.12);
            border-radius: 10px;
            font-size: 13.5px;
            color: var(--text-muted);
            transition: all 0.2s;
        }
        .rights-item:hover { background: rgba(121,77,253,0.1); }
        .rights-item svg { width:18px; height:18px; color: var(--primary-light); flex-shrink:0; margin-top:1px; }

        /* PURPOSE ROWS */
        .purpose-list { list-style: none; display: flex; flex-direction: column; gap: 0.5rem; margin: 1rem 0; }
        .purpose-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            border-radius: 10px;
            font-size: 13.5px;
            color: var(--text-muted);
            border-left: 3px solid;
            background: rgba(255,255,255,0.02);
            transition: background 0.2s;
        }
        .purpose-item:hover { background: rgba(255,255,255,0.05); }
        .purpose-item svg { width:16px; height:16px; flex-shrink:0; }
        .purpose-hr { border-left-color: var(--primary); }
        .purpose-hr svg { color: var(--primary-light); }
        .purpose-green { border-left-color: var(--success); }
        .purpose-green svg { color: #34d399; }
        .purpose-blue { border-left-color: var(--info); }
        .purpose-blue svg { color: #60a5fa; }

        /* OBLIGATION LIST */
        .obligation-list { list-style: none; display: flex; flex-direction: column; gap: 0.5rem; margin: 1rem 0; }
        .obligation-item {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            font-size: 13.5px;
            color: var(--text-muted);
            border-radius: 8px;
            background: rgba(239,68,68,0.04);
            border: 1px solid rgba(239,68,68,0.1);
            transition: all 0.2s;
        }
        .obligation-item:hover { background: rgba(239,68,68,0.08); }
        .obligation-item svg { width:16px; height:16px; color: #f87171; flex-shrink:0; margin-top:2px; }

        /* DIVIDER */
        .section-divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--border), transparent);
            margin: 1.5rem 0;
        }

        /* INFO BOX */
        .info-box {
            display: flex;
            gap: 0.75rem;
            padding: 1rem 1.25rem;
            border-radius: 12px;
            margin: 1rem 0;
            font-size: 13.5px;
            line-height: 1.6;
        }
        .info-box svg { width: 18px; height: 18px; flex-shrink: 0; margin-top: 1px; }
        .info-box-purple { background: rgba(121,77,253,0.1); border: 1px solid rgba(121,77,253,0.2); color: var(--text-muted); }
        .info-box-purple svg { color: var(--primary-light); }
        .info-box-blue { background: rgba(59,130,246,0.08); border: 1px solid rgba(59,130,246,0.2); color: var(--text-muted); }
        .info-box-blue svg { color: #60a5fa; }

        /* CONTACT CARD */
        .contact-card {
            background: linear-gradient(135deg, rgba(121,77,253,0.15), rgba(59,130,246,0.1));
            border: 1px solid rgba(121,77,253,0.3);
            border-radius: 16px;
            padding: 1.75rem;
            text-align: center;
            margin-top: 2rem;
        }
        .contact-card h3 { color: #fff; font-size: 17px; margin-bottom: 0.75rem; }
        .contact-card p { color: var(--text-muted); font-size: 14px; margin-bottom: 1rem; }
        .contact-links { display: flex; justify-content: center; gap: 1rem; flex-wrap: wrap; }
        .contact-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 1.4rem;
            border-radius: 100px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
        }
        .contact-link-primary {
            background: linear-gradient(135deg, #794DFD, #9d6fff);
            color: #fff;
            box-shadow: 0 4px 16px rgba(121,77,253,0.3);
        }
        .contact-link-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(121,77,253,0.45); }
        .contact-link-outline {
            border: 1px solid var(--border);
            color: var(--text-muted);
        }
        .contact-link-outline:hover { border-color: var(--primary); color: var(--primary-light); }
        .contact-link svg { width: 16px; height: 16px; }

        /* PROGRESS BAR (reading) */
        #reading-progress {
            position: fixed;
            top: 0; left: 0;
            width: 0%; height: 3px;
            background: linear-gradient(90deg, #794DFD, #9d6fff);
            z-index: 100;
            transition: width 0.1s;
        }

        /* BACK TO TOP */
        #back-top {
            position: fixed;
            bottom: 2rem; right: 2rem;
            width: 44px; height: 44px;
            border-radius: 12px;
            background: linear-gradient(135deg, #794DFD, #9d6fff);
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 4px 16px rgba(121,77,253,0.4);
            opacity: 0;
            transform: translateY(10px);
            transition: all 0.3s;
            z-index: 99;
        }
        #back-top.visible { opacity: 1; transform: translateY(0); }
        #back-top:hover { transform: translateY(-2px); }
        #back-top svg { width: 18px; height: 18px; color: #fff; }

        /* ANIMATIONS */
        @keyframes fadeUp { from { opacity:0; transform: translateY(24px); } to { opacity:1; transform: translateY(0); } }

        .section-block:nth-child(1) { animation-delay: 0.05s; }
        .section-block:nth-child(2) { animation-delay: 0.1s; }
        .section-block:nth-child(3) { animation-delay: 0.15s; }
        .section-block:nth-child(4) { animation-delay: 0.2s; }
        .section-block:nth-child(5) { animation-delay: 0.25s; }
        .section-block:nth-child(6) { animation-delay: 0.3s; }
        .section-block:nth-child(7) { animation-delay: 0.35s; }
        .section-block:nth-child(8) { animation-delay: 0.4s; }
        .section-block:nth-child(9) { animation-delay: 0.45s; }

        /* FOOTER */
        .page-footer {
            text-align: center;
            padding: 2rem 0;
            font-size: 13px;
            color: var(--text-dim);
            border-top: 1px solid var(--border-light);
            margin-top: 3rem;
        }
        .page-footer a { color: var(--primary-light); text-decoration: none; }
    </style>
</head>
<body>

<div id="reading-progress"></div>
<div class="bg-mesh"></div>
<div class="bg-grid"></div>

<div class="page-wrap">

    <!-- HERO -->
    <div class="hero">
        <div class="hero-badge">
            <span class="dot"></span>
            Публичный документ
        </div>
        <h1>
            Политика конфиденциальности
            <span>и Пользовательское соглашение EdoLine</span>
        </h1>
        <div class="hero-meta">
            <div class="hero-meta-item">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                Соответствует GDPR
            </div>
            <div class="hero-meta-item">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064"/></svg>
                Закон РУз «О персональных данных»
            </div>
            <div class="hero-meta-item">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                App Store & Google Play
            </div>
        </div>
    </div>

    <!-- TAB NAVIGATION -->
    <nav class="tab-nav">
        <button class="tab-btn active" onclick="switchTab('privacy', this)">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            Политика конфиденциальности
        </button>
        <button class="tab-btn" onclick="switchTab('agreement', this)">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Пользовательское соглашение
        </button>
    </nav>

    <!-- PRIVACY TAB -->
    <div id="tab-privacy" class="tab-panel active">
        <div class="content-layout">

            <!-- SIDEBAR TOC -->
            <aside class="toc-sidebar">
                <div class="toc-title">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7"/></svg>
                    Содержание
                </div>
                <ul class="toc-list">
                    <li class="toc-item"><a href="#p1" class="toc-link"><span class="toc-num">1</span> Введение</a></li>
                    <li class="toc-item"><a href="#p2" class="toc-link"><span class="toc-num">2</span> Обрабатываемые данные</a></li>
                    <li class="toc-item"><a href="#p3" class="toc-link"><span class="toc-num">3</span> Источники данных</a></li>
                    <li class="toc-item"><a href="#p4" class="toc-link"><span class="toc-num">4</span> Правовые основания</a></li>
                    <li class="toc-item"><a href="#p5" class="toc-link"><span class="toc-num">5</span> Цели обработки</a></li>
                    <li class="toc-item"><a href="#p6" class="toc-link"><span class="toc-num">6</span> Передача данных</a></li>
                    <li class="toc-item"><a href="#p7" class="toc-link"><span class="toc-num">7</span> Хранение и защита</a></li>
                    <li class="toc-item"><a href="#p8" class="toc-link"><span class="toc-num">8</span> Права пользователя</a></li>
                    <li class="toc-item"><a href="#p9" class="toc-link"><span class="toc-num">9</span> Cookies</a></li>
                    <li class="toc-item"><a href="#p10" class="toc-link"><span class="toc-num">10</span> Изменения политики</a></li>
                </ul>
            </aside>

            <!-- MAIN CONTENT -->
            <div class="sections-main">

                <!-- §1 Введение -->
                <div class="section-block" id="p1">
                    <div class="section-header" onclick="toggleSection(this)">
                        <div class="section-icon icon-purple">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div class="section-title-wrap">
                            <div class="section-label">§ 1.1</div>
                            <div class="section-title">Введение</div>
                        </div>
                        <svg class="chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                    <div class="section-body">
                        <div class="prose">
                            <p>Настоящая Политика конфиденциальности регулирует порядок обработки данных пользователей приложения <strong style="color:#fff">EdoLine</strong>, функционирующего как корпоративная SaaS‑платформа для автоматизации HR‑процессов и внутренних процессов документооборота.</p>
                            <p>Приложение предоставляет доступ к персональным, организационным и кадровым данным, размещённым работодателем.</p>
                            <div class="info-box info-box-purple">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Используя приложение, пользователь подтверждает, что ознакомлен с Политикой и принимает её условия.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- §2 Обрабатываемые данные -->
                <div class="section-block" id="p2">
                    <div class="section-header" onclick="toggleSection(this)">
                        <div class="section-icon icon-blue">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/></svg>
                        </div>
                        <div class="section-title-wrap">
                            <div class="section-label">§ 2</div>
                            <div class="section-title">Категории обрабатываемых данных</div>
                        </div>
                        <svg class="chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                    <div class="section-body">
                        <div class="prose">
                            <p>EdoLine обрабатывает корпоративные данные сотрудников, предоставленные работодателем, а также сведения, введённые пользователем самостоятельно.</p>
                        </div>
                        <div class="data-grid">
                            <div class="data-card">
                                <div class="data-card-title">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    2.1.1 Личные данные
                                </div>
                                <ul>
                                    <li>ФИО, дата рождения, пол</li>
                                    <li>ИНН, ПИНФЛ</li>
                                    <li>Паспортные данные</li>
                                    <li>Адрес регистрации / проживания</li>
                                </ul>
                            </div>
                            <div class="data-card">
                                <div class="data-card-title">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                    2.1.2 Корпоративные данные
                                </div>
                                <ul>
                                    <li>Место работы, должность</li>
                                    <li>Трудовой договор</li>
                                    <li>Табель учёта рабочего времени</li>
                                    <li>Отпуска, командировки</li>
                                    <li>Электронные заявки</li>
                                    <li>Данные о начислениях</li>
                                </ul>
                            </div>
                            <div class="data-card">
                                <div class="data-card-title">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    2.1.3 Контактные данные
                                </div>
                                <ul>
                                    <li>Корпоративный e‑mail</li>
                                    <li>Номер телефона</li>
                                    <li>Альтернативный e‑mail</li>
                                    <li>Логины, токены, SSO‑ID</li>
                                </ul>
                            </div>
                            <div class="data-card">
                                <div class="data-card-title">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2v-4M9 21H5a2 2 0 01-2-2v-4m0 0h18"/></svg>
                                    2.1.4 Техническая информация
                                </div>
                                <ul>
                                    <li>IP‑адрес, ID устройства</li>
                                    <li>Версия ОС, модель телефона</li>
                                    <li>Логи ошибок и сбоев</li>
                                    <li>Cookies, аналитика</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- §3 Источники данных -->
                <div class="section-block" id="p3">
                    <div class="section-header" onclick="toggleSection(this)">
                        <div class="section-icon icon-teal">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        </div>
                        <div class="section-title-wrap">
                            <div class="section-label">§ 3</div>
                            <div class="section-title">Источники данных</div>
                        </div>
                        <svg class="chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                    <div class="section-body">
                        <div class="pill-list">
              <span class="pill pill-purple">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16"/></svg>
                Работодатель (API, ERP, HRM)
              </span>
                            <span class="pill pill-blue">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                Пользователь (формы, заявления)
              </span>
                            <span class="pill pill-orange">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2"/></svg>
                Автосбор технических данных
              </span>
                        </div>
                        <div class="info-box info-box-blue">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Работодатель является контролёром данных в значении GDPR. Большинство данных загружается через корпоративные интеграции.
                        </div>
                    </div>
                </div>

                <!-- §4 Правовые основания -->
                <div class="section-block" id="p4">
                    <div class="section-header" onclick="toggleSection(this)">
                        <div class="section-icon icon-orange">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
                        </div>
                        <div class="section-title-wrap">
                            <div class="section-label">§ 4</div>
                            <div class="section-title">Правовые основания обработки</div>
                        </div>
                        <svg class="chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                    <div class="section-body">
                        <div class="prose"><h3>4.1. Соответствие GDPR</h3></div>
                        <table class="legal-table">
                            <tr><td>Art. 6(1)(b)</td><td>Выполнение трудовых и корпоративных обязательств</td></tr>
                            <tr><td>Art. 6(1)(f)</td><td>Законный интерес работодателя</td></tr>
                            <tr><td>Art. 6(1)(c)</td><td>Выполнение юридических обязательств</td></tr>
                            <tr><td>Art. 6(1)(a)</td><td>Согласие пользователя (при необходимости)</td></tr>
                        </table>
                        <div class="prose"><h3>4.2. Закон РУз «О персональных данных»</h3></div>
                        <div class="security-grid" style="--success:#a78bfa">
                            <div class="security-item" style="border-color:rgba(121,77,253,0.2);background:rgba(121,77,253,0.05);">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="color:var(--primary-light)"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Согласие субъекта данных
                            </div>
                            <div class="security-item" style="border-color:rgba(121,77,253,0.2);background:rgba(121,77,253,0.05);">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="color:var(--primary-light)"><path stroke-linecap="round" stroke-linejoin="round" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945"/></svg>
                                Локализация данных в РУз
                            </div>
                            <div class="security-item" style="border-color:rgba(121,77,253,0.2);background:rgba(121,77,253,0.05);">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="color:var(--primary-light)"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                Уведомление субъекта
                            </div>
                            <div class="security-item" style="border-color:rgba(121,77,253,0.2);background:rgba(121,77,253,0.05);">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="color:var(--primary-light)"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                Регистрация операторов
                            </div>
                        </div>
                    </div>
                </div>

                <!-- §5 Цели обработки -->
                <div class="section-block" id="p5">
                    <div class="section-header" onclick="toggleSection(this)">
                        <div class="section-icon icon-green">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        </div>
                        <div class="section-title-wrap">
                            <div class="section-label">§ 5</div>
                            <div class="section-title">Цели обработки данных</div>
                        </div>
                        <svg class="chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                    <div class="section-body">
                        <ul class="purpose-list">
                            <li class="purpose-item purpose-hr">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                Предоставление доступа к личному кабинету сотрудника
                            </li>
                            <li class="purpose-item purpose-hr">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                Ведение электронных заявлений, кадровых документов и согласований
                            </li>
                            <li class="purpose-item purpose-blue">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                Отображение расписания, графиков, табелей и расчётных листков
                            </li>
                            <li class="purpose-item purpose-blue">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                Внутренний документооборот между сотрудником и работодателем
                            </li>
                            <li class="purpose-item purpose-green">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                Аналитика работы приложения и повышение стабильности сервиса
                            </li>
                            <li class="purpose-item purpose-green">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                Идентификация, аутентификация и информационная безопасность
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- §6 Передача данных -->
                <div class="section-block" id="p6">
                    <div class="section-header" onclick="toggleSection(this)">
                        <div class="section-icon icon-blue">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                        </div>
                        <div class="section-title-wrap">
                            <div class="section-label">§ 6</div>
                            <div class="section-title">Передача и доступ к данным</div>
                        </div>
                        <svg class="chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                    <div class="section-body">
                        <div class="prose"><h3>6.1. Кому могут передаваться данные</h3></div>
                        <div class="pill-list">
                            <span class="pill pill-blue">Работодатель (основной владелец)</span>
                            <span class="pill pill-purple">Провайдеры облачной инфраструктуры</span>
                            <span class="pill pill-orange">Государственные органы (по закону)</span>
                            <span class="pill pill-green">Партнёры по интеграции (SSO, ERP)</span>
                        </div>
                        <div class="section-divider"></div>
                        <div class="prose"><h3>6.2. Данные НЕ передаются</h3></div>
                        <div class="pill-list">
              <span class="pill pill-red">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                Маркетинговым платформам
              </span>
                            <span class="pill pill-red">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                Внешним компаниям без запроса работодателя
              </span>
                            <span class="pill pill-red">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                Третьим лицам в коммерческих целях
              </span>
                        </div>
                    </div>
                </div>

                <!-- §7 Хранение и защита -->
                <div class="section-block" id="p7">
                    <div class="section-header" onclick="toggleSection(this)">
                        <div class="section-icon icon-green">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        </div>
                        <div class="section-title-wrap">
                            <div class="section-label">§ 7</div>
                            <div class="section-title">Хранение и защита данных</div>
                        </div>
                        <svg class="chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                    <div class="section-body">
                        <div class="security-grid">
                            <div class="security-item">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                HTTPS / TLS шифрование
                            </div>
                            <div class="security-item">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                                Токенизация паролей
                            </div>
                            <div class="security-item">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                Доступ по ролям
                            </div>
                            <div class="security-item">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                Журналы аудита
                            </div>
                            <div class="security-item">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064"/></svg>
                                Серверы в РУз (если требуется)
                            </div>
                            <div class="security-item">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                Удаление по запросу
                            </div>
                        </div>
                    </div>
                </div>

                <!-- §8 Права пользователя -->
                <div class="section-block" id="p8">
                    <div class="section-header" onclick="toggleSection(this)">
                        <div class="section-icon icon-purple">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div class="section-title-wrap">
                            <div class="section-label">§ 8</div>
                            <div class="section-title">Права пользователя</div>
                        </div>
                        <svg class="chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                    <div class="section-body">
                        <ul class="rights-list">
                            <li class="rights-item">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                Право на доступ к своим данным
                            </li>
                            <li class="rights-item">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                Право на корректировку данных
                            </li>
                            <li class="rights-item">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                Право на ограничение обработки
                            </li>
                            <li class="rights-item">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                Право на удаление (при отсутствии юридических препятствий)
                            </li>
                            <li class="rights-item">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                                Право на отзыв согласия
                            </li>
                            <li class="rights-item">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Право на получение информации о целях обработки
                            </li>
                        </ul>
                        <div class="info-box info-box-purple">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Для реализации прав пользователь обращается к работодателю как к контролёру данных.
                        </div>
                    </div>
                </div>

                <!-- §9 Cookies -->
                <div class="section-block" id="p9">
                    <div class="section-header" onclick="toggleSection(this)">
                        <div class="section-icon icon-teal">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
                        </div>
                        <div class="section-title-wrap">
                            <div class="section-label">§ 9</div>
                            <div class="section-title">Cookies и аналитика</div>
                        </div>
                        <svg class="chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                    <div class="section-body">
                        <div class="prose">
                            <p>Приложение может использовать технические cookies и SDK аналитики производительности для обеспечения стабильной работы.</p>
                        </div>
                        <div class="pill-list">
                            <span class="pill pill-blue">Технические cookies</span>
                            <span class="pill pill-purple">Crashlytics</span>
                            <span class="pill pill-purple">Firebase Performance</span>
                        </div>
                        <div class="info-box info-box-blue">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Аналитика <strong style="color:#fff">не используется</strong> для рекламных целей.
                        </div>
                    </div>
                </div>

                <!-- §10 Изменения -->
                <div class="section-block" id="p10">
                    <div class="section-header" onclick="toggleSection(this)">
                        <div class="section-icon icon-orange">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        </div>
                        <div class="section-title-wrap">
                            <div class="section-label">§ 10</div>
                            <div class="section-title">Изменения политики</div>
                        </div>
                        <svg class="chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                    <div class="section-body">
                        <div class="prose">
                            <p>Компания оставляет за собой право обновлять Политику конфиденциальности. Все изменения публикуются в приложении и на официальном сайте EdoLine.uz.</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- AGREEMENT TAB -->
    <div id="tab-agreement" class="tab-panel">
        <div class="content-layout">
            <aside class="toc-sidebar">
                <div class="toc-title">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7"/></svg>
                    Разделы
                </div>
                <ul class="toc-list">
                    <li class="toc-item"><a href="#a1"><span class="toc-num">1</span> Общие положения</a></li>
                    <li class="toc-item"><a href="#a2"><span class="toc-num">2</span> Условия использования</a></li>
                    <li class="toc-item"><a href="#a3"><span class="toc-num">3</span> Ответственность</a></li>
                    <li class="toc-item"><a href="#a4"><span class="toc-num">4</span> SaaS‑модель</a></li>
                    <li class="toc-item"><a href="#a5"><span class="toc-num">5</span> Платные услуги</a></li>
                    <li class="toc-item"><a href="#a6"><span class="toc-num">6</span> Интеллектуальная собственность</a></li>
                    <li class="toc-item"><a href="#a7"><span class="toc-num">7</span> Прекращение доступа</a></li>
                    <li class="toc-item"><a href="#a8"><span class="toc-num">8</span> Контакты</a></li>
                </ul>
            </aside>

            <div class="sections-main">

                <div class="section-block" id="a1">
                    <div class="section-header" onclick="toggleSection(this)">
                        <div class="section-icon icon-purple">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <div class="section-title-wrap">
                            <div class="section-label">§ 1</div>
                            <div class="section-title">Общие положения</div>
                        </div>
                        <svg class="chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                    <div class="section-body">
                        <div class="prose">
                            <p>Настоящее Соглашение регулирует использование приложения <strong style="color:#fff">EdoLine</strong> как корпоративного SaaS‑решения.</p>
                        </div>
                        <div class="info-box info-box-purple">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            Доступ в приложение предоставляется исключительно сотрудникам компаний‑клиентов EdoLine.
                        </div>
                    </div>
                </div>

                <div class="section-block" id="a2">
                    <div class="section-header" onclick="toggleSection(this)">
                        <div class="section-icon icon-blue">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        </div>
                        <div class="section-title-wrap">
                            <div class="section-label">§ 2</div>
                            <div class="section-title">Условия использования</div>
                        </div>
                        <svg class="chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                    <div class="section-body">
                        <div class="prose"><p>Пользователь обязуется:</p></div>
                        <ul class="rights-list">
                            <li class="rights-item">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Использовать приложение только в корпоративных целях
                            </li>
                            <li class="rights-item">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Не нарушать функционирование сервиса
                            </li>
                            <li class="rights-item">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Не пытаться получить несанкционированный доступ к чужим данным
                            </li>
                            <li class="rights-item">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Соблюдать требования информационной безопасности
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="section-block" id="a3">
                    <div class="section-header" onclick="toggleSection(this)">
                        <div class="section-icon icon-red">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </div>
                        <div class="section-title-wrap">
                            <div class="section-label">§ 3</div>
                            <div class="section-title">Ограничение ответственности</div>
                        </div>
                        <svg class="chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                    <div class="section-body">
                        <div class="prose"><p>EdoLine не несёт ответственность за:</p></div>
                        <ul class="obligation-list">
                            <li class="obligation-item">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                Корректность данных, предоставленных работодателем
                            </li>
                            <li class="obligation-item">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                Сбои инфраструктуры третьих сторон
                            </li>
                            <li class="obligation-item">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                Последствия использования устаревших версий приложения
                            </li>
                            <li class="obligation-item">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                Ошибки интеграции со стороны работодателя
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="section-block" id="a4">
                    <div class="section-header" onclick="toggleSection(this)">
                        <div class="section-icon icon-teal">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        </div>
                        <div class="section-title-wrap">
                            <div class="section-label">§ 4</div>
                            <div class="section-title">SaaS‑модель сервиса</div>
                        </div>
                        <svg class="chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                    <div class="section-body">
                        <div class="security-grid">
                            <div class="security-item" style="border-color:rgba(20,184,166,0.25);background:rgba(20,184,166,0.06);">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="color:#2dd4bf"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                Изолированный контур данных для каждой организации
                            </div>
                            <div class="security-item" style="border-color:rgba(20,184,166,0.25);background:rgba(20,184,166,0.06);">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="color:#2dd4bf"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                Работодатель назначает администраторов
                            </div>
                            <div class="security-item" style="border-color:rgba(20,184,166,0.25);background:rgba(20,184,166,0.06);">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="color:#2dd4bf"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                EdoLine: платформа, обновления, безопасность, поддержка
                            </div>
                        </div>
                    </div>
                </div>

                <div class="section-block" id="a5">
                    <div class="section-header" onclick="toggleSection(this)">
                        <div class="section-icon icon-green">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div class="section-title-wrap">
                            <div class="section-label">§ 5</div>
                            <div class="section-title">Платные услуги</div>
                        </div>
                        <svg class="chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                    <div class="section-body">
                        <div class="prose">
                            <p>Приложение <strong style="color:#fff">не продаёт подписок</strong> физическим лицам. Все расчёты осуществляются между EdoLine и компанией‑клиентом по договору B2B.</p>
                        </div>
                    </div>
                </div>

                <div class="section-block" id="a6">
                    <div class="section-header" onclick="toggleSection(this)">
                        <div class="section-icon icon-purple">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                        </div>
                        <div class="section-title-wrap">
                            <div class="section-label">§ 6</div>
                            <div class="section-title">Интеллектуальная собственность</div>
                        </div>
                        <svg class="chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                    <div class="section-body">
                        <div class="prose">
                            <p>Код, интерфейс, дизайн и логотип EdoLine являются интеллектуальной собственностью правообладателя.</p>
                        </div>
                        <div class="pill-list">
              <span class="pill pill-red">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                Копирование запрещено
              </span>
                            <span class="pill pill-red">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                Модификация запрещена
              </span>
                        </div>
                    </div>
                </div>

                <div class="section-block" id="a7">
                    <div class="section-header" onclick="toggleSection(this)">
                        <div class="section-icon icon-red">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        </div>
                        <div class="section-title-wrap">
                            <div class="section-label">§ 7</div>
                            <div class="section-title">Прекращение доступа</div>
                        </div>
                        <svg class="chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                    <div class="section-body">
                        <div class="prose"><p>Доступ пользователя прекращается:</p></div>
                        <div class="pill-list">
                            <span class="pill pill-orange">При увольнении сотрудника</span>
                            <span class="pill pill-orange">По решению работодателя</span>
                            <span class="pill pill-red">При нарушении Соглашения</span>
                        </div>
                    </div>
                </div>

                <div class="section-block" id="a8">
                    <div class="section-header" onclick="toggleSection(this)">
                        <div class="section-icon icon-purple">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                        <div class="section-title-wrap">
                            <div class="section-label">§ 8</div>
                            <div class="section-title">Контакты</div>
                        </div>
                        <svg class="chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                    <div class="section-body">
                        <div class="contact-card">
                            <h3>Свяжитесь с нами</h3>
                            <p>По вопросам политики конфиденциальности и пользовательского соглашения</p>
                            <div class="contact-links">
                                <a href="mailto:support@EdoLine.uz" class="contact-link contact-link-primary">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    support@EdoLine.uz
                                </a>
                                <a href="https://EdoLine.uz" target="_blank" class="contact-link contact-link-outline">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    EdoLine.uz
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- FOOTER -->
    <footer class="page-footer">
        © 2025 EdoLine (EdoLine.uz) — Разработано с учётом GDPR и Закона РУз «О персональных данных».
        Для публикации в <a href="#">App Store</a> и <a href="#">Google Play</a>.
    </footer>

</div>

<button id="back-top" onclick="window.scrollTo({top:0,behavior:'smooth'})" title="Наверх">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/></svg>
</button>

<script>
    // Reading progress
    window.addEventListener('scroll', () => {
        const el = document.documentElement;
        const progress = (el.scrollTop / (el.scrollHeight - el.clientHeight)) * 100;
        document.getElementById('reading-progress').style.width = progress + '%';
        const btn = document.getElementById('back-top');
        el.scrollTop > 400 ? btn.classList.add('visible') : btn.classList.remove('visible');
        updateActiveTOC();
    });

    // Tab switching
    function switchTab(name, btn) {
        document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.getElementById('tab-' + name).classList.add('active');
        btn.classList.add('active');
    }

    // Section collapse
    function toggleSection(header) {
        header.parentElement.classList.toggle('collapsed');
    }

    // Active TOC highlighting
    function updateActiveTOC() {
        const links = document.querySelectorAll('.toc-link, .toc-item a');
        links.forEach(link => {
            const href = link.getAttribute('href');
            if (!href || !href.startsWith('#')) return;
            const target = document.querySelector(href);
            if (!target) return;
            const rect = target.getBoundingClientRect();
            if (rect.top <= 120 && rect.bottom >= 0) {
                links.forEach(l => l.classList.remove('active'));
                link.classList.add('active');
            }
        });
    }
</script>

</body>
</html>
