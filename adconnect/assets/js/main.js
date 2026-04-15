(() => {
    const path = window.location.pathname.toLowerCase();
    const marker = '/adconnect/';
    const markerIndex = path.indexOf(marker);
    const appBasePath = markerIndex >= 0
        ? window.location.pathname.slice(0, markerIndex + marker.length - 1)
        : '/adconnect';

    const appData = {
        listings: [
            {
                id: 1,
                name: "BrightPixel Studio",
                category: "Creative",
                location: "Manila",
                rating: 4.8,
                budget: "mid",
                specialties: ["Branding", "Social Media"],
                description: "Creative campaigns for startups and growing brands."
            },
            {
                id: 2,
                name: "MetroReach Media",
                category: "Digital",
                location: "Cebu",
                rating: 4.6,
                budget: "high",
                specialties: ["PPC", "Performance"],
                description: "Data-driven ads with conversion-first strategy."
            },
            {
                id: 3,
                name: "Northlight Productions",
                category: "Video",
                location: "Davao",
                rating: 4.7,
                budget: "mid",
                specialties: ["Video", "Storytelling"],
                description: "Video-first campaigns for product launch moments."
            },
            {
                id: 4,
                name: "Community Buzz PH",
                category: "Events",
                location: "Baguio",
                rating: 4.5,
                budget: "low",
                specialties: ["Events", "Activation"],
                description: "Hyperlocal events and street-level campaign execution."
            }
        ],
        ads: [
            {
                id: 101,
                title: "Summer Promo Campaign",
                owner: "BrightPixel Studio",
                channel: "social",
                status: "Live",
                budget: "PHP 120,000",
                location: "Manila",
                objective: "Lead Generation"
            },
            {
                id: 102,
                title: "Grand Opening Push",
                owner: "Community Buzz PH",
                channel: "events",
                status: "Review",
                budget: "PHP 45,000",
                location: "Baguio",
                objective: "Awareness"
            },
            {
                id: 103,
                title: "Holiday Search Blitz",
                owner: "MetroReach Media",
                channel: "search",
                status: "Live",
                budget: "PHP 200,000",
                location: "Cebu",
                objective: "Sales"
            },
            {
                id: 104,
                title: "New Product Teaser",
                owner: "Northlight Productions",
                channel: "video",
                status: "Planned",
                budget: "PHP 90,000",
                location: "Davao",
                objective: "Engagement"
            }
        ],
        notifications: [
            "A new business profile is waiting for approval.",
            "Two inquiries need a response before end of day.",
            "Your campaign CTR is up by 8% this week."
        ]
    };

    window.AdConnectData = appData;

    const selectOne = (selector, scope = document) => scope.querySelector(selector);
    const selectAll = (selector, scope = document) => Array.from(scope.querySelectorAll(selector));

    function listingCard(item) {
        const tags = item.specialties
            .map((tag) => `<span class="chip">${tag}</span>`)
            .join("");

        return `
            <article class="card js-search-item js-filter-item" data-search-item data-filter-item data-category="${item.category.toLowerCase()}" data-location="${item.location.toLowerCase()}" data-budget="${item.budget}" data-keywords="${item.name.toLowerCase()} ${item.category.toLowerCase()} ${item.location.toLowerCase()}">
                <div class="card-top">
                    <h3>${item.name}</h3>
                    <span class="badge badge-neutral">${item.rating.toFixed(1)} ★</span>
                </div>
                <p>${item.description}</p>
                <div class="chip-row" style="margin-top:0.7rem">${tags}</div>
                <div class="inline-split" style="margin-top:0.8rem">
                    <small>${item.category} · ${item.location}</small>
                    <a class="btn-ghost" href="${appBasePath}/pages/business-profile.php">View</a>
                </div>
            </article>
        `;
    }

    function adCard(item) {
        const badgeClass = item.status === "Live" ? "badge-success" : item.status === "Review" ? "badge-warning" : "badge-neutral";

        return `
            <article class="card js-search-item js-filter-item" data-search-item data-filter-item data-channel="${item.channel}" data-location="${item.location.toLowerCase()}" data-status="${item.status.toLowerCase()}" data-keywords="${item.title.toLowerCase()} ${item.owner.toLowerCase()} ${item.channel.toLowerCase()}">
                <div class="card-top">
                    <h3>${item.title}</h3>
                    <span class="badge ${badgeClass}">${item.status}</span>
                </div>
                <p><strong>${item.owner}</strong></p>
                <p>${item.objective} · ${item.location}</p>
                <div class="inline-split" style="margin-top:0.8rem">
                    <small>${item.budget}</small>
                    <button class="btn-ghost" type="button" data-notify="Ad details are currently simulated with static data.">Preview</button>
                </div>
            </article>
        `;
    }

    function renderFeeds() {
        selectAll('[data-feed="listings"]').forEach((container) => {
            container.innerHTML = appData.listings.map(listingCard).join("");
        });

        selectAll('[data-feed="ads"]').forEach((container) => {
            container.innerHTML = appData.ads.map(adCard).join("");
        });

        selectAll('[data-feed="notifications"]').forEach((container) => {
            container.innerHTML = appData.notifications
                .map((note) => `<article class="notice-item">${note}</article>`)
                .join("");
        });
    }

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

                event.preventDefault();
                showToast(isFormValid ? 'Form validated. Ready for backend POST handling.' : 'Please fix validation errors before submitting.');
            });
        });
    }

    function initRoleVisibility() {
        const role = new URLSearchParams(window.location.search).get('role') || 'guest';
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
        renderFeeds();
        initMobileNav();
        initDropdowns();
        initTabs();
        initModals();
        initNotificationTriggers();
        initForms();
        initRoleVisibility();
    });
})();
