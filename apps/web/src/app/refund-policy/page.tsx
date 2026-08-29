import type { Metadata } from 'next';
import { LegalDocument } from '@/components/legal/LegalDocument';
import { pageMetadata } from '@/lib/site-metadata';

export const metadata = pageMetadata({
  title: 'Refund & Cancellation Policy | Sai Flower',
  description: 'Cancellation windows, replacements, and refund processing for Sai Flower orders.',
  keywords: ['refund policy', 'cancellation', 'returns'],
  canonical: '/refund-policy',
});

export default function RefundPolicyPage() {
  return (
    <LegalDocument title="Refund & Cancellation Policy">
      <p className="italic text-center">
        Because our products (flowers and cakes) are fresh and perishable, our policy is designed to
        balance customer satisfaction with the nature of our handcrafted items.
      </p>
      <h2>1. Cancellations</h2>
      <ul>
        <li>
          Cancellations made 24 hours or more before the scheduled delivery time are eligible for a full
          refund or store credit.
        </li>
        <li>
          Cancellations made within 24 hours of delivery cannot be refunded as the flowers have already
          been sourced and the cake prepared.
        </li>
      </ul>
      <h2>2. Damaged or Incorrect Items</h2>
      <p>
        If you receive wilted flowers or a damaged cake, please contact us at <strong>8802004527</strong>{' '}
        or email us at <strong>support@saiflower.com</strong> within 2 hours of delivery.
      </p>
      <p>
        <strong>Important:</strong> You must provide clear photographs of the damaged product and the
        delivery tag to be eligible for a replacement or refund.
      </p>
      <h2>3. Refund Processing</h2>
      <p>
        Approved refunds will be processed within 5–7 business days to your original payment method (Bank
        Account/UPI/Wallet).
      </p>
    </LegalDocument>
  );
}
