<?php
require_once __DIR__ . '/../../includes/celebrations_calendar_data.php';
require_once __DIR__ . '/../../includes/occasion_links.php';
global $conn;
$hpCelebrations = celebrations_calendar_get_items();
?>
<link rel="stylesheet" href="/assets/css/celebrations-calendar.css?v=4" />
<section class="hp-section hp-celebrations" aria-labelledby="hp-celebrations-title">
    <div class="hp-container">
        <div class="hp-section-head">
            <div class="hp-celebrations-head">
                <h2 id="hp-celebrations-title" class="hp-section-title">Celebrations Calendar</h2>
                <a class="hp-celebrations-all" href="/celebration-calendar">View full calendar <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
            </div>
        </div>

        <div class="hp-celebrations-carousel-wrap">
            <button type="button"
                    class="hp-celebrations-nav hp-celebrations-nav--prev"
                    id="hpCelebrationsPrev"
                    aria-label="Previous celebrations"
                    aria-controls="hpCelebrationsTrack">
                <i class="fas fa-chevron-left" aria-hidden="true"></i>
            </button>

            <div class="hp-celebrations-track-wrap" id="hpCelebrationsTrackWrap">
                <div class="hp-celebrations__track hide-scrollbar"
                     id="hpCelebrationsTrack"
                     role="list"
                     tabindex="0"
                     aria-label="Celebrations calendar carousel">
                    <?php foreach ($hpCelebrations as $item): ?>
                    <a href="<?= htmlspecialchars(celebrations_calendar_href($item, ($conn instanceof mysqli) ? $conn : null)) ?>"
                       class="hp-celebration-card"
                       role="listitem"
                       draggable="false"
                       aria-label="<?= htmlspecialchars($item['title']) ?> — <?= htmlspecialchars($item['date']) ?>">
                        <span class="hp-celebration-card__date"><?= htmlspecialchars($item['date']) ?></span>
                        <span class="hp-celebration-card__img-wrap">
                            <img src="<?= htmlspecialchars($item['image']) ?>"
                                 alt="<?= htmlspecialchars($item['title']) ?> celebration gifts"
                                 width="320"
                                 height="400"
                                 loading="lazy"
                                 decoding="async"
                                 draggable="false">
                        </span>
                        <span class="hp-celebration-card__title"><?= htmlspecialchars($item['title']) ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <button type="button"
                    class="hp-celebrations-nav hp-celebrations-nav--next"
                    id="hpCelebrationsNext"
                    aria-label="Next celebrations"
                    aria-controls="hpCelebrationsTrack">
                <i class="fas fa-chevron-right" aria-hidden="true"></i>
            </button>
        </div>
    </div>
</section>
<script defer src="/assets/js/celebrations-calendar.js?v=3"></script>
