<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { Plus, Pencil, Trash2, Search, ShieldCheck } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import Pagination from '@/components/Pagination.vue';
import { useSwal } from '@/composables/useSwal';
import type { PaginatedData, BreadcrumbItem } from '@/types';

const { confirmDelete: swalDelete } = useSwal();

type Role = { id: number; name: string };
type User = {
    id: number;
    name: string;
    email: string;
    roles: Role[];
    created_at: string;
};

const props = defineProps<{
    usuarios: PaginatedData<User>;
    filtros: Record<string, string>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Usuários', href: '/usuarios' },
];

const busca = ref(props.filtros.busca || '');

let timeout: ReturnType<typeof setTimeout>;
watch(busca, (value) => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get('/usuarios', { busca: value || undefined }, {
            preserveState: true,
            replace: true,
        });
    }, 300);
});

function confirmDelete(user: User) {
    if (user.id === 1) {
        alert('O administrador principal não pode ser removido.');
        return;
    }
    swalDelete(`O usuário "${user.name}" perderá o acesso ao sistema.`, `/usuarios/${user.id}`);
}

function formatDate(dateString: string) {
    return new Date(dateString).toLocaleDateString('pt-BR');
}
</script>

<template>
    <Head title="Usuários" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-foreground">Controle de Usuários</h1>
                    <p class="text-sm text-muted-foreground">Gerencie o acesso e permissões da equipe</p>
                </div>
                <Link href="/usuarios/create" class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-primary-foreground shadow-sm hover:bg-primary/90 transition-colors">
                    <Plus class="h-4 w-4" />
                    Novo Usuário
                </Link>
            </div>

            <!-- Area de Busca -->
            <div class="flex flex-wrap items-center gap-3">
                <div class="relative flex-1 sm:max-w-xs">
                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <input
                        v-model="busca"
                        type="text"
                        placeholder="Buscar por nome ou email..."
                        class="h-10 w-full rounded-lg border border-input bg-background pl-10 pr-4 text-sm outline-none transition-colors focus:border-primary disabled:cursor-not-allowed disabled:opacity-50"
                    />
                </div>
            </div>

            <!-- Tabela -->
            <div class="overflow-hidden rounded-xl border border-border bg-card shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead>
                            <tr class="border-b border-border bg-muted/50">
                                <th class="px-4 py-3 font-medium text-muted-foreground">Nome (Email)</th>
                                <th class="px-4 py-3 font-medium text-muted-foreground">Acesso Atual (Role)</th>
                                <th class="hidden px-4 py-3 font-medium text-muted-foreground md:table-cell">Data Criação</th>
                                <th class="px-4 py-3 text-right font-medium text-muted-foreground">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr v-for="user in usuarios.data" :key="user.id" class="transition-colors hover:bg-muted/30">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary/10 text-sm font-bold text-primary">
                                            {{ user.name.charAt(0) }}
                                        </div>
                                        <div>
                                            <p class="font-medium text-foreground">{{ user.name }} <span v-if="user.id === 1" class="text-xs ml-1 font-normal text-muted-foreground">(Supremo)</span></p>
                                            <p class="text-xs text-muted-foreground">{{ user.email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="inline-flex items-center gap-1.5 rounded-full border border-primary/20 bg-primary/10 px-2.5 py-0.5 text-xs font-semibold text-primary" v-for="role in user.roles" :key="role.id">
                                        <ShieldCheck class="h-3 w-3" />
                                        {{ role.name.toUpperCase() }}
                                    </div>
                                    <span v-if="!user.roles.length" class="text-muted-foreground italic">Sem Acesso</span>
                                </td>
                                <td class="hidden px-4 py-3 text-muted-foreground md:table-cell">
                                    {{ formatDate(user.created_at) }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-1">
                                        <Link :href="`/usuarios/${user.id}/edit`" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-muted-foreground hover:bg-muted hover:text-foreground transition-colors" title="Editar">
                                            <Pencil class="h-4 w-4" />
                                        </Link>
                                        <button v-if="user.id !== 1" @click="confirmDelete(user)" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-muted-foreground hover:bg-red-50 hover:text-red-600 transition-colors" title="Excluir">
                                            <Trash2 class="h-4 w-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!usuarios.data.length">
                                <td colspan="4" class="px-4 py-8 text-center text-muted-foreground">
                                    <p>Nenhum usuário encontrado.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <Pagination :links="usuarios.links" />
            </div>
        </div>
    </AppLayout>
</template>
