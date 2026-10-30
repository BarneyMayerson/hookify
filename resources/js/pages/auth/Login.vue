<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

import GitHubIcon from '@/components/GitHubIcon.vue';
import { Button } from '@/components/ui/button';
import { redirect } from '@/routes/auth/github';

defineOptions({
  layout: {
    title: 'Log in to Hookify',
    description: 'Sign in with your GitHub account to continue',
  },
});

defineProps<{
  status?: string;
}>();
</script>

<template>
  <Head title="Log in" />

  <div v-if="status" class="mb-4 text-center text-sm font-medium text-green-600">
    {{ status }}
  </div>

  <Button as-child class="w-full" data-test="github-login-button">
    <a :href="redirect().url">
      <GitHubIcon class="mr-2 size-4" />
      Continue with GitHub
    </a>
  </Button>
</template>
