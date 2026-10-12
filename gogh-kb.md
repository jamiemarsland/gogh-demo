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

Generated for plugin version 0.99.762 · knowledge base e6a9bf3.

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
1. **Admin bar** — **Edit** (a solid button; before v0.99.618 `🎨 Edit with gogh`) appears on any single front-end page you can edit. Links to the permalink + `?gogh-edit=1`.
2. **Corner button** on the front end — `✏️ Edit` — only on pages with no admin bar (since v0.99.628); hides once editing starts.
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
Two tabs, Page and SEO, sit on the left edge of the window in edit mode (Site moved to the brush in the top bar in v0.99.618). Click one and a drawer docks beside the page (the page shrinks to fit next to it; nothing opens on hover, and there is no palette on the right any more). There is no exit control here — leaving edit mode is **View site (was Exit gogh editor)** in the admin toolbar.
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
| **Hero** | Heading, body copy, two buttons, image + tinted box |
| **Feature cards** | Centred heading + three real cards with headings and copy |
| **Big statement** | Badge, huge centred heading, ghost button |
| **Quote** | Tinted box + pull quote + attribution + image |
| **Call to action** | Wide tinted box, heading, button |
| **Article** | 640px reading column, heading, three paragraphs, ghost button |
| **Photo cards** | Two full-bleed photo cards with gradient scrims, ratings, buttons |
| **Gallery** | Heading + three staggered images |
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
| `exp` | `core/group` > `core/paragraph.gogh-fallback` > `<a class="gogh-exp-link">` | KSES-safe and the plugin-off fallback; PHP swaps the group's inside for the sandboxed iframe at render. The link sits in a paragraph because a loose `<a>` in a group fails block validation in the block editor |
| `embed` (map) | `core/group.gogh-embed-map` > `core/paragraph.gogh-fallback` > `<a class="gogh-embed-link">` | Same contract as `exp`: PHP (`gogh_inject_embeds`) swaps the inside for the map's iframe. Pages published through v0.99.646 hold the link loose in the group; the render still reads both |

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
| `admin_bar_menu` | action, priority 90 | The **Edit** node (was `🎨 Edit with gogh`) |
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
A site booted from `blueprint-usertest.json` (the tester is sent to the helper's `/test` page, which thanks them for helping make gogh better, explains the persona — Elliot Grey, a photographer, on a site that still says Elliot Grey so renaming it is a real task — and has the Start button) carries the option `gogh_user_test`. On such a site the plugin loads `gogh-usertest.js`: a small card on the right, below the site header so the menu stays visible, with seven tasks (site name · headline · swap a photo · a different look · a way to get in touch · a Prices page in the menu · phone check then publish and view), each with Done, Couldn't do it, an optional note and a hint (opening the hint is recorded), then a four-question wrap-up (happy 1–5, could finish, how it felt, what confused) and an optional name. The card keeps its place in localStorage across reloads and the walk between editor and published site. Events go to the helper's `POST /api/test` under a random session id (`gogh_user_test_session`), batched and retried. Jamie reads them at `/tests` behind a password (the Worker secret `TEST_TOKEN`, which Jamie chose; the refresh token also works): one row per tester, click for the tasks, times, notes and wrap-up. Tasks come from `gogh_user_test_tasks()`; the option may carry its own `tasks` array, and the `gogh_user_test_tasks` filter can change them. Nothing else is collected.

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

**Seeing the page on a phone (v0.99.594).** Two words sit at the top right of the admin bar the whole time you are editing, just left of the ? help button: **Desktop | Phone**. Press **Phone** and the page is pinned to a phone's width (390px) on the zoomed-out desk, with the page's real mobile layout live, so you can tune it (Hide on phone, move up/down in the phone stack). It needs no drawer open; **Desktop** puts the page back. (The Page drawer's *See it on a phone* card was removed in v0.99.611: the top bar's Phone is the one door.) Opening and closing drawers or panels while on the phone keeps the phone view; only Desktop (or ⌥Z) leaves it. Leaving the editor leaves it too. Seven of the first sixty-four testers reported "there is no phone button" because the switch used to live inside the zoom slider, which only appears while a drawer is open. There is no phone button in the zoom slider any more.

**The see-through header sits flush in the design view (v0.99.616).** It sits 32px down on the real page to clear the admin bar; in the zoomed design view and the phone view the page already starts below the bar, so a band of hero used to show above it. Under a zoom it now sits at the page's top.

**Contact form (v0.99.615).** The section layout that carries gogh's form is called *Contact form* in the picker and on the seam (it was *Get in touch*; saved sections, blueprints and site definitions keep the old key and still work).

**The site name changes as you type (v0.99.614).** In *Logo & name*, the Name field paints the header's site title with every keystroke and saves about a second after you stop typing; it used to wait until you left the field. If the save fails the painted name goes back.

**Name first, everywhere (v0.99.613).** Every road into a new page now starts with its name. The Page drawer's *Add a page* always did; the WordPress door (*+ New → Page*, or Add Page in the dashboard) used to land on an untitled canvas and ask at Publish. Now a page with no name opens with **Name this page** as a modal in the middle of the page over a dim (v0.99.617; it was a small panel by the page chip, which went unseen) the moment it lands (*A new page starts with its name — in menus, and in its web address. You can change it later.*), the name saves at once, and only then does the blank-page section picker appear. Dismiss the panel and the picker still comes; Publish still pauses for a name if the page has none. Rename any time from the page chip.

**Hiding things on phones, from the desktop (v0.99.596).** You no longer have to be in the phone view to learn that pieces can be hidden or reordered for small screens. Every piece's bar has a **phone button** (a small phone; lit and slashed once the piece is hidden on phones) — press it to hide the piece on phones, press again to show it. Every piece that has a panel (button, image, video, box, widget, line, icon, embed) ends with an **On phones** row: **Shown | Hidden** and **See it on a phone**, which opens the phone view. A section's ⋯ menu has **Hide on phones** (then **Show on phones**). Hiding says so in a toast with a *See it on a phone* door, and on the desktop canvas a hidden piece wears a small **Hidden on phones** tag. Inside the phone view one sentence sits under the phone the whole time: *"Press anything to hide it on phones or move it up and down."* Reordering for phones (move up / move down) still lives in the phone view only.

**The phone view is the page's own phone CSS (v0.99.597).** The artboard is phone-wide but the browser window is not, so rules written for narrow windows (`@media (max-width: …)`) — the header's hamburger menu, shop grids, blog cards, forms — used to keep answering to the window, and the phone view showed a desktop menu squeezed into a phone. Now, entering the phone view, gogh walks the page's stylesheets and flips each such rule's media condition on the live CSSOM: on for rules a 390px-wide phone would fire, off for desktop-only (`min-width`) ones, leaving rules that are not about size (hover, reduced motion) alone and the editor's own stylesheets untouched. Desktop puts every condition back. Nothing is copied or rewritten, so the phone view is exactly the page's mobile CSS, including the real hamburger, which opens inside the artboard.

**The Mobile menu page opens with the menu open (v0.99.604).** Arrive and the real menu is already open on the page beside the panel, so every layout and ground you hover shows at once. Under the intro line a switch with a hamburger icon, **Preview the menu**, is already on; turn it off to see the page behind. Back to header, ✕ and Esc close the menu on the way out. Saving a phone number or email re-renders the header and the menu reopens by itself if the switch is on. (Before: an *Open the menu to preview* row that looked like a door to another page.)

**Opens as: Full screen, Drawer or Sheet (v0.99.607).** The Mobile menu page offers three ways of opening; choose *Full screen* and a second pick, **Lines: Left or Centred**, appears beneath it. (Until v0.99.606 the list was Stack, Centred, Drawer, Sheet — Stack and Centred were both the whole screen and differed only in alignment. Sites saved as Stack are now *Full screen, left*; nothing changed on the site.) Stack had also inherited the header menu's own alignment, so on a centred header it looked the same as Centred; it is left-aligned now.

**The open menu keeps to the window in a zoomed view (v0.99.605).** Inside the zoomed page, a menu positioned to the screen answers to the page's box instead, so Stack and Centred filled the whole page, Drawer ran the page's height and Sheet landed at the page's foot, out of sight. While the menu is open under a zoom, gogh pins its box to the part of the page the window shows and re-pins on every layout change, scroll or resize, so Sheet auditions and every layout looks as it does on a phone. Nothing of this runs on the real page.

**The phone view survives navigation (v0.99.605).** Press one of your own pages while on the phone and it opens in the editor, still on the phone: the link carries `gogh-edit=1&gogh-phone=1`, the next page reads them at boot and strips them from the address. Other sites, files, wp-admin and anchors on the same page are left alone. Unpublished changes still ask before leaving.

**A set's bar is Add card · Edit design (v0.99.762).** The bar for a set of cards now matches a shop's (Manage products · Edit design): **+ Add card**, **Edit design** and **•••**. Edit design opens the sidebar on Layout, with Style one tab away, and stays pressed while the sidebar is open; press it again to put the sidebar away. The separate "Layout · 3 across" and "Style · Panel" chips are gone, since both opened the same sidebar.

**Rounded corners works on every set, and a calmer picture chooser (v0.99.761).** On a set made from freeform cards, Rounded corners did nothing because the cards kept their own corners. Now the cards' own corners show while it is on, and turning it off makes them square. In a card's picture chooser, Upload and Library are two matching buttons on one line, and "Remove picture" is a quiet link rather than red words.

**Card titles that were plain lines keep their face (v0.99.760).** When a set is made from freeform cards or columns whose titles were paragraphs (a schedule's talks, "01 — Listen first"), the titles keep the body typeface instead of switching to the theme's heading face. Ledger rows from the blueprints sit closer together, nearer their original spacing.

**Team and Photo cards are Cards, and "Make these a set" (v0.99.759).** On the shelf, Team is a set of people in all four takes: 3 across with square pictures, the list as Rows, the ink studio, and the offset as Steps. Photo cards are a set On the photo, with white words and white buttons over the picture, in all four takes. Their pictures come from your own media like any design's, and a site definition's people or places fill the set. Choose two or more matching freeform cards together (Shift-click, or drag a box around them) and the selection bar leads with **Make these a set**. It turns them into one Cards set, keeping their words, type, colour and corners, and Undo puts them back.

**Menus, schedules and work lists are Ledgers (v0.99.758).** Fourteen ruled lists in the site-definition blueprints are now Cards sets in the Ledger layout, keeping their type and their rule colour. They are Cinder's menu and suppliers; Northern Blocks' Friday and Saturday schedules; Ferro & Lane's and Halloran's selected work and work pages; Seventy South's itineraries; and Rosa & Tom's day, story and places to stay. Adding a dish, a talk or a year is now Add card, with no rows to draw by hand. The shelf's Menu design is a Ledger in all four takes, and "The two tops" runs it in two columns. Misprint's workshops keep their own print styles and stay freeform.

**Cards has a Ledger layout (v0.99.757).** Ledger lays a set out as ruled rows in columns, for menus, schedules, work lists and itineraries. From left to right: the line above the title (a time or a year) as a narrow lead column, the title, the words, and a value on the right (the price, or the name-and-role line when there is no price, such as a room or a place). Columns follow the parts that are switched on; a set with no words drops that column. Adding a row is Add card, and the + between rows works too. On a phone each row stacks: lead, title and value on one line, the words under them. Choose it from the set's Layout.

**Blueprints use Cards, and a site definition can name a set (v0.99.756).** Twelve sections in the site-definition blueprints are now Cards sets: Northern Blocks' speakers, tickets and "Good to know"; Seventy South's camps; Ferro & Lane's approach, beliefs and partners; Halloran's approach; The Violet Hour's candle steps; Cinder's "Getting here"; and Rosa & Tom's "Good to know". A definition names a set as a piece `{ "type": "cards", "x", "y", "w", "h", "cards": { "layout", "look", "pic", "items": [...] } }`. Items can use the set's own words (`t`, `d`, `k`, `role`, `n`, `price`, `link`, `href`, `img`) or friendlier ones (`title`, `text`, `above`, `name`, `role`, `note`, `price`, `button`, `link`, `image`). A set can also carry a hairline over each card (`box.rule`). Misprint's rack (its riso print styles), Verditer's swatches and Plinth's contributors stay freeform for now.

**The starters use Cards (v0.99.755).** The card rows in the starter sites are now Cards sets rather than separate freeform boxes: Elliot Grey's "What I make", the cake shop's prices and kind words, the ceramics reviews, Halcyon's portfolio and testimonials, and the life coach's topics, stories, kind words and prices. They look as before, but you can add, move and restyle the cards as one set. The ceramics "Seconds Saturday / Kiln notes" pair stays freeform because its two cards are different.

**Cards keep the type of the cards they are made from (v0.99.754).** When a row of matching freeform cards becomes a Cards set, each part keeps its look: the title's size, weight and face, the words, the name and its note, the price, the link. The card keeps its colour, corners, padding and height, buttons keep their full width, photo cards keep their columns and shape, and a name under a quote stays at the foot. A card can now carry two more parts, switched on from the set's ••• menu: "A line above the title" (a small label like "Spring" or "Step one") and "A small note" (a date, a size, a place). Badges are not carried over.

**The take count reads small again (v0.99.753).** The "1/4" beside the die on a section's bar now uses the bar's own small type; on some themes it had picked up the site's large body text.

**Adding a card, and clearer doors (v0.99.752).** "+ Add card" on a set's bar adds a new card right after the chosen card (or at the end when only the set is chosen); it arrives chosen, with the caret in its title and its other parts asking to be filled, never a copy of another card's words (Duplicate in a card's ••• copies one). Point at the gap between two cards of a chosen set and a line with a + appears: press it to put a card exactly there. In a grid whose last row has room, a dashed Add card fills the next empty slot. The bar's doors now say what they change and what it is: "Layout · 3 across" and "Style · Panel", with no ▾ (they open the sidebar, not a dropdown) and pressed while their page is open. A card set has no turn handle.

**Layout and Style beside the page (v0.99.751).** A card set's Layout and Style now open in the sidebar on the left, and the page shrinks beside it, so the whole set stays in view while you point at choices to try them. Layout and Style sit as two tabs at the top of the sidebar, so you can move between them without closing it; Back closes it. The ••• menu, a card's picture chooser and its link bar still open right beside what they change.

**A card's link, where the link is (v0.99.750).** Press a card's link or button (with the set chosen) and a small bar opens right under it: Words, Goes to (an address or a page like /contact/), and Your pages to pick from. Pointing at a card's link shows where it goes ("→ /contact/") before you press it. An empty link reads "Add a link" (or "Add a button"), and a link with words but no address carries a "No address yet" marker on the canvas. When you publish, gogh names any card links that still go nowhere, so you can fix them; the page publishes either way. A card's ••• menu still has Link as a second way in.

**Choosing a card set, then one card (v0.99.749).** Point at a card set and the whole set is outlined and named ("Cards · 3"). The first press chooses the set, with its bar. A second press goes into the card it lands on: a ring round that card, its handle (a grip to drag it, ••• for its menu) on its corner, and "Cards › Card 2" at the start of the bar. Pressing a card's words also puts the caret there; pressing its picture opens the picture chooser. Esc steps out one level at a time: the words, then the card, then the set. Clicking "Cards" on the bar goes back to the whole set too. Choosing a layout built around pictures (Small and centred, On the photo, One big) on a set with Picture set to None now switches pictures on, and each card without one shows a spot to choose it.

**Cards: one bar, no trips to the sidebar (v0.99.748).** A chosen card set's bar is now Layout, Style, + Add card and •••. Layout opens a small panel of drawings of every layout: point at one to try it on the page, click to keep it. Style holds everything about how each card looks: the card look (Open, Panel, Outline, Raised), rounded corners, the picture shape, left or centred words, what each card ends with, and titles on or off. ••• holds the extras (a price, a name and role, one featured card), All cards as a list (the side panel), and Duplicate or Delete the set. The cards edit themselves: a dashed Add card sits after the last card, a card's handle has a grip to drag it to another place in the set, clicking a card's picture opens a picture chooser beside it (your newest pictures, Upload, the alt text, or no picture), and a card's ••• menu has Link, where you type an address or pick one of your pages. There is no Edit cards button any more.

**The card designs are Cards now (v0.99.747).** Feature cards, Pricing and Testimonials, in every take, are now built from Cards sets instead of loose freeform boxes, so their cards always match: type on any card, + Add card adds one in the same design, and the side panel moves, duplicates or removes them. Pricing keeps its featured middle card (dark and a little taller, the others with outline buttons) and its prices; Testimonials are cards without titles, the words with the person's name under them. Cards gained what those designs needed: rounded corners (in Card look), a Titles switch (in Words), a Steps layout (each card a little lower than the last) and, for Rows with no picture, the first line on the left with the rest beside it. Rolling the die keeps your cards and changes only the design. An older section made from these designs, with freeform cards, turns into a set the first time you roll it, keeping its words. Profile, Job and Place card, and the designs with one card in them, are still freeform.

**Cards: a set that shares one design (v0.99.746).** The + menu has a new piece, Cards: three cards (a picture, a title, a line or two, a link) that always match. Type straight on any card to change its words. "+ Add card" on the bar adds one; hover a card for its ••• menu (make it the featured card, duplicate, move, delete). The set's bar (see v0.99.748) holds one design for every card: Layout (3, 2 or 4 across, Rows, Zig-zag, On the photo, Slider, One big, Index, Small and centred), Card look (Open, Panel, Outline, Raised), Picture (Landscape, Square, Portrait, No picture) and Words (left or centred, ending with a link, a button or nothing). Extras switch on a price, a name and role, or one featured card (dark and a little taller). Point at a layout to try it; click to keep it. The set measures itself, so it folds to two cards a row and then one as it gets narrow, on phones too. It publishes as plain WordPress groups, images, headings and paragraphs, so the words and pictures stay if gogh is switched off. The older single Card (a box you drop pieces into) is still there for freeform designs.

**Empty shelves that read (v0.99.745).** A shop or posts piece with nothing to show yet (no posts, nothing on sale) now reads on a dark section: its note and link take the light ink, and on any coloured section a shop's names, prices and Add to cart buttons follow the section's colours too. Words drawn beside a shelf stay centred on it once it shrinks to its empty note, and never climb above it.

**Every section design redrawn (v0.99.744).** All the ready-made section designs and their takes (34 designs, 136 layouts) now follow one system. gogh sets the text sizes for its own designs (a big heading, a section heading, a card heading, a lead line, body text, a small label), so headings look like headings on every theme. The sizes grow and shrink with the section the way the layout does, and never drop below a readable size on a phone. The gaps follow one rhythm on an 8-point grid: label to heading, heading to words, words to buttons, an intro to the cards under it. When you add a section or roll the die, gogh measures the words in your theme's fonts and closes the design up around them: cards fit what they hold and a row of cards keeps one height with its buttons lined up, words beside a tall photo stay centred on it, and the section keeps the same air above and below. Words over a photo (photo cards, scrims) stay readable: the contrast check now looks at what's directly behind the words instead of the page.

**Calmer section designs, a truer phone view (v0.99.743).** The ready-made section designs no longer carry little badges such as "Nº 01" or "★ Est. 2019"; you can still add a badge yourself from the + menu. Hovering a design in the Add a section chooser now just says **Add**. In the phone view, shop pieces show two products to a row the way a phone does (and published pages now do the same on phones), and the Lookbook shows one page at a time, picture above words, instead of a squeezed spread. Giving a section a dark background colour now also lightens its small label above the heading, which used to stay dark. In the sidebar, the Page drawer shows what's set (the page style, how many sections, the motion), Add a page has one clear blue **Add page** button, and the header and footer rooms use the same calm rows as Site styles.

**One look for every sidebar (v0.99.742).** The panels that open on the left now share the Site styles look: a small **‹ Back** at the top, then the panel's name as a real title and a line saying what it's for. Lists of choices (Page style, Motion and the page styles in Add a page) are calm rows with a line under each name and a tick on the current one, instead of stacked buttons. Section background, Layouts and the panels for photo walls and other blocks use the same heading. Fonts, Motion and Add a page now always open beside the page, zoomed out, like the rest.

**Add a section, redesigned (v0.99.741).** The Add a section chooser is now a plain white sheet with a list on the left: **Start blank**, **Paste HTML** and **My sections** at the top, then the groups **Introduce**, **Sell**, **Showcase** and **Share**, each with its designs listed by name (and your theme's own patterns at the end). Click a group to see all its designs; click a design's name to see every layout of it side by side, and add the one you like. Designs show at their natural height, in your site's own fonts, colours and products, without names or boxes; hover one and **Add** (or **Add here**) appears. The three buttons top right change the size: three across, two across, or one large; gogh remembers your choice. Search is the magnifier beside them.

**Real products in the chooser; Edit on photo walls (v0.99.740).** In Add a section, the shop designs (Featured product, Bestsellers, New in, Editorial split, Categories) now show your own products and category pictures instead of samples; a design whose shelf would be empty, like Sale with nothing on sale, keeps its sample. Choose a photo wall, carousel, questions or tabs on the page and its toolbar now says **Edit photos**, **Edit slides**, **Edit questions** or **Edit tabs**, which opens what's inside. In a button's colours, "Words" is now called **Text**.

**Adding a section, Squarespace-style (v0.99.739).** Click **+ Section** between two sections and the Add a section chooser opens straight away, right where you clicked. Every design is drawn in your site's own fonts and colours, with a slice of the section above and the one below hugging it, so you can see how it sits in place; hover one and **Add here** appears. Click a design to add it exactly there; the page marks the spot with a blue line while the chooser is open. Search works as before, and pressing Enter takes the first design showing; if nothing matches, words like "a map and opening hours" are understood the way the + used to understand them. The gap and the strip from v0.99.736–738 are gone.

**Adding a section: the slim strip (v0.99.738).** "What should go here?" is now one slim row at the top of the gap: the likeliest choices, a box to describe anything else, Add, Browse all and ✕. Hover a choice and that section is drawn in the gap at full size, in your site's own fonts and colours, exactly where it will land. The page never zooms or jumps. If the section is taller than your screen, scroll: the strip sticks under the top bar while you're inside the gap, and the preview stays while your pointer is anywhere in the gap. Click a choice to add it there; move out of the gap to clear the preview; ✕ or Esc closes the gap.

**The whole section, previewed (v0.99.737).** Adding a section now zooms the page out with "What should go here?" in the sidebar, so a hovered choice fits in the gap whole instead of being cropped.

**See where a new section goes (v0.99.736).** Click **+ Section** between two sections and the page now opens a dashed gap at that exact spot, with the sections above and below still in view, so there's no doubt where the new one will land. Click a choice to add it there, or press Esc or ✕ to close the gap. Browse them all still opens the full shelf.

**No bins in Layouts (v0.99.735).** The Layouts panel no longer puts a bin on every piece. To take something out while the panel is open, click it in the section and press Delete (or use the bin in its own toolbar): it leaves that layout and every other layout of the design too, so switching layouts never brings it back. **Restore removed** brings it back.

**No name pill on hover (v0.99.734).** The section hover keeps its thin outline but no longer shows the section's name in a pill, and the ring round a section in Section background is labelled "Section background".

**Sections you can see, and backgrounds in the sidebar (v0.99.733).** When you move over a section while editing, a thin blue line now traces its edges, so you can see where it starts and ends. It comes and goes with the section bar, so it steps back while you work on a piece. **Background** in the section bar now opens Section background in the sidebar on the left, with the page zoomed out beside it, so the whole section stays in view while you try colours and pictures; the rest of the page is washed back and the section is ringed. Clicking inside that section keeps the panel open; clicking anywhere else, or ✕, closes it. In the phone preview it still floats.

**Sizes on shop cards (v0.99.732).** In a grid of products, a product that comes in sizes (one choice, up to six options) now shows its sizes as small buttons on its card, above the Add to cart button. Pick one and the card's price changes to that size's price; press Add to cart and it goes straight into the cart without leaving the page, and the cart in your header counts it. Before a size is picked the button says "Choose a size" and points to the sizes when pressed. The picked size uses your site's button colour and the sizes line up with the button. Products with more than one thing to choose, or more than six sizes, keep WooCommerce's "Select options" link to the product page. It works in the Lookbook too. To show no buttons at all, turn off Add to cart in the shop panel.

**A tidier add to cart (v0.99.731).** On a product page, gogh now dresses WooCommerce's add to cart form to match your site. A product's options, such as sizes, show as buttons you tap instead of a "Choose an option" dropdown (a list longer than twelve stays a dropdown, styled to match). The quantity has − and + buttons and stands as tall as the Add to cart button beside it. Until a size is picked, the button says what it needs, such as "Choose a size", instead of fading out; pressing it then points to the sizes instead of showing an alert. The price at the top changes to the chosen size's price instead of a second price appearing below. The chosen button uses your site's button colour, and the corners follow your button's corners. WooCommerce still does all the work underneath, so stock, prices and the cart behave exactly as before.

**The bin in Layouts (v0.99.730).** While the Layouts panel is open, every piece of the section has a small bin on its corner. Click it to take that piece (a button, a picture, a line of words, a badge) out of **every** layout of the design, not just the one you're on: the tiles redraw without it, and rolling the die later leaves it out too. **Restore removed** in the panel brings everything back, with its words and picture, into whichever layout you're on. Reset puts the section back as it was when you opened the panel, bin and all, and the whole visit is still one undo step.

**Layouts, side by side (v0.99.729).** When you select a section that came from a starter, the section bar shows which take of its design you're on as a small count beside the die, such as "2/4". Click that count to open the **Layouts** panel: every take of the design is drawn small with this section's own words and pictures, so you can compare them all at once instead of rolling the die one by one. Click a tile to try it on the page; the page zooms out so you can see it. **Reset** puts the section back as it was when you opened the panel, and **Done** keeps your pick. The whole visit is one undo step.

**Section styles, lightest to darkest (v0.99.728).** In a section's background panel, the colour styles are now grouped and named by how light they are: **Light** (Lightest 1 and 2, Light 1 and 2), **Bright** (from your accent colours), **Dark** (Dark 1 and 2, Darkest 1 and 2) and **Backdrops**. Hover a chip to see its name. A "2" puts your accent colour on the section's headings while the rest of the words stay plain. Every style is drawn from your site colours, so changing them in Site styles recolours every section, and a style only appears when its words read clearly on it, so some palettes show fewer. gogh also now picks real colours, not near-blacks, as your accents, and its automatic readability fix no longer turns words dark on a dark section while you try a new Site style.

**Accent sections stay readable (v0.99.727).** When you try or apply a different Site style or a Remix look, sections in an accent colour now pick their text colour from the colours you are actually seeing, not the ones the page started with. Before, a darker Site style such as Midnight could leave an accent section's words almost invisible until the page was reloaded. And when an accent colour is a middle shade that no text colour reads well on, the section style menu offers only its soft version, so body text always reads. Behind the scenes, every gogh release is now checked so that every section and starter saves as valid WordPress blocks, and that every Site style and Remix look reads in every section style.

**Index, a third new look for the shop page (v0.99.726).** The top bar's **Shop layout** menu now also offers **Index**: the shop page set out like a magazine's contents page, every product's name in large type, numbered, with its price at the end of the line. On a computer, hovering a name brings it forward in italics, fades the others, and floats that product's picture beside the pointer; click to open it. On a phone each line starts with a small picture. Category buttons, sorting and page numbers work as usual.

**Showroom fixes (v0.99.725).** On the Showroom shop page the big picture no longer goes blank now and then (a picture the browser already had could be shown and then hidden again), every picture fills the frame, and each product's price sits beside its name instead of above it. Hovering **Showroom** or **Aisles** in the top bar's **Shop layout** menu now previews the real thing (the big picture, the sideways rows) instead of a long column of large pictures.

**Aisles, another look for the shop page (v0.99.724).** The top bar's **Shop layout** menu now also offers **Aisles**: the shop page is laid out category by category, like walking the aisles of a real shop. Each category gets its own band with its name, a line about it (its description), a link to all of it, and a row of its newest products you swipe or drag sideways. On a computer, two arrows beside each band scroll its row, and they only appear when there is more to see. Buttons under the page title jump straight to each aisle. On a category page, Aisles walks through that category's sub-categories; a category without sub-categories shows the Editorial look instead.

**Showroom, a new look for the shop page (v0.99.723).** On the shop page, the top bar's **Shop layout** menu now offers **Showroom**: one big product picture holds still on the left while a calm list of your products scrolls beside it, each with its name, a line about it and its price. The product you hover over, or the one you scroll to the middle of the screen, takes the big picture with a soft crossfade, and a small label on the picture shows its name and price. Click a product, or the picture, to open it. Category buttons, sorting and page numbers work as usual. On phones it becomes one column with each picture above its name. The Violet Hour blueprint's shop page now uses it.

**Shop panels stay in the sidebar (v0.99.722).** Choosing **Shop the photo**, adding a hand-picked product, or opening the photo library in a shop grid's panel used to pull the panel off the left-hand sidebar and float it over the page. It now stays docked on the left, with the page beside it.

**Shop the photo (v0.99.721).** A product grid has a new layout, **Shop the photo**: one picture, such as a candle-lit table or a styled shelf, with a small dot on each product in it. Visitors hover over, tap or tab to a dot and that product's card opens beside it with its picture, name, price and Add to cart. On a phone the card slides across the foot of the photo. To set one up, choose **Layout › Shop the photo** in the grid's panel, then **Choose a photo** from your media library, and type the names of the products that appear in it under **Products in the photo**. Each product gets a dot with its name on it: drag each dot onto its product in the picture. In the editor the photo is shown still; the cards open on your published page. The Violet Hour blueprint has one on its home page, "An evening, set".

**The Violet Hour opens with a Lookbook (v0.99.720).** The Violet Hour blueprint's home page now shows its new-season candles, "The spring pour", as a Lookbook with Add to cart, so you can see the page-turning book straight away. On a Lookbook page, Add to cart now lines up with the words above it instead of sitting in the middle. And if your site uses scroll motion (Calm, Rise or Drama), a Lookbook or an Index finishes fading in once about a third of it is on screen, so you are never turning faded pages.

**The Lookbook says what it does, and sells (v0.99.719).** In the editor, the Lookbook and the Index are shown still, so a small note under each now says what visitors get: "The pages turn on your published page" and "Pictures follow the pointer on your published page". Choosing **Lookbook** also switches **Add to cart** on, since the right-hand page is where people buy. You can turn it off again under **Show**.

**An easier Lookbook (v0.99.718).** The Lookbook now always fits on screen: it is never taller than the window, so the whole spread is in view at once. The arrows sit beside the book, halfway down, instead of underneath it, and the page number sits quietly below. Each page has a small folded corner that lifts when you hover over it: click it or drag it to turn the page. Turning pages also look smoother, because a turning page stays exactly the size of the page it came from.

**Two new looks for a product grid: Lookbook and Index (v0.99.717).** In a product grid's panel, **Layout** now offers **Lookbook** and **Index** next to Grid and List. **Lookbook** turns the products into a book. Each product is a spread: its photo fills the left page, and its name, price, a few words about it and (if shown) Add to cart sit on the right. Shoppers turn the pages with the arrows, the arrow keys, or by dragging a page across, and the page turns over in 3D. On a phone it shows one page at a time with a swipe, and anyone who has asked their device for less motion gets a gentle fade instead. **Index** sets the product names very large, one per line like a magazine's contents page, numbered, with prices on the right. On a computer, hovering a name dims the others and floats that product's photo beside the pointer. On a phone each row leads with a small photo. Both keep your theme's fonts and colours, and prices, sale badges and Add to cart work as normal.

**Recently viewed, and a tidier site menu (v0.99.716).** A product grid's **Order** now offers **Recently viewed**: each shopper sees the products they last looked at, most recent first, topped up with your newest products. Until a shopper has looked around, the grid shows your newest products, so it never sits empty. If the grid is narrowed to a category, only products from that category show. gogh remembers the last twelve products a shopper viewed in a small cookie for 30 days, and only once a published page uses Recently viewed. A page drawn for one shopper is never kept by a page cache. The menu under your site name in the top bar now has one **Dashboard** link and no **New page**: make pages with **Add a page** in the page switcher.

**The piece you are editing stands out (v0.99.715).** When a shop grid, product categories, a posts grid, an FAQ, tabs or a slideshow opens its panel on the left, the rest of the page fades back under a soft wash of its own background colour. The piece the panel changes stays clear, with a ring and the panel's name on it, so it is plain what you are changing. The wash is only a look: click anywhere on the page to close the panel and pick what you clicked.

**Click the page to leave any sidebar (v0.99.714).** Whatever is open down the left (Site styles, Colours, Fonts, Text size, Page style, Motion, History, Add a page, the header or footer room, or a long panel such as a shop or posts grid), a click on the page closes it, the page eases back to full size, and what you clicked is chosen, ready to edit. It works the same as pressing Esc. A click on the thing the sidebar is about keeps it open: the header while you are editing the header, the grid while its panel is open.

**Adding a page no longer meets "Leave site?" (v0.99.713).** On a page with a posts or products grid, gogh used to think the page had changed the moment the grid loaded its posts, so **Add a page** set off the browser's "Leave site? Changes you made may not be saved". A grid loading is no longer counted as a change. And when you really have changed the page, Add a page now asks gogh's own question first: **Publish, then go**, **Go, and keep my changes for later**, or **Stay**. The new page is only made once you choose to go.

**A Share shelf for your posts (v0.99.712).** Add a section now has a fourth group, **Share — Your posts and news**, with three layouts that show your real posts and keep themselves up to date: **Latest posts** (a heading and three post cards), **Featured post** (the newest post large, the next ones under it) and **Titles and dates** (a quiet list of post titles with their dates). Each has an **All posts** button that goes to your blog page (it is left out if the site has no blog page), and each has three more takes behind the dice. Searching the picker for "blog", "news" or "posts" finds them. With no posts yet, they show a card that offers to write the first one.

**Long panels open in the sidebar (v0.99.711).** The settings for a product grid, category tiles, a posts grid and an FAQ or tabs piece now open down the left side, full height, with a title and ✕, and the page steps over and shrinks to sit beside them, scrolled so the piece you are changing and its section are in view. Every choice still shows on the page straight away. Press ✕, Escape, or anywhere else on the page to close: the page comes back to full size and the press picks what you pressed. Small panels (a button's link, a colour, a picture) still open next to their piece.

**Product and post grids: a quieter way to your products (v0.99.710).** Selecting a product grid, category tiles or a posts grid no longer puts **Manage products** (or posts) on the bar above it; the bar is about the design on the page. To add or change the products themselves, open the grid's panel (Edit design): the last line is a link, **Add or change products in WordPress ↗** (categories and posts have their own). When a shop has nothing to show, its empty card still links straight to adding a product. Also: putting on a font pair from Site styles' looks no longer leaves broken font sizes behind that made WordPress print warnings, and gogh can draw any page through its REST API, so it can run on hosts that only pass WordPress its admin and API (such as a Spacefast space).

**AI assistants can work with your site (v0.99.709).** On WordPress 6.9 or later, gogh tells WordPress six things an AI assistant connected to your site may do, through WordPress's Abilities API: **describe the site** (every page and what is on it, which is the home page, which pages have changes not live yet), **read the design** (each colour and what it is for, the fonts, the text size, the looks the theme offers, the site's brief), **save the brief** (who the site is for and how it should sound), **change the colours, fonts or text size** (one of the theme's looks, colour sets or font pairs, by name; text size snug, regular, airy or grand), **stock the shop** (categories, sizes and products with prices and pictures, if WooCommerce is on) and **publish a page** (its changes not yet live, or a draft). An assistant can do only what your own login allows. It cannot add or move things on a page; that still happens in the editor. Also: new pages get the theme's own "no title" template, so on themes such as Purple a page saved later no longer fails with "Invalid page template".

**Header stays readable while you try looks (v0.99.708).** With the Site styles panel open the page sits zoomed out on a grey desk. The page behind the header now follows the look you're trying: on themes like Purple it used to keep the old background, so a dark look left the header's light words on a pale band.

**Try another works on more themes (v0.99.707).** Site styles' **Try another** now reads a theme's colours for the part each one plays, so themes that number their colours (Purple's theme-1 to theme-7) get the accent on their buttons and readable words, instead of a dark button on a dark ground. Outline buttons take an ink that reads on the section they sit in — the accent on the page's own background, the light or dark words on a coloured band. Trying several looks in a row no longer spreads the page apart: each look settles from the layout you had, so gaps don't grow. In the editor, shop grids and category tiles show "&" properly and four category tiles sit four across, as they do on the live page.

**No Live chip (v0.99.706).** On a WooCommerce site, the green **Live** chip WooCommerce put beside the site name in the top bar is gone. If the shop is set to Coming soon, the **Coming soon** chip still shows, because that one warns that visitors can't see the shop yet.

**Find a page to link to (v0.99.705).** Every link box (a button's link, a link in text, a card's link) now reads **Search your site, or paste a link**. Empty, it lists your pages. Type a few words and it searches the whole site — pages, posts, products and shop categories — with each result's kind beside it; click one, or use the arrow keys and Enter, and the link is set straight away. A pasted address is used as it is (example.com becomes https://example.com); words that find nothing are never saved as a link. A button's panel no longer has the On phones row: it is about where the button goes and how it looks. Other pieces keep On phones.

**Shop look previews keep their styling (v0.99.704).** Hovering a look in the Shop, Category or Product layout menu now shows it fully styled: a look with parts this page has never used (Catalogue's price, size and stock filters) no longer shows huge tick marks and bare boxes in the preview. The styling goes away when you leave the menu.

**The Violet Hour, a store on Purple (v0.99.703).** A new demo blueprint builds a candle and scent shop on Automattic's Purple theme with WooCommerce: a hero of lit tapers, the season's candles as a live product grid, category tiles, a dark studio band, a gift box, a map, plus Our story and Visit pages. Shop, Basket, Checkout and Your account are WooCommerce's own pages. Site definitions can now set up a store: WooCommerce's pages and currency, categories, products (with sizes), a product grid that shows one category, the shop and category looks, a theme's colour or type set by name (Purple's "Libre Baskerville + Figtree"), a page template, and a footer of their own. A home page built with gogh now shows on themes whose front-page template ignores the page (Purple's storefront). Product names in a grid are set at label size, so a theme with very large headings no longer prints four names in a row as headlines.

**Layout menus step the page back (v0.99.702).** Opening one of the layout menus in the top bar (**Post layout**, Blog, Product, Shop or Category layout) now zooms the page out to half size on a grey desk, so the whole design shows while you hover the looks; each look still tries itself on as you hover, and leaving the menu brings the page back to full size. Nothing is kept until you click.

**Help docks on the right (v0.99.701).** Pressing **?** (at the foot of the Page or Site drawer) opens gogh help as a panel down the right-hand side, the full height under the top bar. It stays open while you select, drag and edit, until you press ✕, and it comes back if you move to another page in the same tab. The page is fitted beside it with the same zoom the drawer uses, and the zoom slider and messages step inside its edge. The ⤢ button makes the panel wider for long answers or the film.

**The top bar in the writing room (v0.99.700).** Writing a post now keeps the same dark bar as editing a page: the site's name at the top left (hover it for Dashboard, Your profile, Log out), **View post**, and at the top right the status (**Draft · not live yet**, or **Live**) with the blue **Publish** (or **Update** on a published post). The floating chip at the bottom right keeps the word count, the saved note and Categories & tags; Publish, Save draft and View post moved up to the bar (your words save as you type). While you type the bar fades to a whisper; moving the mouse or pausing brings it back. Leaving by the site's name or View post saves first.

**A button's colour has its own door; Dashboard under the site name (v0.99.699).** A chosen button's toolbar now has a round colour chip showing the button's colour. It opens **Button colour**: Solid or Outline, then **Fill**, **Words** and **On hover**, each a row of swatches with **Custom colour** first (any colour at all) and the theme default second. The link icon now opens only **Button link**: where the button goes. A button inside a card keeps one panel with both. Hover the site's name at the top left for **Dashboard**, **Your profile** and **Log out**; going to the dashboard or logging out with changes not yet live asks first.

**The grid wears the section's own colours (v0.99.698).** The grid that shows while you move things (or with the # grid button) is now drawn tone on tone in the section's own ink, the colour its words wear: a faint wash for the columns, slightly stronger gaps between rows, and clear lines at the margins and the centre. On an orange section it's a deeper orange grid, on cream a faint warm grey, on a dark section a soft light one. It used to lay fixed white bars over every light-looking section, which striped coloured backgrounds. Sections with a picture or video behind them keep the old light treatment.

**Site styles, rethought (v0.99.697).** **Site styles** (the paint brush at the top right, or the Site drawer's first card) now opens on **Your site now**: a small piece of your own page (its heading, a line of text and a button) in its real colours and fonts. It changes the moment the page does, so hovering a colour set, trying a new look or keeping a font pair shows there too. Under it are three rows, each showing its current value: **Colours** (the three colours doing the work, **Use my brand colour** — pick one colour and gogh builds the rest, or **Your brand** once set — and the theme's ready-made colour sets as tiles: hover to see one on your page, click to keep it), **Fonts** (the pairs, as before) and **Text size** (Snug, Regular, Airy, Grand, each drawn at its size; hover to see it, click to keep it). Underneath, **✦ Try a new look** (it used to be called Remix) changes the colours and fonts across the site in one press; a **Trying** card names the look with **↶ Back** and **Keep this**, and the button becomes **Try another**. The Site drawer's separate Fonts card is gone: Fonts is a row in Site styles, and its Back returns there.

**Drawers start under the bar, wherever it is (v0.99.696).** The Page and Site drawers, and the panels that dock to the left, now start exactly where the top bar ends. In Playground, where the bar is moved down a few pixels, they overlapped its bottom edge.

**Changes not live yet; a full bar in Playground (v0.99.695).** Beside Publish, a published page with new changes says **Changes not live yet** (the page itself is live; it's your latest changes that aren't), and a draft says **Draft · not live yet**. In WordPress Playground's seamless mode the site sits a few pixels above the top of the window, which cut off the top of the admin bar and made its buttons look cropped; gogh now notices that and moves the bar down by exactly that much. Anywhere else nothing changes.

**The top bar in a narrow window (v0.99.694).** When the browser window is narrow (under about 780 pixels, for example a Playground beside other windows, or a tablet), WordPress makes its top bar taller; gogh's items now fill it and sit centred instead of jammed at the top. **View site** stays on the bar at every width (WordPress hid it on narrow screens, leaving no way out of the editor); in a phone-narrow window it is just its eye icon, with the name in its tooltip, and **Site styles** is just its paint brush, so the phone and computer switch and **Publish** still fit.

**Publish stands in from the edge (v0.99.693).** The Publish button now sits a little in from the right edge of the top bar, so it reads as the end of the row rather than falling off the screen.

**Site styles, with a paint brush (v0.99.692).** The top bar's **Styles** is now called **Site styles**, with a decorator's paint brush for its icon (the old one looked like a pen). It changes the fonts and colours of the whole site; the **Style · …** chip on a chosen piece's own toolbar changes just that piece's look.

**Publish sits in the bar (v0.99.691).** The Publish button is a little shorter, with room above and below it in the top bar, the same height as the computer/phone switch, and a little more space from the right edge.

**A cleaner bar; no grid while resizing words (v0.99.690).** While editing, the top bar's right side holds only **Styles**, the computer/phone switch, and the status with **Publish**. The **?** has left the bar while you edit; help is the **?** at the foot of the Page and Site drawers, and the bar's ? is back whenever you are only viewing the site or in the dashboard. Dragging a text box's side to make it narrower or wider no longer paints the grid over the section: for words it only changes where they wrap. Dragging pieces, and resizing pictures and shapes, still show the grid to line things up.

**Log out from the site name (v0.99.689).** While editing, your account picture steps aside so Publish has the corner to itself. Your account moved to the site's name at the top left: hover it for **Your profile** and **Log out**. Clicking the name itself still takes you to the home page, still editing. Logging out with changes not yet live asks first, the same way leaving a page does: publish and log out, keep the changes for later, or stay. When you are only viewing the site, the account picture is there as usual.

**Publish takes the corner (v0.99.688).** **Publish** is now the biggest thing on the top bar and the very last thing on the right, after your account picture. With changes waiting, an amber dot and **Not live yet** sit beside it (hover for more: whether your work is saved, and that visitors still see the page as it was). With nothing to publish, the words go quiet and the button itself reads **Published ✓**, greyed. The first time you change something in a session, Publish glows softly twice so you can see where it is; it doesn't do it again. The items on the right of the bar have more room between them.

**Fitted words stay in their box; the size list opens from the size pill; Misprint's card styles (v0.99.687).** Text fitted to its box (the ↔ button) is measured from a copy of the real words, so a design's wide display style counts, and if the words ever spill past the box (a style or font changed since the fit), gogh fits them again by itself, including once web fonts finish loading. Pressing the size on the toolbar opens the size list just under the size pill, like a menu. The **Card** tag of a chosen card sits just inside its frame's top-left corner. The Misprint design's card styles are now still and flat, since cards hold words: **Plain**, **Paper** (cream, a soft shadow, sitting straight), **Ink frame**, **Pink ink**, **Yellow ink** (both through a halftone screen) and **Ink line** (just the ink edge). The overlapping circles and the spinning hero sticker keep their print effects as the page's own classes, outside the style list.

**A chosen card's tag sits on the selection box (v0.99.686).** When you choose a card, the small blue **Card** tag now sits on the selection frame above it rather than painted inside the card. Inside the card it shared a drawing layer with styles that paint their own texture (Misprint's **Pink ink** and **Overprint** halftone dots), and the two merged into a big purple circle over the card whenever it was chosen.

**Publish on the right, in WordPress blue; sizes that won't fit are greyed (v0.99.685).** The status and the **Publish** button now sit at the right of the top bar, last before your account, where editors keep their Publish; the left of the bar says where you are (the site, View site). Publish is WordPress blue, or the colour of your admin colour scheme if you picked one in your profile (Ocean, Coffee and so on), like WordPress's own buttons. While changes wait to go live the words stay plain with an amber dot: waiting is normal, not a warning. On a heading's or paragraph's toolbar, **+** greys out when the next size would push the longest word off the page ("Bigger won't fit"), and the size list greys those sizes out with a note saying why.

**A press on the page puts the drawer away; the picture shelf grows up; big words stay in their box (v0.99.684).** With the Page or Site drawer open, pressing anywhere on the page closes the drawer, zooms the page back to full size around the spot you pressed, and chooses what you pressed, all in one go (a section's own panel, like its background, still stays open). The section background's **Picture or video** shelf now shows two rows: the Upload tile, the picture in use, and the newest pictures. **See all pictures** under it shows the whole library, every shape, with a **Find a picture** search and **Show more**; **Show fewer** goes back. The row that shapes the edge into the next section is now called **Bottom edge** (it was "How it ends"). On the desktop canvas, pieces hidden on phones no longer wear a "Hidden on phones" tag; the phone view shows what is hidden there. And a size too big for a word's box no longer lets the word run out past it: the box widens to hold its longest word, and if even the full page width can't, the size comes down to the biggest that fits, with a note saying so.

**Text sizes read like clothes (v0.99.683).** The size pill on a heading's or paragraph's toolbar now shows a clothing size, **− XL +**: S, M, L, XL, XXL, then 3XL, 4XL and 5XL for the giant sizes. Press it for the list, which spells them out (Small S, Medium M, Large L, Extra large XL, Huge XXL, 3XL, 4XL, 5XL), each drawn at its size, with the size the words start at tagged **default**. A theme with its own size names shows those. (This replaces v0.99.682's numbers: a number looked like something you could type, and you can't.) Also fixed: text given an exact size of its own, by a paste or a hand-drawn site definition such as Misprint's, had both − and + greyed out and a one-line list; now the steps and the list work, and choosing a size replaces the exact one.

**Text size is a number (v0.99.682).** On a heading's or paragraph's toolbar the size is one small pill, **− 32 +**: the number is the size the words draw at on this screen, and − and + step one size smaller or bigger through the theme's sizes, stopping at the ends. Press the number to see every size as a list of numbers, each drawn at its size, smallest to biggest; the one the words start at with no size chosen is tagged **default**. The big sizes scale with the page width, so the same heading can read 130 on a computer and less in the phone view. Text fitted to its box shows its number too (the tooltip says it is fitted). The sizes no longer have names on screen (Normal, Headline, Poster, Billboard were dropped in favour of numbers).

**Custom colour for words, lines and icons (v0.99.681).** The colour button on a heading's, paragraph's, line's or icon's toolbar opens a panel that starts with **Custom colour**, like the section and card colour rows. It opens a colour surface in the panel: drag the hue, pick a shade or type a colour code, and the words try it on as you go, shown on their real background with how easy they are to read ("Easy to read here · contrast 6.0", or "Hard to read on this background"). **Use it** keeps it; **Cancel** puts the words back. The palette swatches sit below. The difference: a palette colour changes when you change the site's colours in Site style, a custom colour stays put. Choosing a swatch replaces a custom colour, and choosing a style again clears it.

**No gaps after a size round trip (v0.99.680).** Making text bigger pushes what sits below it down by exactly how much taller the text got, and making it smaller pulls it back up by the same amount. Before this, growing pushed things down twice as far as it should, so choosing Huge and then Small left a big empty gap under the text. The same fix covers Fit to width and painting a style onto text.

**Text size: A−, A+ and plain names (v0.99.679).** On a heading's or a paragraph's toolbar, the size now sits between **A−** (smaller) and **A+** (bigger), which stop at the ends instead of wrapping round. The names are plain: text with no size set says **Normal**, and the three biggest sizes are **Headline**, **Poster** and **Billboard** (they used to be Display S, M and L). Press the size name to see every size in a list, each drawn at its size, smallest to biggest *for that piece*: in many themes a heading's Normal is already as big as Extra large, so sizes are ordered by what they really draw at, and two that look the same count as one step (A+ always makes the words visibly bigger). Text fitted to its box says **Fitted**; choosing a size lets it wrap again. The page switcher at the top of the Page drawer no longer has *Add a page* at the bottom; it is the last card in the Page drawer.

**Published ✓, then Live (v0.99.678).** After you publish, the status says **Published ✓** for a couple of seconds and then settles on **Live**. It used to flash "Answer-ready" in between, gogh's name for how search engines and AI read the page; that now lives only in the **SEO** panel, which explains it.

**A plainer admin bar, and the picture in use (v0.99.676).** While editing, the status beside Publish now says what isn't live yet (since v0.99.677): on a published page **Changes not live yet**, or **Changes saved · not live yet** once gogh has backed them up, and **Live** when everything is published; on a draft **Draft · not live yet** (or **Draft saved · not live yet**), with Publish lit even when nothing has changed, because a draft still needs publishing. WordPress's own logo and search are hidden while you edit, the account menu shows just your picture (Log out is still in it), and your site's name in the bar takes you to your home page, still editing, instead of opening the Dashboard. On picture shelves (a section's background, a card's picture), the picture in use now wears a clear ring and an **✓ In use** badge, and hovering another picture shows a lighter outline.

**Uploaded pictures show on the shelf (v0.99.675).** Uploading a picture in a section's background panel, or in a card's picture panel, now puts it first on that panel's shelf of pictures, marked as chosen, as well as using it. Before, the picture was used but didn't appear on the shelf until the panel was opened again (and on the section shelf, a tall photo could stay hidden by its 'wide pictures' filter).

**The colour button shows the real colour (v0.99.674).** On a heading's, a paragraph's, a line's or an icon's toolbar, the colour button now shows the colour the words actually are, including the theme's default (black text shows black), instead of a colour wheel when no colour has been picked. The section background's colour chip is now called **Custom colour**.

**Add a page is the last card (v0.99.671–673).** In the Page drawer, **Add a page** is a card like the others but comes last, after Page style and Rearrange sections, so the first cards are about the page you're on. It's always in sight, and also at the bottom of the page switcher's list.

**The drawer stays open, Any colour first, Misprint's cards (v0.99.668).** Moving to another page with the drawer open (from the page switcher or a menu link) opens the next page with the same drawer and tab open, so switching pages feels like moving around rather than starting over. Since v0.99.669–672 nothing blinks or jumps: the next page shows the drawer exactly as you left it (with the new page's name) and the page already zoomed out beside it from its very first paint, and gogh takes over without a slide or a snap. In a section's background panel, **Custom colour** (called Any colour until v0.99.674) now comes first in the colour row, with a colour wheel and its name, instead of a + at the end. Misprint's card styles are now six that look different: Plain, **Paper card** (now visibly pinned up, leaning a little one way or the other), **Ink frame** (an ink edge and offset shadow, like the buttons), **Pink ink** (a pink card through the halftone), **Overprint** (for coloured shapes, as before) and **Sticker** (for the yellow circle).

**A simpler page switcher (v0.99.667).** The page switcher's list is now just your pages: the ones in your menu first, a thin line, then the rest, with a small house beside the home page and drafts marked quietly ("Privacy Policy · draft"), and Add a page at the end. What you can do to the page you're on moved out of the list into a **⋯** button beside the switcher: **Rename** and **Make this the home page**. A page with no name yet still shows the amber **Name it** beside the switcher.

**Make this the home page (v0.99.666).** The page you're editing can be made the home page (since v0.99.667 from the **⋯** beside the page switcher), so you no longer need Settings › Reading. It sets the page visitors see first, the Home page mark moves to it, and a message says so with **Undo**, which puts back exactly what was there before (another page, or your latest posts). A draft has to be published first. The offer is for people who can change the site's settings (administrators). If the theme draws its own home page and wouldn't show the page's content there, gogh says so instead of changing anything.

**Misprint's styles come in even sets (v0.99.665).** Misprint gains a heading style, **Yellow pass** (the words in ink with the yellow drum a hair out of register), and a button style, **Paper print** (a paper-coloured print with the ink edge, for a quieter second button). Its Style panels now show four heading styles, six button styles, four picture styles and four shapes. A design's own plain style, such as Misprint's **Plain photo**, now stands in for gogh's built-in Plain, so the pictures panel no longer shows two plains.

**Getting around while editing (v0.99.663).** Editing now stays on as you move around your own site. Press a page in your header or footer menu while editing and it opens in the editor (clicking anywhere else on the header still opens header editing); hovering a menu link says "Go to Workshops · you'll keep editing" (every time, since v0.99.664). The Page drawer now starts with a page switcher, **Editing Workshops ▾**: it lists every page, the ones in your menu first, Home marked, drafts labelled, with a find box on bigger sites and Add a page at the end; Rename sits beside it. Leaving a page with unpublished changes, by either route, asks: **Publish (page), then go**, **Go, and keep my changes for later** (gogh backs them up first; when you come back to that page it offers "You left changes on this page unpublished. Bring them back?"), or **Stay**.

**History shows the whole page (v0.99.662).** Opening History now zooms the page out beside the panel, the way Site style does, so a version you choose is seen whole instead of with its left side under the panel. It stays zoomed while you switch versions and returns to full size when you restore, go back or close History.

**History: earlier versions of a page (v0.99.661).** In the drawer's foot, after Undo and Redo, a clock icon opens **History**: every version of this page you have published, newest first, in words ("Today, 2:14pm", "Mon 6 Oct, 4:20pm"), the newest marked Live now. Choose one and the page shows it as it was, read only (the sections wear a dashed outline and can't be edited while you look). **Restore this version** keeps it as your unpublished changes, so Publish puts it live and one Undo puts your page back; **Back to my page**, Back, or closing the panel leaves your page exactly as it was. Versions where only the title changed are listed once, and versions from before the page was made with gogh are left out. History brings back gogh's sections; the header, footer and any blocks gogh did not make stay as they are. WordPress keeps a version each time a page is updated (your host may limit how many). Also fixed: the offer to restore unpublished work backed up from an earlier session ("gogh backed up unpublished work") could not read pages saved in gogh's current format, so it never appeared; it does now.

**Blueprint export only in labs (v0.99.660).** The Saved styles page no longer shows "Save to the blueprint" and "Copy as definition", and the save form no longer mentions blueprints: those are for people building gogh blueprints, so they appear only with labs on (add `?gogh-experiments=1` to the editor's address).

**Hide on phones moves to the phone view; picture styles back (v0.99.659).** The piece toolbar on the desktop no longer carries the Hide on phones button: switch the top bar to Phone and press a piece to hide it there (or use the On phones row in a piece's panel; sections keep Hide on phones in their ⋯ menu). A piece that is hidden on phones still shows its small "Hidden on phones" tag on the desktop. Designs that give a button and a picture the same style name (Misprint's Pink, Blue and Yellow print) offer all their picture styles again: v0.99.655 to v0.99.658 kept only the button ones, so a Misprint photo's Style button showed just Plain and Plain photo (a site built in that time gets them back when its design is applied again). Choosing a picture for a card or a piece now always lands on the piece whose panel you opened, even when an earlier panel's picture list arrives late. The default star's message now says it plainly: "New buttons you add will now start in Blue print."

**Four small fixes to dragging, resizing and Remix (v0.99.658).** Dragging a piece no longer changes the height of the text beside it: the grid draws pieces whose edges sit within a few units of each other on one shared line, so a paragraph could draw a couple of units narrower when its neighbour moved and, if its words sat right on a wrap point, jump from one line to two. Text height is now measured at the width the design gives it. A sideways drag no longer nudges a piece up or down (the equal-gap and rhythm magnets used to pull the axis you were not moving). When resizing, "same width" now matches pieces of the same kind (card to card, picture to picture), not the box of a heading somewhere else on the page. And Remix no longer offers an accent that barely shows on its background: on a pale wash, a yellow or orange that dark words could read on but that faded into the page is now darkened until it stands out, or rolled again.

**Manage styles from the Style button (v0.99.657).** Saved styles are no longer in Site style: Site style is about the whole site's colours and brand, and a Saved styles door beside Remix read as a third kind of "look". Styles are now managed where they are chosen: press a heading's, text's, button's, picture's or shape's Style button, and at the foot of its panel press **Manage styles**. That opens the Saved styles page (every style by kind, the default star, rename, remove, the specimen, Save to the blueprint and Copy as definition), and Back returns to the piece's Style panel. Manage styles is there for people who can edit the site's CSS.

**Saved styles live inside Site style (v0.99.656, replaced by v0.99.657).** The style set has no card of its own in the Site drawer any more: two cards called Site style and Styles side by side read as the same thing. Open Site style and, under Your brand and Remix, press **Saved styles** ("Looks you can reuse on headings, buttons and more"). The page is called Saved styles, Back returns to Site style, and the save form on a piece's Style button says where to find a style later: Site style → Saved styles.

**Save a look as a style, and the design's style set (v0.99.655).** Style a heading, some text, a button, a picture or a shape by hand (a colour, a weight, capitals, a hover colour), and its Style chip reads "Style · Custom". The chip's panel shows Plain, the design's own styles, and "This heading, as you made it", with a form below: give the style a name, see what it keeps (the colour, the type, the hover, and the look it was built on, with that look's hover and animation; size is never kept, so the style works on a small title as well as a big one), and tick "Make it the default for new headings" if you want. Save style and the piece wears it straight away. Every heading on the site can now choose it, the WordPress block editor lists it in its Styles panel, and new pieces of that kind arrive in it when it is the default. Choosing any style also clears colour and type set by hand on that piece (size stays): the last thing you chose wins. In Site, a new Styles card opened "Styles in this design" (since v0.99.657: a piece's Style button → Manage styles): every style by kind (Headings, Text, Buttons, Pictures, Shapes), a star for the default, a ⋯ menu to rename (pieces keep wearing it) or remove (pieces go back to Plain), Plain always there, and beside it a specimen that draws every style on your page's own words. "Save to the blueprint" downloads the set as a piece of site definition (`<design>-styles.json`), and "Copy as definition" copies the same thing: each style with its name, its default and its CSS, ready to sit beside `pages` in a blueprint's definition. Saving and the Styles card are for people who can edit the site's CSS (administrators on a normal site). Saved styles are printed on every page after Additional CSS.

**Fonts arrive sooner, and no warning in the block editor (v0.99.654).** Every page now tells the browser, at the very top, to start downloading the typeface its body text wears and the one its headings wear (at most two files, at the exact addresses the page's own font rules use). Before, the browser only found the font files after reading the stylesheet, which on a hosted site held up text in its real face for about two seconds. A site can turn this off with the `gogh_preload_fonts` filter. Separately, gogh's section block now uses WordPress's current block format (API version 3), so the WordPress 6.9 block editor no longer warns that it is deprecated, and the editor can draw the page in its iframe. Sections saved before still open as valid.

**Switching header layout keeps your menu (v0.99.653).** Picking a different header or footer layout in the header room used to save the new layout without saying which menu it shows, so WordPress showed the newest menu on the site instead. A site with more than one menu (a separate footer menu, or menus left behind by a starter) could find a different menu in its header after a layout change. Now the menu the part showed comes along with every layout pick, and the layout previews show your real menu too. Also: maps and experiences now publish as valid blocks, so the WordPress block editor no longer flags those sections as broken ("This block contains unexpected or invalid content"). Pages published before still show their maps.

**Readable small print (v0.99.652).** An accessibility pass over five blueprint sites with axe (WCAG 2.2 AA, every page, desktop and phone) found one kind of problem: small text gogh had softened too far. Three changes. The contrast check that runs when you add, roll or restyle a section now holds small words (under 24px, or under 18.7px when bold) to 4.5:1 and big words to 3:1: a softened colour such as a section's eyebrow keeps its colour and is made just strong enough, quietly, and a solid colour that reads but falls short (white on a mid orange) changes to the theme colour that passes, with the usual message and Undo. Small print in the looks (blog dates, captions, the contents title, tab labels, the form's note, the photo credits line) is a little stronger, and carousel dots are easier to see. The contact form's fields have proper labels for screen readers, fill in your name and email from the browser, and announce the thank-you. Pages you published before keep their colours until you next touch that section.

**Blog looks wear the site's heading face (v0.99.651).** The blog and reading looks give post titles a "second voice": the site's heading font. gogh read the body font only in one of the two forms WordPress stores it in, so on many sites it never recognised the body font and took whichever custom font was installed first, which could be the body font itself. A recipe blog's titles came out in its text face. Now the second voice is the font the site's headings wear.

**The Long Way, a travel journal blueprint (v0.99.650).** A slow-travel journal by a made-up writer who stopped flying: seven stories by train and ferry, each with a photograph and a "Getting there" list of the route. The home page opens on a photo of the sea through a train window. Below it, the latest stories in the cover look (the newest opens over its picture), a band of numbers (countries, kilometres by rail, ferries, flights: zero), "Right now" beside a map of where the writer is, and a dark band with RSS and "Suggest a place". The Stories page wears the cover look too. Fixed on the way: in the cover look on a posts page, the newest story's date was dark on the dark picture, because the theme links the date.

**Second Helpings, a recipe blog blueprint (v0.99.649).** A home cook's recipe blog: eight recipes, each with a photograph, filed under *Quick suppers*, *Baking* and *Slow weekends*. The home page shows two of those categories as card grids, with each card's time and servings under the title. The Recipes page wears the cards look, and every recipe is written as ingredients and method lists. Site definitions gained post categories (`cats: ['Baking']`, made when missing), and a posts section can ask for one category by name (`posts: { cat: 'Baking' }`) and a picture shape (`aspect`). Also: installing a font pair on a site that already had other fonts installed could give the new fonts the old fonts' files (seen on Studio and Playground). Now each font only uses its own files. On the posts page, the cards, cover and list looks take the page heading out to the grid's edge, and a posts grid sits closer under its heading.

**Small Hours, a writer's blueprint (v0.99.648).** A blueprint for an essayist: *Small Hours*, monthly essays by a made-up writer, in Instrument Serif and Source Serif on warm paper with one deep red. The home page leads with the latest essay, then the archive as a ruled list of titles and dates, a short about and a "Get the next one" band with an RSS link. It opens straight into the writing room on the newest essay, because a writer's first job is writing. Site definitions gained what this needed: a post can carry a **date** (`2026-09-14`), an **excerpt** and a **slug**; `blog_look` sets the posts page's look (here the ledger); and `open: 'writing'` sends the finished build to the writing room on the newest post. In the ledger look, a post with no picture of its own shows no coloured stand-in, and on the posts page the ledger keeps to the reading column so it lines up under the page heading.

**A design's pieces arrive already styled (v0.99.647).** A site definition can name **styles** for each kind of piece (headings, text, buttons, pictures, and shapes and cards) and mark one of each as the default. Add a button to such a site and it arrives in the design's button style, with no class to type and no CSS to open. A chosen piece's bar now starts with its style, for example *Style · Pink print*: tap it for a panel of pictures of that piece in each style, the default marked. Pieces inside a card get a small bar with the same chip. The section picker opens with the design's own sections ("From Misprint": a ticker band, a drying rack, ink circles). Styles for headings, text, buttons and pictures are WordPress block styles (`is-style-…`), so the block editor's Styles panel lists the same names; shape styles stay gogh's own, because a group style would show on every group. Choosing a style clears a colour picked before it (the last choice wins), and while words wear a style the colour panel's first swatch is the style's own colour. Also: the size button says *Fitted* once a corner-drag fits words to their box, theme sizes read as words (*Extra large*, not `x-large`), a dragged piece keeps a design's site-wide CSS under the hand, the *Hidden on phones* tag ignores a design's text effects, and sections carrying a design's class or a one-page anchor no longer show as invalid blocks in the block editor. Misprint now uses all of it.

**A design can bring its own CSS (v0.99.646).** Any piece or section can carry its own class names, the way core blocks have *Additional CSS class*: letters, digits and dashes, up to four, never starting `gogh-` or `wp-`. They ride the canvas, the saved model and the published blocks, so a stylesheet can find a piece after any edit. A site definition can bring that stylesheet as `css`; gogh writes it into WordPress's own Additional CSS between markers (the person can read and change it there, and a rebuild replaces only gogh's part). Google Fonts `@import` lines are lifted out and loaded as proper stylesheet links, because WordPress prints Additional CSS at the end of the global styles, where an import is ignored. The first blueprint to use it is **Misprint**, a risograph studio and zine library (`blueprint-misprint.json`): misregistered blue-and-pink headlines that snap into register on hover, photographs pulled as halftone duotones, three ink circles that overprint where they meet, prints pinned at angles that straighten when you look at them, a spinning sticker, an endless ticker, a giant outlined word and paper grain. Motion stops while editing and for anyone who prefers reduced motion.

**Make: chat, and gogh builds your site right here (v0.99.645).** A blueprint that is not a design: `blueprint-make.json` lands on `/?gogh-make=1`, a page of its own where you say what your site is for and what it is called. gogh's helper asks a question or two, finds pictures and writes the site; the moment it publishes, the page fetches the definition, applies it to this WordPress through `gogh/v1/site-def?replace=1` and runs the build, landing on the new home page in about a minute. The admin bar gains **Chat**, which returns to the same conversation (kept in the browser): ask for a change and it rebuilds in place, moving the pages and posts the last chat made to the bin and never touching anything you made yourself. The helper learned three things for it: a chat running inside the site is told not to hand over a link; the stream sends a heartbeat every fifteen seconds so a long write is not cut off; and the builder can place a **map** of a place you name (never an invented street), drawn with gogh's map piece. The four showcase definitions were also snapped back onto gogh's 24/72 rhythm.

**Northern Blocks, a community conference (v0.99.644).** The fourth showcase blueprint: the name at display size beside a stage picture with a yellow ticket sticker, a cobalt band of numbers in yellow, speaker cards with a portrait, name, role and talk, the schedule as a ruled timetable over two days, the venue with a map and access notes, three ticket cards, news posts with pictures, and a form to hold a ticket (`blueprint-conference.json`). A fictional event; a site definition only, on the Noon variation.

**Rosa & Tom, a wedding on a farm (v0.99.643).** The third of the showcase blueprints after Ferro & Lane and Cinder: the couple's names at display size over the corner of a golden-hour picture, a blush sticker saying when to reply, the date in big sage numerals, the day as a ruled timetable, the story as five lines, three pictures, travel and places to stay beside a map, and an RSVP form (`blueprint-wedding.json`). Built from a site definition on the Afternoon variation with Unsplash pictures; no plugin change was needed.

**Maps frame on the live page, and a definition can carry a loop and a map (v0.99.642).** A map piece published as a plain *Open the map* link on every site: the saved model never kept the embed's link, so the page renderer had nothing to frame. The model keeps it now, and pages published before this read it back from their own markup, so their maps frame without a republish. A site definition section can carry `video` (a web address of an .mp4) with `tint`, and an `embed` piece with a `url`; buttons in a definition can carry `btnBg`, `btnText` and `ghost`. A section drawn from a template now keeps its background video (it used to be dropped). When a design brings its own palette, the variation's picture filter goes (Midnight's purple-and-green duotone stained every photograph). The first site to use all of it is **Cinder**, a wood-fired restaurant: a kitchen loop behind the name, tonight's menu as a priced ledger, a poster of the fire with a big 450°, the room, hours beside a map, the full menu by course, the chef and the farms, private dining, and a booking form (`blueprint-cinder.json`).

**Pictures from the web become pictures on the site (v0.99.641).** A site definition's post pictures and section pictures that came from Unsplash (or any address without a file extension in its path) were never sideloaded: WordPress's own sideloader refuses a URL with no `.jpg` in it, so every AI-built site and every hand-drawn definition published posts with no featured image. The boot now fetches the file itself, names it by what it turns out to be, and hands it to the media library. The first site to use it is **Ferro & Lane**, a poster of an architecture studio: a headline over the corner of a picture, an orange disc, a turned caption, a ruled ledger of ten buildings, three pictures with a big number, two partners, notes with pictures, and a contact form (`blueprint-ferro-lane.json`).

**The grid is drawn the way Squarespace draws it (v0.99.640).** While a piece is on the move, or always with the # button, a section shows 24 tinted columns with gutters inside the page margins, rows of 24 with gutters the full height, and nothing painted in the side margins; hairlines mark the margins and the centre. Dark and pictured sections lift the tint; photo, video-tint and effect sections draw the same lattice. Underneath, gogh's magnets are untouched: a neighbour's edge, the centre line, equal gaps and rhythm still come first, and sizes stay free. Only the lowest snap tier changed: an edge released near a drawn line lands on it, where it used to land on an invisible 72 line, and with the grid button on the drawn lines are the mesh. It came out of the Cells experiment, which James parked: he wanted the look and gogh's feel, not whole-cell sizes.

**Buttons on phones are their own size again (v0.99.639).** Since the ceramics shop work on 10 September every gogh button published full width and squat on a real phone, and in the editor's phone preview. The desktop rule that makes a button fill its box had gained `:not(.gogh-widget *)` so it would leave WooCommerce's Add to cart alone, and that clause lifted it above the phone rule that shrinks a button back to its words and 44px. The phone rule now carries the same clause, so the two tie and the later, phone one wins. A test proves the order.

**Typing, properly (v0.99.638).** Three fixes to editing words on the canvas. **Enter in a heading is a line break** (it used to finish the heading and hop to the paragraph below; a tester 'could not insert a CR/LF in the headline'); Esc or a click away finishes. In a paragraph Enter still makes a paragraph gap and Shift+Enter a single break. **An Enter or Esc that belongs to an IME composition** (Japanese, Chinese, Korean input) is left to the keyboard. **A third click straight after entering an edit keeps the caret where it is** instead of selecting the whole paragraph, which made the next keystroke replace everything (Sjoerd: 'All text gets highlighted when I want to edit. This erases what I typed before'); a deliberate double-click on a word a moment later still selects the word. Deleting words already pulled the pieces below back up; a test now proves it. **The tester card** on the gogh user test moved to the bottom left and went dark (amber label and Done), the same as the core Site Editor test's card, so it stops covering the section pill's menu and the bar's tools.

**The same user test on the core Site Editor (v0.99.637).** A second tester link, `/test?suite=core` on the helper, boots `blueprint-usertest-core.json`: Twenty Twenty-Five, Contact Form 7, and the Elliot Grey site rebuilt in core blocks (`core-boot.php` in the gogh-demo repo, same pages, words, pictures, menu and palette), with a small plugin, `usertest-card.zip`, that puts the same seven-task card over the Site Editor, the page editor and the site. Every session's start event now carries `suite`, *gogh* or *core*, and the `/tests` report has a Suite column and an All / gogh / core filter, so the two funnels sit side by side.

**The brand card's foot says what it does (v0.99.635):** *Remix rolls new looks around these colours*, with **Edit brand** and **Try other fonts** as the two doors (the second read *Fonts*, a noun beside a verb).

**The brand card labels its rows (v0.99.634):** *Colours* with the dots, and *Fonts* as *Fraunces for headings, Manrope for body*, so the font names no longer read as more colours.

**The brand reader reads roles written after a code, and fonts named before their role (v0.99.633).** A guide that lists several colours on one line with the role in brackets after each code (*Charcoal #1B1B1F (background), Bone #EDE8DF (text), Ember #E8602C (accent)*) used to give each code the previous code's role, so the page came out Bone, the buttons Steel and the words Charcoal; and *Space Grotesk for headings* read no font at all. The words right after a code now win when they name a role, a colour's own name before it is only a fallback (Charcoal is a word for ink, but that Charcoal was the page), a second *accent* becomes the second accent, and *Name for headings* / *Name for body* are read as well as *Headings: Name*.

**The sketch's Add to cart pill is visible on every theme (v0.99.632).** It was there but invisible: its ground was mixed from `currentColor` while the same rule set its own colour to the theme's base, so pill and words were both the page colour on any pale theme (Hollowell, the ceramics starter). It is drawn in the theme's contrast on base now.

**The products element's sketch follows the markup that publishes (v0.99.631).** Price, rating and Add to cart in the editor's product cards, and the panel's *Show* chips, are read from the element's saved block markup on load, not from the panel's own memory of the choices. The two had drifted on a starter shop: the editor drew no Add to cart while the live page had one.

**The bar's Edit button keeps a little air on its left (v0.99.630):** WooCommerce's *Live* / *Coming soon* badge is WooCommerce's own admin-bar node and sits right before gogh's Edit; the two touched on a shop.

**Site style says what your brand uses (v0.99.629).** With a brand set and nothing rolled yet, the *On your site now* card shows straight away as the brand's receipt: **Your brand**, its colours as dots with a count, and its font pair by name (or *the theme's own type*), with *Remix keeps these colours* underneath and two doors, **Edit brand** (the brand form) and **Fonts**. After a Remix the card turns into the roll receipt as before, with Back and Keep this. The line that sat under the two buttons (*Remix keeps your brand colours…*, v0.99.628) is gone; the card carries it.

**The corner ✏️ Edit pill is gone wherever the admin bar is (v0.99.628):** it duplicated the bar's Edit. It still shows on a page with no admin bar, where it is the only way in.

**Site style's top row: Your brand on the left, Remix on the right (v0.99.628).** The brand is an outlined pill the same height as Remix, with its dots, its name and a pencil, so it reads as a door; a line under the pair says *Remix keeps your brand colours and rolls everything else.* Before, Remix was a loud black pill with the brand as a caption beside it.

**The brush carries its word, *Styles* (v0.99.627)**: an icon alone was a guess, and Site style is the door that matters most. The Desktop | Mobile pair stays icon-only.

**The brush is a round paintbrush now (v0.99.626)**, handle and tip; the flat Squarespace-style brush read as a spatula at bar size.

**The brush sits left of the Desktop | Mobile pair (v0.99.625):** making, then looking, then help. It was between the pair and the ? before.

**Site style's *On your site now* card stays hidden until a Remix has been rolled (v0.99.624).** It used to show as an empty card under Remix and Your brand, a label over nothing, because its layout rule out-ranked the hidden attribute.

**The suite never writes the site's header, footer or global styles (v0.99.623).** `?gogh-test=1` answers every write to `/wp/v2/template-parts/` and `/wp/v2/global-styles/` itself and lists what it blocked on `window.__goghTestBlockedWrites`. Before this, a run could leave a site with the test's header layout or a test palette and fonts when a keep's restore step never ran.

**Publish lives in the admin bar (v0.99.622).** The chip that floated at the bottom right (page name, status dot, Publish, ✦) is gone. Its job sits in the admin bar right after **View site**: a dot and a few words, *All published* (green) or *Unpublished changes* (amber; *· backed up* joins it once the draft is safe on the server), then a **Publish** button that greys out when there is nothing to publish rather than vanishing, so nothing jumps. *Publishing…* and *Published ✓* show there too, and a failed publish reads *Publish failed* with **Retry**. Hover the status for the longer sentence (*Your changes are backed up. Publish puts them live.*). The page's name, which rode the chip, now sits at the top of the **Page** drawer, with *· Rename* after it; click it to rename, and an unnamed page shows the amber *Untitled page — name it* invitation there. The ✦ answer-ready mark is no longer on the chip; the SEO tab on the rail is that door. A page with no admin bar (a seamless playground) keeps the floating chip at the bottom right.

**A see-through header auditions properly in the design view again (v0.99.621).** The rule that keeps a *sticky* header in flow inside the zoomed page matched the live header's content even while another layout was previewing, so with a sticky header the Transparent option sat in flow over a pale band instead of floating. The rule now looks only at whichever content is showing, and a see-through header floats at the artboard's top in the design view even when it is also sticky.

**The help sheet's bigger/smaller button is a drawn icon (v0.99.620)**, the same round button as ✕ at the sheet's top right; before, a typed ⤡ glyph came out small and squashed.

**The top bar, the simple way (v0.99.618).** The admin bar's gogh door is one word now, **Edit**, drawn as a solid white button at the top left where *Edit with gogh* sat (posts keep *Edit post*). While editing, the same spot reads **View site** with the eye, as before: the way in and the way out are one spot. At the top right, just left of the ? help button, the **Desktop | Phone** pill is a pair of icons, a screen and a phone, named on hover (the tooltips and screen-reader labels read *Desktop view* and *Mobile view*, v0.99.619). Left of it (right of it before v0.99.625), a **brush** (tooltip *Site Styles*; icons in the bar are drawn with paths only, since WordPress's admin-bar reset zeroes an SVG `<rect>`, v0.99.619) opens the **Site** drawer: Site style, Fonts, Motion, Edit header, Edit footer, everything about the whole site. The left rail keeps **Page** and **SEO**, the two doors about *this* page; it no longer carries a *Site* tab, and the Page and SEO drawers are the same size as before. Pressing the brush while the Site cards are already open closes the drawer. The tester card's *look* hint now says to press the brush at the top right, and its *phone* hint says to press the phone icon there.

**Edit header and Edit footer dock on the left (v0.99.600).** Everything about the whole site now lives in the left panel: Site style, Fonts, Motion, Page style, and from this release the header room and the footer room too, with the page zoomed out beside them. The part being edited keeps a ring and a small *Editing the header — changes preview live* label; nothing is dimmed any more. The panel is titled **Editing the header** (or footer). **There is no Done and no Cancel (v0.99.611): the room keeps on click like Site style and Fonts.** A change waits about a second for its neighbours (a dial still dragging, a colour being compared), then writes the header and re-renders it in place — no reload — and the receipt says *The header is kept — it is on every page* with **Undo**, which writes the previous header back. ‹ Back, ✕ and Esc return to the Site cards, keeping any change still waiting. Picking a layout keeps the Layout list open (v0.99.614; it used to fold on a pick), and nothing in the room is rebuilt by a keep — the controls stay exactly where your hand is. The room's own pages — Edit menu items, Logo & name, Mobile menu — dock in the same place and their *Back to header* re-opens the room without leaving the design view. Edit footer scrolls the zoomed page down to the footer. Before this, the room floated centred under the header over a dimmed page, and its Mobile menu page hid the Centred layout behind its own controls. The chip above a docked page's title reads **‹ Back** (it said *Site* or *Page*, which meant nothing to someone who had just pressed Edit header). **Stick to the top** cannot be shown in the design view — a pinned header inside the zoomed page slid the first section under it — so there the header stays in place and a line under the switch says *Sticks to the top as visitors scroll. Press View site to see it.* Saving a phone number, email or phone menu re-renders the header; the room's label rides the fresh markup and *Open the menu to preview* still opens it (v0.99.601). Leaving a room brings the *Edit header* pill on the header back. The *Edit header* pill also shows on hover in the zoomed design view (v0.99.602), since a saving Done lands there. The **footer room** has no *Mobile menu* door and no *Stick to the top* switch — those belong to the header. Sticking cannot be shown in the design view at all: the page sits inside a scaled transform there and neither sticky nor fixed positioning answers to the window through it; the real page does the sticking (checked: the header holds at the admin bar's height as visitors scroll). The row above a room page's title (*‹ Back to header*) has more room beneath it. While a header or footer room is open, the part's own hover pill (*Edit header*; it read *Edit site header* before v0.99.612) and veil are hidden, on desktop and in the phone view alike (v0.99.603); they return when you leave.

A small ? sits in the admin bar just left of Howdy, on every screen where the bar shows, for any logged-in user when a helper URL is set. In the gogh editor it opens the help sheet (the same one the side rail's ? opens); on any other screen, wp-admin included, it opens the helper in a new tab. The node is `gogh-help` under `top-secondary`; the `gogh_helper_url` filter turning the URL off removes it.

### The admin bar's edit label never flashes (v0.99.567)
One function, `gogh_ab_edit_label( $editing, $is_post )`, renders the bar's gogh node — 'Edit' (before v0.99.618: 'Edit with gogh') / 'Edit post' on a page, 'View site' (before v0.99.598: 'Exit gogh editor') in the editor — and the editor receives both variants (`cfg.abEdit`) so when the mode flips without a reload it writes the same markup the server would. Before, the server rendered a tick and 'Done' while the page loaded and the editor's script then wrote 'Exit gogh editor' over it, so 'Done' flashed for an instant on every open.

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

- Plugin version: **0.99.762**
- Requires WordPress **6.5+**, PHP **7.4+**
- Text domain: `gogh-editor`
- readme.txt Stable tag: `0.26.0` · Tested up to: `7.0`
- Note: the readme Stable tag (`0.26.0`) does not match the plugin header version (`0.99.762`). Quote the plugin header version.

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

Shown in the picker: **Hero**, **Cover**, **Big statement**, **Story**, **Numbers**, **Article**, **Featured product**, **Bestsellers**, **Editorial split**, **Latest posts**, **Featured post**, **Titles and dates**, **Columns of links**, **Featured and links**, **Picture doors**, **Categories in the menu**, **Featured product in the menu**, **Shop the look**, **New in**, **Sale**, **Categories**, **Feature cards**, **Pricing**, **Quote**, **Testimonials**, **Call to action**, **Get in touch**, **Profile card**, **Job card**, **Place card**, **Photo wall**, **Carousel**, **FAQ**, **Tabs**, **Gallery**, **Photo cards**, **Portfolio**, **Menu**, **Team**.

Non-starter (surfaced elsewhere): **Start from scratch**.

## Shapes and dividers

Shapes: `square` (Square), `rounded` (Rounded), `circle` (Circle), `pill` (Pill), `arch` (Arch), `tri` (Triangle), `diamond` (Diamond), `blob` (Blob).

Divider shapes (plus "None"): `curve` (Curve), `sweep` (Sweep), `dunes` (Dunes), `arch` (Arch), `sheet` (Sheet), `melt` (Melt), `mist` (Mist).

## Elements

Element types that survive a publish: `heading`, `para`, `button`, `image`, `video`, `embed`, `icon`, `rule`, `badge`, `box`, `widget`, `exp`. Anything else added from the block editor is lost on the next Gogh publish.

"Add element" palette items: `badge`, `button`, `card`, `cards`, `embed`, `exp`, `form`, `heading`, `icon`, `image`, `list`, `para`, `posts`, `products`, `rule`, `video`, `write`.

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
| `save_post` | action | 1 |
| `wp_footer` | action | 10 |
| `query_loop_block_query_vars` | filter | 10 |
| `wp_enqueue_scripts` | action | 10 |
| `wp_enqueue_scripts` | action | 10 |
| `render_block_woocommerce/add-to-cart-form` | filter | 10 |
| `woocommerce_before_add_to_cart_form` | action | 10 |
| `render_block_woocommerce/product-button` | filter | 10 |
| `init` | action | 10 |
| `body_class` | filter | 10 |
| `wp_enqueue_scripts` | action | 10 |
| `body_class` | filter | 10 |
| `wp_insert_post_empty_content` | filter | 10 |
| `trashed_post` | action | 10 |
| `frontpage_template_hierarchy` | filter | 10 |
| `wp_head` | action | 10 |
| `wp_head` | action | 10 |
| `wp_head` | action | 10 |
| `enqueue_block_assets` | action | 10 |
| `init` | action | 10 |
| `wp_enqueue_scripts` | action | 10 |
| `template_redirect` | action | 10 |
| `admin_bar_menu` | action | 10 |
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
| `wp_head` | action | 2 |
| `wp_enqueue_scripts` | action | 10 |
| `wp_enqueue_scripts` | action | 10 |
| `rest_api_init` | action | 10 |
| `enqueue_block_assets` | action | 10 |
| `admin_bar_menu` | action | 10 |
| `admin_bar_menu` | action | 10 |
| `admin_bar_menu` | action | 10 |
| `admin_bar_menu` | action | 10 |
| `admin_bar_menu` | action | 10 |
| `admin_bar_menu` | action | 10 |
| `wp_insert_post` | action | 10 |
| `block_editor_settings_all` | filter | 10 |

Filters exposed for third parties: `gogh_default_editor`, `gogh_claims_post`, `gogh_labs_ask`, `gogh_card_chips`, `gogh_helper_url`, `gogh_user_test_tasks`, `gogh_rebake_enabled`, `gogh_schema`, `gogh_schema_enabled`, `gogh_preload_fonts`, `gogh_webmcp_enabled`, `gogh_convert_enabled`.

REST routes registered: `gogh/v1/version`, `gogh/v1/starter`, `gogh/v1/site-def`, `gogh/v1/type-scale`, `gogh/v1/blog-style`, `gogh/v1/motion`, `gogh/v1/home`, `gogh/v1/design-looks`, `gogh/v1/ask`, `gogh/v1/imagine-exp`, `gogh/v1/ask-key`, `gogh/v1/menu-style`, `gogh/v1/first-minute`, `gogh/v1/ask-log`, `gogh/v1/active-style`, `wp/v2/gogh-product/(?P<id>\d+)`, `wp/v2/gogh-product/(?P<id>\d+)/autosaves`, `gogh/v1/pattern`, `gogh/v1/render`.

Core REST endpoints used by the editor: `wp/v2/blocks`, `wp/v2/categories`, `wp/v2/pages`, `wp/v2/posts`, `wp/v2/template-parts`.

Capability checks in PHP: `edit_posts`, `edit_post`, `edit_theme_options`, `edit_others_posts`, `edit_products`, `manage_options`, `edit_pages`, `edit_css`, `upload_files`, `unfiltered_html`, `activate_plugins`, `install_plugins`, `manage_woocommerce`, `read`.

Query-string switches: `?gogh-edit`, `?gogh-ps`, `?gogh-test`.

## Exact UI labels (tooltips and button titles)

These are the real strings in the current build. Use them verbatim; never paraphrase a label.

- "' + ALIGN_WORD[how] + '"
- "' + d.label + '"
- "' + d.title.replace(/"
- "' + escAttr(e.alt || 'Video') + '"
- "' + escAttr(l.name) + '"
- "' + escAttr(o[2]) + '"
- "' + escAttr(stl.name) + '’s colour"
- "' + escAttr(t.name) + '"
- "' + hp[1] + ' — ' + hp[2] + ' units"
- "' + label + '"
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
- "A set keeps at least one card"
- "A set of cards that share one design — type on any card, add more in a click"
- "A video — upload one, or paste a YouTube or Vimeo link"
- "AG"
- "Aa"
- "Add"
- "Add a card"
- "Add a card here"
- "Add a category ↗"
- "Add a page to this menu"
- "Add a picture or video"
- "Add a product ↗"
- "Add card"
- "Add link"
- "Add or change categories in WordPress ↗"
- "Add or change products in WordPress ↗"
- "Add something to this section"
- "Add to page"
- "Adjust spacing"
- "Align the pieces inside this card"
- "Align the pieces to each other"
- "Align ▾"
- "All options"
- "All the pictures in your media library"
- "An icon — a simple line drawing in your text colour"
- "Apply"
- "As placed"
- "As typed"
- "Auto"
- "Back"
- "Back to Site styles"
- "Back to my page"
- "Back to the Site cards"
- "Back to the whole set (Esc)"
- "Background & look"
- "Badge"
- "Bigger text"
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
- "Card options"
- "Cards"
- "Change your colours"
- "Changes the colours and fonts across your site. Back puts it back."
- "Choose a style"
- "Close"
- "Close help"
- "Copies the styles as site definition JSON"
- "Copy as definition"
- "Copy machine version"
- "Copy style — then click other text to paint it"
- "Copy summary to share"
- "Create"
- "Custom colour"
- "Custom text colour"
- "Dark"
- "Dark page"
- "Delete"
- "Delete (Del)"
- "Delete card"
- "Delete saved section"
- "Delete the selection"
- "Delete the set"
- "Delivery & returns"
- "Desktop menu"
- "Desktop view"
- "Discard changes"
- "Done"
- "Done — keep this look"
- "Downloads the styles as a piece of site definition, to add to the blueprint"
- "Drag to move this card"
- "Duplicate"
- "Duplicate (or Alt-drag)"
- "Duplicate the selection"
- "Duplicate the set"
- "Edge to edge of the section"
- "Edit design"
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
- "Go, and keep my changes for later"
- "Grid: show and snap"
- "Heading"
- "Headings"
- "Help — ask gogh anything"
- "Hidden"
- "Hide on phones"
- "History — earlier published versions of this page"
- "Home"
- "Home page"
- "Icon"
- "Image"
- "Imagine"
- "It is already on your site — this closes the panel"
- "Italic"
- "I’ll find my own way"
- "Journal"
- "Keep editing"
- "Keep it"
- "Keep this"
- "Keep this brand"
- "Keep this layout (updates every page)"
- "Layout and style for every card"
- "Left"
- "Library"
- "Light"
- "Light page"
- "Line"
- "Line the pieces up, even the gaps, match sizes that are nearly the same"
- "Link"
- "Link text (⌘K)"
- "List"
- "Make a card"
- "Make it freeform"
- "Make these a set"
- "Make these cards one set: add, move and restyle them together"
- "Make these one card — it holds together on phones"
- "Make this wider"
- "Manage products"
- "Manage products ↗"
- "Manage this menu — reorder, nest, swap menus"
- "Mobile view"
- "More for this set"
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
- "My sections"
- "Name, email and a message — straight into your own site, no plugin"
- "New in"
- "No picture"
- "None"
- "On sale"
- "One product, hero-sized — a card with a real add-to-cart button"
- "Open interactive experience"
- "Open the map"
- "Open your products in WordPress"
- "Our story"
- "Outline"
- "Paste HTML"
- "Peek at pages"
- "Phone menu"
- "Pick a custom colour for this section"
- "Pick any colour for this ' + noun.toLowerCase() + '"
- "Picture and link"
- "Posts"
- "Products"
- "Publish"
- "Publish & close"
- "Publish this page first: visitors can only arrive at a published page"
- "Put it back"
- "Questions"
- "Redo (⇧⌘Z)"
- "Remix keeps these colours and rolls everything else"
- "Remove"
- "Remove from Your sections"
- "Remove from menu"
- "Remove image"
- "Remove link (keep the text)"
- "Remove picture"
- "Remove poster"
- "Remove the address"
- "Remove this ' + labels.one + '"
- "Remove this photo"
- "Remove this slide"
- "Remove video"
- "Rename"
- "Rename or remove"
- "Reset"
- "Restore this version"
- "Right"
- "Roll another take of this design"
- "Save"
- "Save description"
- "Save style"
- "Save to the blueprint"
- "See all pictures"
- "See all →"
- "See every layout of this design side by side"
- "See it on a phone"
- "See the page as a phone"
- "Send backward"
- "Shape"
- "Show"
- "Show fewer"
- "Show more"
- "Show the menu open on the page"
- "Show the next layout"
- "Shown"
- "Site designs — a whole site, ready to tweak (replaces this one)"
- "Site styles: the fonts and colours of the whole site"
- "Smaller text"
- "Solid"
- "Start blank"
- "Start writing — a reading column, cursor ready"
- "Switch design"
- "Switch it on"
- "Text"
- "Text alignment"
- "Text colour"
- "Text size: choose from every size"
- "The featured card"
- "The gogh build this tab is running"
- "The header rides along as visitors scroll"
- "The look before this one"
- "Theme default"
- "Tidy up"
- "Too big — the longest word would run off the page"
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
- "Write"
- "Write a post ↗"
- "Write or change posts in WordPress ↗"
- "Your latest posts, live"
- "Your latest products, live — prices and add to cart included"
- "ag"
- "get one here"
- "gogh help"
- "lowercase"
- "pick from the shelf"
- "re-centre"
- "×"
- "‹ All cards"
- "← All layouts"
- "↕ Spacing…"
- "↶ Back"
- "▶ Auto-play"
- "⛶ Click to enlarge"
- "✦ Not this look?"
- "✦ Try a new look"
- "✨ Make draggable"
- "✨ Make it freeform"

## Exact toast and message copy

- "A section keeps at least one piece."
- "Back to freeform — drag it anywhere."
- "Backup restored — publish when ready."
- "Button updated."
- "Copied. Paste it into the site definition, beside pages."
- "Could not add that section."
- "Could not apply that style."
- "Could not change the styles: "
- "Could not delete that section."
- "Could not make "
- "Could not open the "
- "Could not open the layout panel."
- "Could not preview that layout — "
- "Could not put the "
- "Could not put the home page back: "
- "Could not rescale the type — "
- "Could not restore it."
- "Could not save that section."
- "Could not save the motion style."
- "Could not save the name — try again."
- "Could not save the page, so the "
- "Could not switch the "
- "Could not update the "
- "Downloaded "
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
- "One set now: add, move and restyle the cards together."
- "Out of the card — it’s its own piece again."
- "Publish failed: "
- "Publish your changes first — changing the page style reloads the page."
- "Published. "
- "Removed from the card."
- "Restored the version from "
- "Saved — search results and AI answers now quote your words ✓"
- "Saved “"
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
- "That’s as big as this text goes — a bigger size would run off the page."
- "That’s gogh. Everything else is just more of this."
- "The "
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
- "You left changes on this page unpublished. Bring them back?"
- "Your changes could not be kept for later. Publish them, or stay on "
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

`382` tests, run by appending `?gogh-test` to any Gogh page URL while logged in with edit rights on that page.
