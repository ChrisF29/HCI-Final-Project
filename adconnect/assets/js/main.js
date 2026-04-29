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

            trigger.setAttribute('aria-expanded', 'false');

            trigger.addEventListener("click", (event) => {
                event.stopPropagation();
                const willOpen = !menu.classList.contains("is-open");

                selectAll('[data-dropdown-menu].is-open').forEach((openMenu) => {
                    openMenu.classList.remove("is-open");
                    const parent = openMenu.closest('[data-dropdown]');
                    const parentTrigger = parent ? selectOne('[data-dropdown-toggle]', parent) : null;
                    if (parentTrigger) {
                        parentTrigger.setAttribute('aria-expanded', 'false');
                    }
                });

                menu.classList.toggle("is-open", willOpen);
                trigger.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
            });
        });

        document.addEventListener("click", () => {
            selectAll('[data-dropdown-menu].is-open').forEach((menu) => {
                menu.classList.remove("is-open");
                const parent = menu.closest('[data-dropdown]');
                const trigger = parent ? selectOne('[data-dropdown-toggle]', parent) : null;
                if (trigger) {
                    trigger.setAttribute('aria-expanded', 'false');
                }
            });
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

    function fetchJson(url) {
        return fetch(url, { credentials: 'same-origin' })
            .then((response) => {
                if (!response.ok) {
                    throw new Error('Request failed');
                }
                return response.json();
            });
    }

    function renderNoticeItems(container, items, fallback) {
        container.innerHTML = '';

        if (!items || items.length === 0) {
            const empty = document.createElement('article');
            empty.className = 'notice-item';
            empty.textContent = fallback;
            container.appendChild(empty);
            return;
        }

        items.forEach((item) => {
            const notice = document.createElement('article');
            notice.className = 'notice-item';
            notice.textContent = item;
            container.appendChild(notice);
        });
    }

    function initAlertsPolling() {
        const modal = selectOne('[data-modal="alerts-modal"]');
        if (!modal) {
            return;
        }

        const list = selectOne('[data-alerts-list]', modal);
        const endpoint = modal.getAttribute('data-alerts-endpoint');
        if (!list || !endpoint) {
            return;
        }

        let timer = null;
        const intervalMs = 2000;

        const pollAlerts = () => {
            fetchJson(endpoint)
                .then((payload) => {
                    if (!payload || !payload.ok) {
                        const message = payload && payload.message ? payload.message : 'Unable to load alerts.';
                        renderNoticeItems(list, [], message);
                        return;
                    }
                    renderNoticeItems(list, payload.alerts || [], 'No alerts available right now.');
                })
                .catch(() => {
                    renderNoticeItems(list, [], 'Unable to load alerts.');
                });
        };

        const startPolling = () => {
            if (timer) {
                return;
            }
            pollAlerts();
            timer = window.setInterval(pollAlerts, intervalMs);
        };

        const stopPolling = () => {
            if (timer) {
                window.clearInterval(timer);
                timer = null;
            }
        };

        selectAll('[data-modal-target="alerts-modal"]').forEach((trigger) => {
            trigger.addEventListener('click', startPolling);
        });

        selectAll('[data-modal-close]', modal).forEach((closeBtn) => {
            closeBtn.addEventListener('click', stopPolling);
        });

        modal.addEventListener('click', (event) => {
            if (event.target === modal) {
                stopPolling();
            }
        });

        const observer = new MutationObserver(() => {
            if (!modal.classList.contains('is-open')) {
                stopPolling();
            }
        });
        observer.observe(modal, { attributes: true, attributeFilter: ['class'] });
    }

    function isNearBottom(element) {
        const threshold = 40;
        return element.scrollHeight - element.scrollTop - element.clientHeight < threshold;
    }

    function renderChatMessages(thread, messages, separator) {
        const shouldScroll = isNearBottom(thread);
        thread.innerHTML = '';

        if (!messages || messages.length === 0) {
            const empty = document.createElement('div');
            empty.className = 'notice-item';
            empty.textContent = 'No messages yet. Start the conversation below.';
            thread.appendChild(empty);
            return;
        }

        messages.forEach((message) => {
            const article = document.createElement('article');
            article.className = `chat-bubble ${message.isSent ? 'is-sent' : 'is-received'}`;

            const body = document.createElement('p');
            body.textContent = message.body || '';
            article.appendChild(body);

            const meta = document.createElement('div');
            meta.className = 'chat-meta';
            const label = message.senderLabel || 'User';
            const time = message.relativeTime || '';
            meta.textContent = `${label}${separator}${time}`;
            article.appendChild(meta);

            thread.appendChild(article);
        });

        if (shouldScroll) {
            thread.scrollTop = thread.scrollHeight;
        }
    }

    function initChatPolling() {
        const modals = selectAll('[data-chat-endpoint]');
        if (modals.length === 0) {
            return;
        }

        modals.forEach((modal) => {
            const endpoint = modal.getAttribute('data-chat-endpoint');
            const inquiryId = modal.getAttribute('data-chat-inquiry-id');
            const separator = modal.getAttribute('data-chat-meta-separator') || ' · ';
            const thread = selectOne('[data-chat-thread]', modal);
            const modalId = modal.getAttribute('data-modal');

            if (!endpoint || !inquiryId || !thread || !modalId) {
                return;
            }

            let timer = null;
            let lastMessageId = 0;
            const intervalMs = 2000;

            const pollMessages = () => {
                const url = `${endpoint}?inquiry_id=${encodeURIComponent(inquiryId)}`;
                fetchJson(url)
                    .then((payload) => {
                        if (!payload || !payload.ok) {
                            return;
                        }

                        const items = payload.messages || [];
                        const newestId = items.length > 0 ? items[items.length - 1].id : 0;
                        if (newestId === lastMessageId && thread.childElementCount > 0) {
                            return;
                        }

                        lastMessageId = newestId;
                        renderChatMessages(thread, items, separator);
                    })
                    .catch(() => {
                        return;
                    });
            };

            const startPolling = () => {
                if (timer) {
                    return;
                }
                pollMessages();
                timer = window.setInterval(pollMessages, intervalMs);
            };

            const stopPolling = () => {
                if (timer) {
                    window.clearInterval(timer);
                    timer = null;
                }
            };

            selectAll(`[data-modal-target="${modalId}"]`).forEach((trigger) => {
                trigger.addEventListener('click', startPolling);
            });

            selectAll('[data-modal-close]', modal).forEach((closeBtn) => {
                closeBtn.addEventListener('click', stopPolling);
            });

            modal.addEventListener('click', (event) => {
                if (event.target === modal) {
                    stopPolling();
                }
            });

            const observer = new MutationObserver(() => {
                if (!modal.classList.contains('is-open')) {
                    stopPolling();
                }
            });
            observer.observe(modal, { attributes: true, attributeFilter: ['class'] });

            if (modal.classList.contains('is-open')) {
                startPolling();
            }
        });
    }

    function applyTheme(theme) {
        const isNight = theme === 'night';
        document.body.classList.toggle('theme-night', isNight);

        const toggle = selectOne('[data-theme-toggle]');
        if (!toggle) {
            return;
        }

        const icon = isNight ? '\u2600' : '\u263E';
        const label = isNight ? 'Enable day mode' : 'Enable night mode';

        toggle.setAttribute('aria-pressed', String(isNight));
        toggle.setAttribute('aria-label', label);
        toggle.setAttribute('title', label);
        toggle.textContent = icon;
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

    function initConditionalRequired(form) {
        const conditionalFields = selectAll('[data-required-when]', form);
        if (conditionalFields.length === 0) {
            return;
        }

        const syncField = (field) => {
            const requiredWhen = (field.getAttribute('data-required-when') || '').trim().toLowerCase();
            const sourceName = (field.getAttribute('data-required-source') || 'account_type').trim();
            const source = selectOne(`[name="${sourceName}"]`, form);

            if (!source || !requiredWhen) {
                return;
            }

            const sourceValue = String(source.value || '').trim().toLowerCase();
            const isRequired = sourceValue === requiredWhen;
            field.toggleAttribute('required', isRequired);

            if (!isRequired) {
                field.classList.remove('is-invalid');

                const fieldName = field.getAttribute('name') || '';
                const errorContainer = selectOne(`[data-error-for="${fieldName}"]`, form);
                if (errorContainer) {
                    errorContainer.textContent = '';
                }
            }
        };

        conditionalFields.forEach((field) => {
            syncField(field);

            const sourceName = (field.getAttribute('data-required-source') || 'account_type').trim();
            const source = selectOne(`[name="${sourceName}"]`, form);
            if (!source) {
                return;
            }

            source.addEventListener('change', () => {
                syncField(field);
                validateField(field, form);
            });
        });
    }

    function initForms() {
        selectAll('form[data-validate]').forEach((form) => {
            const inputs = selectAll('input, select, textarea', form);

            initConditionalRequired(form);

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
        initAlertsPolling();
        initChatPolling();
        initThemeToggle();
        initForms();
        initRoleVisibility();
    });
})();
