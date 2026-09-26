<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
  Dialog,
  DialogClose,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { AlertTriangle } from '@lucide/vue';

const confirmationText = ref('');
const isConfirmed = computed(() => confirmationText.value === 'DELETE');
</script>

<template>
  <div class="space-y-4">
    <div class="flex items-start justify-between gap-4">
      <div class="space-y-1">
        <div class="flex items-center gap-2 text-base font-semibold text-red-600 dark:text-red-400">
          <AlertTriangle class="h-5 w-5 shrink-0" />
          <span>Delete Account</span>
        </div>
        <p class="text-xs text-slate-600 dark:text-slate-400">
          Permanently remove your account, projects, rules, and active API tokens. This action is
          irreversible.
        </p>
      </div>

      <Dialog>
        <DialogTrigger as-child>
          <Button
            variant="destructive"
            data-test="delete-user-button"
            @click="confirmationText = ''"
          >
            Delete account
          </Button>
        </DialogTrigger>

        <DialogContent>
          <Form
            v-bind="ProfileController.destroy.form()"
            reset-on-success
            :options="{
              preserveScroll: true,
            }"
            class="space-y-6"
            v-slot="{ errors, processing, reset, clearErrors }"
          >
            <DialogHeader class="space-y-3">
              <DialogTitle>Are you sure you want to delete your account?</DialogTitle>
              <DialogDescription class="text-sm text-slate-500 dark:text-slate-400">
                Once your account is deleted, all of its resources, projects, and rules will be
                permanently deleted. Please type
                <span class="font-mono font-bold text-slate-900 dark:text-slate-100">DELETE</span>
                below to confirm.
              </DialogDescription>
            </DialogHeader>

            <div class="grid gap-2">
              <Label for="confirmation" class="sr-only">Type DELETE to confirm</Label>
              <Input
                id="confirmation"
                v-model="confirmationText"
                autocomplete="off"
                placeholder="Type DELETE to confirm"
              />
              <InputError
                :message="errors.confirmation || errors.password || Object.values(errors)[0]"
              />
            </div>

            <DialogFooter class="gap-2 sm:gap-0">
              <DialogClose as-child>
                <Button
                  type="button"
                  variant="outline"
                  @click="
                    () => {
                      clearErrors();
                      reset();
                      confirmationText = '';
                    }
                  "
                >
                  Cancel
                </Button>
              </DialogClose>

              <Button
                type="submit"
                variant="destructive"
                :disabled="processing || !isConfirmed"
                data-test="confirm-delete-user-button"
              >
                Permanently delete account
              </Button>
            </DialogFooter>
          </Form>
        </DialogContent>
      </Dialog>
    </div>
  </div>
</template>
