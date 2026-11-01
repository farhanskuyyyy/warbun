# SOP pilot · Warbun payment

> Only [selected previews](../PREVIEWS.md) are retained in Git. Export filenames below describe the original review run; bulk PNG/PDF evidence can be regenerated from the final native document.

30 October 2026. Updated `final.figcraft` in place.

## Brief

Warbun customers need to deliberately select a payment channel, review their order total and place an order. Scope: checkout/payment/confirmation, mobile 390 × 844 and desktop 1440 × 960, light/dark. Existing soft neobrutalism and Warbun/teal palette retained. This targeted usability refinement does not need two new structural alternatives. Catalog/account/CMS and Laravel implementation are outside this pilot. Amounts and channels are illustrative, not live gateway availability.

## Actual tools and skills

Used product-design-sop and relevant antislop/antislop-ui guidance with Figcraft MCP inspection, batch editing, prototype links, paint styles, Auto Layout, screenshots and exports. UI UX Pro Max was searched for, not found and not claimed as used. No MCP lint command was available.

## Findings and fixes

- Removed the implicit default GoPay selection from entry frames.
- Opening a category no longer chooses its first channel. Added 20 open-unselected states across five groups, two sizes and two themes; confirmation is inactive until channel selection.
- Rebuilt channel-row destinations explicitly after duplication, because copied self-links could point back to the unselected copy.
- Removed the hardcoded Tunai label on confirmation; actual persisted order-method display is an implementation requirement.
- Converted repeated accordion headers and channel rows to horizontal Auto Layout. Labels fill available width; logos remain fixed. First render showed radio drift; positions were corrected and re-rendered.
- Bound scoped payment fills to ten reusable semantic paint styles under Warbun/Payment. Logo artwork is excluded from recoloring.
- Added an in-canvas SOP pilot handoff on the light foundations page.

## Verification

Rendered 64 payment states and reviewed eight contact-sheet overviews. Some thumbnails had crop artifacts; native BRI and desktop entry screenshots were separately checked. Full-resolution inspection additionally covered mobile entry, unselected e-wallet and light/dark confirmations. Contact sheets are not equivalent to full-resolution typography inspection of every state.

Structural inspection found no child-bound overflow or missing navigation destinations before the final explicit channel-link normalization; normalized targets are existing selected payment frames. Contrast measurements: white/burgundy 9.05:1; teal light/selected surface 5.34:1; teal dark/selected surface 6.15:1; muted text/cream 5.51:1. These measurements do not establish accessibility compliance.

Native save and four main theme PDF exports succeeded. Per-frame review PNGs/contact sheets are in sop-review. No application code/database changed; application tests were not run.

## Handoff and remaining validation

Focus/hover, keyboard/screen reader, provider loading/error/retry, intermediate widths, zoom and long localized labels still need implementation tests. Only header/row layout is flexible; page compositions remain fixed reference sizes. Linked-frame prototype groups reset to unselected rather than persist runtime form state; selection persistence on close/reopen/back needs implementation and user testing. Whole-page responsive Auto Layout and reusable components beyond scoped styles/layout remain further system work. A whole-product catalog/account audit is not claimed by this pilot.
