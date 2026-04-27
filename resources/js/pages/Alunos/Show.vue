<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Pencil, Phone, MapPin, GraduationCap, FileText, HeartPulse, Plus, Trash2 } from 'lucide-vue-next';
import { ChartBarIcon, ExclamationTriangleIcon, BookOpenIcon, UserIcon, DevicePhoneMobileIcon } from '@heroicons/vue/24/outline';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import SearchableSelect from '@/components/SearchableSelect.vue';
import DatePicker from '@/components/DatePicker.vue';
import { useSwal } from '@/composables/useSwal';
import { computed } from 'vue';
import type { Aluno, BreadcrumbItem } from '@/types';

interface ProgramaSaude { id: number; nome: string; area: string }

interface AtendimentoSaude {
    id: number;
    data_atendimento: string;
    profissional: string | null;
    observacoes: string | null;
    programa: { id: number; nome: string; area: string };
    convocacao: { id: number; titulo: string } | null;
}

const props = defineProps<{
    aluno: Aluno & { atendimentos_saude?: AtendimentoSaude[] };
    frequencia: { total: number; presencas: number; percentual: number };
    programas_saude: ProgramaSaude[];
}>();

const { confirmDelete: swalDelete } = useSwal();

const showFormSaude = ref(false);
const formSaude = useForm({
    aluno_id: props.aluno.id,
    programa_id: '' as number | string,
    data_atendimento: new Date().toISOString().slice(0, 10),
    profissional: '',
    observacoes: '',
});
function submitAtendimento() {
    formSaude.post('/saude/atendimentos', {
        preserveScroll: true,
        onSuccess: () => {
            showFormSaude.value = false;
            formSaude.reset();
            formSaude.aluno_id = props.aluno.id;
            formSaude.data_atendimento = new Date().toISOString().slice(0, 10);
        },
    });
}
function deletarAtendimento(a: AtendimentoSaude) {
    swalDelete(`O atendimento de ${a.programa.nome} em ${formatDate(a.data_atendimento)} será removido.`, `/saude/atendimentos/${a.id}`);
}

const areaLabels: Record<string, string> = {
    odontologia: 'Odontologia',
    psicologia: 'Psicologia',
    medica: 'Médica',
    nutricao: 'Nutrição',
    fonoaudiologia: 'Fonoaudiologia',
    geral: 'Geral',
};

function formatDate(d: string) { return new Date(d).toLocaleDateString('pt-BR'); }

const programaSaudeOptions = computed(() =>
    props.programas_saude.map(p => ({ value: p.id, label: p.nome, hint: areaLabels[p.area] || p.area })),
);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Alunos', href: '/alunos' },
    { title: props.aluno.nome, href: `/alunos/${props.aluno.id}` },
];

const rendaLabels: Record<string, string> = {
    menos_1_salario: 'Menos de 1 salário',
    ate_2_salarios: 'Até 2 salários',
    acima_3_salarios: 'Acima de 3 salários',
};

function calcIdade(dataNasc: string): number {
    const nasc = new Date(dataNasc);
    const hoje = new Date();
    let idade = hoje.getFullYear() - nasc.getFullYear();
    const m = hoje.getMonth() - nasc.getMonth();
    if (m < 0 || (m === 0 && hoje.getDate() < nasc.getDate())) idade--;
    return idade;
}
</script>

