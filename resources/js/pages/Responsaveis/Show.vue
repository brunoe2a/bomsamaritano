<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Pencil, Plus, Phone, MessageCircle, MapPin, IdCard } from 'lucide-vue-next';
import { UserIcon, AcademicCapIcon } from '@heroicons/vue/24/outline';
import AppLayout from '@/layouts/AppLayout.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import type { BreadcrumbItem } from '@/types';

interface AlunoLite {
    id: number;
    nome: string;
    data_nascimento: string;
    ano_escolar: string | null;
    status: string;
    matriculas?: { id: number; turma?: { id: number; nome: string; curso?: { nome: string } | null } | null }[];
}

interface Responsavel {
    id: number;
    nome: string;
    cpf: string | null;
    telefone: string | null;
    whatsapp: string | null;
    endereco_completo?: string;
    renda_familiar: string | null;
    veiculo_proprio: boolean;
    casa_propria: boolean;
    cadastro_cras: boolean;
    auxilio_governo: boolean;
    desempregado: boolean;
    autorizacao_sozinho: boolean;
    autorizacao_imagem: boolean;
    alunos: AlunoLite[];
}

const props = defineProps<{ responsavel: Responsavel }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Responsáveis', href: '/responsaveis' },
    { title: props.responsavel.nome, href: `/responsaveis/${props.responsavel.id}` },
];

const rendaLabels: Record<string, string> = {
    menos_1_salario: 'Menos de 1 salário',
    ate_2_salarios: 'Até 2 salários',
    acima_3_salarios: 'Acima de 3 salários',
};
</script>

<template>
    <Head :title="responsavel.nome" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full p-4 md:p-6">
            <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <Link href="/responsaveis" class="flex h-9 w-9 items-center justify-center rounded-lg border border-border transition-colors hover:bg-muted">
                        <ArrowLeft class="h-4 w-4" />
                    </Link>
                    <div>
                        <h1 class="text-2xl font-bold text-foreground">{{ responsavel.nome }}</h1>
                        <p class="text-sm text-muted-foreground">Responsável</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <Link
                        :href="`/alunos/create?responsavel_id=${responsavel.id}`"
                        class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow-sm transition-colors hover:bg-primary/90"
                    >
                        <Plus class="h-4 w-4" /> Adicionar outro filho
                    </Link>
                    <Link
                        :href="`/responsaveis/${responsavel.id}/edit`"
                        class="inline-flex items-center gap-2 rounded-lg border border-border px-4 py-2 text-sm font-medium transition-colors hover:bg-muted"
                    >
                        <Pencil class="h-4 w-4" /> Editar
                    </Link>
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-3">
                <div class="space-y-6 md:col-span-2">
                    <div class="rounded-xl border border-border bg-card p-6 shadow-sm">
                        <h2 class="mb-4 flex items-center gap-2 text-lg font-semibold text-foreground"><UserIcon class="size-5" /> Dados</h2>
                        <dl class="grid gap-4 text-sm sm:grid-cols-2">
                            <div>
                                <dt class="text-xs font-medium uppercase text-muted-foreground">CPF</dt>
                                <dd class="mt-1 flex items-center gap-2 text-foreground"><IdCard class="h-4 w-4 text-muted-foreground" /> {{ responsavel.cpf || '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium uppercase text-muted-foreground">Renda Familiar</dt>
                                <dd class="mt-1 text-foreground">{{ responsavel.renda_familiar ? rendaLabels[responsavel.renda_familiar] : '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium uppercase text-muted-foreground">Telefone</dt>
                                <dd class="mt-1 flex items-center gap-2 text-foreground"><Phone class="h-4 w-4 text-muted-foreground" /> {{ responsavel.telefone || '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium uppercase text-muted-foreground">WhatsApp</dt>
                                <dd class="mt-1 flex items-center gap-2 text-foreground"><MessageCircle class="h-4 w-4 text-muted-foreground" /> {{ responsavel.whatsapp || '—' }}</dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-xs font-medium uppercase text-muted-foreground">Endereço</dt>
                                <dd class="mt-1 flex items-start gap-2 text-foreground"><MapPin class="mt-0.5 h-4 w-4 text-muted-foreground" /> {{ responsavel.endereco_completo || '—' }}</dd>
                            </div>
                        </dl>
                    </div>

                    <div class="rounded-xl border border-border bg-card p-6 shadow-sm">
                        <div class="mb-4 flex items-center justify-between">
                            <h2 class="flex items-center gap-2 text-lg font-semibold text-foreground"><AcademicCapIcon class="size-5" /> Filhos ({{ responsavel.alunos.length }})</h2>
                            <Link
                                :href="`/alunos/create?responsavel_id=${responsavel.id}`"
                                class="inline-flex items-center gap-1 rounded-md border border-input px-3 py-1.5 text-xs font-medium transition-colors hover:bg-muted"
                            >
                                <Plus class="h-3 w-3" /> Adicionar
                            </Link>
                        </div>

                        <p v-if="!responsavel.alunos.length" class="text-sm text-muted-foreground">Nenhum aluno vinculado a este responsável.</p>

                        <ul v-else class="divide-y divide-border">
                            <li v-for="aluno in responsavel.alunos" :key="aluno.id" class="flex items-center justify-between gap-3 py-3">
                                <div>
                                    <Link :href="`/alunos/${aluno.id}`" class="font-medium text-foreground hover:underline">{{ aluno.nome }}</Link>
                                    <p class="text-xs text-muted-foreground">
                                        {{ aluno.ano_escolar || 'Sem ano escolar' }}
                                        <template v-if="aluno.matriculas?.length">
                                            · {{ aluno.matriculas.map(m => m.turma?.nome).filter(Boolean).join(', ') }}
                                        </template>
                                    </p>
                                </div>
                                <StatusBadge :status="aluno.status" />
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="rounded-xl border border-border bg-card p-6 shadow-sm">
                        <h3 class="mb-3 text-sm font-semibold text-foreground">Situação Socioeconômica</h3>
                        <ul class="space-y-1.5 text-sm">
                            <li class="flex justify-between"><span class="text-muted-foreground">Veículo próprio</span><span>{{ responsavel.veiculo_proprio ? 'Sim' : 'Não' }}</span></li>
                            <li class="flex justify-between"><span class="text-muted-foreground">Casa própria</span><span>{{ responsavel.casa_propria ? 'Sim' : 'Não' }}</span></li>
                            <li class="flex justify-between"><span class="text-muted-foreground">Cadastro CRAs</span><span>{{ responsavel.cadastro_cras ? 'Sim' : 'Não' }}</span></li>
                            <li class="flex justify-between"><span class="text-muted-foreground">Auxílio governo</span><span>{{ responsavel.auxilio_governo ? 'Sim' : 'Não' }}</span></li>
                            <li class="flex justify-between"><span class="text-muted-foreground">Desempregado</span><span>{{ responsavel.desempregado ? 'Sim' : 'Não' }}</span></li>
                        </ul>
                    </div>

                    <div class="rounded-xl border border-border bg-card p-6 shadow-sm">
                        <h3 class="mb-3 text-sm font-semibold text-foreground">Autorizações</h3>
                        <ul class="space-y-1.5 text-sm">
                            <li class="flex justify-between"><span class="text-muted-foreground">Ir embora sozinho</span><span>{{ responsavel.autorizacao_sozinho ? 'Sim' : 'Não' }}</span></li>
                            <li class="flex justify-between"><span class="text-muted-foreground">Uso de imagem</span><span>{{ responsavel.autorizacao_imagem ? 'Sim' : 'Não' }}</span></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
