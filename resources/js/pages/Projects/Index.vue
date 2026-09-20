<script setup lang="ts">
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { show, store } from '@/routes/projects';

interface Project {
  id: number;
  name: string;
  api_token_prefix: string;
  last_synced_at: string | null;
}

const props = defineProps<{
  projects: Project[];
  apiBase: string;
}>();

const page = usePage<{ flash?: { plaintext_token?: string } }>();
const plaintextToken = computed(() => page.props.flash?.plaintext_token);

const installCommand = computed(() =>
  plaintextToken.value
    ? `HOOKIFY_TOKEN=${plaintextToken.value} HOOKIFY_API=${props.apiBase} npx @hookify/cli sync`
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
  <div class="mx-auto max-w-2xl space-y-8 p-6">
    <h1 class="text-xl font-semibold">Проекты</h1>

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

    <form class="flex gap-2" @submit.prevent="submit">
      <input
        v-model="form.name"
        type="text"
        placeholder="Название проекта"
        class="flex-1 rounded border px-3 py-2"
      />
      <button
        type="submit"
        :disabled="form.processing"
        class="rounded bg-black px-4 py-2 text-white disabled:opacity-50"
      >
        Создать
      </button>
    </form>
    <p v-if="form.errors.name" class="text-sm text-red-600">{{ form.errors.name }}</p>

    <ul class="divide-y">
      <li v-for="project in projects" :key="project.id" class="flex justify-between py-3">
        <Link :href="show(project.id).url" class="hover:underline">
          {{ project.name }}
        </Link>
        <span class="text-sm text-gray-500">{{ project.api_token_prefix }}…</span>
      </li>
    </ul>
  </div>
</template>
