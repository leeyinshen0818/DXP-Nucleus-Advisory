<!--
    Single Product Template — 3-Tier Package Design
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
$packages   = get_post_meta($product_id, '_nucleus_packages', true);

// Fallback logic for packages if missing
if (!is_array($packages) || empty($packages['basic'])) {
    $packages = array(
        'basic' => array('price' => $price, 'shopify' => $shopify_button, 'sec1_items' => is_array($sec1_items) ? $sec1_items : array()),
        'plus'  => array('price' => '', 'shopify' => '', 'sec1_items' => array()),
        'max'   => array('price' => '', 'shopify' => '', 'sec1_items' => array())
    );
}

$popup_data = array();

// --- BUILD PACKAGE SLIDES ---
$pkg_keys = array('basic', 'plus', 'max');
$pkg_default_names = array('basic' => 'Basic Package', 'plus' => 'Plus Package', 'max' => 'Max Package');
$pkg_default_tabs  = array('basic' => 'A Package', 'plus' => 'B Package', 'max' => 'C Package');
$slides_html = '';
$tab_labels = array(); // For dynamic tab names

foreach ($pkg_keys as $k => $pkg) {
    $pkg_data = $packages[$pkg];
    $pkg_items = isset($pkg_data['sec1_items']) && is_array($pkg_data['sec1_items']) ? $pkg_data['sec1_items'] : array();
    
    // Use editable names, fallback to defaults
    $pkg_name = !empty($pkg_data['name']) ? $pkg_data['name'] : $pkg_default_names[$pkg];
    $tab_labels[$pkg] = !empty($pkg_data['tab_name']) ? $pkg_data['tab_name'] : $pkg_default_tabs[$pkg];
    
    $slides_html .= '<div class="n-package-slide" data-index="' . $k . '">';
    
    // Slide header (title + price only, no subtitle)
    $slides_html .= '<div class="n-slide-header">';
    $slides_html .= '<h3 class="n-slide-title">' . esc_html($pkg_name) . '</h3>';
    if (!empty($pkg_data['price'])) {
        $slides_html .= '<div class="n-slide-price">' . esc_html($pkg_data['price']) . '</div>';
    }
    $slides_html .= '</div>';
    
    // Assessment grid
    $slides_html .= '<div class="n-slide-receive-label">What You Will Receive</div>';
    $slides_html .= '<ul class="n-list-receive n-structured-list">';
    
    $curr_pkg_item_ids = array();

    foreach ($pkg_items as $idx => $item) {
        $item_id = '';
        $is_new = false;
        
        // Determine unique string ID for this item for cross-package comparison
        if (is_array($item) && isset($item['type'])) {
            if ($item['type'] === 'assessment' && !empty($item['key'])) {
                $item_id = 'assessment_' . $item['key'];
            } elseif ($item['type'] === 'custom' && !empty($item['text'])) {
                $item_id = 'custom_' . md5(strtolower(trim($item['text'])));
            }
        } elseif (is_string($item) && !empty($item)) {
            $item_id = 'custom_' . md5(strtolower(trim($item)));
        }

        if (!empty($item_id)) {
            $curr_pkg_item_ids[] = $item_id;
            // It's "new" if this isn't the first tab AND it wasn't in the previous tab
            if ($k > 0 && !in_array($item_id, $prev_pkg_item_ids)) {
                $is_new = true;
            }
        }
        
        $highlight_class = $is_new ? ' n-item-highlight' : '';
        $highlight_badge = $is_new ? '<span class="n-item-badge">+ Added</span>' : '';

        if (is_array($item) && isset($item['type'])) {
            if ($item['type'] === 'assessment' && !empty($item['key']) && isset($catalog[$item['key']])) {
                $a = $catalog[$item['key']];
                $label = esc_html($a['label']);
                $icon_url = !empty($item['icon_url']) ? $item['icon_url'] : (!empty($a['icon_url']) ? $a['icon_url'] : '');
                $desc = !empty($item['desc']) ? $item['desc'] : '';
                $popup_id = 'popup-' . $pkg . '-' . esc_attr($item['key']);
                $clickable = !empty($desc) ? ' n-assessment-clickable" data-popup="' . $popup_id . '"' : '"';

                $slides_html .= '<li class="n-dynamic-assessment-item' . $highlight_class . $clickable . '>';
                if (!empty($icon_url)) {
                    $slides_html .= '<img src="' . esc_url($icon_url) . '" class="n-assessment-icon" alt="' . esc_attr($label) . ' icon">';
                }
                $slides_html .= '<div class="n-item-text-wrap"><span>' . $label . '</span>' . $highlight_badge . '</div>';
                $slides_html .= '</li>';

                if (!empty($desc)) {
                    $popup_data[] = array(
                        'id' => $popup_id,
                        'label' => $label,
                        'icon_url' => !empty($icon_url) ? esc_url($icon_url) : '',
                        'desc' => $desc,
                    );
                }
            } elseif ($item['type'] === 'custom' && !empty($item['text'])) {
                $desc = !empty($item['desc']) ? $item['desc'] : '';
                $custom_icon = !empty($item['icon_url']) ? $item['icon_url'] : '';
                $has_icon = !empty($custom_icon);
                $li_class = $has_icon ? 'n-dynamic-assessment-item' : '';
                $li_class .= $highlight_class;

                if (!empty($desc)) {
                    $popup_id = 'popup-custom-' . $pkg . '-' . $idx;
                    $li_class .= (trim($li_class) ? ' ' : '') . 'n-assessment-clickable';
                    $slides_html .= '<li class="' . trim($li_class) . '" data-popup="' . $popup_id . '">';
                    if ($has_icon) {
                        $slides_html .= '<img src="' . esc_url($custom_icon) . '" class="n-assessment-icon" alt="">';
                    }
                    $slides_html .= '<div class="n-item-text-wrap"><span>' . esc_html($item['text']) . '</span>' . $highlight_badge . '</div></li>';
                    $popup_data[] = array(
                        'id' => $popup_id,
                        'label' => esc_html($item['text']),
                        'icon_url' => $has_icon ? esc_url($custom_icon) : '',
                        'desc' => $desc,
                    );
                } else {
                    $slides_html .= '<li class="' . trim($li_class) . '">';
                    if ($has_icon) {
                        $slides_html .= '<img src="' . esc_url($custom_icon) . '" class="n-assessment-icon" alt="">';
                    }
                    $slides_html .= '<div class="n-item-text-wrap"><span>' . esc_html($item['text']) . '</span>' . $highlight_badge . '</div></li>';
                }
            }
        } else {
            if (is_string($item) && !empty($item)) {
                $slides_html .= '<li class="' . trim($highlight_class) . '"><div class="n-item-text-wrap"><span>' . esc_html($item) . '</span>' . $highlight_badge . '</div></li>';
            }
        }
    }
    $slides_html .= '</ul>';
    
    // Save current package identifier list for the next iteration step
    $prev_pkg_item_ids = $curr_pkg_item_ids;
    
    // Checkout area
    $slides_html .= '<div class="n-package-checkout-area">';
    $slides_html .= '<label class="n-terms-checkbox">';
    $slides_html .= '<input type="checkbox" class="n-pkg-terms-checkbox" data-pkg="' . $pkg . '">';
    $slides_html .= '<span>I agree to the <a href="/wp-content/uploads/2026/02/Nucleus_Advisory_Privacy_Policy.pdf" target="_blank">Privacy Policy</a>, <a href="/wp-content/uploads/2026/02/Nucleus_Advisory_Delivery_Policy.pdf" target="_blank">Delivery Policy</a> and <a href="/wp-content/uploads/2026/03/Nucleus_Advisory_Refund_Policy.pdf" target="_blank">Refund Policy</a>.</span>';
    $slides_html .= '</label>';
    $slides_html .= '<button class="n-custom-add-to-cart" id="btn-custom-add-' . $pkg . '" onclick="triggerShopifyCheckout(\'' . $pkg . '\')" disabled>';
    $slides_html .= 'Add to Cart';
    $slides_html .= '</button>';
    $slides_html .= '</div>';

    $slides_html .= '</div>'; // End slide
}

