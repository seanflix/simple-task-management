<script setup>
import { Form, Head, router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import DeleteConfirmation from '@/components/DeleteConfirmation.vue';
import Modal from '@/components/Modal.vue';
import ProjectForm from '@/components/ProjectForm.vue';

const { projects, selectedProjectId, tasks } = defineProps({
    projects: Array,
    selectedProjectId: Number,
    tasks: Array,
});

const items = ref([]);
const draggedId = ref(null);
const editingId = ref(null);
const reorderError = ref('');
const taskToDelete = ref(null);
const editingProject = ref(false);
const addingProject = ref(false);
const projectToDelete = ref(false);

const editForm = useForm({
    name: '',
});

watch(
    () => tasks,
    (tasks) => {
        items.value = tasks.map((task) => ({ ...task }));
    },
    { immediate: true },
);

const selectProject = (event) => {
    router.get('/', { project: event.target.value }, { preserveScroll: true });
};

const selectedProjectName = () => {
    const project = projects.find((item) => item.id === selectedProjectId);

    if (!project) return '';

    return project.name;
};

const startProjectEdit = () => {
    if (selectedProjectId == null) return;
    editingProject.value = true;
};

const deleteProject = () => {
    projectToDelete.value = true;
};

const cancelProjectDelete = () => {
    projectToDelete.value = false;
};

const confirmProjectDelete = () => {
    router.delete(`/projects/${selectedProjectId}`, {
        onFinish: () => {
            projectToDelete.value = false;
            editingProject.value = false;
        },
    });
};

const projectDeleteMessage = () => {
    const project = projects.find((item) => item.id === selectedProjectId);

    if (!project) return '';

    return `Delete "${project.name}"? Its tasks will be deleted too. This cannot be undone.`;
};

const formatTimestamp = (value) => {
    return new Intl.DateTimeFormat(undefined, {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    }).format(new Date(value));
};

const startEdit = (task) => {
    editingId.value = task.id;
    editForm.name = task.name;
    editForm.clearErrors();
};

const cancelEdit = () => {
    editingId.value = null;
    editForm.reset();
    editForm.clearErrors();
};

const saveEdit = (task) => {
    editForm.patch(`/tasks/${task.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            editingId.value = null;
            editForm.reset();
        },
    });
};

const deleteTask = (task) => {
    taskToDelete.value = task;
};

const cancelDelete = () => {
    taskToDelete.value = null;
};

const confirmDelete = () => {
    router.delete(`/tasks/${taskToDelete.value.id}`, {
        preserveScroll: true,
        onFinish: () => {
            taskToDelete.value = null;
        },
    });
};

const taskDeleteMessage = () => {
    if (!taskToDelete.value) return '';
    return `Delete "${taskToDelete.value.name}"? This cannot be undone.`;
};

const onDragStart = (taskId, event) => {
    draggedId.value = taskId;

    if (event.dataTransfer) {
        event.dataTransfer.effectAllowed = 'move';
        event.dataTransfer.setData('text/plain', String(taskId));
    }
};

const onDragOver = (targetId, event) => {
    event.preventDefault();

    if (event.dataTransfer) event.dataTransfer.dropEffect = 'move';
    if (draggedId.value === null || draggedId.value === targetId) return;

    const fromIndex = items.value.findIndex(
        (task) => task.id === draggedId.value,
    );
    const toIndex = items.value.findIndex((task) => task.id === targetId);

    if (fromIndex === -1 || toIndex === -1) return;

    const [moved] = items.value.splice(fromIndex, 1);

    if (!moved) return;

    items.value.splice(toIndex, 0, moved);
};

const onDragEnd = () => {
    const nextIds = items.value.map((task) => task.id);
    const previousIds = tasks.map((task) => task.id);
    const orderChanged = nextIds.some((id, index) => id !== previousIds[index]);

    draggedId.value = null;

    if (!orderChanged) return;

    items.value = items.value.map((task, index) => ({
        ...task,
        priority: index + 1,
    }));

    router.patch(
        '/tasks/reorder',
        { ids: nextIds },
        {
            preserveScroll: true,
            onSuccess: () => {
                reorderError.value = '';
            },
            onError: (errors) => {
                reorderError.value =
                    errors.ids ?? 'Could not save the new order.';
                items.value = tasks.map((task) => ({ ...task }));
            },
        },
    );
};
</script>

<template>
    <Head title="Tasks" />

    <div class="min-h-screen bg-stone-50 px-4 py-10 text-stone-900">
        <div class="mx-auto flex w-full max-w-xl flex-col gap-5">
            <header class="flex items-start justify-between gap-2">
                <div class="flex min-w-0 flex-col gap-2">
                    <div class="flex min-w-0 items-center gap-2">
                        <div
                            v-if="projects.length"
                            class="relative max-w-full min-w-0"
                        >
                            <select
                                id="project"
                                aria-label="Project"
                                class="w-full min-w-0 cursor-pointer appearance-none truncate rounded-lg border border-stone-200 bg-white py-2 pr-10 pl-4 text-xl font-semibold outline-none focus:border-stone-900"
                                :value="selectedProjectId ?? ''"
                                @change="selectProject"
                            >
                                <option
                                    v-for="project in projects"
                                    :key="project.id"
                                    :value="project.id"
                                >
                                    {{ project.name }}
                                </option>
                            </select>
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                stroke="currentColor"
                                class="pointer-events-none absolute top-1/2 right-3 size-4 -translate-y-1/2"
                                aria-hidden="true"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="m19.5 8.25-7.5 7.5-7.5-7.5"
                                />
                            </svg>
                        </div>
                        <button
                            type="button"
                            aria-label="Add project"
                            class="inline-flex h-10 shrink-0 items-center justify-center rounded-lg bg-stone-900 px-4 text-white transition-colors hover:bg-stone-800 focus-visible:ring-2 focus-visible:ring-stone-900 focus-visible:ring-offset-2 focus-visible:ring-offset-white disabled:cursor-not-allowed disabled:opacity-50"
                            @click="addingProject = true"
                        >
                            {{ projects.length ? '+' : 'Add project' }}
                        </button>
                    </div>
                </div>

                <div class="flex shrink-0 gap-2 self-center">
                    <button
                        type="button"
                        aria-label="Edit project"
                        class="inline-flex size-10 shrink-0 items-center justify-center rounded-lg border border-stone-300 bg-white text-stone-900 transition-colors hover:bg-stone-100 focus-visible:ring-2 focus-visible:ring-stone-900 focus-visible:ring-offset-2 focus-visible:ring-offset-white disabled:cursor-not-allowed disabled:opacity-50"
                        :disabled="selectedProjectId == null"
                        @click="startProjectEdit"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                            class="size-5"
                            aria-hidden="true"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125"
                            />
                        </svg>
                    </button>
                    <button
                        type="button"
                        aria-label="Delete project"
                        class="inline-flex size-10 shrink-0 items-center justify-center rounded-lg border border-red-600/25 bg-red-50 text-red-700 transition-colors hover:bg-red-100 focus-visible:ring-2 focus-visible:ring-red-600 focus-visible:ring-offset-2 focus-visible:ring-offset-white disabled:cursor-not-allowed disabled:opacity-50"
                        :disabled="selectedProjectId == null"
                        @click="deleteProject"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                            class="size-5"
                            aria-hidden="true"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"
                            />
                        </svg>
                    </button>
                </div>
            </header>

            <Form
                action="/tasks"
                method="post"
                reset-on-success
                class="flex flex-col gap-3 rounded-lg bg-white p-4 ring-1 ring-stone-200"
                #default="{ errors, processing }"
            >
                <label for="name" class="text-sm font-semibold"
                    >Create a new task</label
                >
                <input
                    type="hidden"
                    name="project_id"
                    :value="selectedProjectId"
                />
                <div class="flex flex-col gap-2 sm:flex-row">
                    <input
                        id="name"
                        name="name"
                        type="text"
                        required
                        maxlength="255"
                        placeholder="Task name"
                        class="h-10 w-full rounded-lg border border-stone-200 bg-stone-50 px-3 text-sm outline-none focus:border-stone-900 disabled:cursor-not-allowed disabled:opacity-50"
                        :disabled="selectedProjectId == null"
                    />
                    <button
                        type="submit"
                        class="inline-flex h-10 shrink-0 items-center justify-center rounded-lg bg-stone-900 px-4 text-sm font-medium text-white transition-colors hover:bg-stone-800 focus-visible:ring-2 focus-visible:ring-stone-900 focus-visible:ring-offset-2 focus-visible:ring-offset-white disabled:cursor-not-allowed disabled:opacity-50"
                        :disabled="processing || selectedProjectId == null"
                    >
                        {{ processing ? 'Adding...' : 'Add task' }}
                    </button>
                </div>
                <p v-if="errors.name" class="text-sm text-red-600">
                    {{ errors.name }}
                </p>
            </Form>

            <p class="text-sm text-stone-500">
                Drag a task to reorder. Priority #1 stays at the top.
            </p>

            <p v-if="reorderError" class="text-sm text-red-600">
                {{ reorderError }}
            </p>

            <p
                v-if="items.length === 0"
                class="rounded-lg border border-dashed border-stone-200 px-4 py-8 text-center text-sm text-stone-500"
            >
                No tasks yet. Add one above.
            </p>

            <ul v-else class="flex flex-col gap-2">
                <li
                    v-for="task in items"
                    :key="task.id"
                    :class="[
                        'group',
                        draggedId === task.id ? 'opacity-60' : '',
                    ]"
                    @dragover="onDragOver(task.id, $event)"
                >
                    <div
                        :class="[
                            'flex gap-3 rounded-lg bg-white p-3 ring-1 ring-stone-200 transition-shadow duration-200 group-hover:ring-stone-400',
                            editingId === task.id
                                ? 'items-start'
                                : 'items-center',
                        ]"
                    >
                        <button
                            type="button"
                            class="inline-flex size-10 shrink-0 cursor-grab items-center justify-center rounded-lg bg-transparent text-stone-500 transition-colors focus-visible:ring-2 focus-visible:ring-stone-900 focus-visible:ring-offset-2 focus-visible:ring-offset-white active:cursor-grabbing disabled:cursor-not-allowed disabled:opacity-50"
                            draggable="true"
                            aria-label="Drag to reorder"
                            :disabled="editingId === task.id"
                            @dragstart="onDragStart(task.id, $event)"
                            @dragend="onDragEnd"
                        >
                            <svg
                                class="h-4 w-4"
                                width="20"
                                height="20"
                                viewBox="0 0 24 24"
                                stroke-width="2"
                                stroke="currentColor"
                                fill="none"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                aria-hidden="true"
                            >
                                <path stroke="none" d="M0 0h24v24H0z" />
                                <circle cx="9" cy="5" r="1" />
                                <circle cx="9" cy="12" r="1" />
                                <circle cx="9" cy="19" r="1" />
                                <circle cx="15" cy="5" r="1" />
                                <circle cx="15" cy="12" r="1" />
                                <circle cx="15" cy="19" r="1" />
                            </svg>
                        </button>

                        <div
                            class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-stone-50 text-sm font-medium"
                        >
                            {{ task.priority }}
                        </div>

                        <div class="min-w-0 flex-1">
                            <form
                                v-if="editingId === task.id"
                                class="flex flex-col gap-2"
                                @submit.prevent="saveEdit(task)"
                            >
                                <input
                                    v-model="editForm.name"
                                    v-focus
                                    type="text"
                                    required
                                    maxlength="255"
                                    aria-label="Task name"
                                    class="h-10 w-full rounded-lg border border-stone-200 bg-stone-50 px-3 text-sm outline-none focus:border-stone-900"
                                />
                                <p
                                    v-if="editForm.errors.name"
                                    class="text-sm text-red-600"
                                >
                                    {{ editForm.errors.name }}
                                </p>
                                <div class="flex gap-2">
                                    <button
                                        type="submit"
                                        class="inline-flex h-8 shrink-0 items-center justify-center rounded-lg bg-stone-900 px-5 text-xs font-medium text-white transition-colors hover:bg-stone-800 focus-visible:ring-2 focus-visible:ring-stone-900 focus-visible:ring-offset-2 focus-visible:ring-offset-white disabled:cursor-not-allowed disabled:opacity-50"
                                        :disabled="editForm.processing"
                                    >
                                        Save
                                    </button>
                                    <button
                                        type="button"
                                        class="inline-flex h-8 shrink-0 items-center justify-center rounded-lg border border-stone-300 bg-white px-5 text-xs font-medium text-stone-900 transition-colors hover:bg-stone-100 focus-visible:ring-2 focus-visible:ring-stone-900 focus-visible:ring-offset-2 focus-visible:ring-offset-white disabled:cursor-not-allowed disabled:opacity-50"
                                        @click="cancelEdit"
                                    >
                                        Cancel
                                    </button>
                                </div>
                            </form>

                            <div v-else class="flex flex-col gap-2">
                                <div class="flex flex-col gap-1">
                                    <p class="font-medium wrap-break-word">
                                        {{ task.name }}
                                    </p>
                                    <p class="text-xs text-stone-500">
                                        Added
                                        {{ formatTimestamp(task.created_at) }}
                                    </p>
                                </div>
                                <div class="flex gap-2">
                                    <button
                                        type="button"
                                        class="inline-flex h-8 shrink-0 items-center justify-center rounded-lg border border-stone-300 bg-white px-5 text-xs font-medium text-stone-900 transition-colors hover:bg-stone-100 focus-visible:ring-2 focus-visible:ring-stone-900 focus-visible:ring-offset-2 focus-visible:ring-offset-white disabled:cursor-not-allowed disabled:opacity-50"
                                        @click="startEdit(task)"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        type="button"
                                        class="inline-flex h-8 shrink-0 items-center justify-center rounded-lg border border-red-600/25 bg-red-50 px-5 text-xs font-medium text-red-700 transition-colors hover:bg-red-100 focus-visible:ring-2 focus-visible:ring-red-600 focus-visible:ring-offset-2 focus-visible:ring-offset-white disabled:cursor-not-allowed disabled:opacity-50"
                                        @click="deleteTask(task)"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
            </ul>
        </div>

        <Modal
            :show="addingProject"
            labelled-by="add-project-title"
            @close="addingProject = false"
        >
            <ProjectForm
                title="Add project"
                title-id="add-project-title"
                :form="{ action: '/projects', method: 'post' }"
                initial-name=""
                submit-label="Add project"
                @success="addingProject = false"
                @cancel="addingProject = false"
            />
        </Modal>
        <Modal
            :show="editingProject"
            labelled-by="edit-project-title"
            @close="editingProject = false"
        >
            <ProjectForm
                title="Edit project"
                title-id="edit-project-title"
                :form="{
                    action: `/projects/${selectedProjectId}`,
                    method: 'patch',
                }"
                :initial-name="selectedProjectName()"
                submit-label="Save"
                @success="editingProject = false"
                @cancel="editingProject = false"
            />
        </Modal>
        <Modal
            :show="projectToDelete"
            labelled-by="delete-project-title"
            @close="cancelProjectDelete"
        >
            <DeleteConfirmation
                title="Delete project"
                title-id="delete-project-title"
                :message="projectDeleteMessage()"
                @cancel="cancelProjectDelete"
                @confirm="confirmProjectDelete"
            />
        </Modal>
        <Modal
            :show="taskToDelete !== null"
            labelled-by="delete-task-title"
            @close="cancelDelete"
        >
            <DeleteConfirmation
                title="Delete task"
                title-id="delete-task-title"
                :message="taskDeleteMessage()"
                @cancel="cancelDelete"
                @confirm="confirmDelete"
            />
        </Modal>
    </div>
</template>
