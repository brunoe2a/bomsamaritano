<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import type { Unidade, BreadcrumbItem } from '@/types';

const props = defineProps<{
    unidade: Unidade;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Unidades', href: '/unidades' },
    { title: 'Editar', href: `/unidades/${props.unidade.id}/edit` },
];

const form = useForm({
    nome: props.unidade.nome,
    endereco: props.unidade.endereco || '',
    telefone: props.unidade.telefone || '',
    email: props.unidade.email || '',
    contato_responsavel: props.unidade.contato_responsavel || '',
});

function submit() {
    form.patch(`/unidades/${props.unidade.id}`);
}
</script>

<template>
    <Head title="Editar Unidade" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full p-4 md:p-6">
            <div class="mb-6 flex items-center gap-3">
                <Link href="/unidades" class="flex h-9 w-9 items-center justify-center rounded-lg border border-border hover:bg-muted"><ArrowLeft class="h-4 w-4" /></Link>
                <h1 class="text-2xl font-bold text-foreground">Editar Unidade</h1>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <div class="rounded-xl border border-border bg-card p-6 shadow-sm">
                    <div class="grid gap-4">
                        <div>
                            <label class="mb-1 block text-sm font-medium">Nome da Unidade *</label>
                            <input v-model="form.nome" type="text" required class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20" />
                            <p v-if="form.errors.nome" class="mt-1 text-xs text-red-500">{{ form.errors.nome }}</p>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium">Endereço / Localização</label>
                            <input v-model="form.endereco" type="text" class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20" />
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-sm font-medium">Telefone</label>
                                <input v-model="form.telefone" type="tel" class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20" />
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium">E-mail</label>
                                <input v-model="form.email" type="email" class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20" />
                            </div>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium">Responsável da Unidade</label>
                            <input v-model="form.contato_responsavel" type="text" class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20" />
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <Link href="/unidades" class="rounded-lg border border-border px-6 py-2.5 text-sm font-medium hover:bg-muted">Cancelar</Link>
                    <button type="submit" :disabled="form.processing" class="rounded-lg bg-primary px-6 py-2.5 text-sm font-medium text-primary-foreground shadow-sm hover:bg-primary/90 disabled:opacity-50">
                        {{ form.processing ? 'Salvando...' : 'Atualizar' }}
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
