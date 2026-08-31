'use client';

import Link from 'next/link';
import { useRouter, useSearchParams } from 'next/navigation';
import { Suspense, useEffect, useMemo, useRef, useState } from 'react';
import {
  SHIPPING,
  buildHourlyDeliverySlots,
  calcMidnightSurcharge,
  defaultDeliverySlotForDate,
  firstAvailableHourlySlotForDate,
  isHourlySlotPastForDate,
  isMidnightNow,
  resolveCheckoutDelivery,
  shouldApplyMidnightCharge,
  todayDateString,
} from '@saiflower/shared';
import { apiGet, apiSend, getCustomer } from '@/lib/api';
import { useCart } from '@/components/providers/AppProviders';
import { CheckoutProgress } from '@/components/checkout/CheckoutProgress';
import { OptimizedImage } from '@/components/ui/OptimizedImage';
import { formatInr } from '@/lib/images';
import type {
  AddressSuggestion,
  AddressType,
  AuthSession,
  CartData,
  CustomerAddress,
  GoogleAddressDetails,
  PlaceOrderResult,
  ShippingResult,
} from '@/lib/types';

type CheckoutStep = 'address' | 'payment';

type ShippingOk = {
  ok: true;
  fee: number;
  distanceKm: number;
  distanceText: string;
};

type ShippingFail = { ok: false };

type CheckoutAddressSnapshot = {
  recipientName: string;
  mobile: string;
  email: string | null;
  flatHouseNo: string;
  apartmentStreetLocality: string;
  pincode: string;
  addressType: AddressType;
  latitude?: number | null;
  longitude?: number | null;
  orderingForMe?: boolean;
};

const ADDRESS_TYPES: AddressType[] = ['Home', 'Work', 'Other'];

const HOURLY_TIME_SLOTS = buildHourlyDeliverySlots();

function resetDeliverySchedule(): {
  date: string;
  hourlySlot: string;
} {
  const today = todayDateString();
  return {
    date: today,
    hourlySlot: defaultDeliverySlotForDate(today),
  };
}

function buildDeliveryAddress(flatHouseNo: string, locality: string, pincode: string): string {
  return [flatHouseNo.trim(), locality.trim(), 'Delhi', pincode.trim(), 'India']
    .filter(Boolean)
    .join(', ');
}

export default function CheckoutPage() {
  return (
    <Suspense
      fallback={
        <main className="qc-shell">
          <div className="qc-skeleton" />
        </main>
      }
    >
      <CheckoutPageContent />
    </Suspense>
  );
}

