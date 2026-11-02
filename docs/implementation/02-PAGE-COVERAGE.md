# Cakupan semua halaman, views dan endpoints

[Indeks](README.md). Snapshot source 2 November 2026: `php artisan route:list --json`, **144 route entries**. Inventaris ini perlu diperbarui bila source berubah. Cakupan software di bawah selesai berdasarkan [bukti QA](qa/FINAL-QA.md). Checkbox fungsi mengacu ke flow/regression yang relevan, bukan klaim setiap kombinasi input pernah diuji. Provider/hardware eksternal mengikuti batas di laporan tersebut.

## Cara menutup coverage

Untuk setiap halaman, isi tiga checklist dan tambahkan evidence/commit pada STATUS atau laporan fase. UI selesai berarti light/dark dan responsive telah ditinjau. Fungsi selesai berarti form/actions/data/permission bekerja. QA selesai berarti ada pemeriksaan nyata, bukan sekadar HTTP 200.

Halaman bertanda **Adaptasi** tidak punya frame khusus yang diverifikasi dalam peta; gunakan shared final components + isi existing. Child create/edit/show tetap wajib, walaupun tidak tampil di sidebar. Endpoint JSON/webhook/redirect bukan page baru.

## Halaman GET aplikasi

| URI | Named route | Fase | Acuan | UI | Fungsi | QA |
| --- | --- | --- | --- | --- | --- | --- |
| `/` | `landing` | P02 | Landing final; responsive + motion | [x] | [x] | [x] |
| `/audit` | `audit.index` | P09 | Adaptasi pola final + fungsi existing | [x] | [x] | [x] |
| `/audit/{auditLog}` | `audit.show` | P09 | Adaptasi pola final + fungsi existing | [x] | [x] | [x] |
| `/brands` | `brands.index` | P07 | Adaptasi pola final + fungsi existing | [x] | [x] | [x] |
| `/brands/create` | `brands.create` | P07 | Adaptasi pola final + fungsi existing | [x] | [x] | [x] |
| `/brands/{brand}/edit` | `brands.edit` | P07 | Adaptasi pola final + fungsi existing | [x] | [x] | [x] |
| `/cart` | `customer.cart` | P03/P04 | Cart sheet final + fallback checkout | [x] | [x] | [x] |
| `/categories` | `categories.index` | P07 | Adaptasi pola final + fungsi existing | [x] | [x] | [x] |
| `/categories/create` | `categories.create` | P07 | Adaptasi pola final + fungsi existing | [x] | [x] | [x] |
| `/categories/{category}/edit` | `categories.edit` | P07 | Adaptasi pola final + fungsi existing | [x] | [x] | [x] |
| `/confirm-password` | `password.confirm` | P02 | Adaptasi pola final + fungsi existing | [x] | [x] | [x] |
| `/customers` | `customers.index` | P07 | Adaptasi pola final + fungsi existing | [x] | [x] | [x] |
| `/customers/create` | `customers.create` | P07 | Adaptasi pola final + fungsi existing | [x] | [x] | [x] |
| `/customers/{customer}` | `customers.show` | P07 | Adaptasi pola final + fungsi existing | [x] | [x] | [x] |
| `/customers/{customer}/edit` | `customers.edit` | P07 | Adaptasi pola final + fungsi existing | [x] | [x] | [x] |
| `/dashboard` | `dashboard` | P06 | Dashboard final | [x] | [x] | [x] |
| `/debt` | `debt.index` | P09 | Adaptasi pola final + fungsi existing | [x] | [x] | [x] |
| `/debt/create` | `debt.create` | P09 | Adaptasi pola final + fungsi existing | [x] | [x] | [x] |
| `/debt/dashboard` | `debt.dashboard` | P09 | Adaptasi pola final + fungsi existing | [x] | [x] | [x] |
| `/forgot-password` | `password.request` | P02 | Adaptasi pola final + fungsi existing | [x] | [x] | [x] |
| `/inventory` | `inventory.index` | P07 | Adaptasi pola final + fungsi existing | [x] | [x] | [x] |
| `/login` | `login` | P02 | Adaptasi pola final + fungsi existing | [x] | [x] | [x] |
| `/my-addresses` | `customer.addresses` | P04 | Address flow final + CRUD existing | [x] | [x] | [x] |
| `/my-orders` | `customer.history` | P05 | Orders final + receipt/reorder overlays | [x] | [x] | [x] |
| `/order-success/{order}` | `customer.order.success` | P05 | Receipt/success pattern final | [x] | [x] | [x] |
| `/orders` | `orders.index` | P09 | Adaptasi pola final + fungsi existing | [x] | [x] | [x] |
| `/orders/monitor` | `orders.monitor` | P09 | Monitor final | [x] | [x] | [x] |
| `/orders/{order}` | `orders.show` | P09 | Adaptasi pola final + fungsi existing | [x] | [x] | [x] |
| `/payments` | `payments.index` | P09 | Adaptasi pola final + fungsi existing | [x] | [x] | [x] |
| `/payments/create` | `payments.create` | P09 | Adaptasi pola final + fungsi existing | [x] | [x] | [x] |
| `/payments/{payment}` | `payments.show` | P09 | Adaptasi pola final + fungsi existing | [x] | [x] | [x] |
| `/pos` | `pos.index` | P08 | POS final + dialogs | [x] | [x] | [x] |
| `/pos/history` | `pos.history` | P08 | Adaptasi pola final + fungsi existing | [x] | [x] | [x] |
| `/pos/receipt/{sale}` | `pos.receipt` | P08 | Adaptasi pola final + fungsi existing | [x] | [x] | [x] |
| `/product-types` | `product-types.index` | P07 | Adaptasi pola final + fungsi existing | [x] | [x] | [x] |
| `/product-types/create` | `product-types.create` | P07 | Adaptasi pola final + fungsi existing | [x] | [x] | [x] |
| `/product-types/{product_type}/edit` | `product-types.edit` | P07 | Adaptasi pola final + fungsi existing | [x] | [x] | [x] |
| `/products` | `products.index` | P07 | Products final | [x] | [x] | [x] |
| `/products/create` | `products.create` | P07 | Form pattern final | [x] | [x] | [x] |
| `/products/{product}` | `products.show` | P07 | Detail pattern final | [x] | [x] | [x] |
| `/products/{product}/edit` | `products.edit` | P07 | Form pattern final | [x] | [x] | [x] |
| `/profile` | `profile.edit` | P02 | Adaptasi pola final + fungsi existing | [x] | [x] | [x] |
| `/refunds` | `refunds.index` | P09 | Adaptasi pola final + fungsi existing | [x] | [x] | [x] |
| `/register` | `register` | P02 | Adaptasi pola final + fungsi existing | [x] | [x] | [x] |
| `/reports` | `reports.index` | P09 | Adaptasi pola final + fungsi existing | [x] | [x] | [x] |
| `/reports/debt` | `reports.debt` | P09 | Adaptasi pola final + fungsi existing | [x] | [x] | [x] |
| `/reports/inventory` | `reports.inventory` | P09 | Adaptasi pola final + fungsi existing | [x] | [x] | [x] |
| `/reports/payments` | `reports.payments` | P09 | Adaptasi pola final + fungsi existing | [x] | [x] | [x] |
| `/reports/sales` | `reports.sales` | P09 | Adaptasi pola final + fungsi existing | [x] | [x] | [x] |
| `/reports/staff` | `reports.staff` | P09 | Adaptasi pola final + fungsi existing | [x] | [x] | [x] |
| `/reset-password/{token}` | `password.reset` | P02 | Adaptasi pola final + fungsi existing | [x] | [x] | [x] |
| `/settings` | `settings.index` | P07 | Adaptasi pola final + fungsi existing | [x] | [x] | [x] |
| `/shelves` | `shelves.index` | P10 | Grid final + product assignment existing | [x] | [x] | [x] |
| `/shifts` | `shifts.index` | P09 | Adaptasi pola final + fungsi existing | [x] | [x] | [x] |
| `/shop` | `customer.shop` | P03 | Katalog final | [x] | [x] | [x] |
| `/shop/{product}` | `customer.product` | P03 | Picker final + direct route fallback | [x] | [x] | [x] |
| `/stock-opnames` | `opnames.index` | P07 | Adaptasi pola final + fungsi existing | [x] | [x] | [x] |
| `/suppliers` | `suppliers.index` | P07 | Adaptasi pola final + fungsi existing | [x] | [x] | [x] |
| `/suppliers/create` | `suppliers.create` | P07 | Adaptasi pola final + fungsi existing | [x] | [x] | [x] |
| `/suppliers/{supplier}/edit` | `suppliers.edit` | P07 | Adaptasi pola final + fungsi existing | [x] | [x] | [x] |
| `/units` | `units.index` | P07 | Adaptasi pola final + fungsi existing | [x] | [x] | [x] |
| `/units/create` | `units.create` | P07 | Adaptasi pola final + fungsi existing | [x] | [x] | [x] |
| `/units/{unit}/edit` | `units.edit` | P07 | Adaptasi pola final + fungsi existing | [x] | [x] | [x] |
| `/users` | `users.index` | P07 | Adaptasi pola final + fungsi existing | [x] | [x] | [x] |
| `/verify-email` | `verification.notice` | P02 | Adaptasi pola final + fungsi existing | [x] | [x] | [x] |

