# Vision & Mission Page Building Guide

This guide provides the exact configuration and CSS needed to build your new "Vision & Mission" page using a **clean, stacked layout** (no overlapping, no side-by-side cards). This provides a very beautiful, foolproof reading experience.

## 1. Page Content Setup (Content Builder Tab)

Recreate the following structure in the **Content Builder**. We will use three separate sections for maximum flexibility.

### Section 1: `hero`
- **Section Name:** `hero`
- **Background:** Select a dark blue color (e.g., `#0a192f`)
- **Components:**
  1.  **Component Type:** `Text`
      - **Component Name:** `main-title`
      - **Content:** `Our Vision & Mission`
  2.  **Component Type:** `Text`
      - **Component Name:** `subtitle`
      - **Content:** `Shaping the Future of Organizational Consulting with AI`

### Section 2: `vision`
- **Section Name:** `vision`
- **Background:** White (`#ffffff`)
- **Components:**
  1.  **Component Type:** `Text`
      - **Component Name:** `title`
      - **Content:** `Vision`
  2.  **Component Type:** `Text`
      - **Component Name:** `content`
      - **Content:** `Our aspiration is to stand as the pre-eminent people advisory provider in the Asia-Pacific (APAC) region. With unwavering dedication, we aim to set new standards in the realm of people and organization capabilities.`

### Section 3: `mission`
- **Section Name:** `mission`
- **Background:** Light Grey (`#f8f9fa`)
- **Components:**
  1.  **Component Type:** `Text`
      - **Component Name:** `title`
      - **Content:** `Mission`
  2.  **Component Type:** `Text`
      - **Component Name:** `content`
      - **Content:** `At Nucleus Advisory, we are driven by a noble purpose – to empower individuals, talents, and leaders to reach new heights in their roles and within their communities. By doing so, we catalyze future growth in capability, performance, and organizational sustainability. Central to our mission is the fundamental belief that the true value of an organization lies in its people. We exist to help our clients unlock this immense potential.`

---

## 2. Page Styling (CSS Manager Tab)

Switch to the **CSS Manager** tab, completely clear your old CSS rules for this page, and paste this fresh layout.

```css
/* =========================================
   1. Hero Section
   ========================================= */
#nucleus-section-hero {
  background: linear-gradient(135deg, #0f2027 0%, #203a43 50%, #2c5364 100%);
  padding: 100px 20px;
  text-align: center;
  color: #ffffff;
}

#hero-main-title {
  font-size: 3rem;
  font-weight: 700;
  margin-bottom: 20px;
  letter-spacing: -0.5px;
}

#hero-subtitle {
  font-size: 1.25rem;
  font-weight: 400;
  max-width: 800px;
  margin: 0 auto;
  opacity: 0.9;
  line-height: 1.6;
}

/* =========================================
   2. Vision & Mission Sections (Stacked)
   ========================================= */
#nucleus-section-vision,
#nucleus-section-mission {
  padding: 100px 20px;
}

/* Container for text layout */
#nucleus-section-vision .nucleus-container,
#nucleus-section-mission .nucleus-container {
  max-width: 900px; /* Constrains the width making it very readable */
  margin: 0 auto;
  text-align: center;
}

/* Section Titles */
#vision-title,
#mission-title {
  font-size: 2.5rem;
  font-weight: 700;
  color: #1d2327;
  margin-bottom: 30px;
  position: relative;
  display: inline-block;
  padding-bottom: 15px;
}

/* Underline accent */
#vision-title::after,
#mission-title::after {
  content: "";
  position: absolute;
  bottom: 0;
  left: 50%;
  transform: translateX(-50%);
  width: 60px;
  height: 4px;
  background: #2271b1;
  border-radius: 4px;
}

/* Content Text */
#vision-content,
#mission-content {
  font-size: 1.25rem;
  line-height: 1.8;
  color: #50575e;
  font-weight: 400;
}

/* =========================================
   3. Mobile Responsiveness
   ========================================= */
@media (max-width: 900px) {
  #nucleus-section-hero,
  #nucleus-section-vision,
  #nucleus-section-mission {
    padding: 60px 20px;
  }

  #hero-main-title {
    font-size: 2.25rem;
  }
  
  #vision-title,
  #mission-title {
    font-size: 2rem;
  }

  #vision-content,
  #mission-content {
    font-size: 1.1rem;
    line-height: 1.6;
  }
}
```
