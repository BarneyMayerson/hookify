import type { ComputedRef, MaybeRefOrGetter } from 'vue';
import { computed, toValue } from 'vue';

export function useRelativeDate(dateValue: MaybeRefOrGetter<string | null>): ComputedRef<string> {
  return computed(() => {
    const value = toValue(dateValue);

    if (!value) return 'Never synced';

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) return value;

    const differenceInSeconds = (date.getTime() - Date.now()) / 1000;
    const absoluteDifference = Math.abs(differenceInSeconds);
    const units = [
      { unit: 'year', seconds: 31_536_000 },
      { unit: 'month', seconds: 2_592_000 },
      { unit: 'day', seconds: 86_400 },
      { unit: 'hour', seconds: 3_600 },
      { unit: 'minute', seconds: 60 },
      { unit: 'second', seconds: 1 },
    ] as const;
    const { unit, seconds } = units.find(({ seconds }) => absoluteDifference >= seconds) ?? {
      unit: 'second',
      seconds: 1,
    };
    const relativeTime = new Intl.RelativeTimeFormat(undefined, { numeric: 'auto' }).format(
      Math.round(differenceInSeconds / seconds),
      unit,
    );
    const formattedDate = new Intl.DateTimeFormat(undefined, {
      dateStyle: 'medium',
      timeStyle: 'short',
    }).format(date);

    return `${relativeTime} (${formattedDate})`;
  });
}
