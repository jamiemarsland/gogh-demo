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

Generated for plugin version 0.99.598 · knowledge base 632c511.

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
Click **👁 View site** in the WordPress admin toolbar, at the top left of the screen — not in the side rail (it read *Exit gogh editor* before v0.99.598). If clean it goes straight to the site as visitors see it. If dirty you get a dialog:
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
Eight handles: nw, n, ne, e, se, s, sw, w. Minimum size **60 wide × 32 tall**. Handles snap to other elements' edges/centres and section edges/centre; otherwise they round to the 8-unit base (24 with the grid on).

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

### Cards (drop-to-join)
Drop an element **fully inside** a plain box and it becomes a **child ("kid") of that card** — one level only. The card glows as a drop target. A **plain shape can join too, as decoration** (v0.99.576): a circle behind a number, a stripe along the top. It sits behind the words, never pushes them and is never pushed, and on phones it steps aside like any decorative shape (a plain box kid keeps its proportions as a band). Limits: a box that has kids never joins (cards stay one level deep), nothing can join a kid, and a shape more than about 60% of the box's area is a box on a box, not decoration, so it stays a sibling. To recolour a shape kid, drag it out, use its panel, drop it back. Toast: **"Added to the card — it sits behind the words and moves with them."** (+Undo)
- Toast on join: **"Added to the card — it moves and stacks with it now."** (+Undo)
- Drag a kid outside the card bounds to free it: **"Out of the card — it's its own piece again."** (+Undo)
- `Delete` on a kid: **"Removed from the card."** (+Undo)
- **The first press always gets the card.** Click a piece inside a card that is not yet chosen and the card is selected, not the piece; click the piece again to reach it (a third click edits its text; on a button kid the second click opens its link panel). While the card rides in a group selection, its pieces cannot be grabbed at all — a drag from anywhere on it moves the group. A loaded paint roller is the one exception: it paints the piece it touches. (v0.99.575; James kept pulling numbers out of cards he meant to move as a row.)

Cards matter because they're what keeps an image + heading + button together when the layout stacks on mobile.

---

## PART 5 — THE UI, CONTROL BY CONTROL

### The side rail (left edge: Page · Site · SEO)
Three tabs sit on the left edge of the window in edit mode. Click one and a drawer docks beside the page (the page shrinks to fit next to it; nothing opens on hover, and there is no palette on the right any more). There is no exit control here — leaving edit mode is **View site (was Exit gogh editor)** in the admin toolbar.
- **Page** — this page's doors, **Add a page** first (v0.99.585): type a title, press Add, and the page is created and opens in the editor straight away. On arrival a toast says it is a new page, with **Put it in the menu** (one click: the page goes to the end of the site menu, which the header shows on every page) and **Undo** (bins the page and goes back to the page Add was pressed on). Nothing joins the menu unless asked — thank-you pages and drafts are pages too. Then **Page style** and **Rearrange sections**.
- **Site** — the design of the whole site as cards: **Site style** (colours and looks, hover to try, click to keep, Remix), **Fonts** (pairs, tried on your page), **Motion**, **Edit header**, **Edit footer**, **Page style**, **Rearrange sections** (the zoom-out page map). Some doors dock as a panel in the same place; **Back** returns to the cards.
- **SEO** — what the page says to search engines.
The drawer's foot holds **Grid: show and snap** (the painted rhythm grid, 24-unit tiles and a heavier line every 72; off by default), **Undo (⌘Z)** and **Redo (⇧⌘Z)**.

