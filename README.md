# DXP Nucleus Advisory — Core Managers Guide

Welcome to the **Nucleus Advisory DXP Platform**. This repository contains a modular WordPress plugin built to operate as a standalone platform bypassing standard WordPress themes.

This document focuses on the **System Managers**—the core logic modules built into WP Admin that allow the Nucleus team to control products, programs, pages, and leads.

---

## ⚙️ System Managers Overview

To keep the platform flexible and data structured, the system registers several strict Custom Post Types (CPTs) and management dashboards. These are located in the `/includes/` directory and injected directly into the WordPress Admin menu.

### 1. Lead / Data Manager (Testing Dashboard)

**File:** `includes/admin-dashboard.php`
**Purpose:** Serves as the central repository for all inquiries, form submissions, and generated leads, bypassing standard form plugins.

- **Database:** Creates and utilizes the `wp_nucleus_leads_testing` custom table for fast, isolated queries.
- **Dynamic Storage:** Stores form entries as JSON objects natively. This means if you change a form field on the frontend, the database automatically accepts the new data without requiring schema changes.
- **Export Engine:** The "Export to CSV" function dynamically reads the JSON structures of all leads and automatically generates column headers for every unique field ever submitted, ensuring no data is lost during export.

### 2. Product Manager

**File:** `includes/product-manager.php`
**Purpose:** Manages digital assessments and standalone transactional products (CPT: `nucleus_product`).

- **Meta Configuration:** Provides custom fields at the bottom of the WP editor for Subtitles, Pricing schemas, and Hero Summaries.
- **Shopify Embedding:** Features a dedicated block to paste Shopify Buy Button embed codes. The system natively reads this and renders the checkout flow safely inside the `single-nucleus_product.php` template.
- **Automated Rendering:** Any product created here is automatically dynamically pulled into the `[nucleus_products_landing]` carousel and directory pages.

### 3. Program Manager

**File:** `includes/program-manager.php`
**Purpose:** Manages large-scale consulting services, internal training tracks, and corporate programs (CPT: `nucleus_program`).

- **Architecture:** Operates similarly to the Product Manager but is specifically routed to `single-nucleus_program.php`.
- **Separation of Concerns:** Keeps high-ticket consulting workflows separate from the Shopify-driven automated assessments to provide accurate traffic and conversion analytics.

### 4. Page Manager

**File:** `includes/page-manager.php`
**Purpose:** Creates standalone, highly styled landing pages without interference from the site's primary WordPress theme (CPT: `nucleus_page`).

- **Design Encapsulation:** Pages created via this manager automatically inherit the plugin's isolated CSS framework (`assets/css/nucleus-page.css`) utilizing `.nucleus-wrapper` to prevent styling bleed.
- **Marketing Specific:** Ideal for ad-driven campaign pages where loading speed, strict analytics, and distraction-free design are required.

### 5. Theme & Asset Manager (The Router)

**File:** `includes/theme-manager.php`
**Purpose:** The traffic controller of the plugin.

- **Template Hijacking:** Intercepts WordPress's standard `template_include` hook. When a user requests a Product, Program, or Nucleus Page, this supervisor steps in and forces WordPress to load the bespoke PHP templates located inside the plugin's `/templates/` directory.
- **Asset Loading:** Connects with `nucleus-dxp.php` to ensure the tracking scripts (`tracking.js`) and granular CSS files are only loaded dynamically when these specific managers' templates are requested, keeping the rest of the WordPress site lightning fast.

---

## Modifying the Managers

If the team needs to add new functionality to these managers:

1. **Adding Custom Fields (Meta Boxes)**
   If you need a new field (like "Duration" for a Program), edit `includes/program-manager.php`. Hook into `add_meta_boxes` and register the HTML form data. Then update the `save_post` routine to store it. Finally, echo it inside `templates/single-nucleus_program.php`.

2. **Connecting to the External World**
   If integrating with HubSpot, Salesforce, or Looker Studio, refer to `includes/rest-api.php`. The Lead Manager exposes secure API endpoints that external services can ping to extract the JSON-stored leads in real-time.

3. **Deploying Changes**
   Once modifications to the PHP logic are tested locally:
   - Zip the `DXP-Nucleus-Advisory` folder.
   - In WP Admin → Plugins → Add New → Upload Plugin.
   - Upload the ZIP and "Replace current with uploaded version".
