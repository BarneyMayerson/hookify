<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { GitBranch, Mail, User as UserIcon, ExternalLink } from '@lucide/vue';
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

  <div class="mx-auto max-w-4xl space-y-8 p-4 sm:p-6">
    <!-- Header -->
    <div class="border-b border-slate-200 pb-5 dark:border-slate-800">
      <Heading
        title="Profile Settings"
        description="Your profile information is synchronized with your GitHub account."
      />
    </div>

    <!-- Identity Card -->
    <div
      class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
    >
      <div
        class="flex items-center justify-between border-b border-slate-100 bg-slate-50/60 px-6 py-4 dark:border-slate-800/80 dark:bg-slate-800/40"
      >
        <div
          class="flex items-center gap-2 text-sm font-semibold text-slate-900 dark:text-slate-100"
        >
          <GitBranch class="h-4 w-4 text-indigo-500" />
          <span>Connected GitHub Account</span>
        </div>
        <span
          class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700 dark:border-emerald-800/60 dark:bg-emerald-950/60 dark:text-emerald-400"
        >
          Active Identity
        </span>
      </div>

      <div class="p-6">
        <div class="flex flex-col items-start gap-5 sm:flex-row sm:items-center">
          <!-- Avatar -->
          <div class="relative shrink-0">
            <img
              v-if="user.github_avatar"
              :src="user.github_avatar"
              :alt="user.name"
              class="h-20 w-20 rounded-full border-2 border-slate-200 object-cover shadow-sm dark:border-slate-700"
            />
            <div
              v-else
              class="flex h-20 w-20 items-center justify-center rounded-full border border-slate-200 bg-slate-100 text-slate-400 dark:border-slate-700 dark:bg-slate-800"
            >
              <UserIcon class="h-10 w-10" />
            </div>
          </div>

          <!-- User Details -->
          <div class="min-w-0 flex-1 space-y-2">
            <div>
              <h2 class="truncate text-lg font-bold text-slate-900 dark:text-slate-100">
                {{ user.name }}
              </h2>
              <div
                class="mt-1 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-500 dark:text-slate-400"
              >
                <span class="inline-flex items-center gap-1.5">
                  <Mail class="h-3.5 w-3.5 text-slate-400" />
                  {{ user.email }}
                </span>

                <a
                  v-if="user.github_nickname"
                  :href="`https://github.com/${user.github_nickname}`"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="inline-flex items-center gap-1 font-medium text-indigo-600 transition-colors hover:text-indigo-500 dark:text-indigo-400"
                >
                  @{{ user.github_nickname }}
                  <ExternalLink class="h-3 w-3" />
                </a>
              </div>
            </div>

            <p class="pt-1 text-xs text-slate-400 dark:text-slate-500">
              To update your profile information, make changes on GitHub and re-login.
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Danger Zone Container -->
    <div
      class="rounded-xl border border-red-200 bg-red-50/20 p-6 dark:border-red-900/40 dark:bg-red-950/10"
    >
      <DeleteUser />
    </div>
  </div>
</template>