### Adding something to a section
Hover a section and its pill appears; its **＋ Add** opens the **Add to this section** menu: `Heading` · `Text` · `Button` · `Image` · `Video` · `Badge` · `Embed` · `Icon` · `List` · `Line` · `Write` (a reading column, cursor ready) · `Card` (drop pieces inside and they stay together, even on phones) · `Experience` (a self-contained HTML experience) · `Form` (name, email, message), plus `Posts` and `Shop` when those add-ons are on. Or press **/** with nothing being edited for the quick-add search of the same list. Either way the piece lands centred in the section, on the rhythm, below anything it would have covered. A **+ Section** pill between sections adds a whole new section from the layouts.

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

### Section toolbar (one pill, on hover and while a section is selected)
Move the pointer over a section, or click its empty ground, and one pill docks at its top-left (v0.99.578–589; the first three testers asked "Where is the + button?"): **＋ Add** in words with an ink disc — the door to Heading, Text, Button, Image, Form, Contact and the rest — then **Background** (background & look) in words, then the die and its take label where the design has other takes, then **⋯** which holds `Move up` · `Move down` · `Duplicate` · `Save to reuse` · `Delete` as words. Up/Down disable at the ends. The pill pins to the top of the window while a tall section is on screen. It goes when the pointer leaves; a selected section keeps it. Choosing a piece inside the section folds it, and it stays away while the pointer works mid-section (typing included); reaching for the top band of the section brings it, and so does letting the piece go. Any open panel keeps it away. Never shown for the site header or footer.

### Panels
**Line** (the rule piece) has three dials (v0.99.590): **Weight** Hairline · Fine · Bold; **Style** Solid · Dotted · Dashed (dots and dashes are sized by the weight); **Width** As placed · Full width (edge to edge of the section, and As placed puts it back where it was). Its colour rides the bar's swatch like words.

Shape · Add a shape ("A backdrop for the rest — send it backward once it's placed.") · Button (Style: Solid / Outline; swatch rows Background, Text, Hover background) · Image (URL field, alt text, Upload, Remove image, media grid) · Replace image · Text colour · Link / Link text ("Apply", "Remove link (keep the text)") · Section background · Section transition · Save this section · Site style · Page style · Site header / Site footer · Menu / Add to menu · Imported block.

### Section transition panel
Divider shapes: **None, Wave, Curve, Slant, Peaks, Brush, Torn, Melt**. Plus **Above** / **Below** colour pickers with theme palette swatches, and an overlap slider that pulls the divider up over the section above.

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

Saving a section: the ⋯ menu's "Save to reuse" (panel title "Save this section") stores it via `wp/v2/blocks` as an **unsynced pattern** (`meta.wp_pattern_sync_status = 'unsynced'`), in the v2 section format. It then appears under **+ Section → Your sections**. Because it's unsynced, later edits to the saved section do *not* propagate to copies already placed on pages.

---

## PART 7 — SNAPPING, GUIDES AND SPACING

- **Snap threshold is 6 design px.**
- Snap candidates: the section's left (0), right (1200) and horizontal centre (600); top, bottom and vertical centre; plus every other element's left, right, centre-x, top, bottom, centre-y. The dragged element's own left, right and centre edges are all tested.
- **Grid snap is off by default** (opt-in via the Grid button). Mid-gesture the major lines (72) show and an edge near one lands on it; otherwise positions round to the **8-unit** base. With the grid on, the minors (24) show too and the mesh a drop rounds to is **24** — a person who asks for the grid gets the rhythm, not an 8-pixel mesh. Arrow-key nudges stay at 8.
- **Alignment guides**: one vertical and one horizontal line, drawn across the full section, only while a snap is captured.
- **Equal-spacing snap**: when an element sits between two neighbours, the exact midpoint captures within 8px of the raw pointer and **overrides** edge snapping and grid parity — so equal gaps are always reachable.
- **Spacing labels**: up to 4 live distance badges, measuring to the nearest neighbour on each side. With no neighbour on a side, it measures to the **section edge** (page margins are the distances people eyeball most). Gaps under 4 design px, or under 14 rendered px, aren't drawn. Equal gaps get a `=` prefix and turn blue.
- **Shift** during drag pins the locked axis and suppresses its guide. **⌘/Ctrl** disables all of the above.
- **The rhythm.** Everything gogh spaces on its own — the section templates, the scratch section, the bands the connector compiles — sits on one vertical rhythm: positions and heights on multiples of **24** (`RHYTHM`), section heights on multiples of **72** (`MAJOR`). It governs what gogh chooses, never what a person places: a hand-dragged 30 stays 30, and the read-back of a layout never rounds it. The group bar's **Even gaps** (top to bottom) and **Tidy up** choose their vertical gaps on it — the top piece stays where the person put it, the gap becomes 24, 48, 72…, and the toast names it; side-to-side gaps and a card's inner spacing are not on the rhythm. A new piece from the shelf is born on it too: every default height and resting y is a multiple of 24, it lands on a 24, and if that would put it on words already there it goes 24 under them instead (a shape, being a backdrop, may sit behind words). The section floor `MIN_H` is 576, eight majors. The idea comes from Josh Puckett's rule of one major and one minor spacing unit: an agent keeps a rule it can count, and a page whose gaps are all 24 or 72 reads as one design.

---

## PART 8 — ARCHITECTURE (technical)

Single-file ES5 IIFE, no build step. `gogh-editor.js` is ~9,190 lines. It bails immediately unless `window.GOGH` exists (localized from PHP).

### Design constants
```js
var TOL = 8, MIN_H = 576, PAD = 72, SNAP = 6, BASE = 8, W = 1200, RHYTHM = 24, MAJOR = 72;
```
`W = 1200` is the **design width** — every model coordinate is in design units against a 1200-unit-wide section. `TOL` is the solver's line-clustering tolerance, `SNAP` the alignment tolerance, `BASE` the mesh a drop rounds to with the grid off (and the Shift-nudge), `PAD` the bottom padding added to section height, `MIN_H` the section floor. `RHYTHM` (24) and `MAJOR` (72) are the vertical rhythm gogh keeps for its own gaps — see Part 7.

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
- `minH` — design-unit minimum height (default 576, eight majors; 504 for an empty bootstrap)
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

`designH(els, minH)` = `max(minH || 576, maxBottom + 72 rounded up to the next 72)` — a section that outgrows its floor still lands on a major.

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

**v2 (legacy — still used for chrome / template-part saves *and* for "Save to reuse"):**
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

**Confirmations**: "Section moved." · "Added to the card — it moves and stacks with it now." · "Out of the card — it's its own piece again." · "Removed from the card." · "\"<name>\" saved — it's in + Section under Your sections." · "Backup restored — publish when ready." · "Theme style applied: <name>" · "Linked." / "Link updated." / "Link removed — the text stays." · "Image swapped." · "The whole card links to <url>" · "Site header/footer updated across every page." · "Menu order updated — every page gets it." · "Experience added —" followed by "sandboxed in its frame, saved in your media library." · "Nothing overlaps this — it's already in front."

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

### Launch counter (v0.99.549)
Every blueprint's boot step writes `gogh_booted_as` with its own name (`default`, `halloran`, `built` for sites the helper publishes, and so on). The first page the site shows after that sends one beacon to the helper, `POST /api/boot` with `{ bp, v }`, and flips `gogh_boot_pinged` first so a lost ping is never retried. The helper ignores localhost and Studio by origin, caps one address at ten an hour, and keeps `boot:total` and `boot:bp:<name>` in KV without expiry. `GET /api/boot` returns `{ total, seed, counted, blueprints }`; the total starts at 776, the launches counted before the beacon (GitHub release downloads, 1–12 August 2026, and the weeks after that the raw zip could not count). The Plugins screen row says "Launched N times on Playground" (cached six hours) and the release workflow stamps the same number into the demo README.

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

**"Why is snapping not landing on the grid?"** Grid snap is **off by default** — turn it on with the Grid button at the foot of the side rail's drawer. Element-to-element and section-edge snapping (6px) is always on unless you hold ⌘/Ctrl.

**"I added a block inside a Gogh section in the block editor and it disappeared."** Known beta limitation. Only the eight Gogh element types are preserved through a Gogh publish; other block types added from the block editor are lost on the next publish. Text, colour and image edits made in Gutenberg *are* adopted.

**"My inline formatting disappeared."** Only links, bold and italic survive in headings and paragraphs. Everything else is flattened by the inline sanitiser the moment Gogh touches the text.

**"'Make freeform' isn't showing."** It's a labs flag, off by default. Add `?gogh-convert=1` to the URL, or use the `gogh_convert_enabled` filter.

**"How do I let an AI agent drive the editor?"** Add `?gogh-mcp=1`, or enable the `gogh_webmcp_enabled` filter. Then the tools register via `navigator.modelContext`, or you can drive them from the console via `__goghMcp.call(...)`.

**"Why is my card falling apart on mobile?"** It probably isn't a card. Elements must be dropped *fully inside* a box to become its children. Look for the glow and the "Added to the card" toast. Free elements are linearised by the XY-cut algorithm, which keeps columns together but can't know that three loose elements were meant as one unit.

**"Publish failed."** Check the browser console. Common causes: the REST nonce expired (reload), the safety gates refused an ambiguous mapping, or a template part couldn't be resolved for a header/footer edit. Nothing is silently corrupted — Gogh's failure modes bail rather than guess.

### Fonts: pairs, not pickers (v0.99.543)

A **Fonts** card in the Site drawer owns everything about type: the type size (Snug/Regular/Airy/Grand), the theme's own font pairs (first, in the same list, each with a mood word such as 'nerdy' or 'sturdy' worked out from the faces) and twelve heading-and-body pairings with plain names for their character (the theme's own first, then bookish, editorial, studio, modern, warm, elegant, bold, classic, quiet luxury, loud, technical), each row set in its own faces. Hover paints the page in the pair (the editor loads it from Google to try it; nothing is saved); click keeps it. Keeping installs the families through WordPress's own Font Library — the files are downloaded once into the site's font folder and registered as font family and font face posts — then writes the two families into the site's global styles (`styles.typography.fontFamily` and `styles.elements.heading.typography.fontFamily`) and activates them in `settings.typography.fontFamilies.custom`. The toast reads "Fraunces & Inter. Installed on your site, yours to keep." with Undo, which puts the previous families back (nothing is uninstalled). The published page serves the fonts from the site itself, never from Google, and the type survives gogh being deactivated. Two families a page, never a third. **More fonts** folds under the twelve: type a name, pick Headings, Body or Both, and the hits appear — the theme's own families first, then Google's list (WordPress's own font collection, fetched through the site), each shown as the collection's preview of the name. Hover tries it on the page in that role; click keeps it, installing only the family that changed. **Brand guidelines** that name a font the site lacks ("Headings: Cormorant Garamond") get it the same way: the brand door looks the name up in Google's list, tries it on the page while the form is open, and installs it when the brand is kept; the receipt line says "from your guide, installed when you keep". A kept pair from the Fonts door also becomes the brand's fonts when a brand exists, so Remix keeps them. **Remix draws on the same pairs**: without a brand pinning its fonts, a roll picks a font pair by a coin — the theme's own combinations or one of the twelve — tries it from Google, and installs it quietly when the look is kept (the receipt ends "in Fraunces & Inter"). **The AI door** may say `fonts: 'bookish'` or `fonts: { heading: 'Fraunces', body: 'Inter' }` in a site definition; the fonts are installed as the site is built. `__gogh.fontPairs()`, `__gogh.openFontsPanel()`, `__gogh.fontsDry(on)` for tests.

**Two families a page (v0.99.553).** Every keep — a pair, one role from More fonts, a theme preset — trims the site's active family list (global styles `settings.typography.fontFamilies.custom`) to the families the body and heading refs point at. Dropped families stay in the Font Library, so choosing one again is instant with no download; the Undo on the toast puts the dropped ones back. Remix already wrote only the two in use, so the doors now agree.

### User testing (v0.99.556)
A site booted from `blueprint-usertest.json` (the tester is sent to the helper's `/test` page, which thanks them for helping make gogh better, explains the persona — Elliot Smith, a photographer, on a site that still says Elliot Grey so renaming it is a real task — and has the Start button) carries the option `gogh_user_test`. On such a site the plugin loads `gogh-usertest.js`: a small card on the right, below the site header so the menu stays visible, with seven tasks (site name · headline · swap a photo · a different look · a way to get in touch · a Prices page in the menu · phone check then publish and view), each with Done, Couldn't do it, an optional note and a hint (opening the hint is recorded), then a four-question wrap-up (happy 1–5, could finish, how it felt, what confused) and an optional name. The card keeps its place in localStorage across reloads and the walk between editor and published site. Events go to the helper's `POST /api/test` under a random session id (`gogh_user_test_session`), batched and retried. Jamie reads them at `/tests` behind a password (the Worker secret `TEST_TOKEN`, which Jamie chose; the refresh token also works): one row per tester, click for the tasks, times, notes and wrap-up. Tasks come from `gogh_user_test_tasks()`; the option may carry its own `tasks` array, and the `gogh_user_test_tasks` filter can change them. Nothing else is collected.

### Gap magnets and the numbers (v0.99.557)
While dragging, two gap magnets work alongside the edge and centre magnets. **Equalise** (older): between two neighbours, the midpoint that makes both gaps equal captures within 8 units. **Repeat** (new): at the end of a run, the gap the nearest neighbour already keeps to the next piece is offered, so a fourth card lands in step with three; captured within 8 units, never on a purely locked axis, beaten only by equalise. Both work on either axis. **The numbers**: a plain drag stays quiet; the moment a gap magnet holds, every gap in that run shows its number in a blue "= 40" badge (the dragged piece's gap and the one it copies), and they go on drop. Alt during a drag still shows every ruler as before. From the Canva teardown: alignment as placement, not repair — the beginner compares two digits instead of two stretches of whitespace.

### The rest of the Canva teardown (v0.99.558)
**Size matching on resize:** dragging a side or corner handle also snaps to a neighbour's width (and, for pieces with a real height, its height); the guide's chip says "same width" / "same height" so the person knows why the handle stopped. **Match size** on the group bar (in the Tidy up row): every selected piece takes the size of the first one picked — width always, height only where height is a number (words hug their own); greyed with "Already the same size" or "Needs two or more pieces". **Alt + arrow** moves the selected piece to the next magnet in that direction (sibling edges and centres, the section's edges, centre and margin), with the guide shown for a moment, so the keyboard reaches every position the mouse can; plain arrows still nudge 1, Shift 8. **Drag threshold:** a piece moves only after 4px of travel (10 for a finger), so a twitch on a click never nudges it; the marquee selects anything it touches, not only what it surrounds. **The parity set** from the teardown lives in the suite as six tests (edge at 5 snaps, 10 does not, equalise, repeat, width match says so, a 2px twitch is a click).

### Rhythm gaps (v0.99.559)
The third gap magnet, below equalise and repeat: with a neighbour on a side and nothing to copy, gaps of 24, 48 and 72 from that neighbour are offered within 6 units (`rhythmGap`), on either axis, so a hand-placed piece lands in gogh's own spacing without anyone knowing the rhythm exists. The drop keeps the gap exactly, and the number shows in the gap while it holds, like the other gap magnets. Nothing catches when the pointer is more than 6 from any of the three.

### The group bar, quieter (v0.99.560)
Select two or more pieces and the bar reads: Make a card · Duplicate · Delete · Tidy up · Align ▾. **Tidy up** is the one-click fix and now carries everything: it squares each row to its topmost piece, evens the gaps (across, the ends hold; down the page the gap lands on the rhythm), and matches sizes that are nearly the same (within a tenth) to the first piece's — wildly different sizes are a design choice and stay. **Align ▾** opens one row of six icons: the word-processing trio (left, centre, right), a hairline, then top, middle, bottom; hover says the word ("Align top") and a faded icon says what is already right ("Tops already line up", "Left edges already line up", "Already centred on the page") or why not ("Would put words on words"). Even gaps and Match size are gone as separate verbs, and so is the sentence that explained the greys. A card's own bar has the same Align ▾ with the three sideways icons and its Even gaps for the stack inside it.

### Centring a group on the page (v0.99.561)
Two ways, both from James asking how to centre three cards. **Dragging several pieces together:** the group is one thing to the page — its own centre snaps to the page centre and its outer edges to the margins, within 6 units, with the same pink centre guide a single piece gets; the pieces keep their gaps. **Behind Align, a seventh icon, Centre on the page:** moves the whole selection as one so its centre sits on the page's, greyed "Already centred on the page" when it is. The six other icons still align pieces with each other, not with the page.

### Align offers only what fits the shape (v0.99.562)
Behind Align, the icons follow the shape of the selection (`selectionShape`): pieces beside each other are a row, so only top, middle, bottom and Centre on the page show (Align left would pile a row of cards on each other); pieces above each other are a stack, so only left, centre, right and the page show; a grid, or a scatter, shows all seven. Hidden, not faded — a verb that could never make sense here is not offered at all.

### Fonts door: faces load on open (v0.99.564)
Opening the Fonts door loads every listed pair's faces at once — Google's (`ensureGoogleFonts` per pair) and the theme's own from the theme folder (`ensureVariationFonts` per theme pair, via the FontFace API, every weight and style of each family, since the row's heading name is drawn in the bold) — so each row is drawn in its own face from the start. Note: `document.fonts.check()` answers true for a family with no face at all, so the loader looks for a loaded face by name, weight and style instead. Before, a row's faces loaded only when hovered, and the swap from the fallback face as the pointer passed looked like the other rows changing size.

### Help in the admin bar (v0.99.566)
The helper's welcome panel opens with a 35-second film of gogh being used (v0.99.592): muted and looping, with a sound toggle and Full screen, and it stops the moment a question is asked. The file is served from the gogh-demo repo through jsDelivr, so neither the plugin nor the Worker carries its weight. The in-editor help sheet has a **⤡ widen** button beside its close, which takes it from 380px to 820px for reading a long answer or watching the film; the iframe allows fullscreen.

**Seeing the page on a phone (v0.99.594).** Two words sit at the top right of the admin bar the whole time you are editing, just left of the ? help button: **Desktop | Phone**. Press **Phone** and the page is pinned to a phone's width (390px) on the zoomed-out desk, with the page's real mobile layout live, so you can tune it (Hide on phone, move up/down in the phone stack). It needs no drawer open; **Desktop** puts the page back. The Page drawer has the same door as a card, **See it on a phone**. Opening and closing drawers or panels while on the phone keeps the phone view; only Desktop (or ⌥Z) leaves it. Leaving the editor leaves it too. Seven of the first sixty-four testers reported "there is no phone button" because the switch used to live inside the zoom slider, which only appears while a drawer is open. There is no phone button in the zoom slider any more.

**Hiding things on phones, from the desktop (v0.99.596).** You no longer have to be in the phone view to learn that pieces can be hidden or reordered for small screens. Every piece's bar has a **phone button** (a small phone; lit and slashed once the piece is hidden on phones) — press it to hide the piece on phones, press again to show it. Every piece that has a panel (button, image, video, box, widget, line, icon, embed) ends with an **On phones** row: **Shown | Hidden** and **See it on a phone**, which opens the phone view. A section's ⋯ menu has **Hide on phones** (then **Show on phones**). Hiding says so in a toast with a *See it on a phone* door, and on the desktop canvas a hidden piece wears a small **Hidden on phones** tag. Inside the phone view one sentence sits under the phone the whole time: *"Press anything to hide it on phones or move it up and down."* Reordering for phones (move up / move down) still lives in the phone view only.

**The phone view is the page's own phone CSS (v0.99.597).** The artboard is phone-wide but the browser window is not, so rules written for narrow windows (`@media (max-width: …)`) — the header's hamburger menu, shop grids, blog cards, forms — used to keep answering to the window, and the phone view showed a desktop menu squeezed into a phone. Now, entering the phone view, gogh walks the page's stylesheets and flips each such rule's media condition on the live CSSOM: on for rules a 390px-wide phone would fire, off for desktop-only (`min-width`) ones, leaving rules that are not about size (hover, reduced motion) alone and the editor's own stylesheets untouched. Desktop puts every condition back. Nothing is copied or rewritten, so the phone view is exactly the page's mobile CSS, including the real hamburger, which opens inside the artboard.

A small ? sits in the admin bar just left of Howdy, on every screen where the bar shows, for any logged-in user when a helper URL is set. In the gogh editor it opens the help sheet (the same one the side rail's ? opens); on any other screen, wp-admin included, it opens the helper in a new tab. The node is `gogh-help` under `top-secondary`; the `gogh_helper_url` filter turning the URL off removes it.

### The admin bar's edit label never flashes (v0.99.567)
One function, `gogh_ab_edit_label( $editing, $is_post )`, renders the bar's gogh node — 'Edit with gogh' / 'Edit post' on a page, 'View site' (before v0.99.598: 'Exit gogh editor') in the editor — and the editor receives both variants (`cfg.abEdit`) so when the mode flips without a reload it writes the same markup the server would. Before, the server rendered a tick and 'Done' while the page loaded and the editor's script then wrote 'Exit gogh editor' over it, so 'Done' flashed for an instant on every open.

### Pictures published light (v0.99.568, the speed pass)
gogh's pictures used to publish as plain `<img src>` with no attachment id or size, so WordPress could not give them srcset, sizes, lazy loading or a fetch priority — a photographer's home was 3MB with a 7.8s LCP on a phone. Now a content filter (`gogh_light_images`, priority 11, just before core's own) finds the attachment behind each upload URL (`gogh_media_id_for_url`, cached a day), adds the `wp-image-ID` class and the intrinsic width/height, and core adds srcset, sizes, `loading="lazy"` for pictures below the first, `fetchpriority="high"` for the first. A section's background photo gets a `large` (1024px) variant under `@media (max-width: 700px)`, and on a single page the first section's background is preloaded (phone size for phones, full for the rest). Nothing to republish: it reaches every page ever published. The same release gates the look CSS (reading looks only on single posts, blog looks only on lists of posts or pages with a posts rail) and the view script's chunks (the Manual contents on posts, the video-pause control and the carousel/lightbox only where the page carries them). Measured: that home 3009KB → 737KB, LCP 7.8s → 4.2s, score 73 → 85; a text-only page 366KB → 325KB, 90 → 93.

### Pictures from Unsplash, published light (v0.99.569)
The starters' photos come from Unsplash by URL with a `w=` width. As the content is served, each such picture gets a srcset of the same URL at 480, 768, 1024, 1400 and 1800 (up to its own width) with `sizes="(max-width: 700px) 100vw, min(100vw, Wpx)"`; the first picture on the page gets `fetchpriority="high"`, the rest `loading="lazy"`. A section's Unsplash background gets a `w=900` variant on phones. The page's first picture, tag or background, upload or Unsplash, is preloaded — for a tag with the same srcset, so the browser reuses one download. Measured on a hosted InstaWP site (the photographer design): mobile 75 → 83, LCP 7.0s → 4.5s, 1,584KB → 987KB; desktop 88 → 90.

### Honest sizes, a description, a preconnect (v0.99.570)
**Honest sizes:** WordPress guesses every picture is as wide as the screen. gogh reads each picture's real share of its section from the stored CSS (the grid's cqw columns and the piece's grid-area, `gogh_piece_share`) and writes `sizes="(max-width: 700px) 100vw, 53vw"`-style values after core's own pass (priority 13); the first picture's preload speaks the same sizes so one download serves both. Desktop on the hosted photographer: 1,401KB → 914KB, the hero 640KB → 457KB. **A description for every page:** `gogh_page_description()` — the excerpt if written, else the first paragraph that reads as a sentence (40+ characters; eyebrow lines like 'People · Places · Quiet moments' are skipped), else the tagline — as `<meta name="description">` on singular pages and the front page, unless an SEO plugin (Yoast, Rank Math, AIOSEO, SEOPress, The SEO Framework, Slim SEO) is active. **Preconnect** to images.unsplash.com when the page's content uses Unsplash pictures. The InstaWP SEO score of 61 was the sandbox's own 'discourage search engines' setting (noindex) plus the missing description; gogh's part is fixed.

### What should go here: typing filters (v0.99.571)
The + between sections opens "What should go here?" with a box and eight chips. Typing now narrows the chips live to what matches — the panel's own ideas (Testimonials, Pricing, Contact form…) and every section design by name (Carousel, Photo wall, FAQ, Numbers…); a chip adds that section; Enter or Add takes the top match; nonsense leaves "Nothing here matches" and Browse them all, which always stays as the way to the full shelf. Clearing the box brings the usual chips back.

---

# Generated facts appendix

Everything in this section is extracted mechanically from the Gogh source on every release. It is regenerated, never hand-edited. **Where this appendix conflicts with the prose above, this appendix is correct** — the prose may lag a release behind.

## Current release

- Plugin version: **0.99.598**
- Requires WordPress **6.5+**, PHP **7.4+**
- Text domain: `gogh-editor`
- readme.txt Stable tag: `0.26.0` · Tested up to: `7.0`
- Note: the readme Stable tag (`0.26.0`) does not match the plugin header version (`0.99.598`). Quote the plugin header version.

## Design constants

| Constant | Value |
|---|---|
| `TOL` | 8 |
| `MIN_H` | 576 |
| `PAD` | 72 |
| `SNAP` | 6 |
| `BASE` | 8 |
| `W` | 1200 |
| `RHYTHM` | 24 |
| `MAJOR` | 72 |
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

Shown in the picker: **Hero**, **Cover**, **Big statement**, **Story**, **Numbers**, **Article**, **Featured product**, **Bestsellers**, **Editorial split**, **Columns of links**, **Featured and links**, **Picture doors**, **Categories in the menu**, **Featured product in the menu**, **Shop the look**, **New in**, **Sale**, **Categories**, **Feature cards**, **Pricing**, **Quote**, **Testimonials**, **Call to action**, **Get in touch**, **Profile card**, **Job card**, **Place card**, **Photo wall**, **Carousel**, **FAQ**, **Tabs**, **Gallery**, **Photo cards**, **Portfolio**, **Menu**, **Team**.

Non-starter (surfaced elsewhere): **Start from scratch**.

## Shapes and dividers

Shapes: `square` (Square), `rounded` (Rounded), `circle` (Circle), `pill` (Pill), `arch` (Arch), `tri` (Triangle), `diamond` (Diamond), `blob` (Blob).

Divider shapes (plus "None"): `curve` (Curve), `sweep` (Sweep), `dunes` (Dunes), `arch` (Arch), `sheet` (Sheet), `melt` (Melt), `mist` (Mist).

## Elements

Element types that survive a publish: `heading`, `para`, `button`, `image`, `video`, `embed`, `icon`, `rule`, `badge`, `box`, `widget`, `exp`. Anything else added from the block editor is lost on the next Gogh publish.

"Add element" palette items: `badge`, `button`, `card`, `embed`, `exp`, `form`, `heading`, `icon`, `image`, `list`, `para`, `posts`, `products`, `rule`, `video`, `write`.

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
| `render_block_core/heading` | filter | 10 |
| `body_class` | filter | 10 |
| `render_block_core/post-template` | filter | 10 |
| `query_loop_block_query_vars` | filter | 10 |
| `body_class` | filter | 10 |
| `body_class` | filter | 10 |
| `init` | action | 10 |
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
| `render_block_data` | filter | 10 |
| `body_class` | filter | 10 |
| `render_block_core/navigation` | filter | 10 |
| `template_redirect` | action | 302 |
| `save_post_gogh_panel` | action | 10 |
| `render_block_core/navigation-link` | filter | 10 |
| `wp_trim_words` | filter | 10 |
| `render_block_data` | filter | 10 |
| `render_block_core/group` | filter | 10 |
| `pre_render_block` | filter | 1 |
| `render_block_woocommerce/product-collection` | filter | 10 |
| `render_block_woocommerce/product-collection` | filter | 10 |
| `wp_enqueue_scripts` | action | 10 |
| `init` | action | 10 |
| `body_class` | filter | 10 |
| `wp_enqueue_scripts` | action | 10 |
| `body_class` | filter | 10 |
| `wp_insert_post_empty_content` | filter | 10 |
| `trashed_post` | action | 10 |
| `wp_footer` | action | 99 |
| `admin_footer` | action | 99 |
| `plugin_row_meta` | filter | 10 |
| `wp_enqueue_scripts` | action | 20 |
| `wp_footer` | action | 10 |
| `init` | action | 10 |
| `rest_api_init` | action | 10 |
| `init` | action | 10 |
| `init` | action | 10 |
| `get_block_templates` | filter | 10 |
| `body_class` | filter | 10 |
| `admin_post_gogh_shop_layout` | action | 10 |
| `add_meta_boxes_product` | action | 10 |
| `admin_enqueue_scripts` | action | 10 |
| `admin_body_class` | filter | 10 |
| `enter_title_here` | filter | 10 |
| `admin_notices` | action | 10 |
| `admin_menu` | action | 1 |
| `admin_title` | filter | 10 |
| `admin_bar_menu` | action | 10 |
| `render_block_core/group` | filter | 10 |
| `admin_post_gogh_product_related` | action | 10 |
| `admin_post_gogh_product_layout_all` | action | 10 |
| `admin_post_gogh_blog_layout` | action | 10 |
| `admin_post_gogh_post_layout` | action | 10 |
| `admin_post_gogh_post_layout_all` | action | 10 |
| `get_post_metadata` | filter | 10 |
| `wp_footer` | action | 10 |
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
| `the_content` | filter | 11 |
| `wp_head` | action | 10 |
| `the_content` | filter | 13 |
| `wp_head` | action | 10 |
| `wp_enqueue_scripts` | action | 10 |
| `wp_enqueue_scripts` | action | 10 |
| `rest_api_init` | action | 10 |
| `enqueue_block_assets` | action | 10 |
| `admin_bar_menu` | action | 10 |
| `admin_bar_menu` | action | 10 |
| `admin_bar_menu` | action | 10 |
| `admin_bar_menu` | action | 10 |
| `wp_insert_post` | action | 10 |
| `block_editor_settings_all` | filter | 10 |

Filters exposed for third parties: `gogh_default_editor`, `gogh_claims_post`, `gogh_labs_ask`, `gogh_helper_url`, `gogh_user_test_tasks`, `gogh_rebake_enabled`, `gogh_schema`, `gogh_schema_enabled`, `gogh_webmcp_enabled`, `gogh_convert_enabled`.

REST routes registered: `gogh/v1/version`, `gogh/v1/starter`, `gogh/v1/site-def`, `gogh/v1/type-scale`, `gogh/v1/blog-style`, `gogh/v1/motion`, `gogh/v1/ask`, `gogh/v1/imagine-exp`, `gogh/v1/ask-key`, `gogh/v1/menu-style`, `gogh/v1/first-minute`, `gogh/v1/ask-log`, `gogh/v1/active-style`, `wp/v2/gogh-product/(?P<id>\d+)`, `wp/v2/gogh-product/(?P<id>\d+)/autosaves`, `gogh/v1/pattern`, `gogh/v1/render`.

Core REST endpoints used by the editor: `wp/v2/blocks`, `wp/v2/categories`, `wp/v2/pages`, `wp/v2/posts`, `wp/v2/template-parts`.

Capability checks in PHP: `edit_posts`, `edit_post`, `edit_theme_options`, `edit_others_posts`, `edit_products`, `edit_pages`, `manage_options`, `upload_files`, `unfiltered_html`, `activate_plugins`, `install_plugins`, `manage_woocommerce`, `publish_pages`.

Query-string switches: `?gogh-edit`, `?gogh-ps`, `?gogh-test`.

## Exact UI labels (tooltips and button titles)

These are the real strings in the current build. Use them verbatim; never paraphrase a label.

- "' + ALIGN_WORD[how] + '"
- "' + d.label + '"
- "' + d.title.replace(/"
- "' + escAttr(e.alt || 'Video') + '"
- "' + escAttr(l.name) + '"
- "' + escAttr(o[2]) + '"
- "' + escAttr(t.name) + '"
- "' + hp[1] + ' — ' + hp[2] + ' units"
- "' + m[2] + '"
- "' + nm.replace(/-/g, ' ') + '"
- "' + p.slug + '"
- "' + sh.label + '"
- "' + whereTip(it.where !== 'desktop', 'phone') + '"
- "' + whereTip(it.where !== 'phone', 'desktop') + '"
- "+ Link"
- "+ Page"
- "A card — drop pieces inside and they stay together, even on mobile"
- "A line — a thin rule between things"
- "A video — upload one, or paste a YouTube or Vimeo link"
- "AG"
- "Aa"
- "Add"
- "Add a category ↗"
- "Add a page to this menu"
- "Add a picture or video"
- "Add a product ↗"
- "Add link"
- "Add something to this section"
- "Add to page"
- "Adjust spacing"
- "Align the pieces inside this card"
- "Align the pieces to each other"
- "Align ▾"
- "All options"
- "An icon — a simple line drawing in your text colour"
- "Any colour"
- "Apply"
- "As placed"
- "As typed"
- "Auto"
- "Back"
- "Background & look"
- "Badge"
- "Body"
- "Bold"
- "Both"
- "Bring forward"
- "Browse them all →"
- "Bullet points — a text piece wearing bullets"
- "Bullets"
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
- "Dark page"
- "Delete"
- "Delete (Del)"
- "Delete saved section"
- "Delete the selection"
- "Delivery & returns"
- "Desktop"
- "Desktop menu"
- "Discard changes"
- "Done"
- "Done — keep this look"
- "Duplicate"
- "Duplicate (or Alt-drag)"
- "Duplicate the selection"
- "Edge to edge of the section"
- "Embed"
- "Embed a link — a map, a post, a playlist, anything WordPress can show"
- "Equal gaps top to bottom"
- "Even gaps"
- "Everything"
- "Experience"
- "Featured product"
- "Fill screen"
- "Fill the screen"
- "Fill the width — size the text to its box"
- "Find us"
- "Forget the key"
- "Form"
- "Full width"
- "Get in touch"
- "Go"
- "Grid: show and snap"
- "Heading"
- "Headings"
- "Help — ask gogh anything"
- "Hidden"
- "Hide on phones"
- "Home"
- "How Google and AI read this page"
- "Icon"
- "Image"
- "Imagine"
- "It is already on your site — this closes the panel"
- "Italic"
- "I’ll find my own way"
- "Journal"
- "Keep editing"
- "Keep this"
- "Keep this brand"
- "Keep this layout (updates every page)"
- "Keeps your changes on every page"
- "Left"
- "Light"
- "Light page"
- "Line"
- "Line the pieces up, even the gaps, match sizes that are nearly the same"
- "Link"
- "Link text (⌘K)"
- "List"
- "Make a card"
- "Make it freeform"
- "Make these one card — it holds together on phones"
- "Make this bigger"
- "Manage categories ↗"
- "Manage posts ↗"
- "Manage products"
- "Manage products ↗"
- "Manage this menu — reorder, nest, swap menus"
- "Most loved"
- "Move down"
- "Move down in the phone stack"
- "Move earlier"
- "Move later"
- "Move left"
- "Move right"
- "Move up"
- "Move up in the phone stack"
- "Move, duplicate, save, delete…"
- "My account"
- "Name, email and a message — straight into your own site, no plugin"
- "New in"
- "None"
- "On sale"
- "One product, hero-sized — a card with a real add-to-cart button"
- "Open interactive experience"
- "Open the map"
- "Open your products in WordPress"
- "Our story"
- "Outline"
- "Peek at pages"
- "Phone"
- "Phone menu"
- "Posts"
- "Products"
- "Publish"
- "Publish & close"
- "Put it back"
- "Questions"
- "Redo (⇧⌘Z)"
- "Remix keeps these colours and rolls everything else"
- "Remove"
- "Remove from Your sections"
- "Remove from menu"
- "Remove image"
- "Remove link (keep the text)"
- "Remove poster"
- "Remove this ' + labels.one + '"
- "Remove this photo"
- "Remove this slide"
- "Remove video"
- "Right"
- "Roll another take of this design"
- "Save"
- "Save description"
- "See all →"
- "See and tune how the page looks on a phone"
- "See it on a phone"
- "See the page as a phone"
- "Send backward"
- "Shape"
- "Show"
- "Show the next layout"
- "Shown"
- "Site designs — a whole site, ready to tweak (replaces this one)"
- "Solid"
- "Start writing — a reading column, cursor ready"
- "Switch design"
- "Switch it on"
- "Tap to try a new look. Tap again for another."
- "Text"
- "Text alignment"
- "Text colour"
- "The gogh build this tab is running"
- "The header rides along as visitors scroll"
- "The look before this one"
- "Theme default"
- "Tidy up"
- "Try another"
- "UPPERCASE"
- "Undo"
- "Undo (⌘Z)"
- "Unwrap — back to freeform"
- "Updates every page"
- "Upload a self-contained HTML experience — it runs sandboxed"
- "Upload an .html file instead"
- "Use it"
- "Use this design"
- "Use this layout"
- "Video"
- "Where this item shows"
- "Which take of this design is on the page"
- "Write"
- "Write a post ↗"
- "Your latest posts, live"
- "Your latest products, live — prices and add to cart included"
- "ag"
- "get one here"
- "gogh help"
- "lowercase"
- "pick from the shelf"
- "re-centre"
- "×"
- "← All layouts"
- "↕ Spacing…"
- "▶ Auto-play"
- "⛶ Click to enlarge"
- "✦ Not this look?"
- "✦ Remix"
- "✨ Make draggable"
- "✨ Make it freeform"

## Exact toast and message copy

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
- "Guard: "
- "Icon link updated."
- "Image swapped."
- "Installing "
- "It has to show somewhere — remove it instead if you don’t want it."
- "Keep your changes with Done, or undo them with Cancel."
- "Kept — "
- "Link removed — the text stays."
- "Link updated."
- "Linked."
- "Logo set — your image now leads the header."
- "Logo size saved."
- "Made the card’s words readable on its background."
- "Mega menu removed — it is in the trash if you change your mind."
- "Menu order updated — every page gets it."
- "Menu switched — every page shows it."
- "Mobile menu: "
- "Name size saved."
- "One card now — it holds together on phones. Click it for a background."
- "Out of the card — it’s its own piece again."
- "Publish failed: "
- "Publish your changes first — changing the page style reloads the page."
- "Removed from the card."
- "Saved — search results and AI answers now quote your words ✓"
- "Saving your page changes, then updating the "
- "Section deleted."
- "Section moved."
- "Section removed — publish to make it real."
- "Site "
- "Site name restored — click it to rename."
- "Site name saved."
- "Style copied — click other text to paint it. Esc finishes."
- "That is the only section on the page. Add another before deleting it."
- "That saved section can’t be read."
- "That section can’t move past other stored content yet."
- "That upload failed — try again, or paste a link."
- "That’s gogh. Everything else is just more of this."
- "The backup could not be read."
- "The description could not be saved — try again."
- "The header and footer stay — edit them instead."
- "The name didn’t save — try again."
- "The type before is back."
- "Theme style applied: "
- "This "
- "This header has no menu button to open."
- "This mega menu drops from “"
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
- "gogh could not find a menu to add it to — open the header and add it there."
- "gogh could not keep the menu style — "
- "gogh could not make a mega menu — "
- "gogh could not make a phone menu — "
- "gogh could not rename the site — that needs an admin login."
- "gogh could not save that look — it stays for now."
- "gogh could not save the menu — "
- "gogh could not save your brand — "
- "gogh could not set the logo — "
- "gogh could not switch back — "
- "gogh could not switch the design — "
- "gogh could not switch the menu — "
- "gogh could not take that page back — it is under Pages in the dashboard."
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

`327` tests, run by appending `?gogh-test` to any Gogh page URL while logged in with edit rights on that page.
