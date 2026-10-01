<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { ShieldCheck, Clock, ArrowRight } from '@lucide/vue';
import GithubRepoPicker from '@/components/Projects/GithubRepoPicker.vue';
import { Button } from '@/components/ui/button';
import { show } from '@/routes/projects';
import type { ProjectSummary } from '@/types';

const props = defineProps<{
  project: ProjectSummary;
}>();

const rulesCount = computed(() => props.project.enabledIds?.length ?? 0);
</script>

<template>
  <div
    class="bg-card flex flex-col justify-between rounded-xl border p-5 shadow-sm transition-all hover:shadow-md"
  >
    <div class="space-y-3">
      <!-- Header Info -->
      <div class="flex items-start justify-between gap-2">
        <div>
          <h3 class="leading-none font-semibold">{{ project.name }}</h3>
          <p class="mt-1 font-mono text-[11px] text-slate-400">
            token: {{ project.api_token_prefix }}...
          </p>
        </div>
      </div>

      <!-- Stats & Last Synced Info -->
      <div class="flex flex-wrap items-center gap-2 pt-2 text-xs text-slate-500">
        <div class="flex items-center gap-1">
          <ShieldCheck class="size-3.5 text-indigo-500" />
          <span>Rules: {{ rulesCount }}</span>
        </div>
        <span>•</span>
        <div class="flex items-center gap-1">
          <Clock class="size-3.5 text-slate-400" />
          <span>{{ project.last_synced_at || 'Never synced' }}</span>
        </div>
      </div>
    </div>

    <!-- Bottom Bar: GitHub Repo Picker & Link to Project Details -->
    <div class="mt-5 flex items-center justify-between border-t pt-4">
      <GithubRepoPicker :project="project" />

      <Button as-child variant="ghost" size="sm" class="gap-1 text-xs">
        <Link :href="show(project.id).url">
          View Rules
          <ArrowRight class="size-3.5" />
        </Link>
      </Button>
    </div>
  </div>
</template>
