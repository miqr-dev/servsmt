<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { ChevronRight } from '@lucide/vue';
import { computed } from 'vue';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarMenuSub,
    SidebarMenuSubButton,
    SidebarMenuSubItem,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import type { NavItem } from '@/types';

const props = defineProps<{
    items: NavItem[];
    label?: string;
}>();

const { isCurrentUrl, isCurrentOrParentUrl } = useCurrentUrl();

const page = usePage();
const userRoles = computed<string[]>(
    () => (page.props.auth?.user?.roles as string[] | undefined) ?? [],
);

function isVisible(item: NavItem): boolean {
    if (item.hideForRoles?.some((role) => userRoles.value.includes(role))) {
        return false;
    }
    if (!item.roles || item.roles.length === 0) {
        return true;
    }

    return item.roles.some((role) => userRoles.value.includes(role));
}

// A group item with children is only shown if at least one child is visible.
const visibleItems = computed(() =>
    props.items.filter((item) => isVisible(item) && (!item.children?.length || visibleChildren(item).length > 0)),
);

function visibleChildren(item: NavItem): NavItem[] {
    return (item.children ?? []).filter(isVisible);
}
</script>

<template>
    <!-- Whole group (incl. its label, e.g. "Verwaltung") is hidden when the user can't see any of its items. -->
    <SidebarGroup v-if="visibleItems.length" class="px-2 py-0">
        <SidebarGroupLabel v-if="label">{{ label }}</SidebarGroupLabel>
        <SidebarMenu>
            <template v-for="item in visibleItems" :key="item.title">
                <!-- Item with a sub-menu -->
                <Collapsible
                    v-if="item.children && item.children.length > 0"
                    as-child
                    :default-open="isCurrentOrParentUrl(item.href ?? '#')"
                    class="group/collapsible"
                >
                    <SidebarMenuItem>
                        <CollapsibleTrigger as-child>
                            <SidebarMenuButton :tooltip="item.title">
                                <component :is="item.icon" v-if="item.icon" />
                                <span>{{ item.title }}</span>
                                <ChevronRight
                                    class="ml-auto transition-transform duration-200 group-data-[state=open]/collapsible:rotate-90"
                                />
                            </SidebarMenuButton>
                        </CollapsibleTrigger>
                        <CollapsibleContent>
                            <SidebarMenuSub>
                                <SidebarMenuSubItem
                                    v-for="child in visibleChildren(item)"
                                    :key="child.title"
                                >
                                    <SidebarMenuSubButton
                                        as-child
                                        :is-active="isCurrentUrl(child.href ?? '#')"
                                    >
                                        <Link :href="child.href ?? '#'">
                                            <span>{{ child.title }}</span>
                                        </Link>
                                    </SidebarMenuSubButton>
                                </SidebarMenuSubItem>
                            </SidebarMenuSub>
                        </CollapsibleContent>
                    </SidebarMenuItem>
                </Collapsible>

                <!-- Plain link -->
                <SidebarMenuItem v-else>
                    <SidebarMenuButton
                        as-child
                        :is-active="isCurrentUrl(item.href ?? '#')"
                        :tooltip="item.title"
                    >
                        <Link :href="item.href ?? '#'">
                            <component :is="item.icon" v-if="item.icon" />
                            <span>{{ item.title }}</span>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </template>
        </SidebarMenu>
    </SidebarGroup>
</template>
