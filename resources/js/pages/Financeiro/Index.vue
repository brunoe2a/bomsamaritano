<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { Plus, Pencil, Trash2, Search, TrendingUp, TrendingDown, DollarSign, Users, FileSpreadsheet, FileText, List, X, PieChart } from 'lucide-vue-next';
import axios from 'axios';
import AppLayout from '@/layouts/AppLayout.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import Pagination from '@/components/Pagination.vue';
import { useSwal } from '@/composables/useSwal';
import type { PaginatedData, BreadcrumbItem, Unidade } from '@/types';

const { confirmDelete: swalDelete } = useSwal();

type Categoria = { id: number; nome: string; tipo: string };
type Lancamento = {
    id: number; tipo: string; descricao: string; valor: number;
    data: string; categoria?: Categoria; doador?: { nome: string };
    unidade?: { nome: string };
    usuario?: { name: string };
};

const props = defineProps<{
    lancamentos: PaginatedData<Lancamento>;
    filtros: Record<string, string>;
    categorias: Categoria[];
    unidades: Unidade[];
    resumo: { entradas: number; saidas: number; saldo: number };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Financeiro', href: '/financeiro' },
];

const busca = ref(props.filtros.busca || '');
const tipo = ref(props.filtros.tipo || '');
const categoriaId = ref(props.filtros.categoria_id || '');
const unidadeId = ref(props.filtros.unidade_id || '');
const mes = ref(props.filtros.mes || '');
const ano = ref(props.filtros.ano || '');

let timeout: ReturnType<typeof setTimeout>;
function applyFilters() {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get('/financeiro', {
            busca: busca.value || undefined,
            tipo: tipo.value || undefined,
            categoria_id: categoriaId.value || undefined,
            unidade_id: unidadeId.value || undefined,
            mes: mes.value || undefined,
            ano: ano.value || undefined,
        }, { preserveState: true, replace: true });
    }, 300);
}
watch([busca, tipo, categoriaId, unidadeId, mes, ano], applyFilters);

function formatCurrency(v: number) {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(v);
}

function formatDate(d: string) {
    return new Date(d).toLocaleDateString('pt-BR');
}

function confirmDelete(l: Lancamento) {
    swalDelete(`O lançamento "${l.descricao}" será removido.`, `/financeiro/${l.id}`);
}

const showCategoriasModal = ref(false);
const categoriasBanco = ref<{id: number, nome: string, tipo: string, descricao: string}[]>([]);
const categoriaForm = useForm({
    id: null as number | null,
    nome: '',
    tipo: 'receita',
    descricao: '',
});

async function loadCategorias() {
    try {
        const res = await axios.get('/financeiro-categorias');
        categoriasBanco.value = res.data;
    } catch(e) { console.error(e); }
}

function openCategoriasModal() {
    categoriaForm.reset();
    categoriaForm.clearErrors();
    loadCategorias();
    showCategoriasModal.value = true;
}

function saveCategoria() {
    if (categoriaForm.id) {
        categoriaForm.put(`/financeiro-categorias/${categoriaForm.id}`, {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                categoriaForm.reset();
                loadCategorias();
            }
        });
    } else {
        categoriaForm.post('/financeiro-categorias', {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                categoriaForm.reset();
                loadCategorias();
            }
        });
    }
}

function editCategoria(cat: {id: number, nome: string, tipo: string, descricao: string}) {
    categoriaForm.id = cat.id;
    categoriaForm.nome = cat.nome;
    categoriaForm.tipo = cat.tipo as "receita" | "despesa";
    categoriaForm.descricao = cat.descricao || '';
}

function deleteCategoria(id: number) {
    if (confirm('Tem certeza que deseja remover esta categoria?')) {
        router.delete(`/financeiro-categorias/${id}`, {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => loadCategorias()
        });
    }
}

const meses = [
    { value: '1', label: 'Janeiro' }, { value: '2', label: 'Fevereiro' }, { value: '3', label: 'Março' },
    { value: '4', label: 'Abril' }, { value: '5', label: 'Maio' }, { value: '6', label: 'Junho' },
    { value: '7', label: 'Julho' }, { value: '8', label: 'Agosto' }, { value: '9', label: 'Setembro' },
    { value: '10', label: 'Outubro' }, { value: '11', label: 'Novembro' }, { value: '12', label: 'Dezembro' },
];
</script>

