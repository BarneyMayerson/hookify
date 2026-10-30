<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { GitBranch, Loader2, Check, Unlink, AlertCircle } from '@lucide/vue';
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
import { repositories } from '@/routes/github';
import { update } from '@/routes/projects/repository';
import type { Project, GitHubRepository } from '@/types';

const props = defineProps<{
  project: Project;
}>();

const isOpen = ref(false);
const isLoading = ref(false);
const isSaving = ref(false);
const errorMessage = ref<string | null>(null);
const repoList = ref<GitHubRepository[]>([]);

async function fetchRepositories() {
  if (repoList.value.length > 0) return;

  isLoading.value = true;
  errorMessage.value = null;

  try {
    const response = await fetch(repositories().url, {
      headers: {
        Accept: 'application/json',
      },
    });

    const data = await response.json();

    if (!response.ok) {
      errorMessage.value = data.message || 'Failed to fetch repositories.';
      return;
    }

    repoList.value = data.repositories;
  } catch {
    errorMessage.value = 'Network error while fetching repositories. Please try again.';
  } finally {
    isLoading.value = false;
  }
}

function handleOpenChange(open: boolean) {
  isOpen.value = open;
  if (open) {
    fetchRepositories();
  }
}

function linkRepository(selectedRepo: GitHubRepository | null) {
  isSaving.value = true;

  router.patch(
    update(props.project.id).url,
    {
      github_repo_id: selectedRepo ? selectedRepo.id : null,
      github_repo_full_name: selectedRepo ? selectedRepo.full_name : null,
    },
    {
      onSuccess: () => {
        isOpen.value = false;
      },
      onFinish: () => {
        isSaving.value = false;
      },
    },
  );
}
</script>

<template>
  <Dialog :open="isOpen" @update:open="handleOpenChange">
    <DialogTrigger as-child>
      <Button variant="outline" size="sm" class="gap-2">
        <GitBranch class="text-muted-foreground size-4" />
        <span v-if="project.github_repo_full_name" class="font-mono text-xs">
          {{ project.github_repo_full_name }}
        </span>
        <span v-else class="text-muted-foreground">Link GitHub</span>
      </Button>
    </DialogTrigger>

    <DialogContent class="sm:max-w-120">
      <DialogHeader>
        <DialogTitle class="flex items-center gap-2">
          <GitBranch class="text-primary size-5" />
          Link GitHub Repository
        </DialogTitle>
        <DialogDescription>
          Select a repository to bind with project <strong>{{ project.name }}</strong
          >.
        </DialogDescription>
      </DialogHeader>

      <!-- Loading State -->
      <div
        v-if="isLoading"
        class="text-muted-foreground flex flex-col items-center justify-center py-8"
      >
        <Loader2 class="text-primary mb-2 size-8 animate-spin" />
        <p class="text-sm">Fetching repositories from GitHub...</p>
      </div>

      <!-- Error State -->
      <div
        v-else-if="errorMessage"
        class="rounded-lg border border-amber-500/30 bg-amber-500/10 p-4"
      >
        <div class="flex items-start gap-3">
          <AlertCircle class="mt-0.5 size-5 shrink-0 text-amber-600 dark:text-amber-400" />
          <div class="text-sm">
            <p class="font-medium text-amber-800 dark:text-amber-300">{{ errorMessage }}</p>
            <p class="mt-1 text-amber-700 dark:text-amber-400">
              Please reconnect your GitHub account to restore access.
            </p>
          </div>
        </div>
      </div>

      <!-- Repository Picker List -->
      <div v-else class="my-2 space-y-3">
        <!-- Currently Linked Repository Banner -->
        <div
          v-if="project.github_repo_full_name"
          class="border-border bg-muted/50 flex items-center justify-between rounded-md border p-3"
        >
          <div class="flex min-w-0 items-center gap-2">
            <GitBranch class="text-primary size-4 shrink-0" />
            <span class="truncate font-mono text-xs font-semibold">
              {{ project.github_repo_full_name }}
            </span>
          </div>
          <Button
            variant="ghost"
            size="sm"
            class="h-8 gap-1.5 text-xs text-red-600 hover:bg-red-500/10 hover:text-red-700"
            :disabled="isSaving"
            @click="linkRepository(null)"
          >
            <Unlink class="size-3.5" />
            Unlink
          </Button>
        </div>

        <div
          class="border-border divide-border max-h-65 space-y-1 divide-y overflow-y-auto rounded-md border"
        >
          <button
            v-for="repo in repoList"
            :key="repo.id"
            type="button"
            class="hover:bg-accent/50 flex w-full items-center justify-between p-2.5 text-left text-sm transition-colors"
            :class="{
              'bg-primary/5': project.github_repo_id === repo.id,
            }"
            :disabled="isSaving"
            @click="linkRepository(repo)"
          >
            <div class="flex items-center gap-2 truncate pr-2">
              <span class="truncate font-mono text-xs">{{ repo.full_name }}</span>
              <span
                v-if="repo.private"
                class="bg-muted text-muted-foreground rounded px-1.5 py-0.5 text-[10px]"
              >
                private
              </span>
            </div>
            <Check v-if="project.github_repo_id === repo.id" class="text-primary size-4 shrink-0" />
          </button>
        </div>
      </div>

      <DialogFooter>
        <Button variant="outline" @click="isOpen = false">Close</Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>
