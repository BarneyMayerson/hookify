<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { GitBranch, Loader2, Check, Unlink, AlertCircle } from '@lucide/vue';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
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
        <GitBranch class="size-4 text-slate-500" />
        <span v-if="project.github_repo_full_name" class="font-mono text-xs">
          {{ project.github_repo_full_name }}
        </span>
        <span v-else class="text-slate-500">Link GitHub</span>
      </Button>
    </DialogTrigger>

    <DialogContent class="sm:max-w-120">
      <DialogHeader>
        <DialogTitle class="flex items-center gap-2">
          <GitBranch class="size-5 text-indigo-600 dark:text-indigo-400" />
          Link GitHub Repository
        </DialogTitle>
        <DialogDescription>
          Select a repository to bind with project <strong>{{ project.name }}</strong
          >.
        </DialogDescription>
      </DialogHeader>

      <!-- Loading State -->
      <div v-if="isLoading" class="flex flex-col items-center justify-center py-8 text-slate-500">
        <Loader2 class="mb-2 size-8 animate-spin text-indigo-600" />
        <p class="text-sm">Fetching repositories from GitHub...</p>
      </div>

      <!-- Error State -->
      <div
        v-else-if="errorMessage"
        class="rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-900/50 dark:bg-amber-950/20"
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
          class="flex items-center justify-between rounded-md border bg-slate-50 p-3 dark:bg-slate-900"
        >
          <div class="flex min-w-0 items-center gap-2">
            <GitBranch class="size-4 shrink-0 text-indigo-600" />
            <span class="truncate font-mono text-xs font-semibold">
              {{ project.github_repo_full_name }}
            </span>
          </div>
          <Button
            variant="ghost"
            size="sm"
            class="h-8 gap-1.5 text-xs text-red-600 hover:bg-red-50 hover:text-red-700 dark:hover:bg-red-950/30"
            :disabled="isSaving"
            @click="linkRepository(null)"
          >
            <Unlink class="size-3.5" />
            Unlink
          </Button>
        </div>

        <div
          class="max-h-65 space-y-1 divide-y divide-slate-100 overflow-y-auto rounded-md border dark:divide-slate-800"
        >
          <button
            v-for="repo in repoList"
            :key="repo.id"
            type="button"
            class="flex w-full items-center justify-between p-2.5 text-left text-sm transition-colors hover:bg-slate-50 dark:hover:bg-slate-800/50"
            :class="{
              'bg-indigo-50/50 dark:bg-indigo-950/20': project.github_repo_id === repo.id,
            }"
            :disabled="isSaving"
            @click="linkRepository(repo)"
          >
            <div class="flex items-center gap-2 truncate pr-2">
              <span class="truncate font-mono text-xs">{{ repo.full_name }}</span>
              <span
                v-if="repo.private"
                class="rounded bg-slate-100 px-1.5 py-0.5 text-[10px] text-slate-600 dark:bg-slate-800 dark:text-slate-400"
              >
                private
              </span>
            </div>
            <Check
              v-if="project.github_repo_id === repo.id"
              class="size-4 shrink-0 text-indigo-600"
            />
          </button>
        </div>
      </div>

      <DialogFooter>
        <Button variant="outline" @click="isOpen = false">Close</Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>
