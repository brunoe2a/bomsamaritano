<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { Plus, Eye, Pencil, Trash2, Search, List, X, Check } from 'lucide-vue-next';
import axios from 'axios';
import AppLayout from '@/layouts/AppLayout.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import Pagination from '@/components/Pagination.vue';
import { useSwal } from '@/composables/useSwal';
import type { Voluntario, PaginatedData, BreadcrumbItem } from '@/types';

const { confirmDelete: swalDelete } = useSwal();

const props = defineProps<{
    voluntarios: PaginatedData<Voluntario>;
    filtros: Record<string, string>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Voluntários', href: '/voluntarios' },
];

const busca = ref(props.filtros.busca || '');
const status = ref(props.filtros.status || '');

let timeout: ReturnType<typeof setTimeout>;
function applyFilters() {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get('/voluntarios', {
            busca: busca.value || undefined,
            status: status.value || undefined,
        }, { preserveState: true, replace: true });
    }, 300);
}
watch([busca, status], applyFilters);

function confirmDelete(v: Voluntario) {
    swalDelete(`O voluntário "${v.nome}" será removido.`, `/voluntarios/${v.id}`);
}

const showAreasModal = ref(false);
const areasAtuacao = ref<{id: number, nome: string}[]>([]);
const areaForm = useForm({
    id: null as number | null,
    nome: '',
});

async function loadAreas() {
    try {
        const res = await axios.get('/area-atuacao');
        areasAtuacao.value = res.data;
    } catch(e) { console.error(e); }
}

function openAreasModal() {
    areaForm.reset();
    areaForm.clearErrors();
    loadAreas();
    showAreasModal.value = true;
}

function saveArea() {
    if (areaForm.id) {
        areaForm.put(`/area-atuacao/${areaForm.id}`, {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                areaForm.reset();
                loadAreas();
            }
        });
    } else {
        areaForm.post('/area-atuacao', {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                areaForm.reset();
                loadAreas();
            }
        });
    }
}

function editArea(area: {id: number, nome: string}) {
    areaForm.id = area.id;
    areaForm.nome = area.nome;
}

function deleteArea(id: number) {
    if (confirm('Tem certeza que deseja remover esta área?')) {
        router.delete(`/area-atuacao/${id}`, {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => loadAreas()
        });
    }
}
</script>

