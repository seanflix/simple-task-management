<script setup>
import { Form } from '@inertiajs/vue3';

const { title, titleId, form, initialName, submitLabel } = defineProps({
    title: String,
    titleId: String,
    form: Object,
    initialName: String,
    submitLabel: String,
});

const emit = defineEmits(['success', 'cancel']);

const cancel = () => {
    emit('cancel');
};
</script>

<template>
    <div class="flex flex-col gap-4">
        <h2 :id="titleId" class="text-lg font-medium">
            {{ title }}
        </h2>
        <Form
            v-bind="form"
            reset-on-success
            class="flex flex-col gap-4"
            @success="emit('success')"
            #default="{ errors, processing }"
        >
            <div class="flex flex-col gap-2">
                <label for="project-name" class="text-sm font-medium"
                    >Name</label
                >
                <input
                    id="project-name"
                    v-focus
                    name="name"
                    type="text"
                    required
                    maxlength="255"
                    :value="initialName"
                    class="h-10 w-full rounded-lg border border-stone-200 bg-stone-50 px-3 text-sm outline-none focus:border-stone-900"
                />
                <p v-if="errors.name" class="text-sm text-red-600">
                    {{ errors.name }}
                </p>
            </div>
            <div class="flex justify-end gap-2">
                <button
                    type="button"
                    class="inline-flex h-10 items-center justify-center rounded-lg border border-stone-300 bg-white px-4 text-sm font-medium text-stone-900 transition-colors hover:bg-stone-100 focus-visible:ring-2 focus-visible:ring-stone-900 focus-visible:ring-offset-2 focus-visible:ring-offset-white"
                    @click="cancel"
                >
                    Cancel
                </button>
                <button
                    type="submit"
                    class="inline-flex h-10 items-center justify-center rounded-lg bg-stone-900 px-4 text-sm font-medium text-white transition-colors hover:bg-stone-800 focus-visible:ring-2 focus-visible:ring-stone-900 focus-visible:ring-offset-2 focus-visible:ring-offset-white disabled:cursor-not-allowed disabled:opacity-50"
                    :disabled="processing"
                >
                    {{ processing ? 'Saving...' : submitLabel }}
                </button>
            </div>
        </Form>
    </div>
</template>
