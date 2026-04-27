<template>
    <div class="relative" ref="rootRef">
        <button
            type="button"
            @click="toggle"
            :disabled="disabled"
            class="flex w-full items-center justify-between rounded-lg border border-border bg-background px-3 py-2 text-sm transition-colors focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary disabled:cursor-not-allowed disabled:opacity-50"
            :class="error ? 'border-red-500 ring-red-500/30' : ''"
        >
            <span v-if="displayLabel" class="truncate text-foreground">{{ displayLabel }}</span>
            <span v-else class="truncate text-muted-foreground">{{ placeholder }}</span>
            <svg class="ml-2 h-4 w-4 flex-shrink-0 text-muted-foreground transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <Transition
            enter-active-class="transition ease-out duration-150"
            enter-from-class="opacity-0 translate-y-1"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition ease-in duration-100"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 translate-y-1"
        >
            <div v-if="open" class="absolute z-50 mt-1 w-full overflow-hidden rounded-lg border border-border bg-card shadow-lg">
                <div class="px-2 py-2">
                    <div class="relative">
                        <svg class="absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input
                            ref="searchRef"
                            v-model="search"
                            type="text"
                            :placeholder="searchPlaceholder"
                            class="w-full rounded border border-border bg-background py-1.5 pl-8 pr-3 text-sm outline-none placeholder:text-muted-foreground focus:border-primary focus:ring-1 focus:ring-primary"
                            @keydown.escape="close"
                            @keydown.enter.prevent="onEnter"
                        />
                    </div>
                </div>

                <ul class="max-h-60 overflow-y-auto py-1" role="listbox">
                    <li
                        v-for="option in filteredOptions"
                        :key="option.value"
                        @click="selectExisting(option)"
                        class="flex cursor-pointer items-center justify-between px-3.5 py-2 text-sm transition-colors"
                        :class="isSelected(option) ? 'bg-primary/10 font-semibold text-primary' : 'text-foreground hover:bg-muted'"
                        role="option"
                    >
                        <span class="truncate">{{ option.label }}</span>
                        <svg v-if="isSelected(option)" class="ml-2 h-3.5 w-3.5 flex-shrink-0 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                    </li>

                    <li
                        v-if="canCreate"
                        @click="createNew"
                        class="flex cursor-pointer items-center gap-2 border-t border-border px-3.5 py-2 text-sm font-medium text-primary transition-colors hover:bg-primary/10"
                    >
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                        </svg>
                        {{ createLabelPrefix }} "{{ search.trim() }}"
                    </li>

                    <li v-if="!filteredOptions.length && !canCreate" class="select-none px-3.5 py-5 text-center text-sm text-muted-foreground">
                        Nenhum resultado encontrado.
                    </li>
                </ul>
            </div>
        </Transition>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, nextTick, onMounted, onUnmounted } from 'vue';

interface Option {
    value: number | string;
    label: string;
}

const props = withDefaults(defineProps<{
    modelValueId?: number | string | null;
    modelValueName?: string | null;
    options: Option[];
    placeholder?: string;
    searchPlaceholder?: string;
    createLabelPrefix?: string;
    disabled?: boolean;
    error?: boolean;
}>(), {
    placeholder: 'Selecione',
    searchPlaceholder: 'Pesquisar ou digitar para criar...',
    createLabelPrefix: 'Criar nova:',
});

const emit = defineEmits<{
    'update:modelValueId': [value: number | string | null];
    'update:modelValueName': [value: string | null];
}>();

const open = ref(false);
const search = ref('');
const rootRef = ref<HTMLElement | null>(null);
const searchRef = ref<HTMLInputElement | null>(null);

const displayLabel = computed(() => {
    if (props.modelValueId) {
        return props.options.find(o => o.value === props.modelValueId)?.label || props.modelValueName || '';
    }
    return props.modelValueName || '';
});

const filteredOptions = computed(() => {
    const q = search.value.trim().toLowerCase();
    if (!q) return props.options;
    return props.options.filter(o => o.label.toLowerCase().includes(q));
});

const canCreate = computed(() => {
    const q = search.value.trim();
    if (!q) return false;
    return !props.options.some(o => o.label.toLowerCase() === q.toLowerCase());
});

function isSelected(o: Option): boolean {
    if (props.modelValueId) return o.value === props.modelValueId;
    if (props.modelValueName) return o.label.toLowerCase() === props.modelValueName.toLowerCase();
    return false;
}

function toggle() {
    if (props.disabled) return;
    if (open.value) close();
    else {
        open.value = true;
        search.value = '';
        nextTick(() => searchRef.value?.focus());
    }
}

function close() {
    open.value = false;
    search.value = '';
}

function selectExisting(o: Option) {
    emit('update:modelValueId', o.value);
    emit('update:modelValueName', o.label);
    close();
}

function createNew() {
    const nome = search.value.trim();
    if (!nome) return;
    emit('update:modelValueId', null);
    emit('update:modelValueName', nome);
    close();
}

function onEnter() {
    if (filteredOptions.value.length > 0) {
        selectExisting(filteredOptions.value[0]);
    } else if (canCreate.value) {
        createNew();
    }
}

function handleClickOutside(e: MouseEvent) {
    if (rootRef.value && !rootRef.value.contains(e.target as Node)) close();
}

onMounted(() => document.addEventListener('mousedown', handleClickOutside));
onUnmounted(() => document.removeEventListener('mousedown', handleClickOutside));
</script>
