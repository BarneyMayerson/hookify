<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { store } from '@/routes/projects';

interface Project {
  id: number;
  name: string;
  api_token_prefix: string;
  last_synced_at: string | null;
}

defineProps<{
  projects: Project[];
}>();

const page = usePage<{ flash?: { plaintext_token?: string } }>();
const plaintextToken = computed(() => page.props.flash?.plaintext_token);

const form = useForm({
  name: '',
});

function submit() {
  form.post(store().url, {
    onSuccess: () => form.reset(),
  });
}

function copyToken() {
  if (plaintextToken.value) navigator.clipboard.writeText(plaintextToken.value);
}
</script>

<template>
  <div class="mx-auto max-w-2xl space-y-8 p-6">
    <h1 class="text-xl font-semibold">Проекты</h1>

    <div
      v-if="plaintextToken"
      class="rounded-md border border-amber-300 bg-amber-50 p-4 text-sm dark:border-amber-800 dark:bg-amber-950"
    >
      <p class="font-medium">Сохраните токен — он показывается один раз.</p>
      <div class="mt-2 flex items-center gap-2">
        <code class="flex-1 truncate rounded bg-white px-2 py-1 dark:bg-black">{{
          plaintextToken
        }}</code>
        <button
          type="button"
          class="rounded bg-amber-600 px-3 py-1 text-white hover:bg-amber-700"
          @click="copyToken"
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
        <span>{{ project.name }}</span>
        <span class="text-sm text-gray-500">{{ project.api_token_prefix }}…</span>
      </li>
    </ul>
  </div>
</template>
