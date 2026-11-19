<script setup lang="ts">
import { computed } from 'vue';

import type { TocItem } from '@/composables/useTableOfContents';

const props = defineProps<{
  items: TocItem[];
  activeId: string | null;
}>();

const emit = defineEmits<{
  navigate: [id: string];
}>();

// The h2 section the reader is in; only its h3 entries are listed, which keeps
// the outline short.
const activeSection = computed(() => {
  const active = props.items.find((item) => item.id === props.activeId);

  return active?.level === 2 ? active.id : (active?.parent ?? null);
});

const visibleItems = computed(() =>
  props.items.filter((item) => item.level === 2 || item.parent === activeSection.value),
);
</script>

<template>
  <nav aria-label="On this page">
    <p class="text-foreground mb-3 text-sm font-semibold uppercase">On this page</p>

    <ul class="border-border space-y-0.5 border-l text-sm">
      <li v-for="item in visibleItems" :key="item.id">
        <a
          :href="`#${item.id}`"
          :aria-current="item.id === activeId ? 'location' : undefined"
          :class="[
            '-ml-px block border-l py-1 transition-colors',
            item.level === 3 ? 'pl-7 text-xs' : 'pl-4',
            item.id === activeId
              ? 'border-primary text-foreground font-medium'
              : 'text-muted-foreground hover:text-foreground border-transparent',
          ]"
          @click.prevent="emit('navigate', item.id)"
        >
          {{ item.text }}
        </a>
      </li>
    </ul>
  </nav>
</template>
