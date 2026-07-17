# Advik Optimizer — UI Design System
## Cross-Screen Visual & Interaction Specification

**Companion to:** `advik-optimizer-spec.md` (PRD/TSD)
**Scope:** Every screen the plugin renders — public marketing (coming-soon), onboarding, wp-admin dashboard, settings, and utility views.
**Goal:** One visual language, applied without exception, so no screen ever looks like it was bolted on later.

---

## 1. Design Principles (the 5 rules every screen must follow)

1. **Two contexts, one identity.** The plugin lives in two visual contexts — the public marketing surface (full brand freedom, dark canvas) and the wp-admin surface (must sit inside WordPress's own chrome, light canvas). Both use the *same* accent color, type scale ratios, radius, and iconography — only the base canvas changes. Nobody should be able to screenshot two screens and doubt they're the same product.
2. **Status is color-coded, never color-only.** Green/amber/red always pair with an icon or label. Color communicates at a glance; text confirms for accessibility.
3. **Numbers are the hero.** This is a performance product — scores, percentages, byte counts, and milliseconds are the content. Every screen gives its primary number the most visual weight on the page; nothing decorative competes with it.
4. **One accent, used sparingly.** The brand green appears on: the primary action, active/live states, and positive-trend indicators. It never becomes a background wash or a decorative flourish — scarcity is what makes it register as "good news" when it appears.
5. **Every state is designed, not left to default.** Loading, empty, error, and success states are specified per screen below — never a spinner-and-hope.

---

## 2. Design Tokens

### 2.1 Color

| Token | Hex | Usage |
|---|---|---|
| `--advik-ink-900` | `#0B0E11` | Marketing/coming-soon canvas background |
| `--advik-ink-800` | `#12161B` | Marketing surface cards |
| `--advik-ink-700` | `#1B2127` | Marketing borders/dividers |
| `--advik-mist-100` | `#F6F8F7` | wp-admin page background (replaces WP default `#F0F0F1` with a cooler, branded neutral) |
| `--advik-mist-0` | `#FFFFFF` | wp-admin card background |
| `--advik-border` | `#E3E8E6` | wp-admin card borders / dividers |
| `--advik-green-500` | `#1FCB8E` | Primary brand accent — "performance green" (CTA, live status, positive trend, score-ring fill) |
| `--advik-green-600` | `#17A876` | Accent hover/pressed state |
| `--advik-green-100` | `#E4F9F1` | Accent tint — success chip backgrounds |
| `--advik-amber-500` | `#F5A524` | Warning status (score 50–89, cache miss spikes) |
| `--advik-amber-100` | `#FDF2DE` | Warning tint backgrounds |
| `--advik-red-500` | `#EF4444` | Critical status (score <50, failed job, destructive action) |
| `--advik-red-100` | `#FDECEC` | Critical tint backgrounds |
| `--advik-text-900` | `#12161B` | Primary text (on light) |
| `--advik-text-600` | `#5B6570` | Secondary text (on light) |
| `--advik-text-inverse-100` | `#F6F8F7` | Primary text on dark canvas |
| `--advik-text-inverse-600` | `#9BA5AC` | Secondary text on dark canvas |

Rule: green is a **signal**, not a theme. If more than ~10% of a screen's pixels are green, that's a violation — pull it back to a chip, a ring segment, or a button.

### 2.2 Typography

| Role | Typeface | Notes |
|---|---|---|
| Display / Headlines (marketing only) | **Söhne** (or system fallback: `"Inter", -apple-system, sans-serif` at weight 650) | Used only on coming-soon page hero. Tight tracking (-0.02em), large scale. |
| UI / Body (all screens) | **Inter** | The workhorse — every wp-admin label, table cell, button, and form field. |
| Numeric / Data | **Inter, tabular-nums** | All scores, percentages, byte/ms values use `font-variant-numeric: tabular-nums` so columns of numbers align and don't jitter on live update. |

Type scale (applies everywhere; marketing hero scales up from the same ratio):

| Token | Size / Line-height | Usage |
|---|---|---|
| `--type-hero` | 48px / 1.05 | Coming-soon headline only |
| `--type-display` | 32px / 1.15, tabular-nums | Score-ring big numbers, dashboard headline stat |
| `--type-h1` | 22px / 1.3 | Page title (e.g., "Optimizer Overview") |
| `--type-h2` | 16px / 1.4, weight 600 | Card titles ("Page Load Trend") |
| `--type-body` | 14px / 1.5 | Default UI text |
| `--type-caption` | 12px / 1.4 | Helper text, timestamps, table meta |
| `--type-label` | 11px / 1.2, weight 600, letter-spacing 0.04em, uppercase | Eyebrow labels ("Performance", "Live") |

### 2.3 Spacing & Grid

- Base unit: **4px**. All padding/margin/gap values are multiples of 4 (4, 8, 12, 16, 24, 32, 48).
- wp-admin content max-width: **1200px**, cards laid out on a 12-column grid, 24px gutter.
- Card internal padding: **20px** (mobile/narrow admin sidebar collapsed: 16px).
- Section vertical rhythm: **32px** between major dashboard sections.

### 2.4 Radius, Elevation, Border

| Token | Value | Usage |
|---|---|---|
| `--radius-sm` | 6px | Buttons, chips, inputs |
| `--radius-md` | 10px | Cards |
| `--radius-lg` | 16px | Score-ring container, modals |
| `--radius-pill` | 999px | Status badges ("Live"), toggle switches |
| `--shadow-card` | `0 1px 2px rgba(18,22,27,0.04), 0 1px 8px rgba(18,22,27,0.04)` | Default card elevation on light canvas |
| `--shadow-card-dark` | `0 1px 24px rgba(31,203,142,0.08)` | Marketing card glow, used once per page max |
| Border | 1px solid `--advik-border` (light) / `--advik-ink-700` (dark) | Default card outline — cards use border, not shadow-only, so they read crisply inside wp-admin's flat aesthetic |

### 2.5 Iconography & Motion

- Icon set: single consistent line-icon set (Lucide-style, 1.5px stroke, 20px default size) across every screen — never mix icon styles between marketing and admin.
- Score rings and trend arrows are the only place motion is used: rings animate fill on first paint (600ms ease-out), numbers count up (400ms), trend arrows fade+slide in (200ms). No animation on static content, no looping/ambient motion in wp-admin (respects `prefers-reduced-motion` everywhere).

---

## 3. Core Components (shared across every screen)

Each component is built once and reused — this is the DRY principle applied to UI, mirroring the plugin's own architecture.

### 3.1 Score Ring
Circular progress ring, 4 instances shown together (Performance / SEO / Accessibility / Best Practices).
- Size: 96px diameter (dashboard), 56px (compact/settings summary).
- Ring color by value: ≥90 → green-500, 50–89 → amber-500, <50 → red-500.
- Center: `--type-display` number, `--type-caption` label below ring (outside, not inside — keeps the ring uncluttered).
- Empty/loading state: ring track only (no fill), center shows a skeleton bar, not "0".

### 3.2 Metric Delta Card
Used for LCP / CLS / INP tiles.
- Layout: label (`--type-label`) top-left, delta badge top-right (▼ green if improving, ▲ red if regressing — for CWV, lower is better so arrow-color logic is inverted from typical dashboards; encode this explicitly per metric, don't assume "up = good"), big current value (`--type-display`), a 7-day micro-sparkline beneath.
- Delta badge: pill, `--advik-green-100` bg + `--advik-green-600` text (improving) or `--advik-red-100`/`--advik-red-500` (regressing).

### 3.3 Stat Tile (Cache Hit Rate / Images Saved / JS-CSS Reduced)
- Flat card, icon top-left (green, 20px), value `--type-display`, label `--type-caption` beneath, no chart — these are cumulative "savings" stats, kept visually quieter than the score ring and CWV cards so hierarchy stays: Scores > CWV Trend > Savings Stats.

### 3.4 Status Badge
- `Live` badge: pill, green-100 bg, green-600 text, small filled dot (pulses once on state change, then static — no continuous pulse animation).
- `Pending` / `Processing` / `Failed` / `Restored` (image queue, DB cleanup): same pill shape, amber/gray/red/gray respectively.

### 3.5 Buttons
- Primary: solid `--advik-green-500` bg, white text, `--radius-sm`, used once per screen for the single most important action (e.g., "Run Optimization", "Notify Me").
- Secondary: white bg, 1px border, dark text — everything else.
- Destructive: white bg, red-500 border+text, only for DB cleanup "Run live" and "Restore original" type actions — always paired with a confirmation modal (per TSD 2.14/2.16 dry-run defaults).
- Disabled: 40% opacity, no hover state, cursor not-allowed.

### 3.6 Tabs (Settings navigation)
- Underline-style tabs, not boxed — matches WP-admin's native visual weight while using brand green for the active underline + active label color.

### 3.7 Toggle Switch
- Used for every on/off setting (module enable, exclusion rules). Track: gray-300 off / green-500 on. Always paired with an inline label to the *left* and, where the setting is non-obvious, a `(?)` tooltip — never a bare switch with no label.

### 3.8 Empty / Loading / Error States (must exist on every data screen)
- **Loading:** skeleton blocks matching the exact shape of the real content (skeleton score rings, skeleton table rows) — never a centered spinner replacing a whole card.
- **Empty:** icon + one sentence stating what will appear here and what triggers it ("No scans yet. Your first scan runs automatically within 5 minutes of activation.") — never just "No data."
- **Error:** icon + plain statement of what failed + a retry action in the interface's voice ("Scan failed to complete. Retry scan.") — never a raw error code or apology.

### 3.9 Data Table (Image Queue, DB Cleanup Log, Waitlist Export)
- Row height 44px, zebra-free (relies on 1px row dividers, not background banding, to match WP-admin's native table convention), status column always uses the Status Badge component, right-aligned numeric columns with tabular-nums.

### 3.10 Modal / Confirmation Dialog
- Used for: cache purge confirmation, DB cleanup "run live" confirmation, bulk image optimize start.
- Structure: `--radius-lg`, title (`--type-h1`), one sentence of consequence in plain language, primary+secondary button pair, destructive variant swaps primary button to the Destructive style.

---

## 4. Screen-by-Screen Specification

### Screen 1 — Coming Soon / Waitlist (Public, pre-launch)
**Canvas:** Dark (`--advik-ink-900`), full-bleed, centered single-column content, max-width 640px.
**Purpose:** Capture email, communicate the product's value in one glance, cross-sell AdvikLabs catalog.

Layout (top to bottom):
1. AdvikLabs logo, small, top-left, always links to `advik-labs.com` — the only nav element on the page.
2. Eyebrow label: "COMING SOON · WORDPRESS PLUGIN" (`--type-label`, `--advik-green-500`).
3. Headline (`--type-hero`): the value proposition, one sentence, plain language ("Supercharge your WordPress site speed and SEO").
4. Subhead (`--type-body`, `--advik-text-inverse-600`): 1–2 sentences max, no jargon.
5. **Signature element:** a live-look mock of the Dashboard Overview screen (Screen 3), shown as a static, slightly-scaled browser-chrome frame with the actual score rings and stat tiles from this design system — this is deliberate: the marketing page previews the real UI the user will get, not a generic illustration. This is what should be memorable about the page.
6. Email capture form: single input + primary button ("Notify Me"), inline below it in `--type-caption`: "No spam, unsubscribe anytime." GDPR consent checkbox required, small, unchecked by default, label states plainly what the checkbox does.
7. Footer link: "See All Products" (secondary button style, links to AdvikLabs catalog).

States: submit → button shows inline loading (label swaps to "Sending…", not a separate spinner) → success replaces the form with a green-100/ink-800 confirmation chip: "Check your inbox to confirm." Error (rate-limited/invalid) shows inline red text under the input, form stays filled.

### Screen 2 — Onboarding Wizard (first admin visit after activation)
**Canvas:** Light, but full-screen takeover (no WP-admin sidebar/menu chrome) — this is the one admin screen allowed to feel like the marketing surface, bridging the two contexts.
**Steps:** (1) Site type — 4-5 cards (WooCommerce / Blog-Publisher / Agency / Portfolio-SaaS) each with an icon + one-line description, selecting applies the matching preset from PRD Appendix B. (2) Confirmation screen showing exactly which modules will be enabled and their defaults, in plain language, with a link to "Customize before activating" that drops into Settings instead of accepting defaults. (3) Completion screen with a primary button straight into the Dashboard.
Progress indicator: 3 dots/segments at top, filled left-to-right — this is a real sequence, so numbering/progress is appropriate here (unlike decorative uses elsewhere).

### Screen 3 — Dashboard Overview (`admin.php?page=advik-optimizer`)
**Canvas:** Light, standard wp-admin content area.
Top bar: page title "Optimizer Overview" (`--type-h1`) + system status line ("All systems green · Last scan: 2m ago") + `Live` badge, right-aligned "Run Scan Now" secondary button.

Section A — **Score Rings row**: 4 Score Ring components (Performance, SEO, Accessibility, Best Practices) in a single row, equal width, this is the top of the visual hierarchy on the page — largest, first.

Section B — **Page Load Trend card**: full-width card, title "Page Load Trend" + "42ms avg" as a supporting stat top-right, containing 3 Metric Delta Cards side by side (LCP, CLS, INP) as specified in 3.2.

Section C — **Optimizations Active row**: title "Optimizations Active", 3 Stat Tiles (Cache Hit Rate, Images Saved, JS/CSS Reduced) as specified in 3.3.

Section D — **Built for Every Workload** (only shown pre-first-scan or as a persistent low-emphasis footer strip): the workload-type chips (WooCommerce Stores, News & Publishing, etc.) — rendered as quiet outline chips, not cards; this is context/credibility, not primary content, so it must visually rank below Sections A–C at all times.

Loading state: Section A shows 4 skeleton rings; Sections B/C show skeleton bars — page never blocks entirely on one slow metric.

### Screen 4 — Settings: Cache
Tabs (per component 3.6) across the top of every Settings screen: Cache · Images · Minify · Core Web Vitals · SEO · CDN & Database — identical tab bar, only the active tab and its panel content change, reinforcing "one product."
Panel: grouped setting rows (Toggle + label + optional tooltip per 3.7), grouped under sub-headings ("Exclusion Rules", "Warming"). "Purge Cache Now" as a Secondary button (not destructive-styled — purge is safe/reversible) opens confirmation Modal only if scope = "entire site"; per-URL purge is instant with a toast, no modal.

### Screen 5 — Settings: Images
Panel top: quality slider (visual, with a live before/after size estimate readout in tabular-nums) + format toggle (WebP/AVIF/Both). Below: "Bulk Optimize Media Library" primary button → opens the **Image Queue** table (component 3.9) inline, with per-row Status Badges (Pending/Processing/Done/Failed/Restored) and a "Restore" secondary-text-link action per completed row.

### Screen 6 — Settings: Minify
Panel: per-asset-type toggles (CSS / JS / HTML), an "Exclusion List" section using a simple tag-input pattern (type a handle, press enter, it becomes a removable chip — reuses the Status Badge pill shape at neutral gray). Critical CSS sub-section shows last-scan timestamp + "Rescan" secondary button + a small warning callout (amber-100 background, `--advik-amber-500` icon) explaining safe-mode rollback behavior in plain language.

### Screen 7 — Settings: Core Web Vitals
Panel: RUM sampling rate slider (with plain-language explanation of what sampling means for their traffic), alert threshold inputs per metric (LCP/CLS/INP), and delivery channel toggles (Email / Webhook) each revealing an inline field only when enabled (progressive disclosure — never show an empty webhook URL field until the toggle is on).

### Screen 8 — Settings: SEO
If conflict detected (Yoast/RankMath/AIOSEO active): the entire panel is replaced by a single centered notice (icon + explanation + link to the other plugin's settings) — this is a designed empty/disabled state per 3.8, not a grayed-out form.
If no conflict: template fields per post type (accordion list), schema type toggles, Search Console connect button (OAuth) shown as a Secondary button with the GSC icon, connected state shows a green Status Badge + the connected account email + "Disconnect" text link.

### Screen 9 — Settings: CDN & Database
Two sub-sections in one panel: **CDN** (origin URL field, adapter dropdown, "Test Connection" secondary button with inline success/fail state), **Database Cleanup** (per-task toggles with a "Preview" secondary button next to each that opens a Modal showing the dry-run CleanupReport as a small table before any live run is possible — live "Run Now" button is Destructive-styled and disabled until at least one Preview has been run in that session, enforcing the dry-run-first pattern from the TSD).

### Screen 10 — WP-CLI / REST consumers
No custom UI — covered here only to note that any agency-facing external dashboard consuming the REST API should be told to reuse these same tokens (color thresholds, score-ring logic) if AdvikLabs ever ships a white-label view, so third-party renderings stay visually consistent with the plugin itself.

---

## 5. Consistency Checklist (apply before shipping any new screen)

- [ ] Uses only tokens from Section 2 — no one-off hex values or font sizes.
- [ ] Green appears on ≤1 primary action and status indicators only.
- [ ] Every score/metric uses tabular-nums and the correct threshold-to-color mapping (≥90 green / 50–89 amber / <50 red).
- [ ] Loading, empty, and error states are explicitly designed, not default browser/WP behavior.
- [ ] Any destructive action has a confirmation Modal and Destructive button styling.
- [ ] Tab bar (if present) matches Screen 4's tab bar exactly — same order, same active-state treatment.
- [ ] All copy is written in active voice, plain language, from the user's side of the screen (per writing guidance) — no "submit," no system-internal terms like "webhook config" where "notification link" would do.
- [ ] Keyboard focus is visible on every interactive element; reduced-motion is respected on rings/counters.
