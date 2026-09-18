<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
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
    HelpCircle,
    LayoutGrid,
    Settings,
    ShieldCheck,
    Ticket as TicketIcon,
    Users,
    UsersRound,
} from '@lucide/vue';
import AppLogo from '@/components/AppLogo.vue';
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

const generalItems: NavItem[] = [
    { title: 'Dashboard', href: '/dashboard', icon: LayoutGrid },
    { title: 'Evaluationen', href: '/umfrages', icon: ClipboardList },
    { title: 'Dokumente', href: '/documents', icon: FileText, roles: ['Super_Admin'] },
    { title: 'Einstellungen', href: '/settings', icon: Settings },
    { title: 'Inventar', href: '/inventory', icon: Boxes },
    { title: 'MIQR Mitarbeiter', href: '/contacts', icon: Contact, roles: ['admin', 'Super_Admin'] },
    { title: 'Hilfe', href: '/video', icon: HelpCircle },
];

const ticketItems: NavItem[] = [
    {
        title: 'Ticket',
        icon: TicketIcon,
        children: [
            { title: 'Offen', href: '/opentickets', roles: ['admin', 'Super_Admin'] },
            { title: 'Erledigt', href: '/tickethistory', roles: ['admin', 'Super_Admin'] },
            { title: 'Meine Tickets', href: '/usertickets' },
        ],
    },
    { title: 'IT Ticket', href: '/ticket.index', icon: TicketIcon },
    { title: 'Korso Ticket', href: '/korso', icon: Users },
    {
        title: 'Handwerkaufgaben',
        href: '/handwerk',
        icon: HardHat,
        roles: ['Super_Admin', 'Verwaltung', 'handwerk_admin', 'handwerk'],
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
    { title: 'Teilnehmer Liste', href: '/participants', icon: UsersRound, roles: ['Super_Admin', 'Verwaltung'] },
    { title: 'Mitarbeiter Liste', href: '/employees', icon: Briefcase, roles: ['Super_Admin', 'HR'] },
];
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
