<script>
    // Uzbek locale (Latin)
    const uzMonths = [
        'Yanvar', 'Fevral', 'Mart', 'Aprel', 'May', 'Iyun',
        'Iyul', 'Avgust', 'Sentabr', 'Oktabr', 'Noyabr', 'Dekabr'
    ];

    const uzDays = ['Du', 'Se', 'Ch', 'Pa', 'Ju', 'Sh', 'Ya'];

    // Format date as dd.mm.yyyy
    function formatDateUz(date) {
        const d = String(date.getDate()).padStart(2, '0');
        const m = String(date.getMonth() + 1).padStart(2, '0');
        const y = date.getFullYear();
        return `${d}.${m}.${y}`;
    }

    // Parse date from dd.mm.yyyy format
    function parseDateUz(dateStr) {
        if (!dateStr) return null;
        const parts = dateStr.split('.');
        if (parts.length !== 3) return null;
        const day = parseInt(parts[0], 10);
        const month = parseInt(parts[1], 10) - 1;
        const year = parseInt(parts[2], 10);
        if (isNaN(day) || isNaN(month) || isNaN(year)) return null;
        return new Date(year, month, day);
    }

    // Input mask for dd.mm.yyyy
    function applyDateMask(input) {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, ''); // Remove non-digits
            let formatted = '';

            if (value.length > 0) {
                // Day
                formatted = value.substring(0, 2);
            }
            if (value.length >= 3) {
                // Month
                formatted += '.' + value.substring(2, 4);
            }
            if (value.length >= 5) {
                // Year
                formatted += '.' + value.substring(4, 8);
            }

            e.target.value = formatted;
        });

        input.addEventListener('blur', function(e) {
            const value = e.target.value;
            if (value.length === 10) {
                const date = parseDateUz(value);
                if (date && !isNaN(date.getTime())) {
                    e.target.value = formatDateUz(date);
                    // Update the associated calendar
                    const container = e.target.closest('.date-picker-container');
                    if (container && container.calendarInstance) {
                        container.calendarInstance.selectedDate = date;
                        container.calendarInstance.currentDate = new Date(date);
                    }
                }
            }
        });
    }

    // Calendar class
    class DatePicker {
        constructor(container) {
            this.container = container;
            this.input = container.querySelector('.date-input');
            this.popup = container.querySelector('.calendar-popup');
            this.monthYearDisplay = this.popup.querySelector('.month-year');
            this.daysContainer = this.popup.querySelector('.days');
            this.prevBtn = this.popup.querySelector('.prev');
            this.nextBtn = this.popup.querySelector('.next');

            this.currentDate = new Date();
            this.selectedDate = null;

            this.init();
        }

        init() {
            // Apply input mask
            applyDateMask(this.input);

            // Parse initial value (supports both dd.mm.yyyy and yyyy-mm-dd formats)
            const initialValue = this.input.value.trim();
            if (initialValue) {
                let parsed = null;

                // Try dd.mm.yyyy format first
                parsed = parseDateUz(initialValue);

                // If that fails, try yyyy-mm-dd format (Laravel default)
                if (!parsed || isNaN(parsed.getTime())) {
                    // Check if it's yyyy-mm-dd format
                    if (initialValue.match(/^\d{4}-\d{2}-\d{2}$/)) {
                        const [year, month, day] = initialValue.split('-');
                        parsed = new Date(parseInt(year), parseInt(month) - 1, parseInt(day));
                    }
                }

                if (parsed && !isNaN(parsed.getTime())) {
                    this.selectedDate = parsed;
                    this.currentDate = new Date(parsed);
                    this.input.value = formatDateUz(parsed);
                }
            }

            // Event listeners
            this.input.addEventListener('click', (e) => {
                e.stopPropagation();
                this.toggleCalendar();
            });

            this.prevBtn.addEventListener('click', () => {
                this.currentDate.setMonth(this.currentDate.getMonth() - 1);
                this.renderCalendar();
            });

            this.nextBtn.addEventListener('click', () => {
                this.currentDate.setMonth(this.currentDate.getMonth() + 1);
                this.renderCalendar();
            });

            // Close on outside click
            document.addEventListener('click', (e) => {
                if (!this.container.contains(e.target)) {
                    this.closeCalendar();
                }
            });

            // Prevent popup clicks from closing
            this.popup.addEventListener('click', (e) => {
                e.stopPropagation();
            });

            this.renderCalendar();
        }

        toggleCalendar() {
            const isActive = this.popup.classList.toggle('active');
            if (isActive) {
                // Close other calendars
                document.querySelectorAll('.calendar-popup.active').forEach(popup => {
                    if (popup !== this.popup) {
                        popup.classList.remove('active');
                    }
                });
                this.renderCalendar();
            }
        }

        closeCalendar() {
            this.popup.classList.remove('active');
        }

        renderCalendar() {
            const year = this.currentDate.getFullYear();
            const month = this.currentDate.getMonth();

            this.monthYearDisplay.textContent = `${uzMonths[month]} ${year}`;

            this.daysContainer.innerHTML = '';

            // Add day headers
            const dayHeadersRow = document.createElement('div');
            dayHeadersRow.className = 'day-headers';
            uzDays.forEach(day => {
                const dayHeader = document.createElement('div');
                dayHeader.className = 'day-header';
                dayHeader.textContent = day;
                dayHeadersRow.appendChild(dayHeader);
            });
            this.daysContainer.appendChild(dayHeadersRow);

            // Create days grid
            const daysGrid = document.createElement('div');
            daysGrid.className = 'days-grid';

            const firstDay = new Date(year, month, 1).getDay();
            // Adjust for Monday start (0 = Sunday → shift to Monday = 1)
            const adjustedFirstDay = (firstDay === 0 ? 6 : firstDay - 1);

            const lastDate = new Date(year, month + 1, 0).getDate();
            const prevMonthLastDate = new Date(year, month, 0).getDate();

            // Previous month days
            for (let i = adjustedFirstDay; i > 0; i--) {
                const dayEl = document.createElement('div');
                dayEl.className = 'day other-month';
                dayEl.textContent = prevMonthLastDate - i + 1;
                daysGrid.appendChild(dayEl);
            }

            // Current month days
            for (let day = 1; day <= lastDate; day++) {
                const dayEl = document.createElement('div');
                dayEl.className = 'day';
                dayEl.textContent = day;

                const thisDate = new Date(year, month, day);

                if (thisDate.toDateString() === new Date().toDateString()) {
                    dayEl.classList.add('today');
                }

                if (this.selectedDate && thisDate.toDateString() === this.selectedDate.toDateString()) {
                    dayEl.classList.add('selected');
                }

                dayEl.addEventListener('click', () => {
                    this.selectedDate = thisDate;
                    this.input.value = formatDateUz(thisDate);
                    this.closeCalendar();
                    this.renderCalendar();

                    // Trigger change event
                    this.input.dispatchEvent(new Event('change', { bubbles: true }));
                });

                daysGrid.appendChild(dayEl);
            }

            // Next month placeholders (fill row)
            const totalCells = daysGrid.children.length;
            const remaining = totalCells % 7 === 0 ? 0 : 7 - (totalCells % 7);
            for (let i = 1; i <= remaining; i++) {
                const dayEl = document.createElement('div');
                dayEl.className = 'day other-month';
                dayEl.textContent = i;
                daysGrid.appendChild(dayEl);
            }

            this.daysContainer.appendChild(daysGrid);
        }
    }

    // Initialize all date pickers
    document.querySelectorAll('.date-picker-container').forEach(container => {
        container.calendarInstance = new DatePicker(container);
    });

    // Convert any yyyy-mm-dd dates to dd.mm.yyyy format on page load
    document.querySelectorAll('.date-input').forEach(input => {
        const value = input.value.trim();
        if (value && value.match(/^\d{4}-\d{2}-\d{2}$/)) {
            const [year, month, day] = value.split('-');
            const date = new Date(parseInt(year), parseInt(month) - 1, parseInt(day));
            if (!isNaN(date.getTime())) {
                input.value = formatDateUz(date);
            }
        }
    });
</script>
