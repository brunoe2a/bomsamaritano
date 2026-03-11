<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Cursos', href: '/cursos' },
    { title: 'Novo Curso', href: '/cursos/create' },
];

const form = useForm({
    nome: '',
    descricao: '',
    carga_horaria: null as number | null,
    dias_semana: [] as string[],
    periodo: 'tarde',
    max_alunos: 25,
    status: 'ativo',
});

const diasSemana = [
    { value: '1', label: 'Segunda' },
    { value: '2', label: 'Terça' },
    { value: '3', label: 'Quarta' },
    { value: '4', label: 'Quinta' },
    { value: '5', label: 'Sexta' },
    { value: '6', label: 'Sábado' },
];

function submit() {
    form.post('/cursos');
}
</script>

<template>
    <Head title="Novo Curso" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full p-4 md:p-6">
            <div class="mb-6 flex items-center gap-3">
                <Link href="/cursos" class="flex h-9 w-9 items-center justify-center rounded-lg border border-border hover:bg-muted">
                    <ArrowLeft class="h-4 w-4" />
                </Link>
                <h1 class="text-2xl font-bold text-foreground">Novo Curso</h1>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <div class="rounded-xl border border-border bg-card p-6 shadow-sm">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-sm font-medium">Nome do Curso *</label>
                            <input v-model="form.nome" type="text" required class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20" />
                            <p v-if="form.errors.nome" class="mt-1 text-xs text-red-500">{{ form.errors.nome }}</p>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-sm font-medium">Descrição / Ementa</label>
                            <textarea v-model="form.descricao" rows="3" class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20"></textarea>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium">Carga Horária (h)</label>
                            <input v-model.number="form.carga_horaria" type="number" min="1" class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary" />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium">Período *</label>
                            <select v-model="form.periodo" class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary">
                                <option value="manha">Manhã</option>
                                <option value="tarde">Tarde</option>
                                <option value="noite">Noite</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium">Máx. alunos por turma *</label>
                            <input v-model.number="form.max_alunos" type="number" min="1" required class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary" />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium">Status *</label>
                            <select v-model="form.status" class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary">
                                <option value="ativo">Ativo</option>
                                <option value="inativo">Inativo</option>
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-2 block text-sm font-medium">Dias da Semana</label>
                            <div class="flex flex-wrap gap-2">
                                <label v-for="d in diasSemana" :key="d.value" class="flex cursor-pointer items-center gap-2 rounded-lg border border-input px-3 py-2 text-sm transition-colors" :class="form.dias_semana.includes(d.value) ? 'border-primary bg-primary/10 text-primary' : 'hover:bg-muted'">
                                    <input type="checkbox" :value="d.value" v-model="form.dias_semana" class="sr-only" />
                                    {{ d.label }}
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <Link href="/cursos" class="rounded-lg border border-border px-6 py-2.5 text-sm font-medium hover:bg-muted">Cancelar</Link>
                    <button type="submit" :disabled="form.processing" class="rounded-lg bg-primary px-6 py-2.5 text-sm font-medium text-primary-foreground shadow-sm hover:bg-primary/90 disabled:opacity-50">
                        {{ form.processing ? 'Salvando...' : 'Criar Curso' }}
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
