<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { AtSign, Briefcase, Building2, GraduationCap, MapPin, Phone, Printer, Save, Smartphone, UserRound } from '@lucide/vue';
import { computed, h } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { Auth, BreadcrumbItem } from '@/types';

/**
 * Profil (user menu > Profil, UserController@profile). Converted from
 * resources/views/user/profile.blade.php (2026-10-02).
 *
 * Saving: PATCH /settings/firstpage/{id} -> SettingController@firstupdate,
 * unchanged in what it does: updates the user and appends the row to
 * storage/app/user/updateuser.csv (Excel export read by the AD sync for the
 * Outlook signature). Vorname / Name / Benutzername come from AD and are
 * read-only. The old "Replication" switch had no function (no name, no
 * script) and was dropped.
 */
type Profile = {
    id: number;
    title: string | null;
    vorname: string | null;
    name: string | null;
    username: string | null;
    email: string | null;
    position: string | null;
    abteilung: string | null;
    tel: string | null;
    fax: string | null;
    ort: string | null;
    strasse: string | null;
    plz: string | null;
    mobil: string | null;
    privat: string | null;
    email_privat: string | null;
    abschluss: string | null;
    office: string | null;
};

const props = defineProps<{ profile: Profile }>();

defineOptions({
    layout: (h_: typeof h, page: unknown) => {
        const breadcrumbs: BreadcrumbItem[] = [{ title: 'Profil', href: '/profile' }];

        return h_(AppLayout, { breadcrumbs }, () => page);
    },
});

const TITLES = ['Frau', 'Herr', 'Dr.', 'Prof.', 'Prof.Dr.'];

const page = usePage<{ auth: Auth }>();
const roles = computed<string[]>(() => page.props.auth.user?.assignedRoles ?? page.props.auth.user?.roles ?? []);

const greeting = computed(() => {
    const hour = new Date().getHours();
    if (hour < 12) return 'Guten Morgen';
    if (hour < 18) return 'Guten Tag';

    return 'Guten Abend';
});

const fullName = computed(() => [props.profile.vorname, props.profile.name].filter(Boolean).join(' '));
const initials = computed(() => ((props.profile.vorname?.[0] ?? '') + (props.profile.name?.[0] ?? '')).toUpperCase() || '?');

const p = props.profile;
const form = useForm({
    title: p.title ?? '',
    position: p.position ?? '',
    abteilung: p.abteilung ?? '',
    tel: p.tel ?? '',
    fax: p.fax ?? '',
    ort: p.ort ?? '',
    straße: p.strasse ?? '',
    plz: p.plz ?? '',
    mobil: p.mobil ?? '',
    privat: p.privat ?? '',
    email_privat: p.email_privat ?? '',
    abschluss: p.abschluss ?? '',
    office: p.office ?? '',
});

function submit() {
    form.patch(`/settings/firstpage/${p.id}`, { preserveScroll: true });
}

const inputCls = 'border-input bg-background h-9 w-full rounded-md border px-3 text-sm';
</script>

