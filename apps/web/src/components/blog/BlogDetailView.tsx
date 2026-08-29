'use client';

import Link from 'next/link';
import { OptimizedImage, IMAGE_SIZE_PRESETS } from '@/components/ui/OptimizedImage';
import type { BlogPost } from '@/lib/types';

const FALLBACK =
  'https://images.unsplash.com/photo-1490750967868-58cb75069faf?q=80&w=1600';

/** Mirrors PHP format_content(): markdown links + newlines → HTML. */
export function formatBlogContent(content: string): string {
  const withLinks = (content ?? '').replace(
    /\[([^\]]+)\]\(([^)]+)\)/g,
    '<a href="$2" target="_blank" rel="noopener noreferrer">$1</a>',
  );
  return withLinks.replace(/\n/g, '<br />');
}

function formatDate(iso?: string | null): string {
  if (!iso) return '';
  try {
    return new Date(iso).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
    });
  } catch {
    return '';
  }
}

export function BlogDetailView({ blog }: { blog: BlogPost }) {
  const html = formatBlogContent(blog.content);
  const date = formatDate(blog.createdAt);

  return (
    <main className="blog-detail">
      <div className="blog-detail__hero">
        <span className="blog-detail__badge">Floral Insights</span>
        <h1 className="blog-detail__title serif">{blog.title}</h1>
        <div className="blog-detail__meta">
          {date ? (
            <span>
              <i className="far fa-calendar mr-2" aria-hidden="true" />
              {date}
            </span>
          ) : null}
          <span className="w-1.5 h-1.5 bg-slate-300 rounded-full inline-block" aria-hidden="true" />
          <span>
            <i className="far fa-user mr-2" aria-hidden="true" />
            Sai Flowers Editorial
          </span>
        </div>
      </div>

      <div className="blog-detail__layout">
        <div>
          {blog.image ? (
            <OptimizedImage
              src={blog.image}
              fallback={FALLBACK}
              alt={blog.title}
              className="blog-detail__img"
              width={1200}
              height={500}
              priority
              sizes={IMAGE_SIZE_PRESETS.hero}
            />
          ) : null}
          <article className="blog-detail__article">
            <div dangerouslySetInnerHTML={{ __html: html }} />
          </article>
        </div>

        <aside className="blog-detail__side">
          <div className="blog-detail__card">
            <h3>About the Author</h3>
            <p>
              Sai Flowers is dedicated to creating premium floral arrangements for unforgettable
              moments. We source the freshest blooms to craft stunning masterpieces for every
              occasion.
            </p>
            <Link href="/about">
              Read more <i className="fas fa-arrow-right text-xs" aria-hidden="true" />
            </Link>
          </div>
          <div className="blog-detail__card">
            <h3 style={{ fontSize: '0.875rem', letterSpacing: '0.08em', textTransform: 'uppercase' }}>
              Keep Exploring
            </h3>
            <p>Browse more floral stories or shop fresh bouquets for same-day delivery.</p>
            <div style={{ display: 'flex', flexDirection: 'column', gap: '0.65rem' }}>
              <Link href="/blog">All blog posts →</Link>
              <Link href="/flowers">Shop flowers →</Link>
            </div>
          </div>
        </aside>
      </div>
    </main>
  );
}