// --- BUILD DESCRIPTION SECTION ---
$description_html = '';
if (!empty($sec2_title) || !empty($sec3_title)) {
    $description_html .= '<div class="n-details-split-layout n-structured-split-layout">';

    $description_html .= '<div class="n-details-col n-details-col-left">';
    if (!empty($sec2_title)) {
        $description_html .= '<h3>' . esc_html($sec2_title) . '</h3>';
    }
    if (!empty($sec2_items) && is_array($sec2_items)) {
        $description_html .= '<ul class="n-list-framework">';
        foreach ($sec2_items as $item) {
            $description_html .= '<li><strong>' . esc_html($item['title']) . '</strong> ' . nl2br(esc_html($item['desc'])) . '</li>';
        }
        $description_html .= '</ul>';
    }
    $description_html .= '</div>';

    $description_html .= '<div class="n-details-col n-details-col-right">';
    if (!empty($sec3_title)) {
        $description_html .= '<h3>' . esc_html($sec3_title) . '</h3>';
    }
    if (!empty($sec3_items) && is_array($sec3_items)) {
        $description_html .= '<ul class="n-list-impact">';
        foreach ($sec3_items as $item) {
            $description_html .= '<li><strong>' . esc_html($item['title']) . '</strong> ' . nl2br(esc_html($item['desc'])) . '</li>';
        }
        $description_html .= '</ul>';
    }
    $description_html .= '</div>';

    $description_html .= '</div>';
}

