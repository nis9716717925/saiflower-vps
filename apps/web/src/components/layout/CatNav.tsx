import type { CSSProperties } from 'react';
import { CATNAV_ITEMS } from '@/components/layout/catnavData';

export function CatNav() {
  return (
    <nav className="lx-catnav" aria-label="Shop categories">
      <ul className="lx-catnav__list hide-scrollbar">
        {CATNAV_ITEMS.map((item) => (
          <li key={item.label} className="lx-catnav__item">
            <a
              className="lx-catnav__link"
              href={item.href}
              {...(item.mega ? { 'aria-haspopup': true as const } : {})}
            >
              {item.label}
              {item.mega ? <span className="lx-caret" aria-hidden="true" /> : null}
            </a>

            {item.mega ? (
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
        ))}
      </ul>
    </nav>
  );
}
