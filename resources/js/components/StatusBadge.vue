<script setup lang="ts">
interface Props {
    status: string;
    size?: 'sm' | 'md';
}

const props = withDefaults(defineProps<Props>(), {
    size: 'md',
});

const statusConfig: Record<string, { label: string; class: string }> = {
    ativo: { label: 'Ativo', class: 'bg-emerald-500/15 text-emerald-700 border-emerald-500/20' },
    inativo: { label: 'Inativo', class: 'bg-gray-500/15 text-gray-700 border-gray-500/20' },
    trancado: { label: 'Trancado', class: 'bg-amber-500/15 text-amber-700 border-amber-500/20' },
    concluido: { label: 'Concluído', class: 'bg-blue-500/15 text-blue-700 border-blue-500/20' },
    ativa: { label: 'Ativa', class: 'bg-emerald-500/15 text-emerald-700 border-emerald-500/20' },
    cancelada: { label: 'Cancelada', class: 'bg-red-500/15 text-red-700 border-red-500/20' },
    trancada: { label: 'Trancada', class: 'bg-amber-500/15 text-amber-700 border-amber-500/20' },
    concluida: { label: 'Concluída', class: 'bg-blue-500/15 text-blue-700 border-blue-500/20' },
    planejada: { label: 'Planejada', class: 'bg-purple-500/15 text-purple-700 border-purple-500/20' },
    em_andamento: { label: 'Em Andamento', class: 'bg-emerald-500/15 text-emerald-700 border-emerald-500/20' },
    encerrada: { label: 'Encerrada', class: 'bg-gray-500/15 text-gray-700 border-gray-500/20' },
    voluntario: { label: 'Voluntário', class: 'bg-blue-500/15 text-blue-700 border-blue-500/20' },
    contratado: { label: 'Contratado', class: 'bg-primary/15 text-primary border-primary/20' },
    entrada: { label: 'Entrada', class: 'bg-emerald-500/15 text-emerald-700 border-emerald-500/20' },
    saida: { label: 'Saída', class: 'bg-red-500/15 text-red-700 border-red-500/20' },
};

const config = statusConfig[props.status] || { label: props.status, class: 'bg-gray-100 text-gray-700' };
</script>

<template>
    <span
        class="inline-flex items-center rounded-full border font-medium"
        :class="[
            config.class,
            size === 'sm' ? 'px-2 py-0.5 text-xs' : 'px-3 py-1 text-xs',
        ]"
    >
        {{ config.label }}
    </span>
</template>
