<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { watch } from 'vue';
import { useSwal } from '@/composables/useSwal';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const page = usePage();
const { toastSuccess, toastError, toastInfo } = useSwal();

watch(
    () => page.props.flash as any,
    (flash) => {
        if (flash?.success) toastSuccess(flash.success);
        if (flash?.error) toastError(flash.error);
        if (flash?.info) toastInfo(flash.info);
    },
    { deep: true, immediate: true }
);
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <slot />
    </AppLayout>
</template>
