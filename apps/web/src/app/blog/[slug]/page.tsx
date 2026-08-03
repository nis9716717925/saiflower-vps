import type { Metadata } from 'next';
import { notFound } from 'next/navigation';
import { BlogDetailView } from '@/components/blog/BlogDetailView';
import { fetchBlog } from '@/lib/api';

interface PageProps {
  params: Promise<{ slug: string }>;
}

export async function generateMetadata({ params }: PageProps): Promise<Metadata> {
  const { slug } = await params;
  try {
    const blog = await fetchBlog(slug);
    return {
      title: `${blog.metaTitle || blog.title} | Sai Flowers`,
      description: blog.metaDescription || blog.excerpt,
      alternates: { canonical: `/blog/${blog.slug}` },
    };
  } catch {
    return { title: 'Blog | Sai Flowers' };
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
