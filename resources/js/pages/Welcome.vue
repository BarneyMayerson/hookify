<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { dashboard, login } from '@/routes';
import { useAppearance } from '@/composables/useAppearance';

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
    class="flex min-h-screen flex-col justify-between bg-slate-50 text-slate-800 transition-colors duration-200 selection:bg-indigo-500 selection:text-white dark:bg-slate-950 dark:text-slate-100"
  >
    <!-- Header -->
    <header class="mx-auto flex w-full max-w-7xl items-center justify-between px-6 py-6">
      <div
        class="flex items-center gap-2 text-xl font-bold tracking-tight text-slate-900 dark:text-white"
      >
        <div
          class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-600 font-mono text-sm text-white"
        >
          GH
        </div>
        <span>GitHooks Hub</span>
      </div>

      <nav class="flex items-center gap-4">
        <!-- Theme Toggle Button -->
        <button
          @click="toggleTheme"
          type="button"
          class="rounded-lg p-2 text-slate-500 transition-colors hover:bg-slate-200/60 hover:text-slate-700 dark:text-slate-400 dark:hover:bg-slate-800/60 dark:hover:text-white"
          title="Toggle Theme"
        >
          <!-- Sun Icon (Dark mode active) -->
          <svg
            v-if="resolvedAppearance === 'dark'"
            class="h-5 w-5"
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
          <svg v-else class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"
            />
          </svg>
        </button>

        <Link
          v-if="$page.props.auth.user"
          :href="dashboard().url"
          class="text-sm font-medium text-slate-600 transition-colors hover:text-slate-900 dark:text-slate-300 dark:hover:text-white"
        >
          Dashboard &rarr;
        </Link>
        <template v-else>
          <Link
            :href="login().url"
            class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-indigo-500"
          >
            Log in
          </Link>
        </template>
      </nav>
    </header>

    <!-- Hero Section -->
    <main class="mx-auto flex max-w-4xl flex-col items-center px-6 py-20 text-center">
      <div
        class="mb-8 inline-flex items-center gap-2 rounded-full border border-indigo-200 bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-600 dark:border-indigo-800/50 dark:bg-indigo-950/80 dark:text-indigo-300"
      >
        <span
          class="flex h-2 w-2 animate-pulse rounded-full bg-indigo-500 dark:bg-indigo-400"
        ></span>
        Open Source SaaS
      </div>

      <h1
        class="mb-6 text-4xl leading-[1.15] font-extrabold tracking-tight text-slate-900 sm:text-6xl dark:text-slate-50"
      >
        Centralize & Sync Your <br class="hidden sm:inline" />
        <span
          class="bg-linear-to-r from-indigo-600 via-purple-600 to-pink-600 bg-clip-text text-transparent dark:from-indigo-400 dark:via-purple-400 dark:to-pink-400"
        >
          Git Hooks Across Teams
        </span>
      </h1>

      <p class="mb-10 max-w-2xl text-lg leading-relaxed text-slate-600 dark:text-slate-400">
        Manage, enforce, and distribute repository hooks seamlessly. Keep code quality standards
        consistent without manual developer setup.
      </p>

      <div class="flex w-full flex-col gap-4 sm:w-auto sm:flex-row">
        <Link
          v-if="$page.props.auth.user"
          :href="dashboard().url"
          class="rounded-lg bg-indigo-600 px-6 py-3 text-center font-semibold text-white shadow-lg shadow-indigo-600/20 transition-colors hover:bg-indigo-500"
        >
          Go to Dashboard
        </Link>
        <Link
          v-else
          :href="login().url"
          class="rounded-lg bg-indigo-600 px-6 py-3 text-center font-semibold text-white shadow-lg shadow-indigo-600/20 transition-colors hover:bg-indigo-500"
        >
          Get Started &rarr;
        </Link>
      </div>
    </main>

    <!-- Footer -->
    <footer
      class="mx-auto w-full max-w-7xl border-t border-slate-200 px-6 py-8 text-center text-xs text-slate-500 dark:border-slate-900 dark:text-slate-500"
    >
      &copy; {{ new Date().getFullYear() }} GitHooks Hub. Built with Laravel & Vue.
    </footer>
  </div>
</template>
