<template>
    <div class="relative" ref="rootRef">
        <button
            type="button"
            @click="toggle"
            :disabled="disabled"
            class="flex w-full items-center justify-between rounded-lg border border-border bg-background px-3 py-2 text-sm transition-colors focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary disabled:cursor-not-allowed disabled:opacity-50"
            :class="error ? 'border-red-500 ring-red-500/30' : ''"
        >
            <span v-if="selectedLabel" class="truncate text-foreground">{{ selectedLabel }}</span>
            <span v-else class="truncate text-muted-foreground">{{ placeholder }}</span>
            <svg
                class="ml-2 h-4 w-4 flex-shrink-0 text-muted-foreground transition-transform duration-150"
                :class="open ? 'rotate-180' : ''"
                fill="none" stroke="currentColor" viewBox="0 0 24 24"
            >
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
            <div
                v-if="open"
                class="absolute z-50 mt-1 w-full overflow-hidden rounded-lg border border-border bg-card shadow-lg"
            >
                <div class="px-2 py-2">
                    <div class="relative">
                        <svg class="absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input
                            ref="searchRef"
                            v-model="search"
                            type="text"
                            placeholder="Pesquisar..."
                            class="w-full rounded border border-border bg-background py-1.5 pl-8 pr-3 text-sm outline-none transition-colors placeholder:text-muted-foreground focus:border-primary focus:ring-1 focus:ring-primary"
                            @keydown.escape="close"
                            @keydown.enter.prevent="selectFirst"
                        />
                    </div>
                </div>

                <ul class="max-h-60 overflow-y-auto py-1" role="listbox">
                    <li
                        v-if="allowEmpty"
                        @click="select('')"
                        class="cursor-pointer px-3.5 py-2 text-sm text-muted-foreground transition-colors hover:bg-muted"
                        :class="modelValue === '' ? 'bg-primary/10 font-medium text-primary' : ''"
                    >
                        {{ emptyLabel || 'Nenhum' }}
                    </li>

                    <li
                        v-for="option in filteredOptions"
                        :key="String(option.value)"
                        @click="select(option.value)"
                        class="group flex cursor-pointer items-center justify-between px-3.5 py-2 text-sm transition-colors"
                        :class="modelValue === option.value
                            ? 'bg-primary/10 font-semibold text-primary'
                            : 'text-foreground hover:bg-muted'"
                        role="option"
                    >
                        <div class="flex min-w-0 items-center gap-2">
                            <slot name="option-prefix" :option="option" />
                            <span class="truncate">{{ option.label }}</span>
                            <span v-if="option.hint" class="ml-1 truncate text-xs text-muted-foreground">{{ option.hint }}</span>
                        </div>
                        <svg v-if="modelValue === option.value" class="ml-2 h-3.5 w-3.5 flex-shrink-0 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                    </li>

                    <li v-if="filteredOptions.length === 0" class="select-none px-3.5 py-5 text-center text-sm text-muted-foreground">
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
    value: string | number;
    label: string;
    hint?: string;
}

const props = defineProps<{
    modelValue: string | number | null;
    options: Option[];
    placeholder?: string;
    allowEmpty?: boolean;
    emptyLabel?: string;
    disabled?: boolean;
    error?: boolean;
}>();

const emit = defineEmits<{ 'update:modelValue': [value: string | number] }>();

const open = ref(false);
const search = ref('');
const rootRef = ref<HTMLElement | null>(null);
const searchRef = ref<HTMLInputElement | null>(null);

const selectedLabel = computed(() => props.options.find(o => o.value === props.modelValue)?.label || '');

const filteredOptions = computed(() => {
    const q = search.value.trim().toLowerCase();
    if (!q) return props.options;
    return props.options.filter(o =>
        o.label.toLowerCase().includes(q) ||
        (o.hint ?? '').toLowerCase().includes(q)
    );
});

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

function select(value: string | number) {
    emit('update:modelValue', value);
    close();
}

function selectFirst() {
    if (filteredOptions.value.length > 0) select(filteredOptions.value[0].value);
}

function handleClickOutside(e: MouseEvent) {
    if (rootRef.value && !rootRef.value.contains(e.target as Node)) close();
}

onMounted(() => document.addEventListener('mousedown', handleClickOutside));
onUnmounted(() => document.removeEventListener('mousedown', handleClickOutside));
</script>
