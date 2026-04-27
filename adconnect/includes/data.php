<?php
declare(strict_types=1);

function active_client_user_id(): ?int
{
    $fromQuery = query_int('client_id') ?? query_int('user_id');
    if ($fromQuery !== null) {
        return $fromQuery;
    }

    $id = db_value(
        "SELECT id
         FROM users
         WHERE role = 'client'
         ORDER BY CASE WHEN status IN ('active', 'verified') THEN 0 ELSE 1 END, id ASC
         LIMIT 1"
    );

    return $id !== null ? (int) $id : null;
}

function active_business_user_id(): ?int
{
    $fromQuery = query_int('business_user_id') ?? query_int('user_id');
    if ($fromQuery !== null) {
        return $fromQuery;
    }

    $id = db_value(
        "SELECT id
         FROM users
         WHERE role = 'business'
         ORDER BY CASE WHEN status IN ('active', 'verified') THEN 0 ELSE 1 END, id ASC
         LIMIT 1"
    );

    return $id !== null ? (int) $id : null;
}

function active_business_profile_id(): ?int
{
    $fromQuery = query_int('business_id');
    if ($fromQuery !== null) {
        return $fromQuery;
    }

    $businessUserId = active_business_user_id();
    if ($businessUserId !== null) {
        $businessId = db_value(
            'SELECT id FROM business_profiles WHERE user_id = :user_id LIMIT 1',
            ['user_id' => $businessUserId]
        );

        if ($businessId !== null) {
            return (int) $businessId;
        }
    }

    $fallback = db_value(
        "SELECT id
         FROM business_profiles
         ORDER BY CASE WHEN approval_status = 'approved' THEN 0 ELSE 1 END, rating DESC, id ASC
         LIMIT 1"
    );

    return $fallback !== null ? (int) $fallback : null;
}

function fetch_business_listings(int $limit = 24, ?int $clientUserId = null, bool $favoritesOnly = false): array
{
    if ($favoritesOnly && $clientUserId === null) {
        return [];
    }

    $limit = max(1, min(100, $limit));
    $clientUserId = $clientUserId ?? 0;

    $favoriteJoin = $favoritesOnly
        ? 'INNER JOIN favorites f ON f.business_id = bp.id AND f.client_user_id = :client_user_id'
        : 'LEFT JOIN favorites f ON f.business_id = bp.id AND f.client_user_id = :client_user_id';

    $rows = db_all(
        "SELECT
            bp.id,
            bp.business_name,
            COALESCE(c.name, 'Uncategorized') AS category_name,
            COALESCE(c.slug, 'uncategorized') AS category_slug,
            COALESCE(bp.city, 'Unspecified') AS city,
            bp.rating,
            bp.budget_tier,
            COALESCE(bp.description, 'No profile description yet.') AS description,
            COALESCE(GROUP_CONCAT(DISTINCT bs.specialty ORDER BY bs.specialty SEPARATOR '||'), '') AS specialties,
            CASE WHEN f.client_user_id IS NULL THEN 0 ELSE 1 END AS is_favorite
        FROM business_profiles bp
        LEFT JOIN categories c ON c.id = bp.category_id
        LEFT JOIN business_specialties bs ON bs.business_id = bp.id
        {$favoriteJoin}
        WHERE bp.approval_status = 'approved'
        GROUP BY
            bp.id,
            bp.business_name,
            c.name,
            c.slug,
            bp.city,
            bp.rating,
            bp.budget_tier,
            bp.description,
            f.client_user_id
        ORDER BY bp.is_verified DESC, bp.rating DESC, bp.business_name ASC
        LIMIT {$limit}",
        ['client_user_id' => $clientUserId]
    );

    foreach ($rows as &$row) {
        $specialtyString = (string) ($row['specialties'] ?? '');
        $specialties = array_values(array_filter(array_map('trim', explode('||', $specialtyString))));
        $row['specialties'] = $specialties;
        $row['rating'] = (float) ($row['rating'] ?? 0);
    }

    return $rows;
}