Lihat FINAL-QA untuk408 pemeriksaan render/viewport/theme (324 owner,36 customer,48 guest), pengujian alur dan batas pemeriksaannya.

## GET non-page dan infrastruktur

Uji response/auth/integration berikut melalui flow, bukan redesign sebagai page. Storage route adalah serving file; `/up` adalah healthcheck.

| URI | Peran | Fase | QA |
| --- | --- | --- | --- |
| `/shelf-layout/{shelf}/products` | Live authorized shelf product JSON | P10 | [x] |
| `/checkout/continue` | `customer.checkout.continue` | P04 | Existing guarded contract / regression suite |
| `/my-addresses/data` | `customer.addresses.index` | P04 | Existing guarded contract / regression suite |
| `/pos/barcode` | `pos.barcode` | P08 | Existing guarded contract / regression suite |
| `/pos/customers/{customer}/addresses` | `pos.addresses.index` | P08 | Existing guarded contract / regression suite |
| `/pos/products` | `pos.products` | P08 | Existing guarded contract / regression suite |
| `/storage/{path}` | `storage.local` | P00/P11 | Existing guarded contract / regression suite |
| `/up` | `Closure` | P00/P11 | Existing guarded contract / regression suite |
| `/verify-email/{id}/{hash}` | `verification.verify` | P02 | Existing guarded contract / regression suite |

