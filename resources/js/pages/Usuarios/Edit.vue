<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Save } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type Role = { id: number; name: string };
type User = {
    id: number;
    name: string;
    email: string;
    roles: Role[];
};

const props = defineProps<{
    usuario: User;
    roles: Role[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Usuários', href: '/usuarios' },
    { title: 'Editar Usuário', href: `/usuarios/${props.usuario.id}/edit` },
];

const selectedRole = props.usuario.roles.length > 0 ? props.usuario.roles[0].name : '';

const form = useForm({
    name: props.usuario.name,
    email: props.usuario.email,
    password: '',
    password_confirmation: '',
    role: selectedRole,
});

function submit() {
    form.put(`/usuarios/${props.usuario.id}`);
}
</script>

<template>
    <Head title="Editar Usuário" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-3xl p-4 md:p-6">
            <!-- Header -->
            <div class="mb-6 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link href="/usuarios" class="flex h-9 w-9 items-center justify-center rounded-lg border border-border hover:bg-muted transition-colors">
                        <ArrowLeft class="h-4 w-4" />
                    </Link>
                    <div>
                        <h1 class="text-2xl font-bold text-foreground">Editar Usuário</h1>
                        <p class="text-sm text-muted-foreground">Atualize os dados ou promova o usuário.</p>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <div class="rounded-xl border border-border bg-card shadow-sm">
                <form @submit.prevent="submit" class="flex flex-col gap-6 p-6">
                    <div class="grid gap-6 sm:grid-cols-2">
                        <!-- Nome -->
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-sm font-medium">Nome Completo *</label>
                            <input
                                v-model="form.name"
                                type="text"
                                class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none transition-colors focus:border-primary"
                                :class="{'border-red-500': form.errors.name}"
                                required
                            />
                            <p v-if="form.errors.name" class="mt-1 text-xs text-red-500">{{ form.errors.name }}</p>
                        </div>

                        <!-- Email -->
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-sm font-medium">Email * (Login de acesso)</label>
                            <input
                                v-model="form.email"
                                type="email"
                                class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none transition-colors focus:border-primary"
                                :class="{'border-red-500': form.errors.email}"
                                required
                            />
                            <p v-if="form.errors.email" class="mt-1 text-xs text-red-500">{{ form.errors.email }}</p>
                        </div>

                        <!-- Nova Senha -->
                        <div>
                            <label class="mb-1 block text-sm font-medium">Nova Senha (Opcional)</label>
                            <input
                                v-model="form.password"
                                type="password"
                                placeholder="Deixe em branco para não alterar"
                                class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none transition-colors focus:border-primary"
                                :class="{'border-red-500': form.errors.password}"
                            />
                            <p v-if="form.errors.password" class="mt-1 text-xs text-red-500">{{ form.errors.password }}</p>
                        </div>

                        <!-- Confirmação de Senha -->
                        <div>
                            <label class="mb-1 block text-sm font-medium">Confirmar Nova Senha</label>
                            <input
                                v-model="form.password_confirmation"
                                type="password"
                                class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none transition-colors focus:border-primary"
                            />
                        </div>

                        <!-- Perfil (Role) -->
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-sm font-medium">Perfil de Acesso *</label>
                            <select
                                v-model="form.role"
                                class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none transition-colors focus:border-primary disabled:opacity-50"
                                :class="{'border-red-500': form.errors.role}"
                                :disabled="usuario.id === 1"
                                required
                            >
                                <option value="" disabled>Selecione um perfil...</option>
                                <option v-for="role in roles" :key="role.id" :value="role.name">
                                    {{ role.name.toUpperCase() }}
                                </option>
                            </select>
                            <p v-if="form.errors.role" class="mt-1 text-xs text-red-500">{{ form.errors.role }}</p>
                            <p v-if="usuario.id === 1" class="mt-1 text-xs text-muted-foreground text-amber-500">O usuário #1 tem o perfil irreversível.</p>
                        </div>
                    </div>

                    <!-- Botões -->
                    <div class="flex items-center justify-end gap-3 border-t border-border pt-4 mt-2">
                        <Link href="/usuarios" class="rounded-lg px-4 py-2.5 text-sm font-medium text-muted-foreground hover:bg-muted transition-colors">
                            Cancelar
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary px-6 py-2.5 text-sm font-medium text-primary-foreground shadow-sm hover:bg-primary/90 transition-colors disabled:opacity-50"
                        >
                            <Save class="h-4 w-4" />
                            Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