// Deduplicate popup data
$unique_popups = array();
foreach ($popup_data as $pd) {
    $unique_popups[$pd['id']] = $pd;
}

// --- PREVIOUS & NEXT PRODUCT LOGIC ---
$all_products = get_posts(array(
    'post_type'      => 'nucleus_product',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
    'orderby'        => 'menu_order',
    'order'          => 'ASC',
));

$prev_product = null;
$next_product = null;

foreach ($all_products as $index => $p) {
    if ($p->ID == $product_id) {
        if ($index > 0) {
            $prev_product = $all_products[$index - 1];
        }
        if ($index < count($all_products) - 1) {
            $next_product = $all_products[$index + 1];
        }
        break;
    }
}
?>

<!-- INVISIBLE SHOPIFY WRAPPERS (hidden, triggered programmatically) -->
<div style="display:none !important; opacity:0; position:absolute; z-index:-999; pointer-events:none;">
    <div id="shopify-wrap-basic"><?php echo (isset($packages['basic']['shopify']) ? $packages['basic']['shopify'] : ''); ?></div>
    <div id="shopify-wrap-plus"><?php echo (isset($packages['plus']['shopify']) ? $packages['plus']['shopify'] : ''); ?></div>
    <div id="shopify-wrap-max"><?php echo (isset($packages['max']['shopify']) ? $packages['max']['shopify'] : ''); ?></div>
</div>

