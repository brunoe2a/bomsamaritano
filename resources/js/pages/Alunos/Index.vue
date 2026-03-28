<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { Search, Plus, Eye, Pencil, Trash2, FileSpreadsheet } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import Pagination from '@/components/Pagination.vue';
import { useSwal } from '@/composables/useSwal';
import type { Aluno, Curso, Turma, PaginatedData, BreadcrumbItem } from '@/types';

const { confirmDelete: swalDelete } = useSwal();

const props = defineProps<{
    alunos: PaginatedData<Aluno>;
    filtros: Record<string, string>;
    cursos: Curso[];
    turmas: Turma[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Alunos', href: '/alunos' },
];

const busca = ref(props.filtros.busca || '');
const status = ref(props.filtros.status || '');
const cursoId = ref(props.filtros.curso_id || '');

let debounceTimeout: ReturnType<typeof setTimeout>;

function applyFilters() {
    clearTimeout(debounceTimeout);
    debounceTimeout = setTimeout(() => {
        router.get('/alunos', {
            busca: busca.value || undefined,
            status: status.value || undefined,
            curso_id: cursoId.value || undefined,
        }, { preserveState: true, replace: true });
    }, 300);
}

watch([busca, status, cursoId], applyFilters);

function confirmDelete(aluno: Aluno) {
    swalDelete(`O aluno "${aluno.nome}" será removido permanentemente.`, `/alunos/${aluno.id}`);
}
</script>

<template>
    <Head title="Alunos" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-foreground">Alunos</h1>
                    <p class="text-sm text-muted-foreground">
                        {{ alunos.total }} aluno(s) cadastrado(s)
                    </p>
                </div>
                <div class="flex gap-2">
                    <a
                        :href="`/export/alunos/excel?status=${status}&curso_id=${cursoId}`"
                        class="inline-flex items-center gap-2 rounded-lg border border-border px-4 py-2.5 text-sm font-medium hover:bg-muted"
                    >
                        <FileSpreadsheet class="h-4 w-4" />
                        Excel
                    </a>
                    <Link
                        href="/alunos/create"
                        class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-primary-foreground shadow-sm transition-colors hover:bg-primary/90"
                    >
                        <Plus class="h-4 w-4" />
                        Novo Aluno
                    </Link>
                </div>
            </div>

            <!-- Filters -->
            <div class="flex flex-col gap-3 rounded-xl border border-border bg-card p-4 sm:flex-row">
                <div class="relative flex-1">
                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <input
                        v-model="busca"
                        type="text"
                        placeholder="Buscar por nome do aluno ou responsável..."
                        class="h-10 w-full rounded-lg border border-input bg-background pl-10 pr-4 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20"
                    />
                </div>
                <select
                    v-model="status"
                    class="h-10 rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary"
                >
                    <option value="">Todos os status</option>
                    <option value="ativo">Ativo</option>
                    <option value="inativo">Inativo</option>
                    <option value="trancado">Trancado</option>
                    <option value="concluido">Concluído</option>
                </select>
                <select
                    v-model="cursoId"
                    class="h-10 rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary"
                >
                    <option value="">Todos os cursos</option>
                    <option v-for="c in cursos" :key="c.id" :value="c.id">{{ c.nome }}</option>
                </select>
            </div>

            <!-- Table -->
            <div class="overflow-hidden rounded-xl border border-border bg-card shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-border bg-muted/50">
                                <th class="px-4 py-3 text-left font-medium text-muted-foreground">Nome</th>
                                <th class="hidden px-4 py-3 text-left font-medium text-muted-foreground md:table-cell">Responsável</th>
                                <th class="hidden px-4 py-3 text-left font-medium text-muted-foreground sm:table-cell">Ano Escolar</th>
                                <th class="px-4 py-3 text-left font-medium text-muted-foreground">Status</th>
                                <th class="px-4 py-3 text-right font-medium text-muted-foreground">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="aluno in alunos.data"
                                :key="aluno.id"
                                class="border-b border-border transition-colors last:border-0 hover:bg-muted/30"
                            >
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex h-9 w-9 overflow-hidden items-center justify-center rounded-full border border-primary/20 bg-primary/15 text-xs font-bold text-primary"
                                        >
                                            <img v-if="aluno.foto" :src="aluno.foto_url" :alt="aluno.nome" class="h-full w-full object-cover" />
                                            <span v-else>{{ aluno?.nome?.charAt(0) || '' }}</span>
                                        </div>
                                        <div>
                                            <p class="font-medium text-foreground">{{ aluno.nome }}</p>
                                            <p class="text-xs text-muted-foreground md:hidden">
                                                {{ aluno.responsavel?.nome }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="hidden px-4 py-3 text-muted-foreground md:table-cell">
                                    {{ aluno.responsavel?.nome || '-' }}
                                </td>
                                <td class="hidden px-4 py-3 text-muted-foreground sm:table-cell">
                                    {{ aluno.ano_escolar || '-' }}
                                </td>
                                <td class="px-4 py-3">
                                    <StatusBadge :status="aluno.status" size="sm" />
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-1">
                                        <Link
                                            :href="`/alunos/${aluno.id}`"
                                            class="rounded-lg p-2 text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                                        >
                                            <Eye class="h-4 w-4" />
                                        </Link>
                                        <Link
                                            :href="`/alunos/${aluno.id}/edit`"
                                            class="rounded-lg p-2 text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                                        >
                                            <Pencil class="h-4 w-4" />
                                        </Link>
                                        <button
                                            @click="confirmDelete(aluno)"
                                            class="rounded-lg p-2 text-muted-foreground transition-colors hover:bg-red-50 hover:text-red-600"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!alunos.data.length">
                                <td colspan="5" class="px-4 py-8 text-center text-muted-foreground">
                                    Nenhum aluno encontrado.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <Pagination :links="alunos.links" />
            </div>
        </div>
    </AppLayout>
</template>
