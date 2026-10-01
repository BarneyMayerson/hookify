<script setup lang="ts">
import { computed } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Save, AlertTriangle } from '@lucide/vue';
import GithubRepoPicker from '@/components/Projects/GithubRepoPicker.vue';
import { index } from '@/routes/projects';
import { update } from '@/routes/projects/rules';
import type { Project, Check } from '@/types';

const props = defineProps<{
  project: Project;
  catalog: Check[];
  enabledIds: string[];
}>();

const form = useForm({
  check_ids: [...props.enabledIds],
});

// Group catalog checks by ecosystem
const groups = computed(() => {
  const byEcosystem: Record<string, Check[]> = { php: [], js: [], universal: [] };
  for (const check of props.catalog) {
    if (byEcosystem[check.ecosystem]) {
      byEcosystem[check.ecosystem].push(check);
    }
  }
  return byEcosystem;
});

const ecosystemMeta: Record<string, { label: string; description: string }> = {
  php: { label: 'PHP', description: 'Composer & PHP runtime tools' },
  js: { label: 'JS / TS', description: 'Node.js, npm, ESLint, Prettier & formatters' },
  universal: { label: 'Universal', description: 'Cross-language & repository structure checks' },
};

function submit() {
  form.put(update(props.project.id).url);
}
</script>

