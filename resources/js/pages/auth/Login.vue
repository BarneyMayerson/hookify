<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { GitBranch, ListChecks, RefreshCw } from '@lucide/vue';

import GitHubIcon from '@/components/GitHubIcon.vue';
import GitHubNotice from '@/components/GitHubNotice.vue';
import { Button } from '@/components/ui/button';
import { redirect } from '@/routes/auth/github';

defineProps<{
  status?: string;
}>();

const points = [
  {
    icon: ListChecks,
    title: 'Pick checks, not scripts',
    text: 'Choose from ready-made checks: Pint, PHPStan, oxlint, oxfmt, ESLint, Prettier, gitleaks and commitlint.',
  },
  {
    icon: RefreshCw,
    title: 'One command keeps everyone in sync',
    text: 'Run the sync command in a repository and its hooks match your project settings.',
    code: 'npx hookify-cli sync',
  },
  {
    icon: GitBranch,
    title: 'Open source',
    text: 'Read the code, or run your own instance.',
  },
];
</script>

<template>
  <Head title="Log in" />

  <div
    class="mx-auto grid max-w-5xl items-start gap-10 px-6 pt-8 pb-16 sm:pt-14 lg:grid-cols-[1fr_24rem] lg:gap-16"
  >
    <!-- Sign-in card: first on small screens, on the right on large ones -->
    <section class="order-1 lg:order-2" aria-labelledby="login-title">
      <div class="bg-card border-border rounded-2xl border p-6 shadow-sm sm:p-8">
        <h1 id="login-title" class="text-2xl font-bold tracking-tight">Sign in to Hookify</h1>
        <p class="text-muted-foreground mt-1 text-sm">
          Use your GitHub account. There are no passwords to manage.
        </p>

        <div class="mt-6 space-y-4">
          <p v-if="status" class="text-sm font-medium text-green-600">{{ status }}</p>

          <GitHubNotice />

          <Button as-child size="lg" class="w-full" data-test="github-login-button">
            <a :href="redirect().url">
              <GitHubIcon class="mr-2 size-4" />
              Continue with GitHub
            </a>
          </Button>
        </div>

        <div class="border-border mt-6 border-t pt-5">
          <h2 class="text-sm font-medium">What Hookify asks GitHub for</h2>
          <p class="text-muted-foreground mt-1.5 text-xs leading-relaxed">
            Your public profile and email address, read-only. Access to your repositories is not
            requested when you sign in. You can add it later, when you link a repository to a
            project.
          </p>
        </div>
      </div>
    </section>

    <!-- What the service does -->
    <section class="order-2 lg:order-1 lg:pt-2">
      <h2 class="text-3xl leading-tight font-extrabold tracking-tight sm:text-4xl">
        Git hooks your whole team
        <span
          class="bg-linear-to-r from-indigo-500 via-violet-500 to-purple-500 bg-clip-text text-transparent dark:from-indigo-400 dark:via-violet-400 dark:to-purple-400"
        >
          actually uses
        </span>
      </h2>

      <ul class="mt-8 space-y-6">
        <li v-for="point in points" :key="point.title" class="flex gap-4">
          <div
            class="bg-primary/10 text-primary flex size-10 shrink-0 items-center justify-center rounded-lg"
          >
            <component :is="point.icon" class="size-5" aria-hidden="true" />
          </div>
          <div class="min-w-0">
            <h3 class="font-semibold">{{ point.title }}</h3>
            <p class="text-muted-foreground mt-1 text-sm leading-relaxed">{{ point.text }}</p>
            <code
              v-if="point.code"
              class="bg-muted text-foreground mt-2 inline-block max-w-full overflow-x-auto rounded-md px-2.5 py-1 font-mono text-xs whitespace-nowrap"
            >
              {{ point.code }}
            </code>
          </div>
        </li>
      </ul>
    </section>
  </div>
</template>
