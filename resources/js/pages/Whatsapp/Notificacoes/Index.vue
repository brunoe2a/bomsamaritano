<script setup lang="ts">
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { Send, Search, Trash2, RotateCcw, Smartphone, MessageSquareText, Plus, X } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import Pagination from '@/components/Pagination.vue';
import NativeSelect from '@/components/NativeSelect.vue';
import SearchableSelect from '@/components/SearchableSelect.vue';
import StatCard from '@/components/StatCard.vue';
import { useSwal } from '@/composables/useSwal';
import type { PaginatedData, BreadcrumbItem } from '@/types';

interface Instancia { id: number; nome: string; instance_name: string; }
interface Template { id: number; nome: string; conteudo: string; }
interface Aluno { id: number; nome: string; responsavel?: { id: number; nome: string; whatsapp: string | null; telefone: string | null } | null; }
interface Notificacao {
    id: number;
    numero: string;
    numero_normalizado: string | null;
    mensagem: string;
    status: string;
    numero_valido: boolean | null;
    erro: string | null;
    enviado_em: string | null;
    created_at: string;
    instance?: { nome: string } | null;
    template?: { nome: string } | null;
    aluno?: { nome: string } | null;
    responsavel?: { nome: string } | null;
}

const props = defineProps<{
    notificacoes: PaginatedData<Notificacao>;
    instancias: Instancia[];
    templates: Template[];
    filtros: { status?: string; busca?: string };
    estatisticas: { total: number; pendente: number; enviado: number; falhou: number };
}>();

const { confirmDelete } = useSwal();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'WhatsApp', href: '/whatsapp/notificacoes' },
    { title: 'Notificações', href: '/whatsapp/notificacoes' },
];

const busca = ref(props.filtros.busca || '');
const statusFiltro = ref(props.filtros.status || '');
let debounce: ReturnType<typeof setTimeout>;
function applyFilters() {
    clearTimeout(debounce);
    debounce = setTimeout(() => {
        router.get('/whatsapp/notificacoes', {
            busca: busca.value || undefined,
            status: statusFiltro.value || undefined,
        }, { preserveState: true, replace: true });
    }, 300);
}
watch([busca, statusFiltro], applyFilters);

const showDispatch = ref(false);
const form = useForm({
    whatsapp_instance_id: null as number | null,
    whatsapp_template_id: null as number | null,
    destinatarios: [] as { aluno_id: number | null; nome: string; numero: string }[],
});

const instanciaOptions = computed(() => props.instancias.map(i => ({ value: i.id, label: i.nome })));
const templateOptions = computed(() => props.templates.map(t => ({ value: t.id, label: t.nome })));

const previewTemplate = computed(() => {
    const t = props.templates.find(x => x.id === form.whatsapp_template_id);
    return t?.conteudo || '';
});

const buscaAluno = ref('');
const alunosResultado = ref<Aluno[]>([]);
let buscaDebounce: ReturnType<typeof setTimeout>;

watch(buscaAluno, () => {
    clearTimeout(buscaDebounce);
    buscaDebounce = setTimeout(async () => {
        if (!buscaAluno.value.trim()) { alunosResultado.value = []; return; }
        const r = await fetch(`/whatsapp/alunos-search?busca=${encodeURIComponent(buscaAluno.value)}`);
        alunosResultado.value = await r.json();
    }, 250);
});

function adicionarAluno(a: Aluno) {
    if (form.destinatarios.find(d => d.aluno_id === a.id)) return;
    const numero = a.responsavel?.whatsapp || a.responsavel?.telefone || '';
    form.destinatarios.push({ aluno_id: a.id, nome: a.nome, numero });
}

function adicionarManual() {
    form.destinatarios.push({ aluno_id: null, nome: 'Avulso', numero: '' });
}

function removerDestinatario(i: number) {
    form.destinatarios.splice(i, 1);
}

function abrirDispatch() {
    form.reset();
    form.destinatarios = [];
    buscaAluno.value = '';
    alunosResultado.value = [];
    showDispatch.value = true;
}

function enviar() {
    if (!form.destinatarios.length) return;
    form.post('/whatsapp/notificacoes/dispatch', {
        onSuccess: () => { showDispatch.value = false; },
    });
}

function reenviar(n: Notificacao) {
    router.post(`/whatsapp/notificacoes/${n.id}/reenviar`);
}

function excluir(n: Notificacao) {
    confirmDelete(`A notificação será removida.`, `/whatsapp/notificacoes/${n.id}`);
}

function formatarData(iso: string | null): string {
    if (!iso) return '—';
    const d = new Date(iso);
    if (isNaN(d.getTime())) return '—';
    const dd = String(d.getDate()).padStart(2, '0');
    const mm = String(d.getMonth() + 1).padStart(2, '0');
    const yyyy = d.getFullYear();
    const hh = String(d.getHours()).padStart(2, '0');
    const mi = String(d.getMinutes()).padStart(2, '0');
    return `${dd}/${mm}/${yyyy} ${hh}:${mi}`;
}

