(() => {
    const selectOne = (selector, scope = document) => scope.querySelector(selector);
    const selectAll = (selector, scope = document) => Array.from(scope.querySelectorAll(selector));

    function initMobileNav() {
        const toggle = selectOne('[data-mobile-nav-toggle]');
        const nav = selectOne('[data-mobile-nav]');
        if (!toggle || !nav) {
            return;
        }

        toggle.addEventListener("click", () => {
            const expanded = toggle.getAttribute("aria-expanded") === "true";
            toggle.setAttribute("aria-expanded", String(!expanded));
            nav.classList.toggle("is-open");
        });
    }

    function initDropdowns() {
        selectAll('[data-dropdown]').forEach((dropdown) => {
            const trigger = selectOne('[data-dropdown-toggle]', dropdown);
            const menu = selectOne('[data-dropdown-menu]', dropdown);
            if (!trigger || !menu) {
                return;
            }

            trigger.addEventListener("click", (event) => {
                event.stopPropagation();
                menu.classList.toggle("is-open");
            });
        });

        document.addEventListener("click", () => {
            selectAll('[data-dropdown-menu].is-open').forEach((menu) => menu.classList.remove("is-open"));
        });
    }

    function initTabs() {
        selectAll('[data-tabs]').forEach((tabsWrap) => {
            const tabButtons = selectAll('[data-tab-target]', tabsWrap);
            const tabPanels = selectAll('[data-tab-panel]', tabsWrap);

            tabButtons.forEach((button) => {
                button.addEventListener("click", () => {
                    const targetId = button.getAttribute("data-tab-target");
                    tabButtons.forEach((item) => item.classList.remove("is-active"));
                    tabPanels.forEach((panel) => panel.classList.remove("is-active"));

                    button.classList.add("is-active");
                    const activePanel = selectOne(`[data-tab-panel="${targetId}"]`, tabsWrap);
                    if (activePanel) {
                        activePanel.classList.add("is-active");
                    }
                });
            });
        });
    }

    function initModals() {
        selectAll('[data-modal-target]').forEach((trigger) => {
            trigger.addEventListener("click", () => {
                const modalId = trigger.getAttribute("data-modal-target");
                const modal = selectOne(`[data-modal="${modalId}"]`);
                if (modal) {
                    modal.classList.add("is-open");
                }
            });
        });

        selectAll('[data-modal-close]').forEach((closeBtn) => {
            closeBtn.addEventListener("click", () => {
                const modal = closeBtn.closest('.modal');
                if (modal) {
                    modal.classList.remove("is-open");
                }
            });
        });

        selectAll('.modal').forEach((modal) => {
            modal.addEventListener("click", (event) => {
                if (event.target === modal) {
                    modal.classList.remove("is-open");
                }
            });
        });
    }

    function showToast(message, timeout = 2800) {
        const root = selectOne('#notification-root');
        if (!root) {
            return;
        }

        const toast = document.createElement('div');
        toast.className = 'toast';
        toast.textContent = message;
        root.appendChild(toast);

        window.setTimeout(() => {
            toast.remove();
        }, timeout);
    }

    function initNotificationTriggers() {
        selectAll('[data-notify]').forEach((trigger) => {
            trigger.addEventListener('click', () => {
                const message = trigger.getAttribute('data-notify') || 'Notification';
                showToast(message);
            });
        });
    }

    function applyTheme(theme) {
        const isNight = theme === 'night';
        document.body.classList.toggle('theme-night', isNight);

        const toggle = selectOne('[data-theme-toggle]');
        if (!toggle) {
            return;
        }

        toggle.setAttribute('aria-pressed', String(isNight));
        toggle.textContent = isNight ? 'Day Mode' : 'Night Mode';
    }

    function initThemeToggle() {
        const toggle = selectOne('[data-theme-toggle]');
        if (!toggle) {
            return;
        }

        const storedTheme = window.localStorage.getItem('adconnect-theme');
        const initialTheme = storedTheme === 'night' ? 'night' : 'day';
        applyTheme(initialTheme);

        toggle.addEventListener('click', () => {
            const nextTheme = document.body.classList.contains('theme-night') ? 'day' : 'night';
            window.localStorage.setItem('adconnect-theme', nextTheme);
            applyTheme(nextTheme);
        });
    }

    function validateField(input, form) {
        const name = input.getAttribute('name') || '';
        const errorContainer = selectOne(`[data-error-for="${name}"]`, form);
        let error = '';

        if (input.hasAttribute('required')) {
            if (input.type === 'checkbox' && !input.checked) {
                error = 'Please check this box to continue.';
            } else if (input.type === 'radio') {
                const group = selectAll(`[name="${name}"]`, form);
                const hasChecked = group.some((radio) => radio.checked);
                if (!hasChecked) {
                    error = 'Please choose one option.';
                }
            } else if (!input.value.trim()) {
                error = 'This field is required.';
            }
        }

        if (!error && input.type === 'email') {
            const isEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(input.value.trim());
            if (!isEmail) {
                error = 'Please enter a valid email address.';
            }
        }

        const minLength = Number(input.getAttribute('data-minlength') || 0);
        if (!error && minLength > 0 && input.value.trim().length < minLength) {
            error = `Must be at least ${minLength} characters.`;
        }

        const matchField = input.getAttribute('data-match');
        if (!error && matchField) {
            const peer = selectOne(`[name="${matchField}"]`, form);
            if (peer && peer.value !== input.value) {
                error = 'Values do not match.';
            }
        }

        input.classList.toggle('is-invalid', Boolean(error));
        if (errorContainer) {
            errorContainer.textContent = error;
        }

        return !error;
    }

    function initForms() {
        selectAll('form[data-validate]').forEach((form) => {
            const inputs = selectAll('input, select, textarea', form);

            inputs.forEach((input) => {
                input.addEventListener('blur', () => {
                    validateField(input, form);
                });
            });

            form.addEventListener('submit', (event) => {
                let isFormValid = true;
                inputs.forEach((input) => {
                    if (!validateField(input, form)) {
                        isFormValid = false;
                    }
                });

                if (!isFormValid) {
                    event.preventDefault();
                    showToast('Please fix validation errors before submitting.');

                    return;
                }

                if (form.hasAttribute('data-allow-submit')) {
                    return;
                }

                event.preventDefault();
                showToast('Form validated. Ready for backend POST handling.');
            });
        });
    }

    function initRoleVisibility() {
        const role = (document.body.getAttribute('data-role') || 'guest').toLowerCase();
        selectAll('[data-visible-for]').forEach((element) => {
            const allowed = (element.getAttribute('data-visible-for') || '')
                .split(',')
                .map((entry) => entry.trim().toLowerCase())
                .filter(Boolean);
            if (allowed.length > 0 && !allowed.includes(role.toLowerCase())) {
                element.classList.add('is-hidden');
            }
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        initMobileNav();
        initDropdowns();
        initTabs();
        initModals();
        initNotificationTriggers();
        initThemeToggle();
        initForms();
        initRoleVisibility();
    });
})();
