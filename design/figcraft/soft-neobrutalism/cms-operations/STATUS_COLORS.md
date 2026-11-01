# CMS semantic status colors

> Only [selected previews](../../PREVIEWS.md) are retained in Git. Export filenames below describe the original review run; bulk PNG/PDF evidence can be regenerated from the final native document.

1 November 2026. Updated the same Warbun Figcraft file, pages 16, 18 and 19. Design-only revision: no Laravel/database mutations.

## Applied

50 existing CMS frames updated: eight Products layouts, four product detail dialogs, six Dashboards, six POS screens, six Monitors, sixteen order-detail dialogs and four ready-but-unpaid warnings. Badge status and payment status are separate from customer names and amounts. Stage filters use the same semantic colors; the selected Active queue retains the brand action treatment.

18 semantic states × light/dark = 36 reusable native badge components, with scoped paint styles and a reference board per theme (frames 31607 and 31701). States absent from current demo data appear in the reference boards only; no new records or routes were invented.

| Meaning | Tone |
| --- | --- |
| Pending / unpaid | Amber |
| Confirmed | Blue |
| Preparing | Purple |
| Ready | Teal |
| Completed / paid / active | Green |
| Partial payment / debt / low stock | Orange |
| Failed / cancelled / out of stock | Red |
| Refunded / expired / inactive / unassigned | Slate |

The light palette follows `resources/css/app.css` lines 471–478 and `components/transaction-status.blade.php`; dark palette uses matching hues with darker surfaces and lighter ink. CTA colors and money values retain their own roles. Text labels remain visible alongside a small status indicator.

## Verification

Used product-design-sop, antislop core/UI/human, and Figcraft MCP. Reviewed render sheets covering all 50 affected frames and both reference boards; inspected Monitor, Products, Dashboard and order dialogs at full size. Corrected selected-order reference wrapping and the waiting badge label width. All 16 text/background color pairs were calculated using WCAG relative luminance: light ratios 6.15–7.39:1, dark ratios 8.19–10.37:1.

Immediate-parent text-bounds audit covered all 50 frames and two boards; initial waiting-label overflow was fixed and re-rendered. Scrolling content deliberately clips at viewport boundaries. Keyboard/screen reader behavior, browser accessibility and actual workflow execution remain untested. This is not an accessibility-compliance claim.

Existing PNG/PDF exports were refreshed. `status-review-main.png` and `status-review-dialogs.png` cover the affected screens; `status-colors-light.png` / `status-colors-dark.png` show all semantic tokens. Native file: `../final.figcraft`.
