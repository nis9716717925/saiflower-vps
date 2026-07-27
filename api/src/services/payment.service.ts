import { PaymentProvider } from '@prisma/client';
import { config } from '../config';
import { BadRequestError } from '../utils/errors';

export interface PaymentIntentResult {
  provider: PaymentProvider;
  clientSecret?: string;
  orderId?: string;
  keyId?: string;
  amount: number;
  currency: string;
}

export class PaymentService {
  getAvailableProviders() {
    const providers: Array<{
      id: PaymentProvider;
      name: string;
      enabled: boolean;
    }> = [
      { id: 'STRIPE', name: 'Stripe', enabled: config.payments.stripe.enabled },
      { id: 'PAYPAL', name: 'PayPal', enabled: config.payments.paypal.enabled },
      { id: 'RAZORPAY', name: 'Razorpay', enabled: config.payments.razorpay.enabled },
      { id: 'COD', name: 'Cash on Delivery', enabled: config.payments.cod.enabled },
    ];

    return providers.filter((p) => p.enabled);
  }

  isProviderEnabled(provider: PaymentProvider): boolean {
    const map: Record<PaymentProvider, boolean> = {
      STRIPE: config.payments.stripe.enabled,
      PAYPAL: config.payments.paypal.enabled,
      RAZORPAY: config.payments.razorpay.enabled,
      COD: config.payments.cod.enabled,
    };
    return map[provider] ?? false;
  }

  async createPaymentIntent(
    provider: PaymentProvider,
    amount: number,
    orderId: string,
    metadata?: Record<string, string>,
  ): Promise<PaymentIntentResult> {
    if (!this.isProviderEnabled(provider)) {
      throw new BadRequestError(`Payment provider ${provider} is not enabled`);
    }

    const currency = config.app.currency.toLowerCase();

    switch (provider) {
      case 'STRIPE':
        return {
          provider,
          clientSecret: `pi_mock_${orderId}`,
          amount,
          currency,
        };

      case 'PAYPAL':
        return {
          provider,
          orderId: `PAYPAL-${orderId}`,
          amount,
          currency,
        };

      case 'RAZORPAY':
        return {
          provider,
          orderId: `order_${orderId}`,
          keyId: config.payments.razorpay.keyId,
          amount: Math.round(amount * 100),
          currency,
        };

      case 'COD':
        return { provider, amount, currency };

      default:
        throw new BadRequestError('Unsupported payment provider');
    }
  }

  async verifyPayment(
    provider: PaymentProvider,
    paymentRef: string,
    _amount: number,
  ): Promise<boolean> {
    if (provider === 'COD') return true;
    if (!paymentRef) return false;
    return true;
  }
}

export const paymentService = new PaymentService();
