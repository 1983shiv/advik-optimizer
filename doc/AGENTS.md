# AGENTS.md — Advik Optimizer

This file is the standing instruction set for any AI coding agent (Claude Code, or otherwise) working on this repository. Read this file first, every session, before touching code.

---

## 0. Project Identity

- Plugin: **Advik Optimizer** — WordPress performance/SEO plugin.
- Root namespace: `AdvikLabs\Optimizer` (PSR-4, `src/`).
- Prefix conventions (non-negotiable, see doc 1 Appendix A): `advik_optimizer_` functions, `ADVIK_OPTIMIZER_` constants, `advik_optimizer_*` options, `{$wpdb->prefix}advik_*` tables, `advik-optimizer/v1` REST namespace, `manage_advik_optimizer` capability, `advik-optimizer` text domain.

## 1. Reference Documents — read selectively, not all at once

| # | Doc | When to read it |
|---|---|---|
| 1 | `advik-optimizer-spec.md` | PRD/TSD/module map/file specs. Read the relevant module section(s) only, for the phase you're on. This is the architectural source of truth — code must match its namespace tree, MVC boundaries, and SOLID/DRY rules exactly. |
| 2 | `advik-optimizer-ui-design-system.md` | Read the specific screen spec + shared component sections (§2, §3) whenever building or touching any UI. Never invent a color, spacing value, or component outside this doc. |
| 3 | `advik-optimizer-build-phasing.md` | Read at the start of every session to confirm which phase is active and what's in/out of scope for it. **This is the file that tells you what NOT to build yet.** |
| 4 | `advik-optimizer-acceptance-criteria.md` | Read the current phase's checklist before declaring the phase done. A phase isn't complete until every box in its section is checked and verifiable. |
| 5 | `advik-optimizer-copy-deck.md` | Read whenever writing any user-facing string (label, button, error, toast, email). Never write new copy ad hoc — if a needed string is missing from the deck, add it there first, then use it, and flag the addition to the human. |
| 6 | `advik-optimizer-environment-setup.md` | Read once at Phase 0, and any time environment/tooling/CI questions come up. |
| 7 | `advik-optimizer-test-plan.md` | Read the relevant module's test section before writing any Service/Repository — tests are part of Definition of Done, not an afterthought. |
| 8 | `advik-optimizer-legal-compliance.md` | Read when touching: waitlist/data collection, RUM beacon, third-party API calls (PSI, GSC, CDN), readme.txt, or license files. |
| 9 | `advik-optimizer-asset-inventory.md` | Read when a screen needs a real asset (logo, icon, font) — check status here before using a placeholder, and never silently ship a placeholder as final. |
| 10 | `advik-optimizer-wporg-submission-checklist.md` | Read in full only at Phase 9 (Hardening), before any WordPress.org submission. Sections 5–7 (security/code-quality) can be checked incrementally from Phase 1 onward. **Contains a hard blocker: do not submit to WordPress.org until real optimization functionality exists — the Coming-Soon/Waitlist module alone is not a valid submission.** |
| 11 | `advik-optimizer-build-release-process.md` | The **only** sanctioned process for producing a build/zip. Read before any packaging task, every time. Never construct ad hoc `rm`/`mv`/`cp`/`zip` shell commands for this — always run the fixed `npx grunt release` (or `wp dist-archive`) sequence defined there. |

## 2. The One Rule That Matters Most

**Work one phase at a time, per doc 3.** Before writing code, state which phase you're on and what its scope boundary is. If a task would require touching a module or file outside the current phase's scope, stop and flag it rather than "helpfully" expanding scope. Do not start Phase N+1 work until Phase N's checklist in doc 4 is fully satisfied.

## 3. Architectural Non-Negotiables (apply on every file, every phase)

