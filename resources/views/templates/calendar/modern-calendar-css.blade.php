<style>
    :root {
        --primary: #6366f1;
        --primary-dark: #4f46e5;
        --bg: #f8fafc;
        --card: #ffffff;
        --text: #1e293b;
        --text-light: #64748b;
        --border: #e2e8f0;
        --hover: #f1f5f9;
    }

    .date-picker-container {
        position: relative;
        width: 100%;
        max-width: 320px;
    }

    .input-wrapper {
        position: relative;
        width: 100%;
    }

    .calendar-icon {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-light);
        pointer-events: none;
        z-index: 1;
    }

    .date-input {
        width: 100%;
        padding: 14px 16px 14px 48px;
        border: 2px solid var(--border);
        border-radius: 12px;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.2s ease;
        background: white;
    }

    .date-input:hover {
        border-color: #cbd5e1;
    }

    .date-input:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15);
    }

    .calendar-popup {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        margin-top: 12px;
        background: var(--card);
        border-radius: 16px;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1),
        0 10px 10px -5px rgba(0, 0, 0, 0.04);
        overflow: hidden;
        z-index: 1000;
        opacity: 0;
        transform: translateY(-10px);
        pointer-events: none;
        transition: all 0.25s ease;
    }

    .calendar-popup.active {
        opacity: 1;
        transform: translateY(0);
        pointer-events: auto;
    }

    .calendar-header {
        padding: 8px 14px;
        background: linear-gradient(135deg, #794DFD 0%, #9d6fff 100%);
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .month-year {
        font-weight: 600;
        font-size: 1.1rem;
        color: white;
    }

    .nav-btn {
        background: none;
        border: none;
        color: white;
        font-size: 1.4rem;
        cursor: pointer;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: grid;
        place-items: center;
        transition: background 0.2s;
    }

    .nav-btn:hover {
        background: rgba(255, 255, 255, 0.2);
    }

    .days {
        display: flex;
        flex-direction: column;
        gap: 0;
    }

    .day-headers {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 2px;
        padding: 12px 16px 8px;
    }

    .day-header {
        text-align: center;
        font-size: 12px;
        font-weight: 600;
        color: var(--text-light);
        padding: 4px 0;
    }

    .days-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 2px;
        padding: 4px 16px 12px;
    }

    .day {
        aspect-ratio: 1;
        display: grid;
        place-items: center;
        border-radius: 50%;
        font-size: 0.95rem;
        cursor: pointer;
        transition: all 0.15s ease;
        color: var(--text);
        font-weight: 500;
    }

    .day:not(.other-month):hover {
        background: var(--hover);
    }

    .day.other-month {
        color: #94a3b8;
    }

    .day.today {
        background: linear-gradient(135deg, #794DFD 0%, #9d6fff 100%);
        color: white;
        font-weight: 600;
    }

    .day.selected {
        background: var(--primary-dark);
        color: white;
        font-weight: 600;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.3);
    }

    .day.selected:hover {
        background: var(--primary);
    }

    .day.disabled {
        color: #cbd5e1;
        pointer-events: none;
        cursor: not-allowed;
    }

    /* Error state */
    .date-input.is-invalid {
        border-color: #ef4444;
    }

    .date-input.is-invalid:focus {
        border-color: #ef4444;
        box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.15);
    }

    .error-text {
        color: #ef4444;
        font-size: 12px;
        margin-top: 4px;
        margin-bottom: 0;
    }

    /* Mobile responsive */
    @media (max-width: 400px) {
        .date-picker-container {
            max-width: 100%;
        }

        .calendar-popup {
            left: 50%;
            right: auto;
            transform: translateX(-50%) translateY(-10px);
        }

        .calendar-popup.active {
            transform: translateX(-50%) translateY(0);
        }
    }
</style>
