# Advik Optimizer — Asset Inventory

**Purpose:** Every real file (not placeholder) needed to build the screens in the UI Design System. Nothing in this list should stay as a Lorem-ipsum/mock asset in the final build.

---

## 1. Brand Assets

| Asset | Spec | Status | Owner action needed |
|---|---|---|---|
| AdvikLabs logo (SVG, full color) | Vector, transparent background, for light canvas use | Missing | Provide existing AdvikLabs logo file, or confirm one needs designing |
| AdvikLabs logo (SVG, light/inverse) | For dark canvas (coming-soon page) | Missing | Same as above — derive inverse version once base logo exists |
| Advik Optimizer product mark (if distinct from AdvikLabs logo) | Optional — plugin may just use "AdvikLabs" wordmark + "Optimizer" text, per PRD mock | Decision needed | Confirm whether Optimizer needs its own sub-mark or rides on the parent brand |
| Favicon / plugin icon (WordPress.org listing) | 128×128 and 256×256 PNG | Missing | Required for WordPress.org submission; derive from logo |
| Banner images (WordPress.org listing) | 772×250 and 1544×500 PNG/JPG | Missing | Required for WordPress.org submission page |

## 2. Iconography

| Asset | Spec | Status |
|---|---|---|
| Icon set license confirmation | Lucide (or equivalent MIT-licensed line-icon set) per UI Design System §2.5 | Needs explicit confirmation before bundling (see Legal & Compliance §2) |
| Icon subset actually used | Enumerate exact icons needed: cache, image, minify/code, vitals/speedometer, SEO/search, CDN/globe, database, checkmark, warning, error, close, chevron (tabs), toggle states, export/download | Build list as screens are implemented — don't bundle the entire icon library, only what's used (keeps asset size DRY) |

## 3. Screenshots (for WordPress.org + coming-soon page signature element)

| Asset | Source | Status |
|---|---|---|
| Dashboard Overview screenshot | Real screenshot of Screen 3 once built, not a mockup | Depends on Phase 2 completion |
| Settings: Cache screenshot | Real screenshot of Screen 4 | Depends on Phase 1 |
| Image Queue screenshot | Real screenshot of Screen 5 | Depends on Phase 3 |
| Coming-soon page's "signature element" mock (per UI Design System §4, Screen 1) | Must be the *real* dashboard UI once it exists, scaled into a browser-chrome frame — not a generic illustration | Depends on Phase 2 — coming-soon page copy can ship early, but the signature visual should be swapped in once real | 

## 4. Email Templates (Waitlist module, Phase 8)

| Asset | Status |
|---|---|
| Confirmation email HTML template | Needs building — plain, on-brand, matches Copy Deck §2 exactly, single CTA button styled with `--advik-green-500` |
| Launch-announcement email template (future use, not v1-blocking) | Not needed until actual launch |

## 5. Typography Licensing

| Asset | Spec | Status |
|---|---|---|
| Display face (marketing hero only) | UI Design System specifies "Söhne" with system fallback to Inter 650 weight | Söhne is a commercial license (Klim Type Foundry) — confirm budget/license before committing, or formally adopt the Inter-fallback as the actual choice to avoid a licensing cost. **Recommendation: default to Inter at weight 650/700 for the hero and drop Söhne unless there's budget for it** — flagging this as a decision, not proceeding on the assumption of a paid license. |
| Body/UI face (Inter) | Open-source (SIL Open Font License), free to bundle or load from Google Fonts / bundle locally | Clear — bundle locally in `assets/admin/dist/fonts/` to avoid a Google Fonts external request inside wp-admin (performance-plugin shipping an external font request would be ironic) |

## 6. Legal/Compliance-Adjacent Assets (cross-ref: Legal & Compliance doc)

| Asset | Status |
|---|---|
| `LICENSE.txt` (GPLv2+) | Needs drafting — standard boilerplate, low effort |
| `readme.txt` | Needs drafting per WordPress.org format (see Legal & Compliance §1) |
| Privacy policy copy (plugin disclosure + AdvikLabs.com) | Needs drafting + legal review, see Legal & Compliance §3 |

## 7. Open Decisions Blocking Asset Finalization

1. Does AdvikLabs already have a logo, or does one need to be designed first? (Blocks Section 1 entirely.)
2. Confirm Söhne vs. Inter-only for the hero typeface — recommend resolving this now since it affects the coming-soon page, which is the first thing to ship (Phase 8, but can run in parallel with Phase 0).
3. Confirm final icon set choice and license before any screen implementation begins (low cost to decide now, expensive to swap later across every screen).
4. Who is producing the WordPress.org banner/screenshot artwork — is this Shiv directly (per his existing CapCut/content workflow) or does it need outsourcing?

---

**How to use this doc:** Treat it as a punch list. Nothing here blocks Phase 0–7 code work (those phases don't need final assets, they can use the exact color/type tokens with placeholder logo text). It does block: the coming-soon page going live publicly, and WordPress.org submission — flag both as gated on this checklist being clear.
