<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
const props = defineProps<{ areasAtuacao: { id: number; nome: string }[] }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Voluntários', href: '/voluntarios' },
    { title: 'Novo', href: '/voluntarios/create' },
];

const form = useForm({
    nome: '',
    foto: null as File | null,
    cpf: '',
    data_nascimento: '',
    telefone: '',
    whatsapp: '',
    email: '',
    area_atuacao: '',
    habilidades: '',
    data_inicio: '',
    status: 'ativo',
});

function handleFoto(e: Event) {
    const t = e.target as HTMLInputElement;
    if (t.files?.length) form.foto = t.files[0];
}

function submit() { form.post('/voluntarios'); }
</script>

<template>
    <Head title="Novo Voluntário" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full p-4 md:p-6">
            <div class="mb-6 flex items-center gap-3">
                <Link href="/voluntarios" class="flex h-9 w-9 items-center justify-center rounded-lg border border-border hover:bg-muted"><ArrowLeft class="h-4 w-4" /></Link>
                <h1 class="text-2xl font-bold text-foreground">Novo Voluntário</h1>
            </div>
            <form @submit.prevent="submit" class="space-y-6">
                <div class="rounded-xl border border-border bg-card p-6 shadow-sm">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-sm font-medium">Nome Completo *</label>
                            <input v-model="form.nome" type="text" required class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20" />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium">CPF</label>
                            <input v-model="form.cpf" type="text" class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary" />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium">Data de Nascimento</label>
                            <input v-model="form.data_nascimento" type="date" class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary" />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium">Telefone</label>
                            <input v-model="form.telefone" type="tel" class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary" />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium">WhatsApp</label>
                            <input v-model="form.whatsapp" type="tel" class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary" />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium">E-mail</label>
                            <input v-model="form.email" type="email" class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary" />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium">Foto</label>
                            <input type="file" accept="image/*" @change="handleFoto" class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm file:mr-2 file:rounded file:border-0 file:bg-primary/10 file:px-2 file:py-1 file:text-xs file:text-primary" />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium">Área de Atuação</label>
                            <select v-model="form.area_atuacao" class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary">
                                <option value="">Selecione</option>
                                <option v-for="a in areasAtuacao" :key="a.id" :value="a.nome">{{ a.nome }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium">Data de Início</label>
                            <input v-model="form.data_inicio" type="date" class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary" />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium">Status *</label>
                            <select v-model="form.status" class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary">
                                <option value="ativo">Ativo</option>
                                <option value="inativo">Inativo</option>
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-sm font-medium">Habilidades / Observações</label>
                            <textarea v-model="form.habilidades" rows="2" class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20"></textarea>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end gap-3">
                    <Link href="/voluntarios" class="rounded-lg border border-border px-6 py-2.5 text-sm font-medium hover:bg-muted">Cancelar</Link>
                    <button type="submit" :disabled="form.processing" class="rounded-lg bg-primary px-6 py-2.5 text-sm font-medium text-primary-foreground shadow-sm hover:bg-primary/90 disabled:opacity-50">
                        {{ form.processing ? 'Salvando...' : 'Cadastrar' }}
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
