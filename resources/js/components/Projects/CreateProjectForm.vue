<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Plus, FolderPlus } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { store } from '@/routes/projects';

const form = useForm({
  name: '',
});

function submit() {
  form.post(store().url, {
    onSuccess: () => form.reset(),
  });
}
</script>

<template>
  <form
    class="bg-card flex items-center gap-3 rounded-xl border p-4 shadow-sm"
    @submit.prevent="submit"
  >
    <FolderPlus class="size-5 shrink-0 text-indigo-600" />
    <Input
      v-model="form.name"
      type="text"
      placeholder="New Project Name..."
      class="max-w-xs"
      :disabled="form.processing"
    />
    <Button
      type="submit"
      size="sm"
      class="gap-1.5"
      :disabled="form.processing || !form.name.trim()"
    >
      <Plus class="size-4" />
      Create Project
    </Button>
  </form>
</template>
