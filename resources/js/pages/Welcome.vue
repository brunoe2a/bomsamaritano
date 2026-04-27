<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { dashboard, login } from '@/routes'

withDefaults(
    defineProps<{
        canRegister: boolean
    }>(),
    {
        canRegister: true,
    },
)

const stats = [
    { value: '500+', label: 'Alunos atendidos' },
    { value: '30+', label: 'Voluntários ativos' },
    { value: '12', label: 'Turmas em andamento' },
    { value: '5', label: 'Anos de história' },
]

const features = [
    {
        title: 'Gestão de Alunos',
        description: 'Cadastro completo de alunos e responsáveis, histórico de frequência e acompanhamento pedagógico.',
        icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
        color: 'from-emerald-500 to-emerald-600',
    },
    {
        title: 'Turmas e Chamadas',
        description: 'Organização de turmas por curso e unidade, lista de presença com um clique e relatórios de assiduidade.',
        icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
        color: 'from-sky-500 to-sky-600',
    },
    {
        title: 'Professores e Voluntários',
        description: 'Controle de equipe, distribuição de turmas e registro das horas doadas por cada voluntário.',
        icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
        color: 'from-purple-500 to-purple-600',
    },
    {
        title: 'Doadores e Campanhas',
        description: 'Registro de doadores recorrentes e pontuais, emissão de recibos e acompanhamento de campanhas.',
        icon: 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z',
        color: 'from-rose-500 to-rose-600',
    },
    {
        title: 'Financeiro',
        description: 'Contas a pagar e receber, fluxo de caixa, conciliação bancária e relatórios para prestação de contas.',
        icon: 'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z',
        color: 'from-amber-500 to-amber-600',
    },
    {
        title: 'Multi-Unidade',
        description: 'Atenda várias unidades com dados segmentados, mas com visão consolidada para a direção geral.',
        icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16M9 7h6m-6 4h6m-6 4h6M5 21h14',
        color: 'from-teal-500 to-teal-600',
    },
]
</script>

