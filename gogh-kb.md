# Gogh Editor — Knowledge Base

This knowledge base has two parts.

**Part one is hand-written prose.** It explains how Gogh works and why it works
that way. It is thorough and was fact-checked against the source, but it is
maintained by a person and can lag a release behind.

**Part two is a generated appendix**, extracted mechanically from the Gogh source
on every release. It carries exact values: version numbers, design constants, UI
labels, toast copy, template names, hook names, tool signatures.

**When the two disagree, the appendix is correct.** Say so plainly if a user asks
about something where they conflict, rather than picking one silently. Quote UI
labels and message copy from the appendix, verbatim — those are the current
strings, and a paraphrased button label is the fastest way to lose a user's trust.

Generated for plugin version 0.99.387 · knowledge base 37cd34d.

---

Source of truth: github.com/jamiemarsland/gogh-editor. The current plugin version lives in the facts appendix — this prose never states one, so it can't drift (readme.txt `Stable tag` is chronically out of sync; trust the appendix). Author Jamie Marsland / PootlePress. GPLv2+.

---

## PART 1 — WHAT GOGH IS (beginner level)

Gogh Editor is a WordPress plugin that turns **the front end of your live site into a design canvas**. There is no separate builder screen. You visit a page while logged in, click "Edit with gogh", and drag headings, text, buttons, images and badges anywhere you like.

The trick: you design **freeform** (drag anything anywhere), and when you hit Publish, Gogh solves that freeform layout into a clean, responsive **CSS grid** and saves it as **real WordPress core blocks** — `core/heading`, `core/paragraph`, `core/buttons`, `core/image`, `core/group`. Your theme already knows how to style those.

**The headline promise: no lock-in.** Pages are saved as static core blocks plus a stylesheet baked inside the page content itself. Deactivate the plugin and your pages render exactly the same.

### What makes it different from a page builder
- The live page **is** the canvas — no preview/edit mismatch.
- Typography, colours and button styles come from your **theme's Global Styles**, not from Gogh. Switch style variations and Gogh pages re-skin instantly.
- Text sizes **step through your theme's font-size presets** instead of free-scaling — pages stay on your design system.
- Text and button colours come from your **theme palette**, never invented.
- Mobile layouts are generated automatically, and cards keep their image + text + button together.

