<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Plus, Trash2, RefreshCw, Power, QrCode, Smartphone } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { useSwal } from '@/composables/useSwal';
import type { BreadcrumbItem } from '@/types';

interface Instancia {
    id: number;
    nome: string;
    instance_name: string;
    numero: string | null;
    status: 'connected' | 'connecting' | 'disconnected';
    qr_code: string | null;
    last_status_at: string | null;
}

const props = defineProps<{ instancias: Instancia[] }>();

const { confirmDelete } = useSwal();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'WhatsApp', href: '/whatsapp/notificacoes' },
    { title: 'Instâncias', href: '/whatsapp/instancias' },
];

const showModal = ref(false);
const qrInstancia = ref<Instancia | null>(null);
const qrAtual = ref<string | null>(null);

const form = useForm({ nome: '', numero: '' });

function abrirModal() {
    form.reset();
    showModal.value = true;
}

function salvar() {
    form.post('/whatsapp/instancias', { onSuccess: () => (showModal.value = false) });
}

async function abrirQr(inst: Instancia) {
    qrInstancia.value = inst;
    qrAtual.value = inst.qr_code;
    const resp = await fetch(`/whatsapp/instancias/${inst.id}/qrcode`);
    const data = await resp.json();
    qrAtual.value = data.qr_code;
}

async function atualizarStatus(inst: Instancia) {
    await fetch(`/whatsapp/instancias/${inst.id}/status`);
    router.reload({ only: ['instancias'] });
}

function desconectar(inst: Instancia) {
    router.post(`/whatsapp/instancias/${inst.id}/desconectar`);
}

function excluir(inst: Instancia) {
    confirmDelete(`A instância "${inst.nome}" será removida permanentemente.`, `/whatsapp/instancias/${inst.id}`);
}

function statusBadge(s: string) {
    if (s === 'connected') return { txt: 'Conectado', cls: 'bg-emerald-100 text-emerald-700' };
    if (s === 'connecting') return { txt: 'Conectando', cls: 'bg-amber-100 text-amber-700' };
    return { txt: 'Desconectado', cls: 'bg-muted text-muted-foreground' };
}

function imgSrc(qr: string | null) {
    if (!qr) return '';
    return qr.startsWith('data:') ? qr : `data:image/png;base64,${qr}`;
}
</script>

<template>
    <Head title="Instâncias WhatsApp" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="flex items-center gap-2 text-2xl font-bold text-foreground">
                        <Smartphone class="h-6 w-6" /> Instâncias WhatsApp
                    </h1>
                    <p class="text-sm text-muted-foreground">Cadastre os números e conecte via QR Code da Evolution API.</p>
                </div>
                <button @click="abrirModal" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow-sm transition-colors hover:bg-primary/90">
                    <Plus class="h-4 w-4" /> Nova Instância
                </button>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div v-if="!props.instancias.length" class="rounded-xl border border-dashed border-border p-8 text-center text-muted-foreground sm:col-span-2 lg:col-span-3">
                    Nenhuma instância cadastrada.
                </div>
                <div v-for="inst in props.instancias" :key="inst.id" class="rounded-xl border border-border bg-card p-4 shadow-sm">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <h3 class="font-semibold text-foreground">{{ inst.nome }}</h3>
                            <p class="text-xs text-muted-foreground">{{ inst.numero || 'Número será definido ao conectar' }}</p>
                            <p class="mt-1 text-[10px] uppercase tracking-wider text-muted-foreground">{{ inst.instance_name }}</p>
                        </div>
                        <span :class="statusBadge(inst.status).cls" class="rounded-full px-2 py-0.5 text-xs">
                            {{ statusBadge(inst.status).txt }}
                        </span>
                    </div>

                    <div class="mt-4 flex flex-wrap gap-2">
                        <button v-if="inst.status !== 'connected'" @click="abrirQr(inst)" class="inline-flex items-center gap-1.5 rounded-md border border-border px-3 py-1.5 text-xs font-medium hover:bg-muted">
                            <QrCode class="h-3.5 w-3.5" /> QR Code
                        </button>
                        <button @click="atualizarStatus(inst)" class="inline-flex items-center gap-1.5 rounded-md border border-border px-3 py-1.5 text-xs font-medium hover:bg-muted">
                            <RefreshCw class="h-3.5 w-3.5" /> Atualizar status
                        </button>
                        <button v-if="inst.status === 'connected'" @click="desconectar(inst)" class="inline-flex items-center gap-1.5 rounded-md border border-border px-3 py-1.5 text-xs font-medium text-amber-700 hover:bg-amber-50">
                            <Power class="h-3.5 w-3.5" /> Desconectar
                        </button>
                        <button @click="excluir(inst)" class="ml-auto inline-flex items-center gap-1.5 rounded-md border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50">
                            <Trash2 class="h-3.5 w-3.5" /> Excluir
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-md rounded-xl bg-card p-6 shadow-xl">
                <h2 class="mb-4 text-lg font-semibold">Nova Instância WhatsApp</h2>
                <form @submit.prevent="salvar" class="space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium">Nome *</label>
                        <input v-model="form.nome" required type="text" placeholder="Ex.: Marketing, Cobrança..." class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary" />
                        <p v-if="form.errors.nome" class="mt-1 text-xs text-red-500">{{ form.errors.nome }}</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium">Número (opcional)</label>
                        <input v-model="form.numero" type="text" placeholder="55 11 99999-9999" class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary" />
                        <p class="mt-1 text-xs text-muted-foreground">Número que será conectado a essa instância. Pode ser preenchido depois.</p>
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="showModal = false" class="rounded-lg border border-border px-4 py-2 text-sm font-medium hover:bg-muted">Cancelar</button>
                        <button type="submit" :disabled="form.processing" class="rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90 disabled:opacity-50">
                            {{ form.processing ? 'Criando...' : 'Criar e gerar QR' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div v-if="qrInstancia" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-md rounded-xl bg-card p-6 shadow-xl">
                <h2 class="mb-2 text-lg font-semibold">Conectar — {{ qrInstancia.nome }}</h2>
                <p class="mb-4 text-sm text-muted-foreground">Abra o WhatsApp no celular → Aparelhos conectados → Conectar um aparelho → escaneie o QR.</p>
                <div class="flex aspect-square items-center justify-center rounded-lg border border-dashed border-border bg-muted/30 p-2">
                    <img v-if="qrAtual" :src="imgSrc(qrAtual)" alt="QR Code" class="max-h-full max-w-full" />
                    <span v-else class="text-sm text-muted-foreground">Gerando QR Code...</span>
                </div>
                <div class="mt-4 flex justify-end gap-3">
                    <button @click="atualizarStatus(qrInstancia)" class="rounded-lg border border-border px-4 py-2 text-sm font-medium hover:bg-muted">Verificar conexão</button>
                    <button @click="qrInstancia = null" class="rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90">Fechar</button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
