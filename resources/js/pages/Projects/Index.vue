<script setup lang="ts">
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { Check } from '@lucide/vue';
import { computed } from 'vue';
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

function submit() {
  form.post(store().url, {
    onSuccess: () => form.reset(),
  });
}

function copyCommand() {
  if (installCommand.value) navigator.clipboard.writeText(installCommand.value);
}
</script>

<template>
  <div class="mx-auto max-w-7xl space-y-8 p-6">
    <div class="flex items-center justify-between gap-6">
      <h1 class="shrink-0 text-xl font-semibold">Проекты</h1>

      <form class="flex flex-1 items-center gap-2" @submit.prevent="submit">
        <input
          v-model="form.name"
          type="text"
          placeholder="Название проекта"
          class="flex-1 rounded border px-3 py-2"
        />
        <button
          type="submit"
          :disabled="form.processing"
          class="shrink-0 rounded bg-black px-4 py-2 text-white disabled:opacity-50"
        >
          Создать
        </button>
      </form>
    </div>
    <p v-if="form.errors.name" class="text-sm text-red-600">{{ form.errors.name }}</p>

    <div
      v-if="plaintextToken"
      class="rounded-md border border-amber-300 bg-amber-50 p-4 text-sm dark:border-amber-800 dark:bg-amber-950"
    >
      <p class="font-medium">Сохраните команду — токен в ней показывается один раз.</p>
      <div class="mt-2 flex items-center gap-2">
        <code
          class="flex-1 overflow-x-auto rounded bg-white px-2 py-1 whitespace-nowrap dark:bg-black"
        >
          {{ installCommand }}
        </code>
        <button
          type="button"
          class="shrink-0 rounded bg-amber-600 px-3 py-1 text-white hover:bg-amber-700"
          @click="copyCommand"
        >
          Copy
        </button>
      </div>
    </div>

    <div class="overflow-x-auto rounded border">
      <table class="w-full text-sm">
        <thead>
          <tr class="border-b bg-gray-50 text-left dark:bg-gray-900">
            <th class="px-4 py-2 font-medium">Проект</th>
            <th
              v-for="check in catalog"
              :key="check.id"
              :title="check.label"
              class="px-2 py-2 text-center font-mono text-xs font-medium whitespace-nowrap text-gray-500"
            >
              {{ check.id }}
            </th>
          </tr>
        </thead>
        <tbody class="divide-y">
          <tr v-for="project in projects" :key="project.id">
            <td class="px-4 py-3">
              <Link :href="show(project.id).url" class="hover:underline">{{ project.name }}</Link>
              <div class="text-xs text-gray-500">{{ project.api_token_prefix }}…</div>
            </td>
            <td v-for="check in catalog" :key="check.id" class="px-2 py-3 text-center">
              <Check
                v-if="project.enabledIds.includes(check.id)"
                class="mx-auto h-4 w-4 text-green-600"
              />
              <span v-else class="text-gray-300">—</span>
            </td>
          </tr>
          <tr v-if="projects.length === 0">
            <td :colspan="catalog.length + 1" class="px-4 py-6 text-center text-gray-500">
              Проектов пока нет — создайте первый выше.
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