<template>
    <Head title="Profil" />

    <form class="mx-auto flex w-full max-w-5xl flex-1 flex-col gap-6 p-4 md:p-6" @submit.prevent="submit">
        <!-- Header -->
        <section class="bg-card text-card-foreground flex flex-wrap items-center gap-4 rounded-xl border p-6 shadow-sm">
            <span class="bg-primary/10 text-primary flex size-14 shrink-0 items-center justify-center rounded-full text-lg font-semibold">
                {{ initials }}
            </span>
            <div class="min-w-0 flex-1">
                <p class="text-muted-foreground text-sm">
                    {{ greeting }}<span v-if="form.title">, {{ form.title }} {{ profile.name }}</span>
                </p>
                <h1 class="text-xl font-semibold">{{ fullName }}</h1>
                <p class="text-muted-foreground text-sm">
                    {{ profile.email }}<span v-if="profile.username"> · {{ profile.username }}</span>
                </p>
            </div>
            <ul v-if="roles.length" class="flex flex-wrap gap-1.5">
                <li v-for="r in roles" :key="r" class="bg-muted rounded-full px-2.5 py-0.5 text-xs font-medium">{{ r.replace(/_/g, ' ') }}</li>
            </ul>
        </section>

        <div class="grid gap-6 lg:grid-cols-2">
            <!-- Outlook Signatur (required) -->
            <section class="bg-card text-card-foreground rounded-xl border shadow-sm">
                <header class="border-b px-5 py-4">
                    <h2 class="font-semibold">Outlook Signatur</h2>
                    <p class="text-muted-foreground text-xs">Pflichtfelder - erscheinen in Ihrer E-Mail-Signatur</p>
                </header>
                <div class="flex flex-col gap-4 px-5 py-4">
                    <div class="grid gap-4 sm:grid-cols-[10rem_1fr]">
                        <label class="flex flex-col gap-1 text-sm">
                            <span class="font-medium">Anrede / Titel *</span>
                            <select v-model="form.title" :class="inputCls">
                                <option value="" disabled>Bitte wählen</option>
                                <option v-if="form.title && !TITLES.includes(form.title)" :value="form.title">{{ form.title }}</option>
                                <option v-for="t in TITLES" :key="t" :value="t">{{ t }}</option>
                            </select>
                            <span v-if="form.errors.title" class="text-destructive text-xs">{{ form.errors.title }}</span>
                        </label>
                        <label class="flex flex-col gap-1 text-sm">
                            <span class="font-medium">Name</span>
                            <input
                                :value="fullName"
                                type="text"
                                readonly
                                :class="[inputCls, 'bg-muted/50 text-muted-foreground']"
                                title="Kommt aus dem Active Directory"
                            />
                        </label>
                    </div>

                    <label class="flex flex-col gap-1 text-sm">
                        <span class="flex items-center gap-1.5 font-medium"><Briefcase class="text-primary size-4" /> Tätigkeit *</span>
                        <input v-model="form.position" type="text" :class="inputCls" />
                        <span v-if="form.errors.position" class="text-destructive text-xs">{{ form.errors.position }}</span>
                    </label>
                    <label class="flex flex-col gap-1 text-sm">
                        <span class="flex items-center gap-1.5 font-medium"><Building2 class="text-primary size-4" /> Abteilung *</span>
                        <input v-model="form.abteilung" type="text" :class="inputCls" />
                        <span v-if="form.errors.abteilung" class="text-destructive text-xs">{{ form.errors.abteilung }}</span>
                    </label>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="flex flex-col gap-1 text-sm">
                            <span class="flex items-center gap-1.5 font-medium"><Phone class="text-primary size-4" /> Rufnummer *</span>
                            <input v-model="form.tel" type="tel" :class="inputCls" />
                            <span v-if="form.errors.tel" class="text-destructive text-xs">{{ form.errors.tel }}</span>
                        </label>
                        <label class="flex flex-col gap-1 text-sm">
                            <span class="flex items-center gap-1.5 font-medium"><Printer class="text-primary size-4" /> Fax</span>
                            <input v-model="form.fax" type="tel" :class="inputCls" />
                        </label>
                    </div>
                    <fieldset class="flex flex-col gap-2">
                        <legend class="mb-1 flex items-center gap-1.5 text-sm font-medium"><MapPin class="text-primary size-4" /> Adresse *</legend>
                        <input v-model="form['straße']" type="text" placeholder="Straße und Hausnummer" :class="inputCls" />
                        <span v-if="form.errors['straße']" class="text-destructive text-xs">{{ form.errors['straße'] }}</span>
                        <div class="grid grid-cols-[7rem_1fr] gap-2">
                            <input v-model="form.plz" type="text" placeholder="PLZ" :class="inputCls" />
                            <input v-model="form.ort" type="text" placeholder="Ort" :class="inputCls" />
                        </div>
                        <span v-if="form.errors.plz" class="text-destructive text-xs">{{ form.errors.plz }}</span>
                        <span v-if="form.errors.ort" class="text-destructive text-xs">{{ form.errors.ort }}</span>
                    </fieldset>
                </div>
            </section>

            <!-- Privat (optional) -->
            <section class="bg-card text-card-foreground self-start rounded-xl border shadow-sm">
                <header class="border-b px-5 py-4">
                    <h2 class="font-semibold">Privat</h2>
                    <p class="text-muted-foreground text-xs">Optional</p>
                </header>
                <div class="flex flex-col gap-4 px-5 py-4">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="flex flex-col gap-1 text-sm">
                            <span class="flex items-center gap-1.5 font-medium"><Smartphone class="text-primary size-4" /> Mobiltelefon</span>
                            <input v-model="form.mobil" type="tel" :class="inputCls" />
                        </label>
                        <label class="flex flex-col gap-1 text-sm">
                            <span class="flex items-center gap-1.5 font-medium"><Phone class="text-primary size-4" /> Tel. privat</span>
                            <input v-model="form.privat" type="tel" :class="inputCls" />
                        </label>
                    </div>
                    <label class="flex flex-col gap-1 text-sm">
                        <span class="flex items-center gap-1.5 font-medium"><AtSign class="text-primary size-4" /> E-Mail privat</span>
                        <input v-model="form.email_privat" type="email" :class="inputCls" />
                    </label>
                    <label class="flex flex-col gap-1 text-sm">
                        <span class="flex items-center gap-1.5 font-medium"><GraduationCap class="text-primary size-4" /> Abschluss</span>
                        <input v-model="form.abschluss" type="text" :class="inputCls" />
                    </label>
                    <label class="flex flex-col gap-1 text-sm">
                        <span class="flex items-center gap-1.5 font-medium"><UserRound class="text-primary size-4" /> BusinessUnit</span>
                        <input v-model="form.office" type="text" :class="inputCls" />
                    </label>
                </div>
            </section>
        </div>

        <div class="flex items-center justify-end gap-3">
            <span v-if="form.isDirty" class="text-muted-foreground text-xs">Ungespeicherte Änderungen</span>
            <button
                type="submit"
                :disabled="form.processing"
                class="bg-primary text-primary-foreground hover:bg-primary/90 inline-flex h-9 items-center gap-2 rounded-md px-4 text-sm disabled:opacity-60"
            >
                <Save class="size-4" />
                Speichern
            </button>
        </div>
    </form>
</template>
