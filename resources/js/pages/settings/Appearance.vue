<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppearanceTabs from '@/components/AppearanceTabs.vue';
import Heading from '@/components/Heading.vue';

defineOptions({
  layout: {
    breadcrumbs: [
      {
        title: 'Appearance settings',
      },
    ],
  },
});
</script>

<template>
  <Head title="Appearance settings" />

  <div class="mx-auto max-w-4xl space-y-8 p-4 sm:p-6">
    <!-- Header -->
    <div class="border-b border-slate-200 pb-5 dark:border-slate-800">
      <Heading
        title="Appearance Settings"
        description="Customize the appearance settings for your account and workspace interface."
      />
    </div>

    <!-- Appearance Options Card -->
    <div
      class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
    >
      <AppearanceTabs />
    </div>
  </div>
</template>
