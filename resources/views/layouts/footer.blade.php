<footer class="main-footer">
    <div class="footer-content">
        <div class="footer-left">
            <i data-lucide="copyright" style="width: 16px; height: 16px; display: inline-block; vertical-align: middle;"></i>
            <span>{{ __('field.copyright') }}</span>
        </div>
        <div class="footer-right">
            <a href="https://edoline.uz" class="footer-brand" target="_blank">
                <i data-lucide="file-text" style="width: 16px; height: 16px;"></i>
                EDOLINE
            </a>
        </div>
    </div>
</footer>

<style>
    /* ========================================
       MODERN FOOTER STYLES
       ======================================== */
    .main-footer {
        padding: 1.25rem 2rem 1.25rem 6rem;
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        border-top: 2px solid #e2e8f0;
        box-shadow: 0 -4px 16px rgba(0, 0, 0, 0.06);
        margin-top: auto;
    }

    .footer-content {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 1rem;
        /*max-width: 100%;*/
    }

    .footer-left {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 14px;
        color: #64748b;
        font-weight: 500;
    }

    .footer-left i {
        color: #794DFD;
    }

    .footer-right {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 14px;
        color: #64748b;
    }

    .footer-brand {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        background: linear-gradient(135deg, #794DFD 0%, #9d6fff 100%);
        color: white;
        text-decoration: none;
        border-radius: 8px;
        font-weight: 700;
        font-size: 14px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(121, 77, 253, 0.25);
    }

    .footer-brand:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(121, 77, 253, 0.35);
        color: white;
        text-decoration: none;
    }

    .footer-brand i {
        width: 16px;
        height: 16px;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .main-footer {
            padding: 1rem;
        }

        .footer-content {
            flex-direction: column;
            text-align: center;
        }

        .footer-left,
        .footer-right {
            width: 100%;
            justify-content: center;
        }
    }
</style>
