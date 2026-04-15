(() => {
    const normalize = (value) => (value || '').toString().toLowerCase();

    function runSearch(scope) {
        const input = scope.querySelector('[data-search-input]');
        const term = normalize(input ? input.value.trim() : '');
        const searchableItems = Array.from(scope.querySelectorAll('[data-search-item]'));

        let visibleCount = 0;
        searchableItems.forEach((item) => {
            const haystack = normalize(item.textContent + ' ' + (item.getAttribute('data-keywords') || ''));
            const isVisible = term === '' || haystack.includes(term);
            item.classList.toggle('is-hidden-by-search', !isVisible);
            const visibleBySearch = !item.classList.contains('is-hidden-by-search');
            const visibleByFilter = !item.classList.contains('is-hidden-by-filter');
            if (visibleBySearch && visibleByFilter) {
                visibleCount += 1;
            }
        });

        const countNode = scope.querySelector('[data-search-count]');
        if (countNode) {
            countNode.textContent = String(visibleCount);
        }

        const emptyState = scope.querySelector('[data-empty-state]');
        if (emptyState) {
            emptyState.classList.toggle('is-hidden', visibleCount > 0);
        }
    }

    function initSearch() {
        const scopes = Array.from(document.querySelectorAll('[data-search-scope]'));
        if (scopes.length === 0) {
            return;
        }

        scopes.forEach((scope) => {
            const input = scope.querySelector('[data-search-input]');
            if (!input) {
                return;
            }

            input.addEventListener('input', () => runSearch(scope));
            runSearch(scope);
        });
    }

    document.addEventListener('DOMContentLoaded', initSearch);
})();
