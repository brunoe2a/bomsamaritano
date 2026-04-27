<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { Plus, Pencil, Trash2, Search, Stethoscope } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import Pagination from '@/components/Pagination.vue';
import SearchableSelect from '@/components/SearchableSelect.vue';
import NativeSelect from '@/components/NativeSelect.vue';
import CreatableSelect from '@/components/CreatableSelect.vue';
import { useSwal } from '@/composables/useSwal';
import type { PaginatedData, BreadcrumbItem } from '@/types';

interface Area {
    id: number;
    nome: string;
}

interface Programa {
    id: number;
    nome: string;
    area_id: number;
    area: Area | null;
    descricao: string | null;
    cor: string | null;
    status: string;
    convocacoes_count: number;
    atendimentos_count: number;
}

const { confirmDelete: swalDelete } = useSwal();

const props = defineProps<{
    programas: PaginatedData<Programa>;
    filtros: Record<string, string>;
    areas: Area[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Núcleo de Saúde', href: '/saude' },
    { title: 'Programas', href: '/saude/programas' },
];

const areaOptions = computed(() => props.areas.map(a => ({ value: a.id, label: a.nome })));
const areaFilterOptions = computed(() => props.areas.map(a => ({ value: String(a.id), label: a.nome })));

const busca = ref(props.filtros.busca || '');
const areaFiltroId = ref(props.filtros.area_id || '');
let debounce: ReturnType<typeof setTimeout>;
function applyFilters() {
    clearTimeout(debounce);
    debounce = setTimeout(() => {
        router.get('/saude/programas', { busca: busca.value || undefined, area_id: areaFiltroId.value || undefined }, { preserveState: true, replace: true });
    }, 300);
}
watch([busca, areaFiltroId], applyFilters);

const showModal = ref(false);
const editing = ref<Programa | null>(null);

const form = useForm({
    nome: '',
    area_id: null as number | null,
    area: '' as string,
    descricao: '',
    cor: '#F5A623',
    status: 'ativo',
});

function openCreate() {
    editing.value = null;
    form.reset();
    form.area_id = null;
    form.area = '';
    form.cor = '#F5A623';
    form.status = 'ativo';
    showModal.value = true;
}

function openEdit(p: Programa) {
    editing.value = p;
    form.nome = p.nome;
    form.area_id = p.area_id;
    form.area = p.area?.nome || '';
    form.descricao = p.descricao || '';
    form.cor = p.cor || '#F5A623';
    form.status = p.status;
    showModal.value = true;
}

function submit() {
    if (editing.value) {
        form.put(`/saude/programas/${editing.value.id}`, { onSuccess: () => (showModal.value = false) });
    } else {
        form.post('/saude/programas', { onSuccess: () => (showModal.value = false) });
    }
}

function confirmDelete(p: Programa) {
    if (p.atendimentos_count > 0 || p.convocacoes_count > 0) return;
    swalDelete(`O programa "${p.nome}" será removido permanentemente.`, `/saude/programas/${p.id}`);
}
</script>

<template>
    <Head title="Programas de Saúde" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="flex items-center gap-2 text-2xl font-bold text-foreground"><Stethoscope class="h-6 w-6" /> Programas de Saúde</h1>
                    <p class="text-sm text-muted-foreground">Catálogo de programas (Saúde Bucal, Psicologia, etc.)</p>
                </div>
                <button @click="openCreate" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow-sm transition-colors hover:bg-primary/90">
                    <Plus class="h-4 w-4" /> Novo Programa
                </button>
            </div>

            <div class="rounded-xl border border-border bg-card p-4 shadow-sm">
                <div class="flex flex-wrap gap-3">
                    <div class="relative flex-1 min-w-[240px]">
                        <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <input v-model="busca" type="text" placeholder="Buscar por nome..." class="h-10 w-full rounded-lg border border-input bg-background pl-9 pr-3 text-sm outline-none focus:border-primary" />
                    </div>
                    <div class="w-56">
                        <SearchableSelect v-model="areaFiltroId" :options="areaFilterOptions" placeholder="Todas as áreas" allow-empty empty-label="Todas as áreas" />
                    </div>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl border border-border bg-card shadow-sm">
                <table class="w-full text-sm">
                    <thead class="border-b border-border bg-muted/40 text-xs uppercase tracking-wider text-muted-foreground">
                        <tr>
                            <th class="px-4 py-3 text-left">Nome</th>
                            <th class="px-4 py-3 text-left">Área</th>
                            <th class="px-4 py-3 text-center">Convocações</th>
                            <th class="px-4 py-3 text-center">Atendimentos</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!programas.data.length"><td colspan="6" class="p-8 text-center text-muted-foreground">Nenhum programa cadastrado.</td></tr>
                        <tr v-for="p in programas.data" :key="p.id" class="border-b border-border last:border-0 hover:bg-muted/30">
                            <td class="px-4 py-3 font-medium text-foreground">
                                <span :style="{ background: p.cor || '#F5A623' }" class="mr-2 inline-block h-3 w-3 rounded-full align-middle"></span>
                                {{ p.nome }}
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">{{ p.area?.nome ?? '—' }}</td>
                            <td class="px-4 py-3 text-center">{{ p.convocacoes_count }}</td>
                            <td class="px-4 py-3 text-center">{{ p.atendimentos_count }}</td>
                            <td class="px-4 py-3">
                                <span :class="p.status === 'ativo' ? 'bg-emerald-100 text-emerald-700' : 'bg-muted text-muted-foreground'" class="rounded-full px-2 py-0.5 text-xs">
                                    {{ p.status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="inline-flex gap-1">
                                    <button @click="openEdit(p)" class="rounded-md p-1.5 hover:bg-muted" title="Editar"><Pencil class="h-4 w-4 text-muted-foreground" /></button>
                                    <button :disabled="p.convocacoes_count > 0 || p.atendimentos_count > 0"
                                            :title="(p.convocacoes_count > 0 || p.atendimentos_count > 0) ? 'Possui registros vinculados' : 'Excluir'"
                                            @click="confirmDelete(p)"
                                            class="rounded-md p-1.5 transition-colors hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-40 dark:hover:bg-red-950/30">
                                        <Trash2 class="h-4 w-4 text-red-500" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <Pagination :links="programas.links" />
        </div>

        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-lg rounded-xl bg-card p-6 shadow-xl">
                <h2 class="mb-4 text-lg font-semibold text-foreground">{{ editing ? 'Editar Programa' : 'Novo Programa' }}</h2>
                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium">Nome *</label>
                        <input v-model="form.nome" type="text" required class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary" />
                        <p v-if="form.errors.nome" class="mt-1 text-xs text-red-500">{{ form.errors.nome }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-sm font-medium">Área *</label>
                            <CreatableSelect
                                v-model:model-value-id="form.area_id"
                                v-model:model-value-name="form.area"
                                :options="areaOptions"
                                placeholder="Selecione ou cadastre"
                                search-placeholder="Pesquisar ou digitar nova área..."
                                create-label-prefix="Cadastrar nova área:"
                                :error="!!form.errors.area"
                            />
                            <p v-if="form.errors.area" class="mt-1 text-xs text-red-500">{{ form.errors.area }}</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium">Status</label>
                            <NativeSelect v-model="form.status">
                                <option value="ativo">Ativo</option>
                                <option value="inativo">Inativo</option>
                            </NativeSelect>
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium">Cor (badge)</label>
                        <input v-model="form.cor" type="color" class="h-10 w-20 rounded-lg border border-input bg-background" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium">Descrição</label>
                        <textarea v-model="form.descricao" rows="2" class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm outline-none focus:border-primary"></textarea>
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="showModal = false" class="rounded-lg border border-border px-4 py-2 text-sm font-medium hover:bg-muted">Cancelar</button>
                        <button type="submit" :disabled="form.processing" class="rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90 disabled:opacity-50">
                            {{ form.processing ? 'Salvando...' : 'Salvar' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