function fetch_ads_feed(int $limit = 24, ?int $businessId = null, ?string $status = null): array
{
    $limit = max(1, min(150, $limit));
    $conditions = [];
    $params = [];

    if ($businessId !== null) {
        $conditions[] = 'a.business_id = :business_id';
        $params['business_id'] = $businessId;
    }

    if ($status !== null && $status !== '') {
        $conditions[] = 'a.status = :status';
        $params['status'] = strtolower($status);
    }

    $whereClause = '';
    if ($conditions !== []) {
        $whereClause = 'WHERE ' . implode(' AND ', $conditions);
    }

    $rows = db_all(
        "SELECT
            a.id,
            a.title,
            a.channel,
            a.status,
            a.objective,
            a.location,
            a.budget_amount,
            COALESCE(bp.business_name, 'Unknown business') AS owner_name
        FROM ads a
        LEFT JOIN business_profiles bp ON bp.id = a.business_id
        {$whereClause}
        ORDER BY
            CASE a.status
                WHEN 'review' THEN 1
                WHEN 'live' THEN 2
                WHEN 'planned' THEN 3
                WHEN 'paused' THEN 4
                ELSE 5
            END,
            a.updated_at DESC,
            a.id DESC
        LIMIT {$limit}",
        $params
    );

    foreach ($rows as &$row) {
        $row['budget_amount'] = (float) ($row['budget_amount'] ?? 0);
        $row['status'] = strtolower((string) ($row['status'] ?? 'planned'));
        $row['channel'] = strtolower((string) ($row['channel'] ?? 'social'));
    }

    return $rows;
}

function fetch_moderation_ad_detail(int $adId): ?array
{
    if ($adId <= 0) {
        return null;
    }

    $row = db_one(
        "SELECT
            a.id,
            a.title,
            a.status,
            a.channel,
            COALESCE(a.location, 'Unspecified') AS location,
            a.objective,
            a.budget_amount,
            COALESCE(a.description, 'No campaign description provided.') AS description,
            COALESCE(a.moderation_notes, 'No moderation notes yet.') AS moderation_notes,
            a.created_at,
            a.updated_at,
            a.published_at,
            COALESCE(bp.business_name, 'Unknown business') AS owner_name,
            COALESCE(c.name, 'Uncategorized') AS category_name
        FROM ads a
        LEFT JOIN business_profiles bp ON bp.id = a.business_id
        LEFT JOIN categories c ON c.id = bp.category_id
        WHERE a.id = :id
        LIMIT 1",
        ['id' => $adId]
    );

    if (!$row) {
        return null;
    }

    $row['budget_amount'] = (float) ($row['budget_amount'] ?? 0);
    $row['status'] = strtolower((string) ($row['status'] ?? 'planned'));
    $row['channel'] = strtolower((string) ($row['channel'] ?? 'social'));

    return $row;
}

function fetch_business_ad_detail(int $adId, int $businessId): ?array
{
    if ($adId <= 0 || $businessId <= 0) {
        return null;
    }

    $row = db_one(
        "SELECT
            a.id,
            a.title,
            a.status,
            a.channel,
            COALESCE(a.location, 'Unspecified') AS location,
            a.objective,
            a.budget_amount,
            COALESCE(a.description, 'No campaign description provided.') AS description,
            COALESCE(a.moderation_notes, 'No moderation notes yet.') AS moderation_notes,
            a.created_at,
            a.updated_at,
            a.published_at,
            COALESCE(bp.business_name, 'Unknown business') AS owner_name
        FROM ads a
        LEFT JOIN business_profiles bp ON bp.id = a.business_id
        WHERE a.id = :id
          AND a.business_id = :business_id
        LIMIT 1",
        [
            'id' => $adId,
            'business_id' => $businessId,
        ]
    );

    if (!$row) {
        return null;
    }

    $row['budget_amount'] = (float) ($row['budget_amount'] ?? 0);
    $row['status'] = strtolower((string) ($row['status'] ?? 'planned'));
    $row['channel'] = strtolower((string) ($row['channel'] ?? 'social'));

    return $row;
}

