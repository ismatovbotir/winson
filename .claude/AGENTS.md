# Winson — agent instructions

Public-facing landing/catalog site for Winson China products (barcode
scanners, smart terminals). Informational/lead-gen, not transactional:
product listings + a way to get in touch about pricing or purchasing. No
accounts, cart, or checkout in this app. See `.claude/winsonchina-reference.md`
for the structure/content model this is based on.

Maintained solo / by a small team (<5). No enforced test/style mandates —
keep changes simple, match existing patterns, don't introduce new tooling
unprompted.

Stack: Laravel 13, PHP 8.3+, Blade + Tailwind CSS v4 (Vite), Livewire for
interactive pieces once actually needed. Mobile-first, server-rendered —
avoid adding a JS framework/SPA layer.

Full project guidance lives in `CLAUDE.md` at the repo root — treat it as the
source of truth; this file is a pointer for non-Claude agents/tools.
