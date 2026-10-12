<script setup lang="ts">
// Comment editor (2026-10-13): Tiptap (ProseMirror) with a small toolbar -
// bold, italic, underline, strikethrough, lists, link, quote, code, undo/redo.
// Pictures: paste (screenshot), drop or pick one -> uploaded at once
// (POST /comment-files, CommentFileController) and shown in the text as
// <img src="/comment-files/{id}">. Other files: paperclip, paste or drop ->
// uploaded as attachments (v-model:files). Max 10 MB, office types only -
// checked here for a quick message and again on the server.
// Ctrl+Enter = submit. The HTML is cleaned on the server (App\Support\CommentHtml).
import { EditorContent, useEditor } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';
import Image from '@tiptap/extension-image';
import { Placeholder } from '@tiptap/extensions';
import axios from 'axios';
import { toast } from 'vue-sonner';
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import {
    Bold,
    Code,
    ImagePlus,
    Italic,
    Link2,
    List,
    ListOrdered,
    Loader2,
    Paperclip,
    Redo2,
    Strikethrough,
    TextQuote,
    Underline,
    Undo2,
    X,
} from '@lucide/vue';
import CommentFileIcon from '@/components/CommentFileIcon.vue';
import { formatFileSize, type CommentFileItem } from '@/lib/commentFiles';

const props = withDefaults(
    defineProps<{
        modelValue: string;
        /** attachments (not the pictures in the text) */
        files?: CommentFileItem[];
        /** the ticket the uploads belong to (access check on the server) */
        uploadType: string;
        uploadId: number;
        placeholder?: string;
        autofocus?: boolean;
        /** min. height of the writing area */
        minHeight?: string;
    }>(),
    { files: () => [], placeholder: 'Kommentar schreiben …', autofocus: false, minHeight: '5rem' },
);

const emit = defineEmits<{
    'update:modelValue': [html: string];
    'update:files': [files: CommentFileItem[]];
    /** uploads still running */
    busy: [busy: boolean];
    submit: [];
}>();

