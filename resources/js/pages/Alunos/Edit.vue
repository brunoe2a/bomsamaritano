<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import { ClipboardDocumentListIcon, UserIcon } from '@heroicons/vue/24/outline';
import AppLayout from '@/layouts/AppLayout.vue';
import type { Aluno, Curso, BreadcrumbItem } from '@/types';

const props = defineProps<{
    aluno: Aluno;
    cursos: (Curso & { turmas: { id: number; nome: string }[] })[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Alunos', href: '/alunos' },
    { title: 'Editar', href: `/alunos/${props.aluno.id}/edit` },
];

const currentTurmaIds = props.aluno.matriculas?.map(m => m.turma_id) || [];

const form = useForm({
    nome: props.aluno.nome,
    data_nascimento: props.aluno.data_nascimento?.split('T')[0] || '',
    ano_escolar: props.aluno.ano_escolar || '',
    foto: null as File | null,
    status: props.aluno.status,
    observacoes: props.aluno.observacoes || '',
    turmas_ids: currentTurmaIds,
    responsavel: {
        nome: props.aluno.responsavel?.nome || '',
        endereco_rua: props.aluno.responsavel?.endereco_rua || '',
        endereco_numero: props.aluno.responsavel?.endereco_numero || '',
        endereco_complemento: props.aluno.responsavel?.endereco_complemento || '',
        endereco_bairro: props.aluno.responsavel?.endereco_bairro || '',
        endereco_cidade: props.aluno.responsavel?.endereco_cidade || '',
        endereco_estado: props.aluno.responsavel?.endereco_estado || '',
        endereco_cep: props.aluno.responsavel?.endereco_cep || '',
        telefone: props.aluno.responsavel?.telefone || '',
        whatsapp: props.aluno.responsavel?.whatsapp || '',
        cpf: props.aluno.responsavel?.cpf || '',
        renda_familiar: props.aluno.responsavel?.renda_familiar || '',
        veiculo_proprio: props.aluno.responsavel?.veiculo_proprio || false,
        casa_propria: props.aluno.responsavel?.casa_propria || false,
        cadastro_cras: props.aluno.responsavel?.cadastro_cras || false,
        auxilio_governo: props.aluno.responsavel?.auxilio_governo || false,
        desempregado: props.aluno.responsavel?.desempregado || false,
        autorizacao_sozinho: props.aluno.responsavel?.autorizacao_sozinho || false,
        autorizacao_imagem: props.aluno.responsavel?.autorizacao_imagem || false,
    },
});

function submit() {
    form.post(`/alunos/${props.aluno.id}`, {
        headers: { 'X-HTTP-Method-Override': 'PUT' },
        forceFormData: true,
    });
}

function handleFoto(event: Event) {
    const target = event.target as HTMLInputElement;
    if (target.files?.length) {
        form.foto = target.files[0];
    }
}

const anosEscolares = ['1º Ano', '2º Ano', '3º Ano', '4º Ano', '5º Ano', '6º Ano', '7º Ano', '8º Ano', '9º Ano', '1º EM', '2º EM', '3º EM'];
const estados = ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'];
</script>

<template>
    <Head title="Editar Aluno" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full p-4 md:p-6">
            <div class="mb-6 flex items-center gap-3">
                <Link :href="`/alunos/${aluno.id}`" class="flex h-9 w-9 items-center justify-center rounded-lg border border-border transition-colors hover:bg-muted">
                    <ArrowLeft class="h-4 w-4" />
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-foreground">Editar Aluno</h1>
                    <p class="text-sm text-muted-foreground">{{ aluno.nome }}</p>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Dados do Aluno -->
                <div class="rounded-xl border border-border bg-card p-6 shadow-sm">
                    <h2 class="mb-4 flex items-center gap-2 text-lg font-semibold text-foreground"><ClipboardDocumentListIcon class="size-5" /> Dados do Aluno</h2>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-sm font-medium">Nome Completo *</label>
                            <input v-model="form.nome" type="text" required class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20" />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium">Data de Nascimento *</label>
                            <input v-model="form.data_nascimento" type="date" required class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20" />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium">Ano Escolar</label>
                            <select v-model="form.ano_escolar" class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary">
                                <option value="">Selecione</option>
                                <option v-for="a in anosEscolares" :key="a" :value="a">{{ a }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium">Foto</label>
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 shrink-0 overflow-hidden items-center justify-center rounded-lg border border-primary/20 bg-primary/10 text-xs font-bold text-primary">
                                    <img v-if="aluno.foto" :src="aluno.foto_url" alt="Foto Atual" class="h-full w-full object-cover" />
                                    <span v-else>{{ aluno?.nome?.charAt(0) || '' }}</span>
                                </div>
                                <input type="file" accept="image/*" @change="handleFoto" class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm file:mr-2 file:rounded file:border-0 file:bg-primary/10 file:px-2 file:py-1 file:text-xs file:text-primary" />
                            </div>
                            <p class="mt-1 flex text-xs text-muted-foreground">Envie uma nova para substituir a atual.</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium">Status</label>
                            <select v-model="form.status" class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary">
                                <option value="ativo">Ativo</option>
                                <option value="inativo">Inativo</option>
                                <option value="trancado">Trancado</option>
                                <option value="concluido">Concluído</option>
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-sm font-medium">Observações</label>
                            <textarea v-model="form.observacoes" rows="2" class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Responsável -->
                <div class="rounded-xl border border-border bg-card p-6 shadow-sm">
                    <h2 class="mb-4 flex items-center gap-2 text-lg font-semibold text-foreground"><UserIcon class="size-5" /> Responsável</h2>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-sm font-medium">Nome *</label>
                            <input v-model="form.responsavel.nome" type="text" required class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20" />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium">CPF</label>
                            <input v-model="form.responsavel.cpf" type="text" class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20" />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium">Telefone</label>
                            <input v-model="form.responsavel.telefone" type="tel" class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20" />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium">WhatsApp</label>
                            <input v-model="form.responsavel.whatsapp" type="tel" class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20" />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium">Renda Familiar</label>
                            <select v-model="form.responsavel.renda_familiar" class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary">
                                <option value="">Selecione</option>
                                <option value="menos_1_salario">Menos de 1 salário</option>
                                <option value="ate_2_salarios">Até 2 salários</option>
                                <option value="acima_3_salarios">Acima de 3 salários</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Submit -->
                <div class="flex justify-end gap-3">
                    <Link :href="`/alunos/${aluno.id}`" class="rounded-lg border border-border px-6 py-2.5 text-sm font-medium transition-colors hover:bg-muted">Cancelar</Link>
                    <button type="submit" :disabled="form.processing" class="rounded-lg bg-primary px-6 py-2.5 text-sm font-medium text-primary-foreground shadow-sm transition-colors hover:bg-primary/90 disabled:opacity-50">
                        {{ form.processing ? 'Salvando...' : 'Salvar Alterações' }}
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
