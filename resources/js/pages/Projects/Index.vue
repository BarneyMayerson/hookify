<script setup lang="ts">
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { Check, Copy, Terminal } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import type { ProjectSummary, CatalogEntry } from '@/types';
import CreateProjectForm from '@/components/Projects/CreateProjectForm.vue';
import ProjectCard from '@/components/Projects/ProjectCard.vue';

const props = defineProps<{
  projects: ProjectSummary[];
  catalog: CatalogEntry[];
  apiBase: string;
}>();

const page = usePage<{ flash?: { plaintext_token?: string } }>();
const plaintextToken = computed(() => page.props.flash?.plaintext_token);

const installCommand = computed(() =>
  plaintextToken.value
    ? `HOOKIFY_TOKEN=${plaintextToken.value} HOOKIFY_API=${props.apiBase} npx hookify-cli sync`
    : '',
);

const isCopied = ref(false);

function copyCommand() {
  if (!installCommand.value) return;

  navigator.clipboard.writeText(installCommand.value);
  isCopied.value = true;
  setTimeout(() => {
    isCopied.value = false;
  }, 2000);
}
</script>

<template>
  <div class="space-y-6 p-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold tracking-tight">Projects</h1>
        <p class="text-sm text-slate-500">
          Manage Git hook configurations and repository bindings for your projects.
        </p>
      </div>
    </div>

    <!-- Token Banner After Project Creation -->
    <div
      v-if="plaintextToken"
      class="rounded-xl border border-emerald-200 bg-emerald-50/50 p-4 dark:border-emerald-900/50 dark:bg-emerald-950/20"
    >
      <div class="flex items-start justify-between gap-4">
        <div class="space-y-1">
          <p class="text-sm font-semibold text-emerald-900 dark:text-emerald-300">
            Project Created Successfully!
          </p>
          <p class="text-xs text-emerald-700 dark:text-emerald-400">
            Copy this command to initialize and sync Git hooks locally in your repository:
          </p>
        </div>
        <Button variant="outline" size="sm" class="shrink-0 gap-1.5" @click="copyCommand">
          <Check v-if="isCopied" class="size-4 text-emerald-600" />
          <Copy v-else class="size-4" />
          {{ isCopied ? 'Copied!' : 'Copy CLI Command' }}
        </Button>
      </div>
      <div
        class="mt-3 flex items-center gap-2 overflow-x-auto rounded-lg bg-slate-950 p-3 font-mono text-xs text-emerald-400"
      >
        <Terminal class="size-4 shrink-0 text-slate-500" />
        <code>{{ installCommand }}</code>
      </div>
    </div>

    <!-- Create Project Form -->
    <CreateProjectForm />

    <!-- Projects Grid -->
    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
      <ProjectCard v-for="project in projects" :key="project.id" :project />
    </div>
  </div>
</template>
