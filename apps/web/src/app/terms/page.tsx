import type { Metadata } from 'next';
import { LegalDocument } from '@/components/legal/LegalDocument';
import { pageMetadata } from '@/lib/site-metadata';

export const metadata = pageMetadata({
  title: 'Terms and Conditions | Sai Flower',
  description: 'Terms governing use of saiflower.com and Sai Flower orders.',
  keywords: ['terms and conditions', 'user agreement'],
  canonical: '/terms',
});

export default function TermsPage() {
  return (
    <LegalDocument title="Terms and Conditions">
      <p>
        Welcome to <strong>Sai Flower</strong>. By accessing our website (saiflower.com) and placing an
        order, you agree to the following terms and conditions.
      </p>
      <h2>1. General</h2>
      <p>
        These terms govern your use of our website and services. We reserve the right to update or modify
        these terms at any time without prior notice.
      </p>
      <h2>2. Products and Substitution Policy</h2>
      <ul>
        <li>
          <strong>Freshness:</strong> We deal in perishable items like flowers and cakes. While we strive to
          match the images on our website exactly, natural variations in color, size, and blooming stages
          may occur.
        </li>
        <li>
          <strong>Substitutions:</strong> In the event of temporary, regional availability issues, our expert
          florists may substitute specific flowers, colors, or vases with items of equal or higher value to
          ensure your gift is delivered on time.
        </li>
      </ul>
      <h2>3. Pricing and Payments</h2>
      <ul>
        <li>All prices listed on the website are in Indian Rupees (INR) and are subject to change without notice.</li>
        <li>Full payment must be received before an order is processed and dispatched.</li>
        <li>
          Discount codes must be applied at checkout and cannot be claimed after the order is placed. Only
          one discount code can be used per order.
        </li>
      </ul>
      <h2>4. User Responsibilities</h2>
      <ul>
        <li>
          <strong>Accurate Information:</strong> You are responsible for providing accurate recipient details.
          Sai Flower is not liable for delayed or failed deliveries caused by incorrect or incomplete
          addresses.
        </li>
        <li>
          <strong>Account Security:</strong> If you create an account, you are responsible for maintaining the
          confidentiality of your login credentials.
        </li>
      </ul>
      <h2>5. Limitation of Liability</h2>
      <p>
        Sai Flower shall not be held liable for any indirect, incidental, or consequential damages arising
        from the use of our website or the delay/failure of delivery due to unforeseen circumstances (e.g.,
        severe weather, strikes, or major traffic delays in Delhi NCR).
      </p>
    </LegalDocument>
  );
}
