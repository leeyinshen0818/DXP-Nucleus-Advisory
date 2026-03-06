<!--
    Single Product Template — Minimalist Design
    Variables: $title, $subtitle, $price, $hero_summary, $assessment_types, $shopify_button, $thumbnail_url, $content
-->

<?php
// Load assessment catalog
$catalog = nucleus_get_assessment_catalog();

// Check for new structured fields
$sec1_items = get_post_meta($product_id, '_nucleus_product_section_1_items', true);
$sec2_title = get_post_meta($product_id, '_nucleus_product_section_2_title', true);
$sec2_items = get_post_meta($product_id, '_nucleus_product_section_2_items', true);
$sec3_title = get_post_meta($product_id, '_nucleus_product_section_3_title', true);
$sec3_items = get_post_meta($product_id, '_nucleus_product_section_3_items', true);

$is_structured = !empty($sec1_items) && is_array($sec1_items);
$popup_data = array();

// If using new custom fields
if ($is_structured) {
    $structured_content = '<h3>What You Will Receive</h3>';
    $structured_content .= '<ul class="n-list-receive n-structured-list">';

    foreach ($sec1_items as $idx => $item) {
        if (is_array($item) && isset($item['type'])) {
            if ($item['type'] === 'assessment' && !empty($item['key']) && isset($catalog[$item['key']])) {
                $a = $catalog[$item['key']];
                $label = esc_html($a['label']);
                // Item-level icon takes priority, then catalog icon
                $icon_url = !empty($item['icon_url']) ? $item['icon_url'] : (!empty($a['icon_url']) ? $a['icon_url'] : '');
                $desc = !empty($item['desc']) ? $item['desc'] : '';
                $popup_id = 'popup-' . esc_attr($item['key']);
                $clickable = !empty($desc) ? ' n-assessment-clickable" data-popup="' . $popup_id . '"' : '"';

                $structured_content .= '<li class="n-dynamic-assessment-item' . $clickable . '>';
                if (!empty($icon_url)) {
                    $structured_content .= '<img src="' . esc_url($icon_url) . '" class="n-assessment-icon" alt="' . esc_attr($label) . ' icon">';
                }
                $structured_content .= '<span>' . $label . '</span>';
                $structured_content .= '</li>';

                if (!empty($desc)) {
                    $popup_data[] = array(
                        'id' => $popup_id,
                        'label' => $label,
                        'icon_url' => esc_url($icon_url),
                        'desc' => $desc,
                    );
                }
            } elseif ($item['type'] === 'custom' && !empty($item['text'])) {
                $desc = !empty($item['desc']) ? $item['desc'] : '';
                $custom_icon = !empty($item['icon_url']) ? $item['icon_url'] : '';
                $has_icon = !empty($custom_icon);
                $li_class = $has_icon ? 'n-dynamic-assessment-item' : '';
                if (!empty($desc)) {
                    $popup_id = 'popup-custom-' . $idx;
                    $li_class .= ($li_class ? ' ' : '') . 'n-assessment-clickable';
                    $structured_content .= '<li class="' . $li_class . '" data-popup="' . $popup_id . '">';
                    if ($has_icon) {
                        $structured_content .= '<img src="' . esc_url($custom_icon) . '" class="n-assessment-icon" alt="">';
                    }
                    $structured_content .= '<span>' . esc_html($item['text']) . '</span></li>';
                    $popup_data[] = array(
                        'id' => $popup_id,
                        'label' => esc_html($item['text']),
                        'icon_url' => $has_icon ? esc_url($custom_icon) : '',
                        'desc' => $desc,
                    );
                } else {
                    if ($has_icon) {
                        $structured_content .= '<li class="n-dynamic-assessment-item">';
                        $structured_content .= '<img src="' . esc_url($custom_icon) . '" class="n-assessment-icon" alt="">';
                        $structured_content .= '<span>' . esc_html($item['text']) . '</span></li>';
                    } else {
                        $structured_content .= '<li>' . esc_html($item['text']) . '</li>';
                    }
                }
            }
        } else {
            // Legacy: plain string item
            if (is_string($item) && !empty($item)) {
                $structured_content .= '<li>' . esc_html($item) . '</li>';
            }
        }
    }
    $structured_content .= '</ul>';

    // Section 2 and 3 Split Layout
    if (!empty($sec2_title) || !empty($sec3_title)) {
        $structured_content .= '<div class="n-details-split-layout n-structured-split-layout">';

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

    $content = $structured_content;

} else {
    // Fallback: Legacy approach
    $assessment_types = get_post_meta($product_id, '_nucleus_product_assessment_types', true);
    if (!is_array($assessment_types))
        $assessment_types = array();
    $dynamic_items = '';
    if (!empty($assessment_types)) {
        foreach ($assessment_types as $type) {
            if (isset($catalog[$type])) {
                $label = esc_html($catalog[$type]['label']);
                $icon_url = !empty($catalog[$type]['icon_url']) ? $catalog[$type]['icon_url'] : '';
                $dynamic_items .= '<li class="n-dynamic-assessment-item">';
                if (!empty($icon_url)) {
                    $dynamic_items .= '<img src="' . esc_url($icon_url) . '" class="n-assessment-icon" alt="' . esc_attr($label) . ' icon">';
                }
                $dynamic_items .= '<span>' . $label . '</span>';
                $dynamic_items .= '</li>';
            }
        }
    }
    if (!empty($dynamic_items)) {
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

            // --- Assessment Popup Click Handlers ---
            var scrollbarWidth = window.innerWidth - document.documentElement.clientWidth;
            document.querySelectorAll('.n-assessment-clickable').forEach(function (el) {
                el.style.cursor = 'pointer';
                el.addEventListener('click', function () {
                    var popupId = this.getAttribute('data-popup');
                    var modal = document.getElementById(popupId);
                    if (modal) {
                        modal.style.display = 'flex';
                        // Do not change body overflow to avoid shifting fixed headers/cart
                    }
                });
            });

            // Close modals
            function closeAllModals() {
                document.querySelectorAll('.n-modal-overlay').forEach(function (overlay) {
                    overlay.style.display = 'none';
                });
            }
            document.querySelectorAll('.n-modal-overlay').forEach(function (overlay) {
                overlay.addEventListener('click', function (e) {
                    if (e.target === overlay || e.target.closest('.n-modal-close')) {
                        closeAllModals();
                    }
                });
            });
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') closeAllModals();
            });
        });
    </script>

    <?php if (!empty($popup_data)): ?>
        <!-- Assessment Popup Modals -->
        <style>
            .n-modal-overlay {
                display: none;
                position: fixed;
                inset: 0;
                background: transparent;
                z-index: 99999;
                align-items: center;
                justify-content: center;
                padding: 24px;
            }

            .n-modal-box {
                background: #fff;
                border-radius: 14px;
                max-width: 480px;
                width: 100%;
                position: relative;
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.12), 0 0 0 1px rgba(0, 0, 0, 0.05);
                animation: nModalIn 0.25s cubic-bezier(0.16, 1, 0.3, 1);
                overflow: hidden;
            }

            @keyframes nModalIn {
                from {
                    opacity: 0;
                    transform: translateY(12px) scale(0.98);
                }

                to {
                    opacity: 1;
                    transform: translateY(0) scale(1);
                }
            }

            .n-modal-accent {
                height: 4px;
                background: linear-gradient(90deg, #2563eb, #7c3aed);
            }

            .n-modal-inner {
                padding: 28px 32px 32px;
            }

            .n-modal-close {
                position: absolute;
                top: 16px;
                right: 16px;
                background: transparent;
                border: 1px solid #e5e7eb;
                font-size: 16px;
                cursor: pointer;
                color: #9ca3af;
                width: 32px;
                height: 32px;
                border-radius: 8px;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: all 0.15s ease;
                z-index: 1;
                line-height: 1;
            }

            .n-modal-close:hover {
                background: #f9fafb;
                border-color: #d1d5db;
                color: #374151;
            }

            .n-modal-header {
                display: flex;
                align-items: center;
                gap: 14px;
                padding-bottom: 18px;
                border-bottom: 1px solid #f1f5f9;
                margin-bottom: 18px;
            }

            .n-modal-icon {
                width: 44px;
                height: 44px;
                padding: 8px;
                background: #f8fafc;
                border: 1px solid #e2e8f0;
                border-radius: 10px;
                flex-shrink: 0;
            }

            .n-modal-title {
                font-size: 1.1rem;
                font-weight: 600;
                color: #1e293b;
                margin: 0;
                letter-spacing: -0.01em;
            }

            .n-modal-desc {
                font-size: 0.9rem;
                line-height: 1.7;
                color: #64748b;
                margin: 0;
            }

            .n-assessment-clickable {
                cursor: pointer;
                transition: border-color 0.2s, box-shadow 0.2s;
            }

            .n-assessment-clickable:hover {
                border-color: #2563eb !important;
                box-shadow: 0 4px 12px rgba(37, 99, 235, 0.12) !important;
            }

            @media (max-width: 540px) {
                .n-modal-box {
                    margin: 16px;
                }

                .n-modal-inner {
                    padding: 22px 20px 26px;
                }
            }
        </style>
        <?php foreach ($popup_data as $popup): ?>
            <div class="n-modal-overlay" id="<?php echo esc_attr($popup['id']); ?>">
                <div class="n-modal-box">
                    <div class="n-modal-accent"></div>
                    <div class="n-modal-inner">
                        <button class="n-modal-close" aria-label="Close">✕</button>
                        <div class="n-modal-header">
                            <?php if (!empty($popup['icon_url'])): ?>
                                <img src="<?php echo $popup['icon_url']; ?>" class="n-modal-icon" alt="">
                            <?php endif; ?>
                            <h3 class="n-modal-title"><?php echo $popup['label']; ?></h3>
                        </div>
                        <div class="n-modal-desc">
                            <?php echo nl2br(esc_html($popup['desc'])); ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

</div>