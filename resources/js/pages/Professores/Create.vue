<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import MultiSelect from '@/components/MultiSelect.vue';
import type { BreadcrumbItem, Unidade, Especialidade } from '@/types';

const props = defineProps<{
    unidades: Unidade[];
    especialidades: Especialidade[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Professores', href: '/professores' },
    { title: 'Novo', href: '/professores/create' },
];

const form = useForm({
    nome: '',
    foto: null as File | null,
    cpf: '',
    rg: '',
    data_nascimento: '',
    telefone: '',
    whatsapp: '',
    email: '',
    especialidade: [] as (string | number)[],
    tipo_vinculo: 'voluntario',
    data_inicio: '',
    status: 'ativo',
    unidades: [] as number[],
});

function handleFoto(e: Event) {
    const t = e.target as HTMLInputElement;
    if (t.files?.length) form.foto = t.files[0];
}

function submit() {
    form.post('/professores');
}
</script>

<template>
    <Head title="Novo Professor" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full p-4 md:p-6">
            <div class="mb-6 flex items-center gap-3">
                <Link href="/professores" class="flex h-9 w-9 items-center justify-center rounded-lg border border-border hover:bg-muted"><ArrowLeft class="h-4 w-4" /></Link>
                <h1 class="text-2xl font-bold text-foreground">Novo Professor</h1>
            </div>
            <form @submit.prevent="submit" class="space-y-6">
                <div class="rounded-xl border border-border bg-card p-6 shadow-sm">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-sm font-medium">Nome Completo *</label>
                            <input v-model="form.nome" type="text" required class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20" />
                            <p v-if="form.errors.nome" class="mt-1 text-xs text-red-500">{{ form.errors.nome }}</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium">CPF</label>
                            <input v-model="form.cpf" type="text" placeholder="000.000.000-00" class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20" />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium">Data de Nascimento</label>
                            <input v-model="form.data_nascimento" type="date" class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary" />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium">Telefone</label>
                            <input v-model="form.telefone" type="tel" class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20" />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium">WhatsApp</label>
                            <input v-model="form.whatsapp" type="tel" class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20" />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium">E-mail</label>
                            <input v-model="form.email" type="email" class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20" />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium">Foto</label>
                            <input type="file" accept="image/*" @change="handleFoto" class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm file:mr-2 file:rounded file:border-0 file:bg-primary/10 file:px-2 file:py-1 file:text-xs file:text-primary" />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium">Tipo de Vínculo *</label>
                            <select v-model="form.tipo_vinculo" class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary">
                                <option value="voluntario">Voluntário</option>
                                <option value="contratado">Contratado</option>
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
                            <MultiSelect 
                                v-model="form.especialidade" 
                                :options="especialidades" 
                                label="Especialidades" 
                                placeholder="Selecione ou digite novas especialidades"
                                :allow-add="true"
                            />
                        </div>
                        <div class="sm:col-span-2">
                            <MultiSelect 
                                v-model="form.unidades" 
                                :options="unidades" 
                                label="Unidades *" 
                                placeholder="Selecione uma ou mais unidades"
                            />
                            <p v-if="form.errors.unidades" class="mt-1 text-xs text-red-500">{{ form.errors.unidades }}</p>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end gap-3">
                    <Link href="/professores" class="rounded-lg border border-border px-6 py-2.5 text-sm font-medium hover:bg-muted">Cancelar</Link>
                    <button type="submit" :disabled="form.processing" class="rounded-lg bg-primary px-6 py-2.5 text-sm font-medium text-primary-foreground shadow-sm hover:bg-primary/90 disabled:opacity-50">
                        {{ form.processing ? 'Salvando...' : 'Cadastrar' }}
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
