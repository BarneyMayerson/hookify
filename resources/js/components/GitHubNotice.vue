<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { Info, X } from '@lucide/vue';
import { computed, ref, watch } from 'vue';

import { Alert, AlertDescription } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';

// The server reports GitHub flow results (a cancelled sign-in, access to
// private repositories not granted) under the `github` error key.
const page = usePage<{ errors?: { github?: string } }>();
const message = computed(() => page.props.errors?.github);

const dismissed = ref(false);

// A new message after a later redirect must show even if an earlier one was dismissed.
watch(message, () => {
  dismissed.value = false;
});
</script>

<template>
  <Alert v-if="message && !dismissed" role="alert" class="relative pr-12">
    <Info />
    <AlertDescription>{{ message }}</AlertDescription>
    <Button
      type="button"
      variant="ghost"
      size="icon"
      class="absolute top-1.5 right-1.5 size-7"
      aria-label="Dismiss"
      @click="dismissed = true"
    >
      <X class="size-4" />
    </Button>
  </Alert>
</template>
