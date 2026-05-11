<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { ArrowLeft, FileDown, Save, UserCheck, HandHeart, Plus, X, Check, Building2, CalendarDays } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import MultiSelect from '@/components/MultiSelect.vue';
import { useSwal } from '@/composables/useSwal';
import type { BreadcrumbItem } from '@/types';

interface Pessoa { id: number; nome: string }

interface Escalado {
    id: number;
    expediente_id: number;
    escalavel_type: string;
    escalavel_id: number;
    presente: boolean;
    justificativa: string | null;
    escalavel: { id: number; nome: string } | null;
}

interface Expediente {
    id: number;
    data: string;
    descricao: string | null;
    observacoes: string | null;
    unidade?: { id: number; nome: string };
    escalados: Escalado[];
}

const props = defineProps<{
    expediente: Expediente;
    professores: Pessoa[];
    voluntarios: Pessoa[];
}>();

const { confirmDelete } = useSwal();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Expediente', href: '/expedientes' },
    { title: formatDate(props.expediente.data), href: `/expedientes/${props.expediente.id}` },
];

const profsEscala = computed(() => props.expediente.escalados.filter(e => e.escalavel_type.includes('Professor')));
const volsEscala = computed(() => props.expediente.escalados.filter(e => e.escalavel_type.includes('Voluntario')));

const escaladosIdsProf = computed(() => profsEscala.value.map(e => e.escalavel_id));
const escaladosIdsVol = computed(() => volsEscala.value.map(e => e.escalavel_id));

const profsDisponiveis = computed(() => props.professores.filter(p => !escaladosIdsProf.value.includes(p.id)));
const volsDisponiveis = computed(() => props.voluntarios.filter(v => !escaladosIdsVol.value.includes(v.id)));

const totalEscalados = computed(() => props.expediente.escalados.length);

const form = useForm({
    descricao: props.expediente.descricao || '',
    observacoes: props.expediente.observacoes || '',
    presencas: props.expediente.escalados.map(e => ({
        id: e.id,
        presente: e.presente,
        justificativa: e.justificativa || '',
    })),
    add_professores_ids: [] as (number | string)[],
    add_voluntarios_ids: [] as (number | string)[],
});

const totalPresentes = computed(() => form.presencas.filter(p => p.presente).length);
const totalFaltas = computed(() => form.presencas.filter(p => !p.presente).length);

const mostrarAdicionarProf = ref(false);
const mostrarAdicionarVol = ref(false);

function presencaState(escaladoId: number) {
    return form.presencas.find(p => p.id === escaladoId)!;
}

function togglePresenca(escaladoId: number) {
    const p = presencaState(escaladoId);
    p.presente = !p.presente;
    if (p.presente) p.justificativa = '';
}

function marcarTodos(valor: boolean) {
    form.presencas.forEach(p => {
        p.presente = valor;
        if (valor) p.justificativa = '';
    });
}

