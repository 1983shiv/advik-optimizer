# Advik Optimizer — Legal & Compliance Minimums

**Purpose:** The non-code deliverables required before this plugin can be distributed (WordPress.org or self-hosted) or collect any personal data (waitlist emails, RUM analytics). This is not legal advice — Shiv should have a lawyer or a service like Termageddon/iubenda review final documents before public launch, especially for GDPR/CCPA obligations. This doc defines what needs to exist and roughly what it must cover.

---

## 1. Required Files for WordPress.org Distribution

| File | Purpose | Notes |
|---|---|---|
| `readme.txt` | WordPress.org listing page content | Must follow the [WordPress readme standard](https://wordpress.org/plugins/readme.txt) format exactly (Stable tag, Tested up to, Requires PHP, etc. headers) — malformed headers are a common rejection reason |
| `LICENSE.txt` | Full plugin license text | Must be **GPLv2 or later** (or a GPL-compatible license) — WordPress.org requires this; the plugin cannot be released there under a proprietary/closed license |
| Screenshots | `screenshot-1.png`, `screenshot-2.png`, etc. + descriptions in `readme.txt` | Should show the Dashboard, a Settings screen, and the Image Queue at minimum — reuse the actual UI Design System screens, not mockups |
| Plugin header block (in `advik-optimizer.php`) | Name, URI, Description, Version, Author, License, Text Domain | Must match `readme.txt` exactly or the review tooling flags a mismatch |

## 2. GPL Compliance Notes

- Any third-party PHP library bundled (minifiers, Action Scheduler, etc.) must itself be GPL-compatible. Before Phase 4 (Minify) and Phase 1 (Action Scheduler dependency), verify each library's license explicitly — MIT/BSD/Apache-2.0 are all GPL-compatible for this purpose; anything with a "no commercial use" or similarly restrictive clause is not usable.
- Bundled fonts/icons (if any ship inside the plugin rather than loaded from a CDN) need the same license check — the Lucide-style icon set referenced in the UI Design System is MIT-licensed and safe to bundle; confirm the exact set chosen before shipping.

## 3. Privacy Policy Content (site-owner-facing)

Since this plugin processes visitor data (RUM beacon) and collects site-owner data (waitlist emails, GSC OAuth tokens), it needs a **plugin privacy disclosure** that site owners can paste into their own site's privacy policy, plus the AdvikLabs.com privacy policy for the waitlist itself. Minimum content:

**For the plugin's own privacy disclosure (ships in `readme.txt` "Privacy" section, per WordPress.org convention):**
- What the RUM beacon collects (LCP/CLS/INP timing values, no PII, sampling rate configurable/disclosed) and where it's stored (site's own database — data does not leave the site unless the site owner enables an external webhook/lab-scan API).
- That the PageSpeed Insights lab-scan integration sends the site's public URL to Google's API when enabled — this is third-party data sharing and must be disclosed explicitly.
- That Search Console integration requests OAuth access to the site owner's GSC account — scope and what's read/written must be disclosed.
- Data retention: how long CWV metrics/cache logs are kept before automatic pruning (define a number, e.g., 90 days, and implement the pruning — undefined retention is a compliance gap).

**For AdvikLabs.com's own privacy policy (covers the waitlist):**
- What's collected (email, consent timestamp, hashed IP for rate-limiting/abuse prevention — never raw IP, per TSD §2.6.2 `advik_waitlist` schema).
- Legal basis for processing (consent, per the checkbox).
- How to unsubscribe/request deletion (must actually be functional — ties to `Waitlist\Service` needing a delete/erase path, not just "unsubscribed" status).
- Third parties involved (email delivery provider if not using `wp_mail` directly through the host's own mail).

## 4. GDPR / CCPA Functional Requirements (must exist in code, not just policy text)

- [ ] **Right to erasure**: WordPress core's built-in "Erase Personal Data" tool (Tools → Export/Erase Personal Data) must be hooked via `wp_privacy_personal_data_eraser` for waitlist entries matched by email — this is a required WordPress core integration point, not optional.
- [ ] **Right to export**: same, via `wp_privacy_personal_data_exporter` for the same data.
- [ ] **Consent record**: `consent_at` timestamp (already in schema) must be retained even after unsubscribe/erasure request completes the deletion, or the deletion process itself must be logged separately — confirm approach with legal review, this is a common gray area (some jurisdictions want proof consent existed even after data is deleted).
- [ ] **RUM beacon**: must respect Do Not Track / GPC signals if the site owner enables that setting, and must not collect or transmit anything beyond timing metrics (no page content, no user identifiers).
- [ ] **Cookie/consent banner interaction**: if the site already has a cookie-consent plugin, the RUM beacon should ideally load only after consent for "analytics" category cookies is granted — flag this as an integration point for Phase 2, not an afterthought.

## 5. WordPress.org Plugin Review Common Rejection Points (pre-check before submission)

- No `eval()`, no obfuscated code, no remote code execution outside disclosed, opt-in API calls (PSI, GSC, CDN).
- No calling home / phoning usage data anywhere without explicit opt-in and disclosure (this affects the Vitals RUM design — make sure a "send anonymous usage data to AdvikLabs" toggle, if it ever exists, defaults OFF).
- No "powered by" nags or forced upsell banners inside wp-admin outside of Plugins/Settings screens, per current guideline norms — confirm against current guidelines at submission time since these evolve.
- Sanitize/escape audit (already covered functionally by TSD §2.16, but the review team re-checks this manually).

## 6. Trademark / Branding

- "WordPress" is a trademark of the WordPress Foundation — marketing copy (coming-soon page, readme) must follow their trademark usage guidelines (e.g., "for WordPress" rather than implying official endorsement).
- Confirm "Advik" / "AdvikLabs" / "Advik Optimizer" naming doesn't collide with an existing WordPress.org plugin slug or registered trademark before final naming lock — a quick WordPress.org plugin directory search and a basic trademark search is sufficient at this stage; full trademark registration is a separate business decision outside this doc's scope.

## 7. Action Items Checklist (in rough priority order)

- [ ] Draft `readme.txt` with correct headers (can be done any time after Phase 0).
- [ ] Choose and confirm GPL-compatible license for every bundled dependency (before Phase 1/4).
- [ ] Implement `wp_privacy_personal_data_eraser`/`exporter` hooks for waitlist (Phase 8).
- [ ] Write plugin privacy disclosure section for `readme.txt` (Phase 9, once RUM/GSC/CDN scope is finalized).
- [ ] Write AdvikLabs.com privacy policy covering the waitlist (before Phase 8 goes live publicly).
- [ ] Legal review of both privacy documents before any public traffic hits the waitlist or RUM beacon.
- [ ] Verify data retention pruning is actually implemented for CWV metrics/cache logs, not just documented.
