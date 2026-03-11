<!--
    Products Landing Page — Spotlight Carousel + Details
    Variables: $atts (title, subtitle), $products (array of WP_Post)
-->

<div class="nl-landing-wrapper">

    <!-- Hero -->
    <div class="nl-hero">
        <div class="nl-hero-particles">
            <span class="nl-particle nl-p1"></span>
            <span class="nl-particle nl-p2"></span>
            <span class="nl-particle nl-p3"></span>
            <span class="nl-particle nl-p4"></span>
            <span class="nl-particle nl-p5"></span>
            <span class="nl-particle nl-p6"></span>
        </div>
        <div class="nl-hero-inner">
            <span class="nl-hero-badge">Nucleus Advisory</span>
            <h1 class="nl-hero-title"><?php echo esc_html($atts['title']); ?></h1>
            <p class="nl-hero-subtitle"><?php echo esc_html($atts['subtitle']); ?></p>
            <div class="nl-hero-divider"></div>
        </div>
    </div>

    <!-- Spotlight Carousel -->
    <?php if (!empty($products)): ?>
        <div class="nl-spotlight-section">
            <div class="nl-spotlight-inner">

                <div class="nl-carousel" id="nl-carousel">
                    <?php foreach ($products as $index => $product):
                        $p_title = get_the_title($product->ID);
                        $p_subtitle = get_post_meta($product->ID, '_nucleus_product_subtitle', true);
                        $p_price = get_post_meta($product->ID, '_nucleus_product_price', true);
                        $p_summary = get_post_meta($product->ID, '_nucleus_product_hero_summary', true);
                        $p_thumb = get_the_post_thumbnail_url($product->ID, 'large');
                        $p_link = get_permalink($product->ID);
                        ?>
                        <div class="nl-slide <?php echo $index === 0 ? 'nl-slide-active' : ''; ?>"
                            data-index="<?php echo $index; ?>">
                            <div class="nl-slide-image-col">
                                <?php if ($p_thumb): ?>
                                    <img src="<?php echo esc_url($p_thumb); ?>" alt="<?php echo esc_attr($p_title); ?>"
                                        class="nl-slide-image">
                                <?php else: ?>
                                    <div class="nl-slide-image-placeholder">📦</div>
                                <?php endif; ?>
                            </div>
                            <div class="nl-slide-info-col">
                                <span class="nl-slide-counter"><?php echo str_pad($index + 1, 2, '0', STR_PAD_LEFT); ?> /
                                    <?php echo str_pad(count($products), 2, '0', STR_PAD_LEFT); ?></span>
                                <h2 class="nl-slide-title"><?php echo esc_html($p_title); ?></h2>
                                <?php if ($p_subtitle): ?>
                                    <p class="nl-slide-subtitle"><?php echo esc_html($p_subtitle); ?></p>
                                <?php endif; ?>
                                <?php if ($p_summary): ?>
                                    <p class="nl-slide-desc"><?php echo esc_html($p_summary); ?></p>
                                <?php endif; ?>
                                <div class="nl-slide-footer">
                                    <?php if ($p_price): ?>
                                        <span class="nl-slide-price"><?php echo esc_html($p_price); ?></span>
                                    <?php endif; ?>
                                    <a href="<?php echo esc_url($p_link); ?>" class="nl-slide-btn"
                                        data-product="<?php echo esc_attr($p_title); ?>"
                                        data-position="<?php echo $index + 1; ?>">View Assessment →</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Controls -->
                <div class="nl-carousel-controls">
                    <div class="nl-dots" id="nl-dots">
                        <?php foreach ($products as $index => $product): ?>
                            <button class="nl-dot <?php echo $index === 0 ? 'nl-dot-active' : ''; ?>"
                                data-index="<?php echo $index; ?>" aria-label="Go to slide <?php echo $index + 1; ?>"></button>
                        <?php endforeach; ?>
                    </div>
                    <div class="nl-arrows">
                        <button class="nl-arrow nl-arrow-prev" id="nl-prev" aria-label="Previous">←</button>
                        <button class="nl-arrow nl-arrow-next" id="nl-next" aria-label="Next">→</button>
                    </div>
                </div>

                <div class="nl-progress-track">
                    <div class="nl-progress-bar" id="nl-progress"></div>
                </div>

            </div>
        </div>
    <?php endif; ?>

    <!-- How It Works -->
    <div class="nl-how-section">
        <div class="nl-how-inner">
            <h2 class="nl-section-title">How It Works</h2>
            <p class="nl-section-subtitle">Three simple steps to unlock your potential</p>
            <div class="nl-steps">
                <div class="nl-step">
                    <div class="nl-step-number">01</div>
                    <h3 class="nl-step-title">Choose Your Assessment</h3>
                    <p class="nl-step-desc">Browse our assessments and select the one that matches your career goals and
                        development needs.</p>
                </div>
                <div class="nl-step">
                    <div class="nl-step-number">02</div>
                    <h3 class="nl-step-title">Complete the Assessment</h3>
                    <p class="nl-step-desc">Take the psychometric assessments online at your own pace. Your responses
                        are confidential and secure.</p>
                </div>
                <div class="nl-step">
                    <div class="nl-step-number">03</div>
                    <h3 class="nl-step-title">Get Your Results</h3>
                    <p class="nl-step-desc">Receive a compiled report or a professional consultation with a Nucleus Advisory expert to discuss your insights.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Core Values / Why Choose Us -->
    <div class="nl-includes-section">
        <div class="nl-includes-inner">
            <h2 class="nl-section-title">Why Choose Nucleus Advisory</h2>
            <p class="nl-section-subtitle">We don't just give you a report—we give you a strategic roadmap.</p>
            <div class="nl-includes-grid">
                <div class="nl-include-item">
                    <span class="nl-include-icon">📊</span>
                    <span class="nl-include-text">Data-Backed Insights</span>
                </div>
                <div class="nl-include-item">
                    <span class="nl-include-icon">🎯</span>
                    <span class="nl-include-text">Highly Actionable Roadmaps</span>
                </div>
                <div class="nl-include-item">
                    <span class="nl-include-icon">💬</span>
                    <span class="nl-include-text">1-on-1 Expert Consultations</span>
                </div>
                <div class="nl-include-item">
                    <span class="nl-include-icon">🚀</span>
                    <span class="nl-include-text">Accelerated Career Growth</span>
                </div>
                <div class="nl-include-item">
                    <span class="nl-include-icon">👁️</span>
                    <span class="nl-include-text">Identify Blind Spots</span>
                </div>
                <div class="nl-include-item">
                    <span class="nl-include-icon">🔐</span>
                    <span class="nl-include-text">100% Confidential & Secure</span>
                </div>
            </div>
        </div>
    </div>

    <!-- CTA -->
    <div class="nl-cta-section">
        <div class="nl-cta-inner">
            <h2 class="nl-cta-title">Ready to discover your potential?</h2>
            <p class="nl-cta-text">Choose an assessment above and take the first step toward transforming your career.
            </p>
        </div>
    </div>

    <!-- Disclaimers -->
    <div class="nl-disclaimers-section">
        <div class="nl-disclaimers-inner">
            <h2 class="nl-disclaimers-title">Disclaimers</h2>

            <div class="nl-disclaimer">
                <h4>Educational & Development Purpose Only</h4>
                <p>All assessments are designed for educational and development purposes. They are intended to offer
                    meaningful insights for personal growth, professional development, and career planning. This
                    assessment does not serve as professional counselling, career advice, or psychological evaluation
                    for clinical purposes.</p>
            </div>

            <div class="nl-disclaimer">
                <h4>Personal Data Collection & Confidentiality</h4>
                <p>By purchasing and completing this assessment, you consent to the collection of personal data required
                    during the assessment and consultation process. Your data will be treated with the highest level of
                    confidentiality and will not be shared with third parties without your consent, except where
                    required for the delivery of the assessment services.</p>
            </div>

            <div class="nl-disclaimer">
                <h4>Turnaround Time & Confirmation</h4>
                <p>Upon successful payment, you will receive a confirmation email within 24 hours. Please check your
                    inbox (including spam/junk folders). If the confirmation is not received within 72 hours, please
                    contact us. Processing times for assessment results may vary depending on volume; typical turnaround
                    is 5–7 business days after completion of all assessments.</p>
            </div>

            <div class="nl-disclaimer">
                <h4>Refund Policy for Digital Goods</h4>
                <p>Due to the nature of digital goods and services, all purchases are final. Refunds will not be
                    provided once the product has been delivered or assessment links have been sent. If you encounter
                    any issues, please contact us and we will do our best to resolve them.</p>
            </div>

            <div class="nl-disclaimer">
                <h4>Agreement to Disclaimers & Policies</h4>
                <p>By completing the payment process, you confirm that you fully understand and accept the terms
                    outlined above, including the educational nature of the assessments, data collection practices,
                    turnaround timelines, and refund policy. For further details, please refer to our Privacy Policy and
                    Terms of Service.</p>
            </div>
        </div>
    </div>

