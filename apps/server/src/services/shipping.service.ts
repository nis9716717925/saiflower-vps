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

type GoogleAddressComponent = {
  long_name: string;
  short_name: string;
  types: string[];
};

type GoogleAddressResult = {
  formatted_address?: string;
  address_components?: GoogleAddressComponent[];
  geometry?: { location?: { lat?: number; lng?: number } };
};

function requireMapsKey() {
  if (!config.shipping.googleMapsApiKey) {
    throw new AppError('Google Maps API key is not configured', 503);
  }
  return config.shipping.googleMapsApiKey;
}

function component(
  components: GoogleAddressComponent[] | undefined,
  ...types: string[]
): string {
  return (
    components?.find((item) => types.some((type) => item.types.includes(type)))?.long_name ?? ''
  );
}

function mapGoogleAddress(result: GoogleAddressResult) {
  const parts = result.address_components;
  const streetNumber = component(parts, 'street_number', 'premise', 'subpremise');
  const route = component(parts, 'route');
  const neighborhood = component(
    parts,
    'sublocality_level_2',
    'sublocality_level_1',
    'neighborhood',
  );
  const locality = component(parts, 'locality', 'administrative_area_level_2');
  const state = component(parts, 'administrative_area_level_1');
  const pincode = component(parts, 'postal_code');
  const localityParts = [route, neighborhood, locality, state].filter(
    (value, index, values) => value && values.indexOf(value) === index,
  );
  const lat = result.geometry?.location?.lat;
  const lng = result.geometry?.location?.lng;

  return {
    flatHouseNo: streetNumber,
    apartmentStreetLocality: localityParts.join(', ') || result.formatted_address || '',
    pincode,
    formattedAddress: result.formatted_address ?? '',
    latitude: typeof lat === 'number' ? lat : null,
    longitude: typeof lng === 'number' ? lng : null,
  };
}

async function fetchGoogleJson<T>(url: string): Promise<T> {
  let response: Response;
  try {
    response = await fetch(url);
  } catch {
    throw new AppError('Unable to reach Google Maps. Please try again.', 502);
  }
  if (!response.ok) {
    throw new AppError('Google Maps request failed. Please try again.', 502);
  }
  return (await response.json()) as T;
}

export async function autocompleteAddress(inputRaw: string) {
  const input = inputRaw.trim();
  if (input.length < 3) return [];

  const params = new URLSearchParams({
    input,
    key: requireMapsKey(),
    components: 'country:in',
    location: `${config.shipping.storeLat},${config.shipping.storeLng}`,
    radius: '100000',
  });
  const data = await fetchGoogleJson<{
    status?: string;
    error_message?: string;
    predictions?: Array<{ description: string; place_id: string }>;
  }>(`https://maps.googleapis.com/maps/api/place/autocomplete/json?${params}`);

  if (data.status === 'ZERO_RESULTS') return [];
  if (data.status !== 'OK') {
    throw new AppError(data.error_message ?? 'Could not search Google Maps addresses', 502);
  }
  return (data.predictions ?? []).slice(0, 5).map((prediction) => ({
    description: prediction.description,
    placeId: prediction.place_id,
  }));
}

export async function getPlaceAddress(placeIdRaw: string) {
  const placeId = placeIdRaw.trim();
  if (!placeId) throw new ValidationError('Google place ID is required');

  const params = new URLSearchParams({
    place_id: placeId,
    fields: 'formatted_address,address_component,geometry',
    key: requireMapsKey(),
  });
  const data = await fetchGoogleJson<{
    status?: string;
    error_message?: string;
    result?: GoogleAddressResult;
  }>(`https://maps.googleapis.com/maps/api/place/details/json?${params}`);

  if (data.status !== 'OK' || !data.result) {
    throw new AppError(data.error_message ?? 'Could not load this Google Maps address', 502);
  }
  return mapGoogleAddress(data.result);
}

export async function reverseGeocode(latitude: number, longitude: number) {
  if (!Number.isFinite(latitude) || !Number.isFinite(longitude)) {
    throw new ValidationError('Valid location coordinates are required');
  }

  const params = new URLSearchParams({
    latlng: `${latitude},${longitude}`,
    key: requireMapsKey(),
    result_type: 'street_address|premise|subpremise|route',
  });
  const data = await fetchGoogleJson<{
    status?: string;
    error_message?: string;
    results?: GoogleAddressResult[];
  }>(`https://maps.googleapis.com/maps/api/geocode/json?${params}`);

  const result = data.results?.[0];
  if (data.status !== 'OK' || !result) {
    throw new AppError(data.error_message ?? 'Could not detect an address at this location', 422);
  }
  const mapped = mapGoogleAddress(result);
  // Prefer the GPS fix the browser already gave us when Google omits geometry.
  return {
    ...mapped,
    latitude: mapped.latitude ?? latitude,
    longitude: mapped.longitude ?? longitude,
  };
}

/** Geocode a free-text address to coordinates (for Maps links on orders). */
export async function geocodeAddress(addressRaw: string): Promise<{
  latitude: number;
  longitude: number;
} | null> {
  const address = addressRaw.trim();
  if (!address || !config.shipping.googleMapsApiKey) return null;

  const params = new URLSearchParams({
    address,
    key: config.shipping.googleMapsApiKey,
    components: 'country:IN',
  });
  try {
    const data = await fetchGoogleJson<{
      status?: string;
      results?: GoogleAddressResult[];
    }>(`https://maps.googleapis.com/maps/api/geocode/json?${params}`);
    const loc = data.results?.[0]?.geometry?.location;
    if (data.status !== 'OK' || typeof loc?.lat !== 'number' || typeof loc?.lng !== 'number') {
      return null;
    }
    return { latitude: loc.lat, longitude: loc.lng };
  } catch {
    return null;
  }
}

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
