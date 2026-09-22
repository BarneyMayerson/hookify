<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { index } from '@/routes/projects';
import { update } from '@/routes/projects/rules';

interface Check {
  id: string;
  label: string;
  ecosystem: 'php' | 'js' | 'universal';
  tier: 'primary' | 'secondary';
  requiresConfig: boolean;
  requiresBinary: boolean;
}

const props = defineProps<{
  project: { id: number; name: string; api_token_prefix: string };
  catalog: Check[];
  enabledIds: string[];
}>();

const form = useForm({
  check_ids: [...props.enabledIds],
});

const groups = computed(() => {
  const byEcosystem: Record<string, Check[]> = { php: [], js: [], universal: [] };
  for (const check of props.catalog) byEcosystem[check.ecosystem].push(check);
  return byEcosystem;
});

const ecosystemLabels: Record<string, string> = {
  php: 'PHP',
  js: 'JS / TS',
  universal: 'Универсальные',
};

function submit() {
  form.put(update(props.project.id).url);
}
</script>

<template>
  <div class="mx-auto max-w-7xl space-y-8 p-6">
    <div>
      <Link :href="index().url" class="text-sm text-gray-500 hover:underline">&larr; Проекты</Link>
    </div>

    <div>
      <h1 class="text-xl font-semibold">{{ project.name }}</h1>
      <p class="text-sm text-gray-500">{{ project.api_token_prefix }}…</p>
    </div>

    <form class="space-y-8" @submit.prevent="submit">
      <div class="grid gap-8 md:grid-cols-3">
        <fieldset
          v-for="(checks, ecosystem) in groups"
          :key="ecosystem"
          v-show="checks.length"
          class="space-y-3"
        >
          <legend class="text-sm font-medium text-gray-700">
            {{ ecosystemLabels[ecosystem] }}
          </legend>

          <label
            v-for="check in checks"
            :key="check.id"
            class="flex flex-col gap-1 rounded border px-3 py-2"
          >
            <div class="flex items-center gap-3">
              <input v-model="form.check_ids" type="checkbox" :value="check.id" class="h-4 w-4" />
              <span class="flex-1">{{ check.label }}</span>
              <span
                v-if="check.tier === 'secondary'"
                class="rounded bg-gray-100 px-2 py-0.5 text-xs text-gray-500 dark:bg-gray-800"
              >
                community
              </span>
              <span
                v-if="check.requiresConfig || check.requiresBinary"
                class="rounded bg-amber-100 px-2 py-0.5 text-xs text-amber-700 dark:bg-amber-950 dark:text-amber-400"
              >
                {{ check.requiresBinary ? 'нужна доп. установка' : 'нужен свой конфиг' }}
              </span>
            </div>
            <p
              v-if="
                (check.requiresConfig || check.requiresBinary) && form.check_ids.includes(check.id)
              "
              class="pl-7 text-xs text-amber-600 dark:text-amber-500"
            >
              {{
                check.requiresBinary
                  ? 'Это отдельный бинарник, не npm/composer-зависимость — без него команда упадёт с «command not found» на каждом коммите.'
                  : 'Без конфига в репозитории этот чек будет падать на каждом коммите.'
              }}
            </p>
          </label>
        </fieldset>
      </div>

      <button
        type="submit"
        :disabled="form.processing"
        class="rounded bg-black px-4 py-2 text-white disabled:opacity-50"
      >
        Сохранить
      </button>
    </form>
  </div>
</template>
