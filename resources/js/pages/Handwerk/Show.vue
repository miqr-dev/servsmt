<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { ArrowLeft, FileDown } from '@lucide/vue';
import { computed, h, ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import CommentThread from '@/components/CommentThread.vue';
import { ITEM_GROUPS_BY_TYPE, SUBJECT_TYPES } from '@/lib/handwerkItems';
import type { Auth, BreadcrumbItem } from '@/types';
import type { CommentItem } from '@/components/CommentThread.vue';

/**
 * Converted from resources/views/handwerk/show.blade.php. The old page
 * dynamically @included one of 6 ticket-type partials
 * (handwerk/layout/views/*.blade.php) based on $blade_name - those are
 * reproduced below as a data-driven ITEM_GROUPS_BY_TYPE table instead of
 * separate Vue components, since every one of them is the same
 * "show these boolean columns as label -> qty" shape.
 *
 * The old @comments(...) directive was backed by an abandoned/broken
 * package; rebuilt as first-party code (app/Concerns/Commentable.php,
 * CommentController, CommentPolicy - see modernization-audit.md Section 3)
 * and rendered below via the shared <CommentThread> component.
 */

type LocationInfo = { address: string | null } | null;
type RoomInfo = { rname: string | null; altrname: string | null } | null;
type SubUser = { username: string } | null;

type HandwerkDetail = {
    id: number;
    problem_type: string;
    submitter_name: string;
    submitter_standort: string | null;
    submitter_adresse: string | null;
    tel_number: string | null;
    created_at: string;
    notizen: string | null;
    subject: string | null;
    custom_room: string | null;
    done_by: string | null;
    deleted_at: string | null;
    assignedTo: number | null;
    room: RoomInfo;
    location: LocationInfo;
    subUser: SubUser;
    comments: CommentItem[];
    [key: string]: unknown;
};

type Admin = { id: number; username: string };

const props = defineProps<{
    handwerk: HandwerkDetail;
    admins: Admin[];
}>();

defineOptions({
    layout: (h_: typeof h, page: unknown) => {
        const breadcrumbs: BreadcrumbItem[] = [
            { title: 'Handwerkaufgaben', href: '/handwerk' },
            { title: 'Ticket', href: '#' },
        ];

        return h_(AppLayout, { breadcrumbs }, () => page);
    },
});

const page = usePage<{ auth: Auth }>();
const roles = computed(() => page.props.auth.user?.roles ?? []);
const isSuperAdmin = computed(() => roles.value.includes('Super_Admin'));
const isHandwerkAdmin = computed(() => roles.value.includes('handwerk_admin'));
const canAssign = computed(() => isSuperAdmin.value || isHandwerkAdmin.value);
const canComplete = computed(
    () => isSuperAdmin.value || isHandwerkAdmin.value || roles.value.includes('Sekretariat'),
);

const fromCity = new URLSearchParams(window.location.search).get('from_city');
const backHref = computed(() => (fromCity ? `/handwerker/${fromCity}` : '/handwerk'));

function formatDate(value: string): string {
    return new Date(value).toLocaleDateString('de-DE', { day: '2-digit', month: '2-digit', year: 'numeric' });
}

// --- ticket-type-specific item groups (replaces the 6 *ticket.blade.php partials) ---
// Shared with the "Mobiliar" creation forms - see resources/js/lib/handwerkItems.ts.

const itemGroups = computed(() =>
    (ITEM_GROUPS_BY_TYPE[props.handwerk.problem_type] ?? []).filter((group) =>
        group.items.some((item) => props.handwerk[item.key]),
    ),
);
const showSubject = computed(() => SUBJECT_TYPES.includes(props.handwerk.problem_type));

// --- actions ---

const assignedTo = ref<number | ''>(props.handwerk.assignedTo ?? '');

// The old Blade template read `$handwerk->user->username` here, but the
// Handwerk model has no `user()` relation (only `subUser()`, the ticket's
// creator) - so this always rendered blank. Using the already-loaded
// `admins` list to resolve the actual assigned admin's name instead, which
// matches what the "Zuweisen" dropdown right below it is for.
const assignedAdminUsername = computed(
    () => props.admins.find((admin) => admin.id === props.handwerk.assignedTo)?.username ?? '',
);

async function onAssignChange() {
    await axios.post('/handwerk/assignTo', {
        assignedTo: assignedTo.value,
        handwerkId: props.handwerk.id,
    });
}

function markDone() {
    router.post(
        `/handwerk.delete/${props.handwerk.id}`,
        fromCity ? { from_city: fromCity } : {},
        { preserveScroll: true },
    );
}

function restore() {
    router.post(`/handwerk.restore/${props.handwerk.id}`, {}, { preserveScroll: true });
}
</script>

<template>
    <Head :title="handwerk.problem_type" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <div v-if="canAssign" class="flex justify-end">
            <Link
                :href="backHref"
                class="border-border bg-card hover:bg-accent inline-flex h-9 items-center gap-1.5 rounded-md border px-3 text-sm"
            >
                <ArrowLeft class="h-4 w-4" />
                {{ fromCity ? 'Zurück zur Stadt' : 'Zurück zur Übersicht' }}
            </Link>
        </div>

        <div class="grid gap-4 lg:grid-cols-3">
            <!-- Left card: status / assignment -->
            <div class="bg-card text-card-foreground rounded-xl border p-4 shadow-sm">
                <div class="flex flex-col gap-4">
                    <div class="text-center">
                        <p class="text-muted-foreground text-sm">
                            {{ handwerk.deleted_at ? 'Erledigt von' : 'Zugewiesen an' }}
                        </p>
                        <h3 class="text-lg font-bold" style="color: #661421">
                            {{ handwerk.deleted_at ? handwerk.done_by : assignedAdminUsername }}
                        </h3>
                    </div>

                    <button
                        v-if="handwerk.deleted_at"
                        type="button"
                        class="bg-primary text-primary-foreground hover:bg-primary/90 inline-flex h-9 items-center justify-center rounded-md px-3 text-sm"
                        @click="restore"
                    >
                        Wiederherstellen
                    </button>

                    <template v-else>
                        <button
                            v-if="canComplete"
                            type="button"
                            class="bg-primary text-primary-foreground hover:bg-primary/90 inline-flex h-9 items-center justify-center rounded-md px-3 text-sm"
                            @click="markDone"
                        >
                            Erledigt
                        </button>

                        <div v-if="canAssign" class="flex flex-col gap-1">
                            <label for="assignedTo" class="text-sm font-medium">Zuweisen</label>
                            <select
                                id="assignedTo"
                                v-model="assignedTo"
                                class="border-input bg-background h-9 rounded-md border px-3 text-sm"
                                @change="onAssignChange"
                            >
                                <option value="">Zuweisen</option>
                                <option v-for="admin in admins" :key="admin.id" :value="admin.id">
                                    {{ admin.username }}
                                </option>
                                <!-- Matches the special-case option the old Blade template hardcoded
                                     for Leipzig tickets (id 14441, "Steven Stefanowsky") -->
                                <option v-if="handwerk.submitter_standort === 'Leipzig'" :value="14441">
                                    Steven Stefanowsky
                                </option>
                            </select>
                        </div>
                    </template>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="flex flex-col gap-1">
                            <label class="text-muted-foreground text-xs">Ersteller</label>
                            <input
                                type="text"
                                readonly
                                :value="handwerk.submitter_name"
                                class="border-input bg-muted/40 h-9 rounded-md border px-3 text-sm"
                            />
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-muted-foreground text-xs">Am</label>
                            <input
                                type="text"
                                readonly
                                :value="formatDate(handwerk.created_at)"
                                class="border-input bg-muted/40 h-9 rounded-md border px-3 text-sm"
                            />
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-muted-foreground text-xs">Standort</label>
                            <input
                                type="text"
                                readonly
                                :value="handwerk.submitter_standort"
                                class="border-input bg-muted/40 h-9 rounded-md border px-3 text-sm"
                            />
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-muted-foreground text-xs">Adresse</label>
                            <input
                                type="text"
                                readonly
                                :value="handwerk.submitter_adresse"
                                class="border-input bg-muted/40 h-9 rounded-md border px-3 text-sm"
                            />
                        </div>
                        <div class="col-span-2 flex flex-col gap-1">
                            <label class="text-muted-foreground text-xs">Telefon</label>
                            <input
                                type="text"
                                readonly
                                :value="handwerk.tel_number"
                                class="border-input bg-muted/40 h-9 rounded-md border px-3 text-sm"
                            />
                        </div>
                    </div>

                    <a
                        v-if="canAssign"
                        :href="`/handwerk/${handwerk.id}/pdf`"
                        target="_blank"
                        rel="noopener"
                        class="bg-destructive text-destructive-foreground hover:bg-destructive/90 inline-flex h-9 items-center justify-center gap-1.5 rounded-md px-3 text-sm"
                    >
                        <FileDown class="h-4 w-4" />
                        als PDF Herunterladen
                    </a>
                </div>
            </div>

            <!-- Right card: ticket details -->
            <div class="bg-card text-card-foreground rounded-xl border p-4 shadow-sm lg:col-span-2">
                <h2 class="text-center text-lg font-semibold">{{ handwerk.problem_type }}</h2>

                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                    <div>
                        <p class="text-muted-foreground text-xs">Adresse</p>
                        <p class="font-semibold" style="color: #661421">{{ handwerk.location?.address }}</p>
                    </div>
                    <div v-if="handwerk.room">
                        <p class="text-muted-foreground text-xs">Raum</p>
                        <p class="font-semibold" style="color: #661421">
                            {{ handwerk.room?.rname }}
                            <span v-if="handwerk.room?.altrname"> / {{ handwerk.room?.altrname }}</span>
                        </p>
                    </div>
                    <div v-else-if="handwerk.custom_room">
                        <p class="text-success text-xs">Raum</p>
                        <p class="font-semibold" style="color: #661421">{{ handwerk.custom_room }}</p>
                    </div>
                    <div v-if="showSubject" class="sm:col-span-2">
                        <p class="text-success text-xs">Betreff</p>
                        <p class="font-semibold" style="color: #661421">{{ handwerk.subject }}</p>
                    </div>
                </div>

                <div v-if="itemGroups.length" class="mt-4 flex flex-col gap-3">
                    <div v-for="group in itemGroups" :key="group.title">
                        <h4 class="font-semibold" style="color: #004873">{{ group.title }}</h4>
                        <div class="mt-1 grid gap-x-4 gap-y-1 sm:grid-cols-3">
                            <p
                                v-for="item in group.items.filter((i) => handwerk[i.key])"
                                :key="item.key"
                                class="text-sm font-medium"
                            >
                                {{ item.label }} →
                                <span style="color: #008e5e">{{ handwerk[`${item.key}_qty`] }}</span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <p class="text-sm font-semibold" style="color: #661421">Beschreibung</p>
                    <!-- eslint-disable-next-line vue/no-v-html -->
                    <div class="mt-1 text-sm whitespace-pre-wrap" v-html="handwerk.notizen"></div>
                </div>

                <div class="mt-6 border-t pt-4">
                    <h5 class="mb-2 text-sm font-semibold" style="color: #661421">Kommentare</h5>
                    <CommentThread
                        model-type="App\Handwerk"
                        :model-id="handwerk.id"
                        :comments="handwerk.comments"
                        :is-done="!!handwerk.deleted_at"
                    />
                </div>
            </div>
        </div>
    </div>
</template>