function fetch_notifications(string $role, ?int $userId = null, int $limit = 8): array
{
    $limit = max(1, min(50, $limit));
    $params = ['role' => strtolower($role)];

    if ($userId !== null) {
        $params['user_id'] = $userId;
        $rows = db_all(
            "SELECT message
             FROM notifications
             WHERE (audience_role = 'all' OR audience_role = :role)
               AND (user_id IS NULL OR user_id = :user_id)
             ORDER BY created_at DESC, id DESC
             LIMIT {$limit}",
            $params
        );
    } else {
        $rows = db_all(
            "SELECT message
             FROM notifications
             WHERE (audience_role = 'all' OR audience_role = :role)
               AND user_id IS NULL
             ORDER BY created_at DESC, id DESC
             LIMIT {$limit}",
            $params
        );
    }

    return array_values(array_filter(array_map(
        static fn(array $row): string => trim((string) ($row['message'] ?? '')),
        $rows
    )));
}

function fetch_business_profile(?int $businessId): ?array
{
    if ($businessId === null) {
        return null;
    }

    $row = db_one(
        "SELECT
            bp.id,
            bp.user_id,
            bp.business_name,
            COALESCE(c.name, 'Uncategorized') AS category_name,
            COALESCE(c.slug, 'uncategorized') AS category_slug,
            COALESCE(bp.city, 'Unspecified') AS city,
            bp.budget_tier,
            bp.rating,
            COALESCE(bp.description, 'No profile description yet.') AS description,
            COALESCE(bp.contact_email, '') AS contact_email,
            COALESCE(bp.contact_phone, '') AS contact_phone,
            bp.approval_status,
            COALESCE(GROUP_CONCAT(DISTINCT bs.specialty ORDER BY bs.specialty SEPARATOR '||'), '') AS specialties
        FROM business_profiles bp
        LEFT JOIN categories c ON c.id = bp.category_id
        LEFT JOIN business_specialties bs ON bs.business_id = bp.id
        WHERE bp.id = :business_id
        GROUP BY
            bp.id,
            bp.user_id,
            bp.business_name,
            c.name,
            c.slug,
            bp.city,
            bp.budget_tier,
            bp.rating,
            bp.description,
            bp.contact_email,
            bp.contact_phone,
            bp.approval_status",
        ['business_id' => $businessId]
    );

    if (!$row) {
        return null;
    }

    $specialties = array_values(array_filter(array_map('trim', explode('||', (string) $row['specialties']))));
    $row['specialties'] = $specialties;
    $row['rating'] = (float) ($row['rating'] ?? 0);

    return $row;
}

function fetch_business_campaigns(?int $businessId, int $limit = 20): array
{
    if ($businessId === null) {
        return [];
    }

    $limit = max(1, min(100, $limit));

    $rows = db_all(
        "SELECT
            id,
            name,
            owner_name,
            status,
            budget_amount,
            start_date,
            end_date,
            objective
        FROM campaigns
        WHERE business_id = :business_id
        ORDER BY updated_at DESC, id DESC
        LIMIT {$limit}",
        ['business_id' => $businessId]
    );

    foreach ($rows as &$row) {
        $row['budget_amount'] = (float) ($row['budget_amount'] ?? 0);
        $row['status'] = strtolower((string) ($row['status'] ?? 'planned'));
    }

    return $rows;
}

