<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

import Breadcrumb from '@/components/Breadcrumbs.vue';
import { Button } from '@/components/ui/button';
import { useAppearance } from '@/composables/useAppearance';
import { dashboard, login } from '@/routes';
import type { BreadcrumbItem } from '@/types';

const { resolvedAppearance, updateAppearance } = useAppearance();

withDefaults(
  defineProps<{
    breadcrumbs?: BreadcrumbItem[];
  }>(),
  {
    breadcrumbs: () => [],
  },
);

const toggleTheme = () => {
  updateAppearance(resolvedAppearance.value === 'dark' ? 'light' : 'dark');
};
</script>

<template>
  <div
    class="bg-background text-foreground selection:bg-primary selection:text-primary-foreground flex min-h-screen flex-col justify-between transition-colors duration-200"
  >
    <!-- Header -->
    <header class="mx-auto flex w-full max-w-7xl items-center justify-between px-6 py-6">
      <div class="flex items-center gap-8">
        <Link :href="'/'" class="flex items-center gap-2 text-xl font-bold tracking-tight">
          <div
            class="bg-primary text-primary-foreground flex h-8 w-8 items-center justify-center rounded-lg font-mono text-sm"
          >
            GH
          </div>
          <span>GitHooks Hub</span>
        </Link>

        <nav class="hidden items-center gap-6 sm:flex">
          <Link
            href="/docs"
            class="hover:text-foreground text-sm font-medium transition-colors"
            :class="
              $page.url.startsWith('/docs')
                ? 'text-foreground underline underline-offset-4'
                : 'text-muted-foreground'
            "
          >
            Docs
          </Link>
        </nav>
      </div>

      <nav class="flex items-center gap-4">
        <Button
          variant="ghost"
          size="icon"
          @click="toggleTheme"
          type="button"
          title="Toggle Theme"
          aria-label="Toggle Theme"
        >
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
    <template v-if="breadcrumbs && breadcrumbs.length > 0">
      <div class="mx-auto w-full max-w-5xl px-6 py-3">
        <Breadcrumb :breadcrumbs />
      </div>
    </template>

    <!-- Page content -->
    <main class="flex-1">
      <slot />
    </main>

    <!-- Footer -->
    <footer
      class="border-border text-muted-foreground mx-auto w-full max-w-7xl border-t px-6 py-8 text-center text-xs"
    >
      &copy; {{ new Date().getFullYear() }} GitHooks Hub. Built with Laravel & Vue.
    </footer>
  </div>
</template>
