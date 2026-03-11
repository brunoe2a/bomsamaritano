<script setup lang="ts">
import type { Component } from 'vue';

interface Props {
    title: string;
    value: string | number;
    description?: string;
    icon?: Component | string;
    trend?: 'up' | 'down' | 'neutral';
    color?: 'primary' | 'success' | 'warning' | 'danger' | 'info';
}

const props = withDefaults(defineProps<Props>(), {
    trend: 'neutral',
    color: 'primary',
});

const colorClasses: Record<string, string> = {
    primary: 'bg-primary/10 text-primary',
    success: 'bg-emerald-500/10 text-emerald-600',
    warning: 'bg-amber-500/10 text-amber-600',
    danger: 'bg-red-500/10 text-red-600',
    info: 'bg-blue-500/10 text-blue-600',
};
</script>

<template>
    <div
        class="relative overflow-hidden rounded-xl border border-border bg-card p-6 shadow-sm transition-shadow hover:shadow-md"
    >
        <div class="flex items-start justify-between">
            <div class="space-y-2">
                <p class="text-sm font-medium text-muted-foreground">{{ title }}</p>
                <p class="text-3xl font-bold tracking-tight text-foreground">{{ value }}</p>
                <p v-if="description" class="text-xs text-muted-foreground">{{ description }}</p>
            </div>
            <div
                v-if="icon"
                class="flex h-12 w-12 items-center justify-center rounded-xl"
                :class="colorClasses[color]"
            >
                <component v-if="typeof icon === 'object' || typeof icon === 'function'" :is="icon" class="h-6 w-6" />
                <span v-else class="text-2xl">{{ icon }}</span>
            </div>
        </div>
        <div
            class="absolute bottom-0 left-0 h-1 w-full"
            :class="{
                'bg-primary': color === 'primary',
                'bg-emerald-500': color === 'success',
                'bg-amber-500': color === 'warning',
                'bg-red-500': color === 'danger',
                'bg-blue-500': color === 'info',
            }"
        ></div>
    </div>
</template>