function CheckoutPageContent() {
  const router = useRouter();
  const searchParams = useSearchParams();
  const isGuest = searchParams.get('guest') === '1';
  const { refreshCart } = useCart();
  const [step, setStep] = useState<CheckoutStep>('address');
  const [cart, setCart] = useState<CartData | null>(null);
  const [loading, setLoading] = useState(true);
  const [savingAddress, setSavingAddress] = useState(false);
  const [submitting, setSubmitting] = useState(false);
  const [addresses, setAddresses] = useState<CustomerAddress[]>([]);
  const [guestAddress, setGuestAddress] = useState<CheckoutAddressSnapshot | null>(null);
  const [selectedAddressId, setSelectedAddressId] = useState<number | null>(null);
  const [showNewAddressForm, setShowNewAddressForm] = useState(true);

  const [shippingReady, setShippingReady] = useState(false);
  const [shippingFee, setShippingFee] = useState(0);
  const [distanceKm, setDistanceKm] = useState(0);
  const [distanceText, setDistanceText] = useState('');
  const [shippingMsg, setShippingMsg] = useState<{
    type: 'loading' | 'success' | 'error';
    text: string;
  } | null>(null);

  const [recipientName, setRecipientName] = useState('');
  const [mobile, setMobile] = useState('');
  const [email, setEmail] = useState('');
  const [flatHouseNo, setFlatHouseNo] = useState('');
  const [apartmentStreetLocality, setApartmentStreetLocality] = useState('');
  const [pincode, setPincode] = useState('');
  const [addressType, setAddressType] = useState<AddressType>('Home');
  const [orderingForMe, setOrderingForMe] = useState(false);
  const [deliveryLatitude, setDeliveryLatitude] = useState<number | null>(null);
  const [deliveryLongitude, setDeliveryLongitude] = useState<number | null>(null);
  const [showAddressDetails, setShowAddressDetails] = useState(false);
  const [formError, setFormError] = useState('');
  const [addressSuggestions, setAddressSuggestions] = useState<AddressSuggestion[]>([]);
  const [addressSearchActive, setAddressSearchActive] = useState(false);
  const [searchingAddress, setSearchingAddress] = useState(false);
  const [addressSearchError, setAddressSearchError] = useState('');
  const [detectingLocation, setDetectingLocation] = useState(false);
  const autoDetectAttempted = useRef(false);

  const [delDate, setDelDate] = useState('');
  const [delTime, setDelTime] = useState('');
  const [deliverNow, setDeliverNow] = useState(false);
  const [slotMode, setSlotMode] = useState<'hourly' | 'custom'>('hourly');
  const [customTime, setCustomTime] = useState('');

  const customer = getCustomer();
  const selectedAddress = useMemo(
    () => addresses.find((a) => a.id === selectedAddressId) ?? null,
    [addresses, selectedAddressId],
  );

  const paymentAddress = isGuest ? guestAddress : selectedAddress;

  useEffect(() => {
    void (async () => {
      try {
        const session = await apiGet<AuthSession>('/auth/session');
        if (session.authenticated && isGuest) {
          router.replace('/checkout');
          return;
        }
        if (!session.authenticated && !isGuest) {
          router.replace(`/login?redirect=${encodeURIComponent('/checkout')}`);
          return;
        }

        const data = await apiGet<CartData>('/cart');
        setCart(data);
        if (data.items.length === 0) {
          router.replace('/cart');
          return;
        }

        if (isGuest) {
          setShowNewAddressForm(true);
          return;
        }

        const saved = await apiGet<CustomerAddress[]>('/addresses');
        setAddresses(saved);
        if (saved.length > 0) {
          const preferred = saved.find((a) => a.isDefault) ?? saved[0];
          setSelectedAddressId(preferred.id);
          setShowNewAddressForm(false);
          fillFormFromAddress(preferred);
        } else {
          setShowNewAddressForm(true);
          setEmail(session.customer?.email ?? customer?.email ?? '');
          setMobile(session.customer?.phone ?? customer?.phone ?? '');
        }
      } catch {
        if (isGuest) {
          router.replace('/cart');
        } else {
          router.replace(`/login?redirect=${encodeURIComponent('/checkout')}`);
        }
      } finally {
        setLoading(false);
      }
    })();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [router, isGuest]);

  useEffect(() => {
    const { date, hourlySlot } = resetDeliverySchedule();
    setDelDate(date);
    setDelTime(hourlySlot);
    setDeliverNow(false);
    setSlotMode('hourly');
    setCustomTime('');
  }, []);

  const resolvedDelivery = useMemo(
    () =>
      resolveCheckoutDelivery({
        deliverNow,
        date: delDate,
        hourlySlot: delTime,
        slotMode,
        customTime,
      }),
    [customTime, delDate, delTime, deliverNow, slotMode],
  );

  const midnightApplies = useMemo(
    () =>
      shouldApplyMidnightCharge({
        deliverNow,
        date: delDate,
        deliveryTime: resolvedDelivery.deliveryTime,
        slotMode,
        customTime,
      }),
    [customTime, delDate, deliverNow, resolvedDelivery.deliveryTime, slotMode],
  );

  function handleDeliverNowChange(next: boolean) {
    setDeliverNow(next);
    if (next) {
      setDelDate(todayDateString());
      return;
    }
    const { date, hourlySlot } = resetDeliverySchedule();
    setDelDate(date);
    setDelTime(hourlySlot);
    setSlotMode('hourly');
    setCustomTime('');
  }

  function handleDeliveryDateChange(nextDate: string) {
    setDelDate(nextDate);
    setDelTime(firstAvailableHourlySlotForDate(nextDate));
    setSlotMode('hourly');
    setCustomTime('');
  }

  useEffect(() => {
    if (deliverNow || slotMode !== 'hourly' || !delDate || !delTime) return;
    const slot = HOURLY_TIME_SLOTS.find((s) => s.value === delTime);
    if (!slot || !isHourlySlotPastForDate(slot.hour, delDate)) return;
    setDelTime(firstAvailableHourlySlotForDate(delDate));
  }, [delDate, delTime, deliverNow, slotMode]);

  function fillFormFromAddress(address: CustomerAddress) {
    setRecipientName(address.recipientName);
    setMobile(address.mobile);
    setEmail(address.email ?? '');
    setFlatHouseNo(address.flatHouseNo);
    setApartmentStreetLocality(address.apartmentStreetLocality);
    setPincode(address.pincode);
    setAddressType(address.addressType);
    setDeliveryLatitude(null);
    setDeliveryLongitude(null);
    setAddressSearchActive(false);
    setAddressSuggestions([]);
  }

  function applyOrderingForMe(next: boolean) {
    setOrderingForMe(next);
    setFormError('');
    if (next) {
      const profile = getCustomer();
      if (profile?.name && !recipientName.trim()) setRecipientName(profile.name);
      if (profile?.phone) setMobile(profile.phone);
      if (profile?.email) setEmail(profile.email);
      setAddressType('Home');
      setShowAddressDetails(false);
      if (!flatHouseNo.trim() && !apartmentStreetLocality.trim() && !pincode.trim()) {
        void detectCurrentLocation().catch(() => undefined);
      }
    } else {
      setShowAddressDetails(true);
    }
  }

  useEffect(() => {
    if (!addressSearchActive || apartmentStreetLocality.trim().length < 3) {
      setAddressSuggestions([]);
      return;
    }

    let cancelled = false;
    const timer = setTimeout(() => {
      setSearchingAddress(true);
      setAddressSearchError('');
      void apiGet<AddressSuggestion[]>(
        `/shipping/address-suggestions?input=${encodeURIComponent(apartmentStreetLocality.trim())}`,
      )
        .then((results) => {
          if (!cancelled) setAddressSuggestions(results);
        })
        .catch((err) => {
          if (!cancelled) {
            setAddressSuggestions([]);
            setAddressSearchError(
              err instanceof Error
                ? err.message
                : 'Google address search is unavailable; enter the address manually.',
            );
          }
        })
        .finally(() => {
          if (!cancelled) setSearchingAddress(false);
        });
    }, 350);

    return () => {
      cancelled = true;
      clearTimeout(timer);
    };
  }, [addressSearchActive, apartmentStreetLocality]);

  function applyGoogleAddress(address: GoogleAddressDetails) {
    if (address.flatHouseNo) setFlatHouseNo(address.flatHouseNo);
    setApartmentStreetLocality(address.apartmentStreetLocality);
    if (address.pincode) setPincode(address.pincode);
    if (typeof address.latitude === 'number' && typeof address.longitude === 'number') {
      setDeliveryLatitude(address.latitude);
      setDeliveryLongitude(address.longitude);
    }
    setAddressSearchActive(false);
    setAddressSuggestions([]);
    setAddressSearchError('');
    setFormError('');
  }

  async function selectAddressSuggestion(suggestion: AddressSuggestion) {
    setSearchingAddress(true);
    try {
      const address = await apiSend<GoogleAddressDetails>('/shipping/place-details', 'POST', {
        placeId: suggestion.placeId,
      });
      applyGoogleAddress(address);
    } catch (err) {
      setFormError(err instanceof Error ? err.message : 'Could not load this address');
    } finally {
      setSearchingAddress(false);
    }
  }

  async function detectCurrentLocation() {
    setFormError('');
    if (!navigator.geolocation) {
      setFormError('Location detection is not supported by this browser.');
      return;
    }

    setDetectingLocation(true);
    try {
      const position = await new Promise<GeolocationPosition>((resolve, reject) => {
        navigator.geolocation.getCurrentPosition(resolve, reject, {
          enableHighAccuracy: true,
          timeout: 12000,
          maximumAge: 60000,
        });
      });
      const address = await apiSend<GoogleAddressDetails>('/shipping/reverse-geocode', 'POST', {
        latitude: position.coords.latitude,
        longitude: position.coords.longitude,
      });
      applyGoogleAddress(address);
    } catch (err) {
      if (
        typeof err === 'object' &&
        err !== null &&
        'code' in err &&
        typeof (err as { code?: unknown }).code === 'number'
      ) {
        const code = (err as { code: number }).code;
        const message =
          code === 1
            ? 'Location permission was denied. Allow location access and try again.'
            : 'We could not detect your location. Please try again or enter the address.';
        setFormError(message);
        sessionStorage.setItem('sf-location-denied', '1');
      } else {
        setFormError(err instanceof Error ? err.message : 'Could not detect your location');
      }
    } finally {
      setDetectingLocation(false);
    }
  }

  // Auto-detect location once when the address form is empty (user can still type manually).
  useEffect(() => {
    if (loading || autoDetectAttempted.current) return;
    if (step !== 'address') return;
    if (!showNewAddressForm && addresses.length > 0) return;
    if (flatHouseNo.trim() || apartmentStreetLocality.trim() || pincode.trim()) return;
    if (!navigator.geolocation) return;
    if (sessionStorage.getItem('sf-location-denied') === '1') return;

    autoDetectAttempted.current = true;
    void detectCurrentLocation().catch(() => {
      sessionStorage.setItem('sf-location-denied', '1');
    });
    // eslint-disable-next-line react-hooks/exhaustive-deps -- run once when checkout address step is ready
  }, [loading, step, showNewAddressForm, addresses.length]);

  const subtotal = cart?.subtotal ?? 0;
  const discount = cart?.discountAmount ?? 0;
  const midnightFee = midnightApplies ? calcMidnightSurcharge(subtotal) : 0;
  const grandTotal = Math.max(0, subtotal + shippingFee + midnightFee - discount);

  async function calculateShipping(
    addressOverride?: { flatHouseNo: string; locality: string; pincode: string },
  ): Promise<ShippingOk | ShippingFail> {
    const flat = addressOverride?.flatHouseNo ?? flatHouseNo;
    const locality = addressOverride?.locality ?? apartmentStreetLocality;
    const pin = addressOverride?.pincode ?? pincode;

    if (!flat.trim() || !locality.trim() || !pin.trim()) {
      setShippingReady(false);
      setShippingFee(0);
      setShippingMsg(null);
      return { ok: false };
    }

    setShippingMsg({ type: 'loading', text: 'Calculating delivery distance…' });
    try {
      const result = await apiSend<ShippingResult>('/shipping/calculate', 'POST', {
        address_line: `${flat.trim()}, ${locality.trim()}`,
        city: 'Delhi',
        zip: pin.trim(),
      });
      if (result.status === 'ok') {
        const fee = result.shipping_fee ?? 0;
        const km = result.distance_km ?? 0;
        const text = result.distance_text ?? `${km} km`;
        setShippingReady(true);
        setShippingFee(fee);
        setDistanceKm(km);
        setDistanceText(text);
        setShippingMsg({
          type: 'success',
          text: `Delivery ${text} · Shipping ${formatInr(fee)} (₹${SHIPPING.ratePerKmInr}/km)`,
        });
        return { ok: true, fee, distanceKm: km, distanceText: text };
      }
      setShippingReady(false);
      setShippingFee(0);
      setShippingMsg({ type: 'error', text: result.message ?? 'Could not calculate shipping.' });
      return { ok: false };
    } catch (err) {
      setShippingReady(false);
      setShippingFee(0);
      setShippingMsg({
        type: 'error',
        text: err instanceof Error ? err.message : 'Unable to calculate shipping.',
      });
      return { ok: false };
    }
  }

  useEffect(() => {
    if (step !== 'address' && step !== 'payment') return;
    const timer = setTimeout(() => void calculateShipping(), 600);
    return () => clearTimeout(timer);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [flatHouseNo, apartmentStreetLocality, pincode, step]);

  async function handleSaveAndContinue(e: React.FormEvent) {
    e.preventDefault();
    setFormError('');

    if (!deliverNow) {
      if (!delDate.trim()) {
        setFormError('Please choose a delivery date.');
        return;
      }
      if (slotMode === 'hourly') {
        const slot = HOURLY_TIME_SLOTS.find((s) => s.value === delTime);
        if (!slot) {
          setFormError('Please choose a delivery time slot.');
          return;
        }
        if (isHourlySlotPastForDate(slot.hour, delDate)) {
          setFormError('That time slot has already passed. Please pick a later slot.');
          return;
        }
      }
      if (slotMode === 'custom' && !customTime.trim()) {
        setFormError('Please enter your preferred custom delivery time.');
        return;
      }
      if (slotMode === 'hourly' && !delTime.trim()) {
        setFormError('Please choose a delivery time slot.');
        return;
      }
    }

    setSavingAddress(true);
    try {
      if (isGuest) {
        const profile = getCustomer();
        const resolvedName = recipientName.trim() || profile?.name?.trim() || '';
        const resolvedMobile = (orderingForMe ? mobile.trim() || profile?.phone || '' : mobile.trim()).trim();
        const resolvedEmail = (orderingForMe ? email.trim() || profile?.email || '' : email.trim()) || null;
        const snapshot: CheckoutAddressSnapshot = {
          recipientName: resolvedName,
          mobile: resolvedMobile,
          email: resolvedEmail,
          flatHouseNo: flatHouseNo.trim(),
          apartmentStreetLocality: apartmentStreetLocality.trim(),
          pincode: pincode.trim(),
          addressType: orderingForMe ? 'Home' : addressType,
          latitude: deliveryLatitude,
          longitude: deliveryLongitude,
          orderingForMe,
        };
        if (!snapshot.recipientName) {
          setFormError('Please enter your name.');
          return;
        }
        if (!snapshot.mobile) {
          setFormError('Please enter your mobile number.');
          return;
        }
        if (!snapshot.flatHouseNo || !snapshot.apartmentStreetLocality || !snapshot.pincode) {
          setFormError(
            orderingForMe
              ? 'We need your delivery location. Tap “Use my location” or enter your address.'
              : 'Please fill all required delivery fields.',
          );
          setShowAddressDetails(true);
          return;
        }

        const shipping = await calculateShipping({
          flatHouseNo: snapshot.flatHouseNo,
          locality: snapshot.apartmentStreetLocality,
          pincode: snapshot.pincode,
        });
        if (!shipping.ok) {
          setFormError('Please enter a valid delivery address so we can calculate shipping.');
          return;
        }
        setGuestAddress(snapshot);
        setStep('payment');
        return;
      }

      let saved: CustomerAddress;
      const profile = getCustomer();
      const resolvedName = recipientName.trim() || profile?.name?.trim() || '';
      const resolvedMobile = (orderingForMe ? mobile.trim() || profile?.phone || '' : mobile.trim()).trim();
      const resolvedEmail = (orderingForMe ? email.trim() || profile?.email || '' : email.trim()) || null;

      if (!resolvedName) {
        setFormError('Please enter your name.');
        return;
      }
      if (!resolvedMobile) {
        setFormError('Please enter your mobile number.');
        return;
      }
      if (!flatHouseNo.trim() || !apartmentStreetLocality.trim() || !pincode.trim()) {
        setFormError(
          orderingForMe
            ? 'We need your delivery location. Tap “Use my location” or enter your address.'
            : 'Please fill all required delivery fields.',
        );
        setShowAddressDetails(true);
        return;
      }

      if (!showNewAddressForm && selectedAddressId) {
        saved = await apiSend<CustomerAddress>(`/addresses/${selectedAddressId}`, 'PATCH', {
          recipientName: resolvedName,
          mobile: resolvedMobile,
          email: resolvedEmail,
          flatHouseNo,
          apartmentStreetLocality,
          pincode,
          addressType: orderingForMe ? 'Home' : addressType,
          isDefault: true,
        });
      } else {
        saved = await apiSend<CustomerAddress>('/addresses', 'POST', {
          recipientName: resolvedName,
          mobile: resolvedMobile,
          email: resolvedEmail,
          flatHouseNo,
          apartmentStreetLocality,
          pincode,
          addressType: orderingForMe ? 'Home' : addressType,
          isDefault: true,
        });
      }

      const list = await apiGet<CustomerAddress[]>('/addresses');
      setAddresses(list);
      setSelectedAddressId(saved.id);
      setShowNewAddressForm(false);
      fillFormFromAddress(saved);

      const shipping = await calculateShipping({
        flatHouseNo: saved.flatHouseNo,
        locality: saved.apartmentStreetLocality,
        pincode: saved.pincode,
      });
      if (!shipping.ok) {
        setFormError('Please enter a valid delivery address so we can calculate shipping.');
        return;
      }
      setStep('payment');
    } catch (err) {
      setFormError(err instanceof Error ? err.message : 'Could not save address');
    } finally {
      setSavingAddress(false);
    }
  }

  async function handleSelectSavedAddress(address: CustomerAddress) {
    setSelectedAddressId(address.id);
    setShowNewAddressForm(false);
    fillFormFromAddress(address);
    setFormError('');
    await apiSend(`/addresses/${address.id}/default`, 'POST').catch(() => undefined);
  }

  function startNewAddress() {
    setShowNewAddressForm(true);
    setSelectedAddressId(null);
    setRecipientName(orderingForMe ? customer?.name ?? '' : '');
    setMobile(customer?.phone ?? '');
    setEmail(customer?.email ?? '');
    setFlatHouseNo('');
    setApartmentStreetLocality('');
    setPincode('');
    setAddressType('Home');
    setDeliveryLatitude(null);
    setDeliveryLongitude(null);
    setAddressSearchActive(false);
    setAddressSuggestions([]);
    setShowAddressDetails(!orderingForMe);
  }

  async function handleWhatsAppOrder() {
    if (!cart?.items.length || !paymentAddress) return;

    let fee = shippingFee;
    let km = distanceKm;
    if (!shippingReady) {
      const shipping = await calculateShipping({
        flatHouseNo: paymentAddress.flatHouseNo,
        locality: paymentAddress.apartmentStreetLocality,
        pincode: paymentAddress.pincode,
      });
      if (!shipping.ok) {
        alert('Please enter a valid delivery address so we can calculate shipping.');
        setStep('address');
        return;
      }
      fee = shipping.fee;
      km = shipping.distanceKm;
    }

    setSubmitting(true);
    const address = buildDeliveryAddress(
      paymentAddress.flatHouseNo,
      paymentAddress.apartmentStreetLocality,
      paymentAddress.pincode,
    );
    const itemLines = cart.items.map(
      (item) => `• ${item.name} (x${item.qty}) - ${formatInr(item.price * item.qty)}`,
    );
    const payable = Math.max(0, subtotal + fee + midnightFee - discount);
    const sender = getCustomer();
    const forMe =
      orderingForMe ||
      Boolean('orderingForMe' in paymentAddress && paymentAddress.orderingForMe);
    const customerName = forMe
      ? paymentAddress.recipientName
      : sender?.name || paymentAddress.recipientName;
    const customerPhone = forMe
      ? paymentAddress.mobile
      : sender?.phone || paymentAddress.mobile;
    const lat =
      ('latitude' in paymentAddress ? paymentAddress.latitude : null) ?? deliveryLatitude;
    const lng =
      ('longitude' in paymentAddress ? paymentAddress.longitude : null) ?? deliveryLongitude;

    try {
      const result = await apiSend<PlaceOrderResult>('/checkout/place-order', 'POST', {
        name: customerName,
        phone: customerPhone,
        email: paymentAddress.email || sender?.email || '',
        address,
        date: resolvedDelivery.date,
        delivery_time: resolvedDelivery.deliveryTime,
        deliver_now: deliverNow,
        slot_mode: slotMode,
        custom_delivery_time: customTime.trim() || undefined,
        recipient_name: forMe ? customerName : paymentAddress.recipientName,
        recipient_phone: forMe ? customerPhone : paymentAddress.mobile,
        ordering_for_me: forMe,
        ...(typeof lat === 'number' && typeof lng === 'number'
          ? { latitude: lat, longitude: lng }
          : {}),
        ...(isGuest || !('id' in paymentAddress)
          ? {}
          : { address_id: (paymentAddress as CustomerAddress).id }),
        items: itemLines.join('\n'),
        total: payable,
        shipping_fee: fee,
        midnight_fee: midnightFee,
        distance_km: km,
        discount_amount: discount,
      });
      await refreshCart();
      if (result.whatsappUrl) window.open(result.whatsappUrl, '_blank');
      router.push(`/?order_success=1&oid=${result.order_id}`);
    } catch (err) {
      alert(err instanceof Error ? err.message : 'Could not place order');
      setSubmitting(false);
    }
  }

  if (loading || !cart) {
    return (
      <main className="qc-shell">
        <div className="qc-skeleton" />
      </main>
    );
  }

  const showAddressForm = showNewAddressForm || addresses.length === 0 || Boolean(selectedAddress);

  const addressForm = (
    <div className="qc-card">
      <div className="qc-card__head">
        <h2 className="qc-card__title">
          <span className="material-icons-outlined">location_on</span>
          {showNewAddressForm || addresses.length === 0 ? 'Delivery address' : 'Confirm address'}
        </h2>
        <button
          type="button"
          className="qc-loc-btn"
          onClick={() => void detectCurrentLocation()}
          disabled={detectingLocation}
        >
          <span className="material-icons-outlined">
            {detectingLocation ? 'sync' : 'my_location'}
          </span>
          {detectingLocation ? 'Detecting…' : 'Use my location'}
        </button>
      </div>

      <label className="qc-check">
        <input
          type="checkbox"
          checked={orderingForMe}
          onChange={(e) => applyOrderingForMe(e.target.checked)}
        />
        <span>
          <strong>Ordering for me</strong>
          <span className="qc-check__hint">
            Only enter your name — we&apos;ll deliver to your location
          </span>
        </span>
      </label>

      <div className="qc-grid qc-grid--2">
        <div className="qc-field" style={orderingForMe ? { gridColumn: '1 / -1' } : undefined}>
          <label className="qc-label">{orderingForMe ? 'Your name' : 'Recipient name'}</label>
          <input
            className="qc-input"
            value={recipientName}
            onChange={(e) => setRecipientName(e.target.value)}
            placeholder={orderingForMe ? 'Your full name' : undefined}
            required
          />
        </div>
        {!(orderingForMe && (mobile.trim() || customer?.phone)) && (
          <div className="qc-field" style={orderingForMe ? { gridColumn: '1 / -1' } : undefined}>
            <label className="qc-label">Mobile number</label>
            <input
              className="qc-input"
              type="tel"
              inputMode="numeric"
              value={mobile}
              onChange={(e) => setMobile(e.target.value)}
              required
            />
          </div>
        )}
        {!orderingForMe && (
          <div className="qc-field" style={{ gridColumn: '1 / -1' }}>
            <label className="qc-label">Email id (optional)</label>
            <input
              className="qc-input"
              type="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
            />
          </div>
        )}

        {orderingForMe &&
          Boolean(flatHouseNo.trim() && apartmentStreetLocality.trim() && pincode.trim()) &&
          !showAddressDetails && (
            <div className="qc-field" style={{ gridColumn: '1 / -1' }}>
              <div className="qc-self-address">
                <div>
                  <strong>Delivering to your location</strong>
                  <p className="qc-muted" style={{ margin: '0.25rem 0 0', lineHeight: 1.45 }}>
                    {flatHouseNo}, {apartmentStreetLocality}
                    <br />
                    PIN {pincode}
                  </p>
                </div>
                <button
                  type="button"
                  className="qc-link-btn"
                  onClick={() => setShowAddressDetails(true)}
                >
                  Edit address
                </button>
              </div>
            </div>
          )}

        {(!orderingForMe ||
          showAddressDetails ||
          !flatHouseNo.trim() ||
          !apartmentStreetLocality.trim() ||
          !pincode.trim()) && (
          <>
        <div className="qc-field">
          <label className="qc-label">Flat / House no.</label>
          <input
            className="qc-input"
            value={flatHouseNo}
            onChange={(e) => {
              setFlatHouseNo(e.target.value);
              setDeliveryLatitude(null);
              setDeliveryLongitude(null);
            }}
            required
          />
        </div>
        <div className="qc-field">
          <label className="qc-label">Pincode</label>
          <input
            className="qc-input"
            value={pincode}
            onChange={(e) => {
              setPincode(e.target.value);
              setDeliveryLatitude(null);
              setDeliveryLongitude(null);
            }}
            required
          />
        </div>
        <div className="qc-field" style={{ gridColumn: '1 / -1' }}>
          <label className="qc-label">Apartment / Street / Locality</label>
          <p className="qc-muted" style={{ margin: '0 0 0.45rem', fontSize: '0.82rem' }}>
            Start typing for Google suggestions, or use &ldquo;Use my location&rdquo; above.
          </p>
          <div style={{ position: 'relative' }}>
            <input
              className="qc-input"
              style={{ paddingRight: '2.5rem' }}
              value={apartmentStreetLocality}
              onChange={(e) => {
                setApartmentStreetLocality(e.target.value);
                setAddressSearchActive(true);
                setDeliveryLatitude(null);
                setDeliveryLongitude(null);
              }}
              placeholder="Search with Google Maps"
              autoComplete="off"
              required
            />
            {searchingAddress && (
              <span
                className="material-icons-outlined"
                style={{
                  position: 'absolute',
                  right: '0.8rem',
                  top: '0.85rem',
                  color: '#9aa59e',
                  fontSize: '1.15rem',
                }}
              >
                sync
              </span>
            )}
            {addressSearchActive && addressSuggestions.length > 0 && (
              <div className="qc-suggest">
                {addressSuggestions.map((suggestion) => (
                  <button
                    key={suggestion.placeId}
                    type="button"
                    onClick={() => void selectAddressSuggestion(suggestion)}
                  >
                    <span className="material-icons-outlined" style={{ color: '#1f6a4a' }}>
                      location_on
                    </span>
                    <span>{suggestion.description}</span>
                  </button>
                ))}
                <p className="qc-suggest__powered">Powered by Google</p>
              </div>
            )}
          </div>
          {addressSearchError && (
            <p className="qc-muted" style={{ color: '#8a6110' }}>
              Google address search is unavailable. You can enter the address manually.
            </p>
          )}
        </div>
        {!orderingForMe && (
        <div className="qc-field" style={{ gridColumn: '1 / -1' }}>
          <label className="qc-label">Type of address</label>
          <div className="qc-chips">
            {ADDRESS_TYPES.map((type) => (
              <button
                key={type}
                type="button"
                className={`qc-chip${addressType === type ? ' is-active' : ''}`}
                onClick={() => setAddressType(type)}
              >
                {type}
              </button>
            ))}
          </div>
        </div>
        )}
          </>
        )}
      </div>
    </div>
  );

  const summaryCard = (
    <div className="qc-card">
      <div className="qc-card__head">
        <h2 className="qc-card__title">
          <span className="material-icons-outlined">shopping_bag</span>
          Order summary
        </h2>
      </div>

      {cart.items.map((item) => (
        <div key={`${item.category}-${item.id}`} className="qc-item">
          <div className="qc-item__img">
            <OptimizedImage
              alt={item.name}
              src={item.image}
              width={72}
              height={72}
              sizes="72px"
            />
          </div>
          <div className="qc-item__body">
            <h3 className="qc-item__name">{item.name}</h3>
            <div className="qc-item__row">
              <span className="qc-item__meta">Qty {item.qty}</span>
              <span className="qc-price">{formatInr(item.price * item.qty)}</span>
            </div>
          </div>
        </div>
      ))}

      <div className="qc-divider" />

      <div className="qc-bill">
        <div className="qc-bill__row">
          <span>Item total</span>
          <span>{formatInr(subtotal)}</span>
        </div>
        <div className="qc-bill__row">
          <span>Delivery {shippingReady ? `(${distanceText})` : ''}</span>
          <span>{shippingReady ? formatInr(shippingFee) : 'After address'}</span>
        </div>
        {midnightFee > 0 && (
          <div className="qc-bill__row qc-bill__row--warn">
            <span>Midnight surcharge (1.5× items)</span>
            <span>{formatInr(midnightFee)}</span>
          </div>
        )}
        {discount > 0 && (
          <div className="qc-bill__row qc-bill__row--discount">
            <span>Discount</span>
            <span>- {formatInr(discount)}</span>
          </div>
        )}
        <div className="qc-bill__total">
          <span>{step === 'payment' ? 'Total payable' : 'Estimated total'}</span>
          <strong>{formatInr(grandTotal)}</strong>
        </div>
      </div>

      {shippingMsg && (
        <div
          className={`qc-alert ${
            shippingMsg.type === 'success'
              ? 'qc-alert--ok'
              : shippingMsg.type === 'error'
                ? 'qc-alert--err'
                : 'qc-alert--warn'
          }`}
          style={{ marginTop: '0.85rem' }}
        >
          {shippingMsg.text}
        </div>
      )}

      <p className="qc-muted" style={{ marginTop: '0.85rem', lineHeight: 1.45 }}>
        Dispatching from Sai Flower · {SHIPPING.storeAddress}
      </p>
      <Link href="/cart" className="qc-link-btn" style={{ marginTop: '0.65rem', display: 'inline-flex' }}>
        ← Back to cart
      </Link>
    </div>
  );

  return (
    <main className="qc-shell">
      <CheckoutProgress current={step === 'payment' ? 'payment' : 'address'} />

      <div className="qc-title-row">
        <div>
          <h1 className="qc-title">{step === 'payment' ? 'Review & pay' : 'Delivery details'}</h1>
          <p className="qc-subtitle">
            {step === 'payment'
              ? 'Confirm your address, schedule and WhatsApp payment.'
              : isGuest
                ? 'Guest checkout — enter delivery details, no account needed.'
                : 'Add recipient details, save your address, then continue.'}
          </p>
        </div>
      </div>

      <div className="qc-layout">
        <div className="qc-stack">
          {step === 'address' && (
            <form id="checkout-address-form" onSubmit={handleSaveAndContinue} className="qc-stack">
              {addresses.length > 0 && !isGuest && (
                <div className="qc-card">
                  <div className="qc-card__head">
                    <h2 className="qc-card__title">
                      <span className="material-icons-outlined">bookmark</span>
                      Saved addresses
                    </h2>
                    <button type="button" className="qc-link-btn" onClick={startNewAddress}>
                      + Add new
                    </button>
                  </div>
                  <div className="qc-stack">
                    {addresses.map((address) => (
                      <button
                        key={address.id}
                        type="button"
                        className={`qc-address${
                          selectedAddressId === address.id && !showNewAddressForm ? ' is-active' : ''
                        }`}
                        onClick={() => void handleSelectSavedAddress(address)}
                      >
                        <div className="qc-address__top">
                          <strong>{address.recipientName}</strong>
                          <span className="qc-badge qc-badge--green">{address.addressType}</span>
                        </div>
                        <p className="qc-muted" style={{ margin: '0.35rem 0 0' }}>
                          {address.flatHouseNo}, {address.apartmentStreetLocality}
                        </p>
                        <p className="qc-muted" style={{ margin: '0.2rem 0 0' }}>
                          {address.mobile}
                          {address.email ? ` · ${address.email}` : ''} · PIN {address.pincode}
                        </p>
                      </button>
                    ))}
                  </div>
                </div>
              )}

              {showAddressForm ? addressForm : null}

              <label className="qc-check qc-check--deliver-now">
                <input
                  type="checkbox"
                  checked={deliverNow}
                  onChange={(e) => handleDeliverNowChange(e.target.checked)}
                />
                <span>
                  <strong>Deliver Now</strong>
                  <span className="qc-check__hint">
                    Skip scheduling — we&apos;ll prepare your order for the earliest possible delivery.
                    {isMidnightNow() ? (
                      <>
                        {' '}
                        <strong style={{ color: '#8a6110' }}>
                          Midnight surcharge (1.5× items) applies for 11 PM – 7 AM IST.
                        </strong>
                      </>
                    ) : null}
                  </span>
                </span>
              </label>

              <div className={`qc-card qc-schedule${deliverNow ? ' is-disabled' : ''}`}>
                <div className="qc-card__head">
                  <h2 className="qc-card__title">
                    <span className="material-icons-outlined">schedule</span>
                    Delivery schedule
                  </h2>
                  <span className="qc-muted" style={{ fontSize: '0.78rem' }}>
                    24-hour delivery · 1-hour slots (IST)
                  </span>
                </div>
                <fieldset className="qc-schedule__fields" disabled={deliverNow}>
                  <div className="qc-grid qc-grid--2">
                    <div className="qc-field">
                      <label className="qc-label">Preferred date</label>
                      <input
                        className="qc-input"
                        type="date"
                        min={todayDateString()}
                        value={delDate}
                        onChange={(e) => handleDeliveryDateChange(e.target.value)}
                        required={!deliverNow}
                      />
                    </div>
                    <div className="qc-field">
                      <label className="qc-label">Time slot</label>
                      <div className="qc-slot-grid">
                        {HOURLY_TIME_SLOTS.map((slot) => {
                          const slotPast = isHourlySlotPastForDate(slot.hour, delDate);
                          return (
                            <button
                              key={slot.value}
                              type="button"
                              disabled={slotPast}
                              className={`qc-slot${
                                slotMode === 'hourly' && delTime === slot.value ? ' is-active' : ''
                              }${slotPast ? ' is-disabled' : ''}${
                                slot.hour >= 23 || slot.hour < 7 ? ' is-midnight' : ''
                              }`}
                              onClick={() => {
                                if (slotPast) return;
                                setSlotMode('hourly');
                                setCustomTime('');
                                setDelTime(slot.value);
                              }}
                            >
                              <strong>{slot.title}</strong>
                              <span>
                                {slotPast ? 'Unavailable' : slot.hint}
                                {!slotPast && (slot.hour >= 23 || slot.hour < 7)
                                  ? ' · 1.5× items'
                                  : ''}
                              </span>
                            </button>
                          );
                        })}
                        <button
                          type="button"
                          className={`qc-slot qc-slot--custom${
                            slotMode === 'custom' ? ' is-active' : ''
                          }`}
                          onClick={() => setSlotMode('custom')}
                        >
                          <strong>Custom time</strong>
                          <span>Enter your preferred slot</span>
                        </button>
                      </div>
                      {slotMode === 'custom' ? (
                        <>
                          <input
                            className="qc-input"
                            type="text"
                            value={customTime}
                            onChange={(e) => setCustomTime(e.target.value)}
                            placeholder="e.g. Tomorrow 2:30 AM, after 11 PM"
                            required={!deliverNow}
                            style={{ marginTop: '0.65rem' }}
                          />
                          {customTime.trim() && midnightApplies && !deliverNow ? (
                            <p className="qc-muted" style={{ margin: '0.5rem 0 0', color: '#8a6110' }}>
                              Midnight window detected — 1.5× item surcharge applies.
                            </p>
                          ) : null}
                        </>
                      ) : null}
                    </div>
                  </div>
                </fieldset>
              </div>

              {formError && <div className="qc-alert qc-alert--err">{formError}</div>}

              <button type="submit" className="qc-cta qc-cta--desktop-only" disabled={savingAddress}>
                {savingAddress ? 'Saving…' : isGuest ? 'Continue' : 'Save & Continue'}
                <span className="material-icons-outlined" style={{ fontSize: '1.1rem' }}>
                  arrow_forward
                </span>
              </button>
            </form>
          )}

          {step === 'payment' && paymentAddress && (
            <div className="qc-stack">
              <div className="qc-card">
                <div className="qc-card__head">
                  <h2 className="qc-card__title">
                    <span className="material-icons-outlined">local_shipping</span>
                    Delivering to
                  </h2>
                  <button type="button" className="qc-link-btn" onClick={() => setStep('address')}>
                    Edit
                  </button>
                </div>
                <div style={{ display: 'flex', justifyContent: 'space-between', gap: '0.75rem' }}>
                  <div>
                    <strong>{paymentAddress.recipientName}</strong>
                    <p className="qc-muted" style={{ margin: '0.3rem 0 0' }}>
                      {paymentAddress.mobile}
                      {paymentAddress.email ? ` · ${paymentAddress.email}` : ''}
                    </p>
                    <p className="qc-muted" style={{ margin: '0.35rem 0 0', lineHeight: 1.45 }}>
                      {paymentAddress.flatHouseNo}, {paymentAddress.apartmentStreetLocality}
                      <br />
                      PIN {paymentAddress.pincode} · {paymentAddress.addressType}
                    </p>
                  </div>
                  <span className="qc-badge qc-badge--green">{paymentAddress.addressType}</span>
                </div>
                <div className="qc-divider" />
                <p className="qc-muted" style={{ margin: 0 }}>
                  {deliverNow ? (
                    <strong style={{ color: '#14261c' }}>{resolvedDelivery.deliveryTime}</strong>
                  ) : (
                    <>
                      <strong style={{ color: '#14261c' }}>{resolvedDelivery.date}</strong>
                      {' · '}
                      {resolvedDelivery.deliveryTime}
                    </>
                  )}
                </p>
              </div>

              <div className="qc-card">
                <div className="qc-card__head">
                  <h2 className="qc-card__title">
                    <span className="material-icons-outlined">payments</span>
                    Payment option
                  </h2>
                </div>
                <div className="qc-payment">
                  <div className="qc-payment__icon">
                    <i className="fab fa-whatsapp" />
                  </div>
                  <div>
                    <strong style={{ display: 'block', color: '#0f5132' }}>Buy on WhatsApp</strong>
                    <p className="qc-muted" style={{ margin: '0.35rem 0 0', color: '#157a4b' }}>
                      We&apos;ll create your order and open WhatsApp so you can confirm payment with
                      Sai Flower.
                    </p>
                  </div>
                </div>
                <button
                  type="button"
                  className="qc-cta qc-cta--wa qc-cta--desktop-only"
                  style={{ marginTop: '1rem' }}
                  disabled={submitting}
                  onClick={() => void handleWhatsAppOrder()}
                >
                  <i className="fab fa-whatsapp" style={{ fontSize: '1.25rem' }} />
                  {submitting ? 'Placing order…' : 'Buy on WhatsApp'}
                </button>
              </div>
            </div>
          )}
        </div>

        <aside className="qc-sticky-summary">{summaryCard}</aside>
      </div>

      <div className="qc-mobile-bar">
        <div className="qc-mobile-bar__inner">
          <div className="qc-mobile-bar__meta">
            <small>{step === 'payment' ? 'Total payable' : 'Estimated total'}</small>
            <strong>{formatInr(grandTotal)}</strong>
          </div>
          {step === 'address' ? (
            <button
              type="submit"
              form="checkout-address-form"
              className="qc-cta"
              disabled={savingAddress}
            >
              {savingAddress ? 'Saving…' : 'Continue'}
            </button>
          ) : (
            <button
              type="button"
              className="qc-cta qc-cta--wa"
              disabled={submitting}
              onClick={() => void handleWhatsAppOrder()}
            >
              {submitting ? 'Placing…' : 'WhatsApp'}
            </button>
          )}
        </div>
      </div>
    </main>
  );
}
