const CATNAV_LINKS = [
  { label: 'Birthday', href: '/occasion/birthday' },
  { label: 'Anniversary', href: '/occasion/anniversary' },
  { label: 'Occasions', href: '/occasion/birthday' },
  { label: 'Flowers', href: '/flowers' },
  { label: 'LUXE', href: '/collection/luxury-flowers' },
  { label: 'Personalised', href: '/personalized' },
  { label: 'Lifestyle', href: '/gifts' },
  { label: 'Hampers', href: '/collection/hampers' },
  { label: 'Same Day Delivery', href: '/collection/same-day-delivery' },
  { label: 'Plants', href: '/collection/plants' },
  { label: 'Combos', href: '/collection/flower-combos' },
  { label: 'International', href: '/#hp-send-gifts-abroad' },
];

export function CatNav() {
  return (
    <nav className="lx-catnav" aria-label="Shop categories">
      <ul className="lx-catnav__list hide-scrollbar">
        {CATNAV_LINKS.map((item) => (
          <li key={item.label} className="lx-catnav__item">
            <a className="lx-catnav__link" href={item.href}>
              {item.label}
            </a>
          </li>
        ))}
      </ul>
    </nav>
  );
}
