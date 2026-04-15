# AdConnect Frontend Scaffold

AdConnect is a fully responsive, backend-ready front-end web platform for advertising and client matching.

## Tech Stack

- HTML5 (rendered via PHP pages)
- CSS3
- Vanilla JavaScript
- Backend-ready structure for PHP + MySQL

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
- Breadcrumb hierarchy across pages
- Search and filtering simulation with static data
- Dynamic-looking listings and ads feed using JavaScript arrays
- Role-based UI visibility simulation (client, business, admin)
- Modal popups, tab interfaces, and toast notifications
- Client, business, admin dashboard sections with sidebar navigation
- Front-end form validation for auth, inquiry, and ad management forms

## Backend Preparation Notes

- All pages use `.php` for straightforward server-side integration
- Forms use `method="POST"`, proper `name` attributes, and `action="#"` placeholders
- `includes/config.php` includes placeholders for future MySQL/PDO setup
- Validation and sanitization placeholders are provided for secure expansion

## Run Locally (XAMPP)

1. Place the project in `htdocs`.
2. Start Apache in XAMPP.
3. Open: `http://localhost/HCI-Final-Project/adconnect/`

## Next Backend Steps

- Replace static arrays in `assets/js/main.js` with PHP + MySQL data sources
- Implement authentication and role authorization middleware
- Connect inquiry/message/review forms to database tables
- Add CSRF tokens, server-side validation, and prepared statements