function statusInfo(s: string) {
    const map: Record<string, { txt: string; cls: string }> = {
        pendente: { txt: 'Pendente', cls: 'bg-slate-100 text-slate-700' },
        validando: { txt: 'Validando número', cls: 'bg-blue-100 text-blue-700' },
        enviando: { txt: 'Enviando', cls: 'bg-amber-100 text-amber-700' },
        enviado: { txt: 'Enviado', cls: 'bg-emerald-100 text-emerald-700' },
        falhou: { txt: 'Falhou', cls: 'bg-red-100 text-red-700' },
        numero_invalido: { txt: 'Número inválido', cls: 'bg-orange-100 text-orange-700' },
    };
    return map[s] || { txt: s, cls: 'bg-muted text-muted-foreground' };
}
</script>

<template>
    <Head title="Notificações WhatsApp" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="flex items-center gap-2 text-2xl font-bold text-foreground">
                        <Send class="h-6 w-6" /> Notificações WhatsApp
                    </h1>
                    <p class="text-sm text-muted-foreground">Envie mensagens em massa e acompanhe o status de cada disparo.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <Link href="/whatsapp/instancias" class="inline-flex items-center gap-2 rounded-lg border border-border px-4 py-2 text-sm font-medium hover:bg-muted">
                        <Smartphone class="h-4 w-4" /> Instâncias
                    </Link>
                    <Link href="/whatsapp/templates" class="inline-flex items-center gap-2 rounded-lg border border-border px-4 py-2 text-sm font-medium hover:bg-muted">
                        <MessageSquareText class="h-4 w-4" /> Mensagens
                    </Link>
                    <button @click="abrirDispatch" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90">
                        <Send class="h-4 w-4" /> Enviar em Massa
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                <StatCard title="Total" :value="props.estatisticas.total" color="primary" />
                <StatCard title="Em fila / Enviando" :value="props.estatisticas.pendente" color="warning" />
                <StatCard title="Enviadas" :value="props.estatisticas.enviado" color="success" />
                <StatCard title="Falhas" :value="props.estatisticas.falhou" color="danger" />
            </div>

            <div class="rounded-xl border border-border bg-card p-4 shadow-sm">
                <div class="flex flex-wrap gap-3">
                    <div class="relative flex-1 min-w-[240px]">
                        <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <input v-model="busca" type="text" placeholder="Buscar por número ou mensagem..." class="h-10 w-full rounded-lg border border-input bg-background pl-9 pr-3 text-sm outline-none focus:border-primary" />
                    </div>
                    <div class="w-56">
                        <NativeSelect v-model="statusFiltro">
                            <option value="">Todos os status</option>
                            <option value="pendente">Pendente</option>
                            <option value="validando">Validando</option>
                            <option value="enviando">Enviando</option>
                            <option value="enviado">Enviado</option>
                            <option value="falhou">Falhou</option>
                            <option value="numero_invalido">Número inválido</option>
                        </NativeSelect>
                    </div>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl border border-border bg-card shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="border-b border-border bg-muted/40 text-xs uppercase tracking-wider text-muted-foreground">
                            <tr>
                                <th class="px-4 py-3 text-left">Destinatário</th>
                                <th class="px-4 py-3 text-left">Número</th>
                                <th class="px-4 py-3 text-left">Mensagem</th>
                                <th class="px-4 py-3 text-left">Instância</th>
                                <th class="px-4 py-3 text-left">Status</th>
                                <th class="px-4 py-3 text-left">Enviado em</th>
                                <th class="px-4 py-3 text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="!props.notificacoes.data.length"><td colspan="7" class="p-8 text-center text-muted-foreground">Nenhuma notificação registrada.</td></tr>
                            <tr v-for="n in props.notificacoes.data" :key="n.id" class="border-b border-border last:border-0 hover:bg-muted/30 align-top">
                                <td class="px-4 py-3">
                                    <div class="font-medium">{{ n.aluno?.nome || n.responsavel?.nome || '—' }}</div>
                                    <div class="text-xs text-muted-foreground">{{ n.template?.nome || '' }}</div>
                                </td>
                                <td class="px-4 py-3 text-muted-foreground">{{ n.numero }}</td>
                                <td class="px-4 py-3 max-w-sm truncate text-muted-foreground" :title="n.mensagem">{{ n.mensagem }}</td>
                                <td class="px-4 py-3 text-muted-foreground">{{ n.instance?.nome || '—' }}</td>
                                <td class="px-4 py-3">
                                    <span :class="statusInfo(n.status).cls" class="rounded-full px-2 py-0.5 text-xs">{{ statusInfo(n.status).txt }}</span>
                                    <div v-if="n.erro" class="mt-1 text-[11px] text-red-500" :title="n.erro">{{ n.erro.length > 60 ? n.erro.substring(0, 60) + '…' : n.erro }}</div>
                                </td>
                                <td class="px-4 py-3 text-xs text-muted-foreground">{{ formatarData(n.enviado_em) }}</td>
                                <td class="px-4 py-3 text-right">
                                    <div class="inline-flex gap-1">
                                        <button v-if="['falhou', 'numero_invalido'].includes(n.status)" @click="reenviar(n)" title="Reenviar" class="rounded-md p-1.5 hover:bg-muted"><RotateCcw class="h-4 w-4" /></button>
                                        <button @click="excluir(n)" title="Excluir" class="rounded-md p-1.5 hover:bg-red-50"><Trash2 class="h-4 w-4 text-red-500" /></button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <Pagination :links="props.notificacoes.links" />
        </div>

        <div v-if="showDispatch" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-3xl rounded-xl bg-card p-6 shadow-xl max-h-[90vh] overflow-y-auto">
                <h2 class="mb-4 text-lg font-semibold">Enviar Notificações em Massa</h2>
                <form @submit.prevent="enviar" class="space-y-4">
                    <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium">Instância *</label>
                            <SearchableSelect v-model="form.whatsapp_instance_id" :options="instanciaOptions" placeholder="Selecione a instância conectada" />
                            <p v-if="form.errors.whatsapp_instance_id" class="mt-1 text-xs text-red-500">{{ form.errors.whatsapp_instance_id }}</p>
                            <p v-if="!props.instancias.length" class="mt-1 text-xs text-amber-600">Nenhuma instância conectada. Conecte uma em <Link href="/whatsapp/instancias" class="underline">Instâncias</Link>.</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium">Mensagem *</label>
                            <SearchableSelect v-model="form.whatsapp_template_id" :options="templateOptions" placeholder="Escolha a mensagem" />
                            <p v-if="form.errors.whatsapp_template_id" class="mt-1 text-xs text-red-500">{{ form.errors.whatsapp_template_id }}</p>
                        </div>
                    </div>

                    <div v-if="previewTemplate" class="rounded-lg border border-dashed border-border bg-muted/30 p-3 text-sm whitespace-pre-wrap">{{ previewTemplate }}</div>

                    <div class="space-y-2">
                        <label class="block text-sm font-medium">Destinatários *</label>
                        <div class="flex flex-wrap items-center gap-2">
                            <div class="relative flex-1 min-w-[240px]">
                                <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                                <input v-model="buscaAluno" type="text" placeholder="Buscar aluno..." class="h-10 w-full rounded-lg border border-input bg-background pl-9 pr-3 text-sm outline-none focus:border-primary" />
                                <div v-if="alunosResultado.length" class="absolute z-10 mt-1 max-h-60 w-full overflow-y-auto rounded-lg border border-border bg-popover shadow-lg">
                                    <button v-for="a in alunosResultado" :key="a.id" type="button" @click="adicionarAluno(a)" class="flex w-full items-center justify-between gap-2 px-3 py-2 text-left text-sm hover:bg-muted">
                                        <span>{{ a.nome }}</span>
                                        <span class="text-xs text-muted-foreground">{{ a.responsavel?.whatsapp || a.responsavel?.telefone || 'sem número' }}</span>
                                    </button>
                                </div>
                            </div>
                            <button type="button" @click="adicionarManual" class="inline-flex items-center gap-1.5 rounded-lg border border-border px-3 py-2 text-sm font-medium hover:bg-muted">
                                <Plus class="h-4 w-4" /> Número avulso
                            </button>
                        </div>

                        <div v-if="form.destinatarios.length" class="rounded-lg border border-border">
                            <div v-for="(d, i) in form.destinatarios" :key="i" class="flex items-center gap-2 border-b border-border p-2 last:border-0">
                                <span class="flex-1 truncate text-sm">{{ d.nome }}</span>
                                <input v-model="d.numero" type="text" placeholder="Número WhatsApp" class="h-9 w-48 rounded-lg border border-input bg-background px-2 text-sm outline-none focus:border-primary" />
                                <button type="button" @click="removerDestinatario(i)" class="rounded-md p-1.5 hover:bg-red-50"><X class="h-4 w-4 text-red-500" /></button>
                            </div>
                        </div>
                        <p v-else class="text-xs text-muted-foreground">Adicione alunos ou números avulsos para envio.</p>
                    </div>

                    <div class="rounded-lg bg-blue-50 p-3 text-xs text-blue-900 dark:bg-blue-950 dark:text-blue-200">
                        ℹ️ Os disparos vão para uma fila com delay aleatório de 60 a 180 segundos entre cada mensagem. O número de cada destinatário será validado antes do envio.
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="showDispatch = false" class="rounded-lg border border-border px-4 py-2 text-sm font-medium hover:bg-muted">Cancelar</button>
                        <button type="submit" :disabled="form.processing || !form.destinatarios.length" class="rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90 disabled:opacity-50">
                            {{ form.processing ? 'Enfileirando...' : `Enfileirar ${form.destinatarios.length} envio(s)` }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