<template>
  <div class="mx-auto max-w-7xl space-y-8 p-6">
    <!-- Header Navigation & Actions -->
    <div
      class="flex flex-col gap-4 border-b border-slate-200 pb-6 sm:flex-row sm:items-center sm:justify-between dark:border-slate-800"
    >
      <div class="space-y-1">
        <Link
          :href="index().url"
          class="inline-flex items-center gap-1 text-xs font-medium text-slate-500 transition-colors hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200"
        >
          <ArrowLeft class="h-3.5 w-3.5" />
          Back to Projects
        </Link>
        <div class="flex flex-wrap items-center gap-3 pt-3">
          <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
            {{ project.name }}
          </h1>
          <span
            class="rounded-md border border-slate-200 bg-slate-100 px-2 py-1 font-mono text-xs text-slate-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400"
          >
            Token prefix: {{ project.api_token_prefix }}…
          </span>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex items-center gap-3">
        <!-- GitHub Repo Picker -->
        <GithubRepoPicker :project="project" />

        <button
          type="button"
          @click="submit"
          :disabled="form.processing || !form.isDirty"
          class="inline-flex w-30 items-center justify-center gap-2 rounded-lg bg-indigo-600 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-indigo-500 disabled:cursor-not-allowed disabled:opacity-50"
        >
          <Save class="h-4 w-4" />
          <span>{{ form.processing ? 'Saving...' : 'Save Rules' }}</span>
        </button>
      </div>
    </div>

    <!-- Rules Selection Grid -->
    <form @submit.prevent="submit" class="space-y-8">
      <div class="grid gap-6 lg:grid-cols-3">
        <div
          v-for="(checks, ecosystem) in groups"
          :key="ecosystem"
          v-show="checks.length"
          class="flex flex-col overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
        >
          <!-- Group Header -->
          <div
            class="border-b border-slate-200 bg-slate-50/70 p-4 dark:border-slate-800 dark:bg-slate-800/40"
          >
            <div class="flex items-center justify-between">
              <h2 class="font-semibold text-slate-900 dark:text-slate-100">
                {{ ecosystemMeta[ecosystem]?.label ?? ecosystem }}
              </h2>
              <span
                class="rounded-full bg-slate-200/70 px-2 py-0.5 text-xs font-medium text-slate-700 dark:bg-slate-700/60 dark:text-slate-300"
              >
                {{ checks.filter((c) => form.check_ids.includes(c.id)).length }} /
                {{ checks.length }}
              </span>
            </div>
            <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
              {{ ecosystemMeta[ecosystem]?.description }}
            </p>
          </div>

          <!-- Group Items -->
          <div class="flex-1 space-y-3 p-4">
            <label
              v-for="check in checks"
              :key="check.id"
              :class="[
                'group relative flex cursor-pointer flex-col gap-2 rounded-lg border p-3.5 transition-all select-none',
                form.check_ids.includes(check.id)
                  ? 'border-indigo-500/80 bg-indigo-50/30 ring-1 ring-indigo-500/20 dark:border-indigo-500/50 dark:bg-indigo-950/20'
                  : 'border-slate-200 bg-white hover:border-slate-300 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-slate-700',
              ]"
            >
              <div class="flex items-start gap-3">
                <input
                  v-model="form.check_ids"
                  type="checkbox"
                  :value="check.id"
                  class="mt-0.5 h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-0 dark:border-slate-700 dark:bg-slate-800 dark:checked:bg-indigo-600"
                />

                <div class="min-w-0 flex-1">
                  <div class="flex items-center justify-between gap-2">
                    <span
                      class="text-sm font-medium text-slate-900 transition-colors group-hover:text-indigo-600 dark:text-slate-100 dark:group-hover:text-indigo-400"
                    >
                      {{ check.label }}
                    </span>
                  </div>

                  <div class="mt-1 flex flex-wrap gap-1.5">
                    <span
                      v-if="check.tier === 'secondary'"
                      class="inline-flex items-center rounded bg-slate-100 px-1.5 py-0.5 text-[10px] font-medium text-slate-500 dark:bg-slate-800 dark:text-slate-400"
                    >
                      community
                    </span>
                    <span
                      v-if="check.requiresConfig || check.requiresBinary"
                      class="inline-flex items-center gap-1 rounded border border-amber-200 bg-amber-100 px-1.5 py-0.5 text-[10px] font-medium text-amber-800 dark:border-amber-900/50 dark:bg-amber-950/60 dark:text-amber-300"
                    >
                      <AlertTriangle class="h-2.5 w-2.5 shrink-0" />
                      {{ check.requiresBinary ? 'Needs extra setup' : 'Needs config file' }}
                    </span>
                  </div>
                </div>
              </div>

              <!-- Warning Notice when checked -->
              <div
                v-if="
                  (check.requiresConfig || check.requiresBinary) &&
                  form.check_ids.includes(check.id)
                "
                class="mt-1 flex items-start gap-1.5 rounded-md border border-amber-200/60 bg-amber-50/80 p-2 text-xs text-amber-800 dark:border-amber-900/40 dark:bg-amber-950/40 dark:text-amber-300"
              >
                <AlertTriangle
                  class="mt-0.5 h-3.5 w-3.5 shrink-0 text-amber-600 dark:text-amber-400"
                />
                <p class="text-[11px] leading-normal">
                  {{
                    check.requiresBinary
                      ? 'Requires standalone binary on dev machine (fails with "command not found" otherwise).'
                      : 'Requires configuration file in repo root (will fail on commit if missing).'
                  }}
                </p>
              </div>
            </label>
          </div>
        </div>
      </div>

      <!-- Sticky Save Bar for convenience when scrolling -->
      <div
        v-if="form.isDirty"
        class="sticky bottom-6 flex items-center justify-between rounded-xl border border-slate-200 bg-white/90 p-4 shadow-lg backdrop-blur-md transition-all dark:border-slate-800 dark:bg-slate-900/90"
      >
        <div class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
          <span class="relative flex h-2 w-2">
            <span
              class="absolute inline-flex h-full w-full animate-ping rounded-full bg-amber-400 opacity-75"
            ></span>
            <span class="relative inline-flex h-2 w-2 rounded-full bg-amber-500"></span>
          </span>
          You have unsaved rule changes.
        </div>
        <div class="flex items-center gap-3">
          <button
            type="button"
            @click="form.reset()"
            class="px-3 py-1.5 text-xs font-medium text-slate-600 transition-colors hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-100"
          >
            Reset
          </button>
          <button
            type="submit"
            :disabled="form.processing"
            class="inline-flex w-30 items-center justify-center gap-2 rounded-lg bg-indigo-600 py-2 text-xs font-semibold text-white shadow-sm transition-colors hover:bg-indigo-500 disabled:opacity-50"
          >
            <Save class="h-3.5 w-3.5" />
            <span>{{ form.processing ? 'Saving...' : 'Save Rules' }}</span>
          </button>
        </div>
      </div>
    </form>
  </div>
</template>
