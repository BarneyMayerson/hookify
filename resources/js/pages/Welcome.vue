<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
  ArrowRight,
  Check,
  KeyRound,
  RefreshCw,
  ShieldCheck,
  Terminal,
  Users,
  X,
} from '@lucide/vue';
import { computed } from 'vue';

import { Button } from '@/components/ui/button';
import { dashboard, login } from '@/routes';

const page = usePage();

const cta = computed(() =>
  page.props.auth.user
    ? { href: dashboard().url, label: 'Go to dashboard' }
    : { href: login().url, label: 'Get started' },
);

const description =
  'Hookify keeps Git hooks in one place and syncs them to every developer with a single command. Enforce PHP, JS/TS and universal checks across your team.';

type TerminalLine =
  | { kind: 'command' | 'heading' | 'ok' | 'fail' | 'item' | 'output' | 'plain'; text: string }
  | { kind: 'blank' };

// Mirrors the real output of `hookify-cli sync` and of the generated pre-commit
// hook. The failing phpstan output is illustrative. Keep it in sync with the CLI.
const terminalLines: TerminalLine[] = [
  { kind: 'command', text: 'HOOKIFY_TOKEN=<project-token> npx hookify-cli sync' },
  { kind: 'heading', text: 'Hookify · my-project' },
  { kind: 'ok', text: 'Config fetched from hookify.to' },
  { kind: 'ok', text: 'pre-commit hook installed (3 checks)' },
  { kind: 'item', text: 'pint' },
  { kind: 'item', text: 'phpstan' },
  { kind: 'item', text: 'oxfmt' },
  { kind: 'blank' },
  { kind: 'plain', text: '1 hook active.' },
  { kind: 'blank' },
  { kind: 'command', text: 'git commit -m "Add login form"' },
  { kind: 'ok', text: 'pint' },
  { kind: 'fail', text: 'phpstan' },
  { kind: 'output', text: 'Line 12  Undefined variable: $user' },
  { kind: 'blank' },
  { kind: 'plain', text: 'phpstan failed. Commit aborted.' },
];

const before = [
  'Hook scripts are copied between machines by hand.',
  'New teammates skip the setup and nobody notices.',
  'Changing a rule means asking everyone to update.',
];

const after = [
  'Rules live in one project on Hookify.',
  'One command installs or updates the hooks.',
  'Every developer runs the same checks.',
];

const steps = [
  {
    title: 'Create a project',
    text: 'Register your repository as a project. Hookify generates a secure API token for it.',
  },
  {
    title: 'Configure rules',
    text: 'Turn on the linters and static checks you need: PHP, JS/TS or universal ones.',
  },
  {
    title: 'Run the CLI sync',
    text: 'Run the command below in your repository. Hooks are installed and kept up to date.',
    code: 'HOOKIFY_TOKEN=<project-token> npx hookify-cli sync',
  },
];

const features = [
  {
    icon: ShieldCheck,
    title: 'Checks by stack',
    text: 'Enable PHP, JS/TS or universal linters and static checks per project, not per machine.',
  },
  {
    icon: RefreshCw,
    title: 'Update once, sync everywhere',
    text: 'Change a rule in Hookify, and each developer gets it on their next sync.',
  },
  {
    icon: KeyRound,
    title: 'A token per project',
    text: 'The CLI authenticates with the project token, so your configuration stays private to your team.',
  },
  {
    icon: Users,
    title: 'No manual onboarding',
    text: 'A new developer clones the repository, runs one command and has the team hooks installed.',
  },
];

const faq = [
  {
    q: 'What are Git hooks?',
    a: 'Git hooks are scripts that Git runs at certain moments, for example before a commit is created. Teams use them to run linters and static analysis locally, before problems reach code review or CI.',
  },
  {
    q: 'What do I need to run the CLI?',
    a: 'Node.js, so that npx is available. Set HOOKIFY_TOKEN to your project token and run npx hookify-cli sync in the root of your repository.',
  },
  {
    q: 'Which languages are supported?',
    a: 'You can enable PHP checks, JS/TS checks and universal checks that are not tied to a language.',
  },
  {
    q: 'Is Hookify open source?',
    a: 'Yes. Hookify is an open source project.',
  },
];
</script>

