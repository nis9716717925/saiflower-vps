'use client';

import Link from 'next/link';
import { OptimizedImage, IMAGE_SIZE_PRESETS } from '@/components/ui/OptimizedImage';
import type { BlogListItem } from '@/lib/types';

const FALLBACK =
  'https://images.unsplash.com/photo-1490750967868-58cb75069faf?q=80&w=600';

export function BlogListingView({ blogs }: { blogs: BlogListItem[] }) {
  return (
    <section className="blog-section">
      <h1 className="blog-page-title">Latest Updates</h1>
      <div className="blog-grid">
        {blogs.length === 0 ? (
          <div className="blog-empty">
            <h3>No blogs posted yet.</h3>
            <p>Check back later for updates!</p>
          </div>
        ) : (
          blogs.map((b) => (
            <Link key={b.id} href={b.url || `/blog/${b.slug}`} className="blog-card">
              <OptimizedImage
                src={b.image}
                fallback={FALLBACK}
                alt={b.title}
                width={600}
                height={400}
                sizes={IMAGE_SIZE_PRESETS.gallery}
              />
              <div className="blog-content">
                <h3>{b.title}</h3>
                <p>
                  {b.excerpt}
                  {b.excerpt.endsWith('…') || b.excerpt.endsWith('...') ? '' : '...'}
                </p>
                <span className="read-more-btn">Read Full Article →</span>
              </div>
            </Link>
          ))
        )}
      </div>
    </section>
  );
}
