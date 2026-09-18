<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { LucideIcon } from '@lucide/vue';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';

/**
 * Unified icon-button row actions (view/edit/delete/etc.) for table rows -
 * used everywhere instead of text links ("Ansehen"/"Bearbeiten"/"Löschen")
 * so the whole app reads consistently as pages get converted. New Blade
 * pages that get modernized should reuse this component too rather than
 * inventing their own action-link style.
 */
export type RowAction = {
    icon: LucideIcon;
    label: string;
    href?: string;
    onClick?: () => void;
    variant?: 'default' | 'destructive';
};

defineProps<{
    actions: RowAction[];
}>();

const buttonClass = (variant: RowAction['variant']) => [
    'inline-flex h-8 w-8 items-center justify-center rounded-md transition-colors',
    variant === 'destructive'
        ? 'text-destructive hover:bg-destructive/10'
        : 'text-muted-foreground hover:bg-accent hover:text-foreground',
];
</script>

<template>
    <TooltipProvider :delay-duration="200">
        <div class="flex items-center justify-end gap-1">
            <Tooltip v-for="(action, index) in actions" :key="index">
                <TooltipTrigger as-child>
                    <Link
                        v-if="action.href"
                        :href="action.href"
                        :class="buttonClass(action.variant)"
                    >
                        <component :is="action.icon" class="h-4 w-4" />
                        <span class="sr-only">{{ action.label }}</span>
                    </Link>
                    <button
                        v-else
                        type="button"
                        :class="buttonClass(action.variant)"
                        @click="action.onClick"
                    >
                        <component :is="action.icon" class="h-4 w-4" />
                        <span class="sr-only">{{ action.label }}</span>
                    </button>
                </TooltipTrigger>
                <TooltipContent>{{ action.label }}</TooltipContent>
            </Tooltip>
        </div>
    </TooltipProvider>
</template>
