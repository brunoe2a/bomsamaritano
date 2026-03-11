<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import type { Professor, BreadcrumbItem } from '@/types';

const props = defineProps<{ professor: Professor }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Professores', href: '/professores' },
    { title: 'Editar', href: `/professores/${props.professor.id}/edit` },
];

const form = useForm({
    nome: props.professor.nome,
    foto: null as File | null,
    cpf: props.professor.cpf || '',
    data_nascimento: props.professor.data_nascimento?.split('T')[0] || '',
    telefone: props.professor.telefone || '',
    whatsapp: props.professor.whatsapp || '',
    email: props.professor.email || '',
    especialidade: props.professor.especialidade || [],
    tipo_vinculo: props.professor.tipo_vinculo,
    data_inicio: props.professor.data_inicio?.split('T')[0] || '',
    status: props.professor.status,
});

const especialidades = ['Português', 'Matemática', 'Inglês', 'Violão', 'Informática', 'Reforço Escolar'];

function handleFoto(e: Event) {
    const t = e.target as HTMLInputElement;
    if (t.files?.length) form.foto = t.files[0];
}

function submit() {
    form.post(`/professores/${props.professor.id}`, {
        headers: { 'X-HTTP-Method-Override': 'PUT' },
        forceFormData: true,
    });
}
</script>

<template>
    <Head title="Editar Professor" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full p-4 md:p-6">
            <div class="mb-6 flex items-center gap-3">
                <Link :href="`/professores/${professor.id}`" class="flex h-9 w-9 items-center justify-center rounded-lg border border-border hover:bg-muted"><ArrowLeft class="h-4 w-4" /></Link>
                <h1 class="text-2xl font-bold text-foreground">Editar Professor</h1>
            </div>
            <form @submit.prevent="submit" class="space-y-6">
                <div class="rounded-xl border border-border bg-card p-6 shadow-sm">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-sm font-medium">Nome *</label>
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
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 shrink-0 overflow-hidden items-center justify-center rounded-lg border border-primary/20 bg-primary/10 text-xs font-bold text-primary">
                                    <img v-if="professor.foto" :src="`/storage/${professor.foto}`" alt="Foto Atual" class="h-full w-full object-cover" />
                                    <span v-else>{{ professor?.nome?.charAt(0) || '' }}</span>
                                </div>
                                <input type="file" accept="image/*" @change="handleFoto" class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm file:mr-2 file:rounded file:border-0 file:bg-primary/10 file:px-2 file:py-1 file:text-xs file:text-primary" />
                            </div>
                            <p class="mt-1 flex text-xs text-muted-foreground">Envie uma nova para substituir a atual.</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium">Vínculo *</label>
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
                            <label class="mb-2 block text-sm font-medium">Especialidades</label>
                            <div class="flex flex-wrap gap-2">
                                <label v-for="e in especialidades" :key="e" class="flex cursor-pointer items-center gap-1 rounded-lg border border-input px-3 py-2 text-sm" :class="form.especialidade.includes(e) ? 'border-primary bg-primary/10 text-primary' : 'hover:bg-muted'">
                                    <input type="checkbox" :value="e" v-model="form.especialidade" class="sr-only" />
                                    {{ e }}
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end gap-3">
                    <Link :href="`/professores/${professor.id}`" class="rounded-lg border border-border px-6 py-2.5 text-sm font-medium hover:bg-muted">Cancelar</Link>
                    <button type="submit" :disabled="form.processing" class="rounded-lg bg-primary px-6 py-2.5 text-sm font-medium text-primary-foreground shadow-sm hover:bg-primary/90 disabled:opacity-50">
                        {{ form.processing ? 'Salvando...' : 'Salvar' }}
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