function fetch_business_analytics_summary(?int $businessId): array
{
    if ($businessId === null) {
        return [
            'impressions' => 0,
            'clicks' => 0,
            'leads' => 0,
            'spend' => 0.0,
            'avg_ctr' => 0.0,
            'cost_per_lead' => 0.0,
        ];
    }

    $row = db_one(
        "SELECT
            COALESCE(SUM(cad.impressions), 0) AS impressions,
            COALESCE(SUM(cad.clicks), 0) AS clicks,
            COALESCE(SUM(cad.leads), 0) AS leads,
            COALESCE(SUM(cad.spend_amount), 0) AS spend
        FROM campaigns c
        LEFT JOIN campaign_analytics_daily cad ON cad.campaign_id = c.id
        WHERE c.business_id = :business_id",
        ['business_id' => $businessId]
    );

    $impressions = (int) ($row['impressions'] ?? 0);
    $clicks = (int) ($row['clicks'] ?? 0);
    $leads = (int) ($row['leads'] ?? 0);
    $spend = (float) ($row['spend'] ?? 0);
    $avgCtr = $impressions > 0 ? ($clicks / $impressions) * 100 : 0;
    $costPerLead = $leads > 0 ? ($spend / $leads) : 0;

    return [
        'impressions' => $impressions,
        'clicks' => $clicks,
        'leads' => $leads,
        'spend' => $spend,
        'avg_ctr' => $avgCtr,
        'cost_per_lead' => $costPerLead,
    ];
}

function fetch_business_channel_distribution(?int $businessId): array
{
    $default = [
        'social' => 0,
        'search' => 0,
        'video' => 0,
        'events' => 0,
    ];

    if ($businessId === null) {
        return $default;
    }

    $rows = db_all(
        "SELECT channel, COUNT(*) AS total
         FROM ads
         WHERE business_id = :business_id
         GROUP BY channel",
        ['business_id' => $businessId]
    );

    $totals = $default;
    $grandTotal = 0;

    foreach ($rows as $row) {
        $channel = strtolower((string) ($row['channel'] ?? ''));
        if (!array_key_exists($channel, $totals)) {
            continue;
        }

        $count = (int) ($row['total'] ?? 0);
        $totals[$channel] = $count;
        $grandTotal += $count;
    }

    if ($grandTotal === 0) {
        return $default;
    }

    foreach ($totals as $channel => $count) {
        $totals[$channel] = pct(($count / $grandTotal) * 100);
    }

    return $totals;
}

function fetch_campaign_analytics_rows(?int $businessId, int $limit = 20): array
{
    if ($businessId === null) {
        return [];
    }

    $limit = max(1, min(100, $limit));

    $rows = db_all(
        "SELECT
            c.id,
            c.name,
            c.status,
            COALESCE(SUM(cad.impressions), 0) AS impressions,
            COALESCE(SUM(cad.clicks), 0) AS clicks,
            COALESCE(SUM(cad.leads), 0) AS leads
        FROM campaigns c
        LEFT JOIN campaign_analytics_daily cad ON cad.campaign_id = c.id
        WHERE c.business_id = :business_id
        GROUP BY c.id, c.name, c.status
        ORDER BY c.updated_at DESC, c.id DESC
        LIMIT {$limit}",
        ['business_id' => $businessId]
    );

    foreach ($rows as &$row) {
        $impressions = (int) ($row['impressions'] ?? 0);
        $clicks = (int) ($row['clicks'] ?? 0);
        $ctr = $impressions > 0 ? ($clicks / $impressions) * 100 : 0;
        $row['ctr'] = $ctr;
        $row['status'] = strtolower((string) ($row['status'] ?? 'planned'));
        $row['leads'] = (int) ($row['leads'] ?? 0);
    }

    return $rows;
}

function fetch_inquiries_for_business(?int $businessId, int $limit = 50): array
{
    if ($businessId === null) {
        return [];
    }

    $limit = max(1, min(200, $limit));

    return db_all(
        "SELECT
            i.id,
            COALESCE(NULLIF(u.display_name, ''), CONCAT(u.first_name, ' ', u.last_name), u.email) AS client_name,
            i.campaign_need,
            i.budget_amount,
            i.status,
            i.updated_at
        FROM inquiries i
        LEFT JOIN users u ON u.id = i.client_user_id
        WHERE i.business_id = :business_id
        ORDER BY i.updated_at DESC, i.id DESC
        LIMIT {$limit}",
        ['business_id' => $businessId]
    );
}

