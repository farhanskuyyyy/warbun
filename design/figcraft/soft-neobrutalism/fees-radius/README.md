# Payment fees and delivery radius

> Only [selected previews](../../PREVIEWS.md) are retained in Git. Export filenames below describe the original review run; bulk PNG/PDF evidence can be regenerated from the final native document.

Updated 1 November 2026 in the same `../final.figcraft` document. Design/prototype only, using product-design-sop, antislop core/UI and Figcraft MCP. No Laravel, gateway API or actual geolocation implementation.

## Rules used in this design

| Payment | Example fee |
| --- | --- |
| QRIS | 0.7% of net merchandise plus shipping, before the fee itself |
| E-wallet: GoPay, OVO, DANA | Fixed Rp2.000 per payment |
| Virtual account / card | Rp0 example, pending actual configuration |
| Cash | No fee |

Example with Rp25.000 merchandise and Rp10.000 shipping: QRIS Rp245, total Rp35.245; e-wallet Rp2.000, total Rp37.000. With shipping Rp0: QRIS Rp175, total Rp25.175; e-wallet total Rp27.000. These examples are not claims about provider tariffs. Payment promotions reduce the bill and are distinct from customer service fees.

| Radius from store pin to address pin | Shipping |
| --- | --- |
| Less than 5 km | Free |
| At least 5 km and less than 20 km | Rp10.000 |
| At least 20 km | Delivery unavailable |
| Pickup at store | Free, no distance requirement |

Exactly 5 km is charged and exactly 20 km is blocked. Radius means straight-line geographic distance, not road travel distance. Compare the unrounded distance in meters; show more precision near thresholds when needed. The 20 km boundary follows the user's “20km tidak bisa” requirement.

## Canvas and flows

- Existing payment entry frames: mobile 154/807, desktop 522/1175. Per-type and per-channel fee information added across the native accordion states.
- Page `14 · Payment fees & delivery radius`: unknown/free/paid/outside/pickup delivery states at mobile 390×844, tablet 834×1112 and desktop 1440×960, light/dark; address example overlays; fee receipt examples; native handoff notes.
- Existing delivery entries 126/779 updated. Prototype starting flows are named `Delivery radius · ml/md/tl/td/dl/dd` and start before an address is chosen.
- Page `15 · Payment quotes · fee & shipping variants`: tablet payment variants and zero-shipping counterparts. In total, 192 payment frames cover three viewport families, light/dark, channel/accordion choices and the two shipping amounts. Native links preserve the quote; tablet channel choices stay within tablet layouts.
- Address overlays simulate saved pins at 4.2 km, 8.2 km and exactly 20 km. These are example data; there is no maps/geolocation lookup.
- Outside-radius delivery has no payment continuation and offers changing address/pickup. Unknown address disables continuation. Payment confirmation opens a fee receipt matching the selected amount; it does not create a real order.
- Existing historical order receipts retain their previous charge snapshots; new fee policies do not rewrite history.

## Verification and limits

- Inspected 252 frames (192 payment, 30 delivery, six address overlays, 24 receipt examples). Checked all payment prototype destinations: no missing target roots. After fixing tablet symbol widths, payment text bounds have no accidental parent overflow. The 24 instruction texts beyond mobile scroll viewport height are intentional scroll content.
- Generated 96 PNG renders: every delivery/address/receipt example and representative initial/e-wallet/QRIS payment layouts in paid/free shipping, three devices and light/dark. Main payment PNGs and four main PDFs refreshed. Contact sheets are `review-*.png`.
- Visual corrections: right-side accordion symbols, financial amount alignment, mobile method viewport clearance, tablet method/summary widths, and dark wordmark contrast. All financial examples reconcile. Boundary checks: 0/4.999 km free, 5/19.999 km charged, 20/21 km blocked.
- Node/graph checks cover the payment variants; full-size visual review focuses on the distinct layouts and fee/radius examples, not every repeated provider state. Actual presenter scrolling/clicks, continuous browser resize, keyboard/focus and screen readers remain runtime QA. No accessibility-compliance claim.

## Implementation handoff

Store pin, fee policies and radius bands need backend configuration. Customer address text alone is insufficient: require valid coordinates, compute distance and validate quote server-side, and recheck on address/fulfillment or price changes. Missing coordinates, lookup failure and expired quotes must never default to free shipping. Loading/retry/error behavior is specified in native handoff notes; five eligibility states are drawn.

Persist distance, policy version, shipping, fee type/rate/base/amount, discounts and total with the order/payment. Use transaction snapshots in receipts. Never silently apply a merchant settlement deduction to the customer bill. The example rounding rule is nearest rupiah; production currency precision, caps and rounding must follow the configured provider contract.

CMS/admin policy configuration and actual radius calculation are outside this customer-design update. Product-owner validation is still needed for non-example VA/card fees and the fee calculation basis before implementation.