<template>
  <Head title="GitHooks Hub — Centralized Git Hook Management">
    <meta head-key="description" name="description" :content="description" />
    <meta
      head-key="og:title"
      property="og:title"
      content="Hookify — Centralized Git Hook Management"
    />
    <meta head-key="og:description" property="og:description" :content="description" />
  </Head>

  <div>
    <!-- Hero -->
    <section class="mx-auto flex max-w-4xl flex-col items-center px-6 pt-20 text-center">
      <div
        class="border-border bg-muted text-muted-foreground mb-8 inline-flex items-center gap-2 rounded-full border px-3 py-1 text-xs font-medium"
      >
        <span
          class="bg-primary flex h-2 w-2 animate-pulse rounded-full motion-reduce:animate-none"
        ></span>
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

      <Button as-child size="lg" class="shadow-lg">
        <Link :href="cta.href" class="gap-2">
          {{ cta.label }}
          <ArrowRight class="h-4 w-4" aria-hidden="true" />
        </Link>
      </Button>
    </section>

    <!-- Terminal -->
    <section class="mx-auto mt-16 max-w-2xl px-6" aria-label="Example of running the Hookify CLI">
      <div class="overflow-hidden rounded-xl border border-zinc-800 bg-zinc-950 shadow-2xl">
        <div
          class="flex items-center gap-2 border-b border-zinc-800 px-4 py-2.5 text-xs text-zinc-400"
        >
          <Terminal class="h-3.5 w-3.5" aria-hidden="true" />
          my-project
        </div>
        <div class="overflow-x-auto p-5 text-left font-mono text-sm leading-relaxed whitespace-pre">
          <template v-for="(line, index) in terminalLines" :key="index">
            <div v-if="line.kind === 'blank'" class="h-4" aria-hidden="true"></div>

            <p v-else-if="line.kind === 'command'" class="text-zinc-100">
              <span class="mr-2 text-violet-400 select-none">$</span>{{ line.text }}
            </p>

            <p v-else-if="line.kind === 'heading'" class="font-semibold text-zinc-100">
              {{ line.text }}
            </p>

            <p v-else-if="line.kind === 'ok'" class="text-zinc-300">
              <span class="mr-2 text-emerald-400 select-none">✔</span>{{ line.text }}
            </p>

            <p v-else-if="line.kind === 'fail'" class="text-zinc-300">
              <span class="mr-2 text-red-400 select-none">✖</span>{{ line.text }}
            </p>

            <p v-else-if="line.kind === 'item'" class="pl-4 text-zinc-300">
              <span class="mr-2 text-zinc-600 select-none">•</span>{{ line.text }}
            </p>

            <p v-else-if="line.kind === 'output'" class="pl-4 text-zinc-500">{{ line.text }}</p>

            <p v-else class="text-zinc-300">{{ line.text }}</p>
          </template>
        </div>
      </div>
    </section>

    <!-- Problem / solution -->
    <section class="mx-auto mt-28 max-w-5xl px-6">
      <div class="max-w-2xl">
        <h2 class="text-2xl font-bold tracking-tight sm:text-3xl">
          Git hooks only work when everyone has them
        </h2>
        <p class="text-muted-foreground mt-3 leading-relaxed">
          Hooks live on each developer's machine, not in the repository history. Without a shared
          way to distribute them, quality checks depend on who remembered to set them up.
        </p>
      </div>

      <div class="mt-10 grid gap-6 md:grid-cols-2">
        <div class="border-border rounded-xl border p-6">
          <h3 class="text-muted-foreground font-semibold">Without Hookify</h3>
          <ul class="mt-4 space-y-3">
            <li v-for="item in before" :key="item" class="text-muted-foreground flex gap-3 text-sm">
              <X class="text-muted-foreground/70 mt-0.5 h-4 w-4 shrink-0" aria-hidden="true" />
              {{ item }}
            </li>
          </ul>
        </div>
        <div class="border-primary/40 bg-primary/5 rounded-xl border p-6">
          <h3 class="text-primary font-semibold">With Hookify</h3>
          <ul class="mt-4 space-y-3">
            <li v-for="item in after" :key="item" class="flex gap-3 text-sm">
              <Check class="text-primary mt-0.5 h-4 w-4 shrink-0" aria-hidden="true" />
              {{ item }}
            </li>
          </ul>
        </div>
      </div>
    </section>

    <!-- How it works -->
    <section class="mx-auto mt-28 max-w-5xl px-6">
      <h2 class="text-2xl font-bold tracking-tight sm:text-3xl">How Hookify works</h2>
      <p class="text-muted-foreground mt-3 max-w-2xl leading-relaxed">
        Keep your local Git hooks synchronized across your entire team in three steps.
      </p>

      <ol class="mt-10 grid gap-4 sm:grid-cols-3">
        <li
          v-for="(step, index) in steps"
          :key="step.title"
          class="bg-card border-border rounded-xl border p-5"
        >
          <div
            class="bg-primary/10 text-primary flex h-8 w-8 items-center justify-center rounded-full text-sm font-bold"
          >
            {{ index + 1 }}
          </div>
          <h3 class="mt-4 font-semibold">{{ step.title }}</h3>
          <p class="text-muted-foreground mt-1.5 text-sm leading-relaxed">{{ step.text }}</p>
          <code
            v-if="step.code"
            class="bg-muted text-foreground mt-3 block overflow-x-auto rounded-md px-3 py-2 font-mono text-xs whitespace-nowrap"
          >
            {{ step.code }}
          </code>
        </li>
      </ol>
    </section>

    <!-- Features -->
    <section class="mx-auto mt-28 max-w-5xl px-6">
      <h2 class="text-2xl font-bold tracking-tight sm:text-3xl">
        Built for teams that share a codebase
      </h2>

      <dl class="mt-10 grid gap-x-10 gap-y-8 sm:grid-cols-2">
        <div v-for="feature in features" :key="feature.title" class="flex gap-4">
          <div
            class="bg-primary/10 text-primary flex h-10 w-10 shrink-0 items-center justify-center rounded-lg"
          >
            <component :is="feature.icon" class="h-5 w-5" aria-hidden="true" />
          </div>
          <div>
            <dt class="font-semibold">{{ feature.title }}</dt>
            <dd class="text-muted-foreground mt-1 text-sm leading-relaxed">{{ feature.text }}</dd>
          </div>
        </div>
      </dl>
    </section>

    <!-- FAQ -->
    <section class="mx-auto mt-28 max-w-3xl px-6">
      <h2 class="text-2xl font-bold tracking-tight sm:text-3xl">Questions</h2>

      <div class="divide-border border-border mt-8 divide-y border-y">
        <details v-for="item in faq" :key="item.q" class="group py-4">
          <summary
            class="focus-visible:ring-ring flex cursor-pointer list-none items-center justify-between gap-4 rounded font-medium outline-none focus-visible:ring-2"
          >
            {{ item.q }}
            <span
              class="text-muted-foreground text-xl leading-none transition-transform group-open:rotate-45"
              aria-hidden="true"
            >
              +
            </span>
          </summary>
          <p class="text-muted-foreground mt-3 max-w-2xl text-sm leading-relaxed">{{ item.a }}</p>
        </details>
      </div>
    </section>

    <!-- Final CTA -->
    <section class="mx-auto my-28 max-w-3xl px-6 text-center">
      <h2 class="text-2xl font-bold tracking-tight sm:text-3xl">Set up your first project</h2>
      <p class="text-muted-foreground mx-auto mt-3 max-w-xl leading-relaxed">
        Sign in with GitHub, create a project and run one command in your repository.
      </p>
      <Button as-child size="lg" class="mt-8 shadow-lg">
        <Link :href="cta.href" class="gap-2">
          {{ cta.label }}
          <ArrowRight class="h-4 w-4" aria-hidden="true" />
        </Link>
      </Button>
    </section>
  </div>
</template>