## Aksi backend yang tidak boleh hilang

Checklist endpoint berarti validasi/permission/error/idempotency yang relevan teruji melalui form/API. Nama path bisa sama untuk metode HTTP berbeda. Jangan membuat semua endpoint sebagai menu sidebar.

| Method | URI | Named route | Fase | Regression |
| --- | --- | --- | --- | --- |
| PUT | `/shelf-layout` | `shelves.layout` | P10 | [x] |
| POST | `/pos/delivery-quote` | `pos.delivery-quote` | P08 | [x] |
| POST | `/_boost/browser-logs` | `boost.browser-logs` | P00/P11 | Existing guarded contract / regression suite |
| POST | `/brands` | `brands.store` | P07 | Existing guarded contract / regression suite |
| PUT / PATCH | `/brands/{brand}` | `brands.update` | P07 | Existing guarded contract / regression suite |
| DELETE | `/brands/{brand}` | `brands.destroy` | P07 | Existing guarded contract / regression suite |
| POST | `/cart/quote` | `customer.cart.quote` | P04 | Existing guarded contract / regression suite |
| POST | `/categories` | `categories.store` | P07 | Existing guarded contract / regression suite |
| PUT / PATCH | `/categories/{category}` | `categories.update` | P07 | Existing guarded contract / regression suite |
| DELETE | `/categories/{category}` | `categories.destroy` | P07 | Existing guarded contract / regression suite |
| POST | `/checkout` | `customer.checkout` | P04 | Existing guarded contract / regression suite |
| POST | `/confirm-password` | `—` | P02 | Existing guarded contract / regression suite |
| POST | `/customers` | `customers.store` | P07 | Existing guarded contract / regression suite |
| PUT / PATCH | `/customers/{customer}` | `customers.update` | P07 | Existing guarded contract / regression suite |
| DELETE | `/customers/{customer}` | `customers.destroy` | P07 | Existing guarded contract / regression suite |
| POST | `/debt` | `debt.store` | P09 | Existing guarded contract / regression suite |
| POST | `/debt/correction` | `debt.correction` | P09 | Existing guarded contract / regression suite |
| POST | `/debt/payment` | `debt.payment` | P09 | Existing guarded contract / regression suite |
| POST | `/email/verification-notification` | `verification.send` | P00/P11 | Existing guarded contract / regression suite |
| POST | `/forgot-password` | `password.email` | P02 | Existing guarded contract / regression suite |
| POST | `/inventory/adjust` | `inventory.adjust` | P07 | Existing guarded contract / regression suite |
| POST | `/inventory/stock-in` | `inventory.stock-in` | P07 | Existing guarded contract / regression suite |
| POST | `/inventory/stock-out` | `inventory.stock-out` | P07 | Existing guarded contract / regression suite |
| POST | `/locale` | `locale.update` | P02 | Existing guarded contract / regression suite |
| POST | `/login` | `—` | P02 | Existing guarded contract / regression suite |
| POST | `/logout` | `logout` | P02 | Existing guarded contract / regression suite |
| POST | `/my-addresses` | `customer.addresses.store` | P04 | Existing guarded contract / regression suite |
| PUT | `/my-addresses/{address}` | `customer.addresses.update` | P04 | Existing guarded contract / regression suite |
| DELETE | `/my-addresses/{address}` | `customer.addresses.destroy` | P04 | Existing guarded contract / regression suite |
| PUT | `/orders/deliveries/{sale}/status` | `orders.delivery-status` | P09 | Existing guarded contract / regression suite |
| POST | `/orders/{order}/payments` | `orders.payment` | P09 | Existing guarded contract / regression suite |
| PUT | `/orders/{order}/status` | `orders.update-status` | P09 | Existing guarded contract / regression suite |
| PUT | `/password` | `password.update` | P02 | Existing guarded contract / regression suite |
| POST | `/payments` | `payments.store` | P09 | Existing guarded contract / regression suite |
| POST | `/payments/webhook` | `payments.webhook` | P09 | Existing guarded contract / regression suite |
| POST | `/payments/{payment}/confirm` | `payments.confirm` | P09 | Existing guarded contract / regression suite |
| POST | `/payments/{payment}/refund` | `payments.refund` | P09 | Existing guarded contract / regression suite |
| POST | `/pos/close-shift` | `pos.close-shift` | P08 | Existing guarded contract / regression suite |
| POST | `/pos/customers` | `pos.customers` | P08 | Existing guarded contract / regression suite |
| POST | `/pos/customers/{customer}/addresses` | `pos.addresses.store` | P08 | Existing guarded contract / regression suite |
| POST | `/pos/open-shift` | `pos.open-shift` | P08 | Existing guarded contract / regression suite |
| POST | `/pos/process-sale` | `pos.process-sale` | P08 | Existing guarded contract / regression suite |
| POST | `/product-types` | `product-types.store` | P07 | Existing guarded contract / regression suite |
| PUT / PATCH | `/product-types/{product_type}` | `product-types.update` | P07 | Existing guarded contract / regression suite |
| DELETE | `/product-types/{product_type}` | `product-types.destroy` | P07 | Existing guarded contract / regression suite |
| POST | `/products` | `products.store` | P07 | Existing guarded contract / regression suite |
| PUT / PATCH | `/products/{product}` | `products.update` | P07 | Existing guarded contract / regression suite |
| DELETE | `/products/{product}` | `products.destroy` | P07 | Existing guarded contract / regression suite |
| PATCH | `/profile` | `profile.update` | P02 | Existing guarded contract / regression suite |
| DELETE | `/profile` | `profile.destroy` | P02 | Existing guarded contract / regression suite |
| POST | `/refunds` | `refunds.store` | P09 | Existing guarded contract / regression suite |
| POST | `/register` | `—` | P02 | Existing guarded contract / regression suite |
| POST | `/reset-password` | `password.store` | P02 | Existing guarded contract / regression suite |
| PUT | `/roles/{role}` | `roles.update` | P07 | Existing guarded contract / regression suite |
| PUT | `/settings` | `settings.update` | P07 | Existing guarded contract / regression suite |
| POST | `/shelves` | `shelves.store` | P10 | Existing guarded contract / regression suite |
| PUT | `/shelves/products/{product}/location` | `shelves.place` | P10 | Existing guarded contract / regression suite |
| PUT / PATCH | `/shelves/{shelf}` | `shelves.update` | P10 | Existing guarded contract / regression suite |
| DELETE | `/shelves/{shelf}` | `shelves.destroy` | P10 | Existing guarded contract / regression suite |
| POST | `/stock-opnames` | `opnames.store` | P07 | Existing guarded contract / regression suite |
| POST | `/stock-opnames/{opname}/approve` | `opnames.approve` | P07 | Existing guarded contract / regression suite |
| PUT | `/storage/{path}` | `storage.local.upload` | P00/P11 | Existing guarded contract / regression suite |
| POST | `/suppliers` | `suppliers.store` | P07 | Existing guarded contract / regression suite |
| PUT / PATCH | `/suppliers/{supplier}` | `suppliers.update` | P07 | Existing guarded contract / regression suite |
| DELETE | `/suppliers/{supplier}` | `suppliers.destroy` | P07 | Existing guarded contract / regression suite |
| POST | `/units` | `units.store` | P07 | Existing guarded contract / regression suite |
| PUT / PATCH | `/units/{unit}` | `units.update` | P07 | Existing guarded contract / regression suite |
| DELETE | `/units/{unit}` | `units.destroy` | P07 | Existing guarded contract / regression suite |
| POST | `/users` | `users.store` | P07 | Existing guarded contract / regression suite |
| PUT | `/users/{user}` | `users.update` | P07 | Existing guarded contract / regression suite |

