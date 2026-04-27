<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import { UserIcon } from '@heroicons/vue/24/outline';
import AppLayout from '@/layouts/AppLayout.vue';
import NativeSelect from '@/components/NativeSelect.vue';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Responsáveis', href: '/responsaveis' },
    { title: 'Novo', href: '/responsaveis/create' },
];

const form = useForm({
    nome: '',
    endereco_rua: '',
    endereco_numero: '',
    endereco_complemento: '',
    endereco_bairro: '',
    endereco_cidade: '',
    endereco_estado: '',
    endereco_cep: '',
    telefone: '',
    whatsapp: '',
    cpf: '',
    renda_familiar: '',
    veiculo_proprio: false,
    casa_propria: false,
    cadastro_cras: false,
    auxilio_governo: false,
    desempregado: false,
    autorizacao_sozinho: false,
    autorizacao_imagem: false,
});

function submit() {
    form.post('/responsaveis');
}

const estados = ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'];
const estadoOptions = estados.map(uf => ({ value: uf, label: uf }));
const rendaOptions = [
    { value: 'menos_1_salario', label: 'Menos de 1 salário' },
    { value: 'ate_2_salarios', label: 'Até 2 salários' },
    { value: 'acima_3_salarios', label: 'Acima de 3 salários' },
];
</script>

<template>
    <Head title="Novo Responsável" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full p-4 md:p-6">
            <div class="mb-6 flex items-center gap-3">
                <Link href="/responsaveis" class="flex h-9 w-9 items-center justify-center rounded-lg border border-border transition-colors hover:bg-muted">
                    <ArrowLeft class="h-4 w-4" />
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-foreground">Novo Responsável</h1>
                    <p class="text-sm text-muted-foreground">Cadastre um responsável que poderá ter um ou mais filhos vinculados.</p>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <div class="rounded-xl border border-border bg-card p-6 shadow-sm">
                    <h2 class="mb-4 flex items-center gap-2 text-lg font-semibold text-foreground"><UserIcon class="size-5" /> Dados</h2>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-sm font-medium">Nome Completo *</label>
                            <input v-model="form.nome" type="text" required class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20" />
                            <p v-if="form.errors.nome" class="mt-1 text-xs text-red-500">{{ form.errors.nome }}</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium">CPF</label>
                            <input v-model="form.cpf" type="text" placeholder="000.000.000-00" class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20" />
                            <p v-if="form.errors.cpf" class="mt-1 text-xs text-red-500">{{ form.errors.cpf }}</p>
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
                            <label class="mb-1 block text-sm font-medium">Renda Familiar</label>
                            <NativeSelect v-model="form.renda_familiar">
                                <option value="">Selecione</option>
                                <option value="menos_1_salario">Menos de 1 salário</option>
                                <option value="ate_2_salarios">Até 2 salários</option>
                                <option value="acima_3_salarios">Acima de 3 salários</option>
                            </NativeSelect>
                        </div>

                        <div class="sm:col-span-2">
                            <p class="mb-2 text-sm font-medium text-muted-foreground">Endereço</p>
                        </div>
                        <div class="sm:col-span-2">
                            <input v-model="form.endereco_rua" type="text" placeholder="Rua" class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20" />
                        </div>
                        <div>
                            <input v-model="form.endereco_numero" type="text" placeholder="Número" class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20" />
                        </div>
                        <div>
                            <input v-model="form.endereco_complemento" type="text" placeholder="Complemento" class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20" />
                        </div>
                        <div>
                            <input v-model="form.endereco_bairro" type="text" placeholder="Bairro" class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20" />
                        </div>
                        <div>
                            <input v-model="form.endereco_cidade" type="text" placeholder="Cidade" class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20" />
                        </div>
                        <div>
                            <NativeSelect v-model="form.endereco_estado">
                                <option value="">UF</option>
                                <option v-for="uf in estados" :key="uf" :value="uf">{{ uf }}</option>
                            </NativeSelect>
                        </div>
                        <div>
                            <input v-model="form.endereco_cep" type="text" placeholder="CEP" class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20" />
                        </div>

                        <div class="sm:col-span-2">
                            <p class="mb-2 text-sm font-medium text-muted-foreground">Situação Socioeconômica</p>
                            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                                <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-input px-3 py-2 text-sm" :class="form.veiculo_proprio ? 'border-primary bg-primary/10' : 'hover:bg-muted'">
                                    <input type="checkbox" v-model="form.veiculo_proprio" class="rounded border-input text-primary focus:ring-primary" /> Veículo próprio
                                </label>
                                <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-input px-3 py-2 text-sm" :class="form.casa_propria ? 'border-primary bg-primary/10' : 'hover:bg-muted'">
                                    <input type="checkbox" v-model="form.casa_propria" class="rounded border-input text-primary focus:ring-primary" /> Casa própria
                                </label>
                                <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-input px-3 py-2 text-sm" :class="form.cadastro_cras ? 'border-primary bg-primary/10' : 'hover:bg-muted'">
                                    <input type="checkbox" v-model="form.cadastro_cras" class="rounded border-input text-primary focus:ring-primary" /> Cadastro CRAs
                                </label>
                                <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-input px-3 py-2 text-sm" :class="form.auxilio_governo ? 'border-primary bg-primary/10' : 'hover:bg-muted'">
                                    <input type="checkbox" v-model="form.auxilio_governo" class="rounded border-input text-primary focus:ring-primary" /> Auxílio governo
                                </label>
                                <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-input px-3 py-2 text-sm" :class="form.desempregado ? 'border-primary bg-primary/10' : 'hover:bg-muted'">
                                    <input type="checkbox" v-model="form.desempregado" class="rounded border-input text-primary focus:ring-primary" /> Desempregado
                                </label>
                            </div>
                        </div>

                        <div class="sm:col-span-2">
                            <p class="mb-2 text-sm font-medium text-muted-foreground">Autorizações</p>
                            <div class="flex gap-4">
                                <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-input px-3 py-2 text-sm" :class="form.autorizacao_sozinho ? 'border-primary bg-primary/10' : 'hover:bg-muted'">
                                    <input type="checkbox" v-model="form.autorizacao_sozinho" class="rounded border-input text-primary focus:ring-primary" /> Ir embora sozinho
                                </label>
                                <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-input px-3 py-2 text-sm" :class="form.autorizacao_imagem ? 'border-primary bg-primary/10' : 'hover:bg-muted'">
                                    <input type="checkbox" v-model="form.autorizacao_imagem" class="rounded border-input text-primary focus:ring-primary" /> Uso de imagem
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <Link href="/responsaveis" class="rounded-lg border border-border px-6 py-2.5 text-sm font-medium transition-colors hover:bg-muted">Cancelar</Link>
                    <button type="submit" :disabled="form.processing" class="rounded-lg bg-primary px-6 py-2.5 text-sm font-medium text-primary-foreground shadow-sm transition-colors hover:bg-primary/90 disabled:opacity-50">
                        {{ form.processing ? 'Salvando...' : 'Cadastrar Responsável' }}
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
