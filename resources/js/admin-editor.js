/*
 * Rich-text editor for article bodies in /admin (Quill 2). Loaded only on the
 * article form via its own Vite entry. Each [data-rich-editor] wraps a hidden
 * <input> that receives the HTML on submit; the server sanitizes it again
 * (App\Support\RichText), so the toolbar here is a convenience, not a guard.
 */
import Quill from 'quill';
import 'quill/dist/quill.snow.css';

const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

async function upload(file, url) {
    const body = new FormData();
    body.append('image', file);
    const res = await fetch(url, {
        method: 'POST',
        body,
        headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' },
        credentials: 'same-origin',
    });
    const json = await res.json().catch(() => ({}));
    if (!res.ok) {
        throw new Error(json.errors?.image?.[0] ?? json.message ?? res.statusText);
    }
    return json.url;
}

document.querySelectorAll('[data-rich-editor]').forEach((wrap) => {
    const input = wrap.querySelector('input[type=hidden]');
    const mount = wrap.querySelector('[data-editor-mount]');
    const status = wrap.querySelector('[data-editor-status]');
    const uploadUrl = wrap.dataset.uploadUrl;
    const msg = wrap.dataset;

    const say = (text, error = false) => {
        status.textContent = text;
        status.classList.toggle('text-red-600', error);
    };

    // Upload each file, then insert it where the cursor was.
    const insertImages = async (range, files) => {
        let index = range?.index ?? quill.getLength();
        for (const file of files) {
            if (!/^image\/(jpeg|png|webp)$/.test(file.type)) {
                say(msg.msgType, true);
                continue;
            }
            say(msg.msgUploading);
            try {
                const url = await upload(file, uploadUrl);
                quill.insertEmbed(index, 'image', url, 'user');
                quill.insertText(index + 1, '\n', 'user');
                index += 2;
                say('');
            } catch (e) {
                say(`${msg.msgFailed} ${e.message}`, true);
            }
        }
        quill.setSelection(index, 0, 'silent');
    };

    const quill = new Quill(mount, {
        theme: 'snow',
        placeholder: msg.placeholder,
        formats: ['header', 'bold', 'italic', 'underline', 'strike', 'link', 'blockquote', 'list', 'image'],
        modules: {
            toolbar: {
                container: [
                    [{ header: [2, 3, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ list: 'ordered' }, { list: 'bullet' }, 'blockquote'],
                    ['link', 'image'],
                    ['clean'],
                ],
                handlers: {
                    image() {
                        const picker = document.createElement('input');
                        picker.type = 'file';
                        picker.accept = 'image/jpeg,image/png,image/webp';
                        picker.multiple = true;
                        picker.onchange = () => insertImages(quill.getSelection(true), [...picker.files]);
                        picker.click();
                    },
                },
            },
            // Drag-and-drop / pasted image files → upload instead of base64.
            uploader: { handler: (range, files) => insertImages(range, files) },
            clipboard: {},
        },
    });

    // Never keep pasted base64 images (e.g. copied from another site).
    quill.clipboard.addMatcher('IMG', (node, delta) => {
        const src = node.getAttribute('src') || '';
        return src.startsWith('data:') || !src.includes('/storage/') ? new delta.constructor() : delta;
    });

    // Localized heading labels: CSS reads them from data attributes.
    wrap.classList.add('rich-editor-wrap');
    wrap.querySelectorAll('.ql-header .ql-picker-label, .ql-header .ql-picker-item').forEach((el) => {
        el.dataset.lNormal = msg.lNormal;
        el.dataset.lH2 = msg.lH2;
        el.dataset.lH3 = msg.lH3;
    });

    quill.clipboard.dangerouslyPasteHTML(input.value || '', 'silent');

    wrap.closest('form').addEventListener('submit', () => {
        input.value = quill.getLength() > 1 || quill.root.querySelector('img') ? quill.getSemanticHTML() : '';
    });
});