function fetch_messages_for_client(?int $clientUserId, int $limit = 50): array
{
    if ($clientUserId === null) {
        return [];
    }

    $limit = max(1, min(200, $limit));

    return db_all(
        "SELECT
            m.id,
            COALESCE(bp.business_name, 'Business') AS business_name,
            COALESCE(i.campaign_need, m.subject, 'No inquiry topic') AS inquiry_topic,
            i.status AS inquiry_status,
            i.budget_amount,
            m.subject,
            m.message_status,
            m.created_at,
            i.updated_at AS inquiry_updated_at,
            CASE
                WHEN m.sender_user_id = :direction_client_user_id THEN 'sent'
                ELSE 'received'
            END AS message_direction
        FROM messages m
        LEFT JOIN inquiries i ON i.id = m.inquiry_id
        LEFT JOIN business_profiles bp ON bp.id = i.business_id
        WHERE m.recipient_user_id = :recipient_client_user_id
           OR m.sender_user_id = :sender_client_user_id
        ORDER BY m.created_at DESC, m.id DESC
        LIMIT {$limit}",
        [
            'direction_client_user_id' => $clientUserId,
            'recipient_client_user_id' => $clientUserId,
            'sender_client_user_id' => $clientUserId,
        ]
    );
}

function fetch_reviews_for_client(?int $clientUserId, int $limit = 50): array
{
    if ($clientUserId === null) {
        return [];
    }

    $limit = max(1, min(200, $limit));

    return db_all(
        "SELECT
            r.id,
            COALESCE(bp.business_name, 'Business') AS business_name,
            r.rating,
            r.comment,
            r.created_at
        FROM reviews r
        LEFT JOIN business_profiles bp ON bp.id = r.business_id
        WHERE r.client_user_id = :client_user_id
        ORDER BY r.created_at DESC, r.id DESC
        LIMIT {$limit}",
        ['client_user_id' => $clientUserId]
    );
}

function fetch_reviews_for_business(?int $businessId, int $limit = 10): array
{
    if ($businessId === null) {
        return [];
    }

    $limit = max(1, min(100, $limit));

    return db_all(
        "SELECT
            r.id,
            r.rating,
            r.comment,
            r.created_at,
            COALESCE(NULLIF(u.display_name, ''), CONCAT(u.first_name, ' ', u.last_name), 'Client') AS reviewer_name
        FROM reviews r
        LEFT JOIN users u ON u.id = r.client_user_id
        WHERE r.business_id = :business_id
        ORDER BY r.created_at DESC, r.id DESC
        LIMIT {$limit}",
        ['business_id' => $businessId]
    );
}

function fetch_admin_users(int $limit = 100): array
{
    $limit = max(1, min(300, $limit));

    return db_all(
        "SELECT
            id,
            COALESCE(NULLIF(display_name, ''), CONCAT(first_name, ' ', last_name), email) AS full_name,
            email,
            role,
            status
        FROM users
        ORDER BY created_at DESC, id DESC
        LIMIT {$limit}"
    );
}

function fetch_pending_approvals(int $limit = 100): array
{
    $limit = max(1, min(300, $limit));

    return db_all(
        "SELECT
            bp.id,
            bp.business_name,
            COALESCE(c.name, 'Uncategorized') AS category_name,
            bp.created_at
        FROM business_profiles bp
        LEFT JOIN categories c ON c.id = bp.category_id
        WHERE bp.approval_status = 'pending'
        ORDER BY bp.created_at DESC, bp.id DESC
        LIMIT {$limit}"
    );
}

