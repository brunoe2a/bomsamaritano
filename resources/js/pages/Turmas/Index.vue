<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { Plus, Eye, Pencil, Trash2, Search, ClipboardCheck } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import Pagination from '@/components/Pagination.vue';
import { useSwal } from '@/composables/useSwal';
import type { Turma, Curso, PaginatedData, BreadcrumbItem, Unidade } from '@/types';

const { confirmDelete: swalDelete } = useSwal();

const props = defineProps<{
    turmas: PaginatedData<Turma>;
    filtros: Record<string, string>;
    cursos: Curso[];
    unidades: Unidade[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Turmas', href: '/turmas' },
];

const busca = ref(props.filtros.busca || '');
const cursoId = ref(props.filtros.curso_id || '');
const status = ref(props.filtros.status || '');
const unidadeId = ref(props.filtros.unidade_id || '');

let debounceTimeout: ReturnType<typeof setTimeout>;
function applyFilters() {
    clearTimeout(debounceTimeout);
    debounceTimeout = setTimeout(() => {
        router.get('/turmas', {
            busca: busca.value || undefined,
            curso_id: cursoId.value || undefined,
            status: status.value || undefined,
            unidade_id: unidadeId.value || undefined,
        }, { preserveState: true, replace: true });
    }, 300);
}
watch([busca, cursoId, status, unidadeId], applyFilters);

const periodoLabels: Record<string, string> = { segunda_sexta: 'Seg-Sex', sabados: 'Sábados' };

function confirmDelete(turma: Turma) {
    swalDelete(`A turma "${turma.nome}" será removida.`, `/turmas/${turma.id}`);
}
</script>

<template>
    <Head title="Turmas" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-foreground">Turmas</h1>
                    <p class="text-sm text-muted-foreground">{{ turmas.total }} turma(s)</p>
                </div>
                <Link href="/turmas/create" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-primary-foreground shadow-sm hover:bg-primary/90">
                    <Plus class="h-4 w-4" /> Nova Turma
                </Link>
            </div>

            <div class="flex flex-col gap-3 rounded-xl border border-border bg-card p-4 sm:flex-row">
                <div class="relative flex-1">
                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <input v-model="busca" type="text" placeholder="Buscar turma..." class="h-10 w-full rounded-lg border border-input bg-background pl-10 pr-4 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20" />
                </div>
                <select v-model="cursoId" class="h-10 rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary">
                    <option value="">Todos os cursos</option>
                    <option v-for="c in cursos" :key="c.id" :value="c.id">{{ c.nome }}</option>
                </select>
                <select v-model="status" class="h-10 rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary">
                    <option value="">Todos</option>
                    <option value="planejada">Planejada</option>
                    <option value="em_andamento">Em Andamento</option>
                    <option value="encerrada">Encerrada</option>
                </select>
                <select v-model="unidadeId" class="h-10 rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary">
                    <option value="">Todas as unidades</option>
                    <option v-for="u in unidades" :key="u.id" :value="u.id">{{ u.nome }}</option>
                </select>
            </div>

            <div class="overflow-hidden rounded-xl border border-border bg-card shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-border bg-muted/50">
                                <th class="px-4 py-3 text-left font-medium text-muted-foreground">Turma</th>
                                <th class="hidden px-4 py-3 text-left font-medium text-muted-foreground md:table-cell">Unidade</th>
                                <th class="hidden px-4 py-3 text-left font-medium text-muted-foreground md:table-cell">Curso</th>
                                <th class="hidden px-4 py-3 text-left font-medium text-muted-foreground sm:table-cell">Professor</th>
                                <th class="hidden px-4 py-3 text-left font-medium text-muted-foreground lg:table-cell">Horário</th>
                                <th class="px-4 py-3 text-center font-medium text-muted-foreground">Alunos</th>
                                <th class="px-4 py-3 text-left font-medium text-muted-foreground">Status</th>
                                <th class="px-4 py-3 text-right font-medium text-muted-foreground">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="turma in turmas.data" :key="turma.id" class="border-b border-border transition-colors last:border-0 hover:bg-muted/30">
                                <td class="px-4 py-3">
                                    <p class="font-medium text-foreground">{{ turma.nome }}</p>
                                    <p class="text-xs text-muted-foreground md:hidden">{{ turma.curso?.nome }}</p>
                                </td>
                                <td class="hidden px-4 py-3 text-muted-foreground md:table-cell">{{ turma.unidade?.nome || '-' }}</td>
                                <td class="hidden px-4 py-3 text-muted-foreground md:table-cell">{{ turma.curso?.nome }}</td>
                                <td class="hidden px-4 py-3 text-muted-foreground sm:table-cell">
                                    <template v-if="turma.professores?.length">
                                        {{ turma.professores[0].nome }}
                                        <span v-if="turma.professores.length > 1" class="text-xs text-muted-foreground">
                                            (+{{ turma.professores.length - 1 }})
                                        </span>
                                    </template>
                                    <span v-else>-</span>
                                </td>
                                <td class="hidden px-4 py-3 text-muted-foreground lg:table-cell">
                                    {{ turma.horario_inicio || '-' }} - {{ turma.horario_fim || '-' }}
                                    <span class="ml-1 text-xs">({{ periodoLabels[turma.periodo] }})</span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="rounded-lg bg-primary/10 px-2 py-1 text-xs font-bold text-primary">
                                        {{ turma.alunos_count || 0 }}/{{ turma.capacidade_maxima }}
                                    </span>
                                </td>
                                <td class="px-4 py-3"><StatusBadge :status="turma.status" size="sm" /></td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-1">
                                        <Link :href="`/turmas/${turma.id}/chamada`" class="rounded-lg p-2 text-emerald-600 transition-colors hover:bg-emerald-50" title="Fazer Chamada">
                                            <ClipboardCheck class="h-4 w-4" />
                                        </Link>
                                        <Link :href="`/turmas/${turma.id}`" class="rounded-lg p-2 text-muted-foreground hover:bg-muted hover:text-foreground">
                                            <Eye class="h-4 w-4" />
                                        </Link>
                                        <Link :href="`/turmas/${turma.id}/edit`" class="rounded-lg p-2 text-muted-foreground hover:bg-muted hover:text-foreground">
                                            <Pencil class="h-4 w-4" />
                                        </Link>
                                        <button @click="confirmDelete(turma)" class="rounded-lg p-2 text-muted-foreground hover:bg-red-50 hover:text-red-600">
                                            <Trash2 class="h-4 w-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!turmas.data.length">
                                <td colspan="7" class="px-4 py-8 text-center text-muted-foreground">Nenhuma turma encontrada.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <Pagination :links="turmas.links" />
            </div>
        </div>
    </AppLayout>
</template>
