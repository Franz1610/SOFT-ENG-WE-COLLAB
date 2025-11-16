// Central promo configuration and helpers used by booking flow
// Categories: 'individual' (Phone booth rooms), 'common' (Regular tables), 'master' (Conference rooms)

export type CategoryKey = 'individual' | 'common' | 'master';

// Fixed promo duration lists per category (in hours)
// For conference rooms, pricing is per-hour so duration is unrestricted other than schedule limits
export const promoDurations: Record<CategoryKey, number[] | 'per-hour'> = {
  individual: [1, 2, 3, 4], // Phone booth rooms
  common: [1, 3, 6, 8],     // Regular tables
  master: 'per-hour',       // Conference rooms (200/300 per hour)
};

// Price tables based on the promo poster
const priceTableIndividual: Record<number, number> = {
  1: 70,
  2: 120,
  3: 150,
  4: 200,
};
const priceTableCommon: Record<number, number> = {
  1: 39,
  3: 99,
  6: 195,
  8: 245,
};

export function getDurationOptions(
  category: string,
  maxHours: number,
): number[] {
  const key = (category || '').toLowerCase() as CategoryKey;
  const base = promoDurations[key];
  const cap = Math.max(1, Math.min(8, maxHours)); // enforce a sane UI cap
  if (base === 'per-hour') {
    return Array.from({ length: cap }, (_, i) => i + 1);
  }
  return (base || [1]).filter((h) => h <= cap);
}

export function computePrice(
  category: string,
  durationHours: number,
  pax = 1,
): number | null {
  const key = (category || '').toLowerCase() as CategoryKey;
  if (key === 'individual') {
    return priceTableIndividual[durationHours] ?? null;
  }
  if (key === 'common') {
    return priceTableCommon[durationHours] ?? null;
  }
  if (key === 'master') {
    const perHour = pax <= 6 ? 200 : 300;
    return perHour * durationHours;
  }
  return null;
}

export function formatPHP(n: number | null): string {
  if (n == null || isNaN(n as any)) return '';
  return new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(n);
}
