<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { Plus, Eye, Pencil, Trash2, Search } from 'lucide-vue-next';
import { PhoneIcon, BookOpenIcon } from '@heroicons/vue/24/outline';
import AppLayout from '@/layouts/AppLayout.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import Pagination from '@/components/Pagination.vue';
import { useSwal } from '@/composables/useSwal';
import type { Professor, PaginatedData, BreadcrumbItem } from '@/types';

const { confirmDelete: swalDelete } = useSwal();

const props = defineProps<{
    professores: PaginatedData<Professor & { turmas_count?: number }>;
    filtros: Record<string, string>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Professores', href: '/professores' },
];

const busca = ref(props.filtros.busca || '');
const status = ref(props.filtros.status || '');
const tipoVinculo = ref(props.filtros.tipo_vinculo || '');

let timeout: ReturnType<typeof setTimeout>;
function applyFilters() {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get('/professores', {
            busca: busca.value || undefined,
            status: status.value || undefined,
            tipo_vinculo: tipoVinculo.value || undefined,
        }, { preserveState: true, replace: true });
    }, 300);
}
watch([busca, status, tipoVinculo], applyFilters);

function confirmDelete(p: Professor) {
    swalDelete(`O professor "${p.nome}" será removido.`, `/professores/${p.id}`);
}
</script>

<template>
    <Head title="Professores" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-foreground">Professores</h1>
                    <p class="text-sm text-muted-foreground">{{ professores.total }} professor(es)</p>
                </div>
                <Link href="/professores/create" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-primary-foreground shadow-sm hover:bg-primary/90">
                    <Plus class="h-4 w-4" /> Novo Professor
                </Link>
            </div>

            <div class="flex flex-col gap-3 rounded-xl border border-border bg-card p-4 sm:flex-row">
                <div class="relative flex-1">
                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <input v-model="busca" type="text" placeholder="Buscar por nome..." class="h-10 w-full rounded-lg border border-input bg-background pl-10 pr-4 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20" />
                </div>
                <select v-model="status" class="h-10 rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary">
                    <option value="">Todos os status</option>
                    <option value="ativo">Ativo</option>
                    <option value="inativo">Inativo</option>
                </select>
                <select v-model="tipoVinculo" class="h-10 rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary">
                    <option value="">Todos os vínculos</option>
                    <option value="voluntario">Voluntário</option>
                    <option value="contratado">Contratado</option>
                </select>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div v-for="prof in professores.data" :key="prof.id" class="group rounded-xl border border-border bg-card p-5 shadow-sm transition-all hover:shadow-md">
                    <div class="mb-3 flex items-start gap-3">
                        <div class="flex h-12 w-12 shrink-0 overflow-hidden items-center justify-center rounded-full border border-primary/20 bg-primary/15 text-lg font-bold text-primary">
                            <img v-if="prof.foto" :src="`/storage/${prof.foto}`" :alt="prof.nome" class="h-full w-full object-cover" />
                            <span v-else>{{ prof?.nome?.charAt(0) || '' }}</span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <h3 class="truncate text-base font-semibold text-foreground">{{ prof.nome }}</h3>
                            <div class="mt-1 flex flex-wrap gap-1">
                                <span v-for="e in (prof.especialidade || [])" :key="e" class="rounded bg-primary/10 px-1.5 py-0.5 text-xs font-medium text-primary">{{ e }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3 flex items-center gap-3 text-sm text-muted-foreground">
                        <StatusBadge :status="prof.status" size="sm" />
                        <StatusBadge :status="prof.tipo_vinculo" size="sm" />
                    </div>
                    <div class="mb-3 flex gap-4 text-sm text-muted-foreground">
                        <span><PhoneIcon class="mr-1 inline-block size-4" /> {{ prof.telefone || '-' }}</span>
                        <span><BookOpenIcon class="mr-1 inline-block size-4" /> {{ prof.turmas_count || 0 }} turma(s)</span>
                    </div>
                    <div class="flex justify-end gap-1 opacity-0 transition-opacity group-hover:opacity-100">
                        <Link :href="`/professores/${prof.id}`" class="rounded-lg p-2 text-muted-foreground hover:bg-muted hover:text-foreground"><Eye class="h-4 w-4" /></Link>
                        <Link :href="`/professores/${prof.id}/edit`" class="rounded-lg p-2 text-muted-foreground hover:bg-muted hover:text-foreground"><Pencil class="h-4 w-4" /></Link>
                        <button @click="confirmDelete(prof)" class="rounded-lg p-2 text-muted-foreground hover:bg-red-50 hover:text-red-600"><Trash2 class="h-4 w-4" /></button>
                    </div>
                </div>
                <div v-if="!professores.data.length" class="col-span-full py-12 text-center text-muted-foreground">Nenhum professor encontrado.</div>
            </div>
            <Pagination :links="professores.links" />
        </div>
    </AppLayout>
</template>