### Requirements
- WordPress **6.5+** (the "Blank canvas" page template needs 6.7+)
- PHP **7.4+**
- A **block theme** (it's designed around Global Styles / FSE)
- A **modern browser** — Gogh's output uses CSS container queries, `:has()` and `color-mix()`. There is no fallback for these; no JS feature detection is performed.

---

## PART 2 — GETTING IN AND OUT

### Four ways into the editor
1. **Admin bar** — `🎨 Edit with gogh` appears on any single front-end page you can edit. Links to the permalink + `?gogh-edit=1`.
2. **Corner button** on the front end — `✏️ Edit with gogh`. Hides once editing starts.
3. **The URL** — add `?gogh-edit=1` to any page. If the page has no Gogh content yet, Gogh bootstraps an empty placeholder section at the end of `.entry-content` / `main` so there's a canvas. That placeholder is never saved unless you use it.
4. **Block editor** — insert the **gogh Section** block (category: Design, icon: art). It renders a dashed placeholder — "🎨 gogh section" / "Design this section by dragging elements directly on the live page." — with an **Edit with gogh** button linking to `<permalink>?gogh-edit=1`.

Editor assets only load if: the page is singular, AND (the content contains `gogh-section` OR `?gogh-edit` is present), AND the current user passes `current_user_can('edit_post', $post->ID)`.

### The publish status chip
A chip shows current state:
- **"All changes published"** (clean, no button)
- **"Unpublished changes"** / **"Unpublished changes · backed up"** + a **Publish** button
- **"Publishing…"**
- **"Published ✓"** (reverts to clean after 1.8s)
- **"Publish failed"** + a **Retry** button

### Getting out
Click **🎨 Exit gogh editor** in the WordPress admin toolbar, at the top of the screen — not in the side palette. If clean it exits straight away. If dirty you get a dialog:
- Title: **"You have unpublished changes"**
- Body: "Publish them now, keep editing, or discard them and restore the live page."
- Buttons: **Publish & close** / **Keep editing** / **Discard changes**

Closing the tab with unpublished changes fires the browser's native leave-confirmation.

### Autosave and crash recovery
Every **15 seconds**, while editing and dirty, Gogh POSTs a backup to WordPress's **autosave revision** endpoint (`wp/v2/<type>/<id>/autosaves`). Nothing is published until you press Publish.

On re-entering the editor, if a newer autosave exists than the post's modified time — and its content genuinely differs from what's on screen — you get a sticky toast: **"gogh backed up unpublished work from an earlier session."** with **Restore it** / **Ignore**. Dismissals are remembered in `localStorage['gogh-bak-dismissed-<postId>']` (WP's REST API neither deletes autosave revisions nor reliably overwrites them, hence the client-side dismissal).

Caveat visible in the code: the recovery parser (`extractModels`) only reads **v2-style `script.gogh-model`** carriers, so recovery on v3-only pages is weaker than on v2 pages.

Undo/redo is separate from autosave: a history stack of serialized snapshots capped at **60** entries.

---

## PART 3 — KEYBOARD SHORTCUTS

Global shortcuts are only active while editing, and only when no panel or picker is open.

| Combo | Action |
|---|---|
| `Escape` | Close the open panel (works even outside edit mode); or exit text editing; or clear a multi-selection; or close the zoom overlay / layer fan / header-footer cycle bar |
| `/` | Open the quick-add command menu (placeholder: "Add something…"). `↓`/`↑` navigate, `Enter` inserts, `Escape` closes |
| `⌘K` / `Ctrl+K` | Link the selected text (opens "Link text"). Only when focus is in editable text with a selection, or the caret is inside an existing link |
| `⌘S` / `Ctrl+S` | Publish |
| `⌘Z` / `Ctrl+Z` | Undo |
| `⇧⌘Z` / `Ctrl+Shift+Z` | Redo |
| `Delete` / `Backspace` | Delete the selected element(s). Ignored while typing. On a card child, removes it from the card (with an Undo toast) |
| `← ↑ → ↓` | Nudge selection by **1px** |
| `Shift + arrows` | Nudge by **8px** |
| `⌘V` / `Ctrl+V` | Paste HTML from the clipboard straight onto the page as a section — no modal, works anywhere in edit mode outside a field |

### Typing behaviour
- `Enter` **in a heading** → finishes the heading and moves the caret into the paragraph below. (The code focuses that element; it does not pre-select its contents, so typing inserts rather than replaces.)
- `Enter` **in a paragraph** → inserts a real paragraph gap (`<br><br>`), WordPress-style.
- `Shift+Enter` in a paragraph → a single line break.

Nudging draws live spacing labels which turn blue when the gap becomes equal, and fade after 900ms. History pushes from typing and nudging are debounced (500–800ms).

**There is no right-click / context menu anywhere in Gogh.** "Double click" in practice means a second click on an already-selected element, which enters text editing.

---

## PART 4 — MOUSE AND POINTER

The pointer model is deliberately Canva-style: **click selects, drag-from-anywhere moves, a second click enters text editing.**

- **Click** → selects (selection box, 8 resize handles, rotate grip, floating toolbar appear).
- **Second click** (or clicking an already-selected element) → enters text editing, with the caret placed where you clicked. While the caret is live the selection box and handles fade out; the floating toolbar stays.
- **Drag from anywhere on the element** → moves it. Drag threshold is 4px. A ghost clone follows the pointer so there's no cursor drift, and a landing outline shows where it will drop.
- While editing, clicking a link never navigates.

### Drag modifiers
| Input | Behaviour |
|---|---|
| **Alt-drag** | Duplicates in place, then drags the copy |
| **Shift-drag** | Locks to the dominant axis; the locked axis is pinned and does not snap |
| **⌘-drag / Ctrl-drag** | Free drag — disables all snapping and equal-spacing capture |
| **Shift-click** | Adds an element to a group selection (shift-click a member again to remove it). Dragging any member moves the whole group |

### Marquee selection
Press on empty canvas and drag (left button, no Shift, 6px threshold). On release, everything intersecting the rectangle is selected. Clicking outside a group without Shift clears it.

### Resize handles
Eight handles: nw, n, ne, e, se, s, sw, w. Minimum size **60 wide × 32 tall**. Handles snap to other elements' edges/centres and section edges/centre; otherwise they round to the 8px grid.

Two important special cases:
- **Corner-drag on TEXT does not free-scale.** It steps through the theme's font-size presets — one step per ~56px of diagonal drag — with a floating chip showing the current size ("theme default", "Display S", "Display M", "Display L", or the theme's preset name).
- **Corner-drag on a SHAPE** scales proportionally; **edge handles stretch it freely**, on purpose.

Text height is owned by the measurer — only fixed-height elements accept a dragged height.

### Rotation
A `⟳` grip ("Drag to rotate"). Snaps to **15° increments when within 5°**; anything under 2° collapses to 0.

### Section height
A bar along the bottom edge with a centre pill. Drag to set section height, clamped **160–4000px**. Drag it back to or below the content height to clear the override.

### Hover proximity
- Within **28px of a section boundary**: three pills appear on that boundary — **`+ Section`** (left), the height pill (centre), **`◠ Transition`** (right).
- Away from a boundary, hovering a section shows the **Section** toolbar.
- Within **12px of the right edge of the window**: the side palette opens.

### Cards (drop-to-join)
Drop an element **fully inside** a plain box and it becomes a **child ("kid") of that card** — one level only; boxes never join boxes. The card glows as a drop target.
- Toast on join: **"Added to the card — it moves and stacks with it now."** (+Undo)
- Drag a kid outside the card bounds to free it: **"Out of the card — it's a free element again."** (+Undo)
- `Delete` on a kid: **"Removed from the card."** (+Undo)
- Second click on a selected kid edits its text; first click on a button kid opens its link panel.

Cards matter because they're what keeps an image + heading + button together when the layout stacks on mobile.

---

## PART 5 — THE UI, CONTROL BY CONTROL

### Side palette (titled "gogh")
Icon buttons across the top. There is no exit control here — leaving edit mode
is **🎨 Exit gogh editor** in the admin toolbar.
- **Site style** — theme style-variation drawer (hover to preview, click to keep; grouped Colours / Fonts)
- **Page style** — page template chooser
- **Grid: show and snap** — toggles the 8px grid; tooltip becomes "Grid: on"/"Grid: off". **Off by default**, deliberately: "invisible magnets feel broken to beginners" — the grid you snap to is the grid you see.
- **Whole page — reorder sections** — opens the zoom-out page map
- **Phone preview** — a desktop/phone device toggle on the design view's zoom cluster. Phone mode pins the artboard to a phone's width so the page's own mobile layout renders live. In it you can tune the phone layout without touching desktop: tap an element for a toolbar with **Hide on phone** / **Show on phone** and ↑/↓ arrows that re-stack the mobile column; tap a section's background to **Hide section on phone** (hidden things stay visible in the preview, dimmed with a badge, so they're one tap from back). Overrides are stored as sparse `m` patches on the element/section and self-clear when they match the automatic layout again. (This replaced the old floating 250px mobile-mirror panel in v0.99.197.)

Then **Add element**: `Heading` · `Text` · `Button` · `Image` · `Badge` · `Write` (a reading column, cursor ready) · `Card` (drop elements inside and they stay together, even on mobile) · `Shape` · `Experience` (upload a self-contained HTML experience, runs sandboxed — only if you have the capability) · `Posts` (your latest posts, live).

Footer: `↺ Undo (⌘Z)` · `↻ Redo (⇧⌘Z)`.

Collapsed, it's a slim edge tab labelled **gogh**. Opens on hover or click, auto-closes 500ms after the pointer leaves.

### Floating element toolbar (above the selected element)
| Button | What it does |
|---|---|
| context icon | Opens the type-specific panel: "Button link" / "Shape, colour & image" / "Choose image" / "Block settings & link" |
| `Aa` | **Cycle theme font sizes** — steps through `[theme default, …theme presets…, Display S, Display M, Display L]`, wrapping |
| align icon | Cycles text align left → center → right → default |
| link icon | **Link text (⌘K)** — with no selection, links the whole element |
| colour chip | **Text colour** — theme palette swatches only |
| `▼` | **Send backward** — jumps past the nearest *overlapping* element |
| `▲` | **Bring forward** |
| `⧉` | **Duplicate (or Alt-drag)** — copy offset by +24/+24 |
| `🗑` | **Delete (Del)** |

If nothing overlaps, you get: "Nothing overlaps this — it's already in front." / "…already at the back."

### Section toolbar (on hover, labelled "Section")
`↑ Move up` · `↓ Move down` · `Background image` · `Save this section to reuse` · `⧉ Duplicate section` · `🗑 Delete section`. Up/Down disable at the ends. Never shown for the site header or footer.

### Panels
Shape · Add a shape ("A backdrop for other elements — send it backward once it's placed.") · Button (Style: Solid / Outline; swatch rows Background, Text, Hover background) · Image (URL field, alt text, Upload, Remove image, media grid) · Replace image · Text colour · Link / Link text ("Apply", "Remove link (keep the text)") · Section background · Section transition · Save this section · Site style · Page style · Site header / Site footer · Menu / Add to menu · Imported block.

### Section transition panel
Divider shapes: **None, Wave, Curve, Slant, Peaks, Brush, Torn, Melt**. Plus **Above** / **Below** colour pickers with theme palette swatches, and an **"Overlap the section above"** slider (0–180, step 12).

Dividers are drawn with a `mask-image` (data-URI SVG) plus a background colour — deliberately not a background-image — so the colour can be a CSS variable and palette changes recolour dividers live. A divider is painted in the **next** section's colour. `melt` is a plain gradient fade instead.

### Shapes
**Square, Rounded, Circle, Pill, Arch, Triangle, Diamond, Blob**.

### Header and footer (site chrome)
Fixed pills: **Change header** / **Change footer**. The cycle bar offers **Next header design ›** / **Next footer design ›**, then `✓` (Keep this layout — updates every page), `✨` (Make it freeform), `⋯` (All options), `✕` (Put it back). There's also **📌 Stick to the top** / **📌 Sticky — on**.

Editing chrome publishes to the **template part**, i.e. every page. Gogh warns you: "Editing the site header — publishing will update every page."

Menu editing supports: Add a page to this menu, reorder (drag right to nest), remove, add link, create page. All of it rewrites the navigation block markup.

---

## PART 6 — SECTION TEMPLATES

Picker title: **"Add a section"** — "Choose a layout to get started. You can customise everything." Search box, then shelves: **Quick start** → **Recommended** (first 3 flagged `🔥 Popular`) → **More layouts**.

Quick start tiles:
- **Start from scratch** — build on a blank canvas
- **Paste HTML** — paste your HTML and we'll convert it. ("Great with AI-written HTML. Tip: you can also just press ⌘V anywhere on the page.")
- **My sections** — reuse your saved sections

Live starter layouts:

| Name | What it is |
|---|---|
| **Hero** | Heading, body copy, two buttons, image + tinted box + badge |
| **Feature cards** | Centred heading + three real cards with badges, headings and copy |
| **Big statement** | Badge, huge centred heading, ghost button |
| **Quote** | Tinted box + pull quote + attribution + image |
| **Call to action** | Wide tinted box, heading, button |
| **Article** | 640px reading column, heading, three paragraphs, ghost button |
| **Photo cards** | Two full-bleed photo cards with gradient scrims, ratings, buttons |
| **Gallery** | Heading + three staggered images + badge |
| **Get in touch** | Badge, heading, two contact buttons |

Plus **Start from scratch** (empty). Categories: Heroes & banners · Text · Cards & pricing · Photos · Contact & social.

Templates are tuned for **short copy** — headings of roughly 2–6 words.

Saving a section: the Section toolbar's "Save this section to reuse" stores it via `wp/v2/blocks` as an **unsynced pattern** (`meta.wp_pattern_sync_status = 'unsynced'`), in the v2 section format. It then appears under **+ Section → Your sections**. Because it's unsynced, later edits to the saved section do *not* propagate to copies already placed on pages.

---

## PART 7 — SNAPPING, GUIDES AND SPACING

- **Snap threshold is 6 design px.**
- Snap candidates: the section's left (0), right (1200) and horizontal centre (600); top, bottom and vertical centre; plus every other element's left, right, centre-x, top, bottom, centre-y. The dragged element's own left, right and centre edges are all tested.
- **Grid snap is off by default** (opt-in via the Grid button). When on, non-snapped positions round to the **8px** grid and the grid is drawn.
- **Alignment guides**: one vertical and one horizontal line, drawn across the full section, only while a snap is captured.
- **Equal-spacing snap**: when an element sits between two neighbours, the exact midpoint captures within 8px of the raw pointer and **overrides** edge snapping and grid parity — so equal gaps are always reachable.
- **Spacing labels**: up to 4 live distance badges, measuring to the nearest neighbour on each side. With no neighbour on a side, it measures to the **section edge** (page margins are the distances people eyeball most). Gaps under 4 design px, or under 14 rendered px, aren't drawn. Equal gaps get a `=` prefix and turn blue.
- **Shift** during drag pins the locked axis and suppresses its guide. **⌘/Ctrl** disables all of the above.

---

## PART 8 — ARCHITECTURE (technical)

Single-file ES5 IIFE, no build step. `gogh-editor.js` is ~9,190 lines. It bails immediately unless `window.GOGH` exists (localized from PHP).

### Design constants
```js
var TOL = 8, MIN_H = 560, PAD = 72, SNAP = 6, BASE = 8, W = 1200;
```
`W = 1200` is the **design width** — every model coordinate is in design units against a 1200-unit-wide section. `TOL` is the solver's line-clustering tolerance, `SNAP` the alignment tolerance, `BASE` the opt-in grid, `PAD` the bottom padding added to section height, `MIN_H` the section floor.

### `window.GOGH` (localized config)
`postId`, `restUrl`, `mediaUrl`, `canUpload`, `canExp`, `canConvert`, `pageTemplate`, `pageTemplates[]`, `modified`, `theme`, `themeName`, `gsId`, `palette[{slug}]`, `nonce`.

### Boot sequence
```js
hydrateV3Sections().then(function () {
  S.forEach(renderSection);
  if (wantEdit) { setEditing(true); … strip ?gogh-edit }
});
```
**Timing of `gogh:ready`** — this trips people up. The event is dispatched **synchronously, as soon as `window.__gogh` is installed**, which happens well *before* `hydrateV3Sections()` runs and before any section has been rendered. So a `gogh:ready` listener can call the API, but on a v3 page the models are not hydrated yet. The test suite works around this by also waiting for `document.fonts.ready`.

---

## PART 9 — THE DATA MODEL

### Section
```js
{ scope, els, minH, bg, bgImage, bgId, divider, fx, chrome,
  v3, srcScope, srcSig, bootstrap, wrapEl, sectionEl, styleEl, nodes }
```
- `scope` — `'gogh-sec-<n>'`, the CSS scope class, also emitted as `data-gogh-scope`
- `els` — elements in **stacking order** (index+1 → `z-index` and the `.gogh-el-N` class)
- `minH` — design-unit minimum height (default 560; 480 for an empty bootstrap)
- `bg` — CSS colour string (may be `var(--wp--preset--color--x)` or a `color-mix()`)
- `bgImage` / `bgId` — background image URL + attachment id
- `divider` — `{shape}`: `wave|brush|torn|curve|slant|peaks|melt`
- `fx` — `{pull, curtain, reveal}`: negative top margin in design units; slide-over-previous (pins the previous section sticky); scroll-driven rise animation
- `chrome` — `{area:'header'|'footer', id}` when this section IS a template part canvas
- `v3` — booted as a v3 shell awaiting hydration
- `srcSig` — djb2 signature of the Gutenberg span this was converted from (unpublished conversions)

### Element
```js
{ type, x, y, w, h, text, ghost, cool, src, href, rot, alt, mediaId,
  fs, align, color, tf, btnBg, btnText, btnHover, wsrc, whtml,
  boxBg, radius, shape, boxImg, boxImgId, ph, expId, expUrl, kids }
```

| Field | Applies to | Meaning |
|---|---|---|
| `type` | all | `heading \| para \| button \| image \| badge \| box \| widget \| exp`. Unknown types render as a bare `div` (forward compatible) |
| `x,y,w,h` | all | Absolute design units (0…1200 on x), integers |
| `text` | heading/para/button/badge | Heading/para hold **inline HTML** (cleaned); button/badge are plain text |
| `rot` | all | Rotation degrees — a CSS transform, not part of grid placement |
| `fs` | heading/para | Theme font-size preset slug, or `__disp-s`/`__disp-m`/`__disp-l` (Gogh's own poster sizes), or `null` = theme default |
| `align` | heading/para | `'center' \| 'right' \| null` |
| `color` | heading/para | Theme palette slug |
| `ghost` | button | Outline style |
| `href` | button/box | Link URL |
| `btnBg`, `btnText`, `btnHover` | button | Palette slugs |
| `src`, `alt`, `mediaId` | image | URL, alt text, WP attachment id |
| `cool` | image | Placeholder gradient variant when there's no src |
| `boxBg`, `radius`, `shape`, `boxImg`, `boxImgId` | box | Background, corner radius in design units, shape key, background image |
| `kids` | box | Nested elements → the box becomes a **card**, with its own grid; children get `.gogh-k-N` |
| `wsrc` / `whtml` | widget | Block markup published verbatim / live editor preview HTML |
| `expId`, `expUrl` | exp | Uploaded `.html` attachment (iframe `sandbox="allow-scripts"`, no `allow-same-origin`) |
| `ph` | heading/para | Placeholder text when `text` is empty |
| `tf` | heading/para/button | **Captured typography** from pasted/converted HTML: `{ff, fs, fs2, fw, fst, lh, ls, ls2, tt, col, bg, rad}`. Emitted after theme presets with `!important`, so a paste keeps its look until you touch a theme control (which deletes the relevant `tf` keys) |

Classification: `isText = heading|para`; `fixedHeight = button|image|badge|widget|box|exp`. Only text elements are re-measured; everything else keeps its authored height.

---

## PART 10 — THE FREEFORM → GRID SOLVER

Three functions: `cluster`, `nearest`, `solve`.

`designH(els, minH)` = `max(minH || 560, maxBottom + 72)`.

**`solve(els, minH, dw)`**:
1. Collect **every x edge** of every element (`e.x` and `e.x + e.w`) plus the frame edges 0 and 1200. Same for y edges plus 0 and H.
2. `cluster(vals)` — sort ascending, greedily merge into groups while each new value is within `TOL` (8 units) of the previous member; each group collapses to its **mean**. This produces the grid lines, snapping near-aligned edges onto one shared line.
3. Columns are the gaps between x lines, expressed as **`cqw` percentages of W**. Rows are the same for y but wrapped in `minmax(<pct>cqw, max-content)` — so a row can grow if its text runs taller than designed, but never collapse.
4. For each element, `nearest()` finds the closest line index for each edge → `{c1, c2, r1, r2}`.

Then the CSS emitter writes `grid-area: r1 / c1 / r2 / c2; z-index: i+1;`. **Position comes from `grid-area`, stacking from `z-index` — which frees DOM order to be reading order.**

The container is `.gogh-wrap { container-type: inline-size }`, so every `cqw` unit tracks the section's own width. That single choice makes the whole layout resolution-independent: proportional, never pixel-pinned.

**Cards**: a box with `kids` gets its own nested grid from `solve(e.kids, e.h, e.w)`. Columns convert from `cqw` to `fr` so the card fills its slot at any width; rows convert to percentages of the card's own design height, so its internal proportions scale with it.

**Mobile** (`@container (max-width: 700px)`): the grid is discarded entirely — `grid-template-columns: 7cqw 1fr 7cqw`, `grid-auto-rows: auto`, `row-gap: 6cqw`, every element `grid-area: auto; grid-column: 2`. Images/boxes/experiences keep `aspect-ratio: w / h`; badges become `width: max-content; height: 44px`; decorative shapes are `display: none`; plain boxes keep aspect ratio (they're structural scrims). **No `order:` property is emitted** — the DOM is already in reading order.

### XY-cut linearisation (mobile reading order)

`splitByGaps(items, pos, len)`: sort by position, walk accumulating a running `end`; when the next item starts at or after `end - 2` there's a full gap → cut.

`xyLinearize(items)`, recursive:
1. **≤1 item** → return as-is.
2. **Spanning-header check.** Split into horizontal bands. If >1 band, split everything below band 0 into columns. If that yields >1 column, test whether *every* item in band 0 horizontally overlaps ≥2 column extents. If so, band 0 is a section title spanning the columns → emit `band0 ++ rest`. (Guard: a row of card *images* aligns one-per-column and therefore does not peel — the column pass keeps each card whole.)
3. **Columns first.** Split by x gaps; if >1 column, recurse into each column left-to-right and concatenate. **This is the key rule** — a card's image + copy + button stay together instead of three cards interleaving row-by-row.
4. **No clean columns** (a full-width heading spans them): peel off the top band only, recurse, then recurse on the remainder — so columns underneath stay detectable.
5. **Fully interlocked**: stable sort by y then x.

`readingRank(els)` drives **both** the canvas DOM append order **and** the published block order — so mobile stack order, tab order and screen-reader order all agree. The code cites **WCAG 1.3.2**.

---

## PART 11 — cssT AND GOGHSCOPE

`buildCSS(els, scope, minH, opts)` returns a plain string. Everything is prefixed `.gogh-section.<scope>`; wrapper rules use `.gogh-wrap:has(> .gogh-section.<scope>)`. The sheet always opens with the sentinel comment `/* generated by gogh */`.

`opts` comes from `sectionOpts(sec)`, which is section-*pair* aware: `{bg, divider, bgImage, fx, stickUnder: !!(next.fx && next.fx.curtain), divColor: next ? (next.bg || '#0f0e0c') : null}`. So a divider is painted in the next section's colour, and a section is pinned sticky if the section after it uses `curtain`.

**The GOGHSCOPE placeholder.** `buildSectionAttrsV3(sec)` produces:
```js
{ v: 3, scope: sec.scope, model: sectionModelJSON(sec, 3),
  cssT: buildCSS(sec.els, 'GOGHSCOPE', sec.minH, sectionOpts(sec)) }
```
`cssT` is the identical stylesheet compiled against the literal token `GOGHSCOPE` instead of a real scope class. Three consumers substitute it:
- **JS** (`buildSectionBlocksV3`): `attrs.cssT.split('GOGHSCOPE').join(sec.scope)` — baked into the saved `<style class="gogh-style">`
- **`gogh-block.js`** (`scopedCss`): the same replace, in both `edit()` and `save()`, so save output matches the JS byte-for-byte
- **PHP** (`gogh_css_from_attrs`): slug-casts the scope, substitutes, then **strips `<`, `>`, `\`**

Why templated at all? Because it lets the scope class be reassigned (duplicated sections, migration) without recompiling the stylesheet, and it's the unit the deprecation `migrate()` produces from v2 CSS.

---

## PART 12 — HOW GOGH SERIALISES TO CORE BLOCKS

| `type` | Core block emitted | Notes |
|---|---|---|
| `heading` | `core/heading` | Always `<h2>`, level 2. Display sizes emit **no** `fontSize` attr — they live only in the scoped sheet |
| `para` | `core/paragraph` | |
| `button` | `core/buttons` > `core/button` | Outer `wp:buttons` carries the `gogh-el-N` class |
| `image` (with src) | `core/image` | `sizeSlug: 'full'`, `<img class="wp-image-N">` |
| `image` (no src) | `core/group` | Empty placeholder, background painted by CSS |
| `badge` | `core/paragraph` | className `gogh-el-N gogh-badge`. **A badge is just a paragraph** — the star bullet and pill chrome are `::before` and rules in the scoped sheet. There is no `gogh/badge` block |
| `box` | `core/group` | `gogh-el-N gogh-box` (+ `gogh-cardbox` with kids). `href` adds an `<a class="gogh-card-link">`. Kids publish **inside** via the `gogh-k-` prefix, so with the plugin off a card degrades to stacked blocks in a group |
| `widget` | `core/group` wrapping `wsrc` verbatim | For atomic blocks — navigation, site title, query loop. The Posts element produces a real `core/query` + `core/post-template` |
| `exp` | `core/group` containing only `<a class="gogh-exp-link">` | KSES-safe and the plugin-off fallback; PHP swaps it for the sandboxed iframe at render |

### The section wrapper: v2 vs v3

**v2 (legacy — still used for chrome / template-part saves *and* for "Save this section to reuse"):**
```html
<!-- wp:gogh/section -->
<div class="wp-block-gogh-section alignfull gogh-wrap">
  <style class="gogh-style">…</style>
  <script type="application/json" class="gogh-model">{"version":2,…}</script>
  <div class="gogh-section gogh-sec-N" data-gogh-scope="gogh-sec-N">…blocks…</div>
</div>
<!-- /wp:gogh/section -->
```

**v3 (attrs-as-truth, used for all page content):**
```html
<!-- wp:gogh/section {"v":3,"scope":"gogh-sec-N","model":{…},"cssT":"…GOGHSCOPE…"} -->
<div class="wp-block-gogh-section alignfull gogh-wrap">
  <style class="gogh-style">…scope substituted…</style>
  <div class="gogh-section gogh-sec-N" data-gogh-scope="gogh-sec-N">…blocks…</div>
</div>
<!-- /wp:gogh/section -->
```
No model script. **The attributes are the truth; the baked `<style>` and the inner core blocks are projections of them.**

| | v2 | v3 |
|---|---|---|
| Model storage | `<script class="gogh-model">` in markup | `attrs.model` in the block comment |
| CSS storage | `<style>` in markup only | `attrs.cssT` + baked `<style>` projection |
| Block attrs | none | `{v, scope, model, cssT}` |
| Render | passthrough | scoping + emission + experience injection |
| KSES resilience | `<style>` stripped ⇒ look lost | server rebake regenerates it from attrs |
| Boot | model parsed from DOM immediately | needs `hydrateV3Sections()` REST fetch |

`deprecated[0].migrate()` in `gogh-block.js` converts v2 → v3 by re-templating `css.split(scope).join('GOGHSCOPE')` and setting `model.version = 3`.

`serializeBlockAttrs(obj)` mirrors WP's `serialize_block_attributes()` — escapes `--`, `<`, `>`, `&`, `\"` into `\u…` so the JSON survives inside an HTML comment.

---

## PART 13 — GUTENBERG RECONCILIATION

Three layers.

**(a) Carrier pairing / adoption at boot.** For each `.gogh-wrap`, Gogh looks for the v2 inner format (style + model script), then a v3 shell (style + `data-gogh-scope`, no model script), then a legacy carrier-pair format matched by adjacency. A wrap with **no carrier at all** — e.g. a section duplicated in the block editor — is **adopted**: it sheds any copied scope class, takes a fresh one, and gets a model synthesised by `inferModelFromDom()` (heading/badge/paragraph/buttons/figure → typed elements stacked at x:72, y incrementing). It becomes a normal Gogh section on the next save.

**(b) `syncModelFromMarkup(sectionEl, els)` — "Gutenberg edits win over the model".** The DOM at collect time *is* the saved markup, so anything Gutenberg could legitimately have changed is adopted **before the first render**; position and size stay model-owned. For each element index it looks up `.gogh-el-<i+1>`:
- **missing node** ⇒ deleted in the block editor ⇒ dropped from the model
- heading/para: text from `innerHTML` (cleaned), `fs` from `has-X-font-size` (with the caveat that Display sizes carry no preset class by design, so if the DOM has no class and the model holds a `__disp-*` value, the model wins), align from `has-text-align-*`, colour from the first `has-X-color` that isn't `text`/`link`/`*background*`
- badge: text only
- button: text, href, `btnBg`, `btnText`, `ghost`
- image: src, alt, `mediaId` from `wp-image-N`

Safety valve: if **nothing** matched, it returns the original elements untouched rather than wiping the model on unexpected markup.

**(c) `hydrateV3Sections()`.** v3 models live in block comments, which the rendered DOM doesn't carry. So Gogh fetches `context=edit` raw content, parses top-level blocks, matches `<!-- wp:gogh/section {…} -->` attribute JSON, and pairs to shells primarily by `srcScope` with a **document-order fallback** for duplicated pages carrying colliding scopes. Orphans render as-is but are model-uneditable. On failure it logs `[gogh] v3 hydration failed`. v2 pages skip the fetch entirely.

**(d) Editor-side parity.** The `block_editor_settings_all` filter scrapes `<style class="gogh-style">` out of the post content and injects it into the block editor iframe, plus editor-only fixes: `display: contents` on `.block-editor-inner-blocks` wrappers (otherwise the grid breaks), hiding the leading Custom HTML block, full-bleed `[data-type="gogh/section"]`, suppressing the variation picker inside `.gogh-section`.

---

## PART 14 — "MAKE FREEFORM" (converting existing Gutenberg sections)

**Note: this is a labs flag, off by default.** `cfg.canConvert = apply_filters('gogh_convert_enabled', isset($_GET['gogh-convert']) || isset($_GET['gogh-test']))`. So you enable it with `?gogh-convert=1` on the URL, or with the filter.

`convertBlock(node)`:
1. GET the page's `context=edit` raw content
2. Parse top-level block spans; filter out `gogh/section` spans and any already claimed by an unsaved conversion
3. Collect the rendered top-level nodes
4. **Safety gate**: if the counts differ, throw — "gogh cannot safely map this page's blocks to its stored content (rendered N vs stored M)". The mapping is positional and refuses to guess.
5. `scanDomWithRaw(node, blockRaw, {loose: true, rootIsBlock: true})`
6. Build a section: `minH = round(rect.height * (1200 / rect.width))`. Cover backgrounds lift to the section; else the scan's root background; else the node's own computed background colour.
7. Stash the original node with a marker comment and remove it from the DOM — so deleting an unpublished conversion **puts the original Gutenberg block back**.

### `scanDomWithRaw` — the measuring engine
Scale factor `sx = 1200 / rootRect.width`; every box becomes `x=(left-rootLeft)*sx`, `y=(top-rootTop)*sx`, `w=max(24, width*sx)`, `h=max(16, height*sx)`. Sub-2px boxes are skipped.

`walk()` pairs parsed block spans against the container's element children **1:1 by position**:
- `core/cover` → the backdrop becomes a full-bleed image element, the inner container recurses
- `core/html` with children → a box, then a DOM-only walk with **freeMode on**
- `core/group|columns|column` → recurse when inner spans pair (or always in loose mode), emitting a box first. In strict mode with unpaired children, the whole group is atomised as a **widget with its full markup** so attrs and layout survive
- otherwise a leaf

Leaf typing: `H1–H6` → heading; `P` → para; `IMG` / `FIGURE.wp-block-image` → image; `A`/`BUTTON` passing `looksLikeButton()` → button. That test requires a background or border, text under 60 chars, no `<img>`, and **rejects** anything containing `h1–h6`/`p` or taller than 120px (those are cards, not buttons). `.wp-block-buttons` emits one button **per** `.wp-block-button`, taking geometry from the `<a>` not the wrapper. Spacers, separators and `HR` are dropped. **Everything else becomes a widget** with its markup preserved — nothing is silently lost.

`boxFrom(dom)` preserves any container that paints: solid colour, gradient, or a `url()` photo. Palette classes become `boxBg` slugs.

`textStyle()`: preset slug from `has-X-font-size`; if there's **no** preset class (custom `clamp()` sizes), it steps to the nearest theme preset **by computed px** rather than falling back to default. In freeMode it also captures the full `tf` typography record.

**freeMode** turns on intrinsically whenever the walk enters a `core/html` block or a non-`wp-block-` container holding only elements — so pastes recovered from published pages convert with their own look, not just fresh ones.

**Stylesheet carry-over**: `<style>` tags found during the walk are prepended to the first widget. If the paste fully atomised, a tiny 24×16 carrier widget is appended **only if** the sheet contains genuinely dynamic rules (`:hover|:focus|:active|::before|::after|@media|@keyframes|@font-face|@supports`) — static rules are already captured per-element from computed style.

**Root-background promotion**: if the first emitted element is a box covering essentially the whole root, it's removed and returned as `rootBg` — because a box has a fixed measured height while the rendered section can grow, and the page background bleeding through read as a gap between dark sections.

---

## PART 15 — THEME INTEGRATION

The division of ownership, stated in a code comment: **"gogh owns LAYOUT; the theme (theme.json / Global Styles) owns typography, colours and button treatment — so Full Site Editing changes flow through."** The type rules deliberately contain almost nothing for heading/para (just `align-self: start`, so measurement returns intrinsic not stretched height).

- **Font-size presets** — regex `--wp--preset--font-size--([a-z0-9-]+)` over `#global-styles-inline-css`, then a hidden off-screen probe span reads the real computed pixel value per slug. Zero-px entries dropped, sorted ascending. Stepping order: `[null, …slugs…, '__disp-s', '__disp-m', '__disp-l']`, wrapping.
- **Colour palette** — regex `--wp--preset--color--([a-z0-9-]+)` over the same stylesheet, then **filtered against `cfg.palette`** (from `wp_get_global_settings(['color','palette'])['theme']`) so only slugs the theme actually declares are offered. This is why WP's default presets don't clutter the picker.
- **Display sizes** are Gogh's own three stops above the theme's largest preset — `max(6cqw, 30px)` / `max(9cqw, 36px)` / `max(13cqw, 42px)` — emitted into the section stylesheet, so published pages stay deactivation-safe and Global Styles are never mutated.
- **Style variations** — listed from `wp/v2/global-styles/themes/<theme>/variations`. Hover preview synthesises a `:root{…}` override stylesheet locally; clicking POSTs to `global-styles/<gsId>` and hot-swaps the CSS without a reload. Note that font variations register families under **new** slugs (colours reuse slugs, fonts don't), so the body and heading font mappings have to be applied too. `cfg.gsId` is 0 unless the user has `edit_theme_options`.
- **Patterns** — from `wp/v2/block-patterns/patterns`. Chrome patterns are picked by category `header`/`footer` or `block_types` containing `template-part/<area>`. Content patterns are filtered to `p.name.startsWith(cfg.theme + '/')`.
- **Page templates** — Gogh registers a `gogh//blank-canvas` block template for pages (post content only, no header/footer), guarded by `function_exists('register_block_template')` for WP 6.7+.

### CSS features and why they're load-bearing
- **Container queries** — `.gogh-wrap { container-type: inline-size }` plus every grid track (`cqw`), font size, radius, and the mobile breakpoint `@container (max-width: 700px)`. Without them the layout doesn't resolve at all.
- **`:has()`** — `.gogh-wrap:has(> .gogh-section.<scope>)` is the only selector available for the wrapper rules that implement `fx.pull` / `fx.curtain` / `stickUnder`.
- **`color-mix()`** — template/shape/box default backgrounds and the palette-aware tints over background images (62% on sections, 45% on boxes).

The **only** real `@supports` guard in the codebase is for scroll-driven animation (`animation-timeline: view()`), with a `prefers-reduced-motion` opt-out, and it's also gated on `html:not(.gogh-editing)` — reveal animations never run while editing.

Also shipped to **every visitor**, not just editors: `html { overflow-x: clip }`, because full-bleed `100vw` sections overflow sideways once a scrollbar exists. `clip` not `hidden`, so no new scroll container is created.

---

## PART 16 — THE `window.__gogh` JS API

Installed early in boot; `__gogh.build` carries the version. `document` fires `gogh:ready` synchronously once the object exists — **before** v3 hydration and before the first render (see Part 8).

### Namespaced
- `__gogh.explode.enter(sec, cluster) / .exit() / .state()` — fan a stack of elements out in 3D. **Programmatic only** — the press-and-hold trigger was removed because it kept firing on slow clicks
- `__gogh.multi.set(sec, idxs) / .clear() / .state()` — multi-selection within one section
- `__gogh.zoom.open() / .close() / .el` — the page-map modal

### State / introspection
`state` (getter → `{editing, sections, sel, drag, resize, history, hIdx}`), `sections()`, `pending()`, `storedEdits()`, `chromeEdits()`, `templates()`, `shapeDefs()`, `fontSizes()` (→ `[{slug, px}]`), `isDirty()`, `build`.

### Model / layout
`serialize()`, `restore(snap)`, `pushState()`, `renderSection(sec)`, `resolve(sec)`, `resolveAll()`, `measure(sec)`, `reflowPush(sec, e, oldH)`, `readingOrder(els)`, `syncModelFromMarkup(sectionEl, els)`, `cleanInline(html)`, `sanitizePastedHtml(html)`.

### Sections and elements
`addSection(tpl, idx, before)`, `deleteSection(idx)`, `moveSection(idx, dir)`, `reorderSection(from, to)`, `duplicateSection(idx)`, `setSecBg(idx, src, id)`, `openSecBgPanel(idx)`, `openShapePanel(idx)`, `showHbar(i)`, `openPicker(idx, before)`, `closePanel()`, `addElementAt(kind)` (`heading|para|button|image|badge|posts|card|exp|write`), `addShape(def)`, `setFontSize(sec, i, slug)`, `stepFontSize(sec, i, dir)`, `applyTextLink(url)` (empty ⇒ unlink), `setEditing(on)`, `openSide()`, `closeSide(now)`, `toast(msg, {error, sticky, ttl, actions})`, `showTip(el)`.

### Persistence
`publish()` → Promise<bool>, `buildBlocks()`, `buildV3()`, `mergeContent(raw)`, `gatherRawUnits()`, `resequenceToDom(merged, units)`, `parseTopBlocks(raw)`, `convertBlock(node)`, `convertChrome(partEl)`, `scan(rootEl, raw, opts)`, `insertGoghPattern(...)`, `addHtmlSection(html, idx, before)`, `initStoredEdits()`.

### Theme / chrome / nav
`previewVariation(v)`, `clearVariationPreview()`, `openPageStylePanel(anchorEl)`, `openMenuManager(partEl, anchorEl)`, `startChromeCycle(...)`, `stickyRawToggle(raw, on)`, `parseNavModel(nraw)`, `serializeNavModel(items)`, `reorderNavRaw(nraw, items)`, `navLinkMarkup(page)`.

---

## PART 17 — WEBMCP BRIDGE

`gogh-webmcp.js` exposes the editor to browser agents that speak `navigator.modelContext` (the W3C WebMCP proposal). Every tool is a thin wrapper over `window.__gogh`.

**It is opt-in only** — enabled by `?gogh-mcp=1`, `?gogh-test`, or the `gogh_webmcp_enabled` filter. Explicitly so, to keep agents from discovering publish-capable tools uninvited.

If `navigator.modelContext` is absent, the tools still exist on `window.__goghMcp` and can be driven from the console.

### The 10 tools
| Tool | Params | Behaviour |
|---|---|---|
| `gogh_page_overview` | none | Lists every section with its **content-section index** `[0]`, `[1]`… and each element as `type[:shape] "text"`. Chrome sections print as `(site <area> — not editable via these tools)` with no index. Ends with `Unpublished changes: no` or `Unpublished changes: yes — call gogh_publish when done`. **Call this first to orient yourself** |
| `gogh_list_layouts` | none | Returns the starter template names |
| `gogh_add_section` | **`layout`** | Case-insensitive substring match on starter names; appends at the end |
| `gogh_paste_html` | **`html`** | Lands as a real block, pixel-faithful, text stays editable |
| `gogh_add_element` | **`type`** (`heading\|para\|button\|image\|badge`), `text`, `section` | Adds and centres an element in the given content section |
| `gogh_add_shape` | **`shape`**, `color`, `section` | Shapes: `square, rounded, circle, pill, arch, tri, diamond, blob`. Inserted **at the back of the stack** |
| `gogh_set_section_background` | **`section`**, **`color`** (empty clears) | |
| `gogh_edit_text` | **`find`**, **`replace`** | Find/replace across all element text in content sections; re-measures and reflows so growing text pushes neighbours down |
| `gogh_delete_section` | **`section`** | Warns that sections renumber |
| `gogh_publish` | none | **The only async tool** — returns a Promise |

All `section` params are **content-section indexes** (chrome excluded), matching `gogh_page_overview` numbering. Every mutating tool calls `ensureEditing()` first.

**Colour handling differs between the two colour tools — worth knowing.** `gogh_set_section_background` runs its value through `cssColor()`, which turns a bare slug-shaped string into `var(--wp--preset--color--<slug>)` *unless* it's a common colour name (`red`, `blue`, `green`, `black`, `white`, `transparent`), which passes through as CSS. `gogh_add_shape` does **not** use `cssColor()` — it stores the raw value, and the CSS emitter then wraps any slug-shaped string unconditionally. So `gogh_add_shape({shape:'circle', color:'red'})` emits `var(--wp--preset--color--red)`, which is usually a dead variable, while the same argument to `gogh_set_section_background` correctly yields `red`. For shapes, pass a real palette slug or a hex value.

### Console usage
```js
__goghMcp.call('gogh_page_overview')
__goghMcp.call('gogh_add_section', { layout: 'Hero' })
__goghMcp.call('gogh_edit_text', { find: 'Old', replace: 'New' })
__goghMcp.call('gogh_add_shape', { shape: 'circle', color: 'accent-2', section: 0 })
__goghMcp.call('gogh_publish').then(console.log)   // only this one is a promise
```
`__goghMcp` is `{tools, calls, call(name, args)}`. Unknown names throw. **Synchronous tools answer synchronously** — the test suite depends on it.

**Observability**: every call pushes `{tool, args, via, ms, at}` into `window.__goghMcp.calls` and logs `[gogh] WebMCP call <name> (<via>, <ms>ms) → <first line>`. `via` is `'console'` or `'webmcp'` — so "is the agent really using WebMCP, or just clicking around?" is answerable from the console alone.

---

## PART 18 — PHP / SERVER SURFACE

### Hooks the plugin registers
| Hook | Type | Purpose |
|---|---|---|
| `init` | action | Registers `gogh-block` script + `register_block_type('gogh/section')` |
| `init` | action | `register_block_template('gogh//blank-canvas')` (WP 6.7+ guard) |
| `upload_mimes` | filter | Adds `html => text/html` **only if** the user has `unfiltered_html` |
| `wp_insert_post_data` | filter, **priority 20** | `gogh_rebake_post_data()` — server-side rebake of the baked `<style>` |
| `wp_enqueue_scripts` | action | Front-end editor assets + inline base CSS |
| `rest_api_init` | action | Registers `gogh/v1/render` |
| `enqueue_block_assets` | action | Admin-only inline base CSS |
| `admin_bar_menu` | action, priority 90 | The `🎨 Edit with gogh` node |
| `block_editor_settings_all` | filter | Injects the page's Gogh CSS + editor fixes into the block editor |

### Filters exposed for third parties
| Filter | Default | Effect |
|---|---|---|
| `gogh_rebake_enabled` | `true` | Escape hatch to disable the server-side rebake on save |
| `gogh_webmcp_enabled` | `false` | Enable the WebMCP bridge sitewide |
| `gogh_convert_enabled` | `?gogh-convert` or `?gogh-test` present | Toggles the labs "Make freeform" flag |

Plus the JS event `gogh:ready` on `document`.

Public PHP functions: `gogh_css_from_attrs($attrs)`, `gogh_sweep_css_residue($html)`, `gogh_render_section($attrs, $content)`, `gogh_inject_experiences($attrs, $content)`, `gogh_rebake_post_data($data)`.

### REST
**One custom route: `POST gogh/v1/render`.** Permission: `current_user_can('edit_posts')`. Param `content` (block markup). Returns `{html, css, styles[]}` — `do_blocks($content)`, plus `wp_style_engine_get_stylesheet_from_context('block-supports')`, plus deduped version-stamped `href`s from every registered block type's `style_handles`, walked recursively. It exists because the core block renderer returns bare markup with no per-instance layout CSS, which made header/footer previews look broken.

**No AJAX endpoints. No `admin-ajax.php`.** Everything else is core `wp/v2`:
- the post/page endpoint (`GOGH.restUrl`) — GET `?context=edit`, POST to publish
- `/autosaves` — the 15-second backup and crash recovery
- `media` — the image grid and uploads
- `blocks` — saved sections (stored as **unsynced patterns**, `meta.wp_pattern_sync_status = 'unsynced'`)
- `template-parts` — header/footer edits
- `global-styles` and `global-styles/themes/<theme>/variations` — style variations
- `block-patterns/patterns` and `block-renderer/core/pattern` — the pattern library and previews
- `navigation` — read **and POST**, so menu editing creates/updates `wp_navigation` posts
- `pages` — list, and **POST to create a new page** (the "+ Page" flow in the menu manager)
- a latest-posts query for the Posts element preview

**One nonce**: `wp_create_nonce('wp_rest')`, localized as `GOGH.nonce`, sent as `X-WP-Nonce` with `credentials: 'same-origin'`.

### Capability checks
| Check | Where | Effect |
|---|---|---|
| `edit_post` on the post | `wp_enqueue_scripts` | Gates **all** editor JS/CSS (base CSS loads for everyone) |
| `edit_post` on the post | `admin_bar_menu` | Gates the admin bar entry |
| `edit_posts` | `gogh/v1/render` | REST gate |
| `unfiltered_html` | `upload_mimes` | Gates `.html` uploads — grants no capability the user doesn't already have |
| `upload_files` | localize | `GOGH.canUpload` |
| `upload_files` **and** `unfiltered_html` | localize | `GOGH.canExp` (Experiences) |
| `edit_theme_options` | localize | `GOGH.gsId`, else 0 |

### Asset loading gates
1. `is_singular()` — else return
2. Content contains `gogh-section` **or** `?gogh-edit` is set — else return
3. Base CSS (`gogh-base`) is registered for **every visitor**: `html{overflow-x:clip}`, `.gogh-wrap` margin resets, `.gogh-section > .wp-block-group{padding:0!important}`, box-sizing, full-bleed HTML section rules
4. `current_user_can('edit_post')` — else return. Then `gogh-editor.js` + `.css`, and conditionally `gogh-webmcp.js` (`?gogh-mcp` / `?gogh-test` / filter) and `gogh-tests.js` (`?gogh-test`), plus the `GOGH` localization

### Query-string switches
`?gogh-edit=1` (open the editor / bootstrap a section) · `?gogh-mcp=1` (WebMCP bridge) · `?gogh-test` (bridge + test suite + convert flag) · `?gogh-convert` (labs convert flag)

---

## PART 19 — SECURITY MODEL

This is a genuinely careful part of the codebase and worth explaining accurately.

**The core problem**: block attributes are writable by users **without** `unfiltered_html`, because KSES does not police attribute JSON. So `cssT` would otherwise be a stored-XSS bypass around KSES.

**The defence** (`gogh_css_from_attrs`):
- `$scope = preg_replace('/[^a-z0-9-]/', '', $scope)`; an empty scope returns `''`
- After substitution, `str_replace(array('<','>','\\'), '', $css)` — "CSS never needs these characters; their absence makes markup injection impossible"

**CSS residue sweep** (`gogh_sweep_css_residue`): when KSES strips a baked `<style>` *tag* it leaves the CSS behind as loose text. Every Gogh stylesheet opens with the sentinel `/* generated by gogh */` and never contains `<`, so `preg_replace('/\/\* generated by gogh \*\/[^<]*/', '', $html)` removes exactly the residue and nothing else.

**Experiences** (`gogh_inject_experiences`): stored markup carries only a plain link — KSES-safe, and the plugin-off fallback. At render time it becomes an iframe. The `src` is **never** taken from attributes; only `expId` is, which is `intval()`'d, must be `> 0`, and must have `get_post_mime_type($id) === 'text/html'`. The URL comes from `wp_get_attachment_url()` + `esc_url()`. The iframe is `sandbox="allow-scripts"` **without** `allow-same-origin` — an opaque origin, so uploaded code cannot read cookies or reach the embedding page.

**Rebake** (`gogh_rebake_post_data`, on `wp_insert_post_data` priority 20): on every save, by anyone — including users whose content KSES just filtered — regenerate the baked `<style>` inside each v3 section from its attributes. "The plugin authors these bytes server-side, so the saver's capabilities are irrelevant; deactivation keeps the look." Bails early unless the content contains `wp:gogh/section`; skips blocks already carrying `class="gogh-style"`; only re-serializes when something changed.

**Inline sanitisation** (`cleanInline`, applied on every text read and write): allow-list is `A, STRONG, EM, B, I, BR`. `SCRIPT/STYLE/TEMPLATE/IFRAME` removed with contents; allowed tags stripped of all attributes except `href` on `<a>`, which must match `/^(https?:|mailto:|tel:|\/|#)/i`; any other element unwrapped with contents preserved.

**Paste sanitisation** (`sanitizePastedHtml`): strips `<script>`, `on*` handlers and `javascript:` URLs.

---

## PART 20 — KNOWN LIMITATIONS

Stated in the readme as beta limitations, and enforced in code:

1. **Block types that survive a republish.** `buildElBlocks` has exactly eight arms: `heading, para, button, image, badge, box, widget, exp`. A block added inside a Gogh section from the block editor has no model entry, so the next Gogh publish regenerates the section from the model and **the block is gone**. (Forward-compat note: `makeNode`'s default arm creates a bare `<div>`, so a newer Gogh's element type at least keeps the page alive.)

2. **Inline formats are flattened.** Headings and paragraphs support inline links, bold and italic (`⌘K` to link). Everything else — `<span>`, `<mark>`, `<sub>/<sup>`, `<code>`, `<s>`, inline styles, `class`, `id`, `rel`, `target` on links, and every custom rich-text format — vanishes the moment Gogh touches the text.

3. **Modern browsers required** — container queries, `:has()`, `color-mix()`, with no fallback path.

### Other guardrails in the code (worth knowing when something "refuses" rather than breaks)
- `convertBlock` and `initStoredEdits` refuse to act when rendered top-level nodes and stored top-level spans differ in count, rather than mis-map.
- `resequenceToDom` bails to the unmodified merge on **any** ambiguity — a unit not found verbatim, overlapping matches, non-whitespace between claimed ranges, stray blocks in the head/tail. "The failure mode is a stale order, never corrupted content."
- `gatherRawUnits()` returns `null` if any non-Gogh top node isn't bound to a known raw span.
- Pending-block pairing skips any leaf whose body still contains `<!-- wp:` (cover, quote, gallery…) — uneditable rather than risk corrupting markup.
- The zoom modal refuses to move a section past unbound stored content: "That section can't move past other stored content yet."
- Chrome edits require the template part to resolve, or publish throws rather than silently dropping the header/footer.
- `stickyRawToggle` returns `null` unless the template part's first block is a `core/group` with parseable attrs.
- `reorderNavRaw` converts an automatic page-list into explicit navigation-links (an auto list has no order of its own).

---

## PART 21 — MESSAGES USERS ACTUALLY SEE

Toasts appear at the bottom, auto-dismiss after 4.5s (sticky for decisions), and can carry action buttons.

**Confirmations**: "Section moved." · "Added to the card — it moves and stacks with it now." · "Out of the card — it's a free element again." · "Removed from the card." · "\"<name>\" saved — it's in + Section under Your sections." · "Backup restored — publish when ready." · "Theme style applied: <name>" · "Linked." / "Link updated." / "Link removed — the text stays." · "Image swapped." · "The whole card links to <url>" · "Site header/footer updated across every page." · "Menu order updated — every page gets it." · "Experience added — it runs sandboxed; visitors can interact once published." · "Nothing overlaps this — it's already in front."

**Decisions (sticky)**: "gogh backed up unpublished work from an earlier session." (Restore it / Ignore) · "You have unpublished changes — switching the header reloads the page and discards them." (Switch anyway / Cancel — deliberately Gogh's own dialog, not `window.confirm`, because Chrome can suppress native dialogs)

**Errors**: "Publish failed: <message>" · "Could not save/delete/add that section." · "That saved section can't be read." · "Publish your changes first — changing the page style reloads the page." · "That section can't move past other stored content yet." · "Upload failed — .html uploads need admin rights." · "gogh can't safely swap this image." · "gogh found nothing it can edit in this section." · "gogh could not convert this block." · "gogh could not save the menu — <err>"

**In-flight**: Publishing… · Uploading… · Converting… · Applying… · Creating… · Loading media… · Loading pages… · Loading menu…

---

## PART 22 — TESTING AND DEV WORKFLOW

### Running the test suite
Append **`?gogh-test`** to any Gogh page URL, logged in as a user who can edit that page. Results appear in a fixed on-screen panel (top-left) and in `window.__goghTestResults = {summary, passed, total, jsErrors, results[]}`, plus a console log. **It never saves.**

The harness is a self-invoking IIFE, no framework. `test(name, fn)` catches throws; the model snapshot is **restored after every test**. It waits for `document.fonts.ready` because layout-dependent tests must measure with the real fonts. A global `onerror` handler collects `jsErrors` and reports them separately.

**104 tests**, covering: boot and selection · resize and reflow · drag and drag modifiers · snapping and grid · history and element ops · palette and UI · publish lifecycle · theme fidelity (typography, presets, palette, style variations) · sections (templates, duplicate, move, dividers, backgrounds, saved sections) · Gutenberg interop (boot sync, block parsing, scanning, Make freeform, resequencing) · paste and sanitisation · links · site chrome and menus · mobile and accessibility (XY-cut, phone preview and mobile overrides, WCAG 1.3.2 reading order) · cards, shapes, images, rotation · the picker · the WebMCP bridge.

**Note**: `?gogh-test` also enables the WebMCP bridge and the convert flag.

### Release
`.github/workflows/release.yml` triggers on `v*` tags: checkout → `rsync` excluding `.distignore` → zip → **copy to a second identical asset `gogh-playground.zip`** (identical bytes, separate download counter, because the Playground blueprint installs that one) → `gh release create --generate-notes`.

Usage: `git tag v0.83.0 && git push origin v0.83.0`.

`.distignore` excludes `.git .gitignore .distignore demo-assets blueprint.json gogh-canvas.zip gogh-editor.zip *.bak .DS_Store spike gogh-tests.js .github` — **so the test suite, the spike and the blueprint are not in the shipped zip.**

### Trying it — the Playground blueprint
`blueprint.json` boots a full demo in WordPress Playground: `landingPage: "/?gogh-edit=1"` (straight into the canvas), PHP 8.2, latest WP, networking on, auto-login as admin.

Steps: install **Twenty Twenty-Five** → install the plugin from the latest release's `gogh-playground.zip` → run a PHP seeder that creates a Gogh front page (id 99, "Gogh Editor"), switches Global Styles to the theme's "evening" variation, creates three ordinary Gutenberg pages (About / Team / Contact — the About copy explicitly invites pressing "Make freeform"), builds a navigation menu with real permalinks, repoints the theme's header template part at it, and sideloads four demo images (wheat-field, starry-night, sunflowers, almond-blossom) → set the front page.

### `spike/matrix.php`
A standalone CLI harness answering: *does the attrs-as-truth format survive the real WordPress save pipeline, for every kind of user, in both fallback variants?* It's the experiment that decided the production `gogh_rebake_enabled` default — a comment in `gogh.php` notes "variant B won the matrix".

```
php spike/matrix.php                 # variant A: no rebake
GOGH_REBAKE=1 php spike/matrix.php   # variant B: rebake on
```
It expects to live at `wp-content/plugins/gogh-editor/spike/` and requires a page with slug `gogh-spike-matrix` to exist.

It tests three users — admin, an editor with `unfiltered_html`, and a custom `gogh_filtered` role (editor caps minus `unfiltered_html`, "what every non-super-admin is on multisite, and everyone under `DISALLOW_UNFILTERED_HTML`") — across 8 scenarios: seed, admin re-save, editor re-save, filtered re-save, filtered second save, filtered autosave, filtered revision restore, filtered duplicate.

It also runs an **XSS probe** — injecting `"cssT":"</style><script>alert(1)</script>` as a hostile filtered-user attribute and asserting it never reaches `do_blocks()` output — and a **plugin-inactive check** that unregisters the block and confirms the baked style still renders pixel-perfect.

---

## PART 23 — QUICK ANSWERS TO LIKELY QUESTIONS

**"Will my pages break if I deactivate the plugin?"** No. Gogh saves static core blocks and ships the generated stylesheet inside the page content. Pages render identically with the plugin off. Cards degrade to stacked blocks in a group; Experiences degrade to a plain link. The `spike/matrix.php` harness specifically verifies this.

**"Does it work with my theme?"** It's designed for block themes. Gogh inherits typography, colours and button styles from Global Styles and offers your theme's palette and font presets in its controls.

**"Where is the editor?"** On the front end. Visit any page while logged in with edit rights and click "Edit with gogh" — in the admin bar or the corner button.

**"Why can't I free-scale text?"** By design. Text steps through your theme's font-size presets so pages stay on your design system. The three "Display S/M/L" sizes above the theme's largest are Gogh's own poster type, emitted as `cqw` in the section stylesheet.

**"Why can't I pick any colour?"** Also by design. Colours come from your theme palette only, so a style-variation switch re-skins the page correctly.

**"Why is snapping not landing on the grid?"** Grid snap is **off by default** — turn it on with the Grid button in the side palette. Element-to-element and section-edge snapping (6px) is always on unless you hold ⌘/Ctrl.

**"I added a block inside a Gogh section in the block editor and it disappeared."** Known beta limitation. Only the eight Gogh element types are preserved through a Gogh publish; other block types added from the block editor are lost on the next publish. Text, colour and image edits made in Gutenberg *are* adopted.

**"My inline formatting disappeared."** Only links, bold and italic survive in headings and paragraphs. Everything else is flattened by the inline sanitiser the moment Gogh touches the text.

**"'Make freeform' isn't showing."** It's a labs flag, off by default. Add `?gogh-convert=1` to the URL, or use the `gogh_convert_enabled` filter.

**"How do I let an AI agent drive the editor?"** Add `?gogh-mcp=1`, or enable the `gogh_webmcp_enabled` filter. Then the tools register via `navigator.modelContext`, or you can drive them from the console via `__goghMcp.call(...)`.

**"Why is my card falling apart on mobile?"** It probably isn't a card. Elements must be dropped *fully inside* a box to become its children. Look for the glow and the "Added to the card" toast. Free elements are linearised by the XY-cut algorithm, which keeps columns together but can't know that three loose elements were meant as one unit.

**"Publish failed."** Check the browser console. Common causes: the REST nonce expired (reload), the safety gates refused an ambiguous mapping, or a template part couldn't be resolved for a header/footer edit. Nothing is silently corrupted — Gogh's failure modes bail rather than guess.

---

# Generated facts appendix

Everything in this section is extracted mechanically from the Gogh source on every release. It is regenerated, never hand-edited. **Where this appendix conflicts with the prose above, this appendix is correct** — the prose may lag a release behind.

## Current release

- Plugin version: **0.99.387**
- Requires WordPress **6.5+**, PHP **7.4+**
- Text domain: `gogh-editor`
- readme.txt Stable tag: `0.26.0` · Tested up to: `7.0`
- Note: the readme Stable tag (`0.26.0`) does not match the plugin header version (`0.99.387`). Quote the plugin header version.

## Design constants

| Constant | Value |
|---|---|
| `TOL` | 8 |
| `MIN_H` | 560 |
| `PAD` | 72 |
| `SNAP` | 6 |
| `BASE` | 8 |
| `W` | 1200 |
| `autosave_interval_ms` | 15000 |
| `history_cap` | 60 |
| `mobile_breakpoint_px` | 700 |
| `min_resize_w` | 60 |
| `min_resize_h` | 32 |
| `section_min_h` | 160 |
| `section_max_h` | 4000 |
| `phone_preview_w` | 390 |
| `drag_threshold_px` | 4 |
| `rotation_snap_deg` | 15 |
| `rotation_magnet_deg` | 5 |
| `toast_ttl_ms` | 4500 |

## Section templates

Shown in the picker: **Hero**, **Cover**, **Big statement**, **Story**, **Numbers**, **Article**, **Featured product**, **Bestsellers**, **Feature cards**, **Pricing**, **Quote**, **Testimonials**, **Call to action**, **Get in touch**, **Profile card**, **Job card**, **Place card**, **Photo wall**, **Carousel**, **FAQ**, **Tabs**, **Gallery**, **Photo cards**, **Portfolio**, **Menu**, **Team**.

Non-starter (surfaced elsewhere): **Start from scratch**.

## Shapes and dividers

Shapes: `square` (Square), `rounded` (Rounded), `circle` (Circle), `pill` (Pill), `arch` (Arch), `tri` (Triangle), `diamond` (Diamond), `blob` (Blob).

Divider shapes (plus "None"): `curve` (Curve), `sweep` (Sweep), `dunes` (Dunes), `arch` (Arch), `sheet` (Sheet), `melt` (Melt), `mist` (Mist).

## Elements

Element types that survive a publish: `heading`, `para`, `button`, `image`, `badge`, `box`, `widget`, `exp`. Anything else added from the block editor is lost on the next Gogh publish.

"Add element" palette items: `badge`, `button`, `card`, `exp`, `form`, `heading`, `image`, `para`, `posts`, `products`, `write`.

## WebMCP tools

| Tool | Params | Required |
|---|---|---|
| `gogh_page_overview` | — | — |
| `gogh_list_layouts` | — | — |
| `gogh_add_section` | `layout` | `layout` |
| `gogh_paste_html` | `html` | `html` |
| `gogh_add_element` | `type`, `text`, `section` | `type` |
| `gogh_add_shape` | `shape`, `color`, `section` | `shape` |
| `gogh_set_section_background` | `section`, `color` | `section`, `color` |
| `gogh_edit_text` | `find`, `replace` | `find`, `replace` |
| `gogh_delete_section` | `section` | `section` |
| `gogh_publish` | — | — |

## `window.__gogh` members

`build`.

## WordPress surface

Block: `gogh/section` · v3 attributes: `css`, `model`, `scope`, `v`, `cssT`.

| Hook | Kind | Priority |
|---|---|---|
| `init` | action | 10 |
| `init` | action | 10 |
| `init` | action | 10 |
| `init` | action | 10 |
| `load-post-new.php` | action | 10 |
| `admin_menu` | action | 10 |
| `admin_bar_menu` | action | 10 |
| `get_edit_post_link` | filter | 10 |
| `page_row_actions` | filter | 10 |
| `post_row_actions` | filter | 10 |
| `template_redirect` | action | 10 |
| `wp_enqueue_scripts` | action | 10 |
| `init` | action | 10 |
| `body_class` | filter | 10 |
| `render_block_core/post-template` | filter | 10 |
| `body_class` | filter | 10 |
| `body_class` | filter | 10 |
| `init` | action | 10 |
| `manage_gogh_message_posts_columns` | filter | 10 |
| `manage_gogh_message_posts_custom_column` | action | 10 |
| `get_edit_post_link` | filter | 10 |
| `post_row_actions` | filter | 10 |
| `admin_menu` | action | 10 |
| `views_edit-gogh_message` | filter | 10 |
| `admin_post_gogh_messages_csv` | action | 10 |
| `admin_post_gogh_form_message` | action | 10 |
| `admin_post_nopriv_gogh_form_message` | action | 10 |
| `wp_enqueue_scripts` | action | 10 |
| `body_class` | filter | 10 |
| `wp_trim_words` | filter | 10 |
| `render_block_woocommerce/product-collection` | filter | 10 |
| `wp_enqueue_scripts` | action | 10 |
| `init` | action | 10 |
| `body_class` | filter | 10 |
| `wp_enqueue_scripts` | action | 10 |
| `body_class` | filter | 10 |
| `trashed_post` | action | 10 |
| `init` | action | 10 |
| `rest_api_init` | action | 10 |
| `init` | action | 10 |
| `init` | action | 10 |
| `get_block_templates` | filter | 10 |
| `body_class` | filter | 10 |
| `admin_post_gogh_shop_layout` | action | 10 |
| `render_block_core/group` | filter | 10 |
| `admin_post_gogh_product_related` | action | 10 |
| `admin_post_gogh_product_layout_all` | action | 10 |
| `admin_post_gogh_blog_layout` | action | 10 |
| `admin_post_gogh_post_layout` | action | 10 |
| `admin_post_gogh_post_layout_all` | action | 10 |
| `get_post_metadata` | filter | 10 |
| `admin_post_gogh_product_layout` | action | 10 |
| `safe_style_css` | filter | 10 |
| `wp_kses_allowed_html` | filter | 10 |
| `get_block_templates` | filter | 10 |
| `rest_api_init` | action | 10 |
| `rest_api_init` | action | 10 |
| `rest_pre_insert_wp_template_part` | filter | 10 |
| `rest_pre_insert_wp_navigation` | filter | 10 |
| `init` | action | 10 |
| `upload_mimes` | filter | 10 |
| `wp_insert_post_data` | filter | 20 |
| `save_post` | action | 10 |
| `wp_head` | action | 10 |
| `wp_enqueue_scripts` | action | 10 |
| `wp_enqueue_scripts` | action | 10 |
| `rest_api_init` | action | 10 |
| `enqueue_block_assets` | action | 10 |
| `admin_bar_menu` | action | 10 |
| `admin_bar_menu` | action | 10 |
| `admin_bar_menu` | action | 10 |
| `wp_insert_post` | action | 10 |
| `block_editor_settings_all` | filter | 10 |

Filters exposed for third parties: `gogh_default_editor`, `gogh_claims_post`, `gogh_labs_ask`, `gogh_rebake_enabled`, `gogh_schema`, `gogh_schema_enabled`, `gogh_webmcp_enabled`, `gogh_convert_enabled`, `gogh_helper_url`.

REST routes registered: `gogh/v1/version`, `gogh/v1/starter`, `gogh/v1/type-scale`, `gogh/v1/blog-style`, `gogh/v1/motion`, `gogh/v1/ask`, `gogh/v1/imagine-exp`, `gogh/v1/ask-key`, `gogh/v1/menu-style`, `gogh/v1/first-minute`, `gogh/v1/ask-log`, `gogh/v1/active-style`, `wp/v2/gogh-product/(?P<id>\d+)`, `wp/v2/gogh-product/(?P<id>\d+)/autosaves`, `gogh/v1/pattern`, `gogh/v1/render`.

Core REST endpoints used by the editor: `wp/v2/blocks`, `wp/v2/posts`, `wp/v2/template-parts`.

Capability checks in PHP: `edit_posts`, `edit_post`, `edit_theme_options`, `edit_others_posts`, `manage_options`, `upload_files`, `unfiltered_html`, `publish_pages`.

Query-string switches: `?gogh-edit`, `?gogh-ps`, `?gogh-test`.

## Exact UI labels (tooltips and button titles)

These are the real strings in the current build. Use them verbatim; never paraphrase a label.

- "' + d.label + '"
- "' + d.title.replace(/"
- "' + escAttr(l.name) + '"
- "' + escAttr(o[2]) + '"
- "' + escAttr(t.name) + '"
- "' + hp[1] + ' — ' + hp[2] + ' units"
- "' + p.slug + '"
- "' + sh.label + '"
- "+ Link"
- "+ Page"
- "A card — drop pieces inside and they stay together, even on mobile"
- "AG"
- "Aa"
- "Add"
- "Add a page to this menu"
- "Add a product ↗"
- "Add link"
- "Add something to this section"
- "Add to page"
- "Adjust spacing"
- "All options"
- "Answer-ready — see what machines see"
- "Apply"
- "As typed"
- "Auto"
- "Back"
- "Background & look"
- "Badge"
- "Bold"
- "Bring forward"
- "Browse them all →"
- "Button"
- "Cancel"
- "Card"
- "Close"
- "Copy machine version"
- "Copy style — then click other text to paint it"
- "Copy summary to share"
- "Create"
- "Custom colour"
- "Custom text colour"
- "Cycle theme font sizes"
- "Dark"
- "Delete (Del)"
- "Delete saved section"
- "Desktop"
- "Discard changes"
- "Done"
- "Duplicate (or Alt-drag)"
- "Experience"
- "Featured product"
- "Fill screen"
- "Fill the screen"
- "Fill the width — size the text to its box"
- "Forget the key"
- "Form"
- "Go"
- "Grid: show and snap"
- "Heading"
- "Help — ask gogh anything"
- "Image"
- "Imagine"
- "Italic"
- "I’ll find my own way"
- "Keep editing"
- "Keep this layout (updates every page)"
- "Keeps your changes on every page"
- "Left"
- "Light"
- "Link"
- "Link text (⌘K)"
- "Make it freeform"
- "Manage products"
- "Manage products ↗"
- "Manage this menu — reorder, nest, swap menus"
- "Move down"
- "Move down in the phone stack"
- "Move earlier"
- "Move later"
- "Move left"
- "Move right"
- "Move up"
- "Move up in the phone stack"
- "Move, duplicate, save, delete…"
- "Name, email and a message — straight into your own site, no plugin"
- "None"
- "One product, hero-sized — a card with a real add-to-cart button"
- "Open interactive experience"
- "Open your products in WordPress"
- "Original"
- "Outline"
- "Peek at pages"
- "Phone — see and tune the mobile layout"
- "Posts"
- "Products"
- "Publish"
- "Publish & close"
- "Put it back"
- "Redo (⇧⌘Z)"
- "Remove"
- "Remove from Your sections"
- "Remove from menu"
- "Remove image"
- "Remove link (keep the text)"
- "Remove this ' + labels.one + '"
- "Remove this photo"
- "Remove this slide"
- "Right"
- "Roll another take of this design"
- "Save"
- "Save brand"
- "Save description"
- "See all →"
- "Send backward"
- "Shape"
- "Show the next layout"
- "Six looks derived from your brand — hover to wear one, tap Remix again for six more"
- "Solid"
- "Start writing — a reading column, cursor ready"
- "Switch design"
- "Switch it on"
- "Text"
- "Text alignment"
- "Text colour"
- "The gogh build this tab is running"
- "The header rides along as visitors scroll"
- "Theme default"
- "Try another"
- "UPPERCASE"
- "Undo"
- "Undo (⌘Z)"
- "Unwrap — back to freeform"
- "Updates every page"
- "Upload a self-contained HTML experience — it runs sandboxed"
- "Upload an .html file instead"
- "Use this design"
- "Use this layout"
- "Write"
- "Your latest posts, live"
- "Your latest products, live — prices and add to cart included"
- "ag"
- "get one here"
- "gogh help"
- "lowercase"
- "pick from the shelf"
- "re-centre"
- "← All layouts"
- "↕ Spacing…"
- "▶ Auto-play"
- "⛶ Click to enlarge"
- "✦ Remix"
- "✨ Make freeform"
- "✨ Make it freeform"

## Exact toast and message copy

- "Added to the card — it moves and stacks with it now."
- "Back to freeform — drag it anywhere."
- "Backup restored — publish when ready."
- "Button updated."
- "Could not add that section."
- "Could not apply that style."
- "Could not delete that section."
- "Could not open the "
- "Could not open the layout panel."
- "Could not preview that layout — "
- "Could not rescale the type — "
- "Could not restore it."
- "Could not save that section."
- "Could not save the motion style."
- "Could not save the name — try again."
- "Could not save the page, so the "
- "Could not switch the "
- "Could not update the "
- "Editing the site "
- "Icon link updated."
- "Image swapped."
- "Keep your changes with Done, or undo them with Cancel."
- "Kept — "
- "Link removed — the text stays."
- "Link updated."
- "Linked."
- "Logo set — your image now leads the header."
- "Logo size saved."
- "Made the card’s words readable on its background."
- "Menu order updated — every page gets it."
- "Menu switched — every page shows it."
- "Mobile menu: "
- "Nothing to rearrange yet — add a couple of pieces first."
- "Out of the card — it’s its own piece again."
- "Publish failed: "
- "Publish your changes first — changing the page style reloads the page."
- "Removed from the card."
- "Saved — search results and AI answers now quote your words ✓"
- "Saving your page changes, then updating the "
- "Section moved."
- "Section removed — publish to make it real."
- "Site "
- "Site name saved."
- "Style copied — click other text to paint it. Esc finishes."
- "Text title restored — click it to rename your site."
- "That saved section can’t be read."
- "That section can’t move past other stored content yet."
- "That’s gogh. Everything else is just more of this."
- "The backup could not be read."
- "The description could not be saved — try again."
- "The name didn’t save — try again."
- "Theme style applied: "
- "This "
- "This header has no menu button to open."
- "This page is “"
- "Unpublished page changes will be lost when the "
- "Upload failed — .html uploads need admin rights."
- "Uploading experience…"
- "Wrapped — the words flow around it now. Click the image to adjust."
- "Your site is now called “"
- "gogh backed up unpublished work from an earlier session."
- "gogh can’t safely swap this image."
- "gogh could not change the page style — "
- "gogh could not create that page."
- "gogh could not keep the menu style — "
- "gogh could not rename the site — that needs an admin login."
- "gogh could not save the menu — "
- "gogh could not save your brand — "
- "gogh could not set the logo — "
- "gogh could not switch back — "
- "gogh could not switch the design — "
- "gogh could not switch the menu — "
- "gogh couldn’t find that icon in the stored markup."
- "gogh couldn’t identify that icon."
- "gogh couldn’t restore it."
- "gogh couldn’t safely update this image in the saved markup."
- "gogh couldn’t save that menu order."
- "gogh found nothing it can edit in this section."
- "gogh helper: "
- "gogh: preview of “"
- "“"
- "✨ “"

## Test suite

`211` tests, run by appending `?gogh-test` to any Gogh page URL while logged in with edit rights on that page.
