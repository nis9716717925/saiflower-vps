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
                className={`lx-catnav__mega${item.mega.simple ? ' lx-catnav__mega--simple' : ''}`}
                style={
                  {
                    '--lx-mega-cols': item.mega.cols ?? 2,
                    ...(item.mega.simple
                      ? { width: 'min(420px, calc(100vw - 3rem))' }
                      : {}),
                  } as CSSProperties
                }
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

                  {item.mega.feature ? (
                    <a className="lx-catnav__feature" href={item.mega.feature.href}>
                      <img
                        src={item.mega.feature.img}
                        alt=""
                        width={240}
                        height={240}
                        loading="lazy"
                        decoding="async"
                      />
                      <span className="lx-catnav__feature-label">
                        {item.mega.feature.label}
                        <span>{item.mega.feature.sub}</span>
                      </span>
                    </a>
                  ) : null}
                </div>
              </div>
            ) : null}
          </li>
        ))}
      </ul>
    </nav>
  );
}
