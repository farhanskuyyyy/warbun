# Orders, receipts and reorder review

> Only [selected previews](../../PREVIEWS.md) are retained in Git. Export filenames below describe the original review run; bulk PNG/PDF evidence can be regenerated from the final native document.

Updated 2026-10-31 in the same `../final.figcraft` file using Figcraft MCP, product-design-sop and antislop core/UI. Design only; no Laravel application, database or live payment changes.

## Frames

| Target | Light | Dark | Layout |
| --- | --- | --- | --- |
| Mobile 390 × 844 | 205 | 858 | One-column scrollable history; bottom sheet receipt |
| Tablet 834 × 1112 | 8361 | 8395 | Two-column history; centered modal |
| Desktop 1440 × 960 | 557 | 1210 | Three-column history; centered modal |

Native page `13 · Orders, receipts & responsive` contains tablet frames, 18 receipts, six reorder review overlays, and behavior/handoff notes. Prototype flows are named `Orders · <device> · <theme>`.

Cards show placeholder product images from the existing design asset, product names, quantities, line totals, order/payment status, reference, date, fulfillment and total. Every order has **Lihat struk**. Completed orders also have **Pesan lagi**, which opens a review of products/current example prices before the existing checkout prototype. Pending orders omit reorder to reduce accidental duplicates.

## Receipt calculation examples

| Order | Calculation | Total |
| --- | --- | --- |
| WB-DEMO-001, pending cash | 25,000 subtotal + 10,000 shipping | Rp35.000 |
| WB-DEMO-002, percentage promo | 25,000 − 2,000 product discount − 5% × 23,000 | Rp21.850 |
| WB-DEMO-003, fixed promo | 25,000 − 2,000 product discount − 1,000 payment promo | Rp22.000 |

Customer service fee is a distinct row (Rp0 in these samples). The percentage base excludes shipping/fees in this example. These are illustrative promotions, not real QRIS/e-wallet offers. Actual calculation basis, eligibility, caps, rounding and charged amounts must come from backend/provider configuration. Historical receipts must use immutable order/payment snapshots, never current gateway rates or product prices. Merchant settlement deductions belong outside the customer receipt unless explicitly passed through as a customer promotion.

## Review and handoff

- Rendered/reviewed all six main screens and 24 receipt/reorder overlays in light/dark across three target widths. Individual PNGs are in this directory; receipt/reorder contact sheets are named `review-*.png`.
- Corrected currency alignment/tabular numerals, total-row height, growable product rows and tablet self-navigation. Final node bounds check across 30 frames found no text extending outside its immediate parent. Calculations checked against the displayed totals.
- Main PDF exports refreshed; `orders-tablet.pdf` contains the two tablet screens. Native save verified. No MCP lint command was available.
- Contrast samples: white/burgundy 9.05:1, muted/cream 5.51:1, teal/light status 5.34:1, dark teal status 6.15:1. This does not establish full accessibility compliance.
- Implementation breakpoints specified: one column below 768 px, two from 768–1199 px, three from 1200 px, with container-driven fallback and minimum 320 px cards. Frames demonstrate target layouts; continuous resize in a browser remains untested.
- Empty/loading/error, missing photos, unavailable items and changed price/stock behavior are specified in the native handoff notes. Product list/modal body scrolls; overlay header/footer remain fixed. Pending receipts say **Belum dibayar** and **Total tagihan**.
- Prototype connections were inspected in the layer tree. Headless MCP does not verify presenter playback, Escape/focus trapping or screen-reader operation. Reorder cart merging, quantity adjustment and actual stock/price validation require frontend/backend implementation. Checkout navigation currently uses the existing mobile checkout frame, including from tablet/desktop review overlays.
- Assumption: requested gateway deductions refer to customer payment promotions. Admin provider-fee configuration and merchant payout accounting are outside this customer-design scope. Validate that interpretation and the receipt hierarchy with the product owner/users before implementation.