function fetch_reports(int $limit = 100): array
{
    $limit = max(1, min(300, $limit));

    return db_all(
        "SELECT
            r.reference_code,
            r.issue_type,
            COALESCE(NULLIF(u.display_name, ''), CONCAT(u.first_name, ' ', u.last_name), u.email, 'System') AS reported_by,
            r.status,
            r.created_at
        FROM reports r
        LEFT JOIN users u ON u.id = r.reported_by_user_id
        ORDER BY r.updated_at DESC, r.id DESC
        LIMIT {$limit}"
    );
}

function fetch_categories_with_counts(): array
{
    return db_all(
        "SELECT
            c.id,
            c.name,
            c.slug,
            COUNT(bp.id) AS active_listings
        FROM categories c
        LEFT JOIN business_profiles bp
            ON bp.category_id = c.id
           AND bp.approval_status = 'approved'
        WHERE c.is_active = 1
        GROUP BY c.id, c.name, c.slug
        ORDER BY c.name ASC"
    );
}

function fetch_dashboard_metrics_admin(): array
{
    $totalUsers = db_count('SELECT COUNT(*) FROM users');
    $pendingApprovals = db_count("SELECT COUNT(*) FROM business_profiles WHERE approval_status = 'pending'");
    $openReports = db_count("SELECT COUNT(*) FROM reports WHERE status IN ('open', 'investigating')");

    $approvedProfiles = db_count("SELECT COUNT(*) FROM business_profiles WHERE approval_status = 'approved'");
    $rejectedProfiles = db_count("SELECT COUNT(*) FROM business_profiles WHERE approval_status = 'rejected'");
    $processedProfiles = $approvedProfiles + $rejectedProfiles;
    $approvalThroughput = ($processedProfiles + $pendingApprovals) > 0
        ? pct(($processedProfiles / ($processedProfiles + $pendingApprovals)) * 100)
        : 0;

    $resolvedReports = db_count("SELECT COUNT(*) FROM reports WHERE status = 'resolved'");
    $totalReports = db_count('SELECT COUNT(*) FROM reports');
    $resolvedRate = $totalReports > 0 ? pct(($resolvedReports / $totalReports) * 100) : 0;

    return [
        'total_users' => $totalUsers,
        'pending_approvals' => $pendingApprovals,
        'open_reports' => $openReports,
        'approval_throughput' => $approvalThroughput,
        'resolved_reports' => $resolvedRate,
    ];
}

function fetch_dashboard_metrics_business(?int $businessId): array
{
    if ($businessId === null) {
        return [
            'active_ads' => 0,
            'open_inquiries' => 0,
            'avg_ctr' => 0.0,
            'monthly_spend' => 0.0,
            'lead_quality' => 0,
            'ad_approval_rate' => 0,
            'response_sla' => 0,
        ];
    }

    $activeAds = db_count(
        "SELECT COUNT(*)
         FROM ads
         WHERE business_id = :business_id
           AND status = 'live'",
        ['business_id' => $businessId]
    );

    $openInquiries = db_count(
        "SELECT COUNT(*)
         FROM inquiries
         WHERE business_id = :business_id
           AND status IN ('pending', 'scheduled')",
        ['business_id' => $businessId]
    );

    $summary = fetch_business_analytics_summary($businessId);

    $currentMonthSpend = (float) (db_value(
        "SELECT COALESCE(SUM(cad.spend_amount), 0)
         FROM campaigns c
         LEFT JOIN campaign_analytics_daily cad ON cad.campaign_id = c.id
         WHERE c.business_id = :business_id
           AND DATE_FORMAT(cad.report_date, '%Y-%m') = DATE_FORMAT(CURRENT_DATE, '%Y-%m')",
        ['business_id' => $businessId]
    ) ?? 0);

    $liveAds = db_count(
        "SELECT COUNT(*)
         FROM ads
         WHERE business_id = :business_id
           AND status = 'live'",
        ['business_id' => $businessId]
    );

    $totalAds = db_count(
        "SELECT COUNT(*)
         FROM ads
         WHERE business_id = :business_id",
        ['business_id' => $businessId]
    );

    $approvalRate = $totalAds > 0
        ? pct(($liveAds / $totalAds) * 100)
        : 0;

    $repliedInquiries = db_count(
        "SELECT COUNT(*)
         FROM inquiries
         WHERE business_id = :business_id
           AND status IN ('replied', 'scheduled', 'closed')",
        ['business_id' => $businessId]
    );

    $responsePool = $openInquiries + $repliedInquiries;
    $responseSla = $responsePool > 0 ? pct(($repliedInquiries / $responsePool) * 100) : 0;

    $leadQuality = $summary['clicks'] > 0 ? pct(($summary['leads'] / $summary['clicks']) * 100) : 0;

    return [
        'active_ads' => $activeAds,
        'open_inquiries' => $openInquiries,
        'avg_ctr' => (float) $summary['avg_ctr'],
        'monthly_spend' => $currentMonthSpend,
        'lead_quality' => $leadQuality,
        'ad_approval_rate' => $approvalRate,
        'response_sla' => $responseSla,
    ];
}

