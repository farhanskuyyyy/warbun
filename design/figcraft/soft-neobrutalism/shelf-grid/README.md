# Shelf grid view and editor

> Only [selected previews](../../PREVIEWS.md) are retained in Git. Export filenames below describe the original review run; bulk PNG/PDF evidence can be regenerated from the final native document.

1 November 2026. Same native document: `../final.figcraft`. Page **20 · Shelf layout · Grid editor & viewing** (32521) contains the interaction states, six earlier-list backups, dialogs, alternatives and handoff. The six existing Shelf entry frames on page 18 now show the grid view.

## Scope and decisions

Design for Warbun owners configuring a rack layout, and cashiers/pickers finding products. The previous numbered-rack page remains available through Atur produk di rak. New spatial placement extends that concept; it does not move products between racks or mutate stock. Example rack positions, entrance and cashier landmark are illustrative, not measured store geometry. Grid cells are layout units, not metres.

Canvas + inspector was chosen over a modal editor to retain editing space and rack context. Desktop uses adjacent panels; tablet/mobile stack them inside the scroll body while save actions remain fixed. Mobile uses 20 px per logical cell and minimum example rack targets of 60×60; desktop/tablet use 40 px. Both use the same logical 16×12 grid.

54 main state frames cover light/dark at 1440×1000, 834×1112 and 390×844. Native paths demonstrate viewing rack 03 products, entering edit, moving one cell, resizing one cell, separate move-only/resize-only saves, returning to view and confirming discard. The other three example racks open correctly named empty detail dialogs. 24 dialogs cover all six viewport/theme combinations.

## Frame IDs

| State | dl | dd | tl | td | ml | md |
| --- | --- | --- | --- | --- | --- | --- |
| view | 26074 | 26325 | 26590 | 26749 | 26908 | 26998 |
| selected | 33142 | 33316 | 33490 | 33560 | 33630 | 33696 |
| edit | 33762 | 33936 | 34110 | 34180 | 34250 | 34316 |
| moved | 34382 | 34556 | 34730 | 34800 | 34870 | 34936 |
| resized | 35002 | 35176 | 35350 | 35420 | 35490 | 35556 |
| saved | 35622 | 35796 | 35970 | 36040 | 36110 | 36176 |
| saved-moved | 39260 | 39447 | 39634 | 39717 | 39800 | 39875 |
| resized-only | 40606 | 40793 | 40980 | 41063 | 41146 | 41221 |
| saved-resized-only | 41296 | 41483 | 41670 | 41753 | 41836 | 41911 |

Six flows are named `Shelf grid · <key>`. PNG names use `<state>-<key>.png`; the PDF contains view/edit/saved layouts. `review-main.png`, `review-saved.png`, `review-alternative-paths.png` and `review-dialogs.png` show all rendered examples. Handoff board: 43014.

## Geometry and persistence specification

Coordinates are zero-based integers. Rack 03 begins at x=1,y=6,w=4,h=3. Move-only changes x to 2. Resize-only changes w to 5. Combined changes x=2,w=5; y=6,h=3 remain. Saved example frames match the corresponding gesture path.

Future implementation must persist grid dimensions and each shelf_id,x,y,width,height atomically with a revision/version, validate permissions, preserve draft on failure, prevent duplicate saves and report concurrent modifications. Reject negative/out-of-grid positions, nonpositive dimensions and overlaps; keep the last valid saved layout on errors. Existing shelf IDs/product locations and inventory remain authoritative.

Numeric X/Y/width/height controls provide an alternative to pointer gestures. Implement keyboard movement, Escape cancel, focus/announcements, modal focus trapping/return, touch gesture handling and unsaved navigation guards. View mode must not allow dragging. Editing follows products.update permission.

Empty layout, saving, collision error, failed save/retry and concurrent-update behavior are specified in the handoff; empty/invalid/failure text specimens are drawn on the handoff board, not full connected screens.

## Honest prototype limits

Native onDrag and click actions navigate between predetermined example frames. **This is not free-form drag/resize, editable numeric input, or browser/server persistence.** The editing demonstration centers on rack 03; arbitrary repetitions/re-editing after save, creating new racks, collision detection, zoom/pan and live data require implementation. The saved label is a design state. Existing dialogs and old list patterns are retained without database writes.

## Verification

Skills: product-design-sop and previously loaded antislop guidance. Figcraft MCP performed edits, inspection, native action wiring and PNG/PDF export. No dependencies installed or Laravel/database changes made.

Reviewed render sheets for all 54 main states and 24 dialogs, plus full-size desktop/mobile views and the handoff. Corrected footer text spacing, disabled save before changes, selected outline contrast, product-assignment navigation and independent move/resize geometry. Text-parent bounds audit covered the 54 main frames, 24 dialogs and handoff without accidental overflow. Scroll clipping is intentional; resize handles include larger hit areas extending beyond the small visual grip. Checked 216 example rack rectangles: all within 16×12 bounds and no rack overlaps. This checks the designed examples, not a running collision detector.

Native presenter playback, browser drag/touch interaction, persistent saves, runtime authorization/concurrency, keyboard/screen-reader usability and continuous resizing were not tested. Existing operations PNG/PDF exports were refreshed with the new six grid entry views.
