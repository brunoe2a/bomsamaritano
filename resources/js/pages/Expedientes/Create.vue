<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { ArrowLeft, Save, UserCheck, HandHeart } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import SearchableSelect from '@/components/SearchableSelect.vue';
import DatePicker from '@/components/DatePicker.vue';
import MultiSelect from '@/components/MultiSelect.vue';
import InputError from '@/components/InputError.vue';
import type { BreadcrumbItem, Unidade } from '@/types';

interface Pessoa { id: number; nome: string }

const props = defineProps<{
    unidades: Unidade[];
    professores: Pessoa[];
    voluntarios: Pessoa[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Expediente', href: '/expedientes' },
    { title: 'Novo', href: '/expedientes-novo' },
];

const unidadeOptions = computed(() => props.unidades.map(u => ({ value: u.id, label: u.nome })));

const form = useForm({
    unidade_id: '' as string | number,
    data_expediente: new Date().toISOString().slice(0, 10),
    descricao: '',
    observacoes: '',
    professores_ids: [] as (number | string)[],
    voluntarios_ids: [] as (number | string)[],
});

function submit() {
    form
        .transform((data) => ({
            unidade_id: data.unidade_id,
            data: data.data_expediente,
            descricao: data.descricao,
            observacoes: data.observacoes,
            professores_ids: data.professores_ids,
            voluntarios_ids: data.voluntarios_ids,
        }))
        .post('/expedientes');
}
</script>

<template>
    <Head title="Novo Expediente" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <form @submit.prevent="submit" class="flex flex-col gap-6 p-4 md:p-6">
            <div class="flex items-center gap-3">
                <Link href="/expedientes" class="rounded-lg p-2 hover:bg-muted">
                    <ArrowLeft class="h-5 w-5" />
                </Link>
                <h1 class="text-2xl font-bold text-foreground">Novo Expediente</h1>
            </div>

            <div class="grid grid-cols-1 gap-4 rounded-xl border border-border bg-card p-5 md:grid-cols-3">
                <div>
                    <label class="mb-1 block text-sm font-medium">Unidade *</label>
                    <SearchableSelect
                        v-model="form.unidade_id"
                        :options="unidadeOptions"
                        placeholder="Selecione a unidade"
                        :error="!!form.errors.unidade_id"
                    />
                    <InputError :message="form.errors.unidade_id" class="mt-1" />
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">Data *</label>
                    <DatePicker v-model="form.data_expediente" :error="!!form.errors.data" />
                    <InputError :message="form.errors.data" class="mt-1" />
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">Descrição</label>
                    <input v-model="form.descricao" type="text" placeholder="Ex: Sábado letivo" class="h-10 w-full rounded-lg border border-border bg-background px-3 text-sm outline-none transition-colors focus:border-primary focus:ring-1 focus:ring-primary" />
                </div>
                <div class="md:col-span-3">
                    <label class="mb-1 block text-sm font-medium">Observações</label>
                    <textarea v-model="form.observacoes" rows="2" class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none transition-colors focus:border-primary focus:ring-1 focus:ring-primary"></textarea>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                <div class="rounded-xl border border-border bg-card p-5">
                    <div class="mb-3 flex items-center gap-2 font-semibold">
                        <UserCheck class="h-5 w-5 text-primary" /> Professores ({{ form.professores_ids.length }})
                    </div>
                    <MultiSelect
                        v-model="form.professores_ids"
                        :options="professores"
                        placeholder="Selecione os professores escalados..."
                    />
                    <InputError :message="form.errors.professores_ids" class="mt-1" />
                </div>

                <div class="rounded-xl border border-border bg-card p-5">
                    <div class="mb-3 flex items-center gap-2 font-semibold">
                        <HandHeart class="h-5 w-5 text-primary" /> Voluntários ({{ form.voluntarios_ids.length }})
                    </div>
                    <MultiSelect
                        v-model="form.voluntarios_ids"
                        :options="voluntarios"
                        placeholder="Selecione os voluntários escalados..."
                    />
                    <InputError :message="form.errors.voluntarios_ids" class="mt-1" />
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <Link href="/expedientes" class="rounded-lg border border-border px-5 py-2.5 text-sm hover:bg-muted">Cancelar</Link>
                <button type="submit" :disabled="form.processing" class="inline-flex items-center gap-2 rounded-lg bg-primary px-5 py-2.5 text-sm font-medium text-primary-foreground hover:bg-primary/90 disabled:opacity-60">
                    <Save class="h-4 w-4" /> Criar Expediente
                </button>
            </div>
        </form>
    </AppLayout>
</template>
