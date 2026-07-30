import { config } from '../config';
import { AppError, ValidationError } from '../utils/errors';

export type ShippingOk = {
  status: 'ok';
  distance_km: number;
  shipping_fee: number;
  distance_text: string;
};

export type ShippingErr = {
  status: 'error';
  message: string;
};

/** Mirrors includes/shipping_helper.php calculate_shipping_from_address(). */
export async function calculateShippingFromAddress(
  destinationRaw: string,
): Promise<ShippingOk | ShippingErr> {
  const destination = destinationRaw.trim();
  if (!destination) {
    return { status: 'error', message: 'Delivery address is required.' };
  }

  if (!config.shipping.googleMapsApiKey) {
    return {
      status: 'error',
      message: 'Could not calculate distance: Maps API key is not configured',
    };
  }

  const origin = `${config.shipping.storeLat},${config.shipping.storeLng}`;
  const params = new URLSearchParams({
    origins: origin,
    destinations: destination,
    mode: 'driving',
    units: 'metric',
    key: config.shipping.googleMapsApiKey,
  });

  const url = `https://maps.googleapis.com/maps/api/distancematrix/json?${params}`;

  let data: {
    status?: string;
    error_message?: string;
    rows?: Array<{ elements?: Array<{ status?: string; distance?: { value?: number; text?: string } }> }>;
  };

  try {
    const res = await fetch(url);
    data = (await res.json()) as typeof data;
  } catch {
    return { status: 'error', message: 'Unable to reach maps service. Please try again.' };
  }

  if (!data || data.status !== 'OK') {
    const apiMessage = data?.error_message ?? data?.status ?? 'Unknown error';
    return { status: 'error', message: `Could not calculate distance: ${apiMessage}` };
  }

  const element = data.rows?.[0]?.elements?.[0];
  if (!element || element.status !== 'OK') {
    return {
      status: 'error',
      message: 'We could not find a route to this address. Please check and try again.',
    };
  }

  const meters = Number(element.distance?.value ?? 0);
  const distanceKm = Math.round((meters / 1000) * 100) / 100;
  const shippingFee = Math.round(distanceKm * config.shipping.ratePerKm);

  return {
    status: 'ok',
    distance_km: distanceKm,
    shipping_fee: shippingFee,
    distance_text: element.distance?.text ?? `${distanceKm} km`,
  };
}

export async function calculateShippingParts(input: {
  address_line?: string;
  city?: string;
  zip?: string;
}) {
  const parts = [input.address_line, input.city, input.zip || null, 'India'].filter(
    (p): p is string => Boolean(p && String(p).trim()),
  );
  const destination = parts.join(', ');
  const result = await calculateShippingFromAddress(destination);

  if (result.status === 'ok') {
    return {
      status: 'ok' as const,
      distance_km: result.distance_km,
      distance_text: result.distance_text,
      shipping_fee: result.shipping_fee,
      rate_per_km: config.shipping.ratePerKm,
      store_address: config.shipping.storeAddress,
    };
  }
  return result;
}

export function assertShippingReady(result: ShippingOk | ShippingErr): asserts result is ShippingOk {
  if (result.status !== 'ok') {
    throw new AppError(result.message, 400);
  }
}

export function requireAddressFields(name: string, phone: string, address: string) {
  if (!name.trim() || !phone.trim() || !address.trim()) {
    throw new ValidationError('Missing required fields');
  }
}
