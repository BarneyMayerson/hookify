<script setup lang="ts">
import GitHubNotice from '@/components/GitHubNotice.vue';
import CreateProjectForm from '@/components/Projects/CreateProjectForm.vue';
import ProjectCard from '@/components/Projects/ProjectCard.vue';
import TokenBanner from '@/components/Projects/TokenBanner.vue';
import { dashboard } from '@/routes';
import { index as projectsIndex } from '@/routes/projects';
import type { ProjectSummary, CatalogEntry } from '@/types';

const props = defineProps<{
  projects: ProjectSummary[];
  catalog: CatalogEntry[];
  apiBase: string;
  openRepoPickerFor?: number | null;
}>();

defineOptions({
  layout: {
    breadcrumbs: [
      {
        title: 'Dashboard',
        href: dashboard().url,
      },
      {
        title: 'Projects',
        href: projectsIndex().url,
      },
    ],
  },
});
</script>

<template>
  <div class="space-y-6 p-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-foreground text-2xl font-bold tracking-tight">Projects</h1>
        <p class="text-muted-foreground text-sm">
          Manage Git hook configurations and repository bindings for your projects.
        </p>
      </div>
    </div>

    <GitHubNotice />

    <TokenBanner :api-base="apiBase" />

    <CreateProjectForm />

    <!-- Projects grid -->
    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
      <ProjectCard
        v-for="project in projects"
        :key="project.id"
        :project
        :open-picker="project.id === openRepoPickerFor"
      />
    </div>
  </div>
</template>
