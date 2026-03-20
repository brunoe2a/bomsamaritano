<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { Plus, Pencil, Trash2, Search, MapPin, Phone, Mail, User } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import Pagination from '@/components/Pagination.vue';
import { useSwal } from '@/composables/useSwal';
import type { Unidade, PaginatedData, BreadcrumbItem } from '@/types';

const { confirmDelete: swalDelete } = useSwal();

const props = defineProps<{
    unidades: PaginatedData<Unidade>;
    filtros: Record<string, string>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Unidades', href: '/unidades' },
];

const busca = ref(props.filtros.busca || '');

let timeout: ReturnType<typeof setTimeout>;
function applyFilters() {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get('/unidades', {
            busca: busca.value || undefined,
        }, { preserveState: true, replace: true });
    }, 300);
}
watch(busca, applyFilters);

function confirmDelete(u: Unidade) {
    if (u.id === 1) return;
    swalDelete(`A unidade "${u.nome}" será removida.`, `/unidades/${u.id}`);
}
</script>

<template>
    <Head title="Unidades" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-foreground">Unidades</h1>
                    <p class="text-sm text-muted-foreground">{{ unidades.total }} unidade(s)</p>
                </div>
                <Link href="/unidades/create" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-primary-foreground shadow-sm hover:bg-primary/90">
                    <Plus class="h-4 w-4" /> Nova Unidade
                </Link>
            </div>

            <div class="flex flex-col gap-3 rounded-xl border border-border bg-card p-4 sm:flex-row">
                <div class="relative flex-1">
                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <input v-model="busca" type="text" placeholder="Buscar por nome..." class="h-10 w-full rounded-lg border border-input bg-background pl-10 pr-4 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20" />
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div v-for="u in unidades.data" :key="u.id" class="group rounded-xl border border-border bg-card p-5 shadow-sm transition-all hover:shadow-md">
                    <div class="mb-4">
                        <h3 class="text-lg font-bold text-foreground">{{ u.nome }}</h3>
                        <span v-if="u.id === 1" class="rounded bg-primary/10 px-2 py-0.5 text-xs font-medium text-primary">Sede Principal</span>
                    </div>

                    <div class="space-y-2 text-sm text-muted-foreground">
                        <div class="flex items-start gap-2">
                            <MapPin class="mt-0.5 h-4 w-4 shrink-0" />
                            <span>{{ u.endereco || 'Endereço não informado' }}</span>
                        </div>
                        <div v-if="u.telefone" class="flex items-center gap-2">
                            <Phone class="h-4 w-4 shrink-0" />
                            <span>{{ u.telefone }}</span>
                        </div>
                        <div v-if="u.email" class="flex items-center gap-2">
                            <Mail class="h-4 w-4 shrink-0" />
                            <span class="truncate">{{ u.email }}</span>
                        </div>
                        <div v-if="u.contato_responsavel" class="flex items-center gap-2">
                            <User class="h-4 w-4 shrink-0" />
                            <span>{{ u.contato_responsavel }}</span>
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end gap-1 opacity-0 transition-opacity group-hover:opacity-100">
                        <Link :href="`/unidades/${u.id}/edit`" class="rounded-lg p-2 text-muted-foreground hover:bg-muted hover:text-foreground"><Pencil class="h-4 w-4" /></Link>
                        <button v-if="u.id !== 1" @click="confirmDelete(u)" class="rounded-lg p-2 text-muted-foreground hover:bg-red-50 hover:text-red-600"><Trash2 class="h-4 w-4" /></button>
                    </div>
                </div>
                <div v-if="!unidades.data.length" class="col-span-full py-12 text-center text-muted-foreground">Nenhuma unidade encontrada.</div>
            </div>
            <Pagination :links="unidades.links" />
        </div>
    </AppLayout>
</template>
