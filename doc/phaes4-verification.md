Here's the step-by-step verification plan for Phase 4, compiled from all the project docs:

---

## Phase 4 — Verification Instructions

### 1. Automated Checks (dev environment)

```powershell
cd D:\jobs\advik-optimizer
.\vendor\bin\phpcs --standard=phpcs.xml    # Lint: expect 0 errors
.\vendor\bin\phpunit                        # Tests: expect 171 tests, 310 assertions
```

### 2. Activation & Settings (WP admin)

1. Activate Advik Optimizer on a **clean WordPress install** (Twenty Twenty-Five theme preferred)
2. Go to **Advik Optimizer → Settings → Minify tab**
3. **Check:** Tab label reads "Minify" and is clickable (not grayed out)
4. **Check:** Section titles — "Asset Minification", "Exclude from minification", "Critical CSS"
5. Toggle **Enable asset minification** ON
6. Toggle **Minify CSS** ON, **Minify JavaScript** ON, **Minify HTML** OFF
7. In the **Exclude from minification** section:
   - Type `jquery-core` in the CSS field, press Enter → chip appears
   - Type `jquery-core` in the JS field, press Enter → chip appears
   - **Check:** Chips are removable by clicking ×
8. **Save Settings** → green "Settings saved." notice appears

### 3. CSS Minification (front-end)

1. Visit any front-end page. View page source (Ctrl+U).
2. **Check:** Stylesheet URLs now point to `wp-content/uploads/advik-optimizer/cache/assets/*.css` instead of original theme/plugin paths
3. **Check:** The minified CSS file is served (copy the URL and open it in a new tab — contents should be one long line, no comments, minimal whitespace)
4. **Check:** The page renders identically to the pre-minify state — no layout breakage, no missing styles
5. Open **Browser DevTools → Console** — expect **zero JS errors**
6. **Check:** `jquery-core` stylesheet is NOT minified (its URL still points to the original location)

### 4. JS Minification

1. Toggle **Minify JavaScript** ON, **Minify HTML** ON, save settings
2. **Check:** Script URLs now point to `wp-content/uploads/advik-optimizer/cache/assets/*.js`
3. **Check:** Console has **zero new JS errors** vs pre-minify baseline
4. **Check:** jQuery continues to work (dropdown menus, etc.)
5. **Check:** `jquery-core` script is NOT minified (its URL still points to the original location)

### 5. HTML Minification

1. With **Minify HTML** ON, view page source
2. **Check:** No HTML comments (`<!-- ... -->`) in source
3. **Check:** Whitespace is collapsed between tags (`><` not `>   <`)
4. **Check:** The page still renders correctly

### 6. Critical CSS Scan

1. On the Settings → Minify screen, click **Rescan Now**
2. **Check:** The "Last scanned" timestamp updates to "a few seconds ago"
3. **Check:** The rule count shows above the Rescan button
4. **Check:** A `<style id="advik-critical-css">` tag appears in `<head>` on front-end pages
5. **Check:** The critical CSS content is minified (single line)

### 7. Dashboard Stat Tile

1. Go to **Advik Optimizer → Dashboard**
2. **Check:** The "JS/CSS Reduced" stat tile shows a value like "XYZ KB" (instead of the placeholder `—`)
3. **Check:** It updates as you minify more assets

### 8. MinifyRollbackGuard Test (safe-mode)

1. Open the browser DevTools Console on the front end
2. Execute: `window.dispatchEvent(new ErrorEvent('error', { message: 'test', filename: 'test.js' }))`
3. Repeat this **3 times** (the threshold is 3 errors per URL)
4. **Check:** An admin notice appears at the top of WP admin pages:
   > "Minification was paused on [URL] after we detected a script error. Review and re-enable."
5. **Check:** Front-end pages now show original (unminified) asset URLs
6. Click the **Re-enable Minification** button in the notice
7. **Check:** Minification resumes (asset URLs go back to cached versions)
8. **Check:** You're redirected to Settings → Minify with a "Minification re-enabled" notice

### 9. Acceptance Criteria Cross-Check

| Criterion | How to Verify |
|---|---|
| FR-3.1: Minified output functionally equivalent | Pages render identically, console has zero new JS errors |
| FR-3.3: Critical CSS non-empty rule | Homepage and a post template produce inlined `<style id="advik-critical-css">` in `<head>` |
| Rollback on broken script | Inject 3 JS errors → admin notice appears → minification disabled |
| Exclusion list respected | Excluded handles' URLs remain unchanged |
| Screen 6 matches design spec | Tab, toggles, exclusion chips, last-scan timestamp, Rescan button, amber-100 warning callout all present |

### 10. Theme Compatibility Spot-Check

- Repeat steps 3-6 on **Twenty Twenty-Five** (default WP theme)
- Repeat on **Astra** or **GeneratePress** (popular lightweight theme)
- Check that no layout is broken and no console errors appear on either theme