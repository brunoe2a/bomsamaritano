<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, Unidade } from '@/types';

type Categoria = { id: number; nome: string; tipo: string };
type Doador = { id: number; nome: string; tipo: string };

const props = defineProps<{
    categorias: Categoria[];
    doadores: Doador[];
    unidades: Unidade[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Financeiro', href: '/financeiro' },
    { title: 'Novo Lançamento', href: '/financeiro/create' },
];

const form = useForm({
    tipo: 'entrada' as string,
    unidade_id: 1 as number,
    categoria_id: '' as string | number,
    doador_id: '' as string | number,
    descricao: '',
    valor: null as number | null,
    data: new Date().toISOString().split('T')[0],
    comprovante: null as File | null,
    observacoes: '',
});

function handleFile(e: Event) {
    const t = e.target as HTMLInputElement;
    if (t.files?.length) form.comprovante = t.files[0];
}

function submit() { form.post('/financeiro'); }

const categoriasFiltradas = () => {
    const tipoCategoria = form.tipo === 'entrada' ? 'receita' : 'despesa';
    return props.categorias.filter(c => c.tipo === tipoCategoria);
};
</script>

<template>
    <Head title="Novo Lançamento" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full p-4 md:p-6">
            <div class="mb-6 flex items-center gap-3">
                <Link href="/financeiro" class="flex h-9 w-9 items-center justify-center rounded-lg border border-border hover:bg-muted"><ArrowLeft class="h-4 w-4" /></Link>
                <h1 class="text-2xl font-bold text-foreground">Novo Lançamento</h1>
            </div>
            <form @submit.prevent="submit" class="space-y-6">
                <div class="rounded-xl border border-border bg-card p-6 shadow-sm">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <!-- Tipo -->
                        <div class="sm:col-span-2">
                            <label class="mb-2 block text-sm font-medium">Tipo *</label>
                            <div class="flex gap-3">
                                <label class="flex flex-1 cursor-pointer items-center justify-center gap-2 rounded-lg border-2 p-3 text-sm font-medium transition-all" :class="form.tipo === 'entrada' ? 'border-emerald-500 bg-emerald-50 text-emerald-700' : 'border-border hover:bg-muted'">
                                    <input type="radio" v-model="form.tipo" value="entrada" class="sr-only" />
                                    ↑ Entrada
                                </label>
                                <label class="flex flex-1 cursor-pointer items-center justify-center gap-2 rounded-lg border-2 p-3 text-sm font-medium transition-all" :class="form.tipo === 'saida' ? 'border-red-500 bg-red-50 text-red-700' : 'border-border hover:bg-muted'">
                                    <input type="radio" v-model="form.tipo" value="saida" class="sr-only" />
                                    ↓ Saída
                                </label>
                            </div>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-sm font-medium">Descrição *</label>
                            <input v-model="form.descricao" type="text" required placeholder="Ex: Doação empresa X" class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20" />
                            <p v-if="form.errors.descricao" class="mt-1 text-xs text-red-500">{{ form.errors.descricao }}</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium">Valor (R$) *</label>
                            <input v-model.number="form.valor" type="number" step="0.01" min="0.01" required class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary" />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium">Unidade *</label>
                            <select v-model="form.unidade_id" required class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary">
                                <option v-for="u in unidades" :key="u.id" :value="u.id">{{ u.nome }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium">Data *</label>
                            <input v-model="form.data" type="date" required class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary" />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium">Categoria *</label>
                            <select v-model="form.categoria_id" required class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary">
                                <option value="">Selecione</option>
                                <option v-for="c in categoriasFiltradas()" :key="c.id" :value="c.id">{{ c.nome }}</option>
                            </select>
                        </div>
                        <div v-if="form.tipo === 'entrada'">
                            <label class="mb-1 block text-sm font-medium">Doador</label>
                            <select v-model="form.doador_id" class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary">
                                <option value="">Nenhum</option>
                                <option v-for="d in doadores" :key="d.id" :value="d.id">{{ d.nome }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium">Comprovante</label>
                            <input type="file" @change="handleFile" class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm file:mr-2 file:rounded file:border-0 file:bg-primary/10 file:px-2 file:py-1 file:text-xs file:text-primary" />
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-sm font-medium">Observações</label>
                            <textarea v-model="form.observacoes" rows="2" class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm outline-none focus:border-primary"></textarea>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end gap-3">
                    <Link href="/financeiro" class="rounded-lg border border-border px-6 py-2.5 text-sm font-medium hover:bg-muted">Cancelar</Link>
                    <button type="submit" :disabled="form.processing" class="rounded-lg bg-primary px-6 py-2.5 text-sm font-medium text-primary-foreground shadow-sm hover:bg-primary/90 disabled:opacity-50">
                        {{ form.processing ? 'Salvando...' : 'Registrar' }}
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