function salvar() {
    form.put(`/expedientes/${props.expediente.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            mostrarAdicionarProf.value = false;
            mostrarAdicionarVol.value = false;
        },
    });
}

function removerEscalado(escalado: Escalado) {
    confirmDelete(
        `Remover ${escalado.escalavel?.nome ?? 'escalado'} deste expediente?`,
        `/expedientes/${props.expediente.id}/escalado/${escalado.id}`,
    );
}

function formatDate(d: string): string {
    if (!d) return '-';
    const ymd = (d || '').slice(0, 10);
    return new Date(ymd + 'T12:00:00').toLocaleDateString('pt-BR');
}

function formatDateLong(d: string): string {
    const ymd = (d || '').slice(0, 10);
    return new Date(ymd + 'T12:00:00').toLocaleDateString('pt-BR', {
        weekday: 'long', day: 'numeric', month: 'long', year: 'numeric',
    });
}

function abrirPdf() {
    window.open(`/expedientes/${props.expediente.id}/escala-pdf`, '_blank');
}
</script>

<template>
    <Head :title="`Expediente ${formatDate(expediente.data)}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <form @submit.prevent="salvar" class="mx-auto w-full p-4 md:p-6">
            <!-- Header -->
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <Link href="/expedientes" class="flex h-9 w-9 items-center justify-center rounded-lg border border-border hover:bg-muted">
                        <ArrowLeft class="h-4 w-4" />
                    </Link>
                    <div>
                        <h1 class="text-2xl font-bold text-foreground">Expediente — {{ formatDate(expediente.data) }}</h1>
                        <p class="text-sm capitalize text-muted-foreground">{{ formatDateLong(expediente.data) }}</p>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" @click="abrirPdf" class="inline-flex items-center gap-2 rounded-lg border border-border px-3 py-2 text-sm font-medium hover:bg-muted">
                        <FileDown class="h-4 w-4" /> Imprimir Escala
                    </button>
                    <button type="submit" :disabled="form.processing" class="inline-flex items-center gap-2 rounded-lg bg-primary px-5 py-2 text-sm font-medium text-primary-foreground shadow-sm hover:bg-primary/90 disabled:opacity-50">
                        <Save class="h-4 w-4" /> {{ form.processing ? 'Salvando...' : 'Salvar Alterações' }}
                    </button>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Coluna esquerda: Info + Resumo -->
                <div class="space-y-6 lg:col-span-1">
                    <div class="rounded-xl border border-border bg-card p-6 shadow-sm">
                        <h3 class="mb-3 text-sm font-semibold text-muted-foreground">INFORMAÇÕES</h3>
                        <div class="space-y-3 text-sm">
                            <div class="flex items-center gap-2">
                                <Building2 class="h-4 w-4 text-muted-foreground" />
                                <span class="font-medium">{{ expediente.unidade?.nome ?? '-' }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <CalendarDays class="h-4 w-4 text-muted-foreground" />
                                <span class="font-medium">{{ formatDate(expediente.data) }}</span>
                            </div>
                            <hr class="border-border" />
                            <div>
                                <label class="mb-1 block text-xs font-medium text-muted-foreground">Descrição</label>
                                <input v-model="form.descricao" type="text" placeholder="Ex: Sábado letivo" class="h-9 w-full rounded-lg border border-border bg-background px-2 text-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary" />
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium text-muted-foreground">Observações</label>
                                <textarea v-model="form.observacoes" rows="3" placeholder="Anote algo sobre este expediente..." class="w-full rounded-lg border border-border bg-background px-2 py-1.5 text-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Resumo -->
                    <div class="rounded-xl border border-border bg-card p-6 shadow-sm">
                        <h3 class="mb-3 text-sm font-semibold text-muted-foreground">RESUMO</h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex items-center justify-between">
                                <span class="text-muted-foreground">Total escalados</span>
                                <span class="rounded-lg bg-primary/10 px-2 py-0.5 font-bold text-primary">{{ totalEscalados }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="flex items-center gap-1 text-emerald-600"><Check class="h-4 w-4" /> Presentes</span>
                                <span class="font-bold text-emerald-600">{{ totalPresentes }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="flex items-center gap-1 text-red-600"><X class="h-4 w-4" /> Faltas</span>
                                <span class="font-bold text-red-600">{{ totalFaltas }}</span>
                            </div>
                        </div>
                        <div v-if="form.presencas.length" class="mt-4 flex gap-2">
                            <button type="button" @click="marcarTodos(true)" class="flex-1 rounded-lg bg-emerald-100 px-3 py-1.5 text-xs font-medium text-emerald-700 hover:bg-emerald-200">
                                Todos presentes
                            </button>
                            <button type="button" @click="marcarTodos(false)" class="flex-1 rounded-lg bg-red-100 px-3 py-1.5 text-xs font-medium text-red-700 hover:bg-red-200">
                                Todos faltaram
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Coluna direita: Chamada -->
                <div class="space-y-6 lg:col-span-2">
                    <!-- Professores -->
                    <div class="rounded-xl border border-border bg-card p-6 shadow-sm">
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="flex items-center gap-2 text-lg font-semibold text-foreground">
                                <UserCheck class="h-5 w-5 text-primary" /> Professores
                                <span class="rounded-full bg-primary/10 px-2 py-0.5 text-xs font-bold text-primary">{{ profsEscala.length }}</span>
                            </h3>
                            <button v-if="profsDisponiveis.length" type="button" @click="mostrarAdicionarProf = !mostrarAdicionarProf" class="inline-flex items-center gap-1 rounded-lg bg-primary px-2.5 py-1.5 text-xs font-medium text-primary-foreground hover:bg-primary/90">
                                <Plus class="h-3.5 w-3.5" /> Adicionar
                            </button>
                        </div>

                        <div v-if="mostrarAdicionarProf" class="mb-4 rounded-lg border border-primary/30 bg-primary/5 p-3">
                            <div class="mb-2 flex items-center justify-between">
                                <span class="text-xs font-semibold text-primary">Selecione os professores a adicionar</span>
                                <button type="button" @click="mostrarAdicionarProf = false" class="rounded p-0.5 text-muted-foreground hover:text-foreground">
                                    <X class="h-3.5 w-3.5" />
                                </button>
                            </div>
                            <MultiSelect v-model="form.add_professores_ids" :options="profsDisponiveis" placeholder="Buscar professor..." />
                            <p class="mt-2 text-xs text-muted-foreground">Clique em "Salvar Alterações" para incluir.</p>
                        </div>

                        <div v-if="profsEscala.length" class="space-y-2">
                            <div
                                v-for="esc in profsEscala"
                                :key="esc.id"
                                class="group flex items-center gap-3 rounded-xl border p-3 transition-all"
                                :class="presencaState(esc.id).presente ? 'border-emerald-200 bg-emerald-50/50' : 'border-red-200 bg-red-50/50'"
                            >
                                <button
                                    type="button"
                                    @click="togglePresenca(esc.id)"
                                    class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full text-white transition-all"
                                    :class="presencaState(esc.id).presente ? 'bg-emerald-500 hover:bg-emerald-600' : 'bg-red-500 hover:bg-red-600'"
                                >
                                    <Check v-if="presencaState(esc.id).presente" class="h-6 w-6" />
                                    <X v-else class="h-6 w-6" />
                                </button>
                                <div class="min-w-0 flex-1">
                                    <p class="font-medium text-foreground">{{ esc.escalavel?.nome ?? '(removido)' }}</p>
                                    <input
                                        v-if="!presencaState(esc.id).presente"
                                        v-model="presencaState(esc.id).justificativa"
                                        type="text"
                                        placeholder="Justificativa (opcional)"
                                        class="mt-1 h-8 w-full rounded-lg border border-input/50 bg-background px-2 text-xs outline-none focus:border-primary"
                                    />
                                </div>
                                <button
                                    type="button"
                                    @click="removerEscalado(esc)"
                                    class="rounded p-1 text-muted-foreground opacity-0 transition-all hover:bg-red-50 hover:text-red-600 group-hover:opacity-100"
                                    title="Remover escalado"
                                >
                                    <X class="h-4 w-4" />
                                </button>
                            </div>
                        </div>
                        <p v-else class="py-6 text-center text-sm text-muted-foreground">Nenhum professor escalado.</p>
                    </div>

                    <!-- Voluntários -->
                    <div class="rounded-xl border border-border bg-card p-6 shadow-sm">
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="flex items-center gap-2 text-lg font-semibold text-foreground">
                                <HandHeart class="h-5 w-5 text-primary" /> Voluntários
                                <span class="rounded-full bg-primary/10 px-2 py-0.5 text-xs font-bold text-primary">{{ volsEscala.length }}</span>
                            </h3>
                            <button v-if="volsDisponiveis.length" type="button" @click="mostrarAdicionarVol = !mostrarAdicionarVol" class="inline-flex items-center gap-1 rounded-lg bg-primary px-2.5 py-1.5 text-xs font-medium text-primary-foreground hover:bg-primary/90">
                                <Plus class="h-3.5 w-3.5" /> Adicionar
                            </button>
                        </div>

                        <div v-if="mostrarAdicionarVol" class="mb-4 rounded-lg border border-primary/30 bg-primary/5 p-3">
                            <div class="mb-2 flex items-center justify-between">
                                <span class="text-xs font-semibold text-primary">Selecione os voluntários a adicionar</span>
                                <button type="button" @click="mostrarAdicionarVol = false" class="rounded p-0.5 text-muted-foreground hover:text-foreground">
                                    <X class="h-3.5 w-3.5" />
                                </button>
                            </div>
                            <MultiSelect v-model="form.add_voluntarios_ids" :options="volsDisponiveis" placeholder="Buscar voluntário..." />
                            <p class="mt-2 text-xs text-muted-foreground">Clique em "Salvar Alterações" para incluir.</p>
                        </div>

                        <div v-if="volsEscala.length" class="space-y-2">
                            <div
                                v-for="esc in volsEscala"
                                :key="esc.id"
                                class="group flex items-center gap-3 rounded-xl border p-3 transition-all"
                                :class="presencaState(esc.id).presente ? 'border-emerald-200 bg-emerald-50/50' : 'border-red-200 bg-red-50/50'"
                            >
                                <button
                                    type="button"
                                    @click="togglePresenca(esc.id)"
                                    class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full text-white transition-all"
                                    :class="presencaState(esc.id).presente ? 'bg-emerald-500 hover:bg-emerald-600' : 'bg-red-500 hover:bg-red-600'"
                                >
                                    <Check v-if="presencaState(esc.id).presente" class="h-6 w-6" />
                                    <X v-else class="h-6 w-6" />
                                </button>
                                <div class="min-w-0 flex-1">
                                    <p class="font-medium text-foreground">{{ esc.escalavel?.nome ?? '(removido)' }}</p>
                                    <input
                                        v-if="!presencaState(esc.id).presente"
                                        v-model="presencaState(esc.id).justificativa"
                                        type="text"
                                        placeholder="Justificativa (opcional)"
                                        class="mt-1 h-8 w-full rounded-lg border border-input/50 bg-background px-2 text-xs outline-none focus:border-primary"
                                    />
                                </div>
                                <button
                                    type="button"
                                    @click="removerEscalado(esc)"
                                    class="rounded p-1 text-muted-foreground opacity-0 transition-all hover:bg-red-50 hover:text-red-600 group-hover:opacity-100"
                                    title="Remover escalado"
                                >
                                    <X class="h-4 w-4" />
                                </button>
                            </div>
                        </div>
                        <p v-else class="py-6 text-center text-sm text-muted-foreground">Nenhum voluntário escalado.</p>
                    </div>
                </div>
            </div>
        </form>
    </AppLayout>
</template>