function fetch_dashboard_metrics_client(?int $clientUserId): array
{
    if ($clientUserId === null) {
        return [
            'saved_businesses' => 0,
            'open_inquiries' => 0,
            'unread_messages' => 0,
            'average_response_hours' => 0.0,
            'discovery_stage' => 0,
            'ongoing_campaigns' => 0,
            'completed_negotiations' => 0,
        ];
    }

    $savedBusinesses = db_count(
        'SELECT COUNT(*) FROM favorites WHERE client_user_id = :client_user_id',
        ['client_user_id' => $clientUserId]
    );

    $openInquiries = db_count(
        "SELECT COUNT(*)
         FROM inquiries
         WHERE client_user_id = :client_user_id
           AND status IN ('pending', 'replied', 'scheduled')",
        ['client_user_id' => $clientUserId]
    );

    $unreadMessages = db_count(
        "SELECT COUNT(*)
         FROM messages
         WHERE recipient_user_id = :client_user_id
           AND message_status IN ('open', 'pending')",
        ['client_user_id' => $clientUserId]
    );

    $averageResponseHours = (float) (db_value(
        "SELECT COALESCE(AVG(TIMESTAMPDIFF(HOUR, i.created_at, reply.first_reply_at)), 0)
         FROM inquiries i
         LEFT JOIN (
             SELECT
                 inquiry_id,
                 MIN(created_at) AS first_reply_at
             FROM messages
             WHERE message_status IN ('open', 'pending', 'reviewed', 'read')
             GROUP BY inquiry_id
         ) reply ON reply.inquiry_id = i.id
         WHERE i.client_user_id = :client_user_id
           AND reply.first_reply_at IS NOT NULL",
        ['client_user_id' => $clientUserId]
    ) ?? 0.0);

    $closedInquiries = db_count(
        "SELECT COUNT(*)
         FROM inquiries
         WHERE client_user_id = :client_user_id
           AND status = 'closed'",
        ['client_user_id' => $clientUserId]
    );

    $inquiryPool = $openInquiries + $closedInquiries;

    return [
        'saved_businesses' => $savedBusinesses,
        'open_inquiries' => $openInquiries,
        'unread_messages' => $unreadMessages,
        'average_response_hours' => $averageResponseHours,
        'discovery_stage' => pct(min(100, $savedBusinesses * 10)),
        'ongoing_campaigns' => $inquiryPool > 0 ? pct(($openInquiries / $inquiryPool) * 100) : 0,
        'completed_negotiations' => $inquiryPool > 0 ? pct(($closedInquiries / $inquiryPool) * 100) : 0,
    ];
}

