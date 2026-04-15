(() => {
    function applyFilters(scope) {
        const controls = Array.from(scope.querySelectorAll('[data-filter-select]'));
        const items = Array.from(scope.querySelectorAll('[data-filter-item]'));

        let visibleCount = 0;
        items.forEach((item) => {
            const show = controls.every((control) => {
                const key = (control.getAttribute('name') || '').toLowerCase();
                const expected = (control.value || '').toLowerCase();
                if (!key || !expected || expected === 'all') {
                    return true;
                }

                const current = (item.getAttribute(`data-${key}`) || '').toLowerCase();
                return current === expected;
            });

            item.classList.toggle('is-hidden-by-filter', !show);
            const visibleBySearch = !item.classList.contains('is-hidden-by-search');
            const visibleByFilter = !item.classList.contains('is-hidden-by-filter');
            if (visibleBySearch && visibleByFilter) {
                visibleCount += 1;
            }
        });

        const count = scope.querySelector('[data-filter-count]');
        if (count) {
            count.textContent = String(visibleCount);
        }

        const emptyState = scope.querySelector('[data-filter-empty]');
        if (emptyState) {
            emptyState.classList.toggle('is-hidden', visibleCount > 0);
        }
    }

    function initFilters() {
        const scopes = Array.from(document.querySelectorAll('[data-filter-scope]'));
        scopes.forEach((scope) => {
            const controls = Array.from(scope.querySelectorAll('[data-filter-select]'));
            controls.forEach((control) => {
                control.addEventListener('change', () => applyFilters(scope));
            });

            const reset = scope.querySelector('[data-filter-reset]');
            if (reset) {
                reset.addEventListener('click', () => {
                    controls.forEach((control) => {
                        control.value = 'all';
                    });
                    applyFilters(scope);
                });
            }

            applyFilters(scope);
        });
    }

    document.addEventListener('DOMContentLoaded', initFilters);
})();
