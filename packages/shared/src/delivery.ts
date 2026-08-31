/** Label stored on orders when customer wants immediate delivery. */
export const DELIVER_NOW_LABEL = 'Deliver Now (ASAP)';

export type DeliverySlotOption = {
  value: string;
  title: string;
  hint: string;
};

function formatHour12(hour24: number): string {
  const h = hour24 % 24;
  const period = h < 12 ? 'AM' : 'PM';
  const hour12 = h % 12 === 0 ? 12 : h % 12;
  return `${hour12}:00 ${period}`;
}

/** 24 hourly slots — e.g. "9:00 AM - 10:00 AM". */
export function buildHourlyDeliverySlots(): DeliverySlotOption[] {
  const slots: DeliverySlotOption[] = [];
  for (let hour = 0; hour < 24; hour += 1) {
    const start = formatHour12(hour);
    const end = formatHour12((hour + 1) % 24);
    const value = `${start} - ${end}`;
    slots.push({ value, title: start, hint: `Until ${end}` });
  }
  return slots;
}

export function todayDateString(now = new Date()): string {
  const y = now.getFullYear();
  const m = String(now.getMonth() + 1).padStart(2, '0');
  const d = String(now.getDate()).padStart(2, '0');
  return `${y}-${m}-${d}`;
}

/** Default slot: next full hour today, or 9:00 AM for future dates. */
export function defaultDeliverySlotForDate(dateStr: string, now = new Date()): string {
  const slots = buildHourlyDeliverySlots();
  if (!slots.length) return '';

  const today = todayDateString(now);
  if (dateStr !== today) {
    return slots[9]?.value ?? slots[0].value;
  }

  const nextHour = Math.min(now.getHours() + 1, 23);
  return slots[nextHour]?.value ?? slots[slots.length - 1].value;
}

export function resolveCheckoutDelivery(input: {
  deliverNow: boolean;
  date: string;
  hourlySlot: string;
  slotMode: 'hourly' | 'custom';
  customTime: string;
  now?: Date;
}): { date: string; deliveryTime: string } {
  const now = input.now ?? new Date();
  const today = todayDateString(now);

  if (input.deliverNow) {
    return { date: today, deliveryTime: DELIVER_NOW_LABEL };
  }

  if (input.slotMode === 'custom') {
    const note = input.customTime.trim();
    return {
      date: input.date || today,
      deliveryTime: note ? `Custom: ${note}` : '',
    };
  }

  return {
    date: input.date || today,
    deliveryTime: input.hourlySlot,
  };
}
