<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeft, Pencil, FileText, CalendarDays, MapPin, Stethoscope, Building2 } from 'lucide-vue-next';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface AlunoConvocado {
    id: number;
    nome: string;
    ano_escolar: string | null;
    responsavel: { id: number; nome: string; telefone: string | null; whatsapp: string | null } | null;
    pivot: { id: number; presente: boolean; observacao: string | null };
}
interface Convocacao {
    id: number;
    titulo: string;
    data: string;
    hora: string | null;
    local: string | null;
    profissional: string | null;
    observacoes: string | null;
    status: string;
    programa: { id: number; nome: string; area: string } | null;
    unidade: { id: number; nome: string } | null;
    user: { id: number; name: string } | null;
    alunos: AlunoConvocado[];
}

const props = defineProps<{ convocacao: Convocacao }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Núcleo de Saúde', href: '/saude' },
    { title: 'Convocações', href: '/saude/convocacoes' },
    { title: props.convocacao.titulo, href: `/saude/convocacoes/${props.convocacao.id}` },
];

function formatData(d: string) { return new Date(d).toLocaleDateString('pt-BR'); }

const presencas = ref(
    props.convocacao.alunos.map(a => ({
        aluno_id: a.id,
        nome: a.nome,
        presente: !!a.pivot.presente,
        observacao: a.pivot.observacao || '',
    })),
);

const salvando = ref(false);
function salvarPresencas() {
    salvando.value = true;
    router.post(`/saude/convocacoes/${props.convocacao.id}/presenca`, {
        presencas: presencas.value.map(p => ({
            aluno_id: p.aluno_id,
            presente: p.presente ? 1 : 0,
            observacao: p.observacao || null,
        })),
    }, {
        preserveScroll: true,
        onFinish: () => (salvando.value = false),
    });
}

const statusClasses: Record<string, string> = {
    planejada: 'bg-amber-100 text-amber-700',
    realizada: 'bg-emerald-100 text-emerald-700',
    cancelada: 'bg-rose-100 text-rose-700',
};
</script>

<template>
    <Head :title="convocacao.titulo" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full p-4 md:p-6">
            <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <Link href="/saude/convocacoes" class="flex h-9 w-9 items-center justify-center rounded-lg border border-border hover:bg-muted"><ArrowLeft class="h-4 w-4" /></Link>
                    <div>
                        <h1 class="text-2xl font-bold text-foreground">{{ convocacao.titulo }}</h1>
                        <p class="text-sm text-muted-foreground">{{ convocacao.programa?.nome }}</p>
                    </div>
                    <span :class="statusClasses[convocacao.status]" class="rounded-full px-3 py-1 text-xs font-medium uppercase">{{ convocacao.status }}</span>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a :href="`/export/saude/convocacoes/${convocacao.id}/pdf`" target="_blank" class="inline-flex items-center gap-2 rounded-lg border border-border px-4 py-2 text-sm font-medium hover:bg-muted">
                        <FileText class="h-4 w-4" /> Imprimir Lista
                    </a>
                    <Link :href="`/saude/convocacoes/${convocacao.id}/edit`" class="inline-flex items-center gap-2 rounded-lg border border-border px-4 py-2 text-sm font-medium hover:bg-muted">
                        <Pencil class="h-4 w-4" /> Editar
                    </Link>
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-3">
                <div class="md:col-span-2 space-y-6">
                    <div class="rounded-xl border border-border bg-card p-6 shadow-sm">
                        <h2 class="mb-4 text-lg font-semibold text-foreground">Registro de Presença</h2>
                        <p class="mb-4 text-sm text-muted-foreground">Marque os presentes — ao salvar, atendimentos serão registrados automaticamente para cada aluno presente.</p>

                        <div v-if="!presencas.length" class="py-8 text-center text-sm text-muted-foreground">Nenhum aluno convocado.</div>

                        <table v-else class="w-full text-sm">
                            <thead class="border-b border-border text-xs uppercase text-muted-foreground">
                                <tr>
                                    <th class="px-2 py-2 text-left">Aluno</th>
                                    <th class="px-2 py-2 text-center">Presente</th>
                                    <th class="px-2 py-2 text-left">Observação</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="p in presencas" :key="p.aluno_id" class="border-b border-border last:border-0">
                                    <td class="px-2 py-2 font-medium text-foreground">{{ p.nome }}</td>
                                    <td class="px-2 py-2 text-center">
                                        <input v-model="p.presente" type="checkbox" class="h-5 w-5 rounded border-input text-primary focus:ring-primary" />
                                    </td>
                                    <td class="px-2 py-2">
                                        <input v-model="p.observacao" type="text" placeholder="Observação..." class="h-9 w-full rounded-md border border-input bg-background px-2 text-xs outline-none focus:border-primary" />
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <div class="mt-4 flex justify-end">
                            <button v-if="presencas.length" @click="salvarPresencas" :disabled="salvando" class="rounded-lg bg-primary px-5 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90 disabled:opacity-50">
                                {{ salvando ? 'Salvando...' : 'Salvar Presenças & Registrar Atendimentos' }}
                            </button>
                        </div>
                    </div>

                    <div v-if="convocacao.observacoes" class="rounded-xl border border-border bg-card p-6 shadow-sm">
                        <h3 class="mb-2 text-sm font-semibold text-foreground">Observações</h3>
                        <p class="whitespace-pre-line text-sm text-muted-foreground">{{ convocacao.observacoes }}</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="rounded-xl border border-border bg-card p-6 shadow-sm">
                        <h3 class="mb-3 text-sm font-semibold text-foreground">Detalhes</h3>
                        <ul class="space-y-2 text-sm">
                            <li class="flex items-start gap-2"><CalendarDays class="mt-0.5 h-4 w-4 text-muted-foreground" /><span>{{ formatData(convocacao.data) }}<span v-if="convocacao.hora"> às {{ convocacao.hora.slice(0,5) }}</span></span></li>
                            <li v-if="convocacao.local" class="flex items-start gap-2"><MapPin class="mt-0.5 h-4 w-4 text-muted-foreground" /><span>{{ convocacao.local }}</span></li>
                            <li v-if="convocacao.profissional" class="flex items-start gap-2"><Stethoscope class="mt-0.5 h-4 w-4 text-muted-foreground" /><span>{{ convocacao.profissional }}</span></li>
                            <li v-if="convocacao.unidade" class="flex items-start gap-2"><Building2 class="mt-0.5 h-4 w-4 text-muted-foreground" /><span>{{ convocacao.unidade.nome }}</span></li>
                        </ul>
                    </div>

                    <div class="rounded-xl border border-border bg-card p-6 shadow-sm">
                        <h3 class="mb-3 text-sm font-semibold text-foreground">Resumo</h3>
                        <ul class="space-y-1.5 text-sm">
                            <li class="flex justify-between"><span class="text-muted-foreground">Convocados</span><span class="font-semibold">{{ convocacao.alunos.length }}</span></li>
                            <li class="flex justify-between"><span class="text-muted-foreground">Presentes</span><span class="font-semibold text-emerald-600">{{ presencas.filter(p => p.presente).length }}</span></li>
                            <li class="flex justify-between"><span class="text-muted-foreground">Ausentes</span><span class="font-semibold text-rose-600">{{ presencas.filter(p => !p.presente).length }}</span></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
