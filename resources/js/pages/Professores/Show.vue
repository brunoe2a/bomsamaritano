<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Pencil, Phone, Mail } from 'lucide-vue-next';
import { DevicePhoneMobileIcon, BookOpenIcon } from '@heroicons/vue/24/outline';
import AppLayout from '@/layouts/AppLayout.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import type { Professor, Turma, BreadcrumbItem } from '@/types';

const props = defineProps<{
    professor: Professor & { turmas?: Turma[] };
    aulasMes: number;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Professores', href: '/professores' },
    { title: props.professor.nome, href: `/professores/${props.professor.id}` },
];
</script>

<template>
    <Head :title="professor.nome" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full p-4 md:p-6">
            <div class="mb-6 flex items-center justify-between">
                <div class="flex flex-col gap-4 md:flex-row md:items-center">
                    <Link href="/professores" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-border hover:bg-muted"><ArrowLeft class="h-4 w-4" /></Link>
                    
                    <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-full border-2 border-primary/20 bg-primary/10 text-xl font-bold text-primary">
                        <img v-if="professor.foto" :src="professor.foto_url" :alt="professor.nome" class="h-full w-full object-cover" />
                        <span v-else>{{ professor?.nome?.charAt(0) || '' }}</span>
                    </div>

                    <div>
                        <h1 class="text-2xl font-bold text-foreground">{{ professor.nome }}</h1>
                        <div class="mt-1 flex gap-2">
                            <StatusBadge :status="professor.status" size="sm" />
                            <StatusBadge :status="professor.tipo_vinculo" size="sm" />
                        </div>
                    </div>
                </div>
                <Link :href="`/professores/${professor.id}/edit`" class="inline-flex items-center gap-2 rounded-lg border border-border px-3 py-2 text-sm font-medium hover:bg-muted">
                    <Pencil class="h-4 w-4" /> Editar
                </Link>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <!-- Info -->
                <div class="rounded-xl border border-border bg-card p-6 shadow-sm">
                    <h3 class="mb-4 text-sm font-semibold text-muted-foreground">INFORMAÇÕES</h3>
                    <div class="space-y-3 text-sm">
                        <div v-if="professor.email" class="flex items-center gap-2 text-muted-foreground"><Mail class="h-4 w-4" /> {{ professor.email }}</div>
                        <div v-if="professor.telefone" class="flex items-center gap-2 text-muted-foreground"><Phone class="h-4 w-4" /> {{ professor.telefone }}</div>
                        <div v-if="professor.whatsapp" class="flex items-center gap-2 text-muted-foreground"><DevicePhoneMobileIcon class="h-4 w-4" /> {{ professor.whatsapp }}</div>
                        <div v-if="professor.cpf" class="text-muted-foreground">CPF: {{ professor.cpf }}</div>
                        <div v-if="professor.data_inicio" class="text-muted-foreground">Início: {{ new Date(professor.data_inicio).toLocaleDateString('pt-BR') }}</div>
                        <div class="mt-3">
                            <p class="mb-1 text-xs font-medium text-muted-foreground">Unidades de Atuação</p>
                            <div class="flex flex-wrap gap-1">
                                <span v-for="u in (professor.unidades || [])" :key="u.id" class="rounded-full bg-secondary px-2 py-0.5 text-xs font-medium text-secondary-foreground">{{ u.nome }}</span>
                            </div>
                        </div>
                        <div class="mt-3">
                            <p class="mb-1 text-xs font-medium text-muted-foreground">Especialidades</p>
                            <div class="flex flex-wrap gap-1">
                                <span v-for="e in (professor.especialidades || [])" :key="e.id" class="rounded-full bg-primary/10 px-2 py-0.5 text-xs font-medium text-primary">{{ e.nome }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stats -->
                <div class="space-y-4">
                    <div class="rounded-xl border border-border bg-card p-6 shadow-sm">
                        <p class="text-sm text-muted-foreground">Aulas este mês</p>
                        <p class="text-3xl font-bold text-primary">{{ aulasMes }}</p>
                    </div>
                    <div class="rounded-xl border border-border bg-card p-6 shadow-sm">
                        <p class="text-sm text-muted-foreground">Turmas ativas</p>
                        <p class="text-3xl font-bold text-foreground">{{ professor.turmas?.length || 0 }}</p>
                    </div>
                </div>

                <!-- Turmas -->
                <div class="rounded-xl border border-border bg-card p-6 shadow-sm md:col-span-2">
                    <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold text-foreground"><BookOpenIcon class="size-5" /> Turmas</h3>
                    <div v-if="professor.turmas?.length" class="space-y-2">
                        <Link v-for="t in professor.turmas" :key="t.id" :href="`/turmas/${t.id}`" class="flex items-center justify-between rounded-lg bg-muted/30 p-3 hover:bg-muted/50">
                            <div>
                                <p class="font-medium text-foreground">{{ t.nome }}</p>
                                <p class="text-xs text-muted-foreground">{{ t.curso?.nome }} · {{ t.horario_inicio }} - {{ t.horario_fim }}</p>
                            </div>
                            <StatusBadge :status="t.status" size="sm" />
                        </Link>
                    </div>
                    <p v-else class="text-sm text-muted-foreground">Nenhuma turma atribuída.</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
