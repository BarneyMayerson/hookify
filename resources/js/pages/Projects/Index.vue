<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import {
  Check,
  Copy,
  Plus,
  Terminal,
  FolderPlus,
  ArrowRight,
  ShieldCheck,
  Clock,
} from '@lucide/vue';
import GithubRepoPicker from '@/components/Projects/GithubRepoPicker.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { show, store } from '@/routes/projects';
import type { Project, CatalogEntry } from '@/types';

const props = defineProps<{
  projects: Project[];
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

const form = useForm({
  name: '',
});

const isCopied = ref(false);

function submit() {
  form.post(store().url, {
    onSuccess: () => form.reset(),
  });
}

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
          {{ isCopied ? 'Copy CLI Command' : 'Copy' }}
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
    <form
      class="bg-card flex items-center gap-3 rounded-xl border p-4 shadow-sm"
      @submit.prevent="submit"
    >
      <FolderPlus class="size-5 shrink-0 text-indigo-600" />
      <Input
        v-model="form.name"
        type="text"
        placeholder="New Project Name..."
        class="max-w-xs"
        :disabled="form.processing"
      />
      <Button
        type="submit"
        size="sm"
        class="gap-1.5"
        :disabled="form.processing || !form.name.trim()"
      >
        <Plus class="size-4" />
        Create Project
      </Button>
    </form>

    <!-- Projects Grid -->
    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
      <div
        v-for="project in projects"
        :key="project.id"
        class="bg-card flex flex-col justify-between rounded-xl border p-5 shadow-sm transition-all hover:shadow-md"
      >
        <div class="space-y-3">
          <div class="flex items-start justify-between gap-2">
            <div>
              <h3 class="leading-none font-semibold">{{ project.name }}</h3>
              <p class="mt-1 font-mono text-[11px] text-slate-400">
                token: {{ project.api_token_prefix }}...
              </p>
            </div>
          </div>

          <!-- Stats & Last Synced Info -->
          <div class="flex flex-wrap items-center gap-2 pt-2 text-xs text-slate-500">
            <div class="flex items-center gap-1">
              <ShieldCheck class="size-3.5 text-indigo-500" />
              <span>Rules: {{ project.enabledIds?.length ?? 0 }}</span>
            </div>
            <span>•</span>
            <div class="flex items-center gap-1">
              <Clock class="size-3.5 text-slate-400" />
              <span>{{ project.last_synced_at || 'Never synced' }}</span>
            </div>
          </div>
        </div>

        <!-- Bottom Bar: GitHub Repo Picker & Link to Project Details -->
        <div class="mt-5 flex items-center justify-between border-t pt-4">
          <GithubRepoPicker :project="project" />

          <Button as-child variant="ghost" size="sm" class="gap-1 text-xs">
            <Link :href="show(project.id).url">
              View Rules
              <ArrowRight class="size-3.5" />
            </Link>
          </Button>
        </div>
      </div>
    </div>
  </div>
</template>
