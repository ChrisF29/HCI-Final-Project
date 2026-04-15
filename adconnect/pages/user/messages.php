<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';

$pageTitle = 'Messages';
$activePage = '';
$sidebarRole = 'user';
$sidebarPage = 'messages';

require_once dirname(__DIR__, 2) . '/includes/header.php';
require_once dirname(__DIR__, 2) . '/includes/navbar.php';
?>
<main class="page-main">
    <div class="container content-grid">
        <?php require dirname(__DIR__, 2) . '/includes/sidebar.php'; ?>

        <div class="section-stack">
            <section class="page-hero">
                <nav class="breadcrumbs" aria-label="Breadcrumb">
                    <a href="<?php echo e(url('pages/home.php')); ?>">Home</a>
                    <span>/</span>
                    <a href="<?php echo e(url('pages/user/dashboard.php?role=client')); ?>">Client Dashboard</a>
                    <span>/</span>
                    <span>Messages</span>
                </nav>
                <h1>Message center</h1>
                <p>Track incoming replies from businesses and continue conversations.</p>
            </section>

            <section class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Business</th>
                            <th>Subject</th>
                            <th>Status</th>
                            <th>Updated</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>BrightPixel Studio</td><td>Campaign discovery call</td><td><span class="badge badge-success">Open</span></td><td>2h ago</td></tr>
                        <tr><td>MetroReach Media</td><td>Budget clarification</td><td><span class="badge badge-warning">Pending</span></td><td>Today</td></tr>
                        <tr><td>Northlight Productions</td><td>Creative samples</td><td><span class="badge badge-neutral">Reviewed</span></td><td>Yesterday</td></tr>
                    </tbody>
                </table>
            </section>

            <section class="card section-stack">
                <h3>Send a new message</h3>
                <form action="#" method="POST" data-validate>
                    <div class="form-grid">
                        <div class="form-field">
                            <label for="msg-recipient">Recipient</label>
                            <select id="msg-recipient" name="recipient" required>
                                <option value="">Choose business</option>
                                <option value="brightpixel">BrightPixel Studio</option>
                                <option value="metroreach">MetroReach Media</option>
                                <option value="northlight">Northlight Productions</option>
                            </select>
                            <small class="field-error" data-error-for="recipient"></small>
                        </div>
                        <div class="form-field">
                            <label for="msg-subject">Subject</label>
                            <input id="msg-subject" name="subject" required>
                            <small class="field-error" data-error-for="subject"></small>
                        </div>
                    </div>
                    <div class="form-grid full">
                        <div class="form-field">
                            <label for="msg-body">Message</label>
                            <textarea id="msg-body" name="message_body" required data-minlength="15"></textarea>
                            <small class="field-error" data-error-for="message_body"></small>
                        </div>
                    </div>
                    <button class="btn" type="submit">Send Message</button>
                </form>
            </section>
        </div>
    </div>
</main>
<?php require_once dirname(__DIR__, 2) . '/includes/footer.php'; ?>
