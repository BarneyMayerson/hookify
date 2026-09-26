<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
  FolderGit2,
  ShieldCheck,
  Activity,
  Plus,
  Terminal,
  ArrowRight,
  Clock,
  CheckCircle2,
} from '@lucide/vue';
import { dashboard } from '@/routes';
import { index } from '@/routes/projects';

defineOptions({
  layout: {
    breadcrumbs: [
      {
        title: 'Dashboard',
        href: dashboard().url,
      },
    ],
  },
});

interface ProjectSummary {
  id: number;
  name: string;
  api_token_prefix: string;
  active_rules_count: number;
  last_synced_at: string | null;
}

// Пропсы с фоллбэками на случай, если данные еще не проброшены с бэкенда
const props = withDefaults(
  defineProps<{
    stats?: {
      totalProjects: number;
      activeRulesCount: number;
      lastSyncTime: string | null;
    };
    recentProjects?: ProjectSummary[];
  }>(),
  {
    stats: () => ({
      totalProjects: 0,
      activeRulesCount: 0,
      lastSyncTime: null,
    }),
    recentProjects: () => [],
  },
);
</script>

<template>
  <Head title="Dashboard" />

  <div class="mx-auto max-w-7xl space-y-6 p-4 sm:p-6">
    <!-- Welcome Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
          Overview
        </h1>
        <p class="text-sm text-slate-500 dark:text-slate-400">
          Monitor your Git hooks configuration and project sync states.
        </p>
      </div>

      <Link
        :href="index().url"
        class="inline-flex shrink-0 items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-indigo-500"
      >
        <Plus class="h-4 w-4" />
        <span>Manage Projects</span>
      </Link>
    </div>

    <!-- Stat Cards -->
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
      <!-- Stat 1: Total Projects -->
      <div
        class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
      >
        <div class="flex items-center justify-between">
          <span
            class="text-xs font-medium tracking-wider text-slate-500 uppercase dark:text-slate-400"
          >
            Total Projects
          </span>
          <div
            class="rounded-lg bg-indigo-50 p-2 text-indigo-600 dark:bg-indigo-950/60 dark:text-indigo-400"
          >
            <FolderGit2 class="h-5 w-5" />
          </div>
        </div>
        <div class="mt-2 flex items-baseline gap-2">
          <span class="text-3xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
            {{ props.stats.totalProjects }}
          </span>
        </div>
      </div>

      <!-- Stat 2: Active Rules -->
      <div
        class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
      >
        <div class="flex items-center justify-between">
          <span
            class="text-xs font-medium tracking-wider text-slate-500 uppercase dark:text-slate-400"
          >
            Active Hooks & Rules
          </span>
          <div
            class="rounded-lg bg-emerald-50 p-2 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-400"
          >
            <ShieldCheck class="h-5 w-5" />
          </div>
        </div>
        <div class="mt-2 flex items-baseline gap-2">
          <span class="text-3xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
            {{ props.stats.activeRulesCount }}
          </span>
        </div>
      </div>

      <!-- Stat 3: Last Sync -->
      <div
        class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:col-span-2 lg:col-span-1 dark:border-slate-800 dark:bg-slate-900"
      >
        <div class="flex items-center justify-between">
          <span
            class="text-xs font-medium tracking-wider text-slate-500 uppercase dark:text-slate-400"
          >
            CLI Sync Activity
          </span>
          <div class="rounded-lg bg-sky-50 p-2 text-sky-600 dark:bg-sky-950/60 dark:text-sky-400">
            <Activity class="h-5 w-5" />
          </div>
        </div>
        <div class="mt-2 flex items-baseline gap-2">
          <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">
            {{ props.stats.lastSyncTime || 'No recent syncs' }}
          </span>
        </div>
      </div>
    </div>

    <!-- Main Content Area Grid -->
    <div class="grid gap-6 lg:grid-cols-3">
      <!-- Quick Setup Guide (2 columns on large screens) -->
      <div
        class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2 dark:border-slate-800 dark:bg-slate-900"
      >
        <div class="flex items-center gap-2 font-semibold text-slate-900 dark:text-slate-100">
          <Terminal class="h-5 w-5 text-indigo-600 dark:text-indigo-400" />
          <h2>How Hookify Works</h2>
        </div>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
          Keep your local Git hooks synchronized across your entire team in three steps:
        </p>

        <div class="mt-6 grid gap-4 sm:grid-cols-3">
          <div
            class="rounded-lg border border-slate-100 bg-slate-50/50 p-4 dark:border-slate-800 dark:bg-slate-800/30"
          >
            <div
              class="flex h-7 w-7 items-center justify-center rounded-full bg-indigo-100 text-xs font-bold text-indigo-600 dark:bg-indigo-900/60 dark:text-indigo-400"
            >
              1
            </div>
            <h3 class="mt-3 text-xs font-semibold text-slate-900 dark:text-slate-100">
              Create Project
            </h3>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
              Register a project to generate a secure API token.
            </p>
          </div>

          <div
            class="rounded-lg border border-slate-100 bg-slate-50/50 p-4 dark:border-slate-800 dark:bg-slate-800/30"
          >
            <div
              class="flex h-7 w-7 items-center justify-center rounded-full bg-indigo-100 text-xs font-bold text-indigo-600 dark:bg-indigo-900/60 dark:text-indigo-400"
            >
              2
            </div>
            <h3 class="mt-3 text-xs font-semibold text-slate-900 dark:text-slate-100">
              Configure Rules
            </h3>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
              Enable PHP, JS/TS, or Universal linters and static checks.
            </p>
          </div>

          <div
            class="rounded-lg border border-slate-100 bg-slate-50/50 p-4 dark:border-slate-800 dark:bg-slate-800/30"
          >
            <div
              class="flex h-7 w-7 items-center justify-center rounded-full bg-indigo-100 text-xs font-bold text-indigo-600 dark:bg-indigo-900/60 dark:text-indigo-400"
            >
              3
            </div>
            <h3 class="mt-3 text-xs font-semibold text-slate-900 dark:text-slate-100">
              Run CLI Sync
            </h3>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
              Run
              <code class="font-mono text-[11px] text-slate-700 dark:text-slate-300"
                >npx hookify-cli sync</code
              >
              in your repository.
            </p>
          </div>
        </div>

        <div class="mt-6 flex justify-end">
          <Link
            :href="index().url"
            class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-600 transition-colors hover:text-indigo-500 dark:text-indigo-400"
          >
            <span>Go to Projects list</span>
            <ArrowRight class="h-3.5 w-3.5" />
          </Link>
        </div>
      </div>

      <!-- Recent Projects Widget (1 column) -->
      <div
        class="flex flex-col justify-between rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
      >
        <div>
          <div
            class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800"
          >
            <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-100">
              Recent Projects
            </h2>
            <Link
              :href="index().url"
              class="text-xs font-medium text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
            >
              View all
            </Link>
          </div>

          <div v-if="props.recentProjects.length > 0" class="mt-4 space-y-3">
            <div
              v-for="proj in props.recentProjects"
              :key="proj.id"
              class="flex items-center justify-between rounded-lg border border-slate-100 p-3 transition-colors hover:bg-slate-50/50 dark:border-slate-800/80 dark:hover:bg-slate-800/30"
            >
              <div>
                <p class="text-sm font-medium text-slate-900 dark:text-slate-100">
                  {{ proj.name }}
                </p>
                <div class="mt-0.5 flex items-center gap-2">
                  <span class="font-mono text-[10px] text-slate-400">
                    {{ proj.api_token_prefix }}…
                  </span>
                  <span class="text-[10px] text-slate-400">•</span>
                  <span class="text-[10px] text-slate-500 dark:text-slate-400">
                    {{ proj.active_rules_count }} rules
                  </span>
                </div>
              </div>

              <div class="text-right">
                <span class="inline-flex items-center gap-1 text-[11px] text-slate-400">
                  <Clock class="h-3 w-3" />
                  {{ proj.last_synced_at || 'Never' }}
                </span>
              </div>
            </div>
          </div>

          <!-- Empty State -->
          <div v-else class="py-8 text-center">
            <p class="text-xs text-slate-500 dark:text-slate-400">No projects configured yet.</p>
            <Link
              :href="index().url"
              class="mt-2 inline-flex items-center gap-1 text-xs font-medium text-indigo-600 hover:underline dark:text-indigo-400"
            >
              <Plus class="h-3.5 w-3.5" />
              <span>Create project</span>
            </Link>
          </div>
        </div>

        <div class="mt-4 border-t border-slate-100 pt-4 text-center dark:border-slate-800">
          <span class="inline-flex items-center gap-1.5 text-xs text-slate-400">
            <CheckCircle2 class="h-3.5 w-3.5 text-emerald-500" />
            Hookify API status: Operational
          </span>
        </div>
      </div>
    </div>
  </div>
</template>
