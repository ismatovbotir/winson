/*
 * Instant site search (partials/search-dialog.blade.php).
 * Combobox pattern: focus stays in the input, ↑/↓ move aria-activedescendant
 * through the listbox options, Enter opens the active one (or submits the
 * form → full results page), Esc closes the dialog.
 * Results are built with DOM APIs (textContent) — never innerHTML from data.
 */
const dialog = document.querySelector('[data-search]');

if (dialog) {
    const input = dialog.querySelector('[data-search-input]');
    const form = dialog.querySelector('[data-search-form]');
    const list = dialog.querySelector('[data-search-list]');
    const start = dialog.querySelector('[data-search-start]');
    const status = dialog.querySelector('[data-search-status]');
    const clearBtn = dialog.querySelector('[data-search-clear]');
    const recentBox = dialog.querySelector('[data-search-recent]');
    const recentList = dialog.querySelector('[data-search-recent-list]');
    const msg = dialog.dataset;
    const cache = new Map();
    const RECENT_KEY = 'winson.search.recent';
    const MIN = 2;
    let controller = null;
    let timer = null;
    let active = -1;
    let opener = null;

    // ---------- recent searches (per-browser convenience; storage may be blocked) ----------
    const readRecent = () => {
        try { return JSON.parse(localStorage.getItem(RECENT_KEY)) || []; } catch { return []; }
    };
    const saveRecent = (q) => {
        q = q.trim();
        if (q.length < MIN) return;
        try {
            const items = [q, ...readRecent().filter((x) => x.toLowerCase() !== q.toLowerCase())].slice(0, 6);
            localStorage.setItem(RECENT_KEY, JSON.stringify(items));
        } catch { /* ignore */ }
    };
    const renderRecent = () => {
        const items = readRecent();
        recentBox.hidden = items.length === 0;
        recentList.replaceChildren(...items.map((q) => {
            const li = document.createElement('li');
            const b = document.createElement('button');
            b.type = 'button';
            b.className = 'inline-flex items-center gap-1.5 rounded-full bg-canvas-alt px-3 py-1.5 text-sm text-navy transition hover:bg-accent-soft';
            b.textContent = '↺ ' + q;
            b.addEventListener('click', () => { input.value = q; onInput(); input.focus(); });
            li.append(b);
            return li;
        }));
    };
    dialog.querySelector('[data-search-recent-clear]').addEventListener('click', () => {
        try { localStorage.removeItem(RECENT_KEY); } catch { /* ignore */ }
        renderRecent();
        input.focus();
    });

    // ---------- open / close ----------
    const open = () => {
        if (dialog.open) return;
        opener = document.activeElement;
        renderRecent();
        dialog.showModal();
        input.focus();
        input.select();
    };
    const close = () => dialog.open && dialog.close();
    dialog.addEventListener('close', () => opener?.focus?.());
    dialog.querySelector('[data-search-close]').addEventListener('click', close);
    // Click on the backdrop (outside the panel) closes.
    dialog.addEventListener('click', (e) => { if (e.target === dialog) close(); });

    document.querySelectorAll('[data-search-open]').forEach((b) => b.addEventListener('click', open));
    document.addEventListener('keydown', (e) => {
        const typing = /^(INPUT|TEXTAREA|SELECT)$/.test(e.target.tagName) || e.target.isContentEditable;
        if ((e.key === '/' && !typing) || ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k')) {
            e.preventDefault();
            open();
        }
    });

    // ---------- rendering ----------
    const tokensOf = (q) => q.toLowerCase().split(/\s+/).filter((t) => t.length >= MIN);
    const escapeRe = (s) => s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');

    /** Append `text` to `el`, wrapping query words in <mark>. */
    const highlight = (el, text, tokens) => {
        if (!tokens.length) { el.textContent = text; return; }
        const re = new RegExp(`(${tokens.map(escapeRe).join('|')})`, 'gi');
        text.split(re).forEach((part, i) => {
            if (i % 2) {
                const m = document.createElement('mark');
                m.className = 'rounded-sm bg-accent-soft px-0.5 text-navy';
                m.textContent = part;
                el.append(m);
            } else if (part) {
                el.append(document.createTextNode(part));
            }
        });
    };

    const options = () => [...list.querySelectorAll('[role=option]')];
    const setActive = (i) => {
        const opts = options();
        if (!opts.length) return;
        active = (i + opts.length) % opts.length;
        opts.forEach((o, n) => {
            o.setAttribute('aria-selected', String(n === active));
            o.classList.toggle('bg-accent-soft', n === active);
        });
        input.setAttribute('aria-activedescendant', opts[active].id);
        opts[active].scrollIntoView({ block: 'nearest' });
    };

    const showState = (state, text = '') => {
        start.hidden = state !== 'start';
        list.hidden = state !== 'results';
        status.hidden = !text;
        status.textContent = text;
        input.setAttribute('aria-expanded', String(state === 'results'));
        if (state !== 'results') {
            active = -1;
            input.removeAttribute('aria-activedescendant');
        }
    };

    const render = (data, q) => {
        const tokens = tokensOf(q);
        list.replaceChildren();
        active = -1;
        let n = 0;

        if (!data.total) {
            showState('none', msg.msgNone.replace(':query', q));
            return;
        }

        data.groups.forEach((group) => {
            const head = document.createElement('li');
            head.setAttribute('role', 'presentation');
            head.className = 'px-5 pb-1 pt-3 font-mono text-[11px] uppercase tracking-[0.16em] text-ink-soft';
            head.textContent = group.label;
            list.append(head);

            group.items.forEach((item) => {
                const li = document.createElement('li');
                li.id = `search-opt-${n++}`;
                li.setAttribute('role', 'option');
                li.setAttribute('aria-selected', 'false');
                li.className = 'mx-2 rounded-lg';

                const a = document.createElement('a');
                a.href = item.url;
                a.tabIndex = -1;
                a.className = 'flex items-center gap-3 px-3 py-2.5';
                a.addEventListener('click', () => saveRecent(q));

                const thumb = document.createElement('span');
                thumb.className = 'flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-md bg-canvas-alt';
                if (item.image) {
                    const img = document.createElement('img');
                    img.src = item.image;
                    img.alt = '';
                    img.loading = 'lazy';
                    img.className = 'max-h-full max-w-full object-contain';
                    thumb.append(img);
                }

                const text = document.createElement('span');
                text.className = 'min-w-0 flex-1';
                const title = document.createElement('span');
                title.className = 'block truncate font-medium text-navy';
                highlight(title, item.title, tokens);
                text.append(title);
                if (item.meta) {
                    const meta = document.createElement('span');
                    meta.className = 'block truncate text-xs text-ink-soft';
                    meta.textContent = item.meta;
                    text.append(meta);
                }

                const arrow = document.createElement('span');
                arrow.className = 'text-ink-soft';
                arrow.setAttribute('aria-hidden', 'true');
                arrow.textContent = '→';

                a.append(thumb, text, arrow);
                li.append(a);
                li.addEventListener('mousemove', () => setActive(options().indexOf(li)));
                list.append(li);
            });
        });

        // "See all results" — also an option, so it's reachable by keyboard.
        const all = document.createElement('li');
        all.id = `search-opt-${n}`;
        all.setAttribute('role', 'option');
        all.setAttribute('aria-selected', 'false');
        all.className = 'mx-2 mt-2 rounded-lg border-t border-line';
        const allLink = document.createElement('a');
        allLink.href = data.all_url;
        allLink.tabIndex = -1;
        allLink.className = 'block px-3 py-3 text-sm font-semibold text-accent-ink';
        allLink.textContent = `${msg.msgAll} (${data.total}) →`;
        allLink.addEventListener('click', () => saveRecent(q));
        all.append(allLink);
        all.addEventListener('mousemove', () => setActive(options().indexOf(all)));
        list.append(all);

        showState('results');
    };

    // ---------- fetching ----------
    const fetchResults = async (q) => {
        if (cache.has(q)) { render(cache.get(q), q); return; }
        controller?.abort();
        controller = new AbortController();
        showState(list.hidden ? 'loading' : 'results', list.hidden ? msg.msgLoading : '');
        try {
            const url = new URL(dialog.dataset.suggestUrl, location.origin);
            url.searchParams.set('q', q);
            const res = await fetch(url, { signal: controller.signal, headers: { Accept: 'application/json' } });
            if (!res.ok) throw new Error(res.status);
            const data = await res.json();
            cache.set(q, data);
            if (input.value.trim() === q) render(data, q);
        } catch (e) {
            if (e.name !== 'AbortError') showState('none', msg.msgError);
        }
    };

    const onInput = () => {
        const q = input.value.trim();
        clearBtn.hidden = input.value === '';
        clearTimeout(timer);
        if (q.length < MIN) {
            controller?.abort();
            renderRecent();
            showState('start');
            return;
        }
        timer = setTimeout(() => fetchResults(q), 160);
    };

    input.addEventListener('input', onInput);
    clearBtn.addEventListener('click', () => { input.value = ''; onInput(); input.focus(); });

    input.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowDown') { e.preventDefault(); setActive(active + 1); }
        else if (e.key === 'ArrowUp') { e.preventDefault(); setActive(active - 1); }
        else if (e.key === 'Enter' && active >= 0) {
            e.preventDefault();
            saveRecent(input.value);
            options()[active].querySelector('a').click();
        }
    });

    form.addEventListener('submit', (e) => {
        if (input.value.trim().length < MIN) { e.preventDefault(); return; }
        saveRecent(input.value);
    });
}
