import { computed, ref, unref, watch, type ComputedRef, type Ref } from 'vue';

/**
 * Shared client-side sort/search/pagination for any table fed by a plain
 * array (i.e. every converted list page except Korso/Dashboard.vue, whose
 * "Alle erledigten Tickets" filter can hold far more rows than makes sense
 * to ship to the browser at once and so keeps its own server-side
 * filterTickets() - see that file's own comment. Everything else in this
 * app already gets its full result set as a plain Inertia prop, so all of
 * this can run in the browser with zero backend changes).
 *
 * Standard going forward for every table, new or retrofitted (per your
 * 2026-09-17 instruction): sortable columns, a free-text search box, a
 * page-size picker, and pagination defaulting to 15 rows unless a specific
 * table is told otherwise.
 *
 * Columns are the single source of truth for both sorting and searching - a
 * column not explicitly marked `sortable: false` / `searchable: false`
 * participates in both, using `value()` (or a plain dot-path lookup on
 * `key` when `value` isn't given, e.g. "subUser.username") as the accessor
 * for both purposes.
 */

export type SortDirection = 'asc' | 'desc';

export interface DataTableColumn<T> {
    /** Dot-path into the row when no `value` accessor is given (e.g. "subUser.username"). */
    key: string;
    /** Custom accessor - required for computed/derived cells (badge labels, formatted dates, etc.). */
    value?: (row: T) => unknown;
    /** Default true - every column sorts unless explicitly opted out (icon-only/actions columns). */
    sortable?: boolean;
    /** Default = sortable - sorting off doesn't have to imply search-off, so this can differ. */
    searchable?: boolean;
}

export interface UseDataTableOptions {
    /** Default 15 - override per table only when explicitly asked for something different. */
    pageSize?: number;
}

function getAt(row: unknown, path: string): unknown {
    return path.split('.').reduce<unknown>((acc, key) => {
        if (acc == null) return acc;
        return (acc as Record<string, unknown>)[key];
    }, row);
}

export function useDataTable<T>(rows: Ref<T[]> | ComputedRef<T[]>, columns: DataTableColumn<T>[], options: UseDataTableOptions = {}) {
    const search = ref('');
    const sortKey = ref<string | null>(null);
    const sortDir = ref<SortDirection>('asc');
    const pageSize = ref(options.pageSize ?? 15);
    const page = ref(1);

    const columnByKey = new Map(columns.map((c) => [c.key, c]));

    function valueOf(row: T, key: string): unknown {
        const col = columnByKey.get(key);
        if (col?.value) return col.value(row);
        return getAt(row, key);
    }

    const searchableKeys = computed(() => columns.filter((c) => c.searchable ?? c.sortable ?? true).map((c) => c.key));

    const filtered = computed(() => {
        const q = search.value.trim().toLowerCase();
        const all = unref(rows);
        if (!q) return all;
        return all.filter((row) => searchableKeys.value.some((key) => String(valueOf(row, key) ?? '').toLowerCase().includes(q)));
    });

    const sorted = computed(() => {
        const key = sortKey.value;
        if (!key) return filtered.value;
        const dir = sortDir.value === 'asc' ? 1 : -1;
        return [...filtered.value].sort((a, b) => {
            const av = valueOf(a, key);
            const bv = valueOf(b, key);
            if (av == null && bv == null) return 0;
            if (av == null) return 1;
            if (bv == null) return -1;
            if (typeof av === 'number' && typeof bv === 'number') return (av - bv) * dir;
            if (typeof av === 'boolean' && typeof bv === 'boolean') return (av === bv ? 0 : av ? -1 : 1) * dir;
            return String(av).localeCompare(String(bv), 'de', { numeric: true, sensitivity: 'base' }) * dir;
        });
    });

    const total = computed(() => sorted.value.length);
    // pageSize 0 means "Alle" (show every row on one page).
    const pageCount = computed(() => (pageSize.value <= 0 ? 1 : Math.max(1, Math.ceil(total.value / pageSize.value))));

    const pagedRows = computed(() => {
        if (pageSize.value <= 0) return sorted.value;
        const start = (page.value - 1) * pageSize.value;
        return sorted.value.slice(start, start + pageSize.value);
    });

    const rangeFrom = computed(() => (total.value === 0 ? 0 : pageSize.value <= 0 ? 1 : (page.value - 1) * pageSize.value + 1));
    const rangeTo = computed(() => (pageSize.value <= 0 ? total.value : Math.min(page.value * pageSize.value, total.value)));

    // Search/page-size changes jump back to page 1; shrinking the result set
    // (via search, or a smaller page size) can also strand `page` past the
    // new last page - both are normalized here rather than in every caller.
    watch([search, pageSize], () => {
        page.value = 1;
    });
    watch(pageCount, (pc) => {
        if (page.value > pc) page.value = pc;
    });

    function toggleSort(key: string) {
        const col = columnByKey.get(key);
        if (col?.sortable === false) return;
        if (sortKey.value !== key) {
            sortKey.value = key;
            sortDir.value = 'asc';
        } else if (sortDir.value === 'asc') {
            sortDir.value = 'desc';
        } else {
            sortKey.value = null;
            sortDir.value = 'asc';
        }
    }

    function goToPage(n: number) {
        page.value = Math.min(Math.max(1, n), pageCount.value);
    }

    return {
        search,
        sortKey,
        sortDir,
        toggleSort,
        pageSize,
        page,
        pagedRows,
        total,
        pageCount,
        rangeFrom,
        rangeTo,
        goToPage,
    };
}
