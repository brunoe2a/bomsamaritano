<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { ArrowLeft, ClipboardCheck, Pencil, Users, FileText, UserPlus, X, Search } from 'lucide-vue-next';
import { ClipboardDocumentListIcon } from '@heroicons/vue/24/outline';
import AppLayout from '@/layouts/AppLayout.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { useSwal } from '@/composables/useSwal';
import type { Turma, Chamada, PaginatedData, BreadcrumbItem } from '@/types';

type AlunoDisponivel = { id: number; nome: string; ano_escolar: string };

const { confirmDelete: swalDelete, confirmAction } = useSwal();

const props = defineProps<{
    turma: Turma;
    chamadas: PaginatedData<Chamada>;
    alunosDisponiveis: AlunoDisponivel[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Turmas', href: '/turmas' },
    { title: props.turma.nome, href: `/turmas/${props.turma.id}` },
];

const periodoLabels: Record<string, string> = { segunda_sexta: 'Segunda a Sexta', sabados: 'Aos Sábados' };

function formatDate(date: string): string {
    return new Date(date).toLocaleDateString('pt-BR');
}

// --- Inscrição ---
const showInscrever = ref(false);
const buscaAluno = ref('');

const alunosFiltrados = computed(() => {
    if (!buscaAluno.value) return props.alunosDisponiveis.slice(0, 10);
    const q = buscaAluno.value.toLowerCase();
    return props.alunosDisponiveis
        .filter(a => a.nome.toLowerCase().includes(q))
        .slice(0, 10);
});

const matriculaForm = useForm({ aluno_id: 0 });

async function inscreverAluno(aluno: AlunoDisponivel) {
    const isConfirmed = await confirmAction(
        'Confirmar Inscrição',
        `Deseja inscrever o aluno "${aluno.nome}" na turma "${props.turma.nome}"?`,
        'Sim, inscrever'
    );

    if (isConfirmed) {
        matriculaForm.aluno_id = aluno.id;
        matriculaForm.post(`/turmas/${props.turma.id}/matricular`, {
            preserveScroll: true,
            onSuccess: () => {
                buscaAluno.value = '';
                showInscrever.value = false;
            },
        });
    }
}

function desmatricular(matriculaId: number, nomeAluno: string) {
    swalDelete(
        `A matrícula de "${nomeAluno}" nesta turma será cancelada.`,
        `/turmas/${props.turma.id}/desmatricular/${matriculaId}`,
    );
}
</script>

<template>
    <Head :title="turma.nome" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full p-4 md:p-6">
            <!-- Header -->
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <Link href="/turmas" class="flex h-9 w-9 items-center justify-center rounded-lg border border-border hover:bg-muted">
                        <ArrowLeft class="h-4 w-4" />
                    </Link>
                    <div>
                        <h1 class="text-2xl font-bold text-foreground">{{ turma.nome }}</h1>
                        <p class="text-sm text-muted-foreground">
                            {{ turma.curso?.nome }} · {{ periodoLabels[turma.periodo] }} · {{ turma.ano_letivo }}
                        </p>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <StatusBadge :status="turma.status" />
                    <a :href="`/export/frequencia?turma_id=${turma.id}`" class="inline-flex items-center gap-2 rounded-lg border border-border px-3 py-2 text-sm font-medium hover:bg-muted">
                        <FileText class="h-4 w-4" /> Frequência PDF
                    </a>
                    <Link :href="`/turmas/${turma.id}/chamada`" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-emerald-700">
                        <ClipboardCheck class="h-4 w-4" /> Fazer Chamada
                    </Link>
                    <Link :href="`/turmas/${turma.id}/edit`" class="inline-flex items-center gap-2 rounded-lg border border-border px-3 py-2 text-sm font-medium hover:bg-muted">
                        <Pencil class="h-4 w-4" /> Editar
                    </Link>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Info -->
                <div class="space-y-6 lg:col-span-1">
                    <div class="rounded-xl border border-border bg-card p-6 shadow-sm">
                        <h3 class="mb-3 text-sm font-semibold text-muted-foreground">INFORMAÇÕES</h3>
                        <div class="space-y-4 text-sm">
                            <div>
                                <p class="font-medium text-muted-foreground mb-1">Professores Vinculados</p>
                                <ul v-if="turma.professores?.length" class="space-y-1">
                                    <li v-for="p in turma.professores" :key="p.id" class="flex items-center gap-2 text-foreground">
                                        <div class="flex h-5 w-5 items-center justify-center rounded-full bg-primary/10 text-[10px] font-bold text-primary">{{ p.nome.charAt(0) }}</div>
                                        {{ p.nome }}
                                    </li>
                                </ul>
                                <p v-else class="text-muted-foreground">Nenhum professor</p>
                            </div>
                            
                            <div>
                                <p class="font-medium text-muted-foreground mb-1">Voluntários / Auxiliares</p>
                                <ul v-if="turma.voluntarios?.length" class="space-y-1">
                                    <li v-for="v in turma.voluntarios" :key="v.id" class="flex items-center gap-2 text-foreground">
                                        <div class="flex h-5 w-5 items-center justify-center rounded-full bg-blue-500/10 text-[10px] font-bold text-blue-600">{{ v.nome.charAt(0) }}</div>
                                        {{ v.nome }}
                                    </li>
                                </ul>
                                <p v-else class="text-muted-foreground">Nenhum voluntário</p>
                            </div>

                            <hr class="border-border" />
                            <div class="space-y-2">
                                <p><span class="font-medium text-muted-foreground">Horário:</span> {{ turma.horario_inicio }} - {{ turma.horario_fim }}</p>
                                <p><span class="font-medium text-muted-foreground">Capacidade:</span> {{ turma.matriculas?.length || 0 }}/{{ turma.capacidade_maxima }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Alunos Matriculados -->
                    <div class="rounded-xl border border-border bg-card p-6 shadow-sm">
                        <div class="mb-3 flex items-center justify-between">
                            <h3 class="flex items-center gap-2 text-sm font-semibold text-muted-foreground">
                                <Users class="h-4 w-4" /> ALUNOS MATRICULADOS
                            </h3>
                            <button
                                @click="showInscrever = !showInscrever"
                                class="inline-flex items-center gap-1 rounded-lg bg-primary px-2.5 py-1.5 text-xs font-medium text-primary-foreground shadow-sm hover:bg-primary/90"
                            >
                                <UserPlus class="h-3.5 w-3.5" />
                                Inscrever
                            </button>
                        </div>

                        <!-- Formulário de inscrição inline -->
                        <div v-if="showInscrever" class="mb-4 rounded-lg border border-primary/30 bg-primary/5 p-3">
                            <div class="mb-2 flex items-center justify-between">
                                <span class="text-xs font-semibold text-primary">Buscar aluno para inscrever</span>
                                <button @click="showInscrever = false; buscaAluno = ''" class="rounded p-0.5 text-muted-foreground hover:text-foreground">
                                    <X class="h-3.5 w-3.5" />
                                </button>
                            </div>
                            <div class="relative mb-2">
                                <Search class="absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-muted-foreground" />
                                <input
                                    v-model="buscaAluno"
                                    type="text"
                                    placeholder="Digite o nome do aluno..."
                                    class="h-9 w-full rounded-lg border border-input bg-background pl-8 pr-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20"
                                    autofocus
                                />
                            </div>
                            <div class="max-h-48 space-y-1 overflow-y-auto">
                                <button
                                    v-for="aluno in alunosFiltrados"
                                    :key="aluno.id"
                                    @click="inscreverAluno(aluno)"
                                    :disabled="matriculaForm.processing"
                                    class="flex w-full items-center gap-2 rounded-lg p-2 text-left text-sm transition-colors hover:bg-primary/10 disabled:opacity-50"
                                >
                                    <div class="flex h-7 w-7 items-center justify-center rounded-full bg-primary/15 text-xs font-bold text-primary">
                                        {{ aluno?.nome?.charAt(0) || '' }}
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <span class="block truncate font-medium text-foreground">{{ aluno.nome }}</span>
                                        <span class="text-xs text-muted-foreground">{{ aluno.ano_escolar }}</span>
                                    </div>
                                    <UserPlus class="h-4 w-4 text-primary" />
                                </button>
                                <p v-if="!alunosFiltrados.length" class="py-2 text-center text-xs text-muted-foreground">
                                    Nenhum aluno disponível encontrado.
                                </p>
                            </div>
                        </div>

                        <!-- Lista de alunos matriculados -->
                        <div v-if="turma.matriculas?.length" class="space-y-1">
                            <div v-for="m in turma.matriculas" :key="m.id" class="group flex items-center gap-2 rounded-lg p-2 text-sm hover:bg-muted">
                                <Link :href="`/alunos/${m.aluno?.id}`" class="flex min-w-0 flex-1 items-center gap-2">
                                    <div class="flex h-7 w-7 items-center justify-center rounded-full bg-primary/15 text-xs font-bold text-primary">
                                        {{ m.aluno?.nome?.charAt(0) }}
                                    </div>
                                    <span class="truncate">{{ m.aluno?.nome }}</span>
                                </Link>
                                <button
                                    @click="desmatricular(m.id, m.aluno?.nome || '')"
                                    class="rounded p-1 text-muted-foreground opacity-0 transition-all hover:bg-red-50 hover:text-red-600 group-hover:opacity-100"
                                    title="Cancelar matrícula"
                                >
                                    <X class="h-3.5 w-3.5" />
                                </button>
                            </div>
                        </div>
                        <p v-else class="text-sm text-muted-foreground">Nenhum aluno matriculado.</p>
                    </div>
                </div>

                <!-- Histórico de Chamadas -->
                <div class="lg:col-span-2">
                    <div class="rounded-xl border border-border bg-card p-6 shadow-sm">
                        <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold text-foreground"><ClipboardDocumentListIcon class="size-5" /> Histórico de Chamadas</h3>
                        <div v-if="chamadas.data.length" class="space-y-3">
                            <div v-for="chamada in chamadas.data" :key="chamada.id" class="rounded-lg border border-border p-4">
                                <div class="mb-2 flex items-center justify-between">
                                    <span class="font-medium text-foreground">{{ formatDate(chamada.data) }}</span>
                                    <div class="flex gap-2 text-xs">
                                        <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-emerald-700">
                                            {{ chamada.presencas?.filter(p => p.presente).length || 0 }} presentes
                                        </span>
                                        <span class="rounded-full bg-red-100 px-2 py-0.5 text-red-700">
                                            {{ chamada.presencas?.filter(p => !p.presente).length || 0 }} ausentes
                                        </span>
                                    </div>
                                </div>
                                <div v-if="chamada.presencas?.length" class="flex flex-wrap gap-1">
                                    <span v-for="p in chamada.presencas" :key="p.id" class="rounded px-1.5 py-0.5 text-xs" :class="p.presente ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700'">
                                        {{ p.aluno?.nome?.split(' ')[0] }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <p v-else class="text-sm text-muted-foreground">Nenhuma chamada registrada.</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
