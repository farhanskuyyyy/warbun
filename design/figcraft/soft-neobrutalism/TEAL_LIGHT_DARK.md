# Warbun secondary palette · light and dark

> Only [selected previews](../PREVIEWS.md) are retained in Git. Export filenames below describe the original review run; bulk PNG/PDF evidence can be regenerated from the final native document.

Open `final.figcraft`. Earlier native versions were removed after consolidation into final.figcraft.

The file includes six pages: mobile, desktop and foundations for each theme. Each customer screen has a corresponding dark version (17 mobile + five desktop per theme). The dark shopping flow has its own starting point, with cross-page prototype destinations remapped to dark references.

| Role | Light | Dark |
| --- | --- | --- |
| Background | #FFFFFF | #171717 |
| Surface | #F8F6F1 | #242321 |
| Primary button with white label | #8B1E2D | #8B1E2D |
| Primary text/link | #8B1E2D | #E9A4AE |
| Secondary teal | #236B65 | #75C9BD |
| Secondary surface | #E4F0EA | #183D38 |
| Mustard accent | #E7B85B | #E7B85B |
| Product illustration surface | #F4DDAA | #5A4526 |
| Main text | #282724 | #F8F6F1 |

Burgundy remains the checkout/action color. Teal supports secondary controls and navigation. Muted mustard product surfaces add warmth without competing with primary actions. Hard shadows and outlined surfaces retain the Saweria-inspired soft neobrutalist treatment.

Verification: inspected all six pages; no out-of-parent layer bounds or missing prototype destinations. Visually reviewed mobile home and catalog in light/dark. Native save, four PDF exports and two catalog PNG exports succeeded. The prototype is still a design/navigation specification, not an application implementation or an interactive runtime theme toggle.

Review exports: `mobile-light.pdf`, `mobile-dark.pdf`, `desktop-light.pdf`, `desktop-dark.pdf`, `catalog-light.png`, `catalog-dark.png`.

## Payment accordion revision

Updated in the same native file. Mobile payment and desktop checkout now use a payment-type accordion, with selectable channel rows: e-wallet (GoPay/OVO/DANA), virtual account (BRI/BCA/Mandiri), QRIS, cards (Visa/Mastercard), and cash. These are illustrative design channels, not claims of live gateway support. Card payment is handed to the provider; sensitive card inputs are not collected in this design.

Page 07 holds 40 additional navigation states: collapsed and channel-selected variations across mobile/desktop and light/dark. Together with the four updated main frames, they simulate accordion open/close and channel radio selection. Summary and confirmation CTA reflect the selected channel; collapsed/unselected states have no confirmation action. The prototype models these as linked frames, not runtime form state.

Checked 2,839 layers for bounds and prototype destination existence; no bounds overflow or missing destinations found. Corrected desktop back/confirmation targets. Visually inspected mobile light/dark, desktop payment and BRI-selected dark state. Refreshed the four theme PDFs and added `payment-mobile-light.png`, `payment-mobile-dark.png`, `payment-desktop-light.png` and `payment-desktop-dark.png`. Existing application code and payment integrations remain unchanged.
