# Customer navbar authentication states

> Only [selected previews](../PREVIEWS.md) are retained in Git. Export filenames below describe the original review run; bulk PNG/PDF evidence can be regenerated from the final native document.

31 October 2026. Updated the same native Figcraft file; page 12 contains signed-in reference screens, account menus and handoff notes.

Used Playwright to open http://127.0.0.1:8000/ and inspect the guest navbar. Clicked Account and verified its Masuk/Daftar links and language control. The running web's guest state uses an Account disclosure; this design intentionally makes Masuk directly visible as requested. Signed-in menu content was checked in `resources/views/components/account-menu.blade.php`, not by signing into a live customer account.

Public browse references now have a Masuk button; customer-only references show a Font Awesome user control. Added 12 signed-in home/catalog/product variants across mobile/desktop and light/dark. Account menus contain profile, orders, addresses and logout. Staff Dashboard is excluded because this work is customer-only. Existing cart controls remain distinct. Auth forms and focused payment back-only headers are not given redundant login controls.

Guest account/order navbar links lead to the existing login reference. Signed-in duplicate browse links are remapped to signed-in references. Menu entries reuse existing private design screens; desktop account destinations without a dedicated desktop design currently use mobile references. Logout simulates navigation to the guest landing; authentication/session switching is not implemented in the prototype. Desktop Masuk also uses the existing mobile login reference. Login screens do not establish a real session in Figcraft.

Reviewed guest and signed-in mobile/desktop renders, dark mobile and account menu. Fixed an SVG geometry offset that put the logout icon too low. Native save and ten navbar/menu PNG exports succeeded; main theme PDFs refreshed. Structural checks verified child bounds except intentional scroll viewports and existing navigation destinations. Browser guest interaction was exercised; browser logged-in interaction, actual credentials, keyboard/focus/ARIA and role tests were not run. App/database source was not changed.

Implementation must derive auth state from the server, keep logout POST+CSRF, use real authenticated identity and permissions, and test menu focus/escape/outside-click behavior. No invented personal identity or admin capability was added.

## Dropdown positioning correction

User reported that the account dropdown escaped the screen. Root cause: manual overlay offsets are relative to the triggering layer, not screen coordinates (Figcraft MCP docs: https://github.com/storytold/figcraft/blob/main/docs/mcp.md). Replaced the old screen-like positive offsets on all 30 account controls with trigger-relative offsets calculated from bounded screen positions. With a 288 × 346 menu, mobile placement is left 16/top 92 (offset −230/+52 from the button); desktop is left 972/top 77 (offset −244/+52). All calculated menu rectangles have at least 16 px horizontal clearance and remain inside their viewport height.

Verified the saved interaction properties and created static in-context visual references on the authentication page. Inspected mobile light/dark and desktop light renders; these references are not actual presenter captures. Headless MCP still prevents visually confirming the running presenter click. Refreshed boundary PNG references as account-dropdown-bounds-0 through -3. The same native file is saved; reload an already-open preview to pick up the corrected interactions.
