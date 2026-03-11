<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Pencil, Phone, Mail } from 'lucide-vue-next';
import { DevicePhoneMobileIcon } from '@heroicons/vue/24/outline';
import AppLayout from '@/layouts/AppLayout.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import type { Voluntario, BreadcrumbItem } from '@/types';

const props = defineProps<{ voluntario: Voluntario & { telefone?: string; whatsapp?: string; email?: string; cpf?: string; data_inicio?: string; habilidades?: string; area_atuacao?: string } }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Voluntários', href: '/voluntarios' },
    { title: props.voluntario.nome, href: `/voluntarios/${props.voluntario.id}` },
];
</script>

<template>
    <Head :title="voluntario.nome" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full p-4 md:p-6">
            <div class="mb-6 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link href="/voluntarios" class="flex h-9 w-9 items-center justify-center rounded-lg border border-border hover:bg-muted"><ArrowLeft class="h-4 w-4" /></Link>
                    
                    <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-full border-2 border-primary/20 bg-primary/10 text-xl font-bold text-primary">
                        <img v-if="voluntario.foto" :src="`/storage/${voluntario.foto}`" :alt="voluntario.nome" class="h-full w-full object-cover" />
                        <span v-else>{{ voluntario?.nome?.charAt(0) || '' }}</span>
                    </div>

                    <div>
                        <h1 class="text-2xl font-bold text-foreground">{{ voluntario.nome }}</h1>
                        <div class="mt-1 flex gap-2">
                            <StatusBadge :status="voluntario.status" size="sm" />
                            <span v-if="voluntario.area_atuacao" class="rounded-full bg-primary/10 px-2 py-0.5 text-xs font-medium text-primary">{{ voluntario.area_atuacao }}</span>
                        </div>
                    </div>
                </div>
                <Link :href="`/voluntarios/${voluntario.id}/edit`" class="inline-flex items-center gap-2 rounded-lg border border-border px-3 py-2 text-sm font-medium hover:bg-muted">
                    <Pencil class="h-4 w-4" /> Editar
                </Link>
            </div>

            <div class="rounded-xl border border-border bg-card p-6 shadow-sm">
                <h3 class="mb-4 text-sm font-semibold text-muted-foreground">INFORMAÇÕES</h3>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div v-if="voluntario.email" class="flex items-center gap-2 text-sm text-muted-foreground"><Mail class="h-4 w-4" /> {{ voluntario.email }}</div>
                    <div v-if="voluntario.telefone" class="flex items-center gap-2 text-sm text-muted-foreground"><Phone class="h-4 w-4" /> {{ voluntario.telefone }}</div>
                    <div v-if="voluntario.whatsapp" class="flex items-center gap-2 text-sm text-muted-foreground"><DevicePhoneMobileIcon class="h-4 w-4" /> {{ voluntario.whatsapp }}</div>
                    <div v-if="voluntario.cpf" class="text-sm text-muted-foreground">CPF: {{ voluntario.cpf }}</div>
                    <div v-if="voluntario.data_inicio" class="text-sm text-muted-foreground">Início: {{ new Date(voluntario.data_inicio).toLocaleDateString('pt-BR') }}</div>
                </div>
                <div v-if="voluntario.habilidades" class="mt-4">
                    <p class="text-xs font-medium text-muted-foreground">Habilidades / Observações</p>
                    <p class="mt-1 whitespace-pre-wrap text-sm text-foreground">{{ voluntario.habilidades }}</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
