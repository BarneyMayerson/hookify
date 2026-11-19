<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { KeyRound, Loader2 } from '@lucide/vue';
import { ref } from 'vue';

import { Button } from '@/components/ui/button';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog';
import { regenerate } from '@/routes/projects/token';
import type { Project } from '@/types';

const props = defineProps<{
  project: Project;
}>();

const isOpen = ref(false);
const isRegenerating = ref(false);

function regenerateToken() {
  isRegenerating.value = true;

  router.post(
    regenerate(props.project.id).url,
    {},
    {
      preserveScroll: true,
      onSuccess: () => {
        isOpen.value = false;
      },
      onFinish: () => {
        isRegenerating.value = false;
      },
    },
  );
}
</script>

<template>
  <div class="bg-card border-border rounded-xl border p-6 shadow-sm">
    <div class="text-foreground flex items-center gap-2 font-semibold">
      <KeyRound class="text-primary h-5 w-5" aria-hidden="true" />
      <h2>API token</h2>
    </div>
    <p class="text-muted-foreground mt-1 text-sm">
      hookify-cli uses this token to sync the project's hooks. It is shown only once, when it is
      created.
    </p>

    <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
      <code class="bg-muted text-foreground rounded-md px-3 py-1.5 font-mono text-xs">
        {{ project.api_token_prefix }}...
      </code>

      <Dialog :open="isOpen" @update:open="isOpen = $event">
        <DialogTrigger as-child>
          <Button variant="outline" size="sm">Regenerate token</Button>
        </DialogTrigger>

        <DialogContent class="sm:max-w-md">
          <DialogHeader>
            <DialogTitle>Regenerate the API token?</DialogTitle>
            <DialogDescription>
              The current token <code class="font-mono">{{ project.api_token_prefix }}...</code>
              stops working immediately. A new one is shown once, right after this.
            </DialogDescription>
          </DialogHeader>

          <ul class="text-muted-foreground list-disc space-y-1.5 pl-5 text-sm">
            <li>Everyone who uses the old token needs the new one: teammates and CI secrets.</li>
            <li>
              Anyone who runs sync with the old token gets an error, and their Hookify hooks are
              removed until they use the new token.
            </li>
          </ul>

          <DialogFooter>
            <Button variant="outline" :disabled="isRegenerating" @click="isOpen = false">
              Cancel
            </Button>
            <Button variant="destructive" :disabled="isRegenerating" @click="regenerateToken">
              <Loader2 v-if="isRegenerating" class="size-4 animate-spin" />
              Regenerate token
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  </div>
</template>
