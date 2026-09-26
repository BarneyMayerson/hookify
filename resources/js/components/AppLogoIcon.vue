<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
  inheritAttrs: false,
});

type Props = {
  className?: HTMLAttributes['class'];
};

defineProps<Props>();
</script>

<template>
  <svg
    xmlns="http://www.w3.org/2000/svg"
    viewBox="0 0 24 24"
    fill="none"
    stroke="currentColor"
    stroke-width="2"
    stroke-linecap="round"
    stroke-linejoin="round"
    :class="className"
    v-bind="$attrs"
  >
    <path d="M6 3v18" />
    <circle cx="6" cy="6" r="2" fill="currentColor" />
    <circle cx="6" cy="18" r="2" fill="currentColor" />

    <path d="M6 12h4a4 4 0 0 0 4-4V7a2 2 0 1 1 4 0v5a6 6 0 0 1-6 6h-2" />
    <circle cx="18" cy="7" r="1.5" fill="currentColor" />
  </svg>
</template>
