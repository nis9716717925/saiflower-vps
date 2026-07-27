<section class="sai-neighborhood-map" aria-label="Neighborhood map">
  <div class="sai-neighborhood-map__inner">
    <div class="sai-neighborhood-map__header">
      <h2 class="sai-neighborhood-map__title">Explore Our Neighborhood</h2>
      <p class="sai-neighborhood-map__subtitle">Discover nearby places around Sai Flower on Lodhi Road — search, browse, and get directions.</p>
    </div>
    <div class="sai-neighborhood-map__frame-wrap">
      <iframe
        class="sai-neighborhood-map__frame"
        src="/tools/neighborhood-discovery.php"
        title="Sai Flower neighborhood discovery map"
        loading="lazy"
        allowfullscreen
        referrerpolicy="no-referrer-when-downgrade">
      </iframe>
    </div>
  </div>
</section>

<style>
  .sai-neighborhood-map {
    padding: 2rem 1rem 2.75rem;
    background: #f9fafb;
  }

  .sai-neighborhood-map__inner {
    max-width: 1200px;
    margin: 0 auto;
  }

  .sai-neighborhood-map__header {
    margin-bottom: 1rem;
    text-align: center;
  }

  .sai-neighborhood-map__title {
    margin: 0 0 0.35rem;
    font-size: clamp(1.35rem, 2.5vw, 1.75rem);
    font-weight: 700;
    color: #111827;
    line-height: 1.25;
  }

  .sai-neighborhood-map__subtitle {
    margin: 0;
    font-size: clamp(0.9rem, 2vw, 1rem);
    color: #6b7280;
    line-height: 1.5;
    max-width: 42rem;
    margin-inline: auto;
  }

  .sai-neighborhood-map__frame-wrap {
    border: 1px solid #e5e7eb;
    border-radius: 1rem;
    overflow: hidden;
    background: #fff;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
  }

  .sai-neighborhood-map__frame {
    width: 100%;
    border: 0;
    display: block;
    height: 560px;
    min-height: 560px;
  }

  /* Mobile: taller frame so 48% map + 52% list both get usable space */
  @media (max-width: 768px) {
    .sai-neighborhood-map {
      padding: 1.5rem 0.75rem 2rem;
    }

    .sai-neighborhood-map__frame-wrap {
      border-radius: 0.75rem;
    }

    .sai-neighborhood-map__frame {
      height: min(88vh, 720px);
      min-height: 640px;
    }
  }

  /* Large desktop: side-by-side layout needs less vertical space */
  @media (min-width: 1024px) {
    .sai-neighborhood-map__frame {
      height: 520px;
      min-height: 520px;
    }
  }
</style>
