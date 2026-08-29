import type { Metadata } from 'next';
import { LegalDocument } from '@/components/legal/LegalDocument';
import { pageMetadata } from '@/lib/site-metadata';

export const metadata = pageMetadata({
  title: 'Privacy Policy | Sai Flower',
  description: 'How Sai Flower collects, uses, and protects your personal information.',
  keywords: ['privacy policy', 'data protection'],
  canonical: '/privacy',
});

export default function PrivacyPage() {
  return (
    <LegalDocument title="Privacy Policy">
      <p>
        At <strong>Sai Flowers</strong>, we are committed to protecting your privacy. This Privacy Policy
        explains how we collect, use, and safeguard your information when you visit our website or store.
      </p>
      <h2>1. Information We Collect</h2>
      <p>We may collect personal information that you voluntarily provide to us when you:</p>
      <ul>
        <li>Place an order for flowers or event decoration.</li>
        <li>Contact us via phone, email, or WhatsApp.</li>
      </ul>
      <h2>2. How We Use Your Information</h2>
      <p>We use the information we collect to:</p>
      <ul>
        <li>Process and deliver your floral orders.</li>
        <li>Communicate with you regarding your event decoration bookings.</li>
        <li>Respond to your inquiries and customer service requests.</li>
        <li>Improve our website and service offerings.</li>
      </ul>
      <h2>3. Information Sharing</h2>
      <p>
        We do not sell, trade, or rent your personal information to others. We may share your delivery
        address and phone number with our trusted delivery personnel strictly for the purpose of fulfilling
        your order.
      </p>
      <h2>4. Cookies</h2>
      <p>
        Our website may use &quot;cookies&quot; to enhance user experience. You may choose to set your web
        browser to refuse cookies, or to alert you when cookies are being sent.
      </p>
      <h2>5. Contact Us</h2>
      <p>If you have any questions about this Privacy Policy, please contact us at:</p>
      <div className="bg-[#f4f9f6] border-l-4 border-primary rounded-lg p-5 mt-4">
        <p className="font-bold text-slate-800">Sai Flowers</p>
        <p>
          RZ-44A/1, Street No.1, Main Palam-Dabri Road,
          <br />
          Vaishali, Sector 7 Dwarka, New Delhi 110045
        </p>
        <p className="mt-3">
          <strong>Phone:</strong> +91 8802004527
          <br />
          <strong>Email:</strong> info@saiflowers.com
        </p>
      </div>
    </LegalDocument>
  );
}
