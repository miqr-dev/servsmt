<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import { startUnreadPolling, titleState, useUnread, withUnreadPrefix } from '@/composables/useUnread';
import {
    Boxes,
    Briefcase,
    Building2,
    ClipboardCheck,
    ClipboardList,
    Contact,
    FileText,
    FolderKanban,
    HardHat,
    LayoutDashboard,
    ListChecks,
    LayoutGrid,
    Settings,
    ShieldCheck,
    Ticket as TicketIcon,
    Users,
    UserMinus,
    UsersRound,
} from '@lucide/vue';
import AppLogo from '@/components/AppLogo.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import type { NavItem } from '@/types';

// Nav structure ported from the old resources/views/layouts/admin_layout/admin_sidebar.blade.php.
// Reorganized into labeled groups and flattened to at most 2 levels (the old "Übungsfirmen" and
// "Ticket" menus went 3 levels deep); dead placeholder links (Suhl, "Kürzlich aktualisiert") and
// the one-off "or user id == 39" exception on Dokumente were dropped. Role names match Spatie
// roles exactly as used server-side - this only hides links client-side for a cleaner menu, the
// actual authorization still lives in the route middleware/policies, same as before.
//
// 2026-09-17: per request, removed the "Nichtzugewiesen" (unassignedtickets) and "Standort
// Tickets" (special-tickets) nav links - both routes/pages still exist server-side, just not
// linked from here anymore (a simple filter on the open-tickets table is planned to replace
// both later). "Ticket Erstellen" was pulled out of the "Ticket" group, renamed "IT Ticket",
// and made a standalone top-level item directly above "Korso Ticket".

const baseGeneralItems: NavItem[] = [
    // "/" is the unified Dashboard for everyone (DashboardController); every
    // role's boxes (incl. HR Kündigungen, Super_Admin Lizenzen) live there.
    { title: 'Dashboard', href: '/', icon: LayoutGrid },
    // Roles below match what the server allows (2026-09-28): Verwaltung - the
    // default role of every employee - no longer sees these three.
    { title: 'Evaluationen', href: '/umfrages', icon: ClipboardList, roles: ['Super_Admin'] },
    { title: 'Dokumente', href: '/documents', icon: FileText, roles: ['Super_Admin'] },
    { title: 'Einstellungen', href: '/settings', icon: Settings, roles: ['Super_Admin'] },
    { title: 'Inventar', href: '/inventory', icon: Boxes, roles: ['Super_Admin', 'INV'] },
    { title: 'MIQR Mitarbeiter', href: '/contacts', icon: Contact, roles: ['admin', 'Super_Admin'] },
];

// Unread notifications (all systems): counter on "Dashboard", where the
// "Neu für dich" box lists them; "(n)" prefix on the browser tab title.
const unread = useUnread();
startUnreadPolling();
const generalItems = computed<NavItem[]>(() => baseGeneralItems.map((item) => (item.href === '/' ? { ...item, badge: unread.count.value } : item)));
watch(
    unread.count,
    (n) => {
        titleState.count = n;
        document.title = withUnreadPrefix(document.title);
    },
    { immediate: true },
);

// Two layouts (2026-09-28, your request):
// - IT admins (Super_Admin, admin): the "Ticket" group with Offen / Erledigt /
//   Meine Tickets, plus "Handwerkaufgaben".
// - Everyone else: flat "Meine Tickets", "IT Ticket", "Korso Ticket",
//   "Handwerk Ticket" - nothing hidden in a sub-menu.
// Super_Admin counts as having every role (App\User::hasRole), so
// hideForRoles: ['Super_Admin', 'admin'] always hides the flat variants for them.
const IT_ADMIN_ROLES = ['Super_Admin', 'admin'];

const ticketItems: NavItem[] = [
    {
        title: 'Ticket',
        icon: TicketIcon,
        roles: IT_ADMIN_ROLES,
        children: [
            { title: 'Offen', href: '/opentickets' },
            { title: 'Erledigt', href: '/tickethistory' },
            { title: 'Meine Tickets', href: '/usertickets' },
        ],
    },
    { title: 'Meine Tickets', href: '/usertickets', icon: ListChecks, hideForRoles: IT_ADMIN_ROLES },
    { title: 'IT Ticket', href: '/ticket.index', icon: TicketIcon },
    { title: 'Korso Ticket', href: '/korso', icon: Users },
    // The Korso ticket queue. The old app reached it via a "K" icon in the top
    // header bar (admin_header.blade.php), which converted pages don't have -
    // so it's a sidebar entry now, for the same roles the old icon used.
    {
        title: 'Korso Dashboard',
        href: '/korso-dashboard',
        icon: LayoutDashboard,
        roles: ['Korso_ma', 'Korso_Admin', 'Super_Admin'],
    },
    {
        title: 'Handwerkaufgaben',
        href: '/handwerk',
        icon: HardHat,
        roles: IT_ADMIN_ROLES,
    },
    {
        title: 'Handwerk Ticket',
        href: '/handwerk',
        icon: HardHat,
        roles: ['Verwaltung', 'handwerk_admin', 'handwerk'],
        hideForRoles: IT_ADMIN_ROLES,
    },
];

const practiceCompanyItems: NavItem[] = [
    {
        title: 'Übungsfirmen',
        icon: Building2,
        roles: ['Terminal', 'Super_Admin'],
        children: [
            { title: 'Berlin', href: '/practice-companies/city/Berlin' },
            { title: 'Chemnitz – Profil & E-Mail', href: '/practice-companies/city/Chemnitz' },
            { title: 'Chemnitz – Lexware', href: '/practice-companies/lex/Chemnitz' },
            { title: 'Dresden – Profil & E-Mail', href: '/practice-companies/city/Dresden' },
            { title: 'Dresden – Lexware', href: '/practice-companies/lex/Dresden' },
            { title: 'Erfurt – Profil & E-Mail', href: '/practice-companies/city/Erfurt' },
            { title: 'Erfurt – Lexware', href: '/practice-companies/lex/Erfurt' },
            { title: 'Leipzig – Profil & E-Mail', href: '/practice-companies/city/Leipzig' },
            { title: 'Leipzig – Lexware', href: '/practice-companies/lex/Leipzig' },
        ],
    },
];

const adminItems: NavItem[] = [
    { title: 'Bedarf', href: '/tasks', icon: ClipboardCheck, roles: ['Super_Admin'] },
    { title: 'Project', href: '/projects', icon: FolderKanban, roles: ['Super_Admin'] },
    {
        title: 'Rollen & Berechtigungen',
        icon: ShieldCheck,
        roles: ['Super_Admin'],
        children: [
            { title: 'Rollen', href: '/roles' },
            { title: 'Berechtigungen', href: '/permissions' },
            { title: 'Benutzer', href: '/users' },
        ],
    },
    { title: 'Teilnehmer Liste', href: '/participants', icon: UsersRound, roles: ['Super_Admin', 'Teilnehmer_Info', 'Sekretariat'] },
    { title: 'Mitarbeiter Liste', href: '/employees', icon: Briefcase, roles: ['Super_Admin', 'HR'] },
    { title: 'Kündigungen', href: '/terminations', icon: UserMinus, roles: ['Super_Admin', 'HR'] },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link href="/">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="generalItems" />
            <NavMain :items="ticketItems" label="Tickets" />
            <NavMain :items="practiceCompanyItems" />
            <NavMain :items="adminItems" label="Verwaltung" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
