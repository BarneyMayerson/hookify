<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import DeleteUser from '@/components/DeleteUser.vue';
import Heading from '@/components/Heading.vue';
import type { Auth } from '@/types';

defineOptions({
  layout: {
    breadcrumbs: [
      {
        title: 'Profile settings',
      },
    ],
  },
});

const page = usePage<{ auth: Auth }>();
const user = computed(() => page.props.auth.user);
</script>

<template>
  <Head title="Profile settings" />

  <h1 class="sr-only">Profile settings</h1>

  <div class="flex flex-col space-y-6">
    <Heading
      variant="small"
      title="Profile"
      description="Managed by your GitHub account — sign in with a different GitHub account to change these"
    />

    <div class="flex items-center gap-4">
      <img
        v-if="user.github_avatar"
        :src="user.github_avatar"
        :alt="user.name"
        class="h-16 w-16 rounded-full"
      />
      <div class="grid gap-1">
        <p class="font-medium">{{ user.name }}</p>
        <p class="text-muted-foreground text-sm">{{ user.email }}</p>
        <a
          v-if="user.github_nickname"
          :href="`https://github.com/${user.github_nickname}`"
          target="_blank"
          rel="noopener noreferrer"
          class="text-sm text-blue-600 hover:underline"
        >
          @{{ user.github_nickname }} on GitHub
        </a>
      </div>
    </div>
  </div>

  <DeleteUser />
</template>