<div class="nucleus-single-product-wrapper">

    <!-- ==================== HERO SECTION ==================== -->
    <div class="n-product-hero">
        
        <?php if ($prev_product): ?>
            <a href="<?php echo esc_url(get_permalink($prev_product->ID)); ?>" class="n-hero-nav-arrow n-hero-nav-prev" title="Previous: <?php echo esc_attr($prev_product->post_title); ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            </a>
        <?php endif; ?>
        
        <?php if ($next_product): ?>
            <a href="<?php echo esc_url(get_permalink($next_product->ID)); ?>" class="n-hero-nav-arrow n-hero-nav-next" title="Next: <?php echo esc_attr($next_product->post_title); ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </a>
        <?php endif; ?>

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

                <?php 
                $basic_price = isset($packages['basic']['price']) ? $packages['basic']['price'] : $price;
                if ($basic_price): 
                ?>
                    <div class="n-product-price-hero">
                        <span class="n-price-label">Starts from</span>
                        <span class="n-price-value"><?php echo esc_html($basic_price); ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($hero_summary): ?>
                    <div class="n-product-summary">
                        <?php echo nl2br(esc_html($hero_summary)); ?>
                    </div>
                <?php endif; ?>
                
                <div class="n-hero-actions">
                    <a href="#n-package-slider-section" class="n-btn-view-packages" id="btn-view-packages">
                        <span>View all packages</span>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                    </a>
                </div>
            </div>

        </div>
    </div>

    <!-- ==================== PACKAGE SLIDER SECTION ==================== -->
    <div id="n-package-slider-section" class="n-package-slider-section">
        <h2 class="n-pkg-section-title">Choose Your Package</h2>

        <div class="n-slider-tabs">
            <div class="n-slider-tab active" data-pkg="basic" id="tab-basic"><?php echo esc_html($tab_labels['basic']); ?></div>
            <div class="n-slider-tab" data-pkg="plus" id="tab-plus"><?php echo esc_html($tab_labels['plus']); ?></div>
            <div class="n-slider-tab" data-pkg="max" id="tab-max"><?php echo esc_html($tab_labels['max']); ?></div>
        </div>
        
        <div class="n-package-slider-body">
            <button class="n-slider-arrow n-slider-prev" id="btn-slider-prev" aria-label="Previous package">&larr;</button>
            <div class="n-package-slider-container">
                <div class="n-package-slider-track" id="n-slider-track">
                    <?php echo $slides_html; ?>
                </div>
            </div>
            <button class="n-slider-arrow n-slider-next" id="btn-slider-next" aria-label="Next package">&rarr;</button>
        </div>
    </div>

    <!-- ==================== PRODUCT DESCRIPTION SECTION ==================== -->
    <?php if (!empty($description_html)): ?>
    <div class="n-product-details-section">
        <div class="n-product-details-inner">
            <div class="n-details-content" data-structured="true">
                <?php echo $description_html; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- ==================== JAVASCRIPT ==================== -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            
            // --- Package Slider ---
            var currentPackageIndex = 0;
            var track = document.getElementById('n-slider-track');
            var tabs = document.querySelectorAll('.n-slider-tab');
            var prevBtn = document.getElementById('btn-slider-prev');
            var nextBtn = document.getElementById('btn-slider-next');
            
            function goToPackage(index) {
                if (!track) return;
                currentPackageIndex = index;
                var offset = index * -33.33333;
                track.style.transform = 'translateX(' + offset + '%)';
                
                tabs.forEach(function(t) { t.classList.remove('active'); });
                if (tabs[index]) tabs[index].classList.add('active');

                // Update arrow visibility
                if (prevBtn) prevBtn.style.opacity = index === 0 ? '0.3' : '1';
                if (nextBtn) nextBtn.style.opacity = index === 2 ? '0.3' : '1';
            }
            
            // Expose globally for onclick
            window.goToPackage = goToPackage;
            window.nextPackage = function() { if (currentPackageIndex < 2) goToPackage(currentPackageIndex + 1); };
            window.prevPackage = function() { if (currentPackageIndex > 0) goToPackage(currentPackageIndex - 1); };

            // Tab click
            tabs.forEach(function(tab, i) {
                tab.addEventListener('click', function() { goToPackage(i); });
            });
            if (prevBtn) prevBtn.addEventListener('click', function() { window.prevPackage(); });
            if (nextBtn) nextBtn.addEventListener('click', function() { window.nextPackage(); });

            // Initial arrow state
            if (prevBtn) prevBtn.style.opacity = '0.3';

            // --- Smooth Scroll ---
            var viewPkgsBtn = document.getElementById('btn-view-packages');
            if (viewPkgsBtn) {
                viewPkgsBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    var target = document.getElementById('n-package-slider-section');
                    if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                });
            }

            // --- Checkbox per Package ---
            var checkboxes = document.querySelectorAll('.n-pkg-terms-checkbox');
            checkboxes.forEach(function(cb) {
                cb.addEventListener('change', function() {
                    var pkg = this.getAttribute('data-pkg');
                    var btn = document.getElementById('btn-custom-add-' + pkg);
                    if (btn) {
                        btn.disabled = !this.checked;
                        if (this.checked) {
                            btn.classList.add('enabled');
                        } else {
                            btn.classList.remove('enabled');
                        }
                    }
                });
            });

            // --- Shopify Checkout Trigger ---
            window.triggerShopifyCheckout = function(pkgName) {
                var wrapper = document.getElementById('shopify-wrap-' + pkgName);
                if (!wrapper) { alert("No store link configured for this package."); return; }
                
                var iframe = wrapper.querySelector('iframe');
                if (iframe) {
                    try {
                        var innerDoc = iframe.contentDocument || iframe.contentWindow.document;
                        var btn = innerDoc.querySelector('.shopify-buy__btn');
                        if (btn) {
                            btn.click();
                            openShopifyCart();
                        } else {
                            alert("Checkout is loading. Please try again in a moment.");
                        }
                    } catch(e) {
                        // Fallback: try finding Shopify button inside the wrapper div directly
                        var directBtn = wrapper.querySelector('.shopify-buy__btn');
                        if (directBtn) {
                            directBtn.click();
                            openShopifyCart();
                        } else {
                            alert("Checkout is loading. Please try again in a moment.");
                        }
                    }
                } else {
                    // Might not be using iframe, try direct button
                    var directBtn = wrapper.querySelector('.shopify-buy__btn');
                    if (directBtn) {
                        directBtn.click();
                        openShopifyCart();
                    } else {
                        alert("Checkout is loading. Please try again in a moment.");
                    }
                }
            };
            
            function openShopifyCart() {
                // The Shopify Cart Toggle is drawn in a totally separate iframe injected into the body.
                // We need to hunt it down across all iframes on the page to open it.
                function tryClickCart() {
                    // Check main document just in case
                    var mainToggle = document.querySelector('.shopify-buy__cart-toggle');
                    if (mainToggle) { mainToggle.click(); return true; }
                    
                    // Check inside all iframes
                    var frames = document.querySelectorAll('iframe');
                    var clicked = false;
                    for (var i = 0; i < frames.length; i++) {
                        try {
                            var fDoc = frames[i].contentDocument || frames[i].contentWindow.document;
                            var t = fDoc.querySelector('.shopify-buy__cart-toggle');
                            if (t) {
                                t.click();
                                clicked = true;
                                break;
                            }
                        } catch(err) { /* Cross-origin block for external frames, ignore */ }
                    }
                    return clicked;
                }

                // Try shortly after
                setTimeout(function() {
                    var opened = tryClickCart();
                    // Fallback try a little later if it was still loading
                    if (!opened) {
                        setTimeout(tryClickCart, 800);
                    }
                }, 400);
            }

            // --- Scroll-reveal for lists ---
            var lists = document.querySelectorAll('.n-list-receive, .n-list-framework, .n-list-impact');
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

            // --- Assessment Popup Handlers ---
            document.querySelectorAll('.n-assessment-clickable').forEach(function (el) {
                el.style.cursor = 'pointer';
                el.addEventListener('click', function () {
                    var popupId = this.getAttribute('data-popup');
                    var modal = document.getElementById(popupId);
                    if (modal) {
                        modal.style.display = 'flex';
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

    <!-- ==================== POPUP MODALS ==================== -->
    <?php if (!empty($unique_popups)): ?>
        <style>
            .n-modal-overlay {
                display: none;
                position: fixed;
                inset: 0;
                background: rgba(255, 255, 255, 0.65);
                backdrop-filter: blur(12px);
                -webkit-backdrop-filter: blur(12px);
                z-index: 99999;
                align-items: center;
                justify-content: center;
                padding: 24px;
            }

            .n-modal-box {
                background: #ffffff;
                border-radius: 20px;
                max-width: 460px;
                width: 100%;
                position: relative;
                box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.15), 0 0 0 1px rgba(15, 23, 42, 0.05);
                animation: nModalIn 0.35s cubic-bezier(0.16, 1, 0.3, 1);
                overflow: hidden;
            }

            @keyframes nModalIn {
                from { opacity: 0; transform: translateY(16px) scale(0.96); }
                to { opacity: 1; transform: translateY(0) scale(1); }
            }

            .n-modal-inner { padding: 36px 36px 40px; }

            .n-modal-close {
                position: absolute;
                top: 20px;
                right: 20px;
                background: transparent;
                border: none;
                cursor: pointer;
                color: #94a3b8;
                width: 36px;
                height: 36px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: all 0.2s ease;
                z-index: 10;
            }

            .n-modal-close svg {
                width: 20px;
                height: 20px;
            }

            .n-modal-close:hover {
                background: #f1f5f9;
                color: #0f172a;
            }

            .n-modal-content-wrap {
                display: flex;
                flex-direction: column;
                align-items: flex-start;
                gap: 20px;
            }

            .n-modal-icon {
                width: 56px;
                height: 56px;
                padding: 12px;
                background: #f8fafc;
                border: 1px solid #e2e8f0;
                border-radius: 14px;
                flex-shrink: 0;
            }

            .n-modal-text {
                display: flex;
                flex-direction: column;
                gap: 12px;
            }

            .n-modal-title {
                font-size: 1.25rem;
                font-weight: 700;
                color: #0f172a;
                margin: 0;
                letter-spacing: -0.02em;
                line-height: 1.3;
            }

            .n-modal-desc {
                font-size: 0.95rem;
                line-height: 1.6;
                color: #475569;
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
                .n-modal-box { margin: 16px; border-radius: 20px; }
                .n-modal-inner { padding: 32px 24px 36px; }
            }
        </style>
        <?php foreach ($unique_popups as $popup): ?>
            <div class="n-modal-overlay" id="<?php echo esc_attr($popup['id']); ?>">
                <div class="n-modal-box">
                    <div class="n-modal-inner">
                        <button class="n-modal-close" aria-label="Close">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                        <div class="n-modal-content-wrap">
                            <?php if (!empty($popup['icon_url'])): ?>
                                <img src="<?php echo $popup['icon_url']; ?>" class="n-modal-icon" alt="">
                            <?php endif; ?>
                            <div class="n-modal-text">
                                <h3 class="n-modal-title"><?php echo $popup['label']; ?></h3>
                                <div class="n-modal-desc">
                                    <?php echo nl2br(esc_html($popup['desc'])); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

</div>
