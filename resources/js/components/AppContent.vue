<script setup lang="ts">
import { computed } from 'vue';
import { SidebarInset } from '@/components/ui/sidebar';
import type { AppVariant } from '@/types';

type Props = {
    variant?: AppVariant;
    class?: string;
};

const props = withDefaults(defineProps<Props>(), {
    variant: 'sidebar',
});
const className = computed(() => props.class);
</script>

<template>
    <SidebarInset v-if="props.variant === 'sidebar'" :class="[className, 'flex flex-col']">
        <slot />
        <footer class="mt-auto py-6 pr-6 text-right text-xs text-muted-foreground border-t border-border/50 bg-card/5">
            © 2026 - {{ new Date().getFullYear() }} Desenvolvido por E2A Soluções Digitais - Versão 1.0.2
        </footer>
    </SidebarInset>
    <main
        v-else
        class="mx-auto flex h-full w-full max-w-7xl flex-1 flex-col gap-4 rounded-xl"
        :class="className"
    >
        <div class="flex-1">
            <slot />
        </div>
        <footer class="mt-auto py-6 pr-6 text-right text-xs text-muted-foreground border-t border-border/50 bg-card/5">
            © 2026 - {{ new Date().getFullYear() }} Desenvolvido por E2A Soluções Digitais - Versão 1.0.2
        </footer>
    </main>
</template>
