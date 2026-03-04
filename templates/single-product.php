<!--
    Single Product Template — Minimalist Design
    Variables: $title, $subtitle, $price, $hero_summary, $assessment_types, $shopify_button, $thumbnail_url, $content
-->

<?php
// Dynamically inject selected offering Assessment types
$assessment_types = get_post_meta($product_id, '_nucleus_product_assessment_types', true);
$catalog = nucleus_get_assessment_catalog();

if (!is_array($assessment_types)) {
    $assessment_types = array();
}

$dynamic_items = '';
if (!empty($assessment_types)) {
    foreach ($assessment_types as $type) {
        if (isset($catalog[$type])) {
            $label = esc_html($catalog[$type]['label']);
            $icon = esc_attr($catalog[$type]['icon']);
            $icon_url = NUCLEUS_DXP_URL . 'assets/icons/' . $icon;
            $dynamic_items .= '<li class="n-dynamic-assessment-item">';
            $dynamic_items .= '<img src="' . esc_url($icon_url) . '" class="n-assessment-icon" alt="' . esc_attr($label) . ' icon">';
            $dynamic_items .= '<span>' . $label . '</span>';
            $dynamic_items .= '</li>';
        }
    }
}

// Check for new structured fields
$sec1_items = get_post_meta($product_id, '_nucleus_product_section_1_items', true);
$sec2_title = get_post_meta($product_id, '_nucleus_product_section_2_title', true);
$sec2_items = get_post_meta($product_id, '_nucleus_product_section_2_items', true);
$sec3_title = get_post_meta($product_id, '_nucleus_product_section_3_title', true);
$sec3_items = get_post_meta($product_id, '_nucleus_product_section_3_items', true);

$is_structured = !empty($sec1_items) && is_array($sec1_items);

// If using new custom fields
if ($is_structured) {
    $structured_content = '<h3>What You Will Receive</h3>';
    $structured_content .= '<ul class="n-list-receive n-structured-list">'; // Added custom class just in case to stop JS manip
    $structured_content .= $dynamic_items;

    foreach ($sec1_items as $item) {
        $structured_content .= '<li>' . esc_html($item) . '</li>';
    }
    $structured_content .= '</ul>';

    // Section 2 and 3 Split Layout
    if (!empty($sec2_title) || !empty($sec3_title)) {
        $structured_content .= '<div class="n-details-split-layout n-structured-split-layout">';

        // Left Col (Sec 2)
        $structured_content .= '<div class="n-details-col n-details-col-left">';
        if (!empty($sec2_title)) {
            $structured_content .= '<h3>' . esc_html($sec2_title) . '</h3>';
        }
        if (!empty($sec2_items) && is_array($sec2_items)) {
            $structured_content .= '<ul class="n-list-framework">';
            foreach ($sec2_items as $item) {
                $structured_content .= '<li><strong>' . esc_html($item['title']) . '</strong> ' . nl2br(esc_html($item['desc'])) . '</li>';
            }
            $structured_content .= '</ul>';
        }
        $structured_content .= '</div>';

        // Right Col (Sec 3)
        $structured_content .= '<div class="n-details-col n-details-col-right">';
        if (!empty($sec3_title)) {
            $structured_content .= '<h3>' . esc_html($sec3_title) . '</h3>';
        }
        if (!empty($sec3_items) && is_array($sec3_items)) {
            $structured_content .= '<ul class="n-list-impact">';
            foreach ($sec3_items as $item) {
                $structured_content .= '<li><strong>' . esc_html($item['title']) . '</strong> ' . nl2br(esc_html($item['desc'])) . '</li>';
            }
            $structured_content .= '</ul>';
        }
        $structured_content .= '</div>';

        $structured_content .= '</div>';
    }

    // Override the post shortcode content entirely
    $content = $structured_content;

} else {
    // Fallback: Legacy WordPress Content Editor approach
    if (!empty($dynamic_items)) {
        // Inject into first matched UL
        $content = preg_replace(
            '/(<ul[^>]*>)/',
            '$1' . $dynamic_items,
            $content,
            1
        );
    }
}
?>

