<?php
require_once dirname(__DIR__) . '/includes/config.php';

$pageTitle = 'Ads Feed';
$activePage = 'ads';

require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/includes/navbar.php';
?>
<main class="page-main">
    <div class="container section-stack">
        <section class="page-hero">
            <nav class="breadcrumbs" aria-label="Breadcrumb">
                <a href="<?php echo e(url('pages/home.php')); ?>">Home</a>
                <span>/</span>
                <span>Ads Feed</span>
            </nav>
            <h1>Campaign feed with simulated moderation states</h1>
            <p>Preview advertisement cards, filter channels, and test role-based visibility before backend integration with PHP and MySQL.</p>
        </section>

        <section class="tabs" data-tabs>
            <div class="tab-list" role="tablist" aria-label="Ad feed tabs">
                <button class="is-active" type="button" data-tab-target="ads-all">All Campaigns</button>
                <button type="button" data-tab-target="ads-live">Live Focus</button>
                <button type="button" data-tab-target="ads-safety">Moderation Notes</button>
            </div>

            <article class="tab-panel is-active" data-tab-panel="ads-all">
                <section class="section-stack" data-search-scope data-filter-scope>
                    <div class="toolbar">
                        <input type="search" data-search-input placeholder="Search ad title or agency">
                        <select name="channel" data-filter-select>
                            <option value="all">All Channels</option>
                            <option value="social">Social</option>
                            <option value="search">Search</option>
                            <option value="video">Video</option>
                            <option value="events">Events</option>
                        </select>
                        <select name="status" data-filter-select>
                            <option value="all">All Status</option>
                            <option value="live">Live</option>
                            <option value="review">Review</option>
                            <option value="planned">Planned</option>
                        </select>
                        <button class="btn-ghost" type="button" data-filter-reset>Reset</button>
                    </div>
                    <p><strong><span data-filter-count>0</span></strong> campaigns currently visible.</p>
                    <div class="card-grid" data-feed="ads"></div>
                    <div class="empty-state is-hidden" data-filter-empty data-empty-state>
                        <p>No campaigns match your current criteria.</p>
                    </div>
                </section>
            </article>

            <article class="tab-panel" data-tab-panel="ads-live">
                <p>Live campaigns should prioritize clear CTA language, honest claims, and audience fit. Once backend is connected, this tab can fetch live-only items from the database.</p>
            </article>

            <article class="tab-panel" data-tab-panel="ads-safety">
                <p>Moderation placeholders are included for review states, role-based actions, and content policy checks in future admin workflows.</p>
            </article>
        </section>
    </div>
</main>
<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
