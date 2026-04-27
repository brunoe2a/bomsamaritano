<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { Search, Plus, Eye, Pencil, Trash2, Users } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import Pagination from '@/components/Pagination.vue';
import { useSwal } from '@/composables/useSwal';
import type { PaginatedData, BreadcrumbItem } from '@/types';

interface Responsavel {
    id: number;
    nome: string;
    cpf: string | null;
    telefone: string | null;
    whatsapp: string | null;
    endereco_cidade: string | null;
    endereco_estado: string | null;
    alunos_count: number;
}

const { confirmDelete: swalDelete } = useSwal();

const props = defineProps<{
    responsaveis: PaginatedData<Responsavel>;
    filtros: Record<string, string>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Responsáveis', href: '/responsaveis' },
];

const busca = ref(props.filtros.busca || '');
let debounceTimeout: ReturnType<typeof setTimeout>;

function applyFilters() {
    clearTimeout(debounceTimeout);
    debounceTimeout = setTimeout(() => {
        router.get('/responsaveis', { busca: busca.value || undefined }, { preserveState: true, replace: true });
    }, 300);
}

watch(busca, applyFilters);

function confirmDelete(r: Responsavel) {
    if (r.alunos_count > 0) {
        return;
    }
    swalDelete(`O responsável "${r.nome}" será removido permanentemente.`, `/responsaveis/${r.id}`);
}
</script>

<template>
    <Head title="Responsáveis" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-foreground">Responsáveis</h1>
                    <p class="text-sm text-muted-foreground">{{ responsaveis.total }} responsável(is) cadastrado(s)</p>
                </div>
                <Link
                    href="/responsaveis/create"
                    class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow-sm transition-colors hover:bg-primary/90"
                >
                    <Plus class="h-4 w-4" /> Novo Responsável
                </Link>
            </div>

            <div class="rounded-xl border border-border bg-card p-4 shadow-sm">
                <div class="relative">
                    <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <input
                        v-model="busca"
                        type="text"
                        placeholder="Buscar por nome, CPF ou telefone..."
                        class="h-10 w-full rounded-lg border border-input bg-background pl-9 pr-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20"
                    />
                </div>
            </div>

            <div class="overflow-hidden rounded-xl border border-border bg-card shadow-sm">
                <table class="w-full text-sm">
                    <thead class="border-b border-border bg-muted/40 text-xs uppercase tracking-wider text-muted-foreground">
                        <tr>
                            <th class="px-4 py-3 text-left">Nome</th>
                            <th class="px-4 py-3 text-left">CPF</th>
                            <th class="px-4 py-3 text-left">Contato</th>
                            <th class="px-4 py-3 text-left">Cidade</th>
                            <th class="px-4 py-3 text-center">Filhos</th>
                            <th class="px-4 py-3 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!responsaveis.data.length">
                            <td colspan="6" class="p-8 text-center text-muted-foreground">Nenhum responsável encontrado.</td>
                        </tr>
                        <tr v-for="r in responsaveis.data" :key="r.id" class="border-b border-border last:border-0 hover:bg-muted/30">
                            <td class="px-4 py-3 font-medium text-foreground">{{ r.nome }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ r.cpf || '—' }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ r.telefone || r.whatsapp || '—' }}</td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ r.endereco_cidade ? `${r.endereco_cidade}/${r.endereco_estado || ''}` : '—' }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center gap-1 rounded-full bg-primary/10 px-2 py-0.5 text-xs font-medium text-primary">
                                    <Users class="h-3 w-3" /> {{ r.alunos_count }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="inline-flex gap-1">
                                    <Link :href="`/responsaveis/${r.id}`" class="rounded-md p-1.5 hover:bg-muted" title="Ver">
                                        <Eye class="h-4 w-4 text-muted-foreground" />
                                    </Link>
                                    <Link :href="`/responsaveis/${r.id}/edit`" class="rounded-md p-1.5 hover:bg-muted" title="Editar">
                                        <Pencil class="h-4 w-4 text-muted-foreground" />
                                    </Link>
                                    <button
                                        type="button"
                                        :disabled="r.alunos_count > 0"
                                        :title="r.alunos_count > 0 ? 'Possui alunos vinculados' : 'Excluir'"
                                        class="rounded-md p-1.5 transition-colors hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-40 dark:hover:bg-red-950/30"
                                        @click="confirmDelete(r)"
                                    >
                                        <Trash2 class="h-4 w-4 text-red-500" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <Pagination :links="responsaveis.links" />
        </div>
    </AppLayout>
</template>
