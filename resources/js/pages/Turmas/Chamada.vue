<script setup lang="ts">
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { ArrowLeft, Check, X } from 'lucide-vue-next';
import { ClipboardDocumentListIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/outline';
import AppLayout from '@/layouts/AppLayout.vue';
import DatePicker from '@/components/DatePicker.vue';
import type { Turma, Aluno, Chamada, BreadcrumbItem } from '@/types';

const props = defineProps<{
    turma: Turma;
    alunos: (Aluno & { pivot?: Record<string, unknown> })[];
    chamadaExistente: Chamada | null;
    data: string;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Turmas', href: '/turmas' },
    { title: props.turma.nome, href: `/turmas/${props.turma.id}` },
    { title: 'Chamada', href: `/turmas/${props.turma.id}/chamada` },
];

// Inicializar presenças (todos presentes por padrão ou do estado existente)
const presencas = ref<Record<number, { presente: boolean; observacao: string }>>(
    props.alunos.reduce((acc, aluno) => {
        const existente = props.chamadaExistente?.presencas?.find(p => p.aluno_id === aluno.id);
        acc[aluno.id] = {
            presente: existente ? existente.presente : true,
            observacao: existente?.observacao || '',
        };
        return acc;
    }, {} as Record<number, { presente: boolean; observacao: string }>)
);

const observacoesGerais = ref(props.chamadaExistente?.observacoes || '');
const dataSelecionada = ref(props.data);

function onDataChange(novaData: string) {
    if (!novaData || novaData === props.data) return;
    router.get(
        `/turmas/${props.turma.id}/chamada`,
        { data: novaData },
        { preserveScroll: true, preserveState: false },
    );
}

const totalPresentes = computed(() => Object.values(presencas.value).filter(p => p.presente).length);
const totalAusentes = computed(() => Object.values(presencas.value).filter(p => !p.presente).length);

function togglePresenca(alunoId: number) {
    presencas.value[alunoId].presente = !presencas.value[alunoId].presente;
}

function marcarTodos(valor: boolean) {
    Object.keys(presencas.value).forEach(id => {
        presencas.value[Number(id)].presente = valor;
    });
}

const form = useForm({});
const submitting = ref(false);

function submit() {
    submitting.value = true;
    form.transform(() => ({
        data: dataSelecionada.value || props.data,
        observacoes: observacoesGerais.value,
        presencas: props.alunos.map(a => ({
            aluno_id: a.id,
            presente: presencas.value[a.id].presente,
            observacao: presencas.value[a.id].observacao,
        })),
    })).post(`/turmas/${props.turma.id}/chamada`, {
        preserveScroll: true,
        onFinish: () => { submitting.value = false; },
    });
}

function formatDate(date: string): string {
    return new Date(date + 'T12:00:00').toLocaleDateString('pt-BR', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
}
</script>

<template>
    <Head title="Chamada" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full p-4 md:p-6">
            <!-- Header -->
            <div class="mb-6 flex items-center gap-3">
                <Link :href="`/turmas/${turma.id}`" class="flex h-9 w-9 items-center justify-center rounded-lg border border-border hover:bg-muted">
                    <ArrowLeft class="h-4 w-4" />
                </Link>
                <div>
                    <h1 class="flex items-center gap-2 text-2xl font-bold text-foreground"><ClipboardDocumentListIcon class="size-6" /> Chamada</h1>
                    <p class="text-sm text-muted-foreground">
                        {{ turma.nome }} · {{ turma.curso?.nome }}
                    </p>
                </div>
            </div>

            <!-- Data e Resumo -->
            <div class="mb-6 rounded-xl border border-border bg-card p-4 shadow-sm">
                <div class="mb-3 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-muted-foreground">Data da chamada</label>
                        <div class="w-full sm:w-64">
                            <DatePicker v-model="dataSelecionada" @update:model-value="onDataChange" />
                        </div>
                    </div>
                    <p class="text-sm font-medium capitalize text-foreground">{{ formatDate(data) }}</p>
                </div>
                <div class="flex items-center gap-4 text-sm">
                    <span class="flex items-center gap-1 text-emerald-600">
                        <Check class="h-4 w-4" /> {{ totalPresentes }} presentes
                    </span>
                    <span class="flex items-center gap-1 text-red-600">
                        <X class="h-4 w-4" /> {{ totalAusentes }} ausentes
                    </span>
                    <span class="text-muted-foreground">{{ alunos.length }} alunos</span>
                </div>
                <div class="mt-3 flex gap-2">
                    <button @click="marcarTodos(true)" class="rounded-lg bg-emerald-100 px-3 py-1.5 text-xs font-medium text-emerald-700 hover:bg-emerald-200">
                        Todos presentes
                    </button>
                    <button @click="marcarTodos(false)" class="rounded-lg bg-red-100 px-3 py-1.5 text-xs font-medium text-red-700 hover:bg-red-200">
                        Todos ausentes
                    </button>
                </div>
            </div>

            <!-- Alerta chamada existente -->
            <div v-if="chamadaExistente" class="mb-4 rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800">
                <p class="flex items-center gap-1 font-medium"><ExclamationTriangleIcon class="size-4" /> Uma chamada já foi registrada para esta data. Os dados serão atualizados.</p>
            </div>

            <!-- Lista de Alunos -->
            <form @submit.prevent="submit" class="space-y-4">
                <div class="space-y-2">
                    <div
                        v-for="aluno in alunos"
                        :key="aluno.id"
                        class="flex items-center gap-3 rounded-xl border p-3 transition-all"
                        :class="presencas[aluno.id]?.presente
                            ? 'border-emerald-200 bg-emerald-50/50'
                            : 'border-red-200 bg-red-50/50'"
                    >
                        <button
                            type="button"
                            @click="togglePresenca(aluno.id)"
                            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full text-white transition-all"
                            :class="presencas[aluno.id]?.presente
                                ? 'bg-emerald-500 hover:bg-emerald-600'
                                : 'bg-red-500 hover:bg-red-600'"
                        >
                            <Check v-if="presencas[aluno.id]?.presente" class="h-6 w-6" />
                            <X v-else class="h-6 w-6" />
                        </button>
                        <div class="min-w-0 flex-1">
                            <p class="font-medium text-foreground">{{ aluno.nome }}</p>
                            <input
                                v-model="presencas[aluno.id].observacao"
                                type="text"
                                placeholder="Observação (opcional)"
                                class="mt-1 h-8 w-full rounded-lg border border-input/50 bg-background px-2 text-xs outline-none focus:border-primary"
                            />
                        </div>
                    </div>
                </div>

                <div v-if="!alunos.length" class="rounded-xl border border-border bg-card p-8 text-center text-muted-foreground">
                    Nenhum aluno matriculado nesta turma.
                </div>

                <!-- Observações Gerais -->
                <div v-if="alunos.length" class="rounded-xl border border-border bg-card p-4 shadow-sm">
                    <label class="mb-1 block text-sm font-medium">Observações da chamada</label>
                    <textarea v-model="observacoesGerais" rows="2" placeholder="Observações gerais sobre a aula de hoje..." class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20"></textarea>
                </div>

                <!-- Submit -->
                <div v-if="alunos.length" class="flex justify-end gap-3">
                    <Link :href="`/turmas/${turma.id}`" class="rounded-lg border border-border px-6 py-2.5 text-sm font-medium hover:bg-muted">Cancelar</Link>
                    <button type="submit" :disabled="submitting" class="rounded-lg bg-primary px-8 py-2.5 text-sm font-medium text-primary-foreground shadow-sm hover:bg-primary/90 disabled:opacity-50">
                        {{ submitting ? 'Salvando...' : '✓ Registrar Chamada' }}
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
