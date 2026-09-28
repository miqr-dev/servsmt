import type { InertiaLinkProps } from '@inertiajs/vue3';
import type { LucideIcon } from '@lucide/vue';

export type BreadcrumbItem = {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
};

export type NavItem = {
    title: string;
    href?: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon;
    isActive?: boolean;
    /** Only shown to users who have at least one of these roles. Omit to show to everyone. */
    roles?: string[];
    /** Hidden from users who have any of these roles (e.g. a simplified item that admins get in a richer form elsewhere). */
    hideForRoles?: string[];
    /** Nested links, rendered as a collapsible sub-menu. */
    children?: NavItem[];
};
