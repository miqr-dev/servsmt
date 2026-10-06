<script setup lang="ts">
import { ChevronRight } from '@lucide/vue';
import { computed, inject } from 'vue';
import { AD_TREE_KEY, type AdTreeNode, type AdTreeState } from './adTree';

/** One OU in the "AD Räume & Computer" tree (recursive). */
const props = defineProps<{ node: AdTreeNode; level: number }>();

const tree = inject(AD_TREE_KEY) as AdTreeState;

const open = computed(() => tree.expanded.has(props.node.id));
const selected = computed(() => tree.selected.value === props.node.id);

const TYPE_STYLE: Record<string, string> = {
    standort: 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
    adresse: 'bg-violet-100 text-violet-700 dark:bg-violet-900/40 dark:text-violet-300',
    etage: 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
    raum: 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300',
    gruppe: 'bg-teal-100 text-teal-700 dark:bg-teal-900/40 dark:text-teal-300',
    other: 'bg-muted text-muted-foreground',
};
const TYPE_LABEL: Record<string, string> = {
    standort: 'Standort',
    adresse: 'Adresse',
    etage: 'Etage',
    raum: 'Raum',
    gruppe: 'Gruppe',
    other: 'OU',
};
</script>

<template>
    <li>
        <div
            class="group flex cursor-pointer items-center gap-1 rounded-md py-1 pr-2 text-sm"
            :class="selected ? 'bg-primary/10 text-primary font-medium' : 'hover:bg-accent'"
            :style="{ paddingLeft: `${level * 14 + 4}px` }"
            @click="tree.select(node.id)"
        >
            <button
                v-if="node.children.length"
                type="button"
                class="text-muted-foreground hover:text-foreground rounded p-0.5"
                :aria-label="open ? 'Zuklappen' : 'Aufklappen'"
                @click.stop="tree.toggle(node.id)"
            >
                <ChevronRight class="size-3.5 transition-transform" :class="{ 'rotate-90': open }" />
            </button>
            <span v-else class="inline-block w-[18px]" />
            <span class="min-w-0 flex-1 truncate" :title="node.name">{{ node.label }}</span>
            <span
                v-if="node.source === 'app'"
                class="shrink-0 rounded border border-dashed px-1 text-[10px] font-medium"
                title="Im App angelegt, nicht im AD"
                >App</span
            >
            <span class="shrink-0 rounded px-1.5 py-px text-[10px] font-medium uppercase" :class="TYPE_STYLE[node.type] ?? TYPE_STYLE.other">
                {{ TYPE_LABEL[node.type] ?? node.type }}
            </span>
            <span class="text-muted-foreground w-9 shrink-0 text-right text-xs tabular-nums" title="Computer (inkl. Unter-OUs)">{{
                node.count
            }}</span>
        </div>
        <ul v-if="open && node.children.length">
            <AdOuTreeNode v-for="child in node.children" :key="child.id" :node="child" :level="level + 1" />
        </ul>
    </li>
</template>
