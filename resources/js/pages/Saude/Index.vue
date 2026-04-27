<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { HeartPulse, ListChecks, CalendarPlus, BadgeCheck, Plus, Stethoscope } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface Convocacao {
    id: number;
    titulo: string;
    data: string;
    local: string | null;
    status: string;
    alunos_count: number;
    programa: { id: number; nome: string; area: string } | null;
}

interface Atendimento {
    id: number;
    data_atendimento: string;
    profissional: string | null;
    aluno: { id: number; nome: string };
    programa: { id: number; nome: string; area: string };
}

defineProps<{
    totais: {
        programas_ativos: number;
        convocacoes_planejadas: number;
        atendimentos_total: number;
        atendimentos_mes: number;
    };
    proximas_convocacoes: Convocacao[];
    ultimos_atendimentos: Atendimento[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Núcleo de Saúde', href: '/saude' },
];

function formatData(d: string) {
    return new Date(d).toLocaleDateString('pt-BR');
}
</script>

<template>
    <Head title="Núcleo de Saúde" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="flex items-center gap-2 text-2xl font-bold text-foreground">
                        <HeartPulse class="h-6 w-6 text-rose-500" /> Núcleo de Saúde
                    </h1>
                    <p class="text-sm text-muted-foreground">Programas, convocações e atendimentos (psicologia, odontologia, médica, etc.)</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <Link href="/saude/convocacoes/create" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow-sm transition-colors hover:bg-primary/90">
                        <CalendarPlus class="h-4 w-4" /> Nova Convocação
                    </Link>
                    <Link href="/saude/programas" class="inline-flex items-center gap-2 rounded-lg border border-border px-4 py-2 text-sm font-medium transition-colors hover:bg-muted">
                        <Stethoscope class="h-4 w-4" /> Programas
                    </Link>
                </div>
            </div>

            <!-- Cards -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl border border-border bg-card p-5 shadow-sm">
                    <p class="text-xs font-medium uppercase text-muted-foreground">Programas Ativos</p>
                    <p class="mt-2 text-3xl font-bold text-foreground">{{ totais.programas_ativos }}</p>
                </div>
                <div class="rounded-xl border border-border bg-card p-5 shadow-sm">
                    <p class="text-xs font-medium uppercase text-muted-foreground">Convocações planejadas</p>
                    <p class="mt-2 text-3xl font-bold text-amber-500">{{ totais.convocacoes_planejadas }}</p>
                </div>
                <div class="rounded-xl border border-border bg-card p-5 shadow-sm">
                    <p class="text-xs font-medium uppercase text-muted-foreground">Atendimentos no mês</p>
                    <p class="mt-2 text-3xl font-bold text-emerald-500">{{ totais.atendimentos_mes }}</p>
                </div>
                <div class="rounded-xl border border-border bg-card p-5 shadow-sm">
                    <p class="text-xs font-medium uppercase text-muted-foreground">Atendimentos totais</p>
                    <p class="mt-2 text-3xl font-bold text-foreground">{{ totais.atendimentos_total }}</p>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Próximas Convocações -->
                <div class="rounded-xl border border-border bg-card p-6 shadow-sm">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="flex items-center gap-2 text-lg font-semibold text-foreground"><CalendarPlus class="h-5 w-5" /> Próximas Convocações</h2>
                        <Link href="/saude/convocacoes" class="text-xs font-medium text-primary hover:underline">Ver todas</Link>
                    </div>
                    <p v-if="!proximas_convocacoes.length" class="py-8 text-center text-sm text-muted-foreground">Nenhuma convocação planejada.</p>
                    <ul v-else class="divide-y divide-border">
                        <li v-for="c in proximas_convocacoes" :key="c.id" class="flex items-center justify-between gap-3 py-3">
                            <div>
                                <Link :href="`/saude/convocacoes/${c.id}`" class="font-medium text-foreground hover:underline">{{ c.titulo }}</Link>
                                <p class="text-xs text-muted-foreground">
                                    {{ c.programa?.nome }} · {{ formatData(c.data) }} · {{ c.alunos_count }} aluno(s)
                                </p>
                            </div>
                            <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700 dark:bg-amber-900/30 dark:text-amber-200">{{ c.status }}</span>
                        </li>
                    </ul>
                </div>

                <!-- Últimos Atendimentos -->
                <div class="rounded-xl border border-border bg-card p-6 shadow-sm">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="flex items-center gap-2 text-lg font-semibold text-foreground"><BadgeCheck class="h-5 w-5" /> Últimos Atendimentos</h2>
                        <Link href="/saude/atendimentos" class="text-xs font-medium text-primary hover:underline">Ver todos</Link>
                    </div>
                    <p v-if="!ultimos_atendimentos.length" class="py-8 text-center text-sm text-muted-foreground">Nenhum atendimento registrado.</p>
                    <ul v-else class="divide-y divide-border">
                        <li v-for="a in ultimos_atendimentos" :key="a.id" class="flex items-center justify-between gap-3 py-3">
                            <div>
                                <Link :href="`/alunos/${a.aluno.id}`" class="font-medium text-foreground hover:underline">{{ a.aluno.nome }}</Link>
                                <p class="text-xs text-muted-foreground">{{ a.programa.nome }} · {{ formatData(a.data_atendimento) }} <span v-if="a.profissional">· {{ a.profissional }}</span></p>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
