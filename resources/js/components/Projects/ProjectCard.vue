<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { ShieldCheck, Clock, Settings } from '@lucide/vue';
import GitHubRepoPicker from '@/components/Projects/GitHubRepoPicker.vue';
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
    class="bg-card border-border flex flex-col justify-between rounded-xl border p-5 shadow-sm transition-all hover:shadow-md"
  >
    <div class="space-y-3">
      <!-- Header Info -->
      <div class="flex items-start justify-between gap-2">
        <div>
          <h3 class="text-foreground leading-none font-semibold">{{ project.name }}</h3>
          <p class="text-muted-foreground mt-1 font-mono text-[11px]">
            token: {{ project.api_token_prefix }}...
          </p>
        </div>
      </div>

      <!-- Stats & Last Synced Info -->
      <div class="text-muted-foreground flex flex-wrap items-center gap-2 pt-2 text-xs">
        <div class="flex items-center gap-1">
          <ShieldCheck class="text-primary size-3.5" />
          <span>Rules: {{ rulesCount }}</span>
        </div>
        <span>•</span>
        <div class="flex items-center gap-1">
          <Clock class="text-muted-foreground size-3.5" />
          <span>{{ project.last_synced_at || 'Never synced' }}</span>
        </div>
      </div>
    </div>

    <!-- Bottom Bar -->
    <div class="border-border mt-5 flex items-center justify-between border-t pt-4">
      <GitHubRepoPicker :project />

      <Button as-child variant="ghost" size="sm" class="gap-1 text-xs">
        <Link :href="show(project.id).url">
          <Settings class="size-3.5" />
          Settings
        </Link>
      </Button>
    </div>
  </div>
</template>
