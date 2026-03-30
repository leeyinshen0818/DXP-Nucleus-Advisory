# 🏗️ Page Manager Guide: Our Vision & Mission

This guide will help you build a clean, impactful **Vision & Mission** page that perfectly aligns with your brand. It uses a structured, minimalist approach to convey your core aspirations and purpose.

---

## 1️⃣ Section 1: Page Hero (`vision-hero`)

A striking entry point with a rich navy gradient.

- **Section Name:** `vision-hero`
- **Background:** `Color` -> `#0A1628`

**Add Components:**

1. **Field Type:** String (Text) | **Name:** `hero-badge`  
   **Content:** Vision & Mission
2. **Field Type:** Heading | **Name:** `hero-title` | **Meta:** `H1`  
   **Content:** Shaping the Future of Organizational Consulting with AI
3. **Field Type:** Text Area | **Name:** `hero-desc`  
   **Content:** At Nucleus Advisory, our direction is set by a commitment to excellence and a deep belief in the transformative power of people.

---

## 2️⃣ Section 2: Core Philosophy (`core-values`)

A dual-card layout highlighting the Vision and the Purpose.

- **Section Name:** `core-values`
- **Background:** `Color` -> `#F8FAFC`

**Add Components:**

1. **Field Type:** Card | **Name:** `card-vision`
   - **Title:** Our Vision
   - **Content:** Our aspiration is to stand as the pre-eminent people advisory provider in the Asia-Pacific (APAC) region. With unwavering dedication, we aim to set new standards in the realm of people and organization capabilities.
2. **Field Type:** Card | **Name:** `card-purpose`
   - **Title:** Our Purpose
   - **Content:** At Nucleus Advisory, we are driven by a noble purpose – to empower individuals, talents, and leaders to reach new heights in their roles and within their communities. By doing so, we catalyze future growth in capability, performance, and organizational sustainability.

---

## 3️⃣ Section 3: The People First Banner (`mission-footer`)

A final, focused statement about your core belief.

- **Section Name:** `mission-footer`
- **Background:** `Color` -> `#FFFFFF`

**Add Components:**

1. **Field Type:** Heading | **Name:** `footer-statement` | **Meta:** `H3`  
   **Content:** We believe that the true value of an organization lies in its people.
2. **Field Type:** Text Area | **Name:** `footer-subtext`  
   **Content:** We exist to help our clients unlock this immense potential.

---

## 🎨 CSS Styling for Vision & Mission

Copy and paste the respective snippets into the CSS Manager for each section.
_(Note: The bottom padding of the hero section has been significantly increased to give more breathing room between the hero text and the overlapping cards)._

### 1. Hero (`vision-hero`)

```css
/* =========================================
   VISION & MISSION - HERO STYLES
========================================= */
#nucleus-section-vision-hero {
  background: linear-gradient(160deg, #0a1628 0%, #1a2d4a 100%);
  text-align: center;
  padding: 120px 20px 160px; /* Adjusted padding properly */
  position: relative;
}

#vision-hero-hero-badge {
  display: inline-block;
  background: rgba(147, 197, 253, 0.1);
  color: #93c5fd;
  padding: 8px 18px;
  border-radius: 50px;
  font-size: 0.85rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 1.5px;
  border: 1px solid rgba(147, 197, 253, 0.2);
  margin-bottom: 24px;
}

#vision-hero-hero-title {
  color: #ffffff;
  font-size: clamp(2.2rem, 5vw, 3.5rem);
  font-weight: 800;
  max-width: 900px;
  margin: 0 auto 24px;
  line-height: 1.2;
}

#vision-hero-hero-desc {
  color: #94a3b8;
  font-size: 1.2rem;
  max-width: 650px;
  margin: 0 auto;
  line-height: 1.7;
}
```

### 2. Core Philosophy (`core-values`)

```css
/* =========================================
   VISION & MISSION - CORE VALUES STYLES
========================================= */
#nucleus-section-core-values {
  padding: 0 20px 100px;
  background-color: #f8fafc;
}

/* Constrain the group of cards */
#core-values-card {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 30px;
  max-width: 1100px;
  margin: -80px auto 0; /* Push cards up over the hero section instead of the entire section */
  position: relative;
  z-index: 10;
}

#core-values-card-vision,
#core-values-card-purpose {
  background: #ffffff;
  padding: 50px;
  border-radius: 16px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.05);
  border: 1px solid #e8ecf1;
  transition: all 0.3s ease;
}

#core-values-card-vision:hover,
#core-values-card-purpose:hover {
  transform: translateY(-5px);
  box-shadow: 0 20px 50px rgba(37, 99, 235, 0.1);
  border-color: #2563eb;
}

/* Card Titles */
#core-values-card-vision .nucleus-card-title,
#core-values-card-purpose .nucleus-card-title {
  color: #334155;
  font-size: 1.8rem;
  font-weight: 800;
  margin-bottom: 20px;
  position: relative;
}

/* Style the dash under 'Vision' / 'Mission' title */
#core-values-card-vision .nucleus-card-title::after,
#core-values-card-purpose .nucleus-card-title::after {
  content: "";
  display: block;
  width: 40px;
  height: 4px;
  background: #2563eb;
  margin-top: 10px;
  border-radius: 2px;
}

/* Card Description/Content */
#core-values-card-vision .nucleus-card-content,
#core-values-card-purpose .nucleus-card-content {
  color: #64748b;
  font-size: 1.1rem;
  line-height: 1.8;
}

/* Mobile Responsiveness for Core Values */
@media (max-width: 800px) {
  #core-values-card {
    grid-template-columns: 1fr;
    margin-top: -50px;
  }

  #nucleus-section-core-values {
    padding-top: 0;
  }

  #core-values-card-vision,
  #core-values-card-purpose {
    padding: 30px;
  }
}
```

### 3. The People First Banner (`mission-footer`)

```css
/* =========================================
   VISION & MISSION - FOOTER STATEMENT STYLES
========================================= */
#nucleus-section-mission-footer {
  padding: 100px 20px;
  text-align: center;
  background: #ffffff;
}

#mission-footer-footer-statement {
  color: #334155;
  font-size: 2rem;
  font-weight: 800;
  margin-bottom: 20px;
}

#mission-footer-footer-subtext {
  color: #2563eb;
  font-size: 1.3rem;
  font-weight: 600;
  letter-spacing: -0.01em;
}
```
