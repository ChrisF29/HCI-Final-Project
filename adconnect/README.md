# AdConnect PHP + MySQL Platform

AdConnect is a role-based advertising and client-matching web platform powered by PHP and MySQL.

## Tech Stack

- HTML5 (rendered via PHP pages)
- CSS3
- Vanilla JavaScript
- PHP server-rendered pages with shared includes
- MySQL schema in `database/schema.sql`

## Folder Structure

```text
/adconnect/
├── /assets/
│   ├── /css/
│   │   ├── main.css
│   │   ├── components.css
│   │   └── responsive.css
│   ├── /js/
│   │   ├── main.js
│   │   ├── search.js
│   │   ├── filter.js
│   │   └── dashboard.js
│   ├── /images/
│   └── /icons/
├── /includes/
│   ├── config.php
│   ├── header.php
│   ├── navbar.php
│   ├── sidebar.php
│   └── footer.php
├── /pages/
│   ├── home.php
│   ├── directory.php
│   ├── ads.php
│   ├── business-profile.php
│   ├── about.php
│   ├── help.php
│   ├── /auth/
│   ├── /user/
│   ├── /business/
│   ├── /admin/
│   └── /errors/
├── index.php
└── README.md
```

## Features Included

- Shared reusable PHP includes (header, navbar, footer, sidebar)
- Database-backed listings, ads, dashboard metrics, and tables
- Role-aware sections for client, business, and admin pages
- Search/filter UI using server-rendered record cards
- Modal popups, tab interfaces, and toast notifications
- Front-end form validation for auth, inquiry, and ad management forms

## Database Schema

- File: `database/schema.sql`
- Covers users, business profiles, categories, specialties, campaigns, ads, inquiries, messages, favorites, reviews, reports, notifications, analytics, and support requests.
- No sample seed rows are included.

## Run Locally (XAMPP)

1. Place the project in `htdocs`.
2. Start Apache in XAMPP.
3. Create/import the database schema:
	- Open phpMyAdmin
	- Import `adconnect/database/schema.sql`
4. Open: `http://localhost/HCI-Final-Project/adconnect/`

## Next Backend Steps

- Implement authentication and role authorization middleware
- Wire all form submissions (`action="#"`) to insert/update handlers
- Add CSRF tokens and stricter server-side validation rules
- Add pagination and role-scoped data policies for production usage
