<script setup>
import { onUnmounted, watch } from 'vue';

const { show, labelledBy } = defineProps({
    show: Boolean,
    labelledBy: String,
});

const emit = defineEmits(['close']);

const close = () => {
    emit('close');
};

const onKeydown = (event) => {
    if (event.key === 'Escape') {
        close();
    }
};

watch(
    () => show,
    (isShown) => {
        if (isShown) {
            window.addEventListener('keydown', onKeydown);
            return;
        }

        window.removeEventListener('keydown', onKeydown);
    },
);

onUnmounted(() => {
    window.removeEventListener('keydown', onKeydown);
});
</script>

<template>
    <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center bg-stone-900/40 p-4"
        @click.self="close"
    >
        <div
            role="dialog"
            aria-modal="true"
            :aria-labelledby="labelledBy"
            class="flex w-full max-w-sm flex-col gap-4 rounded-lg bg-white p-5 ring-1 ring-stone-200"
        >
            <slot />
        </div>
    </div>
</template>
