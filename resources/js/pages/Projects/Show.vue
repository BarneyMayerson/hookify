<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Save, AlertTriangle, Trash2 } from '@lucide/vue';
import { computed, ref } from 'vue';

import GitHubRepoPicker from '@/components/Projects/GitHubRepoPicker.vue';
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
import { Input } from '@/components/ui/input';
import { destroy, index } from '@/routes/projects';
import { update } from '@/routes/projects/rules';
import type { Project, Check } from '@/types';

const props = defineProps<{
  project: Project;
  catalog: Check[];
  enabledIds: string[];
}>();

const form = useForm({
  check_ids: [...props.enabledIds],
});

const groups = computed(() => {
  const byEcosystem: Record<string, Check[]> = { php: [], js: [], universal: [] };
  for (const check of props.catalog) {
    if (byEcosystem[check.ecosystem]) {
      byEcosystem[check.ecosystem].push(check);
    }
  }
  return byEcosystem;
});

const ecosystemMeta: Record<string, { label: string; description: string }> = {
  php: { label: 'PHP', description: 'Composer & PHP runtime tools' },
  js: { label: 'JS / TS', description: 'Node.js, npm, ESLint, Prettier & formatters' },
  universal: { label: 'Universal', description: 'Cross-language & repository structure checks' },
};

function submit() {
  form.put(update(props.project.id).url);
}

const isDeleteOpen = ref(false);
const confirmationText = ref('');
const isConfirmed = computed(() => confirmationText.value === props.project.name);
const deleteForm = useForm({});

function deleteProject() {
  deleteForm.delete(destroy(props.project.id).url);
}
</script>