</div>

<!-- Carousel Script -->
<script>
    (function () {
        var currentSlide = 0;
        var slides = document.querySelectorAll('.nl-slide');
        var dots = document.querySelectorAll('.nl-dot');
        var progress = document.getElementById('nl-progress');
        var totalSlides = slides.length;
        var interval = 6000;
        var timer;

        if (totalSlides === 0) return;

        function goToSlide(index) {
            slides[currentSlide].classList.remove('nl-slide-active');
            dots[currentSlide].classList.remove('nl-dot-active');
            currentSlide = (index + totalSlides) % totalSlides;
            slides[currentSlide].classList.add('nl-slide-active');
            dots[currentSlide].classList.add('nl-dot-active');
            if (progress) {
                progress.style.transition = 'none';
                progress.style.width = '0%';
                setTimeout(function () {
                    progress.style.transition = 'width ' + interval + 'ms linear';
                    progress.style.width = '100%';
                }, 50);
            }
        }

        function nextSlide() { goToSlide(currentSlide + 1); }
        function prevSlide() { goToSlide(currentSlide - 1); }

        function startAutoPlay() {
            stopAutoPlay();
            if (progress) {
                progress.style.transition = 'width ' + interval + 'ms linear';
                progress.style.width = '100%';
            }
            timer = setInterval(nextSlide, interval);
        }

        function stopAutoPlay() {
            clearInterval(timer);
            if (progress) {
                progress.style.transition = 'none';
                progress.style.width = '0%';
            }
        }

        dots.forEach(function (dot) {
            dot.addEventListener('click', function () {
                goToSlide(parseInt(this.getAttribute('data-index')));
                startAutoPlay();
            });
        });

        var prevBtn = document.getElementById('nl-prev');
        var nextBtn = document.getElementById('nl-next');
        if (prevBtn) prevBtn.addEventListener('click', function () { prevSlide(); startAutoPlay(); });
        if (nextBtn) nextBtn.addEventListener('click', function () { nextSlide(); startAutoPlay(); });

        startAutoPlay();
    })();
</script>