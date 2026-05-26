<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#0a0e1a">
    <title>E-IMZO — Imzolash</title>
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
    <style>
        /* ════════════════════════════════════════════════════════════════════
           Design tokens — Telegram theme aware + custom palette
           ════════════════════════════════════════════════════════════════════ */
        :root {
            /* Surfaces */
            --surface-0:   var(--tg-theme-bg-color, #f5f7fb);
            --surface-1:   rgba(255, 255, 255, 0.72);
            --surface-2:   var(--tg-theme-secondary-bg-color, #ffffff);
            --surface-blur: 24px;

            /* Text */
            --text:        var(--tg-theme-text-color, #0a0e1a);
            --text-muted:  var(--tg-theme-hint-color, #64748b);
            --text-subtle: rgba(10, 14, 26, 0.55);

            /* Brand & accents */
            --brand-1:     #3b82f6;     /* blue-500 */
            --brand-2:     #8b5cf6;     /* violet-500 */
            --brand-3:     #ec4899;     /* pink-500 */
            --success:     #10b981;
            --success-2:   #059669;
            --warning:     #f59e0b;
            --danger:      #ef4444;

            /* Borders & shadows */
            --border:      rgba(15, 23, 42, 0.06);
            --border-glass: rgba(255, 255, 255, 0.45);
            --shadow-1:    0 0 0 1px rgba(15, 23, 42, 0.04), 0 2px 4px rgba(15, 23, 42, 0.04);
            --shadow-2:    0 0 0 1px rgba(15, 23, 42, 0.04), 0 4px 12px -2px rgba(15, 23, 42, 0.08), 0 24px 40px -16px rgba(15, 23, 42, 0.18);
            --shadow-glow: 0 0 0 1px rgba(255, 255, 255, 0.12), 0 12px 32px -8px rgba(59, 130, 246, 0.45);

            /* Geometry */
            --r-sm: 12px;
            --r-md: 18px;
            --r-lg: 24px;

            /* Easing */
            --spring: cubic-bezier(0.34, 1.56, 0.64, 1);
            --smooth: cubic-bezier(0.22, 1, 0.36, 1);
        }

        /* Dark mode adjustments — Telegram dark theme */
        @media (prefers-color-scheme: dark) {
            :root {
                --surface-0:   var(--tg-theme-bg-color, #0f1629);
                --surface-1:   rgba(30, 41, 59, 0.72);
                --surface-2:   var(--tg-theme-secondary-bg-color, #1e293b);
                --text:        var(--tg-theme-text-color, #f1f5f9);
                --text-muted:  var(--tg-theme-hint-color, #94a3b8);
                --text-subtle: rgba(241, 245, 249, 0.55);
                --border:      rgba(255, 255, 255, 0.08);
                --border-glass: rgba(255, 255, 255, 0.10);
                --shadow-1:    0 0 0 1px rgba(255, 255, 255, 0.05), 0 2px 4px rgba(0, 0, 0, 0.3);
                --shadow-2:    0 0 0 1px rgba(255, 255, 255, 0.05), 0 4px 12px rgba(0, 0, 0, 0.4), 0 24px 40px -16px rgba(0, 0, 0, 0.6);
            }
        }

        /* ════════════════════════════════════════════════════════════════════
           Reset & base
           ════════════════════════════════════════════════════════════════════ */
        *, *::before, *::after {
            box-sizing: border-box; margin: 0; padding: 0;
            -webkit-tap-highlight-color: transparent;
        }

        html, body {
            background: var(--surface-0);
            color: var(--text);
            min-height: 100vh;
            min-height: 100dvh;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'SF Pro Text', 'SF Pro Display',
            'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            font-size: 14px;
            line-height: 1.5;
            font-feature-settings: 'cv02', 'cv03', 'cv04', 'cv11';
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            text-rendering: optimizeLegibility;
            position: relative;
            overflow-x: hidden;
            padding-bottom: env(safe-area-inset-bottom);
        }

        /* ════════════════════════════════════════════════════════════════════
           Animated background blobs
           ════════════════════════════════════════════════════════════════════ */
        .bg-blobs {
            position: fixed; inset: 0; z-index: -1;
            overflow: hidden;
            pointer-events: none;
        }
        .blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(60px);
            opacity: 0.55;
            will-change: transform;
        }
        .blob-1 {
            width: 360px; height: 360px;
            top: -120px; right: -80px;
            background: radial-gradient(circle, var(--brand-1), transparent 70%);
            animation: float-1 18s ease-in-out infinite;
        }
        .blob-2 {
            width: 320px; height: 320px;
            top: 40%; left: -80px;
            background: radial-gradient(circle, var(--brand-2), transparent 70%);
            animation: float-2 22s ease-in-out infinite;
        }
        .blob-3 {
            width: 280px; height: 280px;
            bottom: -80px; right: 30%;
            background: radial-gradient(circle, var(--brand-3), transparent 70%);
            animation: float-3 26s ease-in-out infinite;
            opacity: 0.35;
        }
        @media (prefers-color-scheme: dark) {
            .blob { opacity: 0.30; }
            .blob-3 { opacity: 0.18; }
        }
        @keyframes float-1 {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50%      { transform: translate(-30px, 40px) scale(1.1); }
        }
        @keyframes float-2 {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50%      { transform: translate(40px, -30px) scale(0.9); }
        }
        @keyframes float-3 {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50%      { transform: translate(-40px, -50px) scale(1.15); }
        }

        /* ════════════════════════════════════════════════════════════════════
           Top bar — frosted glass with step indicator
           ════════════════════════════════════════════════════════════════════ */
        .top-bar {
            position: sticky; top: 0; z-index: 50;
            padding: 10px 16px 12px;
            padding-top: max(10px, env(safe-area-inset-top));
            background: linear-gradient(180deg,
            rgba(255, 255, 255, 0.85) 0%,
            rgba(255, 255, 255, 0.65) 100%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            backdrop-filter: blur(20px) saturate(180%);
            border-bottom: 1px solid var(--border);
        }
        @media (prefers-color-scheme: dark) {
            .top-bar {
                background: linear-gradient(180deg,
                rgba(15, 22, 41, 0.85) 0%,
                rgba(15, 22, 41, 0.65) 100%);
            }
        }

        .top-bar-row {
            display: flex; align-items: center; gap: 12px;
            margin-bottom: 12px;
        }
        .brand {
            display: flex; align-items: center; gap: 10px;
            font-weight: 700; font-size: 15px;
            letter-spacing: -0.01em;
            color: var(--text);
        }
        .brand-icon {
            width: 32px; height: 32px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, var(--brand-1), var(--brand-2));
            color: #fff;
            box-shadow: 0 6px 16px -4px rgba(59, 130, 246, 0.5);
        }
        .brand-icon svg { width: 16px; height: 16px; }

        /* Step indicator */
        .steps {
            display: flex; align-items: center; gap: 6px;
        }
        .step {
            flex: 1;
            height: 4px;
            background: rgba(15, 23, 42, 0.08);
            border-radius: 999px;
            position: relative;
            overflow: hidden;
            transition: background 0.4s var(--smooth);
        }
        @media (prefers-color-scheme: dark) {
            .step { background: rgba(255, 255, 255, 0.08); }
        }
        .step.done {
            background: linear-gradient(90deg, var(--brand-1), var(--brand-2));
        }
        .step.active {
            background: linear-gradient(90deg, var(--brand-1), var(--brand-2));
            box-shadow: 0 0 12px -2px rgba(59, 130, 246, 0.6);
        }
        .step.active::after {
            content: "";
            position: absolute; inset: 0;
            background: linear-gradient(90deg,
            transparent 0%,
            rgba(255, 255, 255, 0.7) 50%,
            transparent 100%);
            animation: step-shimmer 1.6s linear infinite;
        }
        @keyframes step-shimmer {
            from { transform: translateX(-100%); }
            to   { transform: translateX(100%); }
        }

        /* ════════════════════════════════════════════════════════════════════
           Cards — glassmorphism
           ════════════════════════════════════════════════════════════════════ */
        .card {
            background: var(--surface-1);
            -webkit-backdrop-filter: blur(var(--surface-blur)) saturate(180%);
            backdrop-filter: blur(var(--surface-blur)) saturate(180%);
            border: 1px solid var(--border-glass);
            border-radius: var(--r-lg);
            padding: 18px;
            margin: 14px 14px 0;
            box-shadow: var(--shadow-2);

            opacity: 0;
            transform: translateY(16px) scale(0.98);
            animation: card-in 0.55s var(--spring) forwards;
        }
        .card:nth-of-type(1) { animation-delay: 0.05s; }
        .card:nth-of-type(2) { animation-delay: 0.12s; }
        .card:nth-of-type(3) { animation-delay: 0.19s; }
        .card:nth-of-type(4) { animation-delay: 0.26s; }
        .card:nth-of-type(5) { animation-delay: 0.33s; }

        @keyframes card-in {
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        .label {
            display: flex; align-items: center; gap: 6px;
            font-size: 10px; font-weight: 700;
            color: var(--text-subtle);
            text-transform: uppercase; letter-spacing: 0.08em;
            margin-bottom: 12px;
        }
        .label svg { width: 12px; height: 12px; opacity: 0.7; }

        /* ════════════════════════════════════════════════════════════════════
           Document card
           ════════════════════════════════════════════════════════════════════ */
        .doc-name {
            font-size: 18px;
            font-weight: 700;
            color: var(--text);
            line-height: 1.35;
            letter-spacing: -0.02em;
            margin-bottom: 6px;
        }
        .doc-meta {
            display: flex; align-items: center; gap: 6px;
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 14px;
        }
        .doc-meta-dot {
            width: 3px; height: 3px;
            border-radius: 50%;
            background: var(--text-muted);
            opacity: 0.5;
        }

        .role-badge {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 7px 13px;
            border-radius: 999px;
            font-size: 12px; font-weight: 600;
            letter-spacing: -0.005em;
            position: relative;
            transition: transform 0.2s var(--smooth);
        }
        .role-SIGN  {
            background: rgba(59, 130, 246, 0.10);
            color: #2563eb;
            border: 1px solid rgba(59, 130, 246, 0.20);
        }
        .role-RESOL {
            background: rgba(139, 92, 246, 0.10);
            color: #7c3aed;
            border: 1px solid rgba(139, 92, 246, 0.20);
        }
        .role-AGREE {
            background: rgba(16, 185, 129, 0.10);
            color: #059669;
            border: 1px solid rgba(16, 185, 129, 0.20);
        }
        @media (prefers-color-scheme: dark) {
            .role-SIGN  { color: #60a5fa; }
            .role-RESOL { color: #a78bfa; }
            .role-AGREE { color: #34d399; }
        }

        /* ════════════════════════════════════════════════════════════════════
           Document body
           ════════════════════════════════════════════════════════════════════ */
        .doc-body {
            background: var(--surface-1);
            -webkit-backdrop-filter: blur(var(--surface-blur)) saturate(180%);
            backdrop-filter: blur(var(--surface-blur)) saturate(180%);
            border: 1px solid var(--border-glass);
            border-radius: var(--r-lg);
            margin: 14px 14px 0;
            overflow: hidden;
            box-shadow: var(--shadow-2);
            opacity: 0;
            transform: translateY(16px) scale(0.98);
            animation: card-in 0.55s var(--spring) 0.12s forwards;
            position: relative;
        }
        /* Subtle fade at bottom — pseudo overlay so scroll behavior is untouched */
        .doc-body::after {
            content: "";
            position: absolute;
            left: 1px; right: 1px; bottom: 1px;
            height: 28px;
            border-radius: 0 0 var(--r-lg) var(--r-lg);
            background: linear-gradient(to bottom,
            rgba(255, 255, 255, 0) 0%,
            rgba(255, 255, 255, 0.85) 100%);
            pointer-events: none;
        }
        @media (prefers-color-scheme: dark) {
            .doc-body::after {
                background: linear-gradient(to bottom,
                rgba(30, 41, 59, 0) 0%,
                rgba(30, 41, 59, 0.85) 100%);
            }
        }
        .doc-body-header {
            padding: 14px 18px;
            border-bottom: 1px solid var(--border);
            display: flex; align-items: center; gap: 6px;
            font-size: 10px; font-weight: 700;
            color: var(--text-subtle);
            text-transform: uppercase; letter-spacing: 0.08em;
        }
        .doc-body-content {
            padding: 18px;
            font-size: 13px; line-height: 1.7;
            color: var(--text);
            max-height: 320px;
            overflow-y: auto;
            overflow-x: hidden;
            -webkit-overflow-scrolling: touch;
        }
        .doc-body-content::-webkit-scrollbar { width: 4px; }
        .doc-body-content::-webkit-scrollbar-thumb {
            background: var(--text-muted);
            border-radius: 4px;
            opacity: 0.3;
        }
        .doc-body-content img { max-width: 100%; border-radius: 8px; }

        /* ════════════════════════════════════════════════════════════════════
           Files
           ════════════════════════════════════════════════════════════════════ */
        .file-row {
            display: flex; gap: 12px; align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid var(--border);
            font-size: 13px;
            color: var(--text);
            transition: padding 0.2s ease;
        }
        .file-row:last-child { border-bottom: none; }
        .file-icon {
            width: 36px; height: 36px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg,
            rgba(59, 130, 246, 0.12),
            rgba(139, 92, 246, 0.12));
            color: var(--brand-1);
            flex-shrink: 0;
            border: 1px solid rgba(59, 130, 246, 0.15);
        }
        .file-icon svg { width: 16px; height: 16px; }
        .file-name {
            flex: 1;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-weight: 500;
        }

        /* ════════════════════════════════════════════════════════════════════
           QR section
           ════════════════════════════════════════════════════════════════════ */
        .qr-section {
            display: none;
            text-align: center;
            padding: 18px 0 4px;
            opacity: 0;
            transform: scale(0.94);
            transition: opacity 0.5s var(--smooth), transform 0.5s var(--spring);
        }
        .qr-section.active {
            display: block;
            opacity: 1;
            transform: scale(1);
        }

        .qr-hint {
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 20px;
            line-height: 1.55;
            max-width: 280px;
            margin-left: auto;
            margin-right: auto;
        }

        .qr-frame {
            position: relative;
            width: 280px; height: 280px;
            margin: 0 auto;
            display: flex; align-items: center; justify-content: center;
        }
        /* Glowing aura behind QR */
        .qr-frame::before {
            content: "";
            position: absolute; inset: -20px;
            border-radius: 30px;
            background: conic-gradient(from 0deg,
            var(--brand-1), var(--brand-2), var(--brand-3),
            var(--success), var(--brand-1));
            animation: aura-spin 6s linear infinite;
            filter: blur(24px);
            opacity: 0.55;
            z-index: -1;
        }
        @keyframes aura-spin { to { transform: rotate(360deg); } }

        #eimzo-qrcode {
            display: inline-block;
            padding: 16px;
            background: #fff;
            border-radius: 22px;
            box-shadow:
                0 0 0 1px rgba(255, 255, 255, 0.5),
                0 12px 32px -8px rgba(15, 23, 42, 0.25);
            position: relative;
            z-index: 1;
            transition: transform 0.3s var(--smooth);
        }
        #eimzo-qrcode:active { transform: scale(0.98); }

        /* Countdown ring with percentage */
        .countdown-ring {
            position: absolute;
            inset: -14px;
            pointer-events: none;
            z-index: 0;
        }
        .countdown-ring svg { width: 100%; height: 100%; transform: rotate(-90deg); }
        .countdown-ring .track {
            fill: none; stroke-width: 2.5;
            stroke: rgba(59, 130, 246, 0.15);
        }
        .countdown-ring .progress {
            fill: none; stroke-width: 2.5;
            stroke: url(#ring-gradient);
            stroke-linecap: round;
            transition: stroke-dashoffset 1s linear;
            filter: drop-shadow(0 0 4px rgba(59, 130, 246, 0.5));
        }

        /* Hash display */
        .hash-block {
            margin-top: 16px;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            border-radius: 14px;
            overflow: hidden;
            position: relative;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.06);
        }
        .hash-block::before {
            content: ""; position: absolute; inset: 0;
            background:
                linear-gradient(135deg, transparent 40%, rgba(59, 130, 246, 0.08) 50%, transparent 60%);
            pointer-events: none;
        }
        .hash-bar {
            display: flex; align-items: center; justify-content: space-between;
            padding: 10px 14px;
            font-size: 10px; font-weight: 600;
            color: rgba(255, 255, 255, 0.5);
            text-transform: uppercase; letter-spacing: 0.08em;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        }
        .copy-btn {
            display: flex; align-items: center; gap: 4px;
            padding: 4px 8px;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.08);
            color: rgba(255, 255, 255, 0.7);
            border-radius: 6px;
            font-size: 10px; font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            font-family: inherit;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .copy-btn:active { transform: scale(0.95); background: rgba(255, 255, 255, 0.12); }
        .copy-btn.done { background: rgba(16, 185, 129, 0.2); color: #6ee7b7; border-color: rgba(16, 185, 129, 0.3); }
        .copy-btn svg { width: 11px; height: 11px; }

        .hash-display {
            padding: 12px 14px;
            color: #6ee7b7;
            font-family: 'SF Mono', 'JetBrains Mono', Menlo, Consolas, monospace;
            font-size: 11px;
            word-break: break-all;
            text-align: left;
            line-height: 1.9;
            position: relative;
        }
        .hash-display .digit { color: #cbd5e1; }

        /* ════════════════════════════════════════════════════════════════════
           Buttons
           ════════════════════════════════════════════════════════════════════ */
        .btn, .btn-deeplink {
            display: flex; align-items: center; justify-content: center; gap: 8px;
            width: 100%;
            padding: 16px 18px;
            border: none;
            border-radius: var(--r-md);
            font-size: 15px; font-weight: 700;
            letter-spacing: -0.01em;
            cursor: pointer;
            margin-top: 12px;
            text-decoration: none;
            position: relative;
            overflow: hidden;
            font-family: inherit;
            transition: transform 0.15s var(--smooth), box-shadow 0.25s var(--smooth);
        }
        .btn:active, .btn-deeplink:active { transform: scale(0.97); }
        .btn svg, .btn-deeplink svg { width: 18px; height: 18px; }

        .btn-primary {
            background: linear-gradient(135deg, var(--brand-1) 0%, var(--brand-2) 100%);
            color: #fff;
            box-shadow:
                0 1px 0 rgba(255, 255, 255, 0.18) inset,
                0 12px 32px -8px rgba(59, 130, 246, 0.55),
                0 4px 8px -2px rgba(139, 92, 246, 0.35);
        }
        /* Idle shimmer on primary button */
        .btn-primary::before {
            content: "";
            position: absolute; inset: 0;
            background: linear-gradient(120deg,
            transparent 30%,
            rgba(255, 255, 255, 0.22) 50%,
            transparent 70%);
            transform: translateX(-100%);
            animation: btn-shimmer 3.5s ease-in-out infinite;
        }
        @keyframes btn-shimmer {
            0%, 60%, 100% { transform: translateX(-100%); }
            70%, 90%      { transform: translateX(100%); }
        }

        .btn-deeplink {
            background: linear-gradient(135deg, var(--success) 0%, var(--success-2) 100%);
            color: #fff;
            box-shadow:
                0 1px 0 rgba(255, 255, 255, 0.18) inset,
                0 12px 32px -8px rgba(16, 185, 129, 0.55);
        }

        .btn:disabled { opacity: 0.5; cursor: not-allowed; box-shadow: none; }
        .btn:disabled::before { display: none; }

        /* ════════════════════════════════════════════════════════════════════
           Status / spinner / pulse
           ════════════════════════════════════════════════════════════════════ */
        .status-bar {
            display: none;
            align-items: center; gap: 12px;
            background: linear-gradient(135deg,
            rgba(245, 158, 11, 0.10),
            rgba(245, 158, 11, 0.05));
            border: 1px solid rgba(245, 158, 11, 0.22);
            border-radius: 14px;
            padding: 13px 14px;
            margin-top: 12px;
            font-size: 13px;
            color: #b45309;
            font-weight: 500;
            position: relative;
            overflow: hidden;
        }
        @media (prefers-color-scheme: dark) {
            .status-bar { color: #fbbf24; }
        }
        .status-bar::before {
            content: "";
            position: absolute; inset: 0;
            background: linear-gradient(90deg,
            transparent 0%,
            rgba(245, 158, 11, 0.10) 50%,
            transparent 100%);
            transform: translateX(-100%);
            animation: status-stripe 2s linear infinite;
        }
        @keyframes status-stripe {
            from { transform: translateX(-100%); }
            to   { transform: translateX(100%); }
        }
        .status-bar.active {
            display: flex;
            animation: status-in 0.35s var(--spring);
        }
        @keyframes status-in {
            from { opacity: 0; transform: translateY(-6px) scale(0.98); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }
        .pulse-dot {
            width: 10px; height: 10px;
            border-radius: 50%;
            background: var(--warning);
            position: relative;
            flex-shrink: 0;
        }
        .pulse-dot::before {
            content: "";
            position: absolute; inset: -4px;
            border-radius: 50%;
            background: var(--warning);
            opacity: 0.4;
            animation: pulse-ring 1.4s ease-out infinite;
        }
        @keyframes pulse-ring {
            0%   { transform: scale(0.6); opacity: 0.6; }
            100% { transform: scale(1.6); opacity: 0; }
        }

        /* ════════════════════════════════════════════════════════════════════
           Result box
           ════════════════════════════════════════════════════════════════════ */
        .result-box {
            display: none;
            border-radius: 14px;
            padding: 14px;
            margin-top: 12px;
            font-size: 13px;
            line-height: 1.55;
            border: 1px solid transparent;
            font-weight: 500;
        }
        .result-box.active   { display: block; animation: result-in 0.35s var(--spring); }
        @keyframes result-in {
            from { opacity: 0; transform: translateY(-4px) scale(0.98); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }
        .result-box.error    {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.10), rgba(239, 68, 68, 0.05));
            border-color: rgba(239, 68, 68, 0.25);
            color: #b91c1c;
            animation: shake 0.5s cubic-bezier(0.36, 0.07, 0.19, 0.97);
        }
        @media (prefers-color-scheme: dark) {
            .result-box.error { color: #fca5a5; }
        }
        .result-box.success  {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.10), rgba(16, 185, 129, 0.05));
            border-color: rgba(16, 185, 129, 0.25);
            color: #047857;
        }
        @keyframes shake {
            10%, 90% { transform: translateX(-1px); }
            20%, 80% { transform: translateX(2px); }
            30%, 50%, 70% { transform: translateX(-4px); }
            40%, 60% { transform: translateX(4px); }
        }

        /* ════════════════════════════════════════════════════════════════════
           Success screen
           ════════════════════════════════════════════════════════════════════ */
        .success-screen {
            display: none;
            min-height: 100vh;
            min-height: 100dvh;
            text-align: center;
            padding: 80px 24px;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        .success-screen.active { display: flex; }

        .success-icon {
            width: 96px; height: 96px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--success) 0%, var(--success-2) 100%);
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 24px;
            box-shadow:
                0 0 0 1px rgba(255, 255, 255, 0.18) inset,
                0 20px 48px -12px rgba(16, 185, 129, 0.55),
                0 4px 12px -2px rgba(16, 185, 129, 0.35);
            animation: success-pop 0.7s var(--spring) forwards;
            position: relative;
        }
        @keyframes success-pop {
            0%   { transform: scale(0) rotate(-180deg); opacity: 0; }
            60%  { transform: scale(1.18) rotate(10deg); opacity: 1; }
            100% { transform: scale(1) rotate(0); opacity: 1; }
        }
        .success-icon svg { width: 52px; height: 52px; stroke: #fff; stroke-width: 4; fill: none; stroke-linecap: round; stroke-linejoin: round; }
        .success-icon svg path {
            stroke-dasharray: 60; stroke-dashoffset: 60;
            animation: draw-check 0.55s 0.4s var(--spring) forwards;
        }
        @keyframes draw-check { to { stroke-dashoffset: 0; } }

        /* Confetti sparkles around success */
        .sparkle {
            position: absolute;
            width: 6px; height: 6px;
            background: var(--brand-2);
            border-radius: 50%;
            opacity: 0;
        }
        .success-screen.active .sparkle {
            animation: sparkle-burst 1.2s ease-out forwards;
        }
        .sparkle:nth-child(1) { top: 30%; left: 30%; background: var(--brand-1); animation-delay: 0.5s !important; }
        .sparkle:nth-child(2) { top: 25%; right: 28%; background: var(--brand-3); animation-delay: 0.6s !important; }
        .sparkle:nth-child(3) { top: 40%; left: 22%; background: var(--success); animation-delay: 0.7s !important; }
        .sparkle:nth-child(4) { top: 38%; right: 22%; background: var(--brand-2); animation-delay: 0.55s !important; }
        .sparkle:nth-child(5) { top: 50%; left: 35%; background: var(--warning); animation-delay: 0.65s !important; }
        .sparkle:nth-child(6) { top: 50%; right: 35%; background: var(--brand-1); animation-delay: 0.45s !important; }
        @keyframes sparkle-burst {
            0%   { opacity: 0; transform: scale(0); }
            50%  { opacity: 1; transform: scale(1.4) translateY(-30px); }
            100% { opacity: 0; transform: scale(0.8) translateY(-60px); }
        }

        .success-title {
            font-size: 24px; font-weight: 700;
            color: var(--text);
            margin-bottom: 8px;
            letter-spacing: -0.02em;
            opacity: 0;
            animation: fade-up 0.5s 0.6s var(--smooth) forwards;
            background: linear-gradient(135deg, var(--text) 0%, var(--brand-1) 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .success-sub {
            font-size: 14px;
            color: var(--text-muted);
            opacity: 0;
            animation: fade-up 0.5s 0.7s var(--smooth) forwards;
        }
        @keyframes fade-up {
            from { opacity: 0; transform: translateY(8px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .pb { padding-bottom: 24px; }

        /* ════════════════════════════════════════════════════════════════════
           Reduced motion fallback
           ════════════════════════════════════════════════════════════════════ */
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
            .card, .doc-body { opacity: 1 !important; transform: none !important; }
            .blob { animation: none !important; }
        }
    </style>
</head>
<body>

<!-- Animated background blobs -->
<div class="bg-blobs" aria-hidden="true">
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
    <div class="blob blob-3"></div>
</div>

<!-- SVG defs (gradients used by countdown ring) -->
<svg width="0" height="0" style="position:absolute" aria-hidden="true">
    <defs>
        <linearGradient id="ring-gradient" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%"  stop-color="#3b82f6"/>
            <stop offset="100%" stop-color="#8b5cf6"/>
        </linearGradient>
    </defs>
</svg>

<!-- Success screen (hidden by default) -->
<div class="success-screen" id="success-screen">
    <span class="sparkle"></span>
    <span class="sparkle"></span>
    <span class="sparkle"></span>
    <span class="sparkle"></span>
    <span class="sparkle"></span>
    <span class="sparkle"></span>

    <div class="success-icon">
        <svg viewBox="0 0 24 24"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>
    </div>
    <div class="success-title">Muvaffaqiyatli imzolandi</div>
    <div class="success-sub">Telegramga qaytishingiz mumkin</div>
</div>

<div id="main-content">
    <div class="top-bar">
        <div class="top-bar-row">
            <div class="brand">
                <div class="brand-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="4" y="11" width="16" height="10" rx="2"/>
                        <path d="M8 11V7a4 4 0 0 1 8 0v4"/>
                    </svg>
                </div>
                <span>E-IMZO Imzolash</span>
            </div>
        </div>

        <!-- 4-step indicator -->
        <div class="steps" id="steps">
            <div class="step active" data-step="1"></div>
            <div class="step" data-step="2"></div>
            <div class="step" data-step="3"></div>
            <div class="step" data-step="4"></div>
        </div>
    </div>

    <div class="card">
        <div class="label">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            Hujjat
        </div>
        <div class="doc-name">{{ $doc->doc_name ?? 'Hujjat' }}</div>

        @if(!empty($doc->doc_number))
            <div class="doc-meta">
                <span>№ {{ $doc->doc_number }}</span>
            </div>
        @endif

        @php
            $roleMap = [
                'sign_resolution'    => ['label' => 'Rezolyutsiya', 'class' => 'role-RESOL', 'emoji' => '📋'],
                'send_to_agreement'  => ['label' => 'Kelishish',    'class' => 'role-AGREE', 'emoji' => '✅'],
                'send_to_ag_out_doc' => ['label' => 'Kelishish',    'class' => 'role-AGREE', 'emoji' => '✅'],
            ];
            $roleInfo = $roleMap[$doc_type] ?? ['label' => 'Imzolash', 'class' => 'role-SIGN', 'emoji' => '✍️'];
        @endphp
        <span class="role-badge {{ $roleInfo['class'] }}">{{ $roleInfo['emoji'] }} {{ $roleInfo['label'] }}</span>
    </div>

    @if(!empty($html_doc))
        <div class="doc-body">
            <div class="doc-body-header">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><line x1="4" y1="6" x2="20" y2="6"/><line x1="4" y1="12" x2="20" y2="12"/><line x1="4" y1="18" x2="14" y2="18"/></svg>
                Hujjat matni
            </div>
            <div class="doc-body-content">{!! $html_doc !!}</div>
        </div>
    @endif

    @if($files->isNotEmpty())
        <div class="card">
            <div class="label">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
                Fayllar
            </div>
            @foreach($files as $file)
                <div class="file-row">
                    <div class="file-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    </div>
                    <span class="file-name">{{ $file->original_name ?? $file->file_name }}</span>
                </div>
            @endforeach
        </div>
    @endif

    <div class="card pb" id="sign-card">
        <div class="label">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
            E-IMZO ID-CARD orqali imzolash
        </div>

        <div class="qr-section" id="qr-section">
            <p class="qr-hint">
                E-IMZO ilovasida QR-kodni skaning qiling yoki quyidagi tugmani bosing
            </p>

            <div class="qr-frame">
                <div class="countdown-ring">
                    <svg viewBox="0 0 100 100">
                        <circle class="track"    cx="50" cy="50" r="48"/>
                        <circle class="progress" id="countdown-progress" cx="50" cy="50" r="48"
                                stroke-dasharray="301.59" stroke-dashoffset="0"/>
                    </svg>
                </div>
                <div id="eimzo-qrcode"></div>
            </div>

            <div class="hash-block">
                <div class="hash-bar">
                    <span>Hash · GOST</span>
                    <button type="button" class="copy-btn" id="copy-hash" onclick="copyHash()">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="9" y="9" width="13" height="13" rx="2"/>
                            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                        </svg>
                        <span id="copy-label">Copy</span>
                    </button>
                </div>
                <div class="hash-display" id="hash-display"></div>
            </div>

            <a id="deeplink-btn" href="#" class="btn-deeplink">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="2" width="14" height="20" rx="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                E-IMZO da ochish
            </a>
        </div>

        <div class="status-bar" id="status-bar">
            <div class="pulse-dot"></div>
            <span id="status-text">Imzo kutilmoqda...</span>
        </div>

        <div class="result-box" id="result-box"></div>

        <button class="btn btn-primary" id="btn-start" onclick="startSign()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
            <span>Imzolashni boshlash</span>
        </button>
    </div>
</div>

<script src="{{ asset('js/qrcode.min.js') }}"></script>
<script src="{{ asset('js/pkcs.js') }}"></script>
<script src="{{ asset('js/crc32.js') }}"></script>

<script>
    // ── Telegram WebApp init ──────────────────────────────────────────────────
    var tgApp = window.Telegram && window.Telegram.WebApp ? window.Telegram.WebApp : null;
    if (tgApp) {
        tgApp.ready();
        try { tgApp.expand && tgApp.expand(); } catch (e) {}
        try { tgApp.setHeaderColor && tgApp.setHeaderColor('secondary_bg_color'); } catch (e) {}
    }

    function haptic(type) {
        if (!tgApp || !tgApp.HapticFeedback) return;
        try {
            if (type === 'success' || type === 'error' || type === 'warning') {
                tgApp.HapticFeedback.notificationOccurred(type);
            } else {
                tgApp.HapticFeedback.impactOccurred(type || 'light');
            }
        } catch (e) {}
    }

    // ── Step indicator ────────────────────────────────────────────────────────
    function setStep(stepNum) {
        var steps = document.querySelectorAll('.step');
        steps.forEach(function(el, i) {
            el.classList.remove('active', 'done');
            if ((i + 1) < stepNum)      el.classList.add('done');
            else if ((i + 1) === stepNum) el.classList.add('active');
        });
    }

    // ── Config ────────────────────────────────────────────────────────────────
    var BASE_URL   = '{{ rtrim(config("app.url"), "/") }}';
    var DOC_ID     = {{ $journal->doc_id }};
    var DOC_TYPE   = '{{ $doc_type }}';
    var SIGN_TOKEN = '{{ $token }}';
    var API_TOKEN  = '{{ $api_token }}';

    // ── State ─────────────────────────────────────────────────────────────────
    var currentDocumentId = null;
    var currentDeeplink   = null;
    var statusInterval    = null;
    var checkCount        = 0;
    var MAX_CHECKS        = 120;
    var currentHash       = '';

    var RING_LEN = 2 * Math.PI * 48;  // ≈ 301.59

    // ── QR Generation ─────────────────────────────────────────────────────────
    function generateQRCode(documentId, siteId, data) {
        var el = document.getElementById('eimzo-qrcode');
        el.innerHTML = '';

        var hasher   = new GostHash();
        var textHash = hasher.gosthash(data.toString());
        var code     = siteId + documentId + textHash;
        var crc32    = new CRC32().calcHex(code);
        var final    = code + crc32;

        new QRCode(el, {
            text: final, width: 260, height: 260,
            colorDark: '#000000', colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.H
        });

        currentHash = textHash;
        displayHash(textHash);
        return final;
    }

    function displayHash(hash) {
        var el     = document.getElementById('hash-display');
        var chunks = hash.match(/.{1,16}/g) || [];
        var html   = '';
        chunks.forEach(function(chunk) {
            chunk.split('').forEach(function(c) {
                html += /\d/.test(c) ? '<span class="digit">' + c + '</span>' : c;
            });
            html += '\n';
        });
        el.innerHTML = html;
    }

    function copyHash() {
        if (!currentHash) return;
        haptic('light');

        var label = document.getElementById('copy-label');
        var btn   = document.getElementById('copy-hash');

        var done = function() {
            btn.classList.add('done');
            label.textContent = 'Copied';
            setTimeout(function() {
                btn.classList.remove('done');
                label.textContent = 'Copy';
            }, 1400);
        };

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(currentHash).then(done).catch(function() {
                fallbackCopy(currentHash); done();
            });
        } else {
            fallbackCopy(currentHash); done();
        }
    }
    function fallbackCopy(text) {
        var ta = document.createElement('textarea');
        ta.value = text; ta.style.position = 'fixed'; ta.style.opacity = 0;
        document.body.appendChild(ta);
        ta.select();
        try { document.execCommand('copy'); } catch (e) {}
        document.body.removeChild(ta);
    }

    // ── Deeplink ──────────────────────────────────────────────────────────────
    function openDeeplink() {
        if (!currentDeeplink) return;
        haptic('medium');

        var redirectUrl = BASE_URL + '/mobile/redirect?url=' + encodeURIComponent(currentDeeplink);
        if (tgApp && tgApp.openLink) {
            tgApp.openLink(redirectUrl);
        } else {
            window.location.href = currentDeeplink;
        }
    }

    // ── Main flow ─────────────────────────────────────────────────────────────
    function startSign() {
        haptic('light');
        setStep(2);

        var btn = document.getElementById('btn-start');
        btn.disabled = true;
        var btnSpan = btn.querySelector('span');
        if (btnSpan) btnSpan.textContent = 'Yuklanmoqda...';
        hideResult();

        fetch(BASE_URL + '/api/v1/ecp/mobile/sign', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + API_TOKEN,
                'Accept': 'application/json',
            }
        })
            .then(function(r) { return r.json(); })
            .then(function(response) {
                if (response.status !== 1 || !response.documentId) {
                    haptic('error');
                    showResult('Server xatosi: ' + (response.message || 'documentId olinmadi'), true);
                    setStep(1);
                    resetBtn();
                    return;
                }

                currentDocumentId = response.documentId;

                var docContent = btoa(String.fromCharCode.apply(null,
                    new TextEncoder().encode('doc_id:' + DOC_ID + ':' + DOC_TYPE)
                ));

                var qrCode = generateQRCode(currentDocumentId, response.siteId, docContent);
                currentDeeplink = 'eimzo://sign?qc=' + qrCode;

                var dlBtn = document.getElementById('deeplink-btn');
                dlBtn.href = currentDeeplink;
                dlBtn.onclick = function(e) {
                    e.preventDefault();
                    openDeeplink();
                };

                document.getElementById('qr-section').classList.add('active');
                showStatus('QR-kodni skaning qiling yoki E-IMZO da oching...');
                haptic('medium');

                startPolling(docContent);
            })
            .catch(function(e) {
                haptic('error');
                showResult('Ulanishda xatolik: ' + e.message, true);
                setStep(1);
                resetBtn();
            });
    }

    function startPolling(docContent) {
        checkCount = 0;
        var ringEl = document.getElementById('countdown-progress');

        statusInterval = setInterval(function() {
            checkCount++;

            if (ringEl) {
                var offset = RING_LEN * (checkCount / MAX_CHECKS);
                ringEl.style.strokeDashoffset = offset;
            }

            if (checkCount > MAX_CHECKS) {
                stopPolling();
                haptic('warning');
                showResult('Vaqt tugadi. Qayta urinib ko\'ring.', true);
                setStep(1);
                resetBtn();
                return;
            }
            showStatus('Imzo kutilmoqda... (' + checkCount + 's)');

            fetch(BASE_URL + '/api/v1/ecp/mobile/status', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({documentId: currentDocumentId})
            })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.status === 1) {
                        stopPolling();
                        setStep(3);
                        document.getElementById('qr-section').classList.remove('active');
                        showStatus('Imzo tekshirilmoqda...');
                        verifyAndSign(docContent);
                    } else if (data.status < 0) {
                        stopPolling();
                        haptic('error');
                        showResult('Imzolashda xatolik: ' + (data.message || 'Noma\'lum xato'), true);
                        setStep(1);
                        resetBtn();
                    }
                })
                .catch(function() {});
        }, 1000);
    }

    function stopPolling() {
        if (statusInterval) { clearInterval(statusInterval); statusInterval = null; }
    }

    function verifyAndSign(docContent) {
        fetch(BASE_URL + '/api/v1/ecp/mobile/verify', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + API_TOKEN,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                documentId:    currentDocumentId,
                document:      docContent,
                ecp_sign_type: DOC_TYPE,
                ecp_doc_id:    DOC_ID,
            })
        })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                hideStatusBar();
                if (data.status === 1 || data.success === 200) {
                    setStep(4);
                    onSuccess();
                } else {
                    haptic('error');
                    showResult(data.message || 'Imzolashda xatolik', true);
                    setStep(1);
                    resetBtn();
                }
            })
            .catch(function() {
                hideStatusBar();
                haptic('error');
                showResult('Server bilan aloqa uzildi', true);
                setStep(1);
                resetBtn();
            });
    }

    function onSuccess() {
        haptic('success');
        fetch(BASE_URL + '/mobile/sign/' + SIGN_TOKEN + '/complete', {method: 'POST'});
        document.getElementById('main-content').style.display = 'none';
        document.getElementById('success-screen').classList.add('active');
        if (tgApp) {
            setTimeout(function() { tgApp.close(); }, 2200);
        }
    }

    function showStatus(msg) {
        document.getElementById('status-text').textContent = msg;
        document.getElementById('status-bar').classList.add('active');
    }
    function hideStatusBar() { document.getElementById('status-bar').classList.remove('active'); }

    function showResult(msg, isError) {
        var el = document.getElementById('result-box');
        el.textContent = msg;
        el.className = 'result-box active ' + (isError ? 'error' : 'success');
        hideStatusBar();
    }
    function hideResult() { document.getElementById('result-box').className = 'result-box'; }

    function resetBtn() {
        var btn = document.getElementById('btn-start');
        btn.disabled = false;
        btn.innerHTML =
            '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 3-6.7"/><polyline points="3 4 3 10 9 10"/></svg>' +
            '<span>Qayta urinish</span>';
    }

    window.addEventListener('beforeunload', stopPolling);
</script>
</body>
</html>
