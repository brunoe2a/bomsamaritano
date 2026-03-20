<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ChevronLeft, Calendar, User, Building2, Tag, FileText, Download, ExternalLink, DollarSign, Pencil, X } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import type { FinanceiroLancamento, BreadcrumbItem } from '@/types';

const props = defineProps<{
    lancamento: FinanceiroLancamento;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Financeiro', href: '/financeiro' },
    { title: 'Detalhes', href: '#' },
];

function formatCurrency(v: number | string) {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(Number(v));
}

function formatDate(d: string) {
    return new Date(d).toLocaleDateString('pt-BR');
}

const isImage = (path: string) => {
    const ext = path.split('.').pop()?.toLowerCase();
    return ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext || '');
};
</script>

<template>
    <Head title="Detalhes do Lançamento" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6 w-full mx-auto">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <Link href="/financeiro" class="inline-flex items-center gap-2 text-sm text-muted-foreground hover:text-foreground">
                    <ChevronLeft class="h-4 w-4" /> Voltar para listagem
                </Link>
                <div class="flex gap-2">
                    <Link :href="`/financeiro/${lancamento.id}/edit`" class="inline-flex items-center gap-2 rounded-lg border border-border px-4 py-2 text-sm font-medium hover:bg-muted text-foreground transition-colors">
                        <Pencil class="h-4 w-4" /> Editar Lançamento
                    </Link>
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-3">
                <!-- Informações Principais -->
                <div class="md:col-span-2 space-y-6">
                    <div class="rounded-xl border border-border bg-card p-6 shadow-sm overflow-hidden relative">
                         <!-- Decorative edge based on type -->
                        <div class="absolute top-0 left-0 w-1 h-full" :class="lancamento.tipo === 'entrada' ? 'bg-emerald-500' : 'bg-red-500'"></div>
                        
                        <div class="flex items-start justify-between mb-8">
                            <div>
                                <h1 class="text-2xl font-bold text-foreground leading-tight">{{ lancamento.descricao }}</h1>
                                <p class="text-sm text-muted-foreground mt-1">Lançamento #{{ lancamento.id }} • Registrado em {{ formatDate(lancamento.created_at) }}</p>
                            </div>
                            <StatusBadge :status="lancamento.tipo" />
                        </div>

                        <div class="grid gap-8 sm:grid-cols-2">
                            <div class="flex items-center gap-4">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
                                    <Calendar class="h-6 w-6" />
                                </div>
                                <div>
                                    <p class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Data do Lançamento</p>
                                    <p class="text-base font-bold text-foreground">{{ formatDate(lancamento.data) }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
                                    <DollarSign class="h-6 w-6" />
                                </div>
                                <div>
                                    <p class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Valor Total</p>
                                    <p class="text-2xl font-black" :class="lancamento.tipo === 'entrada' ? 'text-emerald-600' : 'text-red-600'">
                                        {{ lancamento.tipo === 'entrada' ? '+' : '-' }} {{ formatCurrency(lancamento.valor) }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
                                    <Tag class="h-6 w-6" />
                                </div>
                                <div>
                                    <p class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Categoria</p>
                                    <p class="text-base font-bold text-foreground">{{ lancamento.categoria?.nome || 'Não informada' }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
                                    <Building2 class="h-6 w-6" />
                                </div>
                                <div>
                                    <p class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Unidade</p>
                                    <p class="text-base font-bold text-foreground">{{ lancamento.unidade?.nome || 'Todas as Unidades' }}</p>
                                </div>
                            </div>
                        </div>

                        <div v-if="lancamento.doador" class="mt-8 pt-8 border-t border-border">
                            <h3 class="text-sm font-bold mb-4 flex items-center gap-2 text-foreground">
                                <User class="h-4 w-4" /> Doador / Favorecido
                            </h3>
                            <div class="bg-muted/30 rounded-xl p-5 border border-border flex items-center gap-4">
                                <div class="h-10 w-10 rounded-full bg-primary/20 flex items-center justify-center text-primary font-bold">
                                    {{ lancamento.doador.nome.charAt(0) }}
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-foreground">{{ lancamento.doador.nome }}</p>
                                    <p class="text-xs text-muted-foreground">{{ lancamento.doador.tipo === 'pessoa_fisica' ? 'Pessoa Física' : 'Pessoa Jurídica' }}</p>
                                </div>
                            </div>
                        </div>

                        <div v-if="lancamento.observacoes" class="mt-8 pt-6 border-t border-border">
                            <h3 class="text-sm font-bold mb-3 text-foreground">Observações Adicionais</h3>
                            <div class="text-sm text-muted-foreground bg-muted/20 p-4 rounded-lg italic leading-relaxed">
                                {{ lancamento.observacoes }}
                            </div>
                        </div>
                    </div>

                    <!-- Seção do Comprovante -->
                    <div class="rounded-xl border border-border bg-card p-6 shadow-sm overflow-hidden">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-bold flex items-center gap-2">
                                <FileText class="h-5 w-5 text-primary" /> Comprovante de Pagamento
                            </h3>
                            <div v-if="lancamento.comprovante" class="flex gap-2">
                                <a :href="`/storage/${lancamento.comprovante}`" target="_blank" class="p-2 rounded-lg hover:bg-muted text-muted-foreground transition-colors" title="Abrir original">
                                    <ExternalLink class="h-5 w-5" />
                                </a>
                                <a :href="`/storage/${lancamento.comprovante}`" download class="p-2 rounded-lg hover:bg-muted text-muted-foreground transition-colors" title="Download">
                                    <Download class="h-5 w-5" />
                                </a>
                            </div>
                        </div>

                        <div v-if="lancamento.comprovante" class="rounded-xl border border-border bg-muted/10 overflow-hidden">
                            <template v-if="isImage(lancamento.comprovante)">
                                <div class="p-2">
                                    <img :src="`/storage/${lancamento.comprovante}`" alt="Comprovante" class="w-full h-auto max-h-[600px] object-contain mx-auto rounded-lg shadow-sm" />
                                </div>
                            </template>
                            <template v-else>
                                <div class="flex flex-col items-center justify-center p-16 text-center">
                                    <div class="h-20 w-20 rounded-2xl bg-primary/10 flex items-center justify-center mb-6 shadow-inner">
                                        <FileText class="h-10 w-10 text-primary" />
                                    </div>
                                    <p class="text-lg font-bold text-foreground mb-2">Documento Digital</p>
                                    <p class="text-sm text-muted-foreground mb-8 max-w-sm mx-auto">Este comprovante está em formato PDF ou outro tipo de documento que não pode ser exibido diretamente aqui.</p>
                                    <a :href="`/storage/${lancamento.comprovante}`" target="_blank" class="inline-flex items-center gap-2 rounded-xl bg-primary px-8 py-3 text-sm font-bold text-primary-foreground shadow-lg shadow-primary/20 hover:bg-primary/90 transition-all hover:scale-105 active:scale-95">
                                        <ExternalLink class="h-4 w-4" /> Visualizar Documento Completo
                                    </a>
                                </div>
                            </template>
                        </div>
                        <div v-else class="flex flex-col items-center justify-center p-16 text-center border-2 border-dashed border-border rounded-xl bg-muted/5">
                            <div class="h-12 w-12 rounded-full bg-muted flex items-center justify-center mb-4">
                                <X class="h-6 w-6 text-muted-foreground" />
                            </div>
                            <p class="text-sm text-muted-foreground font-medium italic">Nenhum anexo foi enviado para este lançamento.</p>
                        </div>
                    </div>
                </div>

                <!-- Barra Lateral Direita (Informações de Auditoria) -->
                <div class="space-y-6">
                    <div class="rounded-xl border border-border bg-card p-6 shadow-sm">
                        <h3 class="text-xs font-black mb-6 uppercase tracking-[0.2em] text-muted-foreground">Log de Registro</h3>
                        <div class="space-y-6 relative">
                            <!-- Timeline Line -->
                            <div class="absolute left-[7px] top-2 bottom-2 w-0.5 bg-border"></div>
                            
                            <div class="flex items-start gap-4 relative">
                                <div class="h-4 w-4 rounded-full bg-primary border-4 border-card z-10 mt-0.5 shadow-sm"></div>
                                <div>
                                    <p class="text-xs font-bold text-muted-foreground uppercase mb-1">Responsável</p>
                                    <p class="text-sm font-medium text-foreground">{{ lancamento.usuario?.name || 'Inexistente' }}</p>
                                </div>
                            </div>
                            
                            <div class="flex items-start gap-4 relative">
                                <div class="h-4 w-4 rounded-full bg-border border-4 border-card z-10 mt-0.5 shadow-sm"></div>
                                <div>
                                    <p class="text-xs font-bold text-muted-foreground uppercase mb-1">Criado em</p>
                                    <p class="text-sm font-medium text-foreground">{{ formatDate(lancamento.created_at) }}</p>
                                </div>
                            </div>

                            <div v-if="lancamento.updated_at !== lancamento.created_at" class="flex items-start gap-4 relative">
                                <div class="h-4 w-4 rounded-full bg-border border-4 border-card z-10 mt-0.5 shadow-sm"></div>
                                <div>
                                    <p class="text-xs font-bold text-muted-foreground uppercase mb-1">Última Alteração</p>
                                    <p class="text-sm font-medium text-foreground">{{ formatDate(lancamento.updated_at) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Dica Visual -->
                    <div class="rounded-xl bg-primary/5 border border-primary/10 p-6">
                        <div class="flex items-center gap-3 text-primary mb-3">
                            <DollarSign class="h-5 w-5" />
                            <p class="text-sm font-bold">Gestão Financeira</p>
                        </div>
                        <p class="text-xs text-muted-foreground leading-[1.6]">Este lançamento impacta diretamente o saldo da unidade <strong>{{ lancamento.unidade?.nome || 'Geral' }}</strong>. Certifique-se de que o comprovante está legível para fins de prestação de contas.</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
