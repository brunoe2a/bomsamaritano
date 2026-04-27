<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Plus, Pencil, Trash2, MessageSquareText } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import NativeSelect from '@/components/NativeSelect.vue';
import { useSwal } from '@/composables/useSwal';
import type { BreadcrumbItem } from '@/types';

interface Template {
    id: number;
    nome: string;
    conteudo: string;
    variaveis: string[] | null;
    status: string;
}

const props = defineProps<{
    templates: Template[];
    variaveis_disponiveis: Record<string, string>;
}>();

const { confirmDelete } = useSwal();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'WhatsApp', href: '/whatsapp/notificacoes' },
    { title: 'Mensagens', href: '/whatsapp/templates' },
];

const showModal = ref(false);
const editing = ref<Template | null>(null);

const form = useForm({
    nome: '',
    conteudo: '',
    variaveis: [] as string[],
    status: 'ativo',
});

const textareaRef = ref<HTMLTextAreaElement | null>(null);

function abrirCriar() {
    editing.value = null;
    form.reset();
    form.status = 'ativo';
    showModal.value = true;
}

function abrirEditar(t: Template) {
    editing.value = t;
    form.nome = t.nome;
    form.conteudo = t.conteudo;
    form.variaveis = t.variaveis || [];
    form.status = t.status;
    showModal.value = true;
}

function placeholder(chave: string) {
    return '{{' + chave + '}}';
}

function inserirVariavel(chave: string) {
    const tag = placeholder(chave);
    const el = textareaRef.value;
    if (el) {
        const start = el.selectionStart ?? form.conteudo.length;
        const end = el.selectionEnd ?? form.conteudo.length;
        form.conteudo = form.conteudo.substring(0, start) + tag + form.conteudo.substring(end);
    } else {
        form.conteudo += tag;
    }
}

function salvar() {
    if (editing.value) {
        form.put(`/whatsapp/templates/${editing.value.id}`, { onSuccess: () => (showModal.value = false) });
    } else {
        form.post('/whatsapp/templates', { onSuccess: () => (showModal.value = false) });
    }
}

function excluir(t: Template) {
    confirmDelete(`A mensagem "${t.nome}" será removida permanentemente.`, `/whatsapp/templates/${t.id}`);
}
</script>

<template>
    <Head title="Mensagens WhatsApp" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="flex items-center gap-2 text-2xl font-bold text-foreground">
                        <MessageSquareText class="h-6 w-6" /> Mensagens Padrão
                    </h1>
                    <p class="text-sm text-muted-foreground">Cadastre as mensagens com variáveis para uso nas notificações.</p>
                </div>
                <button @click="abrirCriar" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90">
                    <Plus class="h-4 w-4" /> Nova Mensagem
                </button>
            </div>

            <div class="overflow-hidden rounded-xl border border-border bg-card shadow-sm">
                <table class="w-full text-sm">
                    <thead class="border-b border-border bg-muted/40 text-xs uppercase tracking-wider text-muted-foreground">
                        <tr>
                            <th class="px-4 py-3 text-left">Nome</th>
                            <th class="px-4 py-3 text-left">Prévia</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!props.templates.length">
                            <td colspan="4" class="p-8 text-center text-muted-foreground">Nenhuma mensagem cadastrada.</td>
                        </tr>
                        <tr v-for="t in props.templates" :key="t.id" class="border-b border-border last:border-0 hover:bg-muted/30">
                            <td class="px-4 py-3 font-medium">{{ t.nome }}</td>
                            <td class="px-4 py-3 max-w-md truncate text-muted-foreground">{{ t.conteudo }}</td>
                            <td class="px-4 py-3">
                                <span :class="t.status === 'ativo' ? 'bg-emerald-100 text-emerald-700' : 'bg-muted text-muted-foreground'" class="rounded-full px-2 py-0.5 text-xs">{{ t.status }}</span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="inline-flex gap-1">
                                    <button @click="abrirEditar(t)" class="rounded-md p-1.5 hover:bg-muted"><Pencil class="h-4 w-4" /></button>
                                    <button @click="excluir(t)" class="rounded-md p-1.5 hover:bg-red-50"><Trash2 class="h-4 w-4 text-red-500" /></button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-2xl rounded-xl bg-card p-6 shadow-xl">
                <h2 class="mb-4 text-lg font-semibold">{{ editing ? 'Editar Mensagem' : 'Nova Mensagem' }}</h2>
                <form @submit.prevent="salvar" class="space-y-4">
                    <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-sm font-medium">Nome *</label>
                            <input v-model="form.nome" required type="text" class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-primary" />
                            <p v-if="form.errors.nome" class="mt-1 text-xs text-red-500">{{ form.errors.nome }}</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium">Status</label>
                            <NativeSelect v-model="form.status">
                                <option value="ativo">Ativo</option>
                                <option value="inativo">Inativo</option>
                            </NativeSelect>
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium">Variáveis disponíveis</label>
                        <div class="flex flex-wrap gap-2">
                            <button v-for="(label, chave) in props.variaveis_disponiveis" :key="chave" type="button" @click="inserirVariavel(chave)" class="rounded-full border border-border bg-muted/50 px-3 py-1 text-xs hover:bg-muted">
                                {{ label }} <span class="text-muted-foreground">({{ placeholder(chave) }})</span>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium">Conteúdo *</label>
                        <textarea ref="textareaRef" v-model="form.conteudo" required rows="6" placeholder="Olá {{nome_responsavel}}, este é um aviso sobre o aluno {{nome_aluno}} da turma {{turma}}." class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm outline-none focus:border-primary"></textarea>
                        <p v-if="form.errors.conteudo" class="mt-1 text-xs text-red-500">{{ form.errors.conteudo }}</p>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="showModal = false" class="rounded-lg border border-border px-4 py-2 text-sm font-medium hover:bg-muted">Cancelar</button>
                        <button type="submit" :disabled="form.processing" class="rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90 disabled:opacity-50">
                            {{ form.processing ? 'Salvando...' : 'Salvar' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