<template>
    <Head :title="aluno.nome" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full p-4 md:p-6">
            <!-- Header -->
            <div class="mb-6 flex flex-col items-start justify-between gap-4 md:flex-row md:items-center">
                <div class="flex items-center gap-4">
                    <Link href="/alunos" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-border transition-colors hover:bg-muted">
                        <ArrowLeft class="h-4 w-4" />
                    </Link>

                    <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-full border-2 border-primary/20 bg-primary/10 text-xl font-bold text-primary">
                        <img v-if="aluno.foto" :src="aluno.foto_url" :alt="aluno.nome" class="h-full w-full object-cover" />
                        <span v-else>{{ aluno?.nome?.charAt(0) || '' }}</span>
                    </div>

                    <div>
                        <h1 class="text-2xl font-bold text-foreground">{{ aluno.nome }}</h1>
                        <p class="text-sm text-muted-foreground">
                            {{ calcIdade(aluno.data_nascimento) }} anos · {{ aluno.ano_escolar }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <StatusBadge :status="aluno.status" />
                    <a :href="`/export/alunos/${aluno.id}/ficha`" class="inline-flex items-center gap-2 rounded-lg border border-border px-3 py-2 text-sm font-medium transition-colors hover:bg-muted">
                        <FileText class="h-4 w-4" />
                        Ficha PDF
                    </a>
                    <Link :href="`/alunos/${aluno.id}/edit`" class="inline-flex items-center gap-2 rounded-lg border border-border px-3 py-2 text-sm font-medium transition-colors hover:bg-muted">
                        <Pencil class="h-4 w-4" />
                        Editar
                    </Link>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Col. Principal -->
                <div class="space-y-6 lg:col-span-2">
                    <!-- Frequência -->
                    <div class="rounded-xl border border-border bg-card p-6 shadow-sm">
                        <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold text-foreground"><ChartBarIcon class="size-5" /> Frequência</h3>
                        <div class="flex items-center gap-6">
                            <div class="flex h-24 w-24 items-center justify-center rounded-full border-4" :class="frequencia.percentual >= 75 ? 'border-emerald-500' : 'border-red-500'">
                                <span class="text-2xl font-bold" :class="frequencia.percentual >= 75 ? 'text-emerald-600' : 'text-red-600'">
                                    {{ frequencia.percentual }}%
                                </span>
                            </div>
                            <div class="space-y-1 text-sm">
                                <p><span class="font-medium">Total de chamadas:</span> {{ frequencia.total }}</p>
                                <p><span class="font-medium">Presenças:</span> {{ frequencia.presencas }}</p>
                                <p><span class="font-medium">Faltas:</span> {{ frequencia.total - frequencia.presencas }}</p>
                                <p v-if="frequencia.percentual < 75" class="flex items-center gap-1 font-medium text-red-600"><ExclamationTriangleIcon class="size-4" /> Frequência abaixo de 75%</p>
                            </div>
                        </div>
                    </div>

                    <!-- Saúde -->
                    <div class="rounded-xl border border-border bg-card p-6 shadow-sm">
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="flex items-center gap-2 text-lg font-semibold text-foreground">
                                <HeartPulse class="h-5 w-5 text-rose-500" /> Núcleo de Saúde
                            </h3>
                            <button @click="showFormSaude = !showFormSaude" class="inline-flex items-center gap-1 rounded-md border border-input px-3 py-1.5 text-xs font-medium hover:bg-muted">
                                <Plus class="h-3 w-3" /> Registrar atendimento
                            </button>
                        </div>

                        <form v-if="showFormSaude" @submit.prevent="submitAtendimento" class="mb-4 grid gap-3 rounded-lg border border-border bg-muted/20 p-4 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <label class="mb-1 block text-xs font-medium">Programa *</label>
                                <SearchableSelect v-model="formSaude.programa_id" :options="programaSaudeOptions" placeholder="Selecione" :error="!!formSaude.errors.programa_id" />
                                <p v-if="formSaude.errors.programa_id" class="mt-1 text-xs text-red-500">{{ formSaude.errors.programa_id }}</p>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium">Data *</label>
                                <DatePicker v-model="formSaude.data_atendimento" />
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium">Profissional</label>
                                <input v-model="formSaude.profissional" type="text" class="h-9 w-full rounded-md border border-input bg-background px-3 text-sm outline-none focus:border-primary" />
                            </div>
                            <div class="sm:col-span-2">
                                <label class="mb-1 block text-xs font-medium">Observações</label>
                                <textarea v-model="formSaude.observacoes" rows="2" class="w-full rounded-md border border-input bg-background px-3 py-1.5 text-sm outline-none focus:border-primary"></textarea>
                            </div>
                            <div class="sm:col-span-2 flex justify-end gap-2">
                                <button type="button" @click="showFormSaude = false" class="rounded-md border border-border px-3 py-1.5 text-xs font-medium hover:bg-muted">Cancelar</button>
                                <button type="submit" :disabled="formSaude.processing" class="rounded-md bg-primary px-3 py-1.5 text-xs font-medium text-primary-foreground hover:bg-primary/90 disabled:opacity-50">
                                    {{ formSaude.processing ? 'Salvando...' : 'Salvar' }}
                                </button>
                            </div>
                        </form>

                        <p v-if="!aluno.atendimentos_saude?.length" class="text-sm text-muted-foreground">Nenhum atendimento de saúde registrado.</p>

                        <ul v-else class="divide-y divide-border">
                            <li v-for="a in aluno.atendimentos_saude" :key="a.id" class="flex items-center justify-between gap-3 py-3">
                                <div class="flex-1">
                                    <p class="font-medium text-foreground">
                                        {{ a.programa.nome }}
                                        <span class="ml-2 rounded-full bg-rose-100 px-2 py-0.5 text-xs font-normal text-rose-700">{{ areaLabels[a.programa.area] }}</span>
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        {{ formatDate(a.data_atendimento) }}
                                        <span v-if="a.profissional"> · {{ a.profissional }}</span>
                                        <span v-if="a.convocacao"> · via convocação <Link :href="`/saude/convocacoes/${a.convocacao.id}`" class="text-primary hover:underline">"{{ a.convocacao.titulo }}"</Link></span>
                                    </p>
                                    <p v-if="a.observacoes" class="mt-1 text-xs text-muted-foreground">{{ a.observacoes }}</p>
                                </div>
                                <button @click="deletarAtendimento(a)" class="rounded-md p-1.5 hover:bg-red-50 dark:hover:bg-red-950/30" title="Remover"><Trash2 class="h-4 w-4 text-red-500" /></button>
                            </li>
                        </ul>
                    </div>

                    <!-- Turmas Matriculadas -->
                    <div class="rounded-xl border border-border bg-card p-6 shadow-sm">
                        <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold text-foreground"><BookOpenIcon class="size-5" /> Turmas Matriculadas</h3>
                        <div v-if="aluno.matriculas?.length" class="space-y-3">
                            <div v-for="m in aluno.matriculas" :key="m.id" class="flex items-center justify-between rounded-lg bg-muted/30 p-3">
                                <div>
                                    <p class="font-medium text-foreground">{{ m.turma?.curso?.nome }} — {{ m.turma?.nome }}</p>
                                    <p class="text-xs text-muted-foreground">
                                        Professor: 
                                        <template v-if="m.turma?.professores?.length">
                                            {{ m.turma.professores[0].nome }}
                                            <span v-if="m.turma.professores.length > 1" class="text-[10px] text-muted-foreground">(+{{ m.turma.professores.length - 1 }})</span>
                                        </template>
                                        <span v-else>Não definido</span>
                                        · {{ m.ano_letivo }}
                                    </p>
                                </div>
                                <StatusBadge :status="m.status" size="sm" />
                            </div>
                        </div>
                        <p v-else class="text-sm text-muted-foreground">Nenhuma matrícula ativa.</p>
                    </div>
                </div>

                <!-- Sidebar: Info Responsável -->
                <div class="space-y-6">
                    <div class="rounded-xl border border-border bg-card p-6 shadow-sm">
                        <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold text-foreground"><UserIcon class="size-5" /> Responsável</h3>
                        <div class="space-y-3 text-sm">
                            <p class="font-medium text-foreground">{{ aluno.responsavel?.nome }}</p>
                            <div v-if="aluno.responsavel?.telefone" class="flex items-center gap-2 text-muted-foreground">
                                <Phone class="h-4 w-4" />
                                {{ aluno.responsavel.telefone }}
                            </div>
                            <div v-if="aluno.responsavel?.whatsapp" class="flex items-center gap-2 text-muted-foreground">
                                <DevicePhoneMobileIcon class="h-4 w-4" /> {{ aluno.responsavel.whatsapp }}
                            </div>
                            <div v-if="aluno.responsavel?.cpf" class="text-muted-foreground">
                                CPF: {{ aluno.responsavel.cpf }}
                            </div>
                            <div v-if="aluno.responsavel?.renda_familiar" class="text-muted-foreground">
                                Renda: {{ rendaLabels[aluno.responsavel.renda_familiar] || aluno.responsavel.renda_familiar }}
                            </div>
                        </div>
                    </div>

                    <!-- Tags de situação -->
                    <div class="rounded-xl border border-border bg-card p-6 shadow-sm">
                        <h3 class="mb-3 text-sm font-semibold text-foreground">Situação</h3>
                        <div class="flex flex-wrap gap-2 text-xs">
                            <span v-if="aluno.responsavel?.veiculo_proprio" class="rounded-full bg-blue-100 px-2 py-1 text-blue-700">Veículo próprio</span>
                            <span v-if="aluno.responsavel?.casa_propria" class="rounded-full bg-green-100 px-2 py-1 text-green-700">Casa própria</span>
                            <span v-if="aluno.responsavel?.cadastro_cras" class="rounded-full bg-purple-100 px-2 py-1 text-purple-700">CRAs</span>
                            <span v-if="aluno.responsavel?.auxilio_governo" class="rounded-full bg-amber-100 px-2 py-1 text-amber-700">Auxílio gov.</span>
                            <span v-if="aluno.responsavel?.desempregado" class="rounded-full bg-red-100 px-2 py-1 text-red-700">Desempregado</span>
                            <span v-if="aluno.responsavel?.autorizacao_sozinho" class="rounded-full bg-emerald-100 px-2 py-1 text-emerald-700">Vai sozinho</span>
                            <span v-if="aluno.responsavel?.autorizacao_imagem" class="rounded-full bg-sky-100 px-2 py-1 text-sky-700">Uso de imagem</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