<template>
    <Head title="Voluntários" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-foreground">Voluntários</h1>
                    <p class="text-sm text-muted-foreground">{{ voluntarios.total }} voluntário(s)</p>
                </div>
                <div class="flex items-center gap-2">
                    <button @click="openAreasModal" class="inline-flex items-center gap-2 rounded-lg border border-input bg-background px-4 py-2.5 text-sm font-medium hover:bg-muted text-foreground shadow-sm">
                        <List class="h-4 w-4" /> Áreas de Atuação
                    </button>
                    <Link href="/voluntarios/create" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-primary-foreground shadow-sm hover:bg-primary/90">
                        <Plus class="h-4 w-4" /> Novo Voluntário
                    </Link>
                </div>
            </div>

            <div class="flex flex-col gap-3 rounded-xl border border-border bg-card p-4 sm:flex-row">
                <div class="relative flex-1">
                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <input v-model="busca" type="text" placeholder="Buscar..." class="h-10 w-full rounded-lg border border-input bg-background pl-10 pr-4 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20" />
                </div>
                <select v-model="status" class="h-10 rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary">
                    <option value="">Todos</option>
                    <option value="ativo">Ativo</option>
                    <option value="inativo">Inativo</option>
                </select>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div v-for="vol in voluntarios.data" :key="vol.id" class="group rounded-xl border border-border bg-card p-5 shadow-sm transition-all hover:shadow-md">
                    <div class="mb-3 flex items-start gap-3">
                        <div class="flex h-12 w-12 shrink-0 overflow-hidden items-center justify-center rounded-full border border-primary/20 bg-emerald-500/15 text-lg font-bold text-emerald-600">
                            <img v-if="vol.foto" :src="`/storage/${vol.foto}`" :alt="vol.nome" class="h-full w-full object-cover" />
                            <span v-else>{{ vol?.nome?.charAt(0) || '' }}</span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <h3 class="truncate text-base font-semibold text-foreground">{{ vol.nome }}</h3>
                            <p v-if="vol.area_atuacao" class="text-sm text-muted-foreground">{{ vol.area_atuacao }}</p>
                        </div>
                        <StatusBadge :status="vol.status" size="sm" />
                    </div>
                    <div class="flex justify-end gap-1 opacity-0 transition-opacity group-hover:opacity-100">
                        <Link :href="`/voluntarios/${vol.id}`" class="rounded-lg p-2 text-muted-foreground hover:bg-muted hover:text-foreground"><Eye class="h-4 w-4" /></Link>
                        <Link :href="`/voluntarios/${vol.id}/edit`" class="rounded-lg p-2 text-muted-foreground hover:bg-muted hover:text-foreground"><Pencil class="h-4 w-4" /></Link>
                        <button @click="confirmDelete(vol)" class="rounded-lg p-2 text-muted-foreground hover:bg-red-50 hover:text-red-600"><Trash2 class="h-4 w-4" /></button>
                    </div>
                </div>
                <div v-if="!voluntarios.data.length" class="col-span-full py-12 text-center text-muted-foreground">Nenhum voluntário encontrado.</div>
            </div>
            <Pagination :links="voluntarios.links" />
        </div>

        <!-- Modal: Áreas de Atuação -->
        <div v-if="showAreasModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-md rounded-xl bg-card p-6 shadow-lg relative max-h-[90vh] flex flex-col">
                <div class="mb-4 flex items-center justify-between shrink-0">
                    <h2 class="text-lg font-bold">Áreas de Atuação</h2>
                    <button @click="showAreasModal = false" class="rounded p-1 hover:bg-muted"><X class="h-5 w-5" /></button>
                </div>
                
                <form @submit.prevent="saveArea" class="mb-6 flex gap-2 shrink-0">
                    <input v-model="areaForm.nome" type="text" placeholder="Nome da Área (ex: Manutenção)" class="h-10 text-foreground flex-1 rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20" required />
                    <button type="submit" :disabled="areaForm.processing" class="flex h-10 items-center justify-center gap-1 rounded-lg bg-primary px-4 text-sm font-medium text-primary-foreground hover:bg-primary/90 disabled:opacity-50">
                        {{ areaForm.id ? 'Salvar' : 'Adicionar' }}
                    </button>
                    <button v-if="areaForm.id" type="button" @click="areaForm.reset(); areaForm.id = null" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-input text-muted-foreground hover:bg-muted" title="Cancelar Edição">
                        <X class="h-4 w-4" />
                    </button>
                </form>

                <div class="overflow-y-auto rounded-lg border border-border flex-1">
                    <table class="w-full text-left text-sm">
                        <tbody class="divide-y divide-border">
                            <tr v-for="a in areasAtuacao" :key="a.id" class="hover:bg-muted/50 transition-colors">
                                <td class="p-3 text-foreground font-medium">{{ a.nome }}</td>
                                <td class="w-20 p-2 text-right">
                                    <button @click="editArea(a)" class="p-1.5 text-muted-foreground hover:text-primary transition-colors"><Pencil class="h-3.5 w-3.5" /></button>
                                    <button @click="deleteArea(a.id)" class="p-1.5 text-muted-foreground hover:text-red-500 transition-colors"><Trash2 class="h-3.5 w-3.5" /></button>
                                </td>
                            </tr>
                            <tr v-if="!areasAtuacao.length">
                                <td colspan="2" class="p-4 text-center text-muted-foreground">Nenhuma área cadastrada.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </AppLayout>
</template>
