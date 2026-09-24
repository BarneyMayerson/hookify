<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

defineOptions({
  layout: {
    breadcrumbs: [
      {
        title: 'Security settings',
      },
    ],
  },
});
</script>

<template>
  <Head title="Security settings" />

  <h1 class="sr-only">Security settings</h1>
</template>
