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
import { Button } from '@/components/ui/button';

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
        <h1 class="text-foreground text-2xl font-bold tracking-tight">Overview</h1>
        <p class="text-muted-foreground text-sm">
          Monitor your Git hooks configuration and project sync states.
        </p>
      </div>

      <Button as-child>
        <Link :href="index().url">
          <Plus class="h-4 w-4" />
          <span>Manage Projects</span>
        </Link>
      </Button>
    </div>

    <!-- Stat Cards -->
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
      <!-- Stat 1: Total Projects -->
      <div class="bg-card border-border rounded-xl border p-5 shadow-sm">
        <div class="flex items-center justify-between">
          <span class="text-muted-foreground text-xs font-medium tracking-wider uppercase">
            Total Projects
          </span>
          <div class="bg-primary/10 text-primary rounded-lg p-2">
            <FolderGit2 class="h-5 w-5" />
          </div>
        </div>
        <div class="mt-2 flex items-baseline gap-2">
          <span class="text-foreground text-3xl font-bold tracking-tight">
            {{ props.stats.totalProjects }}
          </span>
        </div>
      </div>

      <!-- Stat 2: Active Rules -->
      <div class="bg-card border-border rounded-xl border p-5 shadow-sm">
        <div class="flex items-center justify-between">
          <span class="text-muted-foreground text-xs font-medium tracking-wider uppercase">
            Active Hooks & Rules
          </span>
          <div class="rounded-lg bg-emerald-500/10 p-2 text-emerald-600 dark:text-emerald-400">
            <ShieldCheck class="h-5 w-5" />
          </div>
        </div>
        <div class="mt-2 flex items-baseline gap-2">
          <span class="text-foreground text-3xl font-bold tracking-tight">
            {{ props.stats.activeRulesCount }}
          </span>
        </div>
      </div>

      <!-- Stat 3: Last Sync -->
      <div
        class="bg-card border-border rounded-xl border p-5 shadow-sm sm:col-span-2 lg:col-span-1"
      >
        <div class="flex items-center justify-between">
          <span class="text-muted-foreground text-xs font-medium tracking-wider uppercase">
            CLI Sync Activity
          </span>
          <div class="rounded-lg bg-sky-500/10 p-2 text-sky-600 dark:text-sky-400">
            <Activity class="h-5 w-5" />
          </div>
        </div>
        <div class="mt-2 flex items-baseline gap-2">
          <span class="text-foreground text-sm font-semibold">
            {{ props.stats.lastSyncTime || 'No recent syncs' }}
          </span>
        </div>
      </div>
    </div>

    <!-- Main Content Area Grid -->
    <div class="grid gap-6 lg:grid-cols-3">
      <!-- Quick Setup Guide -->
      <div class="bg-card border-border rounded-xl border p-6 shadow-sm lg:col-span-2">
        <div class="text-foreground flex items-center gap-2 font-semibold">
          <Terminal class="text-primary h-5 w-5" />
          <h2>How Hookify Works</h2>
        </div>
        <p class="text-muted-foreground mt-1 text-sm">
          Keep your local Git hooks synchronized across your entire team in three steps:
        </p>

        <div class="mt-6 grid gap-4 sm:grid-cols-3">
          <div class="bg-muted/50 border-border rounded-lg border p-4">
            <div
              class="bg-primary/10 text-primary flex h-7 w-7 items-center justify-center rounded-full text-xs font-bold"
            >
              1
            </div>
            <h3 class="text-foreground mt-3 text-xs font-semibold">Create Project</h3>
            <p class="text-muted-foreground mt-1 text-xs">
              Register a project to generate a secure API token.
            </p>
          </div>

          <div class="bg-muted/50 border-border rounded-lg border p-4">
            <div
              class="bg-primary/10 text-primary flex h-7 w-7 items-center justify-center rounded-full text-xs font-bold"
            >
              2
            </div>
            <h3 class="text-foreground mt-3 text-xs font-semibold">Configure Rules</h3>
            <p class="text-muted-foreground mt-1 text-xs">
              Enable PHP, JS/TS, or Universal linters and static checks.
            </p>
          </div>

          <div class="bg-muted/50 border-border rounded-lg border p-4">
            <div
              class="bg-primary/10 text-primary flex h-7 w-7 items-center justify-center rounded-full text-xs font-bold"
            >
              3
            </div>
            <h3 class="text-foreground mt-3 text-xs font-semibold">Run CLI Sync</h3>
            <p class="text-muted-foreground mt-1 text-xs">
              Run
              <code class="text-foreground font-mono text-[11px]">npx hookify-cli sync</code>
              in your repository.
            </p>
          </div>
        </div>

        <div class="mt-6 flex justify-end">
          <Link
            :href="index().url"
            class="text-primary hover:text-primary/80 inline-flex items-center gap-1.5 text-xs font-semibold transition-colors"
          >
            <span>Go to Projects list</span>
            <ArrowRight class="h-3.5 w-3.5" />
          </Link>
        </div>
      </div>

      <!-- Recent Projects Widget -->
      <div
        class="bg-card border-border flex flex-col justify-between rounded-xl border p-6 shadow-sm"
      >
        <div>
          <div class="border-border flex items-center justify-between border-b pb-3">
            <h2 class="text-foreground text-sm font-semibold">Recent Projects</h2>
            <Link
              :href="index().url"
              class="text-muted-foreground hover:text-foreground text-xs font-medium"
            >
              View all
            </Link>
          </div>

          <div v-if="props.recentProjects.length > 0" class="mt-4 space-y-3">
            <div
              v-for="proj in props.recentProjects"
              :key="proj.id"
              class="border-border hover:bg-accent/50 flex items-center justify-between rounded-lg border p-3 transition-colors"
            >
              <div>
                <p class="text-foreground text-sm font-medium">
                  {{ proj.name }}
                </p>
                <div class="mt-0.5 flex items-center gap-2">
                  <span class="text-muted-foreground font-mono text-[10px]">
                    {{ proj.api_token_prefix }}…
                  </span>
                  <span class="text-muted-foreground text-[10px]">•</span>
                  <span class="text-muted-foreground text-[10px]">
                    {{ proj.active_rules_count }} rules
                  </span>
                </div>
              </div>

              <div class="text-right">
                <span class="text-muted-foreground inline-flex items-center gap-1 text-[11px]">
                  <Clock class="h-3 w-3" />
                  {{ proj.last_synced_at || 'Never' }}
                </span>
              </div>
            </div>
          </div>

          <!-- Empty State -->
          <div v-else class="py-8 text-center">
            <p class="text-muted-foreground text-xs">No projects configured yet.</p>
            <Link
              :href="index().url"
              class="text-primary mt-2 inline-flex items-center gap-1 text-xs font-medium hover:underline"
            >
              <Plus class="h-3.5 w-3.5" />
              <span>Create project</span>
            </Link>
          </div>
        </div>

        <div class="border-border mt-4 border-t pt-4 text-center">
          <span class="text-muted-foreground inline-flex items-center gap-1.5 text-xs">
            <CheckCircle2 class="h-3.5 w-3.5 text-emerald-500" />
            Hookify API status: Operational
          </span>
        </div>
      </div>
    </div>
  </div>
</template>
