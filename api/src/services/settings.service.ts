import prisma from '../config/database';
import { config } from '../config';
import { Prisma } from '@prisma/client';
import { resolveLegacyMediaUrl } from '../utils/media';

export class SettingsService {
  private isMissingTableError(error: unknown): boolean {
    return (
      error instanceof Prisma.PrismaClientKnownRequestError &&
      error.code === 'P2021'
    );
  }

  private async getLegacySettings() {
    const rows = await prisma.$queryRawUnsafe<
      Array<Record<string, string | null | number>>
    >('SELECT * FROM settings LIMIT 1');

    const row = rows[0] ?? {};

    return {
      appName: String(row.site_title ?? config.app.name),
      currency: config.app.currency,
      currencySymbol: config.app.currencySymbol,
      maintenanceMode: Boolean(Number(row.maintenance_mode ?? 0)) || config.app.maintenanceMode,
      branding: {
        logoUrl: resolveLegacyMediaUrl(row.logo ? String(row.logo) : null, 'generic'),
        primaryColor: row.theme_primary ? String(row.theme_primary) : '#e91e63',
        supportEmail: row.email ? String(row.email) : 'support@saiflower.com',
        supportPhone: row.phone ? String(row.phone) : null,
      },
      features: {
        guestCheckout: true,
        reviewsEnabled: true,
        wishlistEnabled: true,
      },
      paymentProviders: [
        { id: 'STRIPE', enabled: config.payments.stripe.enabled },
        { id: 'PAYPAL', enabled: config.payments.paypal.enabled },
        { id: 'RAZORPAY', enabled: config.payments.razorpay.enabled },
        { id: 'COD', enabled: config.payments.cod.enabled },
      ],
      socialLogin: {
        google: config.oauth.google.enabled,
        facebook: config.oauth.facebook.enabled,
      },
    };
  }

  async getPublicSettings() {
    try {
      const dbSettings = await prisma.appSetting.findMany();
      const settingsMap = Object.fromEntries(dbSettings.map((s) => [s.key, s.value]));

      return {
        appName: settingsMap.appName ?? config.app.name,
        currency: settingsMap.currency ?? config.app.currency,
        currencySymbol: settingsMap.currencySymbol ?? config.app.currencySymbol,
        maintenanceMode: settingsMap.maintenanceMode === 'true' || config.app.maintenanceMode,
        branding: {
          logoUrl: settingsMap.logoUrl ?? null,
          primaryColor: settingsMap.primaryColor ?? '#e91e63',
          supportEmail: settingsMap.supportEmail ?? 'support@saiflower.com',
          supportPhone: settingsMap.supportPhone ?? null,
        },
        features: {
          guestCheckout: settingsMap.guestCheckout !== 'false',
          reviewsEnabled: settingsMap.reviewsEnabled !== 'false',
          wishlistEnabled: settingsMap.wishlistEnabled !== 'false',
        },
        paymentProviders: [
          { id: 'STRIPE', enabled: config.payments.stripe.enabled },
          { id: 'PAYPAL', enabled: config.payments.paypal.enabled },
          { id: 'RAZORPAY', enabled: config.payments.razorpay.enabled },
          { id: 'COD', enabled: config.payments.cod.enabled },
        ],
        socialLogin: {
          google: config.oauth.google.enabled,
          facebook: config.oauth.facebook.enabled,
        },
      };
    } catch (error) {
      if (!this.isMissingTableError(error)) {
        throw error;
      }
      return this.getLegacySettings();
    }
  }
}

export const settingsService = new SettingsService();
