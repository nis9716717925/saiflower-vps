    <?php 
    require_once __DIR__ . '/../../includes/url_helper.php';
    global $conn;
    $dynamicSecIndex = 0; 

    foreach (homepage_ordered_cms_sections($allSections, $homepageCmsSectionOrder ?? []) as $sec):
        if (in_array($sec['type'], ['carousel', 'circle_carousel', 'flower_picker', 'calendar'], true)) {
            continue;
        }

        $secId = $sec['id'];
        $items = $allItemsGrouped[$secId] ?? [];
        
        if(count($items) === 0) continue;

        $dynamicSecIndex++;
    ?>
    
    <div class="container mx-auto px-4">
        <?php if(!in_array($sec['type'], ['banner', 'image_slider'])): ?>
        <div class="flex flex-col items-center text-center mt-4 mb-2 md:mt-6 md:mb-4">
            <div class="w-full">
                <h2 class="hp-section-title text-2xl sm:text-3xl font-bold text-gray-900"><?= htmlspecialchars($sec['title']) ?></h2>
                <?php if(!empty($sec['subtitle'])): ?>
                <p class="text-gray-500 mt-2 text-sm md:text-base"><?= htmlspecialchars($sec['subtitle']) ?></p>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if(false && $sec['type'] == 'carousel'): ?>
        <div class="hp-occasion-carousel-wrap hp-product-carousel-wrap">
            <button type="button" class="hp-occasion-nav hp-occasion-nav--prev" aria-label="Previous products">
                <i class="fas fa-chevron-left" aria-hidden="true"></i>
            </button>
            <div class="hp-occasion-track-wrap">
                <div class="hp-occasion-track hide-scrollbar" role="list">
                    <?= homepage_render_cms_carousel_cards($items) ?>
                </div>
            </div>
            <button type="button" class="hp-occasion-nav hp-occasion-nav--next" aria-label="Next products">
                <i class="fas fa-chevron-right" aria-hidden="true"></i>
            </button>
        </div>

        <?php elseif($sec['type'] == 'grid'): ?>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-2">
            <?php foreach($items as $i): 
                 $img = get_image_url($i['image']);
            ?>
            <a href="<?= htmlspecialchars(resolve_cms_item_link(($conn instanceof mysqli) ? $conn : null, $i)) ?>" class="group block relative rounded-xl overflow-hidden aspect-square border border-gray-200">
                <img src="<?= htmlspecialchars($img) ?>" loading="lazy" width="300" height="300" class="w-full h-full object-cover group-hover:scale-110 transition duration-500" alt="<?= htmlspecialchars($i['title']) ?>">
                <div class="absolute inset-x-0 bottom-0 p-3 bg-gradient-to-t from-black/70 to-transparent">
                    <h3 class="text-white font-medium text-sm text-center truncate"><?= htmlspecialchars($i['title']) ?></h3>
                </div>
            </a>
            <?php endforeach; ?>
        </div>

        <?php elseif($sec['type'] == 'banner'): ?>
            <?php foreach($items as $i): 
                 $desktopImg = get_image_url($i['image']);
                 $mobileImg = !empty($i['mobile_image']) ? get_image_url($i['mobile_image']) : $desktopImg;
            ?>
            <a href="<?= htmlspecialchars(resolve_cms_item_link(($conn instanceof mysqli) ? $conn : null, $i)) ?>" class="block rounded-2xl overflow-hidden shadow-md bg-gray-50 flex flex-col items-center justify-center">
                <picture class="w-full flex items-center justify-center">
                    <source media="(max-width: 640px)" srcset="<?= htmlspecialchars($mobileImg) ?>">
                    <img src="<?= htmlspecialchars($desktopImg) ?>" loading="lazy" width="1200" height="500" class="w-full h-auto max-h-[300px] sm:max-h-[400px] md:max-h-[500px] object-contain" alt="<?= htmlspecialchars($i['title'] ?? 'Banner') ?>">
                </picture>
                <?php if(!empty($i['title'])): ?>
                <div class="w-full py-3 px-4 text-center bg-white border-t border-gray-100">
                    <h3 class="text-gray-800 font-bold text-base md:text-lg"><?= htmlspecialchars($i['title']) ?></h3>
                </div>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>

        <?php elseif($sec['type'] == 'image_slider'): ?>
        
        <div class="relative w-full rounded-2xl overflow-hidden shadow-md bg-white group/slider" id="dynSlider_<?= $secId ?>">
            <div class="aspect-[3/2] relative w-full overflow-hidden">
                <div class="flex h-full w-full transition-transform duration-500 ease-in-out" id="dynTrack_<?= $secId ?>" style="width: <?= count($items) * 100 ?>%">
                    <?php if(count($items) > 0): foreach($items as $index => $i): 
                         $desktopImg = get_image_url($i['image']);
                         $mobileImg = !empty($i['mobile_image']) ? get_image_url($i['mobile_image']) : $desktopImg;
                         $priority = ($index === 0) ? 'fetchpriority="high" loading="eager"' : 'loading="lazy"';
                    ?>
                    <div class="dyn-slide h-full relative" style="width: <?= 100 / count($items) ?>%">
                        <a href="<?= htmlspecialchars(resolve_cms_item_link(($conn instanceof mysqli) ? $conn : null, $i)) ?>" class="block w-full h-full relative group/img overflow-hidden">
                            <picture class="w-full h-full block">
                                <source media="(max-width: 640px)" srcset="<?= htmlspecialchars($mobileImg) ?>">
                                <img src="<?= htmlspecialchars($desktopImg) ?>" 
                                     width="900" height="600"
                                     class="w-full h-full object-cover object-center bg-gray-100 block" 
                                     alt="<?= htmlspecialchars($i['title'] ?? 'Slide') ?>"
                                     decoding="async"
                                     <?= $priority ?>>
                            </picture>
                        </a>
                    </div>
                    <?php endforeach; else: ?>
                    <div class="w-full h-full flex items-center justify-center text-gray-400 bg-gray-50">
                        <span>No Slides Configured</span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <button onclick="moveDynSlide(<?= $secId ?>, -1)" class="hidden md:block absolute left-4 top-1/2 -translate-y-1/2 bg-white/80 hover:bg-white text-gray-800 p-3 rounded-full shadow-lg transition opacity-80 hover:opacity-100 z-10"><i class="fas fa-chevron-left"></i></button>
            <button onclick="moveDynSlide(<?= $secId ?>, 1)" class="hidden md:block absolute right-4 top-1/2 -translate-y-1/2 bg-white/80 hover:bg-white text-gray-800 p-3 rounded-full shadow-lg transition opacity-80 hover:opacity-100 z-10"><i class="fas fa-chevron-right"></i></button>

            <?php if(count($items) > 1): ?>
            <div class="absolute bottom-0 left-0 right-0 h-16 bg-gradient-to-t from-black/20 to-transparent pointer-events-none z-10"></div>
            
            <div class="absolute bottom-2 md:bottom-5 left-0 right-0 flex justify-center items-center gap-2 z-20 pb-2" id="dynTracker_<?= $secId ?>"></div>
            
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    if(typeof initDynSlider === 'function') {
                        initDynSlider(<?= $secId ?>);
                    }
                });
            </script>
            <?php endif; ?>
        </div>

        <?php elseif($sec['type'] == 'split_banner'): ?>
        <div class="grid md:grid-cols-2 gap-6">
            <?php foreach($items as $i): 
                 $img = get_image_url($i['image']);
            ?>
            <a href="<?= htmlspecialchars(resolve_cms_item_link(($conn instanceof mysqli) ? $conn : null, $i)) ?>" class="block rounded-2xl overflow-hidden shadow-md group">
                <div class="relative overflow-hidden">
                    <img src="<?= htmlspecialchars($img) ?>" loading="lazy" width="600" height="400" class="w-full h-auto object-cover group-hover:scale-105 transition duration-500" alt="<?= htmlspecialchars($i['title'] ?? 'Banner') ?>">
                    <div class="absolute inset-0 bg-black/10 group-hover:bg-transparent transition"></div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>

        <?php elseif($sec['type'] == 'reviews'): ?>
        <?php if(count($reviewsData) > 0): ?>
        <div class="grid md:grid-cols-3 gap-8">
            <?php foreach($reviewsData as $r): ?>
            <div class="bg-white p-8 rounded-[2rem] border border-gray-100 shadow-sm hover:shadow-xl transition-all duration-300">
                <div class="flex text-yellow-400 mb-4 text-xs">
                    <?php for($k=0; $k<$r['rating']; $k++) echo '<i class="fas fa-star"></i>'; ?>
                </div>
                <p class="text-gray-600 italic leading-relaxed mb-6 text-sm">"<?= htmlspecialchars($r['review_text']) ?>"</p>
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center font-bold text-primary text-xs">
                        <?= substr($r['name'], 0, 1) ?>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-800 text-sm"><?= htmlspecialchars($r['name']) ?></h4>
                        <span class="text-[10px] uppercase font-bold text-gray-400 tracking-widest"><?= htmlspecialchars($r['platform']) ?></span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
            <div class="text-center text-gray-400 py-10">No reviews to display yet.</div>
        <?php endif; ?>

        <?php elseif(in_array($sec['type'], ['grid_square', 'grid_rect', 'grid_circle', 'grid_heart'])): ?>
        <?php
            $shapeClass = '';
            if($sec['type'] == 'grid_square') $shapeClass = 'm-square';
            elseif($sec['type'] == 'grid_rect') $shapeClass = 'm-rect';
            elseif($sec['type'] == 'grid_circle') $shapeClass = 'm-circle';
            elseif($sec['type'] == 'grid_heart') $shapeClass = 'm-heart';
        ?>
        <div class="master-section-container">
            <div class="master-product-grid">
                <?php foreach($items as $i): 
                    $img = get_image_url($i['image']);
                ?>
                <a href="<?= htmlspecialchars(resolve_cms_item_link(($conn instanceof mysqli) ? $conn : null, $i)) ?>" class="master-card <?= $shapeClass ?>">
                    <div class="master-card-img-wrapper shadow-sm">
                        <img src="<?= htmlspecialchars($img) ?>" width="200" height="200" alt="<?= htmlspecialchars($i['title'] ?? '') ?>">
                    </div>
                    <?php if(!empty($i['title'])): ?>
                    <div class="master-label-ext"><?= htmlspecialchars($i['title']) ?></div>
                    <?php endif; ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>

        <?php elseif($sec['type'] == 'heart_carousel'): ?>
        <div class="heart-carousel-container hide-scrollbar">
            <?php foreach($items as $i): 
                $img = get_image_url($i['image']);
            ?>
            <div class="heart-carousel-item">
                <a href="<?= htmlspecialchars(resolve_cms_item_link(($conn instanceof mysqli) ? $conn : null, $i)) ?>" class="master-card m-heart">
                    <div class="master-card-img-wrapper">
                        <img src="<?= htmlspecialchars($img) ?>" width="160" height="160" alt="<?= htmlspecialchars($i['title'] ?? '') ?>">
                    </div>
                    <?php if(!empty($i['title'])): ?>
                    <div class="master-label-ext"><?= htmlspecialchars($i['title']) ?></div>
                    <?php endif; ?>
                </a>
            </div>
            <?php endforeach; ?>
        </div>

        <?php elseif($sec['type'] == 'circle_carousel'): ?>
        <div class="circle-carousel-container hide-scrollbar">
            <?php foreach($items as $i):
                $img = get_image_url($i['image']);
            ?>
            <div class="circle-carousel-item">
                <a href="<?= htmlspecialchars(resolve_cms_item_link(($conn instanceof mysqli) ? $conn : null, $i)) ?>" class="master-card m-circle">
                    <div class="master-card-img-wrapper shadow-md ring-2 ring-[#d4af37] ring-offset-2">
                        <img src="<?= htmlspecialchars($img) ?>" width="140" height="140" alt="<?= htmlspecialchars($i['title'] ?? '') ?>" loading="lazy">
                    </div>
                    <?php if(!empty($i['title'])): ?>
                    <div class="master-label-ext"><?= htmlspecialchars($i['title']) ?></div>
                    <?php endif; ?>
                </a>
            </div>
            <?php endforeach; ?>
        </div>

        <?php endif; ?>


        <?php if($sec['type'] == 'cta_banner'): ?>
        <?php
            $promos = $promosData;
            $btn = (count($items) > 0) ? $items[0] : null;

            if(count($promos) > 0):
        ?>
        <div class="relative group/promoslider">
            <div class="flex overflow-x-auto gap-4 md:gap-8 px-4 sm:px-8 pb-6 snap-x hide-scrollbar scroll-smooth" id="promoSliderContainer" style="scroll-snap-type: x mandatory;">
                <?php foreach($promos as $index => $promo): ?>
                
                <a href="<?= $btn ? htmlspecialchars(resolve_cms_item_link(($conn instanceof mysqli) ? $conn : null, $btn, '/gifts')) : '#' ?>" class="promo-slide block relative flex-shrink-0 w-[90vw] max-w-[850px] mx-auto flex items-stretch rounded-2xl sm:rounded-3xl md:rounded-[2.5rem] overflow-hidden shadow-[0_8px_30px_rgb(0,0,0,0.06)] hover:shadow-[0_12px_40px_rgb(0,0,0,0.1)] transition-all duration-300 group snap-center" style="background: linear-gradient(135deg, #fefce3 0%, #eadd95 100%);" data-index="<?= $index ?>">
                    
                    <div class="flex-1 min-w-0 p-4 sm:p-8 md:p-12 relative z-20 flex flex-col justify-center border-r-[2px] border-dashed border-[#d2c47a]/70">
                        <div class="flex items-center gap-2 sm:gap-3 mb-3 sm:mb-8 md:mb-12">
                            <img src="/uploads/logo_transparent.png" width="160" height="48" class="h-8 sm:h-10 md:h-12 w-auto object-contain" alt="Sai Flower">
                        </div>
                        
                        <p class="text-[9px] sm:text-[12px] md:text-[14px] text-[#716843] font-semibold tracking-[0.15em] uppercase mb-0.5 sm:mb-2 ml-0.5 font-sans truncate">
                            <?= htmlspecialchars($sec['title']) ?>
                        </p>
                        <h3 class="text-[24px] sm:text-[46px] md:text-[64px] font-bold text-[#554d30] mb-3 sm:mb-8 md:mb-12 leading-[1.05] tracking-tight font-sans whitespace-normal break-words pr-2">
                            <?= htmlspecialchars($promo['discount_text']) ?>
                        </h3>
                        
                        <p class="text-[7.5px] sm:text-[10px] md:text-[12px] text-[#635a39] font-medium ml-0.5 whitespace-normal break-words pr-2" style="letter-spacing: 0.02em;">
                            T&C Apply 
                            <?php if($promo['min_order_amount'] > 0): ?>
                            | Min order &#8377;<?= number_format($promo['min_order_amount']) ?>
                            <?php else: ?>
                            | <?= htmlspecialchars($sec['subtitle'] ?? '') ?>
                            <?php endif; ?>
                        </p>

                        <div class="absolute top-0 right-0 translate-x-[calc(50%+1px)] -translate-y-1/2 w-4 h-4 sm:w-6 sm:h-6 md:w-8 md:h-8 bg-white rounded-full sm:bg-gray-50 mask-bg"></div>
                        <div class="absolute bottom-0 right-0 translate-x-[calc(50%+1px)] translate-y-1/2 w-4 h-4 sm:w-6 sm:h-6 md:w-8 md:h-8 bg-white rounded-full sm:bg-gray-50 mask-bg"></div>
                    </div>

                    <div class="flex-shrink-0 w-[30%] sm:w-[30%] md:w-[28%] max-w-[240px] flex items-center justify-center relative overflow-hidden z-10" style="background: linear-gradient(135deg, rgba(234,221,149,0) 0%, rgba(220,207,130,0.5) 100%);">
                        <div class="absolute inset-0 flex items-center justify-center translate-x-1 sm:translate-x-2 md:translate-x-6">
                            <span class="font-black leading-none select-none" style="color: #e5d793; font-size: min(18vw, 160px); transform: rotate(-90deg) translateX(5%); white-space: nowrap; font-family: sans-serif; letter-spacing: -0.05em;">Off<span class="text-[0.6em] ml-1">%</span></span>
                        </div>
                        
                        <div class="relative z-20 bg-white/80 backdrop-blur-md px-3 py-2 sm:px-4 sm:py-2 md:px-6 md:py-4 rounded-xl border border-white/60 text-center shadow-[0_4px_15px_rgb(0,0,0,0.05)] transform group-hover:-translate-y-1 group-hover:bg-white transition-all duration-300 w-11/12 sm:w-auto mx-auto">
                            <span class="block text-[8px] sm:text-[9px] md:text-[11px] font-bold text-[#8a7a58] mb-0.5 tracking-wider font-sans leading-tight">USE CODE</span>
                            <span class="block text-[14px] sm:text-[15px] md:text-[22px] font-black text-[#e21b22] uppercase tracking-wider font-sans leading-none"><?= htmlspecialchars($promo['code']) ?></span>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
            
            <?php if(count($promos) > 1): ?>
            <div class="absolute bottom-8 left-0 right-0 flex justify-center items-center gap-2 z-30 pb-2 pointer-events-none" id="promoSliderTracker">
                </div>
            <?php endif; ?>

            <style>
                @media (min-width: 640px) { .mask-bg { background-color: #f9fafb !important; } }
                @media (max-width: 639px) { .mask-bg { background-color: #ffffff !important; } }
                .hide-scrollbar::-webkit-scrollbar { display: none; }
                .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
                
                #promoSliderTracker .slider-dot.active-pill::after {
                    background-color: var(--progress-color, #334155);
                }
                #promoSliderTracker .slider-dot { pointer-events: auto; }
            </style>
            
            <?php if(count($promos) > 1): ?>
            <script>
            document.addEventListener("DOMContentLoaded", () => {
                const container = document.getElementById('promoSliderContainer');
                const tracker = document.getElementById('promoSliderTracker');
                const slides = container.querySelectorAll('.promo-slide');
                const totalSlides = slides.length;
                let currentPromoIndex = 0;
                let autoSlidePromoInterval;

                function updatePromoTracker() {
                    if(!tracker) return;
                    tracker.innerHTML = '';
                    for(let i=0; i<totalSlides; i++) {
                        const dot = document.createElement('div');
                        if(i === currentPromoIndex) {
                            dot.className = 'slider-dot active-pill !w-8 !bg-slate-200';
                            dot.style.setProperty('--progress-color', '#334155');
                        } else {
                            dot.className = 'slider-dot !bg-slate-400 !opacity-60 hover:!opacity-100';
                            dot.onclick = () => scrollToPromo(i);
                        }
                        tracker.appendChild(dot);
                    }
                }

                function scrollToPromo(index) {
                    if(index >= totalSlides) index = 0;
                    if(index < 0) index = totalSlides - 1;
                    
                    currentPromoIndex = index;
                    const slide = slides[index];
                    
                    const containerCenter = container.offsetWidth / 2;
                    const slideCenter = slide.offsetLeft + (slide.offsetWidth / 2);
                    const scrollPosition = slideCenter - containerCenter;

                    container.scrollTo({
                        left: scrollPosition,
                        behavior: 'smooth'
                    });
                    
                    updatePromoTracker();
                    resetPromoAutoSlide();
                    
                    const activeDot = tracker.querySelector('.active-pill');
                    if (activeDot) {
                        activeDot.style.animation = 'none';
                        activeDot.offsetHeight;
                        activeDot.style.animation = null; 
                    }
                }

                function nextPromo() {
                    scrollToPromo(currentPromoIndex + 1);
                }

                function startPromoAutoSlide() {
                    autoSlidePromoInterval = setInterval(nextPromo, 3000);
                }

                function resetPromoAutoSlide() {
                    clearInterval(autoSlidePromoInterval);
                    startPromoAutoSlide();
                }

                updatePromoTracker();
                startPromoAutoSlide();

                container.addEventListener("touchstart", () => clearInterval(autoSlidePromoInterval));
                container.addEventListener("touchend", () => startPromoAutoSlide());
                
                container.addEventListener('scroll', () => {
                    const scrollLeft = container.scrollLeft;
                    const containerCenter = scrollLeft + (container.offsetWidth / 2);
                    
                    let closestIndex = 0;
                    let minDiff = Infinity;
                    
                    slides.forEach((slide, index) => {
                        const slideCenter = slide.offsetLeft + (slide.offsetWidth / 2);
                        const diff = Math.abs(slideCenter - containerCenter);
                        if(diff < minDiff) {
                            minDiff = diff;
                            closestIndex = index;
                        }
                    });

                    if(closestIndex !== currentPromoIndex) {
                        currentPromoIndex = closestIndex;
                        updatePromoTracker();
                    }
                }, { passive: true });
            });
            </script>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>

        <?php if($sec['type'] == 'newsletter'): ?>
        <div class="bg-gray-900 text-white py-10 md:py-16 rounded-2xl">
            <div class="text-center px-4">
                <h2 class="text-3xl font-bold mb-4"><?= htmlspecialchars($sec['title']) ?></h2>
                <?php if(!empty($sec['subtitle'])): ?>
                    <p class="text-gray-400 max-w-2xl mx-auto mb-8"><?= htmlspecialchars($sec['subtitle']) ?></p>
                <?php endif; ?>
                <?php if(count($items) > 0): ?>
                <div class="flex justify-center gap-6 flex-wrap mt-8">
                    <?php foreach($items as $i): 
                        $img = get_image_url($i['image']);
                    ?>
                    <div class="flex items-center gap-3 bg-white/10 px-6 py-3 rounded-full backdrop-blur-sm">
                        <?php if(!empty($i['image'])): ?>
                            <img src="<?= htmlspecialchars($img) ?>" width="24" height="24" class="w-6 h-6 object-contain invert">
                        <?php endif; ?>
                        <span class="text-sm font-medium"><?= htmlspecialchars($i['title']) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

    </div>

    <?php endforeach; ?>
