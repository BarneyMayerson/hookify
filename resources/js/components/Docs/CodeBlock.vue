<script setup lang="ts">
import { Check, Copy } from '@lucide/vue';
import { ref } from 'vue';

import { Button } from '@/components/ui/button';

const props = defineProps<{
  code: string;
}>();

const copied = ref(false);

async function copy() {
  try {
    await navigator.clipboard.writeText(props.code);
    copied.value = true;
    setTimeout(() => (copied.value = false), 2000);
  } catch {
    // Clipboard unavailable: the text can still be selected by hand.
  }
}
</script>

<template>
  <div class="not-prose relative my-4">
    <pre
      class="bg-muted text-foreground border-border overflow-x-auto rounded-lg border p-4 pr-14 font-mono text-sm leading-relaxed"
    ><code>{{ code }}</code></pre>

    <Button
      type="button"
      variant="ghost"
      size="icon"
      class="absolute top-2 right-2 size-8"
      :aria-label="copied ? 'Copied' : 'Copy to clipboard'"
      @click="copy"
    >
      <Check v-if="copied" class="size-4 text-emerald-600" />
      <Copy v-else class="text-muted-foreground size-4" />
    </Button>
  </div>
</template>
