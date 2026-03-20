<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import MultiSelect from '@/components/MultiSelect.vue';
import InputError from '@/components/InputError.vue';
import type { Turma, Curso, Professor, Voluntario, BreadcrumbItem, Unidade } from '@/types';

const props = defineProps<{
    turma: Turma;
    cursos: Curso[];
    professores: Professor[];
    voluntarios: Voluntario[];
    unidades: Unidade[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Turmas', href: '/turmas' },
    { title: 'Editar', href: `/turmas/${props.turma.id}/edit` },
];

const form = useForm({
    nome: props.turma.nome,
    curso_id: props.turma.curso_id,
    unidade_id: props.turma.unidade_id,
    professores_ids: props.turma.professores?.map(p => p.id) || [],
    voluntarios_ids: props.turma.voluntarios?.map(v => v.id) || [],
    horario_inicio: props.turma.horario_inicio || '14:00',
    horario_fim: props.turma.horario_fim || '16:00',
    dias_semana: props.turma.dias_semana || [],
    periodo: props.turma.periodo,
    capacidade_maxima: props.turma.capacidade_maxima,
    ano_letivo: props.turma.ano_letivo,
    status: props.turma.status,
});

const diasSemana = [
    { value: '1', label: 'Seg' }, { value: '2', label: 'Ter' },
    { value: '3', label: 'Qua' }, { value: '4', label: 'Qui' },
    { value: '5', label: 'Sex' }, { value: '6', label: 'Sáb' },
];

function submit() { form.put(`/turmas/${props.turma.id}`); }
</script>

<template>
    <Head title="Editar Turma" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full p-4 md:p-6">
            <div class="mb-6 flex items-center gap-3">
                <Link :href="`/turmas/${turma.id}`" class="flex h-9 w-9 items-center justify-center rounded-lg border border-border hover:bg-muted">
                    <ArrowLeft class="h-4 w-4" />
                </Link>
                <h1 class="text-2xl font-bold text-foreground">Editar Turma</h1>
            </div>
            <form @submit.prevent="submit" class="space-y-6">
                <div class="rounded-xl border border-border bg-card p-6 shadow-sm">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-sm font-medium">Nome *</label>
                            <input v-model="form.nome" type="text" required class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20" />
                            <InputError :message="form.errors.nome" class="mt-1" />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium">Curso *</label>
                            <select v-model="form.curso_id" required class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary">
                                <option v-for="c in cursos" :key="c.id" :value="c.id">{{ c.nome }}</option>
                            </select>
                            <InputError :message="form.errors.curso_id" class="mt-1" />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium">Unidade *</label>
                            <select v-model="form.unidade_id" required class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary">
                                <option v-for="u in unidades" :key="u.id" :value="u.id">{{ u.nome }}</option>
                            </select>
                            <InputError :message="form.errors.unidade_id" class="mt-1" />
                        </div>
                        <div class="sm:col-span-2">
                            <MultiSelect 
                                v-model="form.professores_ids" 
                                :options="professores" 
                                label="Professores (Vínculo) *"
                                placeholder="Selecione um ou mais professores..."
                            />
                            <InputError :message="form.errors.professores_ids" class="mt-1" />
                        </div>
                        <div class="sm:col-span-2">
                            <MultiSelect 
                                v-model="form.voluntarios_ids" 
                                :options="voluntarios" 
                                label="Voluntários / Auxiliares"
                                placeholder="Selecione voluntários..."
                            />
                            <InputError :message="form.errors.voluntarios_ids" class="mt-1" />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium">Início</label>
                            <input v-model="form.horario_inicio" type="time" class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary" />
                            <InputError :message="form.errors.horario_inicio" class="mt-1" />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium">Fim</label>
                            <input v-model="form.horario_fim" type="time" class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary" />
                            <InputError :message="form.errors.horario_fim" class="mt-1" />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium">Período</label>
                            <select v-model="form.periodo" class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary">
                                <option value="segunda_sexta">Seg-Sex</option>
                                <option value="sabados">Sábados</option>
                            </select>
                            <InputError :message="form.errors.periodo" class="mt-1" />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium">Capacidade</label>
                            <input v-model.number="form.capacidade_maxima" type="number" min="1" class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary" />
                            <InputError :message="form.errors.capacidade_maxima" class="mt-1" />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium">Ano Letivo</label>
                            <input v-model.number="form.ano_letivo" type="number" class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary" />
                            <InputError :message="form.errors.ano_letivo" class="mt-1" />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium">Status</label>
                            <select v-model="form.status" class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary">
                                <option value="planejada">Planejada</option>
                                <option value="em_andamento">Em Andamento</option>
                                <option value="encerrada">Encerrada</option>
                            </select>
                            <InputError :message="form.errors.status" class="mt-1" />
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-2 block text-sm font-medium">Dias</label>
                            <div class="flex flex-wrap gap-2">
                                <label v-for="d in diasSemana" :key="d.value" class="flex cursor-pointer items-center gap-1 rounded-lg border border-input px-3 py-2 text-sm" :class="form.dias_semana.includes(d.value) ? 'border-primary bg-primary/10 text-primary' : 'hover:bg-muted'">
                                    <input type="checkbox" :value="d.value" v-model="form.dias_semana" class="sr-only" />
                                    {{ d.label }}
                                </label>
                            </div>
                            <InputError :message="form.errors.dias_semana" class="mt-1" />
                        </div>
                    </div>
                </div>
                <div class="flex justify-end gap-3">
                    <Link :href="`/turmas/${turma.id}`" class="rounded-lg border border-border px-6 py-2.5 text-sm font-medium hover:bg-muted">Cancelar</Link>
                    <button type="submit" :disabled="form.processing" class="rounded-lg bg-primary px-6 py-2.5 text-sm font-medium text-primary-foreground shadow-sm hover:bg-primary/90 disabled:opacity-50">
                        {{ form.processing ? 'Salvando...' : 'Salvar' }}
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
