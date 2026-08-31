import type { CSSProperties } from 'react';
import { CATNAV_ITEMS, type CatNavItem } from '@/components/layout/catnavData';
import { InfiniteMarquee } from '@/components/ui/InfiniteMarquee';

function CatNavListItem({ item, withMega }: { item: CatNavItem; withMega: boolean }) {
  return (
    <li className="lx-catnav__item">
      <a
        className="lx-catnav__link"
        href={item.href}
        {...(item.mega && withMega ? { 'aria-haspopup': true as const } : {})}
      >
        {item.label}
        {item.mega && withMega ? <span className="lx-caret" aria-hidden="true" /> : null}
      </a>

      {item.mega && withMega ? (
        <div
          className="lx-catnav__mega lx-catnav__mega--simple"
          data-cols={item.mega.cols ?? 2}
          style={{ '--lx-mega-cols': item.mega.cols ?? 2 } as CSSProperties}
        >
          <div className="lx-catnav__mega-inner">
            {item.mega.columns.map((col) => (
              <div key={col.title} className="lx-catnav__col">
                <p className="lx-catnav__col-title">{col.title}</p>
                {col.links.map((link) => (
                  <a key={link.href + link.label} href={link.href}>
                    {link.label}
                  </a>
                ))}
              </div>
            ))}
          </div>
        </div>
      ) : null}
    </li>
  );
}

function CatNavList({ withMega }: { withMega: boolean }) {
  return (
    <ul className="lx-catnav__list">
      {CATNAV_ITEMS.map((item) => (
        <CatNavListItem key={item.label} item={item} withMega={withMega} />
      ))}
    </ul>
  );
}

export function CatNav() {
  return (
    <nav className="lx-catnav" aria-label="Shop categories">
      <div className="lx-catnav__shell lx-marquee--no-fade">
        <InfiniteMarquee
          className="lx-catnav__marquee"
          trackClassName="lx-catnav__marquee-track"
          speed="slow"
          duplicate={<CatNavList withMega={false} />}
        >
          <CatNavList withMega />
        </InfiniteMarquee>
      </div>
    </nav>
  );
}
