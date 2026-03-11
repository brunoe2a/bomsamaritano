<script setup lang="ts">
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { ArrowLeft, Plus, Trash2, Search } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import Pagination from '@/components/Pagination.vue';
import { useSwal } from '@/composables/useSwal';
import type { PaginatedData, BreadcrumbItem } from '@/types';

const { confirmDelete: swalDelete } = useSwal();

type Doador = { id: number; nome: string; tipo: string; telefone?: string; email?: string; lancamentos_count?: number };

const props = defineProps<{
    doadores: PaginatedData<Doador>;
    filtros: Record<string, string>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Financeiro', href: '/financeiro' },
    { title: 'Doadores', href: '/financeiro/doadores' },
];

const busca = ref(props.filtros.busca || '');
let timeout: ReturnType<typeof setTimeout>;
watch(busca, () => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get('/financeiro/doadores', { busca: busca.value || undefined }, { preserveState: true, replace: true });
    }, 300);
});

const showForm = ref(false);
const form = useForm({
    nome: '',
    tipo: 'pessoa_juridica',
    cpf_cnpj: '',
    telefone: '',
    email: '',
    endereco: '',
});

function submitDoador() {
    form.post('/financeiro/doadores', {
        onSuccess: () => {
            form.reset();
            showForm.value = false;
        },
    });
}

function confirmDelete(d: Doador) {
    swalDelete(`O doador "${d.nome}" será removido.`, `/financeiro/doadores/${d.id}`);
}

const tipoLabels: Record<string, string> = { pessoa_fisica: 'Pessoa Física', pessoa_juridica: 'Pessoa Jurídica' };
</script>

<template>
    <Head title="Doadores" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full p-4 md:p-6">
            <div class="mb-6 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link href="/financeiro" class="flex h-9 w-9 items-center justify-center rounded-lg border border-border hover:bg-muted"><ArrowLeft class="h-4 w-4" /></Link>
                    <div>
                        <h1 class="text-2xl font-bold text-foreground">Doadores</h1>
                        <p class="text-sm text-muted-foreground">{{ doadores.total }} doador(es) cadastrado(s)</p>
                    </div>
                </div>
                <button @click="showForm = !showForm" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-primary-foreground shadow-sm hover:bg-primary/90">
                    <Plus class="h-4 w-4" /> Novo Doador
                </button>
            </div>

            <!-- Add Form -->
            <div v-if="showForm" class="mb-6 rounded-xl border border-primary/30 bg-card p-6 shadow-sm">
                <h3 class="mb-4 text-lg font-semibold text-foreground">Cadastrar Doador</h3>
                <form @submit.prevent="submitDoador" class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-sm font-medium">Nome *</label>
                        <input v-model="form.nome" type="text" required class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium">Tipo *</label>
                        <select v-model="form.tipo" class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary">
                            <option value="pessoa_fisica">Pessoa Física</option>
                            <option value="pessoa_juridica">Pessoa Jurídica</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium">CPF/CNPJ</label>
                        <input v-model="form.cpf_cnpj" type="text" class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium">Telefone</label>
                        <input v-model="form.telefone" type="tel" class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium">E-mail</label>
                        <input v-model="form.email" type="email" class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary" />
                    </div>
                    <div class="sm:col-span-2 flex justify-end gap-2">
                        <button type="button" @click="showForm = false" class="rounded-lg border border-border px-4 py-2 text-sm hover:bg-muted">Cancelar</button>
                        <button type="submit" :disabled="form.processing" class="rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90 disabled:opacity-50">
                            {{ form.processing ? 'Salvando...' : 'Cadastrar' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Search -->
            <div class="mb-4">
                <div class="relative">
                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <input v-model="busca" type="text" placeholder="Buscar doador..." class="h-10 w-full rounded-lg border border-input bg-background pl-10 pr-4 text-sm outline-none focus:border-primary" />
                </div>
            </div>

            <!-- List -->
            <div class="space-y-3">
                <div v-for="d in doadores.data" :key="d.id" class="flex items-center justify-between rounded-xl border border-border bg-card p-4 shadow-sm">
                    <div>
                        <p class="font-medium text-foreground">{{ d.nome }}</p>
                        <div class="mt-1 flex flex-wrap gap-2 text-xs text-muted-foreground">
                            <span class="rounded bg-muted px-1.5 py-0.5">{{ tipoLabels[d.tipo] || d.tipo }}</span>
                            <span v-if="d.email">{{ d.email }}</span>
                            <span v-if="d.telefone">{{ d.telefone }}</span>
                            <span class="font-medium text-primary">{{ d.lancamentos_count || 0 }} doação(ões)</span>
                        </div>
                    </div>
                    <button @click="confirmDelete(d)" class="rounded-lg p-2 text-muted-foreground hover:bg-red-50 hover:text-red-600">
                        <Trash2 class="h-4 w-4" />
                    </button>
                </div>
                <div v-if="!doadores.data.length" class="py-12 text-center text-muted-foreground">Nenhum doador encontrado.</div>
            </div>
            <Pagination :links="doadores.links" />
        </div>
    </AppLayout>
</template>