function render_listing_card(array $listing): string
{
    $id = (int) ($listing['id'] ?? 0);
    $name = (string) ($listing['business_name'] ?? 'Business');
    $category = strtolower((string) ($listing['category_slug'] ?? 'uncategorized'));
    $categoryLabel = (string) ($listing['category_name'] ?? 'Uncategorized');
    $city = (string) ($listing['city'] ?? 'Unspecified');
    $cityLower = strtolower($city);
    $budget = strtolower((string) ($listing['budget_tier'] ?? 'mid'));
    $description = (string) ($listing['description'] ?? 'No profile description yet.');
    $rating = (float) ($listing['rating'] ?? 0);
    $specialties = is_array($listing['specialties'] ?? null) ? $listing['specialties'] : [];

    $chips = '';
    foreach ($specialties as $specialty) {
        $chips .= '<span class="chip">' . e((string) $specialty) . '</span>';
    }

    $keywords = strtolower($name . ' ' . $categoryLabel . ' ' . $city);

    $html = '<article class="card js-search-item js-filter-item"'
        . ' data-search-item data-filter-item'
        . ' data-category="' . e($category) . '"'
        . ' data-location="' . e($cityLower) . '"'
        . ' data-budget="' . e($budget) . '"'
        . ' data-keywords="' . e($keywords) . '">';

    $html .= '<div class="card-top">';
    $html .= '<h3>' . e($name) . '</h3>';
    $html .= '<span class="badge badge-neutral">' . e(number_format($rating, 1)) . ' ★</span>';
    $html .= '</div>';
    $html .= '<p>' . e($description) . '</p>';

    if ($chips !== '') {
        $html .= '<div class="chip-row" style="margin-top:0.7rem">' . $chips . '</div>';
    }

    $html .= '<div class="inline-split" style="margin-top:0.8rem">';
    $html .= '<small>' . e($categoryLabel . ' · ' . $city) . '</small>';
    $html .= '<a class="btn-ghost" href="' . e(url('pages/business-profile.php?business_id=' . $id)) . '">View</a>';
    $html .= '</div>';
    $html .= '</article>';

    return $html;
}

function render_ad_card(array $ad, ?string $previewHref = null): string
{
    $title = (string) ($ad['title'] ?? 'Untitled ad');
    $status = strtolower((string) ($ad['status'] ?? 'planned'));
    $statusLabel = ucfirst($status);
    $channel = strtolower((string) ($ad['channel'] ?? 'social'));
    $owner = (string) ($ad['owner_name'] ?? 'Unknown owner');
    $location = (string) ($ad['location'] ?? 'Unspecified');
    $objective = (string) ($ad['objective'] ?? 'Awareness');
    $budgetAmount = (float) ($ad['budget_amount'] ?? 0);

    $badgeClass = badge_class_for_status($status);
    $keywords = strtolower($title . ' ' . $owner . ' ' . $channel);

    $html = '<article class="card js-search-item js-filter-item"'
        . ' data-search-item data-filter-item'
        . ' data-channel="' . e($channel) . '"'
        . ' data-location="' . e(strtolower($location)) . '"'
        . ' data-status="' . e($status) . '"'
        . ' data-keywords="' . e($keywords) . '">';

    $html .= '<div class="card-top">';
    $html .= '<h3>' . e($title) . '</h3>';
    $html .= '<span class="badge ' . e($badgeClass) . '">' . e($statusLabel) . '</span>';
    $html .= '</div>';
    $html .= '<p><strong>' . e($owner) . '</strong></p>';
    $html .= '<p>' . e($objective . ' · ' . $location) . '</p>';
    $html .= '<div class="inline-split" style="margin-top:0.8rem">';
    $html .= '<small>' . e(money($budgetAmount)) . '</small>';
    if ($previewHref !== null && $previewHref !== '') {
        $html .= '<a class="btn-ghost" href="' . e($previewHref) . '">Preview</a>';
    } else {
        $html .= '<button class="btn-ghost" type="button" data-notify="Campaign detail panel is coming soon.">Preview</button>';
    }
    $html .= '</div>';
    $html .= '</article>';

    return $html;
}

function render_notification_item(string $message): string
{
    return '<article class="notice-item">' . e($message) . '</article>';
}