## Inventaris Blade

Periksa seluruh file berikut. Partial/component bukan halaman mandiri, tetapi setiap consumer harus tetap berfungsi. `welcome.blade.php` mungkin tidak terpakai: buktikan referensi sebelum tandai N/A; jangan menghapusnya hanya berdasarkan nama. View baru ikut ditambahkan. Source controller lebih berwenang daripada menebak nama file untuk suatu route.

| View relatif terhadap resources/views | Review/refactor selesai |
| --- | --- |
| `audit/index.blade.php` | [x] |
| `audit/show.blade.php` | [x] |
| `auth/confirm-password.blade.php` | [x] |
| `auth/forgot-password.blade.php` | [x] |
| `auth/login.blade.php` | [x] |
| `auth/register.blade.php` | [x] |
| `auth/reset-password.blade.php` | [x] |
| `auth/verify-email.blade.php` | [x] |
| `brands/create.blade.php` | [x] |
| `brands/edit.blade.php` | [x] |
| `brands/index.blade.php` | [x] |
| `categories/create.blade.php` | [x] |
| `categories/edit.blade.php` | [x] |
| `categories/index.blade.php` | [x] |
| `components/account-menu.blade.php` | [x] |
| `components/address-book.blade.php` | [x] |
| `components/address-selector.blade.php` | [x] |
| `components/application-logo.blade.php` | [x] |
| `components/auth-layout.blade.php` | [x] |
| `components/auth-session-status.blade.php` | [x] |
| `components/barcode-camera.blade.php` | [x] |
| `components/barcode-scan-actions.blade.php` | [x] |
| `components/crud-action.blade.php` | [x] |
| `components/danger-button.blade.php` | [x] |
| `components/delivery-map-dialog.blade.php` | [x] |
| `components/delivery-map-link.blade.php` | [x] |
| `components/delivery-point.blade.php` | [x] |
| `components/dropdown-link.blade.php` | [x] |
| `components/dropdown.blade.php` | [x] |
| `components/feedback.blade.php` | [x] |
| `components/icon.blade.php` | [x] |
| `components/input-error.blade.php` | [x] |
| `components/input-label.blade.php` | [x] |
| `components/locale-switcher.blade.php` | [x] |
| `components/modal.blade.php` | [x] |
| `components/nav-link.blade.php` | [x] |
| `components/password-field.blade.php` | [x] |
| `components/primary-button.blade.php` | [x] |
| `components/product-location.blade.php` | [x] |
| `components/responsive-nav-link.blade.php` | [x] |
| `components/secondary-button.blade.php` | [x] |
| `components/shopping-steps.blade.php` | [x] |
| `components/text-input.blade.php` | [x] |
| `components/transaction-status.blade.php` | [x] |
| `components/workspace-navigation.blade.php` | [x] |
| `customer/addresses.blade.php` | [x] |
| `customer/cart.blade.php` | [x] |
| `customer/history.blade.php` | [x] |
| `customer/order-success.blade.php` | [x] |
| `customer/product.blade.php` | [x] |
| `customer/shop.blade.php` | [x] |
| `customers/create.blade.php` | [x] |
| `customers/edit.blade.php` | [x] |
| `customers/index.blade.php` | [x] |
| `customers/show.blade.php` | [x] |
| `dashboard.blade.php` | [x] |
| `debt/create.blade.php` | [x] |
| `debt/index.blade.php` | [x] |
| `inventory/index.blade.php` | [x] |
| `landing.blade.php` | [x] |
| `layouts/app.blade.php` | [x] |
| `layouts/customer.blade.php` | [x] |
| `layouts/guest.blade.php` | [x] |
| `layouts/navigation.blade.php` | [x] |
| `management/settings.blade.php` | [x] |
| `management/users.blade.php` | [x] |
| `operations/opnames.blade.php` | [x] |
| `operations/refunds.blade.php` | [x] |
| `operations/shift-table.blade.php` | [x] |
| `operations/shifts.blade.php` | [x] |
| `orders/index.blade.php` | [x] |
| `orders/monitor.blade.php` | [x] |
| `orders/show.blade.php` | [x] |
| `payments/create.blade.php` | [x] |
| `payments/index.blade.php` | [x] |
| `payments/show.blade.php` | [x] |
| `pos/history.blade.php` | [x] |
| `pos/index.blade.php` | [x] |
| `pos/receipt.blade.php` | [x] |
| `product-types/create.blade.php` | [x] |
| `product-types/edit.blade.php` | [x] |
| `product-types/index.blade.php` | [x] |
| `products/create.blade.php` | [x] |
| `products/edit.blade.php` | [x] |
| `products/index.blade.php` | [x] |
| `products/show.blade.php` | [x] |
| `profile/edit.blade.php` | [x] |
| `profile/partials/delete-user-form.blade.php` | [x] |
| `profile/partials/update-password-form.blade.php` | [x] |
| `profile/partials/update-profile-information-form.blade.php` | [x] |
| `reports/debt.blade.php` | [x] |
| `reports/index.blade.php` | [x] |
| `reports/inventory.blade.php` | [x] |
| `reports/payments.blade.php` | [x] |
| `reports/sales.blade.php` | [x] |
| `reports/staff.blade.php` | [x] |
| `shelves/index.blade.php` | [x] |
| `suppliers/create.blade.php` | [x] |
| `suppliers/edit.blade.php` | [x] |
| `suppliers/index.blade.php` | [x] |
| `units/create.blade.php` | [x] |
| `units/index.blade.php` | [x] |
| `welcome.blade.php` | [x] |
| `components/theme-init.blade.php` | [x] |
| `components/theme-toggle.blade.php` | [x] |
| `components/store-overlays.blade.php` | [x] |
| `components/shelf-grid.blade.php` | [x] |

107 Blade files inventoried. `welcome.blade.php` and older navigation templates remain compatibility/legacy source; no new UI route was invented for unused views. Shared partial coverage follows their consuming routes.

## Kondisi lintas halaman

- [x] Guest/login/logout, role permissions dan own-resource isolation.
- [x] Loading/empty/error/success/validation/disabled dan unknown status.
- [x] 403/404/419/422/500: halaman atau response existing ditangani; identitas/error page mengikuti shell bila perlu, jangan mengekspos stack trace. Jangan membuat route khusus hanya untuk memamerkan error.
- [x] Long content, missing image, currency besar, ID/EN, theme, narrow/wide layouts.
- [x] Modal/dropdown/sheet focus dan scroll; pagination/filter/back/refresh/direct links.
- [x] Semua 29 sidebar destinations sesuai sumber CMS; action/JSON route tidak disisipkan sebagai menu.
- [x] Tidak ada route/page baru setelah baseline yang luput dari inventaris ini.
