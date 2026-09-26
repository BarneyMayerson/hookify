<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { Check, Copy, CheckCheck, Plus, Terminal, FolderPlus } from '@lucide/vue';
import { show, store } from '@/routes/projects';

interface CatalogEntry {
  id: string;
  label: string;
}

interface Project {
  id: number;
  name: string;
  api_token_prefix: string;
  last_synced_at: string | null;
  enabledIds: string[];
}

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
  <div class="mx-auto max-w-7xl space-y-8 p-6">
    <!-- Header & Create Form -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
          Projects
        </h1>
        <p class="text-sm text-slate-500 dark:text-slate-400">
          Manage your repositories and configured Git hooks across teams.
        </p>
      </div>

      <form @submit.prevent="submit" class="flex items-start gap-2 sm:max-w-md">
        <div class="flex-1 space-y-1">
          <input
            v-model="form.name"
            type="text"
            placeholder="New project name..."
            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:focus:ring-indigo-500/30"
            :class="{ 'border-red-500 dark:border-red-500': form.errors.name }"
          />
          <p v-if="form.errors.name" class="text-xs text-red-600 dark:text-red-400">
            {{ form.errors.name }}
          </p>
        </div>

        <button
          type="submit"
          :disabled="form.processing || !form.name.trim()"
          class="inline-flex shrink-0 items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-indigo-500 disabled:cursor-not-allowed disabled:opacity-50"
        >
          <Plus class="h-4 w-4" />
          Create
        </button>
      </form>
    </div>

    <!-- Token Flash Banner -->
    <div
      v-if="plaintextToken"
      class="rounded-xl border border-amber-300/80 bg-amber-50/80 p-4 text-sm text-amber-900 shadow-sm backdrop-blur-sm transition-all dark:border-amber-800/60 dark:bg-amber-950/40 dark:text-amber-200"
    >
      <div class="flex items-center gap-2 font-semibold">
        <Terminal class="h-4 w-4 text-amber-600 dark:text-amber-400" />
        <span>Save your API token — it won't be shown again!</span>
      </div>
      <p class="mt-1 text-xs text-amber-700 dark:text-amber-300">
        Run this command inside your target repository to sync your configured hooks:
      </p>

      <div class="mt-3 flex flex-col items-stretch gap-2 sm:flex-row sm:items-center">
        <code
          class="flex-1 overflow-x-auto rounded-lg border border-amber-200 bg-white px-3 py-2 font-mono text-xs whitespace-nowrap text-slate-800 dark:border-amber-900 dark:bg-slate-900 dark:text-slate-200"
        >
          {{ installCommand }}
        </code>
        <!-- Зафиксировали ширину кнопки через w-[135px] и добавили justify-center -->
        <button
          type="button"
          class="inline-flex w-33.75 shrink-0 items-center justify-center gap-1.5 rounded-lg bg-amber-600 py-2 text-xs font-medium text-white transition-colors hover:bg-amber-500"
          @click="copyCommand"
        >
          <CheckCheck v-if="isCopied" class="h-3.5 w-3.5" />
          <Copy v-else class="h-3.5 w-3.5" />
          <span>{{ isCopied ? 'Copied!' : 'Copy Command' }}</span>
        </button>
      </div>
    </div>

    <!-- Projects Matrix Table -->
    <div
      class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
    >
      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
          <thead>
            <tr
              class="border-b border-slate-200 bg-slate-50/70 dark:border-slate-800 dark:bg-slate-800/50"
            >
              <th class="px-5 py-3 font-medium text-slate-700 dark:text-slate-300">Project</th>
              <th
                v-for="check in catalog"
                :key="check.id"
                :title="check.label"
                class="px-3 py-3 text-center font-mono text-xs font-semibold tracking-wider whitespace-nowrap text-slate-500 uppercase dark:text-slate-400"
              >
                {{ check.id }}
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
            <tr
              v-for="project in projects"
              :key="project.id"
              class="transition-colors hover:bg-slate-50/50 dark:hover:bg-slate-800/30"
            >
              <td class="px-5 py-3.5">
                <Link
                  :href="show(project.id).url"
                  class="font-semibold text-slate-900 transition-colors hover:text-indigo-600 dark:text-slate-100 dark:hover:text-indigo-400"
                >
                  {{ project.name }}
                </Link>
                <div class="font-mono text-xs text-slate-400 dark:text-slate-500">
                  {{ project.api_token_prefix }}…
                </div>
              </td>
              <td v-for="check in catalog" :key="check.id" class="px-3 py-3.5 text-center">
                <Check
                  v-if="project.enabledIds.includes(check.id)"
                  class="mx-auto h-4 w-4 text-emerald-500"
                />
                <span v-else class="font-mono text-xs text-slate-300 dark:text-slate-700">—</span>
              </td>
            </tr>

            <!-- Empty State -->
            <tr v-if="projects.length === 0">
              <td :colspan="catalog.length + 1" class="px-6 py-12 text-center">
                <div class="mx-auto flex max-w-xs flex-col items-center justify-center text-center">
                  <div class="mb-3 rounded-full bg-slate-100 p-3 text-slate-400 dark:bg-slate-800">
                    <FolderPlus class="h-6 w-6" />
                  </div>
                  <p class="text-sm font-medium text-slate-900 dark:text-slate-100">
                    No projects yet
                  </p>
                  <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                    Create your first project above to generate a sync token and start configuring
                    hooks.
                  </p>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
