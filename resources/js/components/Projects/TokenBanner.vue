<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { Check, Copy, Terminal, X } from '@lucide/vue';
import { computed, ref, watch } from 'vue';

import { Button } from '@/components/ui/button';

const props = withDefaults(
  defineProps<{
    apiBase: string;
    /** `created` after a new project, `regenerated` after a token was replaced. */
    mode?: 'created' | 'regenerated';
  }>(),
  { mode: 'created' },
);

// Must mirror the default API URL hardcoded in hookify-cli (0.2.0 and later).
// The HOOKIFY_API part is added to the command only for other servers.
const CLI_DEFAULT_API = 'https://hookify.to/api/v1';

const page = usePage<{ flash?: { plaintext_token?: string } }>();

// Flash data lives for a single response. Keeping the token in local state
// means a later Inertia visit doesn't wipe the banner before it is copied.
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

const texts = computed(() =>
  props.mode === 'regenerated'
    ? {
        title: 'New token generated',
        warning:
          "Copy the command now. The token won't be shown again, and the previous token no longer works.",
        steps: [
          'Update HOOKIFY_TOKEN wherever the old token was used: CI secrets and your teammates.',
          'Anyone who runs sync with the old token gets an error. Their installed hooks keep working, but won’t update until they use the new token.',
          'Run the command in a repository to check that the new token works.',
        ],
      }
    : {
        title: 'Project created',
        warning: "Copy the command now. The token won't be shown again.",
        steps: [
          "Open Settings on the new project's card below and enable the checks you need.",
          'Run the command in the root of your repository.',
          'Run it again after you change the rules to update the hooks.',
        ],
      },
);

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
  <div
    v-if="token"
    role="status"
    class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-4"
  >
    <div class="flex items-start justify-between gap-4">
      <div class="space-y-1">
        <p class="text-sm font-semibold text-emerald-700 dark:text-emerald-300">
          {{ texts.title }}
        </p>
        <p class="text-xs font-medium text-emerald-700 dark:text-emerald-300">
          {{ texts.warning }}
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
      <li v-for="step in texts.steps" :key="step">{{ step }}</li>
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
</template>
