# AdConnect Page Documentation

This document explains what each page in the AdConnect codebase does and what it is for.

## Scope

- This covers route pages users can open in the browser.
- It also includes shared PHP layout components used by those pages.
- All pages are currently frontend-simulated and backend-ready for future PHP + MySQL logic.

## Entry Route

### [index.php](index.php)

- Purpose: Main entry point for the AdConnect app.
- What it does: Redirects users to [pages/home.php](pages/home.php).
- Why it exists: Keeps a clean root URL and centralizes initial routing.

## Public Pages

### [pages/home.php](pages/home.php)

- Purpose: Landing and overview page.
- What it does: Shows featured businesses, ad feed preview, quick inquiry modal, and platform flow tabs.
- Why it exists: Helps users quickly understand and start using AdConnect.

### [pages/directory.php](pages/directory.php)

- Purpose: Discover business listings.
- What it does: Displays searchable and filterable business cards from simulated data.
- Why it exists: Core discovery page for client-business matching.

### [pages/ads.php](pages/ads.php)

- Purpose: Public ad campaign feed.
- What it does: Shows campaign cards with status/channel filters and tabbed content.
- Why it exists: Lets users browse active/planned campaigns and moderation states.

### [pages/business-profile.php](pages/business-profile.php)

- Purpose: Individual business detail page.
- What it does: Shows company overview, services, reviews, and inquiry form.
- Why it exists: Supports evaluation and direct contact before campaign collaboration.

### [pages/about.php](pages/about.php)

- Purpose: Platform identity page.
- What it does: Explains mission, vision, and principles.
- Why it exists: Builds trust and explains the project direction.

### [pages/help.php](pages/help.php)

- Purpose: User support center.
- What it does: Provides FAQ, quick guides, and support form.
- Why it exists: Gives users self-service help and a fallback contact flow.

## Authentication Pages

### [pages/auth/login.php](pages/auth/login.php)

- Purpose: Account sign-in.
- What it does: Provides login form with frontend validation.
- Why it exists: Entry point for role-based dashboards.

### [pages/auth/register.php](pages/auth/register.php)

- Purpose: New account onboarding.
- What it does: Collects role, profile, and credentials with validation.
- Why it exists: Standard user/business registration flow prepared for backend processing.

### [pages/auth/forgot-password.php](pages/auth/forgot-password.php)

- Purpose: Password recovery initiation.
- What it does: Accepts email and simulates reset request flow.
- Why it exists: Supports account recovery lifecycle.

## Client (User) Dashboard Pages

### [pages/user/dashboard.php](pages/user/dashboard.php)

- Purpose: Client workspace home.
- What it does: Shows KPIs, progress meters, notifications, and recommended listings.
- Why it exists: Gives clients a centralized campaign and partner overview.

### [pages/user/favorites.php](pages/user/favorites.php)

- Purpose: Saved business shortlist.
- What it does: Displays favorited listings with search/filter controls.
- Why it exists: Helps clients compare and revisit preferred providers.

### [pages/user/messages.php](pages/user/messages.php)

- Purpose: Client message center.
- What it does: Lists conversation summaries and provides send-message form.
- Why it exists: Supports communication between clients and businesses.

### [pages/user/reviews.php](pages/user/reviews.php)

- Purpose: Client review management.
- What it does: Lists existing reviews and allows new review submission via modal.
- Why it exists: Encourages transparent feedback and quality signaling.

### [pages/user/profile-settings.php](pages/user/profile-settings.php)

- Purpose: Client profile management.
- What it does: Lets users update personal info, password, and preferences.
- Why it exists: Keeps account data current and configurable.

## Business Dashboard Pages

### [pages/business/dashboard.php](pages/business/dashboard.php)

- Purpose: Business workspace home.
- What it does: Shows business KPIs, campaign health meters, and notifications.
- Why it exists: Gives businesses an operational summary of ad performance.

### [pages/business/manage-profile.php](pages/business/manage-profile.php)

- Purpose: Business profile editing.
- What it does: Updates business identity, contacts, category, and description.
- Why it exists: Maintains listing quality for better directory matching.

### [pages/business/manage-ads.php](pages/business/manage-ads.php)

- Purpose: Ad creation and ad list management.
- What it does: Provides ad upload form and searchable/filterable ad cards.
- Why it exists: Core page for publishing and monitoring campaign content.