<template>
    <Head title="Bom Samaritano — Transformando vidas">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <div class="min-h-screen bg-gradient-to-b from-white via-emerald-50/40 to-white text-slate-900 antialiased">
        <!-- Nav -->
        <header class="sticky top-0 z-40 backdrop-blur bg-white/80 border-b border-slate-200/70">
            <div class="max-w-6xl mx-auto px-4 lg:px-6 h-16 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-sm">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 010-5 2.5 2.5 0 010 5z"/>
                        </svg>
                    </div>
                    <div class="leading-tight">
                        <p class="font-bold text-slate-900">Bom Samaritano</p>
                        <p class="text-[10px] font-medium text-slate-500 uppercase tracking-widest">Instituição Social</p>
                    </div>
                </div>

                <nav class="flex items-center gap-2 text-sm">
                    <Link
                        v-if="$page.props.auth?.user"
                        :href="dashboard()"
                        class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-medium transition shadow-sm"
                    >
                        Painel
                    </Link>
                    <template v-else>
                        <Link
                            :href="login()"
                            class="px-4 py-2 rounded-lg border border-slate-200 hover:border-emerald-400 hover:text-emerald-700 text-slate-700 font-medium transition"
                        >
                            Entrar
                        </Link>
                        <a
                            href="#doar"
                            class="px-4 py-2 rounded-lg bg-gradient-to-r from-emerald-500 to-teal-600 hover:opacity-95 text-white font-medium transition shadow-sm flex items-center gap-1.5"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                            Doar
                        </a>
                    </template>
                </nav>
            </div>
        </header>

        <!-- Hero -->
        <section class="relative overflow-hidden">
            <div class="absolute inset-0 -z-10">
                <div class="absolute -top-24 -right-24 w-96 h-96 rounded-full bg-emerald-300/30 blur-3xl"></div>
                <div class="absolute top-1/2 -left-32 w-96 h-96 rounded-full bg-teal-300/30 blur-3xl"></div>
            </div>

            <div class="max-w-6xl mx-auto px-4 lg:px-6 pt-16 pb-20 lg:pt-24 lg:pb-28 grid lg:grid-cols-2 gap-12 items-center">
                <div class="space-y-6">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-semibold tracking-wide">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        Transformando vidas desde 2019
                    </span>

                    <h1 class="text-4xl lg:text-5xl xl:text-6xl font-extrabold tracking-tight leading-[1.05]">
                        Educação, cuidado e
                        <span class="bg-gradient-to-r from-emerald-600 to-teal-600 bg-clip-text text-transparent">oportunidades</span>
                        para quem mais precisa.
                    </h1>

                    <p class="text-lg text-slate-600 leading-relaxed max-w-xl">
                        Somos uma instituição social que oferece cursos, acompanhamento pedagógico e apoio integral a crianças e adolescentes em situação de vulnerabilidade.
                    </p>

                    <div class="flex flex-wrap gap-3 pt-2">
                        <a href="#doar" class="inline-flex items-center gap-2 px-6 py-3 rounded-lg bg-gradient-to-r from-emerald-500 to-teal-600 hover:opacity-95 text-white font-semibold shadow-md transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                            Quero Doar
                        </a>
                        <a href="#sobre" class="inline-flex items-center gap-2 px-6 py-3 rounded-lg border border-slate-300 hover:border-emerald-400 hover:text-emerald-700 text-slate-700 font-semibold transition">
                            Conheça o projeto
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                        </a>
                    </div>
                </div>

                <div class="relative">
                    <div class="aspect-[4/5] rounded-3xl bg-gradient-to-br from-emerald-400 via-teal-500 to-sky-500 p-1 shadow-2xl shadow-emerald-500/20">
                        <div class="w-full h-full rounded-[1.4rem] bg-white flex items-center justify-center relative overflow-hidden">
                            <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 20% 20%, #10b981 0%, transparent 50%), radial-gradient(circle at 80% 80%, #0ea5e9 0%, transparent 50%);"></div>
                            <svg class="w-40 h-40 text-emerald-600/80" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm-2 5h1.5V8h1.5V5.5H13V4h1.5v1.5H16V7h-1.5v1.5H13V7h-1.5v1.5H10V7z"/>
                            </svg>
                        </div>
                    </div>

                    <div class="absolute -left-6 top-16 bg-white rounded-xl shadow-lg px-4 py-3 flex items-center gap-3 border border-slate-100">
                        <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197"/></svg>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500">Atendendo</p>
                            <p class="text-sm font-bold text-slate-900">500+ alunos</p>
                        </div>
                    </div>

                    <div class="absolute -right-4 bottom-20 bg-white rounded-xl shadow-lg px-4 py-3 flex items-center gap-3 border border-slate-100">
                        <div class="w-10 h-10 rounded-full bg-rose-100 flex items-center justify-center text-rose-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500">Voluntários</p>
                            <p class="text-sm font-bold text-slate-900">30+ ativos</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Stats -->
        <section class="border-y border-slate-200 bg-white/60">
            <div class="max-w-6xl mx-auto px-4 lg:px-6 py-10 grid grid-cols-2 lg:grid-cols-4 gap-6">
                <div v-for="s in stats" :key="s.label" class="text-center">
                    <p class="text-3xl lg:text-4xl font-extrabold bg-gradient-to-r from-emerald-600 to-teal-600 bg-clip-text text-transparent">{{ s.value }}</p>
                    <p class="text-xs font-semibold uppercase tracking-widest text-slate-500 mt-1">{{ s.label }}</p>
                </div>
            </div>
        </section>

        <!-- Sobre -->
        <section id="sobre" class="py-20 lg:py-28">
            <div class="max-w-6xl mx-auto px-4 lg:px-6 text-center max-w-3xl">
                <span class="text-xs font-bold uppercase tracking-[0.3em] text-emerald-600">Nossa missão</span>
                <h2 class="mt-3 text-3xl lg:text-4xl font-bold tracking-tight">Amar o próximo em ação.</h2>
                <p class="mt-5 text-lg text-slate-600 leading-relaxed">
                    Inspirados na parábola do Bom Samaritano, acreditamos que amar o próximo é agir. Por isso, criamos um espaço seguro onde crianças e adolescentes encontram estudo, alimentação, acolhimento e oportunidades para sonhar e construir seu futuro.
                </p>
            </div>
        </section>

        <!-- Features -->
        <section class="pb-20 lg:pb-28">
            <div class="max-w-6xl mx-auto px-4 lg:px-6">
                <div class="text-center mb-12">
                    <span class="text-xs font-bold uppercase tracking-[0.3em] text-emerald-600">Sistema de gestão</span>
                    <h2 class="mt-3 text-3xl lg:text-4xl font-bold tracking-tight">Tudo o que a instituição precisa, em um só lugar.</h2>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-5">
                    <div v-for="f in features" :key="f.title" class="group p-6 rounded-2xl bg-white border border-slate-200 hover:border-emerald-300 hover:shadow-lg hover:shadow-emerald-500/5 transition">
                        <div :class="`w-12 h-12 rounded-xl bg-gradient-to-br ${f.color} flex items-center justify-center shadow-sm mb-4 group-hover:scale-110 transition`">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="f.icon"/></svg>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 mb-2">{{ f.title }}</h3>
                        <p class="text-sm text-slate-600 leading-relaxed">{{ f.description }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Doar CTA -->
        <section id="doar" class="py-16 lg:py-20">
            <div class="max-w-5xl mx-auto px-4 lg:px-6">
                <div class="relative rounded-3xl overflow-hidden bg-gradient-to-br from-emerald-600 via-teal-600 to-sky-600 p-8 lg:p-14 text-white shadow-xl shadow-emerald-500/20">
                    <div class="absolute inset-0 opacity-20">
                        <svg class="absolute -top-10 -right-10 w-80 h-80" fill="currentColor" viewBox="0 0 24 24"><path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                    </div>

                    <div class="relative grid lg:grid-cols-2 gap-8 items-center">
                        <div>
                            <h2 class="text-3xl lg:text-4xl font-bold tracking-tight">Sua ajuda transforma realidades.</h2>
                            <p class="mt-4 text-emerald-50 text-lg leading-relaxed">
                                Cada contribuição custeia material escolar, alimentação, uniforme e o funcionamento das unidades. Você pode doar uma vez ou tornar-se um padrinho mensal.
                            </p>
                        </div>

                        <div class="space-y-3">
                            <div class="rounded-xl bg-white/10 backdrop-blur p-4 border border-white/20">
                                <p class="text-xs uppercase tracking-widest text-emerald-100 font-semibold">PIX (CNPJ)</p>
                                <p class="mt-1 font-mono text-lg font-bold tracking-wider">00.000.000/0001-00</p>
                            </div>
                            <div class="rounded-xl bg-white/10 backdrop-blur p-4 border border-white/20">
                                <p class="text-xs uppercase tracking-widest text-emerald-100 font-semibold">Conta para depósito</p>
                                <p class="mt-1 text-sm">Banco 000 · Ag. 0000 · CC 00000-0</p>
                            </div>
                            <a href="#" class="block text-center mt-4 px-6 py-3 rounded-lg bg-white text-emerald-700 font-bold hover:bg-emerald-50 transition shadow-md">
                                Quero ser Padrinho(a)
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="border-t border-slate-200 bg-white py-10">
            <div class="max-w-6xl mx-auto px-4 lg:px-6 flex flex-col md:flex-row items-center justify-between gap-4 text-sm text-slate-500">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/></svg>
                    </div>
                    <span class="font-semibold text-slate-700">Bom Samaritano</span>
                </div>
                <p>© {{ new Date().getFullYear() }} Bom Samaritano — Todos os direitos reservados.</p>
                <div class="flex items-center gap-4">
                    <a href="#" class="hover:text-emerald-600 transition">Instagram</a>
                    <a href="#" class="hover:text-emerald-600 transition">Contato</a>
                </div>
            </div>
        </footer>
    </div>
</template>
