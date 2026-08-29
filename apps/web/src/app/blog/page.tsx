import type { Metadata } from 'next';
import { BlogListingView } from '@/components/blog/BlogListingView';
import { fetchBlogs } from '@/lib/api';
import { pageMetadata } from '@/lib/site-metadata';

export const metadata = pageMetadata({
  title: 'Blog | Floral Tips & Updates — Sai Flowers',
  description: 'Latest floral tips, gifting ideas and Sai Flowers updates for Delhi NCR.',
  canonical: '/blog',
});

export const revalidate = 120;

export default async function BlogPage() {
  let blogs: Awaited<ReturnType<typeof fetchBlogs>> = [];
  try {
    blogs = await fetchBlogs(100);
  } catch {
    blogs = [];
  }

  return <BlogListingView blogs={blogs} />;
}
