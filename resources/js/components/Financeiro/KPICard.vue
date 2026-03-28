<script setup lang="ts">
import { computed } from 'vue';
import { LucideIcon } from 'lucide-vue-next';

interface Props {
    title: string;
    value: number;
    icon: LucideIcon;
    variant?: 'emerald' | 'red' | 'blue' | 'slate';
}

const props = withDefaults(defineProps<Props>(), {
    variant: 'slate'
});

const formatCurrency = (v: number) => {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(v);
};

const variantClasses = computed(() => {
    switch (props.variant) {
        case 'emerald': return 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30';
        case 'red': return 'bg-red-100 text-red-600 dark:bg-red-900/30';
        case 'blue': return 'bg-blue-100 text-blue-600 dark:bg-blue-900/30';
        default: return 'bg-slate-100 text-slate-600 dark:bg-slate-900/30';
    }
});

const textClasses = computed(() => {
    switch (props.variant) {
        case 'emerald': return 'text-emerald-600';
        case 'red': return 'text-red-600';
        case 'blue': return 'text-blue-600';
        default: return 'text-foreground';
    }
});
</script>

<template>
    <div class="rounded-xl border border-border bg-card p-6 shadow-sm transition-all hover:shadow-md">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-muted-foreground uppercase tracking-widest">{{ title }}</p>
                <h3 class="mt-2 text-2xl font-bold" :class="textClasses">{{ formatCurrency(value) }}</h3>
            </div>
            <div class="rounded-full p-3 transition-transform hover:scale-110" :class="variantClasses">
                <component :is="icon" class="h-6 w-6" />
            </div>
        </div>
    </div>
</template>