const MAX_BYTES = 10 * 1024 * 1024;
const IMAGE_TYPES = ['image/png', 'image/jpeg', 'image/gif', 'image/webp'];
const EXTENSIONS = ['png', 'jpg', 'jpeg', 'gif', 'webp', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv', 'zip', 'msg', 'eml'];
const ACCEPT = EXTENSIONS.map((e) => `.${e}`).join(',');

/** only our own uploads count as pictures (also when pasting HTML from elsewhere) */
const CommentImage = Image.extend({
    parseHTML() {
        return [{ tag: 'img[src^="/comment-files/"]' }];
    },
}).configure({ allowBase64: false, HTMLAttributes: { alt: '' } });

const editor = useEditor({
    content: props.modelValue,
    autofocus: props.autofocus ? 'end' : false,
    extensions: [
        StarterKit.configure({
            heading: false,
            horizontalRule: false,
            link: { openOnClick: false, autolink: true, defaultProtocol: 'https', protocols: ['http', 'https', 'mailto'] },
        }),
        CommentImage,
        Placeholder.configure({ placeholder: () => props.placeholder }),
    ],
    editorProps: {
        attributes: { class: 'rich-text tiptap px-3 py-2 text-sm focus:outline-none' },
        handlePaste: (_view, event) => {
            const files = Array.from(event.clipboardData?.files ?? []);
            // Excel / Word / OneNote put text AND a picture of it on the clipboard: paste the text
            const text = event.clipboardData?.getData('text/plain') ?? '';
            if (!files.length || text.trim() !== '') return false;
            event.preventDefault();
            addFiles(files, true);

            return true;
        },
        handleDrop: (view, event, _slice, moved) => {
            const files = Array.from(event.dataTransfer?.files ?? []);
            if (moved || !files.length) return false;
            event.preventDefault();
            const pos = view.posAtCoords({ left: event.clientX, top: event.clientY })?.pos;
            addFiles(files, true, pos);

            return true;
        },
        handleKeyDown: (_view, event) => {
            if (event.key === 'Enter' && (event.ctrlKey || event.metaKey)) {
                emit('submit');

                return true;
            }

            return false;
        },
    },
    onUpdate: ({ editor: ed }) => {
        lastEmitted = ed.isEmpty ? '' : ed.getHTML();
        emit('update:modelValue', lastEmitted);
    },
});

let lastEmitted = props.modelValue;
watch(
    () => props.modelValue,
    (value) => {
        if (!editor.value || value === lastEmitted) return;
        lastEmitted = value;
        editor.value.commands.setContent(value || '', { emitUpdate: false });
    },
);

onBeforeUnmount(() => editor.value?.destroy());

// ─── Uploads ────────────────────────────────────────────────────────────
const uploading = ref<{ key: number; name: string }[]>([]);
let uploadKey = 0;
watch(
    () => uploading.value.length,
    (n) => emit('busy', n > 0),
);

function extensionOf(name: string): string {
    const i = name.lastIndexOf('.');

    return i === -1 ? '' : name.slice(i + 1).toLowerCase();
}

/** clipboard screenshots arrive as "image.png" - give them a readable, unique name */
function named(file: File): File {
    if (!IMAGE_TYPES.includes(file.type) || !/^image\.\w+$/i.test(file.name)) return file;
    const d = new Date();
    const pad = (n: number) => String(n).padStart(2, '0');
    const stamp = `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}_${pad(d.getHours())}${pad(d.getMinutes())}${pad(d.getSeconds())}`;

    return new File([file], `Bild_${stamp}.${extensionOf(file.name) || 'png'}`, { type: file.type });
}

function errorMessage(error: unknown): string {
    const response = (error as { response?: { status?: number; data?: { errors?: Record<string, string[]>; message?: string } } })?.response;
    if (response?.status === 413) return 'Die Datei ist zu groß für den Server.';
    const first = response?.data?.errors ? Object.values(response.data.errors)[0]?.[0] : undefined;

    return first ?? response?.data?.message ?? 'Hochladen fehlgeschlagen.';
}

/**
 * asImage: pictures go into the text (paste / drop / picture button),
 * everything else becomes an attachment.
 */
function addFiles(list: File[], asImage: boolean, pos?: number) {
    for (const raw of list) {
        const file = named(raw);
        if (!EXTENSIONS.includes(extensionOf(file.name))) {
            toast.error(`„${file.name}“: Dateityp nicht erlaubt (Bilder, PDF, Office, TXT, CSV, ZIP, E-Mails).`);
            continue;
        }
        if (file.size > MAX_BYTES) {
            toast.error(`„${file.name}“ ist größer als 10 MB.`);
            continue;
        }
        upload(file, asImage && IMAGE_TYPES.includes(file.type), pos);
    }
}

async function upload(file: File, inline: boolean, pos?: number) {
    const key = ++uploadKey;
    uploading.value.push({ key, name: file.name });
    const data = new FormData();
    data.append('file', file);
    data.append('commentable_type', props.uploadType);
    data.append('commentable_id', String(props.uploadId));
    data.append('inline', inline ? '1' : '0');
    try {
        const { data: saved } = await axios.post<CommentFileItem>('/comment-files', data);
        if (inline && editor.value) {
            const node = { type: 'image', attrs: { src: `/comment-files/${saved.id}`, alt: saved.original_name } };
            const chain = editor.value.chain().focus();
            (pos !== undefined ? chain.insertContentAt(pos, node) : chain.insertContent(node)).run();
        } else {
            emit('update:files', [...props.files, saved]);
        }
    } catch (error) {
        toast.error(`„${file.name}“: ${errorMessage(error)}`);
    } finally {
        uploading.value = uploading.value.filter((u) => u.key !== key);
    }
}

function removeFile(file: CommentFileItem) {
    emit(
        'update:files',
        props.files.filter((f) => f.id !== file.id),
    );
    // not in a saved comment yet -> delete right away (saved ones go when the edit is saved)
    if (file.comment_id == null) axios.delete(`/comment-files/${file.id}`).catch(() => {});
}

const imageInput = ref<HTMLInputElement | null>(null);
const fileInput = ref<HTMLInputElement | null>(null);
function picked(event: Event, asImage: boolean) {
    const input = event.target as HTMLInputElement;
    addFiles(Array.from(input.files ?? []), asImage);
    input.value = '';
}

// ─── Toolbar ────────────────────────────────────────────────────────────
function setLink() {
    if (!editor.value) return;
    const previous = (editor.value.getAttributes('link').href as string | undefined) ?? '';
    const url = window.prompt('Link (leer = Link entfernen):', previous || 'https://');
    if (url === null) return;
    const chain = editor.value.chain().focus().extendMarkRange('link');
    if (url.trim() === '' || url.trim() === 'https://') chain.unsetLink().run();
    else chain.setLink({ href: url.trim() }).run();
}

type Tool = { icon: typeof Bold; title: string; run: () => void; active?: () => boolean; disabled?: () => boolean };
const tools = computed<(Tool | 'sep')[]>(() => {
    const ed = editor.value;
    if (!ed) return [];
    const c = () => ed.chain().focus();

    return [
        { icon: Bold, title: 'Fett (Strg+B)', run: () => c().toggleBold().run(), active: () => ed.isActive('bold') },
        { icon: Italic, title: 'Kursiv (Strg+I)', run: () => c().toggleItalic().run(), active: () => ed.isActive('italic') },
        { icon: Underline, title: 'Unterstrichen (Strg+U)', run: () => c().toggleUnderline().run(), active: () => ed.isActive('underline') },
        { icon: Strikethrough, title: 'Durchgestrichen', run: () => c().toggleStrike().run(), active: () => ed.isActive('strike') },
        'sep',
        { icon: List, title: 'Aufzählung', run: () => c().toggleBulletList().run(), active: () => ed.isActive('bulletList') },
        { icon: ListOrdered, title: 'Nummerierte Liste', run: () => c().toggleOrderedList().run(), active: () => ed.isActive('orderedList') },
        { icon: TextQuote, title: 'Zitat', run: () => c().toggleBlockquote().run(), active: () => ed.isActive('blockquote') },
        { icon: Code, title: 'Code', run: () => c().toggleCode().run(), active: () => ed.isActive('code') },
        { icon: Link2, title: 'Link', run: setLink, active: () => ed.isActive('link') },
        'sep',
        { icon: ImagePlus, title: 'Bild einfügen (oder einfach einfügen / hineinziehen)', run: () => imageInput.value?.click() },
        { icon: Paperclip, title: 'Datei anhängen (max. 10 MB)', run: () => fileInput.value?.click() },
        'sep',
        { icon: Undo2, title: 'Rückgängig (Strg+Z)', run: () => c().undo().run(), disabled: () => !ed.can().undo() },
        { icon: Redo2, title: 'Wiederholen (Strg+Y)', run: () => c().redo().run(), disabled: () => !ed.can().redo() },
    ];
});

const dragOver = ref(false);
</script>

<template>
    <div
        class="border-input bg-background focus-within:ring-ring/40 rounded-md border focus-within:ring-2"
        :class="dragOver ? 'ring-primary/50 ring-2' : ''"
        @dragenter="dragOver = true"
        @dragleave.self="dragOver = false"
        @drop="dragOver = false"
    >
        <div v-if="editor" class="flex flex-wrap items-center gap-0.5 border-b px-1.5 py-1" role="toolbar" aria-label="Formatierung">
            <template v-for="(tool, i) in tools" :key="i">
                <span v-if="tool === 'sep'" class="bg-border mx-1 h-5 w-px" />
                <button
                    v-else
                    type="button"
                    class="text-muted-foreground hover:bg-accent hover:text-foreground inline-flex h-7 w-7 items-center justify-center rounded disabled:opacity-30"
                    :class="tool.active?.() ? 'bg-accent text-foreground' : ''"
                    :title="tool.title"
                    :aria-label="tool.title"
                    :aria-pressed="tool.active ? tool.active() : undefined"
                    :disabled="tool.disabled?.()"
                    @mousedown.prevent
                    @click="tool.run()"
                >
                    <component :is="tool.icon" class="h-4 w-4" />
                </button>
            </template>
        </div>

        <EditorContent :editor="editor" class="overflow-y-auto" :style="{ minHeight, maxHeight: '28rem' }" @click="editor?.commands.focus()" />

        <div v-if="files.length || uploading.length" class="flex flex-wrap gap-1.5 border-t px-2 py-1.5">
            <span
                v-for="f in files"
                :key="f.id"
                class="bg-muted inline-flex max-w-full items-center gap-1.5 rounded-md py-1 pr-1 pl-2 text-xs"
                :title="f.original_name"
            >
                <CommentFileIcon :file="f" class="h-3.5 w-3.5 shrink-0" />
                <span class="max-w-48 truncate">{{ f.original_name }}</span>
                <span class="text-muted-foreground">{{ formatFileSize(f.size) }}</span>
                <button type="button" class="hover:bg-background rounded p-0.5" :aria-label="`${f.original_name} entfernen`" @click="removeFile(f)">
                    <X class="h-3 w-3" />
                </button>
            </span>
            <span v-for="u in uploading" :key="u.key" class="text-muted-foreground inline-flex items-center gap-1.5 rounded-md border border-dashed px-2 py-1 text-xs">
                <Loader2 class="h-3.5 w-3.5 animate-spin" />
                <span class="max-w-48 truncate">{{ u.name }}</span>
            </span>
        </div>

        <input ref="imageInput" type="file" class="hidden" accept="image/png,image/jpeg,image/gif,image/webp" multiple @change="picked($event, true)" />
        <input ref="fileInput" type="file" class="hidden" :accept="ACCEPT" multiple @change="picked($event, false)" />
    </div>
</template>
