<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Heart, HandHeart, Cross } from 'lucide-vue-next';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();

const ano = new Date().getFullYear();
</script>

<template>
    <div class="relative grid min-h-svh w-full lg:grid-cols-[1.05fr_1fr]">
        <!-- Painel esquerdo: identidade visual da marca -->
        <aside
            class="relative hidden overflow-hidden bg-zinc-950 text-amber-50 lg:flex lg:flex-col"
        >
            <!-- Gradiente principal -->
            <div
                class="absolute inset-0 bg-[radial-gradient(circle_at_15%_20%,hsl(38_91%_55%/0.35),transparent_55%),radial-gradient(circle_at_85%_85%,hsl(38_91%_45%/0.25),transparent_50%),linear-gradient(160deg,hsl(0_0%_8%)_0%,hsl(0_0%_5%)_60%,hsl(30_30%_8%)_100%)]"
            />

            <!-- Grade decorativa -->
            <div
                class="absolute inset-0 opacity-[0.08] [background-image:linear-gradient(to_right,white_1px,transparent_1px),linear-gradient(to_bottom,white_1px,transparent_1px)] [background-size:48px_48px]"
            />

            <!-- Halo dourado -->
            <div
                class="absolute -top-40 -left-40 h-[28rem] w-[28rem] rounded-full bg-amber-400/15 blur-3xl"
            />
            <div
                class="absolute -right-32 bottom-0 h-[24rem] w-[24rem] rounded-full bg-amber-500/10 blur-3xl"
            />

            <!-- Conteúdo -->
            <div class="relative z-10 flex h-full flex-col justify-between p-12 xl:p-16">
                <!-- Logo + nome -->
                <Link
                    :href="home()"
                    class="group flex items-center gap-3 self-start transition-transform hover:scale-[1.02]"
                >
                    <div
                        class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white/95 p-2 shadow-[0_0_30px_-5px_hsl(38_91%_55%/0.5)] ring-1 ring-amber-300/30"
                    >
                        <img
                            src="/favicon.svg"
                            alt="Bom Samaritano"
                            class="h-full w-full object-contain"
                        />
                    </div>
                    <div class="leading-tight">
                        <p class="text-base font-semibold tracking-tight text-white">
                            Bom Samaritano
                        </p>
                        <p class="text-xs text-amber-200/70">
                            Gestão Educacional
                        </p>
                    </div>
                </Link>

                <!-- Mensagem central -->
                <div class="max-w-md space-y-6">
                    <span
                        class="inline-flex items-center gap-2 rounded-full border border-amber-400/30 bg-amber-400/10 px-3 py-1 text-xs font-medium text-amber-200"
                    >
                        <Cross class="size-3.5" />
                        Ação Social — Igreja IBPaz
                    </span>
                    <h2
                        class="text-4xl font-semibold leading-tight tracking-tight text-white xl:text-[2.75rem]"
                    >
                        Cuidando de crianças,
                        <span class="text-amber-300"
                            >mostrando o caminho em Cristo.</span
                        >
                    </h2>
                    <p class="text-base leading-relaxed text-amber-100/70">
                        Projeto social da Igreja IBPaz dedicado a acolher,
                        ensinar e transformar a vida de crianças e famílias —
                        levando o amor de Jesus em cada passo do caminho.
                    </p>

                    <ul class="space-y-3 pt-2 text-sm">
                        <li class="flex items-start gap-3 text-amber-100/85">
                            <HandHeart
                                class="mt-0.5 size-4 shrink-0 text-amber-300"
                            />
                            <span
                                >Acolhimento e cuidado integral às crianças
                                atendidas</span
                            >
                        </li>
                        <li class="flex items-start gap-3 text-amber-100/85">
                            <Heart
                                class="mt-0.5 size-4 shrink-0 text-amber-300"
                            />
                            <span
                                >Formação cristã guiando cada criança no
                                caminho de Jesus</span
                            >
                        </li>
                    </ul>
                </div>

                <!-- Rodapé do painel -->
                <div
                    class="flex items-center justify-between text-xs text-amber-100/50"
                >
                    <span>© {{ ano }} Bom Samaritano · IBPaz</span>
                    <span>"Deixai vir a mim os pequeninos" — Mc 10.14</span>
                </div>
            </div>
        </aside>

        <!-- Painel direito: formulário -->
        <main
            class="relative flex min-h-svh flex-col bg-background px-6 py-8 sm:px-10 md:px-12"
        >
            <!-- Topo mobile com logo -->
            <Link
                :href="home()"
                class="flex items-center gap-2 self-start lg:hidden"
            >
                <img
                    src="/favicon.svg"
                    alt="Bom Samaritano"
                    class="h-10 w-10 object-contain"
                />
                <div class="leading-tight">
                    <p class="text-sm font-semibold">Bom Samaritano</p>
                    <p class="text-xs text-muted-foreground">
                        Gestão Educacional
                    </p>
                </div>
            </Link>

            <div class="flex flex-1 items-center justify-center">
                <div class="w-full max-w-sm space-y-8">
                    <div class="space-y-2">
                        <h1
                            class="text-2xl font-semibold tracking-tight text-foreground sm:text-[1.75rem]"
                            v-if="title"
                        >
                            {{ title }}
                        </h1>
                        <p
                            class="text-sm leading-relaxed text-muted-foreground"
                            v-if="description"
                        >
                            {{ description }}
                        </p>
                    </div>

                    <slot />
                </div>
            </div>

            <p
                class="mt-8 text-center text-xs text-muted-foreground lg:hidden"
            >
                © {{ ano }} Bom Samaritano · Gestão Educacional
            </p>
        </main>
    </div>
</template>
