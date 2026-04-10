import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Image from '@tiptap/extension-image';
import Link from '@tiptap/extension-link';
import Placeholder from '@tiptap/extension-placeholder';
import { Table, TableRow, TableHeader, TableCell } from '@tiptap/extension-table';

window.createTipTapEditor = function ({ element, content, onChange, uploadUrl, csrfToken }) {
    const editor = new Editor({
        element,
        extensions: [
            StarterKit,
            Image.configure({ inline: false, allowBase64: false }),
            Link.configure({ openOnClick: false, autolink: true }),
            Placeholder.configure({ placeholder: 'Start writing your document…' }),
            Table.configure({ resizable: true }),
            TableRow,
            TableHeader,
            TableCell,
        ],
        content,
        onUpdate({ editor }) {
            onChange(editor.getHTML());
        },
    });

    // Image upload helper
    editor.uploadImage = async function (file) {
        const form = new FormData();
        form.append('image', file);

        const res = await fetch(uploadUrl, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            body: form,
        });

        if (!res.ok) return;
        const { url } = await res.json();
        editor.chain().focus().setImage({ src: url }).run();
    };

    return editor;
};
