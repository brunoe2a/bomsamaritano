<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import { ClipboardDocumentListIcon, AcademicCapIcon, UserIcon } from '@heroicons/vue/24/outline';
import AppLayout from '@/layouts/AppLayout.vue';
import type { Curso, BreadcrumbItem } from '@/types';

const props = defineProps<{
    cursos: (Curso & { turmas: { id: number; nome: string }[] })[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Alunos', href: '/alunos' },
    { title: 'Novo Aluno', href: '/alunos/create' },
];

const form = useForm({
    nome: '',
    data_nascimento: '',
    ano_escolar: '',
    foto: null as File | null,
    status: 'ativo',
    observacoes: '',
    turmas_ids: [] as number[],
    responsavel: {
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
        renda_familiar: '' as string,
        veiculo_proprio: false,
        casa_propria: false,
        cadastro_cras: false,
        auxilio_governo: false,
        desempregado: false,
        autorizacao_sozinho: false,
        autorizacao_imagem: false,
    },
});

function submit() {
    form.post('/alunos');
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
    <Head title="Novo Aluno" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full p-4 md:p-6">
            <div class="mb-6 flex items-center gap-3">
                <Link
                    href="/alunos"
                    class="flex h-9 w-9 items-center justify-center rounded-lg border border-border transition-colors hover:bg-muted"
                >
                    <ArrowLeft class="h-4 w-4" />
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-foreground">Novo Aluno</h1>
                    <p class="text-sm text-muted-foreground">Preencha os dados do aluno e do responsável</p>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Dados do Aluno -->
                <div class="rounded-xl border border-border bg-card p-6 shadow-sm">
                    <h2 class="mb-4 flex items-center gap-2 text-lg font-semibold text-foreground"><ClipboardDocumentListIcon class="size-5" /> Dados do Aluno</h2>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-sm font-medium text-foreground">Nome Completo *</label>
                            <input v-model="form.nome" type="text" required class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20" />
                            <p v-if="form.errors.nome" class="mt-1 text-xs text-red-500">{{ form.errors.nome }}</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-foreground">Data de Nascimento *</label>
                            <input v-model="form.data_nascimento" type="date" required class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20" />
                            <p v-if="form.errors.data_nascimento" class="mt-1 text-xs text-red-500">{{ form.errors.data_nascimento }}</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-foreground">Ano Escolar</label>
                            <select v-model="form.ano_escolar" class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary">
                                <option value="">Selecione</option>
                                <option v-for="a in anosEscolares" :key="a" :value="a">{{ a }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-foreground">Foto</label>
                            <input type="file" accept="image/*" @change="handleFoto" class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm file:mr-2 file:rounded file:border-0 file:bg-primary/10 file:px-2 file:py-1 file:text-xs file:text-primary" />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-foreground">Status</label>
                            <select v-model="form.status" class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary">
                                <option value="ativo">Ativo</option>
                                <option value="inativo">Inativo</option>
                                <option value="trancado">Trancado</option>
                                <option value="concluido">Concluído</option>
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-sm font-medium text-foreground">Observações</label>
                            <textarea v-model="form.observacoes" rows="2" class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Matrícula -->
                <div class="rounded-xl border border-border bg-card p-6 shadow-sm">
                    <h2 class="mb-4 flex items-center gap-2 text-lg font-semibold text-foreground"><AcademicCapIcon class="size-5" /> Matrícula em Turmas</h2>
                    <div class="space-y-3">
                        <template v-for="curso in cursos" :key="curso.id">
                            <div v-if="curso.turmas?.length" class="rounded-lg border border-border p-3">
                                <p class="mb-2 text-sm font-semibold text-foreground">{{ curso.nome }}</p>
                                <div class="flex flex-wrap gap-2">
                                    <label
                                        v-for="turma in curso.turmas"
                                        :key="turma.id"
                                        class="flex cursor-pointer items-center gap-2 rounded-lg border border-input px-3 py-2 text-sm transition-colors"
                                        :class="form.turmas_ids.includes(turma.id) ? 'border-primary bg-primary/10 text-primary' : 'hover:bg-muted'"
                                    >
                                        <input
                                            type="checkbox"
                                            :value="turma.id"
                                            v-model="form.turmas_ids"
                                            class="sr-only"
                                        />
                                        {{ turma.nome }}
                                    </label>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Dados do Responsável -->
                <div class="rounded-xl border border-border bg-card p-6 shadow-sm">
                    <h2 class="mb-4 flex items-center gap-2 text-lg font-semibold text-foreground"><UserIcon class="size-5" /> Dados do Responsável</h2>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-sm font-medium text-foreground">Nome Completo *</label>
                            <input v-model="form.responsavel.nome" type="text" required class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20" />
                            <p v-if="form.errors['responsavel.nome']" class="mt-1 text-xs text-red-500">{{ form.errors['responsavel.nome'] }}</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-foreground">CPF</label>
                            <input v-model="form.responsavel.cpf" type="text" placeholder="000.000.000-00" class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20" />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-foreground">Telefone</label>
                            <input v-model="form.responsavel.telefone" type="tel" class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20" />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-foreground">WhatsApp</label>
                            <input v-model="form.responsavel.whatsapp" type="tel" class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20" />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-foreground">Renda Familiar</label>
                            <select v-model="form.responsavel.renda_familiar" class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary">
                                <option value="">Selecione</option>
                                <option value="menos_1_salario">Menos de 1 salário</option>
                                <option value="ate_2_salarios">Até 2 salários</option>
                                <option value="acima_3_salarios">Acima de 3 salários</option>
                            </select>
                        </div>

                        <!-- Endereço -->
                        <div class="sm:col-span-2">
                            <p class="mb-2 text-sm font-medium text-muted-foreground">Endereço</p>
                        </div>
                        <div class="sm:col-span-2">
                            <input v-model="form.responsavel.endereco_rua" type="text" placeholder="Rua" class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20" />
                        </div>
                        <div>
                            <input v-model="form.responsavel.endereco_numero" type="text" placeholder="Número" class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20" />
                        </div>
                        <div>
                            <input v-model="form.responsavel.endereco_complemento" type="text" placeholder="Complemento" class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20" />
                        </div>
                        <div>
                            <input v-model="form.responsavel.endereco_bairro" type="text" placeholder="Bairro" class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20" />
                        </div>
                        <div>
                            <input v-model="form.responsavel.endereco_cidade" type="text" placeholder="Cidade" class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20" />
                        </div>
                        <div>
                            <select v-model="form.responsavel.endereco_estado" class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary">
                                <option value="">UF</option>
                                <option v-for="uf in estados" :key="uf" :value="uf">{{ uf }}</option>
                            </select>
                        </div>
                        <div>
                            <input v-model="form.responsavel.endereco_cep" type="text" placeholder="CEP" class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20" />
                        </div>

                        <!-- Situação Socioeconômica -->
                        <div class="sm:col-span-2">
                            <p class="mb-2 text-sm font-medium text-muted-foreground">Situação Socioeconômica</p>
                            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                                <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-input px-3 py-2 text-sm transition-colors" :class="form.responsavel.veiculo_proprio ? 'border-primary bg-primary/10' : 'hover:bg-muted'">
                                    <input type="checkbox" v-model="form.responsavel.veiculo_proprio" class="rounded border-input text-primary focus:ring-primary" />
                                    Veículo próprio
                                </label>
                                <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-input px-3 py-2 text-sm transition-colors" :class="form.responsavel.casa_propria ? 'border-primary bg-primary/10' : 'hover:bg-muted'">
                                    <input type="checkbox" v-model="form.responsavel.casa_propria" class="rounded border-input text-primary focus:ring-primary" />
                                    Casa própria
                                </label>
                                <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-input px-3 py-2 text-sm transition-colors" :class="form.responsavel.cadastro_cras ? 'border-primary bg-primary/10' : 'hover:bg-muted'">
                                    <input type="checkbox" v-model="form.responsavel.cadastro_cras" class="rounded border-input text-primary focus:ring-primary" />
                                    Cadastro CRAs
                                </label>
                                <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-input px-3 py-2 text-sm transition-colors" :class="form.responsavel.auxilio_governo ? 'border-primary bg-primary/10' : 'hover:bg-muted'">
                                    <input type="checkbox" v-model="form.responsavel.auxilio_governo" class="rounded border-input text-primary focus:ring-primary" />
                                    Auxílio governo
                                </label>
                                <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-input px-3 py-2 text-sm transition-colors" :class="form.responsavel.desempregado ? 'border-primary bg-primary/10' : 'hover:bg-muted'">
                                    <input type="checkbox" v-model="form.responsavel.desempregado" class="rounded border-input text-primary focus:ring-primary" />
                                    Desempregado
                                </label>
                            </div>
                        </div>

                        <!-- Autorizações -->
                        <div class="sm:col-span-2">
                            <p class="mb-2 text-sm font-medium text-muted-foreground">Autorizações</p>
                            <div class="flex gap-4">
                                <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-input px-3 py-2 text-sm transition-colors" :class="form.responsavel.autorizacao_sozinho ? 'border-primary bg-primary/10' : 'hover:bg-muted'">
                                    <input type="checkbox" v-model="form.responsavel.autorizacao_sozinho" class="rounded border-input text-primary focus:ring-primary" />
                                    Ir embora sozinho
                                </label>
                                <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-input px-3 py-2 text-sm transition-colors" :class="form.responsavel.autorizacao_imagem ? 'border-primary bg-primary/10' : 'hover:bg-muted'">
                                    <input type="checkbox" v-model="form.responsavel.autorizacao_imagem" class="rounded border-input text-primary focus:ring-primary" />
                                    Uso de imagem
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit -->
                <div class="flex justify-end gap-3">
                    <Link href="/alunos" class="rounded-lg border border-border px-6 py-2.5 text-sm font-medium text-foreground transition-colors hover:bg-muted">
                        Cancelar
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-lg bg-primary px-6 py-2.5 text-sm font-medium text-primary-foreground shadow-sm transition-colors hover:bg-primary/90 disabled:opacity-50"
                    >
                        {{ form.processing ? 'Salvando...' : 'Cadastrar Aluno' }}
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
