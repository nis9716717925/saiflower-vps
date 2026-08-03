import Link from 'next/link';
import {
  CELEBRATION_ITEMS,
  celebrationHref,
  celebrationsGroupByMonth,
  celebrationsUpcoming,
} from '@/lib/celebrations';

export function CelebrationCalendarPage() {
  const items = CELEBRATION_ITEMS;
  const months = celebrationsGroupByMonth(items);
  const upcoming = celebrationsUpcoming(items, 6);
  const waLink = 'https://wa.me/918802004527';
  const heroImg = '/celebrations/valentines-day.jpg';

  return (
    <div className="cc-page">
      <header className="cc-hero" style={{ ['--cc-hero-image' as string]: `url('${heroImg}')` }}>
        <div className="cc-hero__shade" />
        <div className="cc-wrap cc-hero__inner">
          <nav className="cc-crumb" aria-label="Breadcrumb">
            <ol>
              <li>
                <Link href="/">Home</Link>
              </li>
              <li aria-current="page">Celebrations Calendar</li>
            </ol>
          </nav>
          <p className="cc-kicker">Sai Flowers · Delhi NCR</p>
          <h1 className="cc-hero__title">Celebrations Calendar</h1>
          <p className="cc-hero__lead">
            Never miss a moment — browse the full year of gifting days and open the matching flower
            collection in one tap.
          </p>
          <div className="cc-hero__actions">
            <a className="cc-btn cc-btn--accent" href="#cc-months">
              Browse by month
            </a>
            <Link className="cc-btn cc-btn--ghost" href="/flowers">
              Shop all flowers
            </Link>
            <Link className="cc-btn cc-btn--ghost" href="/checkout">
              Checkout
            </Link>
          </div>
        </div>
      </header>

      {upcoming.length > 0 ? (
        <section className="cc-upcoming" aria-labelledby="cc-up-title">
          <div className="cc-wrap">
            <div className="cc-head">
              <h2 id="cc-up-title">Coming up next</h2>
              <p>Shop early for the celebrations closest on the calendar.</p>
            </div>
            <div className="cc-upcoming__grid">
              {upcoming.map((item) => (
                <Link key={item.slug} className="cc-card cc-card--feature" href={celebrationHref(item)}>
                  <span className="cc-card__media">
                    <img
                      src={item.image}
                      alt={`${item.title} flowers`}
                      width={400}
                      height={480}
                      loading="eager"
                      decoding="async"
                    />
                  </span>
                  <span className="cc-card__body">
                    <span className="cc-card__date">{item.date}</span>
                    <span className="cc-card__title">{item.title}</span>
                    <span className="cc-card__cta">
                      Shop gifts <i className="fas fa-arrow-right" aria-hidden="true" />
                    </span>
                  </span>
                </Link>
              ))}
            </div>
          </div>
        </section>
      ) : null}

      <main id="cc-months" className="cc-main">
        <div className="cc-wrap">
          <div className="cc-head">
            <h2>Full year at a glance</h2>
            <p>
              {items.length} celebration days with curated flower, gift &amp; relation landings.
            </p>
          </div>

          <div className="cc-month-nav" role="navigation" aria-label="Jump to month">
            {months.map((group) => (
              <a key={group.month} href={`#month-${group.month.toLowerCase()}`}>
                {group.month.slice(0, 3)}
              </a>
            ))}
          </div>

          {months.map((group) => (
            <section
              key={group.month}
              className="cc-month"
              id={`month-${group.month.toLowerCase()}`}
              aria-labelledby={`h-${group.month.toLowerCase()}`}
            >
              <h3 id={`h-${group.month.toLowerCase()}`}>{group.month}</h3>
              <div className="cc-grid">
                {group.items.map((item) => (
                  <Link key={item.slug} className="cc-card" href={celebrationHref(item)}>
                    <span className="cc-card__media">
                      <img
                        src={item.image}
                        alt={`${item.title} celebration gifts`}
                        width={320}
                        height={400}
                        loading="lazy"
                        decoding="async"
                      />
                    </span>
                    <span className="cc-card__body">
                      <span className="cc-card__date">{item.date}</span>
                      <span className="cc-card__title">{item.title}</span>
                    </span>
                  </Link>
                ))}
              </div>
            </section>
          ))}
        </div>
      </main>

      <section className="cc-help" aria-labelledby="cc-help-title">
        <div className="cc-wrap cc-help__inner">
          <div>
            <h2 id="cc-help-title">Need help picking?</h2>
            <p>
              Tell us the date and who you’re gifting — our florists will suggest the right bouquet
              for Delhi NCR same-day delivery.
            </p>
          </div>
          <div className="cc-help__actions">
            <Link className="cc-btn cc-btn--accent" href="/flowers">
              Shop flowers
            </Link>
            <a
              className="cc-btn cc-btn--dark"
              href={waLink}
              target="_blank"
              rel="noopener noreferrer"
            >
              WhatsApp florist
            </a>
          </div>
        </div>
      </section>
    </div>
  );
}