<template>
    <Head title="Financeiro" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-foreground">Financeiro</h1>
                    <p class="text-sm text-muted-foreground">Controle de receitas e despesas</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a :href="`/export/financeiro/excel?tipo=${tipo}&mes=${mes}&ano=${ano}`" class="inline-flex items-center gap-2 rounded-lg border border-border px-3 py-2.5 text-sm font-medium hover:bg-muted">
                        <FileSpreadsheet class="h-4 w-4" /> Excel
                    </a>
                    <a :href="`/export/financeiro/pdf?mes=${mes}&ano=${ano}`" class="inline-flex items-center gap-2 rounded-lg border border-border px-3 py-2.5 text-sm font-medium hover:bg-muted">
                        <FileText class="h-4 w-4" /> PDF
                    </a>
                    <button @click="openCategoriasModal" class="inline-flex items-center gap-2 rounded-lg border border-border px-3 py-2.5 text-sm font-medium hover:bg-muted">
                        <List class="h-4 w-4" /> Categorias
                    </button>
                    <Link href="/financeiro/dashboard" class="inline-flex items-center gap-2 rounded-lg border border-border px-3 py-2.5 text-sm font-medium hover:bg-muted">
                        <PieChart class="h-4 w-4" /> Painel
                    </Link>
                    <Link href="/financeiro/doadores" class="inline-flex items-center gap-2 rounded-lg border border-border px-3 py-2.5 text-sm font-medium hover:bg-muted">
                        <Users class="h-4 w-4" /> Doadores
                    </Link>
                    <Link href="/financeiro/create" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-primary-foreground shadow-sm hover:bg-primary/90">
                        <Plus class="h-4 w-4" /> Novo Lançamento
                    </Link>
                </div>
            </div>

            <!-- Resumo Cards -->
            <div class="grid gap-4 sm:grid-cols-3">
                <div class="rounded-xl border border-border bg-card p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Entradas</p>
                            <p class="text-2xl font-bold text-emerald-600">{{ formatCurrency(resumo.entradas) }}</p>
                        </div>
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/10">
                            <TrendingUp class="h-5 w-5 text-emerald-600" />
                        </div>
                    </div>
                </div>
                <div class="rounded-xl border border-border bg-card p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Saídas</p>
                            <p class="text-2xl font-bold text-red-600">{{ formatCurrency(resumo.saidas) }}</p>
                        </div>
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-red-500/10">
                            <TrendingDown class="h-5 w-5 text-red-600" />
                        </div>
                    </div>
                </div>
                <div class="rounded-xl border border-border bg-card p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Saldo</p>
                            <p class="text-2xl font-bold" :class="resumo.saldo >= 0 ? 'text-emerald-600' : 'text-red-600'">
                                {{ formatCurrency(resumo.saldo) }}
                            </p>
                        </div>
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl" :class="resumo.saldo >= 0 ? 'bg-emerald-500/10' : 'bg-red-500/10'">
                            <DollarSign class="h-5 w-5" :class="resumo.saldo >= 0 ? 'text-emerald-600' : 'text-red-600'" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="flex flex-wrap gap-3 rounded-xl border border-border bg-card p-4">
                <div class="relative min-w-[200px] flex-1">
                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <input v-model="busca" type="text" placeholder="Buscar descrição..." class="h-10 w-full rounded-lg border border-input bg-background pl-10 pr-4 text-sm outline-none focus:border-primary" />
                </div>
                <select v-model="tipo" class="h-10 rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary">
                    <option value="">Todos os tipos</option>
                    <option value="entrada">Entradas</option>
                    <option value="saida">Saídas</option>
                </select>
                <select v-model="categoriaId" class="h-10 rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary">
                    <option value="">Todas as categorias</option>
                    <option v-for="c in categorias" :key="c.id" :value="c.id">{{ c.nome }} ({{ c.tipo === 'receita' ? '↑' : '↓' }})</option>
                </select>
                <select v-model="unidadeId" class="h-10 rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary">
                    <option value="">Todas as unidades</option>
                    <option v-for="u in unidades" :key="u.id" :value="u.id">{{ u.nome }}</option>
                </select>
                <select v-model="mes" class="h-10 rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary">
                    <option value="">Todos os meses</option>
                    <option v-for="m in meses" :key="m.value" :value="m.value">{{ m.label }}</option>
                </select>
            </div>

            <!-- Table -->
            <div class="overflow-hidden rounded-xl border border-border bg-card shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-border bg-muted/50">
                                <th class="px-4 py-3 text-left font-medium text-muted-foreground">Data</th>
                                <th class="px-4 py-3 text-left font-medium text-muted-foreground">Descrição</th>
                                <th class="hidden px-4 py-3 text-left font-medium text-muted-foreground md:table-cell">Categoria</th>
                                <th class="px-4 py-3 text-left font-medium text-muted-foreground">Tipo</th>
                                <th class="hidden px-4 py-3 text-left font-medium text-muted-foreground lg:table-cell">Unidade</th>
                                <th class="px-4 py-3 text-right font-medium text-muted-foreground">Valor</th>
                                <th class="px-4 py-3 text-right font-medium text-muted-foreground">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="l in lancamentos.data" :key="l.id" class="border-b border-border transition-colors last:border-0 hover:bg-muted/30">
                                <td class="px-4 py-3 text-muted-foreground">{{ formatDate(l.data) }}</td>
                                <td class="px-4 py-3">
                                    <p class="font-medium text-foreground">{{ l.descricao }}</p>
                                    <p v-if="l.doador" class="text-xs text-muted-foreground">Doador: {{ l.doador.nome }}</p>
                                </td>
                                <td class="hidden px-4 py-3 text-muted-foreground md:table-cell">{{ l.categoria?.nome || '-' }}</td>
                                <td class="px-4 py-3"><StatusBadge :status="l.tipo" size="sm" /></td>
                                <td class="hidden px-4 py-3 text-muted-foreground lg:table-cell">{{ l.unidade?.nome || '-' }}</td>
                                <td class="px-4 py-3 text-right font-bold" :class="l.tipo === 'entrada' ? 'text-emerald-600' : 'text-red-600'">
                                    {{ l.tipo === 'entrada' ? '+' : '-' }}{{ formatCurrency(Number(l.valor)) }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-1">
                                        <Link :href="`/financeiro/${l.id}/edit`" class="rounded-lg p-2 text-muted-foreground hover:bg-muted hover:text-foreground"><Pencil class="h-4 w-4" /></Link>
                                        <button @click="confirmDelete(l)" class="rounded-lg p-2 text-muted-foreground hover:bg-red-50 hover:text-red-600"><Trash2 class="h-4 w-4" /></button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!lancamentos.data.length">
                                <td colspan="6" class="px-4 py-8 text-center text-muted-foreground">Nenhum lançamento encontrado.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <Pagination :links="lancamentos.links" />
            </div>
        </div>

        <!-- Modal: Categorias -->
        <div v-if="showCategoriasModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-lg rounded-xl bg-card p-6 shadow-lg relative max-h-[90vh] flex flex-col">
                <div class="mb-4 flex items-center justify-between shrink-0">
                    <h2 class="text-lg font-bold">Categorias do Financeiro</h2>
                    <button @click="showCategoriasModal = false" class="rounded p-1 hover:bg-muted"><X class="h-5 w-5" /></button>
                </div>
                
                <form @submit.prevent="saveCategoria" class="mb-6 flex flex-col gap-3 shrink-0 rounded-lg bg-muted/30 p-4 border border-border">
                    <div class="flex gap-3">
                        <div class="flex-1">
                            <label class="mb-1 block text-xs font-medium text-muted-foreground">Nome da Categoria</label>
                            <input v-model="categoriaForm.nome" type="text" placeholder="Ex: Doações" class="h-10 w-full rounded-lg text-foreground border border-input bg-background px-3 text-sm outline-none focus:border-primary" required />
                        </div>
                        <div class="w-32">
                            <label class="mb-1 block text-xs font-medium text-muted-foreground">Tipo</label>
                            <select v-model="categoriaForm.tipo" class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary">
                                <option value="receita">Receita</option>
                                <option value="despesa">Despesa</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex gap-2 items-end">
                        <div class="flex-1">
                            <label class="mb-1 block text-xs font-medium text-muted-foreground">Descrição (Opcional)</label>
                            <input v-model="categoriaForm.descricao" type="text" placeholder="Ex: Entradas referentes a doações online" class="h-10 w-full text-foreground rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary" />
                        </div>
                        <button type="submit" :disabled="categoriaForm.processing" class="flex h-10 items-center justify-center rounded-lg bg-primary px-4 text-sm font-medium text-primary-foreground hover:bg-primary/90 disabled:opacity-50">
                            {{ categoriaForm.id ? 'Salvar' : 'Adicionar' }}
                        </button>
                        <button v-if="categoriaForm.id" type="button" @click="categoriaForm.reset(); categoriaForm.id = null" class="flex h-10 w-10 items-center justify-center rounded-lg border border-input text-muted-foreground hover:bg-muted" title="Cancelar Edição">
                            <X class="h-4 w-4" />
                        </button>
                    </div>
                </form>

                <div class="overflow-y-auto rounded-lg border border-border flex-1">
                    <table class="w-full text-left text-sm">
                        <tbody class="divide-y divide-border">
                            <tr v-for="c in categoriasBanco" :key="c.id" class="hover:bg-muted/50 transition-colors">
                                <td class="p-3">
                                    <p class="font-medium text-foreground">{{ c.nome }}</p>
                                    <p v-if="c.descricao" class="text-xs text-muted-foreground">{{ c.descricao }}</p>
                                </td>
                                <td class="p-3 w-24">
                                    <StatusBadge :status="c.tipo" size="sm" />
                                </td>
                                <td class="w-20 p-2 text-right">
                                    <button @click="editCategoria(c)" class="p-1.5 text-muted-foreground hover:text-primary transition-colors"><Pencil class="h-3.5 w-3.5" /></button>
                                    <button @click="deleteCategoria(c.id)" class="p-1.5 text-muted-foreground hover:text-red-500 transition-colors"><Trash2 class="h-3.5 w-3.5" /></button>
                                </td>
                            </tr>
                            <tr v-if="!categoriasBanco.length">
                                <td colspan="3" class="p-4 text-center text-muted-foreground">Nenhuma categoria cadastrada.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </AppLayout>
</template>
