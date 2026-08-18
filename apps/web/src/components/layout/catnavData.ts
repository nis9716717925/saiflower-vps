/** Category nav mega-menu data mirrored from partials/catnav.php (saiflower.com). */

export type CatNavLink = { label: string; href: string };
export type CatNavColumn = { title: string; links: CatNavLink[] };
export type CatNavItem = {
  label: string;
  href: string;
  mega?: {
    cols?: number;
    columns: CatNavColumn[];
  };
};

export const CATNAV_ITEMS: CatNavItem[] = [
  {
    label: 'Birthday',
    href: '/occasion/birthday',
    mega: {
      cols: 2,
      columns: [
        {
          title: 'Shop Birthday',
          links: [
            { label: 'Birthday Bouquets', href: '/occasion/birthday' },
            { label: 'Birthday Combos', href: '/collection/flower-combos' },
            { label: 'Birthday Cakes', href: '/cakes' },
          ],
        },
        {
          title: 'Popular Picks',
          links: [
            { label: 'Same Day Birthday Gifts', href: '/collection/same-day-delivery' },
            { label: 'Rose Bouquets', href: '/flowers/roses' },
            { label: 'Gift Hampers', href: '/collection/hampers' },
          ],
        },
      ],
    },
  },
  {
    label: 'Anniversary',
    href: '/occasion/anniversary',
    mega: {
      cols: 2,
      columns: [
        {
          title: 'Shop Anniversary',
          links: [
            { label: 'Anniversary Flowers', href: '/occasion/anniversary' },
            { label: 'Romantic Roses', href: '/flowers/roses' },
            { label: 'Flower & Cake Combos', href: '/cakes' },
          ],
        },
        {
          title: 'Make It Special',
          links: [
            { label: 'Premium Collection', href: '/collection/premium-bouquets' },
            { label: 'Same Day Delivery', href: '/collection/same-day-delivery' },
            { label: 'Gift Hampers', href: '/collection/hampers' },
          ],
        },
      ],
    },
  },
  {
    label: 'Occasions',
    href: '/occasion/birthday',
    mega: {
      cols: 2,
      columns: [
        {
          title: 'By Occasion',
          links: [
            { label: 'Love & Romance', href: '/occasion/love-romance' },
            { label: 'Wedding', href: '/occasion/wedding' },
            { label: 'Congratulations', href: '/occasion/congratulations' },
            { label: 'Sympathy', href: '/occasion/sympathy' },
          ],
        },
        {
          title: 'More Moments',
          links: [
            { label: 'Birthday', href: '/occasion/birthday' },
            { label: 'Anniversary', href: '/occasion/anniversary' },
            { label: 'Thank You', href: '/occasion/thank-you' },
            { label: 'Celebrations Calendar', href: '/celebration-calendar' },
          ],
        },
      ],
    },
  },
  {
    label: 'Flowers',
    href: '/flowers',
    mega: {
      cols: 2,
      columns: [
        {
          title: 'Collections',
          links: [
            { label: 'All Bouquets', href: '/flowers' },
            { label: 'Rose Bouquets', href: '/flowers/roses' },
            { label: 'Premium Collection', href: '/collection/premium-bouquets' },
            { label: 'Newly Added', href: '/collection/new-arrivals' },
          ],
        },
        {
          title: 'By Flower Type',
          links: [
            { label: 'Orchids', href: '/flowers/orchids' },
            { label: 'Lilies', href: '/flowers/lilies' },
            { label: 'Carnations', href: '/flowers/carnations' },
            { label: 'Tulips', href: '/flowers/tulips' },
          ],
        },
      ],
    },
  },
  {
    label: 'LUXE',
    href: '/collection/luxury-flowers',
    mega: {
      cols: 2,
      columns: [
        {
          title: 'Shop LUXE',
          links: [
            { label: 'Premium Roses', href: '/flowers/roses' },
            { label: 'Designer Bouquets', href: '/collection/designer-bouquets' },
            { label: 'Luxury Combos', href: '/collection/flower-combos' },
            { label: 'All Luxe Collection', href: '/collection/luxury-flowers' },
          ],
        },
        {
          title: 'Curated For',
          links: [
            { label: 'Anniversary', href: '/occasion/anniversary' },
            { label: 'Wedding', href: '/occasion/wedding' },
            { label: 'Corporate Gifting', href: '/relation/colleagues' },
          ],
        },
      ],
    },
  },
  {
    label: 'Personalised',
    href: '/personalized',
    mega: {
      cols: 1,
      columns: [
        {
          title: 'Personalised Gifts',
          links: [
            { label: 'Photo Frames & Keepsakes', href: '/personalized/photo-frames' },
            { label: 'Custom Message Cards', href: '/personalized/custom-message-cards' },
            { label: 'Engraved Gifts', href: '/personalized/engraved-gifts' },
            { label: 'All Personalised', href: '/personalized' },
          ],
        },
      ],
    },
  },
  {
    label: 'Lifestyle',
    href: '/gifts',
    mega: {
      cols: 1,
      columns: [
        {
          title: 'Home & Living',
          links: [
            { label: 'Home Décor', href: '/gifts' },
            { label: 'Wellness & Candles', href: '/gifts' },
            { label: 'Planters & Pots', href: '/collection/plants' },
            { label: 'Personalised Gifts', href: '/personalized' },
          ],
        },
      ],
    },
  },
  {
    label: 'Hampers',
    href: '/collection/hampers',
    mega: {
      cols: 2,
      columns: [
        {
          title: 'Gift Hampers',
          links: [
            { label: 'Flower Hampers', href: '/collection/hampers' },
            { label: 'Chocolate Hampers', href: '/search-results?q=chocolate' },
            { label: 'Gourmet Hampers', href: '/collection/hampers' },
            { label: 'All Hampers', href: '/collection/hampers' },
          ],
        },
        {
          title: 'Popular Combos',
          links: [
            { label: 'Flower & Cake', href: '/cakes' },
            { label: 'Flower & Chocolates', href: '/collection/flower-combos' },
            { label: 'Same Day Hampers', href: '/collection/same-day-delivery' },
          ],
        },
      ],
    },
  },
  { label: 'Same Day Delivery', href: '/collection/same-day-delivery' },
  { label: 'Plants', href: '/collection/plants' },
  {
    label: 'Combos',
    href: '/collection/flower-combos',
    mega: {
      cols: 1,
      columns: [
        {
          title: 'Gift Combos',
          links: [
            { label: 'Flower & Cake', href: '/cakes' },
            { label: 'Gift Hampers', href: '/collection/hampers' },
            { label: 'All Gifts', href: '/gifts' },
          ],
        },
      ],
    },
  },
  {
    label: 'International',
    href: '/flower-delivery-in-delhi',
    mega: {
      cols: 1,
      columns: [
        {
          title: 'Delivery Locations',
          links: [
            { label: 'Delhi NCR', href: '/flower-delivery-in-delhi' },
            { label: 'Gurgaon', href: '/flower-delivery-in-gurgaon' },
            { label: 'Noida', href: '/flower-delivery-in-noida' },
            { label: 'Send Gifts Abroad', href: '/#hp-send-gifts-abroad' },
          ],
        },
      ],
    },
  },
];
