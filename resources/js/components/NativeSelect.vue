<script setup lang="ts">
import { ChevronDownIcon } from 'lucide-vue-next';

defineOptions({ inheritAttrs: false });

const props = defineProps<{
    modelValue?: string | number | null;
    error?: boolean;
    disabled?: boolean;
}>();

const emit = defineEmits<{ 'update:modelValue': [value: string | number] }>();

function onChange(e: Event) {
    emit('update:modelValue', (e.target as HTMLSelectElement).value);
}
</script>

<template>
    <div class="relative w-full">
        <select
            v-bind="$attrs"
            :value="modelValue ?? ''"
            :disabled="disabled"
            @change="onChange"
            class="h-10 w-full appearance-none rounded-lg border bg-background px-3 pr-9 text-sm text-foreground outline-none transition-colors focus:border-primary focus:ring-1 focus:ring-primary disabled:cursor-not-allowed disabled:opacity-50"
            :class="error ? 'border-red-500 ring-red-500/30' : 'border-border'"
        >
            <slot />
        </select>
        <ChevronDownIcon class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground opacity-60" />
    </div>
</template>
