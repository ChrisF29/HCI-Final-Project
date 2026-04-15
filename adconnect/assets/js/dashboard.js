(() => {
    function animateMeters() {
        const meters = Array.from(document.querySelectorAll('[data-meter]'));
        meters.forEach((meter) => {
            const value = Number(meter.getAttribute('data-meter') || 0);
            const fill = meter.querySelector('span');
            if (!fill) {
                return;
            }

            window.requestAnimationFrame(() => {
                fill.style.width = `${Math.max(0, Math.min(100, value))}%`;
            });
        });
    }

    function animateCounters() {
        const counters = Array.from(document.querySelectorAll('[data-counter]'));
        counters.forEach((counter) => {
            const target = Number(counter.getAttribute('data-counter') || 0);
            const duration = 700;
            const startTime = performance.now();

            function step(now) {
                const progress = Math.min((now - startTime) / duration, 1);
                const current = Math.floor(progress * target);
                counter.textContent = current.toLocaleString();
                if (progress < 1) {
                    requestAnimationFrame(step);
                }
            }

            requestAnimationFrame(step);
        });
    }

    function stampDates() {
        const dateNodes = Array.from(document.querySelectorAll('[data-current-date]'));
        const dateString = new Date().toLocaleDateString(undefined, {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });

        dateNodes.forEach((node) => {
            node.textContent = dateString;
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        animateMeters();
        animateCounters();
        stampDates();
    });
})();