<div class="nucleus-single-product-wrapper">

    <!-- Hero Section -->
    <div class="n-product-hero">
        <div class="np-hero-particles">
            <span class="np-particle np-p1"></span>
            <span class="np-particle np-p2"></span>
            <span class="np-particle np-p3"></span>
            <span class="np-particle np-p4"></span>
            <span class="np-particle np-p5"></span>
            <span class="np-particle np-p6"></span>
        </div>
        <div class="n-product-hero-inner">

            <!-- Left: Product Image -->
            <div class="n-product-image-col">
                <?php if ($thumbnail_url): ?>
                    <img src="<?php echo esc_url($thumbnail_url); ?>" alt="<?php echo esc_attr($title); ?>"
                        class="n-product-image">
                <?php else: ?>
                    <div class="n-product-image-placeholder">No Image</div>
                <?php endif; ?>
            </div>

            <!-- Right: Product Info -->
            <div class="n-product-info-col">
                <span class="n-product-badge">Premium Assessment</span>
                <h1 class="n-product-title"><?php echo esc_html($title); ?></h1>
                <?php if ($subtitle): ?>
                    <p class="n-product-subtitle"><?php echo esc_html($subtitle); ?></p>
                <?php endif; ?>

                <?php if ($price): ?>
                    <div class="n-product-price"><?php echo esc_html($price); ?></div>
                <?php endif; ?>

                <?php if ($hero_summary): ?>
                    <div class="n-product-summary">
                        <?php echo nl2br(esc_html($hero_summary)); ?>
                    </div>
                <?php endif; ?>

                <?php if ($shopify_button): ?>
                    <div class="n-product-terms">
                        <label class="n-terms-checkbox">
                            <input type="checkbox" id="nucleus-terms-checkbox">
                            <span>I agree to the
                                <a href="/wp-content/uploads/2026/02/Nucleus_Advisory_Privacy_Policy.pdf"
                                    target="_blank">Privacy Policy</a>,
                                <a href="/wp-content/uploads/2026/02/Nucleus_Advisory_Delivery_Policy.pdf"
                                    target="_blank">Delivery Policy</a>
                                and
                                <a href="/wp-content/uploads/2026/02/Nucleus_Advisory_Refund_Policy.pdf"
                                    target="_blank">Refund Policy</a>.
                            </span>
                        </label>

                    </div>
                    <div class="n-product-buy-button" id="nucleus-buy-button-wrapper">
                        <?php echo $shopify_button; ?>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>

    <!-- What's Included Section -->
    <?php if ($content && trim(strip_tags($content))): ?>
        <div class="n-product-details-section">
            <div class="n-product-details-inner">
                <h2 class="n-details-title">What's Included</h2>
                <div class="n-details-content" <?php if ($is_structured)
                    echo ' data-structured="true"'; ?>>
                    <?php echo $content; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // --- Terms checkbox ---
            const checkbox = document.getElementById("nucleus-terms-checkbox");
            const buttonWrapper = document.getElementById("nucleus-buy-button-wrapper");

            if (checkbox && buttonWrapper) {
                buttonWrapper.style.opacity = "0.5";
                buttonWrapper.style.pointerEvents = "none";
                checkbox.addEventListener("change", function () {
                    if (this.checked) {
                        buttonWrapper.style.opacity = "1";
                        buttonWrapper.style.pointerEvents = "auto";
                    } else {
                        buttonWrapper.style.opacity = "0.5";
                        buttonWrapper.style.pointerEvents = "none";
                    }
                });
            }

            // --- Auto-Stunning Split Layout Builder ---
            // Only run JS DOM manipulation for OLD legacy content (not structured)
            const detailsContainer = document.querySelector('.n-details-content');
            if (detailsContainer && !detailsContainer.hasAttribute('data-structured')) {
                const targetH3s = detailsContainer.querySelectorAll('h3');

                // Only wrap if we have at least 3 sections
                if (targetH3s.length >= 3) {
                    const h3_2 = targetH3s[1];
                    const h3_3 = targetH3s[2];

                    const splitWrapper = document.createElement('div');
                    splitWrapper.className = 'n-details-split-layout';

                    const colLeft = document.createElement('div');
                    colLeft.className = 'n-details-col n-details-col-left';

                    const colRight = document.createElement('div');
                    colRight.className = 'n-details-col n-details-col-right';

                    splitWrapper.appendChild(colLeft);
                    splitWrapper.appendChild(colRight);

                    h3_2.parentNode.insertBefore(splitWrapper, h3_2);

                    // Move everything from 2nd H3 to 3rd H3 into the left column
                    let curr = h3_2;
                    while (curr && curr !== h3_3) {
                        let next = curr.nextSibling;
                        colLeft.appendChild(curr);
                        curr = next;
                    }

                    // Move everything from 3rd H3 onwards into the right column
                    curr = h3_3;
                    while (curr) {
                        let next = curr.nextSibling;
                        colRight.appendChild(curr);
                        curr = next;
                    }
                }

                // --- Assign robust CSS classes to lists ---
                const colLeftUl = detailsContainer.querySelector('.n-details-col-left ul');
                if (colLeftUl) colLeftUl.classList.add('n-list-framework');

                const colRightUl = detailsContainer.querySelector('.n-details-col-right ul');
                if (colRightUl) colRightUl.classList.add('n-list-impact');

                // The first UL that isn't inside our new split columns gets the "receive" class
                const firstUl = detailsContainer.querySelector('ul:not(.n-list-framework):not(.n-list-impact)');
                if (firstUl) firstUl.classList.add('n-list-receive');
            }

            // --- Scroll-reveal for lists ---
            var lists = document.querySelectorAll('.n-details-content ul');
            if (lists.length) {
                var observer = new IntersectionObserver(function (entries) {
                    entries.forEach(function (entry) {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('is-visible');
                            observer.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.15 });
                lists.forEach(function (el) { observer.observe(el); });
            }
        });
    </script>

</div>