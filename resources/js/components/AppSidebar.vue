<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
    BookOpen,
    CalendarClock,
    DollarSign,
    HandHeart,
    HeartPulse,
    LayoutGrid,
    MessageCircle,
    School,
    UserCheck,
    Users,
    UsersRound,
    ShieldCheck,
    Building2,
} from 'lucide-vue-next';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import type { NavItem } from '@/types';

const page = usePage();
const userPermissions = computed(() => (page.props.auth as any)?.user?.permissions || []);

function can(permission: string): boolean {
    return userPermissions.value.includes(permission);
}

const allNavItems: (NavItem & { permission?: string | 'admin_only' })[] = [
    { title: 'Dashboard', href: '/dashboard', icon: LayoutGrid },
    { title: 'Alunos', href: '/alunos', icon: Users, permission: 'alunos.listar' },
    { title: 'Responsáveis', href: '/responsaveis', icon: UsersRound, permission: 'responsaveis.listar' },
    { title: 'Cursos', href: '/cursos', icon: BookOpen, permission: 'cursos.listar' },
    { title: 'Turmas', href: '/turmas', icon: School, permission: 'turmas.listar' },
    { title: 'Professores', href: '/professores', icon: UserCheck, permission: 'professores.listar' },
    { title: 'Voluntários', href: '/voluntarios', icon: HandHeart, permission: 'voluntarios.listar' },
    { title: 'Expediente', href: '/expedientes', icon: CalendarClock, permission: 'expedientes.listar' },
    { title: 'Núcleo de Saúde', href: '/saude', icon: HeartPulse, permission: 'saude.listar' },
    { title: 'Financeiro', href: '/financeiro', icon: DollarSign, permission: 'financeiro.listar' },
    { title: 'Unidades', href: '/unidades', icon: Building2, permission: 'unidades.listar' },
    { title: 'WhatsApp', href: '/whatsapp/notificacoes', icon: MessageCircle, permission: 'whatsapp.listar' },
    { title: 'Usuários', href: '/usuarios', icon: ShieldCheck, permission: 'admin_only' },
];

const mainNavItems = computed(() =>
    allNavItems.filter(item => {
        if (!item.permission) return true;
        if (item.permission === 'admin_only') {
             const userRoleList = (page.props.auth as any)?.user?.roles || [];
             return userRoleList.includes('admin');
        }
        return can(item.permission);
    })
);

const footerNavItems: NavItem[] = [];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link href="/dashboard">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter v-if="footerNavItems.length" :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
