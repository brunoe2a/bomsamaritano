<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ArrowLeft, Search } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import SearchableSelect from '@/components/SearchableSelect.vue';
import NativeSelect from '@/components/NativeSelect.vue';
import DatePicker from '@/components/DatePicker.vue';
import type { BreadcrumbItem } from '@/types';

interface Programa { id: number; nome: string; area: { id: number; nome: string } | null }
interface Unidade { id: number; nome: string }
interface AlunoOption { id: number; nome: string; ano_escolar: string | null; responsavel: { id: number; nome: string } | null }
interface Convocacao {
    id: number;
    programa_id: number;
    titulo: string;
    data: string;
    hora: string | null;
    local: string | null;
    profissional: string | null;
    observacoes: string | null;
    status: string;
    unidade_id: number | null;
    alunos: { id: number }[];
}

const props = defineProps<{
    convocacao: Convocacao;
    programas: Programa[];
    unidades: Unidade[];
    alunos: AlunoOption[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Núcleo de Saúde', href: '/saude' },
    { title: 'Convocações', href: '/saude/convocacoes' },
    { title: 'Editar', href: `/saude/convocacoes/${props.convocacao.id}/edit` },
];

const form = useForm({
    programa_id: props.convocacao.programa_id,
    titulo: props.convocacao.titulo,
    data_convocacao: props.convocacao.data?.split('T')[0] || props.convocacao.data,
    hora: props.convocacao.hora?.slice(0, 5) || '',
    local: props.convocacao.local || '',
    profissional: props.convocacao.profissional || '',
    observacoes: props.convocacao.observacoes || '',
    status: props.convocacao.status,
    unidade_id: props.convocacao.unidade_id ?? '',
    alunos_ids: props.convocacao.alunos.map(a => a.id),
});

const buscaAluno = ref('');
const alunosFiltrados = computed(() => {
    const termo = buscaAluno.value.trim().toLowerCase();
    if (!termo) return props.alunos;
    return props.alunos.filter(a =>
        a.nome.toLowerCase().includes(termo) ||
        (a.responsavel?.nome ?? '').toLowerCase().includes(termo) ||
        (a.ano_escolar ?? '').toLowerCase().includes(termo),
    );
});

function toggleAluno(id: number) {
    const idx = form.alunos_ids.indexOf(id);
    if (idx >= 0) form.alunos_ids.splice(idx, 1);
    else form.alunos_ids.push(id);
}

function submit() {
    form.transform((d: any) => ({ ...d, data: d.data_convocacao, data_convocacao: undefined }))
        .put(`/saude/convocacoes/${props.convocacao.id}`);
}

const programaOptions = computed(() => props.programas.map(p => ({ value: p.id, label: p.nome, hint: p.area?.nome ?? '' })));
const unidadeOptions = computed(() => props.unidades.map(u => ({ value: u.id, label: u.nome })));
</script>

<template>
    <Head title="Editar Convocação" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full p-4 md:p-6">
            <div class="mb-6 flex items-center gap-3">
                <Link :href="`/saude/convocacoes/${convocacao.id}`" class="flex h-9 w-9 items-center justify-center rounded-lg border border-border hover:bg-muted"><ArrowLeft class="h-4 w-4" /></Link>
                <div>
                    <h1 class="text-2xl font-bold text-foreground">Editar Convocação</h1>
                    <p class="text-sm text-muted-foreground">{{ convocacao.titulo }}</p>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <div class="rounded-xl border border-border bg-card p-6 shadow-sm">
                    <h2 class="mb-4 text-lg font-semibold text-foreground">Dados da Convocação</h2>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-sm font-medium">Título *</label>
                            <input v-model="form.titulo" type="text" required class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary" />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium">Programa *</label>
                            <SearchableSelect v-model="form.programa_id" :options="programaOptions" placeholder="Selecione" />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium">Status</label>
                            <NativeSelect v-model="form.status">
                                <option value="planejada">Planejada</option>
                                <option value="realizada">Realizada</option>
                                <option value="cancelada">Cancelada</option>
                            </NativeSelect>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium">Data *</label>
                            <DatePicker v-model="form.data_convocacao" />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium">Hora</label>
                            <input v-model="form.hora" type="time" class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary" />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium">Local</label>
                            <input v-model="form.local" type="text" class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary" />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium">Profissional</label>
                            <input v-model="form.profissional" type="text" class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary" />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium">Unidade</label>
                            <SearchableSelect v-model="form.unidade_id" :options="unidadeOptions" placeholder="—" allow-empty empty-label="—" />
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-sm font-medium">Observações</label>
                            <textarea v-model="form.observacoes" rows="2" class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm outline-none focus:border-primary"></textarea>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-border bg-card p-6 shadow-sm">
                    <h2 class="mb-4 text-lg font-semibold text-foreground">Alunos Convocados ({{ form.alunos_ids.length }})</h2>
                    <div class="relative mb-3">
                        <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <input v-model="buscaAluno" type="text" placeholder="Buscar..." class="h-10 w-full rounded-lg border border-input bg-background pl-9 pr-3 text-sm outline-none focus:border-primary" />
                    </div>
                    <div class="max-h-[420px] overflow-y-auto rounded-lg border border-border">
                        <label v-for="a in alunosFiltrados" :key="a.id" class="flex cursor-pointer items-center gap-3 border-b border-border px-3 py-2 text-sm last:border-0" :class="form.alunos_ids.includes(a.id) ? 'bg-primary/10' : 'hover:bg-muted'">
                            <input type="checkbox" :checked="form.alunos_ids.includes(a.id)" @change="toggleAluno(a.id)" class="rounded border-input text-primary" />
                            <div class="flex-1">
                                <p class="font-medium text-foreground">{{ a.nome }}</p>
                                <p class="text-xs text-muted-foreground">
                                    <span v-if="a.ano_escolar">{{ a.ano_escolar }}</span>
                                    <span v-if="a.responsavel"> · Resp.: {{ a.responsavel.nome }}</span>
                                </p>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <Link :href="`/saude/convocacoes/${convocacao.id}`" class="rounded-lg border border-border px-6 py-2.5 text-sm font-medium hover:bg-muted">Cancelar</Link>
                    <button type="submit" :disabled="form.processing" class="rounded-lg bg-primary px-6 py-2.5 text-sm font-medium text-primary-foreground shadow-sm hover:bg-primary/90 disabled:opacity-50">
                        {{ form.processing ? 'Salvando...' : 'Salvar Alterações' }}
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
