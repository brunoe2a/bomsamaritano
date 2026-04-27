<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ArrowLeft, Search } from 'lucide-vue-next';
import { ClipboardDocumentListIcon, UserIcon } from '@heroicons/vue/24/outline';
import AppLayout from '@/layouts/AppLayout.vue';
import SearchableSelect from '@/components/SearchableSelect.vue';
import NativeSelect from '@/components/NativeSelect.vue';
import DatePicker from '@/components/DatePicker.vue';
import type { Aluno, Curso, BreadcrumbItem } from '@/types';
import { computed, ref } from 'vue';

interface ResponsavelOption {
    id: number;
    nome: string;
    cpf: string | null;
    telefone: string | null;
    whatsapp: string | null;
    alunos_count: number;
}

const props = defineProps<{
    aluno: Aluno;
    cursos: (Curso & { turmas: { id: number; nome: string }[] })[];
    responsaveis: ResponsavelOption[];
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
    responsavel_id: props.aluno.responsavel_id,
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

const anoEscolarOptions = anosEscolares.map(a => ({ value: a, label: a }));
const estadoOptions = estados.map(uf => ({ value: uf, label: uf }));
const statusOptions = [
    { value: 'ativo', label: 'Ativo' },
    { value: 'inativo', label: 'Inativo' },
    { value: 'trancado', label: 'Trancado' },
    { value: 'concluido', label: 'Concluído' },
];
const rendaOptions = [
    { value: 'menos_1_salario', label: 'Menos de 1 salário' },
    { value: 'ate_2_salarios', label: 'Até 2 salários' },
    { value: 'acima_3_salarios', label: 'Acima de 3 salários' },
];

const trocandoResp = ref(false);
const buscaResp = ref('');
const responsaveisFiltrados = computed(() => {
    const termo = buscaResp.value.trim().toLowerCase();
    const lista = props.responsaveis.filter((r) => r.id !== props.aluno.responsavel_id);
    if (!termo) return lista;
    return lista.filter(
        (r) =>
            r.nome.toLowerCase().includes(termo) ||
            (r.cpf ?? '').toLowerCase().includes(termo) ||
            (r.telefone ?? '').includes(termo) ||
            (r.whatsapp ?? '').includes(termo),
    );
});

function cancelarTroca() {
    trocandoResp.value = false;
    form.responsavel_id = props.aluno.responsavel_id;
}
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
                            <DatePicker v-model="form.data_nascimento" />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium">Ano Escolar</label>
                            <NativeSelect v-model="form.ano_escolar">
                                <option value="">Selecione</option>
                                <option v-for="a in anosEscolares" :key="a" :value="a">{{ a }}</option>
                            </NativeSelect>
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
                            <NativeSelect v-model="form.status">
                                <option value="ativo">Ativo</option>
                                <option value="inativo">Inativo</option>
                                <option value="trancado">Trancado</option>
                                <option value="concluido">Concluído</option>
                            </NativeSelect>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-sm font-medium">Observações</label>
                            <textarea v-model="form.observacoes" rows="2" class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Responsável -->
                <div class="rounded-xl border border-border bg-card p-6 shadow-sm">
                    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                        <h2 class="flex items-center gap-2 text-lg font-semibold text-foreground"><UserIcon class="size-5" /> Responsável</h2>
                        <button
                            v-if="!trocandoResp"
                            type="button"
                            class="rounded-lg border border-input px-3 py-1.5 text-xs font-medium transition-colors hover:bg-muted"
                            @click="trocandoResp = true"
                        >
                            Trocar para outro responsável
                        </button>
                        <button
                            v-else
                            type="button"
                            class="rounded-lg border border-input px-3 py-1.5 text-xs font-medium transition-colors hover:bg-muted"
                            @click="cancelarTroca"
                        >
                            Cancelar troca
                        </button>
                    </div>

                    <div v-if="trocandoResp" class="mb-6 space-y-3 rounded-lg border border-amber-300 bg-amber-50 p-4 dark:bg-amber-950/30">
                        <p class="text-sm text-foreground">Selecione o novo responsável (ao salvar, o aluno passará a pertencer ao escolhido):</p>
                        <div class="relative">
                            <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                            <input
                                v-model="buscaResp"
                                type="text"
                                placeholder="Buscar por nome, CPF ou telefone..."
                                class="h-10 w-full rounded-lg border border-input bg-background pl-9 pr-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20"
                            />
                        </div>
                        <div class="max-h-64 overflow-y-auto rounded-lg border border-border bg-background">
                            <p v-if="!responsaveisFiltrados.length" class="p-4 text-center text-sm text-muted-foreground">Nenhum responsável encontrado.</p>
                            <label
                                v-for="r in responsaveisFiltrados"
                                :key="r.id"
                                class="flex cursor-pointer items-center justify-between gap-3 border-b border-border px-3 py-2 text-sm transition-colors last:border-0"
                                :class="form.responsavel_id === r.id ? 'bg-primary/10' : 'hover:bg-muted'"
                            >
                                <div class="flex items-center gap-3">
                                    <input type="radio" :value="r.id" v-model="form.responsavel_id" class="text-primary focus:ring-primary" />
                                    <div>
                                        <p class="font-medium text-foreground">{{ r.nome }}</p>
                                        <p class="text-xs text-muted-foreground">
                                            <span v-if="r.cpf">CPF: {{ r.cpf }}</span>
                                            <span v-if="r.telefone"> · Tel: {{ r.telefone }}</span>
                                        </p>
                                    </div>
                                </div>
                                <span class="rounded-full bg-muted px-2 py-0.5 text-xs">
                                    {{ r.alunos_count }} {{ r.alunos_count === 1 ? 'filho' : 'filhos' }}
                                </span>
                            </label>
                        </div>
                    </div>

                    <p v-if="!trocandoResp" class="mb-3 text-xs text-muted-foreground">Editando os dados do responsável atual. Para vincular o aluno a outro responsável já cadastrado, clique em "Trocar para outro responsável".</p>

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
                            <NativeSelect v-model="form.responsavel.renda_familiar">
                                <option value="">Selecione</option>
                                <option value="menos_1_salario">Menos de 1 salário</option>
                                <option value="ate_2_salarios">Até 2 salários</option>
                                <option value="acima_3_salarios">Acima de 3 salários</option>
                            </NativeSelect>
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