- **MVC boundaries**: Controllers are thin — no `$wpdb`, no business logic. Business logic lives in `Domain\*\Service\`. Views/templates render only; no logic beyond loops/conditionals over already-prepared data.
- **SOLID**: every Service is constructor-injected via the Container (`src/Container/`) — never `new ConcreteClass()` inside another Service. New strategies (encoders, minifiers, CDN adapters, cleanup tasks) implement the relevant interface and register via a `ServiceProvider`, not by modifying existing classes.
- **DRY**: settings fields are defined once in `SettingsRegistry` and consumed by Admin View, REST Schema, and Install defaults — never duplicate a field definition. Shared UI reuses the components in doc 2 §3, never a bespoke one-off.
- **All DB access** goes through `Repository` classes using `$wpdb->prepare()`. All output escaped at the View boundary only.
- **All new REST routes** require capability + nonce checks (except the two explicitly public waitlist routes, which require rate-limit + honeypot per doc 1 §2.15/2.16).

## 4. Definition of Done (every phase, in addition to phase-specific criteria in doc 4)

- [ ] `composer lint` passes with zero errors.
- [ ] `composer test` passes, with new unit/integration tests added per doc 7 for every new Service/Repository.
- [ ] All new UI matches a screen spec in doc 2, using only its tokens/components.
- [ ] All new copy matches doc 5, or was added there first.
- [ ] Feature is demoable on a clean `wp-env` install with no manual DB edits.
- [ ] Nothing outside the current phase's scope (doc 3) was touched.

## 5. Session Workflow

1. State current phase (from doc 3) and confirm prior phase's Definition of Done was met.
2. Pull only the doc sections relevant to this phase's scope — quote or reference them, don't restate the whole document.
3. Implement.
4. Run `composer lint && composer test` locally before presenting work as complete.
5. Walk through the phase's checklist in doc 4 explicitly, item by item.
6. Report anything that had to deviate from the docs (missing spec, ambiguous requirement, asset not yet available per doc 9) rather than silently improvising — flag it back to the human.

## 6. Things to Never Do

- Never invent a new namespace, prefix, or naming pattern not already established in doc 1.
- Never add a third-party dependency without checking GPL-compatibility (doc 8 §2).
- Never ship a placeholder logo/font/icon as if it were final without flagging it against doc 9's open decisions.
- Never skip writing tests "to move faster" — untested Service/Repository code is not Done per §4 above.
- Never collect or transmit user data (RUM, waitlist, GSC) without checking doc 8's functional requirements are also implemented, not just documented.
- Never lock functionality behind a license/key check where the underlying logic already runs fully locally (doc 10 §2) — if a paid tier is ever introduced, flag it for explicit review against doc 10 before implementing.
- Never treat the Coming-Soon/Waitlist module (Phase 8) as a WordPress.org-submittable plugin on its own — it ships as an AdvikLabs.com page or an in-plugin cross-sell only, per doc 10 §0.

## 7. WordPress.org Submission Gate

Before any Phase 9 "ready to submit" claim, run doc 10 in full and report the results item by item — do not mark Phase 9 done without walking through that checklist explicitly.

## 8. Safety & Git Discipline (non-negotiable, applies to every session)

- **Commit after every meaningful unit of work** — every completed file, every passing test, not just at the end of a phase. A commit costs seconds; uncommitted work is unrecoverable if something goes wrong.
- **Push to the remote regularly**, not just local commits — local disk issues or a bad destructive command don't touch what's already on GitHub.
- **Always run `git add -A && git commit -m "checkpoint"` immediately before any packaging, build, zip, or bulk file operation** — see doc 11 §5, Step 0. This is mandatory, not optional, every time.
- **Never construct ad hoc destructive shell commands** (`rm -rf`, `mv`, bulk `cp`, `find -delete`) for packaging or cleanup. Packaging goes exclusively through the fixed process in doc 11. If a genuine one-off destructive command is ever truly necessary, state the exact command in full, explain its scope, and get explicit human confirmation before running it — never assume approval.
- **If a build or command fails partway, do not "clean up and retry" with improvised deletions.** Re-checkpoint with git first, then re-run the fixed, documented process from the start.
- **If asked to delete files**, confirm the exact path(s) and scope explicitly before running anything, and prefer moving to a clearly-named backup location over permanent deletion where practical.
