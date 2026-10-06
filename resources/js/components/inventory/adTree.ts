import type { InjectionKey, Ref } from 'vue';

export type AdTreeNode = {
    id: number;
    label: string;
    name: string;
    type: string;
    /** 'ad' = imported, 'app' = added in the app (not in AD) */
    source?: string;
    children: AdTreeNode[];
    /** computers in this OU and everything below it */
    count: number;
};

export type AdTreeState = {
    expanded: Set<number>;
    selected: Ref<number | null>;
    toggle: (id: number) => void;
    select: (id: number) => void;
};

export const AD_TREE_KEY: InjectionKey<AdTreeState> = Symbol('adTree');
