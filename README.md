# ��� DXP Nucleus Advisory — Project Handover Document

Welcome to the **Nucleus Advisory DXP Platform** framework. This repository contains the source code for a highly customized, modular WordPress plugin that acts as a standalone platform within the WordPress ecosystem. It manages custom content, an isolated testing environment, lead generation, and dynamic theming specifically designed for Nucleus Advisory.

This document serves as the official handover guide for the Nucleus Advisory technical and marketing teams, detailing what the system is, how it's built, and how to manage it going forward.

---

## ���️ System Overview

Instead of relying on a standard WordPress theme, this plugin acts as a **Custom Experience Builder**. It brings its own templates, database tables, analytics tracking, and Custom Post Types (CPTs) to safely construct landing pages and capture leads without interfering with the rest of the WordPress site.

### Key Capabilities
1. **Lead Capture & Data Management:** A standalone backend for capturing custom form submissions, storing them in a dedicated custom table (`wp_nucleus_leads_testing`), and providing a CSV export tool in the WP Admin.
2. **Modular Content Architecture:** Registers tailored Custom Post Types (CPTs) like `nucleus_product`, `nucleus_program`, and `nucleus_page` to cleanly separate consulting services, digital assessments, and bespoke landing pages.
3. **Template Engine:** Bypasses active WordPress themes to load bespoke templates directly from the plugin (`/templates/`), ensuring absolute fidelity for design and tracking.
4. **Targeted Analytics:** Injects precise Google Tag Manager (GTM) and GA4 event tracking logic specific to Nucleus conversion funnels.
5. **Shopify Integration:** Native spots (meta fields) to embed Shopify Buy Buttons directly into the individual products, connecting lead generation with e-commerce.

---

## ��� Technical Architecture & File Structure

The project is structured logically into distinct modules to keep the codebase clean and maintainable.

```text
nucleus-dxp/
├── nucleus-dxp.php                  ← The Core Plugin File: Initializes everything, manages DB tables, and enqueues assets.
├── assets/
│   ├── css/                         ← Highly modular, component-based CSS architecture.
│   │   ├── nucleus-page.css
│   │   ├── products-landing.css
│   │   └── backup/...               ← Granular, section-specific stylesheets (hero, cta, header, etc.).
│   ├── icons/                       ← SVG and image assets.
│   └── js/
│       └── tracking.js              ← Core behavioral tracking (clicks, scrolls, custom GA4 events).
├── docs/                            ← Integration documentation (Looker Studio, Sheets, etc.).
├── includes/                        ← The logic engine.
│   ├── admin-dashboard.php          ← Builds the WP Admin "Testing Dashboard" and handles CSV lead exports.
│   ├── analytics.php                ← Injects tracking codes securely.
│   ├── form-handler.php             ← Processes incoming leads securely, bypassing standard slow WP hooks.
│   ├── rest-api.php                 ← Custom REST routes for headless or frontend-to-backend communication.
│   ├── page-manager.php             ← Registers 'nucleus_page' CPT and its meta boxes.
│   ├── product-manager.php          ← Registers 'nucleus_product' CPT.
│   ├── program-manager.php          ← Registers 'nucleus_program' CPT.
│   └── theme-manager.php            ← The routing engine binding CPTs to the custom `/templates/`.
└── templates/                       ← PHP files dictating the exact HTML structure for the frontend.
    ├── products-landing.php
    ├── programs-landing.php
    ├── single-nucleus_page.php
    ├── single-nucleus_product.php
    ├── single-nucleus_program.php
    └── testing-page.php
```

---

## ���️ How to Manage the System

### 1. Managing Leads (The Dashboard)
When a user submits a form on one of the landing pages, it does *not* go to standard WP comments or a standard form plugin. 
* **Viewing Leads:** Go to **WP Admin → Testing Dashboard**. Here you will see a chronological list of all captured leads.
* **Exporting:** Click **Export to CSV**. The system dynamically scans the JSON payloads of all leads and automatically builds a CSV with all dynamic fields columns mapped accurately.

### 2. Managing Products, Programs, and Pages
In the WP Admin sidebar, you will see new menus for **Product Manager**, **Program Manager**, and **Pages (Nucleus)**.
* **Adding Content:** Create a new entry just like a normal blog post. 
* **Custom Meta:** Depending on the type (e.g., Product), you will see custom metaboxes at the bottom of the editor. This is where you configure:
  * Subtitles & Pricing
  * Hero Summaries
  * **Shopify Buy Button Snippets:** Paste the embed code from Shopify directly here. The template (`single-nucleus_product.php`) will render it in the right place automatically.

### 3. Modifying the Design (CSS)
This plugin uses a **modular CSS approach**. 
If you need to change a specific section (e.g., the Hero section on the solutions page):
1. Navigate to `assets/css/backup/solutions/hero.css` (or the respective compiled main CSS file).
2. The CSS avoids global resets to prevent conflicts with the parent WordPress theme. All DXP designs are scoped under specific class wrappers (like `.nucleus-wrapper`).

### 4. Updating the Plugin on the Live Site
Since this acts as a WordPress plugin:
1. Make your local code changes.
2. Zip the `nucleus-dxp` folder.
3. In WP Admin, go to **Plugins → Add New → Upload Plugin**.
4. Upload the zip and click **Replace current with uploaded version**. (Ensure you have a backup of the DB just in case, though this plugin only manages its own isolated tables safely).

---

## ⚙️ Developer Notes & Database

* **Database Table:** `wp_nucleus_leads_testing`. This table is created automatically upon plugin activation (`nucleus_core_activate_table()` in `nucleus-dxp.php`). It stores data securely using JSON for the `form_data` column to support infinite, flexible field layouts without needing column migrations.
* **Templates Routing:** `includes/theme-manager.php` intercepts WordPress's default `template_include` hook. If the user visits a `nucleus_product` or `nucleus_program`, it forces WordPress to render the PHP file located in the plugin's `templates/` folder, ignoring the active theme.
* **REST API:** Check `includes/rest-api.php` if you intend to integrate with external dashboards or CRMs (like HubSpot, Salesforce, or connecting to the documented Google Sheets/Looker Studio integrations found in `/docs/`).

## ��� Next Steps for the Team
- **Review `/docs/`**: Check out `google-sheets-looker-integration.md` to see how the leads currently pipeline into analytics dashboards.
- **Review Assets**: Familiarize yourself with `tracking.js` so you know exactly what user actions trigger GA4 events. 
