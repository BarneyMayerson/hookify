<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

import { Button } from '@/components/ui/button';
import { dashboard, login } from '@/routes';
</script>

<template>
  <Head title="GitHooks Hub — Centralized Git Hook Management" />

  <div
    class="bg-background text-foreground selection:bg-primary selection:text-primary-foreground flex min-h-screen flex-col justify-between transition-colors duration-200"
  >
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
  </div>
</template>