<template>
  <div class="mx-auto max-w-7xl space-y-8 p-6">
    <!-- Header -->
    <div
      class="border-border flex flex-col gap-4 border-b pb-6 sm:flex-row sm:items-center sm:justify-between"
    >
      <div class="space-y-1">
        <Link
          :href="index().url"
          class="text-muted-foreground hover:text-foreground inline-flex items-center gap-1 text-xs font-medium transition-colors"
        >
          <ArrowLeft class="h-3.5 w-3.5" />
          Back to Projects
        </Link>
        <div class="flex flex-wrap items-center gap-3 pt-3">
          <h1 class="text-foreground text-2xl font-bold tracking-tight">
            {{ project.name }}
          </h1>
          <span
            class="border-border bg-muted text-muted-foreground rounded-md border px-2 py-1 font-mono text-xs"
          >
            Token prefix: {{ project.api_token_prefix }}…
          </span>
        </div>
      </div>

      <div class="flex items-center gap-3">
        <GitHubRepoPicker :project />

        <Button
          type="button"
          @click="submit"
          :disabled="form.processing || !form.isDirty"
          class="gap-2"
        >
          <Save class="h-4 w-4" />
          <span>{{ form.processing ? 'Saving...' : 'Save Rules' }}</span>
        </Button>
      </div>
    </div>

    <!-- Rules Selection -->
    <form @submit.prevent="submit" class="space-y-8">
      <div class="grid gap-6 lg:grid-cols-3">
        <div
          v-for="(checks, ecosystem) in groups"
          :key="ecosystem"
          v-show="checks.length"
          class="bg-card border-border flex flex-col overflow-hidden rounded-xl border shadow-sm"
        >
          <!-- Group Header -->
          <div class="border-border bg-muted/50 border-b p-4">
            <div class="flex items-center justify-between">
              <h2 class="text-foreground font-semibold">
                {{ ecosystemMeta[ecosystem]?.label ?? ecosystem }}
              </h2>
              <span
                class="bg-muted text-muted-foreground rounded-full px-2 py-0.5 text-xs font-medium"
              >
                {{ checks.filter((c) => form.check_ids.includes(c.id)).length }} /
                {{ checks.length }}
              </span>
            </div>
            <p class="text-muted-foreground mt-0.5 text-xs">
              {{ ecosystemMeta[ecosystem]?.description }}
            </p>
          </div>

          <!-- Group Items -->
          <div class="flex-1 space-y-3 p-4">
            <label
              v-for="check in checks"
              :key="check.id"
              :class="[
                'group relative flex cursor-pointer flex-col gap-2 rounded-lg border p-3.5 transition-all select-none',
                form.check_ids.includes(check.id)
                  ? 'border-primary/60 bg-primary/5 ring-primary/20 ring-1'
                  : 'border-border bg-card hover:border-muted-foreground/30',
              ]"
            >
              <div class="flex items-start gap-3">
                <input
                  v-model="form.check_ids"
                  type="checkbox"
                  :value="check.id"
                  class="border-border text-primary focus:ring-primary mt-0.5 h-4 w-4 rounded"
                />

                <div class="min-w-0 flex-1">
                  <div class="flex items-center justify-between gap-2">
                    <span
                      class="text-foreground group-hover:text-primary text-sm font-medium transition-colors"
                    >
                      {{ check.label }}
                    </span>
                  </div>

                  <div class="mt-1 flex flex-wrap gap-1.5">
                    <span
                      v-if="check.tier === 'secondary'"
                      class="bg-muted text-muted-foreground inline-flex items-center rounded px-1.5 py-0.5 text-[10px] font-medium"
                    >
                      community
                    </span>
                    <span
                      v-if="check.requiresConfig || check.requiresBinary"
                      class="inline-flex items-center gap-1 rounded border border-amber-500/30 bg-amber-500/10 px-1.5 py-0.5 text-[10px] font-medium text-amber-700 dark:text-amber-300"
                    >
                      <AlertTriangle class="h-2.5 w-2.5 shrink-0" />
                      {{ check.requiresBinary ? 'Needs extra setup' : 'Needs config file' }}
                    </span>
                  </div>
                </div>
              </div>

              <div
                v-if="
                  (check.requiresConfig || check.requiresBinary) &&
                  form.check_ids.includes(check.id)
                "
                class="mt-1 flex items-start gap-1.5 rounded-md border border-amber-500/20 bg-amber-500/10 p-2 text-xs text-amber-700 dark:text-amber-300"
              >
                <AlertTriangle
                  class="mt-0.5 h-3.5 w-3.5 shrink-0 text-amber-600 dark:text-amber-400"
                />
                <p class="text-[11px] leading-normal">
                  {{
                    check.requiresBinary
                      ? 'Requires standalone binary on dev machine (fails with "command not found" otherwise).'
                      : 'Requires configuration file in repo root (will fail on commit if missing).'
                  }}
                </p>
              </div>
            </label>
          </div>
        </div>
      </div>

      <!-- Sticky Save Bar -->
      <div
        v-if="form.isDirty"
        class="border-border bg-background/90 sticky bottom-6 flex items-center justify-between rounded-xl border p-4 shadow-lg backdrop-blur-md"
      >
        <div class="text-foreground flex items-center gap-2 text-sm">
          <span class="relative flex h-2 w-2">
            <span
              class="absolute inline-flex h-full w-full animate-ping rounded-full bg-amber-400 opacity-75"
            ></span>
            <span class="relative inline-flex h-2 w-2 rounded-full bg-amber-500"></span>
          </span>
          You have unsaved rule changes.
        </div>
        <div class="flex items-center gap-3">
          <Button variant="ghost" size="sm" type="button" @click="form.reset()"> Reset </Button>
          <Button type="submit" size="sm" :disabled="form.processing" class="gap-2">
            <Save class="h-3.5 w-3.5" />
            <span>{{ form.processing ? 'Saving...' : 'Save Rules' }}</span>
          </Button>
        </div>
      </div>
    </form>

    <!-- Danger Zone -->
    <div class="rounded-xl border border-red-500/30 bg-red-500/10 p-5">
      <h2 class="text-sm font-semibold text-red-700 dark:text-red-300">Danger zone</h2>
      <p class="mt-1 text-xs text-red-600 dark:text-red-400">
        Deleting a project revokes its token immediately. The next time anyone runs
        <code class="font-mono">hookify sync</code> for it, the CLI will report the invalid token
        and remove the hooks it installed. Until then, hooks already installed on a machine keep
        running locally — they don't contact Hookify on their own.
      </p>

      <Dialog v-model:open="isDeleteOpen">
        <DialogTrigger as-child>
          <Button
            variant="outline"
            size="sm"
            class="mt-3 gap-1.5 border-red-500/40 text-red-700 hover:bg-red-500/10 hover:text-red-800 dark:text-red-400"
            @click="confirmationText = ''"
          >
            <Trash2 class="size-4" />
            Delete project
          </Button>
        </DialogTrigger>

        <DialogContent>
          <DialogHeader>
            <DialogTitle>Delete "{{ project.name }}"?</DialogTitle>
            <DialogDescription>
              This permanently deletes the project, its token, and all of its check configuration.
              Type
              <strong class="font-mono">{{ project.name }}</strong>
              below to confirm.
            </DialogDescription>
          </DialogHeader>

          <Input v-model="confirmationText" autocomplete="off" :placeholder="project.name" />

          <DialogFooter class="gap-2">
            <Button variant="secondary" @click="isDeleteOpen = false">Cancel</Button>
            <Button
              variant="destructive"
              :disabled="!isConfirmed || deleteForm.processing"
              @click="deleteProject"
            >
              {{ deleteForm.processing ? 'Deleting...' : 'Delete project' }}
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  </div>
</template>
