<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
</script>

<template>
  <div class="flex items-center gap-2.5">
    <div
      class="flex aspect-square size-8 items-center justify-center rounded-lg bg-indigo-600 text-white shadow-sm shadow-indigo-500/30 transition-transform group-hover:scale-105 dark:bg-indigo-500"
    >
      <AppLogoIcon class="size-5" />
    </div>

    <div class="grid flex-1 text-left text-sm leading-tight">
      <span class="truncate font-bold tracking-tight text-slate-900 dark:text-slate-100">
        GitHooks <span class="text-indigo-600 dark:text-indigo-400">Hub</span>
      </span>
      <span class="truncate text-[10px] font-medium text-slate-500 dark:text-slate-400">
        Hook Management
      </span>
    </div>
  </div>
</template>
