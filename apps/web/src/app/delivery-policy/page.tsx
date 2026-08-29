import type { Metadata } from 'next';
import { LegalDocument } from '@/components/legal/LegalDocument';
import { pageMetadata } from '@/lib/site-metadata';

export const metadata = pageMetadata({
  title: 'Delivery Policy | Sai Flower',
  description: 'Delhi NCR delivery areas, same-day slots, and midnight delivery guidelines.',
  keywords: ['delivery policy', 'same day delivery', 'Delhi NCR'],
  canonical: '/delivery-policy',
});

export default function DeliveryPolicyPage() {
  return (
    <LegalDocument title="Delivery Policy">
      <p className="italic text-center">
        At <strong>Sai Flower</strong>, we strive to deliver your emotions on time, every time. Please
        review our delivery guidelines to ensure a seamless experience.
      </p>
      <h2>1. Delivery Areas</h2>
      <p>
        We currently serve all of Delhi NCR, including New Delhi, Noida, Gurgaon, Ghaziabad, and
        Faridabad.
      </p>
      <h2>2. Delivery Slots</h2>
      <ul>
        <li>
          <strong>Standard Delivery:</strong> Between 9:00 AM and 9:00 PM.
        </li>
        <li>
          <strong>Same-Day Delivery:</strong> Orders must be placed before 6:00 PM (IST) for same-day
          fulfillment.
        </li>
        <li>
          <strong>Midnight Delivery:</strong> Delivered between 11:15 PM and 12:15 AM. Please select the
          date <em>before</em> the occasion.
        </li>
        <li>
          <strong>Fixed Time Delivery:</strong> While we aim for exact timing, please allow a +/-
          30-minute buffer due to traffic and weather conditions in Delhi.
        </li>
      </ul>
      <h2>3. Address Accuracy</h2>
      <p>
        The customer is responsible for providing a correct and reachable phone number and address. If the
        recipient is not available, we will attempt to leave the gift with a neighbor or security guard,
        which will be considered a successful delivery.
      </p>
      <h2>4. Major Holidays</h2>
      <p>
        During peak times like Valentine’s Day or Mother’s Day, specific time slots may not be guaranteed.
        We recommend ordering at least 48 hours in advance.
      </p>
    </LegalDocument>
  );
}
