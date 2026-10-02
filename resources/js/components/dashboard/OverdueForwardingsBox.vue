<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { ArrowRight, CircleCheck, MailWarning } from '@lucide/vue';
import { ref } from 'vue';

/**
 * Super_Admin Dashboard: E-Mail-Weiterleitungen whose "bis" date is yesterday
 * or older and that are not marked as removed yet - they should be taken down.
 * "Entfernt" marks one as removed (POST /ticket/{id}/forwarding-removed).
 * Hidden when there are none; full lists on /email-forwardings.
 */
export type OverdueForwarding = {
    id: number;
    from: string;
    to: string;
    start: string | null;
    end: string | null;
    submitter: string;
};

defineProps<{ forwardings: OverdueForwarding[] }>();

function fmt(iso: string | null): string {
    const m = iso ? /^(\d{4})-(\d{2})-(\d{2})/.exec(iso) : null;

    return m ? `${m[3]}.${m[2]}.${m[1]}` : '–';
}

function daysOver(iso: string | null): number {
    const m = iso ? /^(\d{4})-(\d{2})-(\d{2})/.exec(iso) : null;
    if (!m) return 0;
    const end = new Date(Number(m[1]), Number(m[2]) - 1, Number(m[3]));
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    return Math.round((today.getTime() - end.getTime()) / 86_400_000);
}

const busy = ref<number | null>(null);
function markRemoved(f: OverdueForwarding) {
    if (!confirm(`Weiterleitung ${f.from} → ${f.to} als entfernt markieren?`)) return;
    busy.value = f.id;
    router.post(`/ticket/${f.id}/forwarding-removed`, {}, { preserveScroll: true, onFinish: () => (busy.value = null) });
}
</script>

<template>
    <section class="bg-card text-card-foreground flex min-w-0 flex-col rounded-xl border shadow-sm">
        <header class="flex items-center justify-between gap-3 border-b p-4">
            <div class="flex min-w-0 items-center gap-2">
                <MailWarning class="size-5 shrink-0 text-red-600" />
                <div class="min-w-0">
                    <h3 class="font-semibold">E-Mail-Weiterleitungen beenden</h3>
                    <p class="text-muted-foreground text-xs">
                        Abgelaufen, noch nicht entfernt · {{ forwardings.length }} ·
                        <Link href="/email-forwardings" class="text-primary hover:underline">alle anzeigen</Link>
                    </p>
                </div>
            </div>
        </header>

        <ul class="max-h-[26rem] divide-y overflow-auto">
            <li v-for="f in forwardings" :key="f.id" class="flex items-center gap-3 px-4 py-3">
                <div class="min-w-0 flex-1">
                    <Link :href="`/ticket/${f.id}`" class="flex flex-wrap items-center gap-1.5 text-sm font-medium hover:underline">
                        <span>{{ f.from }}</span>
                        <ArrowRight class="text-muted-foreground size-3.5 shrink-0" />
                        <span>{{ f.to }}</span>
                    </Link>
                    <p class="text-muted-foreground text-xs">
                        {{ fmt(f.start) }} – <span class="font-semibold text-red-600 dark:text-red-400">{{ fmt(f.end) }}</span> · seit
                        {{ daysOver(f.end) }} {{ daysOver(f.end) === 1 ? 'Tag' : 'Tagen' }} abgelaufen · von {{ f.submitter }}
                    </p>
                </div>
                <button
                    type="button"
                    class="hover:bg-accent inline-flex h-8 shrink-0 items-center gap-1 rounded-md border px-2.5 text-xs font-medium disabled:opacity-50"
                    :disabled="busy === f.id"
                    title="Weiterleitung wurde entfernt"
                    @click="markRemoved(f)"
                >
                    <CircleCheck class="size-4 text-green-600" />
                    Entfernt
                </button>
            </li>
        </ul>
    </section>
</template>
