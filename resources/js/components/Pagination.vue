<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

defineProps<{
    links: PaginationLink[];
}>();

const formatLabel = (label: string) => {
    if (label.includes('pagination.previous') || label.includes('Previous') || label.includes('&laquo;')) {
        return `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6"><path stroke-linecap="round" stroke-linejoin="round" d="m11.25 9-3 3m0 0 3 3m-3-3h7.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>`;
    }
    if (label.includes('pagination.next') || label.includes('Next') || label.includes('&raquo;')) {
        return `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6"><path stroke-linecap="round" stroke-linejoin="round" d="m12.75 15 3-3m0 0-3-3m3 3h-7.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>`;
    }
    return label;
};
</script>

<template>
    <nav v-if="links.length > 3" class="flex items-center justify-center gap-1 py-4">
        <template v-for="link in links" :key="link.label">
            <Link
                v-if="link.url"
                :href="link.url"
                class="inline-flex h-9 min-w-[36px] items-center justify-center rounded-lg px-3 text-sm font-medium transition-colors"
                :class="[
                    link.active
                        ? 'bg-primary text-primary-foreground shadow-sm'
                        : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground',
                ]"
                v-html="formatLabel(link.label)"
                preserve-scroll
            />
            <span
                v-else
                class="inline-flex h-9 min-w-[36px] items-center justify-center rounded-lg px-3 text-sm text-muted-foreground/50"
                v-html="formatLabel(link.label)"
            />
        </template>
    </nav>
</template>
