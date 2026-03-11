<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { Plus, Pencil, Trash2, Search } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import Pagination from '@/components/Pagination.vue';
import { useSwal } from '@/composables/useSwal';
import type { Curso, PaginatedData, BreadcrumbItem } from '@/types';

const { confirmDelete: swalDelete } = useSwal();

const props = defineProps<{
    cursos: PaginatedData<Curso>;
    filtros: Record<string, string>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Cursos', href: '/cursos' },
];

const busca = ref(props.filtros.busca || '');
const status = ref(props.filtros.status || '');

let debounceTimeout: ReturnType<typeof setTimeout>;
function applyFilters() {
    clearTimeout(debounceTimeout);
    debounceTimeout = setTimeout(() => {
        router.get('/cursos', {
            busca: busca.value || undefined,
            status: status.value || undefined,
        }, { preserveState: true, replace: true });
    }, 300);
}
watch([busca, status], applyFilters);

const periodoLabels: Record<string, string> = { manha: 'Manhã', tarde: 'Tarde', noite: 'Noite' };

function confirmDelete(curso: Curso) {
    swalDelete(`O curso "${curso.nome}" será removido.`, `/cursos/${curso.id}`);
}
</script>

<template>
    <Head title="Cursos" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-foreground">Cursos</h1>
                    <p class="text-sm text-muted-foreground">{{ cursos.total }} curso(s)</p>
                </div>
                <Link href="/cursos/create" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-primary-foreground shadow-sm hover:bg-primary/90">
                    <Plus class="h-4 w-4" /> Novo Curso
                </Link>
            </div>

            <div class="flex flex-col gap-3 rounded-xl border border-border bg-card p-4 sm:flex-row">
                <div class="relative flex-1">
                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <input v-model="busca" type="text" placeholder="Buscar por nome..." class="h-10 w-full rounded-lg border border-input bg-background pl-10 pr-4 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20" />
                </div>
                <select v-model="status" class="h-10 rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary">
                    <option value="">Todos</option>
                    <option value="ativo">Ativo</option>
                    <option value="inativo">Inativo</option>
                </select>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div v-for="curso in cursos.data" :key="curso.id" class="group rounded-xl border border-border bg-card p-6 shadow-sm transition-all hover:shadow-md">
                    <div class="mb-4 flex items-start justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-foreground">{{ curso.nome }}</h3>
                            <p class="mt-1 text-sm text-muted-foreground line-clamp-2">{{ curso.descricao || 'Sem descrição' }}</p>
                        </div>
                        <StatusBadge :status="curso.status" size="sm" />
                    </div>
                    <div class="mb-4 flex gap-4 text-sm text-muted-foreground">
                        <span>{{ periodoLabels[curso.periodo] }}</span>
                        <span>{{ curso.carga_horaria || '-' }}h</span>
                        <span>Máx. {{ curso.max_alunos }} alunos</span>
                    </div>
                    <div class="mb-4 flex items-center gap-2">
                        <span class="rounded-lg bg-primary/10 px-2 py-1 text-xs font-medium text-primary">
                            {{ curso.turmas_count || 0 }} turma(s)
                        </span>
                    </div>
                    <div class="flex justify-end gap-1 opacity-0 transition-opacity group-hover:opacity-100">
                        <Link :href="`/cursos/${curso.id}/edit`" class="rounded-lg p-2 text-muted-foreground hover:bg-muted hover:text-foreground">
                            <Pencil class="h-4 w-4" />
                        </Link>
                        <button @click="confirmDelete(curso)" class="rounded-lg p-2 text-muted-foreground hover:bg-red-50 hover:text-red-600">
                            <Trash2 class="h-4 w-4" />
                        </button>
                    </div>
                </div>
                <div v-if="!cursos.data.length" class="col-span-full py-12 text-center text-muted-foreground">
                    Nenhum curso encontrado.
                </div>
            </div>
            <Pagination :links="cursos.links" />
        </div>
    </AppLayout>
</template>
