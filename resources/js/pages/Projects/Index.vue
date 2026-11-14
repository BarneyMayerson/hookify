<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { Check, Copy, Terminal, X } from '@lucide/vue';
import { computed, ref, watch } from 'vue';

import CreateProjectForm from '@/components/Projects/CreateProjectForm.vue';
import ProjectCard from '@/components/Projects/ProjectCard.vue';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import { index as projectsIndex } from '@/routes/projects';
import type { ProjectSummary, CatalogEntry } from '@/types';

const props = defineProps<{
  projects: ProjectSummary[];
  catalog: CatalogEntry[];
  apiBase: string;
}>();

defineOptions({
  layout: {
    breadcrumbs: [
      {
        title: 'Dashboard',
        href: dashboard().url,
      },
      {
        title: 'Projects',
        href: projectsIndex().url,
      },
    ],
  },
});

// Must mirror the default API URL hardcoded in hookify-cli.
// When the CLI default is switched to hookify.to, update this constant:
// the HOOKIFY_API part then disappears from the command on production.
const CLI_DEFAULT_API = 'https://hookify.lan/api/v1';

const page = usePage<{ flash?: { plaintext_token?: string } }>();

// Flash data lives for a single response. Keeping the token in local state
// means a later Inertia visit (e.g. toggling a rule) doesn't wipe the banner
// before the user has copied the command.
const token = ref<string | null>(null);

watch(
  () => page.props.flash?.plaintext_token,
  (value) => {
    if (value) token.value = value;
  },
  { immediate: true },
);

const installCommand = computed(() => {
  if (!token.value) return '';

  const api = props.apiBase !== CLI_DEFAULT_API ? ` HOOKIFY_API=${props.apiBase}` : '';

  return `HOOKIFY_TOKEN=${token.value}${api} npx hookify-cli sync`;
});

const copied = ref<'command' | 'token' | null>(null);

async function copy(kind: 'command' | 'token', text: string) {
  if (!text) return;

  try {
    await navigator.clipboard.writeText(text);
    copied.value = kind;
    setTimeout(() => (copied.value = null), 2000);
  } catch {
    // Clipboard unavailable: the text is select-all, so it can be copied manually.
  }
}
</script>

<template>
  <div class="space-y-6 p-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-foreground text-2xl font-bold tracking-tight">Projects</h1>
        <p class="text-muted-foreground text-sm">
          Manage Git hook configurations and repository bindings for your projects.
        </p>
      </div>
    </div>

    <!-- Token banner after project creation -->
    <div
      v-if="token"
      role="status"
      class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-4"
    >
      <div class="flex items-start justify-between gap-4">
        <div class="space-y-1">
          <p class="text-sm font-semibold text-emerald-700 dark:text-emerald-300">
            Project created
          </p>
          <p class="text-xs font-medium text-emerald-700 dark:text-emerald-300">
            Copy the command now. The token won't be shown again.
          </p>
        </div>
        <div class="flex shrink-0 items-center gap-1">
          <Button
            variant="outline"
            size="sm"
            class="gap-1.5"
            @click="copy('command', installCommand)"
          >
            <Check v-if="copied === 'command'" class="size-4 text-emerald-600" />
            <Copy v-else class="size-4" />
            {{ copied === 'command' ? 'Copied' : 'Copy command' }}
          </Button>
          <Button variant="ghost" size="icon" aria-label="Dismiss" @click="token = null">
            <X class="size-4" />
          </Button>
        </div>
      </div>

      <div
        class="bg-muted mt-3 flex items-center gap-2 overflow-x-auto rounded-lg p-3 font-mono text-xs"
      >
        <Terminal class="text-muted-foreground size-4 shrink-0" aria-hidden="true" />
        <code class="text-foreground whitespace-nowrap select-all">{{ installCommand }}</code>
      </div>

      <ol class="text-muted-foreground mt-4 list-decimal space-y-1 pl-5 text-xs">
        <li>Open Settings on the new project's card below and enable the checks you need.</li>
        <li>Run the command in the root of your repository.</li>
        <li>Run it again after you change the rules to update the hooks.</li>
      </ol>

      <div class="mt-3 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs">
        <p class="text-muted-foreground">Don't commit the token. In CI, store it as a secret.</p>
        <button
          type="button"
          class="text-primary hover:text-primary/80 font-medium transition-colors"
          @click="copy('token', token)"
        >
          {{ copied === 'token' ? 'Token copied' : 'Copy token only' }}
        </button>
      </div>
    </div>

    <!-- Create project form -->
    <CreateProjectForm />

    <!-- Projects grid -->
    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
      <ProjectCard v-for="project in projects" :key="project.id" :project />
    </div>
  </div>
</template>
