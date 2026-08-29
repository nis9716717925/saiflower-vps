import type { Metadata } from 'next';
import { notFound } from 'next/navigation';
import { BlogDetailView } from '@/components/blog/BlogDetailView';
import { fetchBlog } from '@/lib/api';
import { pageMetadata } from '@/lib/site-metadata';

export const revalidate = 120;

interface PageProps {
  params: Promise<{ slug: string }>;
}

export async function generateMetadata({ params }: PageProps): Promise<Metadata> {
  const { slug } = await params;
  try {
    const blog = await fetchBlog(slug);
    return pageMetadata({
      title: `${blog.metaTitle || blog.title} | Sai Flowers`,
      description: blog.metaDescription || blog.excerpt || 'Floral tips and gifting ideas from Sai Flowers.',
      canonical: `/blog/${blog.slug}`,
    });
  } catch {
    return pageMetadata({
      title: 'Blog | Sai Flowers',
      description: 'Latest floral tips, gifting ideas and Sai Flowers updates for Delhi NCR.',
    });
  }
}

export default async function BlogDetailPage({ params }: PageProps) {
  const { slug } = await params;
  try {
    const blog = await fetchBlog(slug);
    return <BlogDetailView blog={blog} />;
  } catch {
    notFound();
  }
}