### [pages/business/campaigns.php](pages/business/campaigns.php)

- Purpose: Campaign planning and tracking.
- What it does: Shows campaign snapshots and create-campaign form.
- Why it exists: Supports campaign scheduling and ownership tracking.

### [pages/business/analytics.php](pages/business/analytics.php)

- Purpose: Performance analytics view.
- What it does: Displays KPI counters, channel performance bars, and campaign table.
- Why it exists: Helps optimize budget and channel strategy.

### [pages/business/inquiries.php](pages/business/inquiries.php)

- Purpose: Client inquiry management.
- What it does: Lists incoming inquiries and provides reply modal form.
- Why it exists: Supports lead response workflow and SLA awareness.

## Admin Dashboard Pages

### [pages/admin/dashboard.php](pages/admin/dashboard.php)

- Purpose: Admin control center.
- What it does: Shows platform-level KPIs and moderation health indicators.
- Why it exists: Central overview for governance and operations.

### [pages/admin/users.php](pages/admin/users.php)

- Purpose: User administration.
- What it does: Displays user table with role and status information.
- Why it exists: Enables account monitoring and lifecycle management.

### [pages/admin/approvals.php](pages/admin/approvals.php)

- Purpose: Business approval queue.
- What it does: Lists pending profiles and simulated approval actions.
- Why it exists: Controls quality before listings go public.

### [pages/admin/ads-moderation.php](pages/admin/ads-moderation.php)

- Purpose: Ad moderation workflow.
- What it does: Provides searchable/filterable view of campaign submissions.
- Why it exists: Ensures ad content aligns with policy expectations.

### [pages/admin/categories.php](pages/admin/categories.php)

- Purpose: Taxonomy administration.
- What it does: Allows adding categories and viewing category usage table.
- Why it exists: Keeps classification consistent for search/filter quality.

### [pages/admin/reports.php](pages/admin/reports.php)

- Purpose: Incident and policy report management.
- What it does: Lists report cases and provides internal report form.
- Why it exists: Supports trust, safety, and compliance operations.

## Error Pages

### [pages/errors/404.php](pages/errors/404.php)

- Purpose: Not-found handling.
- What it does: Informs users the route is missing and gives navigation options.
- Why it exists: Improves recovery UX for invalid URLs.

### [pages/errors/access-denied.php](pages/errors/access-denied.php)

- Purpose: Permission fallback page.
- What it does: Informs users access is restricted and provides next actions.
- Why it exists: Supports role-based restrictions and safe redirection flow.

## Shared Layout and Utility Components (Non-Route)

### [includes/config.php](includes/config.php)

- Purpose: App bootstrap and future backend configuration placeholder.
- What it does: Starts session, defines role simulation, base URL helper, and sanitization placeholder.
- Why it exists: Centralized setup point for future DB/auth logic.

### [includes/header.php](includes/header.php)

- Purpose: Reusable page head and opening body markup.
- What it does: Loads meta tags, fonts, CSS files, and notification container.
- Why it exists: Keeps all pages visually and structurally consistent.

### [includes/navbar.php](includes/navbar.php)

- Purpose: Global top navigation.
- What it does: Renders main links, search, role indicator, and portal dropdown.
- Why it exists: Gives a consistent global navigation layer.

### [includes/sidebar.php](includes/sidebar.php)

- Purpose: Role-based dashboard navigation.
- What it does: Renders menu links for client, business, and admin sections.
- Why it exists: Improves dashboard usability with contextual navigation.

### [includes/footer.php](includes/footer.php)

- Purpose: Shared footer and script loader.
- What it does: Renders footer links and loads core JavaScript modules.
- Why it exists: Ensures every page has consistent footer and interactions.

## Frontend Behavior Sources

### [assets/js/main.js](assets/js/main.js)

- Handles simulated data, feed rendering, modals, tabs, dropdowns, notifications, role visibility, and form validation.

### [assets/js/search.js](assets/js/search.js)

- Handles scoped text search for listing and ad cards.

### [assets/js/filter.js](assets/js/filter.js)

- Handles scoped select-based filtering and reset logic.

### [assets/js/dashboard.js](assets/js/dashboard.js)

- Handles animated counters, progress meters, and dynamic date stamps.
