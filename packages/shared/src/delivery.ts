/** Label stored on orders when customer wants immediate delivery. */
export const DELIVER_NOW_LABEL = 'Deliver Now (ASAP)';

/** Night window for 1.5× item pricing: 11:00 PM – 6:59 AM IST (slot starts 23,0..6). */
export const MIDNIGHT_WINDOW_START_HOUR = 23;
export const MIDNIGHT_WINDOW_END_HOUR = 7;
export const MIDNIGHT_ITEM_MULTIPLIER = 1.5;

export const IST_TIMEZONE = 'Asia/Kolkata';

export type DeliverySlotOption = {
  value: string;
  title: string;
  hint: string;
  /** Slot start hour in 24h IST (0–23). */
  hour: number;
};

type ISTParts = {
  year: number;
  month: number;
  day: number;
  hour: number;
  minute: number;
};

export function getISTParts(now = new Date()): ISTParts {
  const fmt = new Intl.DateTimeFormat('en-IN', {
    timeZone: IST_TIMEZONE,
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
    hourCycle: 'h23',
  });
  const parts = fmt.formatToParts(now);
  const get = (type: Intl.DateTimeFormatPartTypes) =>
    Number(parts.find((p) => p.type === type)?.value ?? 0);
  return {
    year: get('year'),
    month: get('month'),
    day: get('day'),
    hour: get('hour'),
    minute: get('minute'),
  };
}

export function todayDateString(now = new Date()): string {
  const { year, month, day } = getISTParts(now);
  return `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
}

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
    slots.push({ value, title: start, hint: `Until ${end}`, hour });
  }
  return slots;
}

/** Hour 23 or 0–6 → midnight delivery window. */
export function isMidnightDeliveryHour(hour: number): boolean {
  return hour >= MIDNIGHT_WINDOW_START_HOUR || hour < MIDNIGHT_WINDOW_END_HOUR;
}

/** True when current IST time is inside the midnight delivery window. */
export function isMidnightNow(now = new Date()): boolean {
  return isMidnightDeliveryHour(getISTParts(now).hour);
}

/** Slot unavailable when its end hour has passed (IST) on today's date. */
export function isHourlySlotPastForDate(
  slotHour: number,
  dateStr: string,
  now = new Date(),
): boolean {
  const today = todayDateString(now);
  if (dateStr !== today) return false;
  const { hour: currentHour } = getISTParts(now);
  return slotHour + 1 <= currentHour;
}

export function parseHourlySlotStartHour(slotValue: string): number | null {
  const match = slotValue.trim().match(/^(\d{1,2}):00\s*(AM|PM)/i);
  if (!match) return null;
  let hour = Number(match[1]);
  const period = match[2].toUpperCase();
  if (period === 'AM') {
    if (hour === 12) hour = 0;
  } else if (hour !== 12) {
    hour += 12;
  }
  return hour;
}

export function isHourlySlotMidnight(slotValue: string): boolean {
  const hour = parseHourlySlotStartHour(slotValue);
  return hour != null && isMidnightDeliveryHour(hour);
}

/** Best-effort parse for custom time text — only used for midnight surcharge. */
export function customTimeLooksMidnight(custom: string): boolean {
  const text = custom.trim().toLowerCase();
  if (!text) return false;
  if (/\bmidnight\b|\blate\s*night\b/.test(text)) return true;

  const matches = text.matchAll(/(\d{1,2})(?::(\d{2}))?\s*(am|pm)\b/gi);
  for (const match of matches) {
    let hour = Number(match[1]);
    const period = match[3].toLowerCase();
    if (period === 'am') {
      if (hour === 12) hour = 0;
    } else if (hour !== 12) {
      hour += 12;
    }
    if (isMidnightDeliveryHour(hour)) return true;
  }
  return false;
}

export function firstAvailableHourlySlotForDate(
  dateStr: string,
  slots = buildHourlyDeliverySlots(),
  now = new Date(),
): string {
  const available = slots.find((slot) => !isHourlySlotPastForDate(slot.hour, dateStr, now));
  if (available) return available.value;
  return slots[9]?.value ?? slots[0]?.value ?? '';
}

/** Default slot: next available hour today (IST), or 9:00 AM for future dates. */
export function defaultDeliverySlotForDate(dateStr: string, now = new Date()): string {
  const slots = buildHourlyDeliverySlots();
  if (!slots.length) return '';

  const today = todayDateString(now);
  if (dateStr !== today) {
    return slots[9]?.value ?? slots[0].value;
  }

  return firstAvailableHourlySlotForDate(dateStr, slots, now);
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

export function shouldApplyMidnightCharge(input: {
  deliverNow: boolean;
  date: string;
  deliveryTime: string;
  slotMode: 'hourly' | 'custom';
  customTime: string;
  now?: Date;
}): boolean {
  const now = input.now ?? new Date();

  if (input.deliverNow) {
    return isMidnightNow(now);
  }

  if (input.slotMode === 'custom') {
    return customTimeLooksMidnight(input.customTime);
  }

  if (input.deliveryTime && isHourlySlotMidnight(input.deliveryTime)) {
    return true;
  }

  return false;
}

/** Additional surcharge so items total becomes 1.5× (adds 50% of subtotal). */
export function calcMidnightSurcharge(subtotal: number): number {
  if (subtotal <= 0) return 0;
  return Math.round(subtotal * (MIDNIGHT_ITEM_MULTIPLIER - 1));
}
