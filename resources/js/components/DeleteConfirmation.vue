<script setup>
const { title, titleId, message } = defineProps({
    title: String,
    titleId: String,
    message: String,
});

const emit = defineEmits(['confirm', 'cancel']);

const cancel = () => {
    emit('cancel');
};

const confirm = () => {
    emit('confirm');
};
</script>

<template>
    <div class="flex flex-col gap-4">
        <div class="flex flex-col gap-1">
            <h2 :id="titleId" class="text-lg font-medium">
                {{ title }}
            </h2>
            <p class="text-sm text-stone-500">
                {{ message }}
            </p>
        </div>
        <div class="flex justify-end gap-2">
            <button
                v-focus
                type="button"
                class="inline-flex h-10 items-center justify-center rounded-lg border border-stone-300 bg-white px-4 text-sm font-medium text-stone-900 transition-colors hover:bg-stone-100 focus-visible:ring-2 focus-visible:ring-stone-900 focus-visible:ring-offset-2 focus-visible:ring-offset-white"
                @click="cancel"
            >
                Cancel
            </button>
            <button
                type="button"
                class="inline-flex h-10 items-center justify-center rounded-lg border border-red-600/25 bg-red-50 px-4 text-sm font-medium text-red-700 transition-colors hover:bg-red-100 focus-visible:ring-2 focus-visible:ring-red-600 focus-visible:ring-offset-2 focus-visible:ring-offset-white"
                @click="confirm"
            >
                Delete
            </button>
        </div>
    </div>
</template>
