<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { Plus, Eye, Trash2, BarChart3, CalendarDays } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import Pagination from '@/components/Pagination.vue';
import SearchableSelect from '@/components/SearchableSelect.vue';
import { useSwal } from '@/composables/useSwal';
import type { PaginatedData, BreadcrumbItem, Unidade } from '@/types';

interface Expediente {
    id: number;
    data: string;
    descricao?: string | null;
    unidade?: { id: number; nome: string };
    escalados_count: number;
    faltas_count: number;
}

const { confirmDelete: swalDelete } = useSwal();

const props = defineProps<{
    expedientes: PaginatedData<Expediente>;
    filtros: Record<string, string>;
    unidades: Unidade[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Expediente', href: '/expedientes' },
];

const unidadeId = ref<string | number>(props.filtros.unidade_id || '');
const mes = ref(props.filtros.mes || '');

const unidadeOptions = computed(() => props.unidades.map(u => ({ value: u.id, label: u.nome })));

let debounceTimeout: ReturnType<typeof setTimeout>;
function applyFilters() {
    clearTimeout(debounceTimeout);
    debounceTimeout = setTimeout(() => {
        router.get('/expedientes', {
            unidade_id: unidadeId.value || undefined,
            mes: mes.value || undefined,
        }, { preserveState: true, replace: true });
    }, 300);
}
watch([unidadeId, mes], applyFilters);

function confirmDelete(exp: Expediente) {
    swalDelete(`O expediente do dia ${formatDate(exp.data)} será removido.`, `/expedientes/${exp.id}`);
}

function formatDate(d: string): string {
    if (!d) return '-';
    const [y, m, day] = d.split('-');
    return `${day}/${m}/${y}`;
}
</script>

<template>
    <Head title="Expediente" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-foreground">Expediente</h1>
                    <p class="text-sm text-muted-foreground">{{ expedientes.total }} expediente(s) — escala de professores e voluntários</p>
                </div>
                <div class="flex gap-2">
                    <Link href="/expedientes/relatorio" class="inline-flex items-center gap-2 rounded-lg border border-border px-4 py-2.5 text-sm font-medium text-foreground hover:bg-muted">
                        <BarChart3 class="h-4 w-4" /> Relatório
                    </Link>
                    <Link href="/expedientes-novo" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-primary-foreground shadow-sm hover:bg-primary/90">
                        <Plus class="h-4 w-4" /> Novo Expediente
                    </Link>
                </div>
            </div>

            <div class="flex flex-col gap-3 rounded-xl border border-border bg-card p-4 sm:flex-row">
                <div class="flex-1">
                    <SearchableSelect
                        v-model="unidadeId"
                        :options="unidadeOptions"
                        placeholder="Todas as unidades"
                        :allow-empty="true"
                        empty-label="Todas as unidades"
                    />
                </div>
                <input v-model="mes" type="month" class="h-10 rounded-lg border border-border bg-background px-3 text-sm outline-none transition-colors focus:border-primary focus:ring-1 focus:ring-primary sm:w-48" />
            </div>

            <div class="overflow-hidden rounded-xl border border-border bg-card shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-border bg-muted/50">
                                <th class="px-4 py-3 text-left font-medium text-muted-foreground">Data</th>
                                <th class="px-4 py-3 text-left font-medium text-muted-foreground">Unidade</th>
                                <th class="hidden px-4 py-3 text-left font-medium text-muted-foreground md:table-cell">Descrição</th>
                                <th class="px-4 py-3 text-center font-medium text-muted-foreground">Escalados</th>
                                <th class="px-4 py-3 text-center font-medium text-muted-foreground">Faltas</th>
                                <th class="px-4 py-3 text-right font-medium text-muted-foreground">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="exp in expedientes.data" :key="exp.id" class="border-b border-border last:border-0 hover:bg-muted/30">
                                <td class="px-4 py-3 font-medium">
                                    <div class="flex items-center gap-2">
                                        <CalendarDays class="h-4 w-4 text-muted-foreground" />
                                        {{ formatDate(exp.data) }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-muted-foreground">{{ exp.unidade?.nome ?? '-' }}</td>
                                <td class="hidden px-4 py-3 text-muted-foreground md:table-cell">{{ exp.descricao || '-' }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="rounded-lg bg-primary/10 px-2 py-1 text-xs font-bold text-primary">{{ exp.escalados_count }}</span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span v-if="exp.faltas_count > 0" class="rounded-lg bg-red-50 px-2 py-1 text-xs font-bold text-red-600">{{ exp.faltas_count }}</span>
                                    <span v-else class="text-xs text-muted-foreground">0</span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-1">
                                        <Link :href="`/expedientes/${exp.id}`" class="rounded-lg p-2 text-muted-foreground hover:bg-muted hover:text-foreground" title="Ver / marcar faltas">
                                            <Eye class="h-4 w-4" />
                                        </Link>
                                        <button @click="confirmDelete(exp)" class="rounded-lg p-2 text-muted-foreground hover:bg-red-50 hover:text-red-600">
                                            <Trash2 class="h-4 w-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!expedientes.data.length">
                                <td colspan="6" class="px-4 py-8 text-center text-muted-foreground">Nenhum expediente encontrado.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <Pagination :links="expedientes.links" />
            </div>
        </div>
    </AppLayout>
</template>
