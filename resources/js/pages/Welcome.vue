<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

import { Button } from '@/components/ui/button';
import { useAppearance } from '@/composables/useAppearance';
import { dashboard, login } from '@/routes';

defineProps<{
  canLogin?: boolean;
}>();

const { resolvedAppearance, updateAppearance } = useAppearance();

const toggleTheme = () => {
  updateAppearance(resolvedAppearance.value === 'dark' ? 'light' : 'dark');
};
</script>

<template>
  <Head title="GitHooks Hub — Centralized Git Hook Management" />

  <div
    class="bg-background text-foreground selection:bg-primary selection:text-primary-foreground flex min-h-screen flex-col justify-between transition-colors duration-200"
  >
    <!-- Header -->
    <header class="mx-auto flex w-full max-w-7xl items-center justify-between px-6 py-6">
      <div class="flex items-center gap-2 text-xl font-bold tracking-tight">
        <div
          class="bg-primary text-primary-foreground flex h-8 w-8 items-center justify-center rounded-lg font-mono text-sm"
        >
          GH
        </div>
        <span>GitHooks Hub</span>
      </div>

      <nav class="flex items-center gap-4">
        <!-- Theme Toggle Button -->
        <Button
          variant="ghost"
          size="icon"
          @click="toggleTheme"
          type="button"
          title="Toggle Theme"
          aria-label="Toggle Theme"
        >
          <!-- Sun Icon (Dark mode active) -->
          <svg
            v-if="resolvedAppearance === 'dark'"
            class="size-5"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"
            />
          </svg>
          <!-- Moon Icon (Light mode active) -->
          <svg v-else class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"
            />
          </svg>
        </Button>

        <Link
          v-if="$page.props.auth.user"
          :href="dashboard().url"
          class="text-muted-foreground hover:text-foreground text-sm font-medium transition-colors"
        >
          Dashboard &rarr;
        </Link>
        <template v-else>
          <Button as-child>
            <Link :href="login().url">Log in</Link>
          </Button>
        </template>
      </nav>
    </header>

    <!-- Hero Section -->
    <main class="mx-auto flex max-w-4xl flex-col items-center px-6 py-20 text-center">
      <div
        class="border-border bg-muted text-muted-foreground mb-8 inline-flex items-center gap-2 rounded-full border px-3 py-1 text-xs font-medium"
      >
        <span class="bg-primary flex h-2 w-2 animate-pulse rounded-full"></span>
        Open Source SaaS
      </div>

      <h1 class="mb-6 text-4xl leading-[1.15] font-extrabold tracking-tight sm:text-6xl">
        Centralize & Sync Your <br class="hidden sm:inline" />
        <span
          class="bg-linear-to-r from-indigo-500 via-violet-500 to-purple-500 bg-clip-text text-transparent dark:from-indigo-400 dark:via-violet-400 dark:to-purple-400"
        >
          Git Hooks Across Teams
        </span>
      </h1>

      <p class="text-muted-foreground mb-10 max-w-2xl text-lg leading-relaxed">
        Manage, enforce, and distribute repository hooks seamlessly. Keep code quality standards
        consistent without manual developer setup.
      </p>

      <div class="flex w-full flex-col gap-4 sm:w-auto sm:flex-row">
        <Button as-child size="lg" class="shadow-lg">
          <Link v-if="$page.props.auth.user" :href="dashboard().url"> Go to Dashboard </Link>
          <Link v-else :href="login().url"> Get Started &rarr; </Link>
        </Button>
      </div>
    </main>

    <!-- Footer -->
    <footer
      class="border-border text-muted-foreground mx-auto w-full max-w-7xl border-t px-6 py-8 text-center text-xs"
    >
      &copy; {{ new Date().getFullYear() }} GitHooks Hub. Built with Laravel & Vue.
    </footer>
  </div>
</template>
