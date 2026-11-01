# Cakupan semua halaman, views dan endpoints

[Indeks](README.md). Snapshot source 1 November 2026: `php artisan route:list --json`, **141 route entries**. Inventaris ini perlu diperbarui bila source berubah. Kolom UI/Fungsi/QA adalah status implementasi **belum selesai**, bukan hasil audit desain.

## Cara menutup coverage

Untuk setiap halaman, isi tiga checklist dan tambahkan evidence/commit pada STATUS atau laporan fase. UI selesai berarti light/dark dan responsive telah ditinjau. Fungsi selesai berarti form/actions/data/permission bekerja. QA selesai berarti ada pemeriksaan nyata, bukan sekadar HTTP 200.

Halaman bertanda **Adaptasi** tidak punya frame khusus yang diverifikasi dalam peta; gunakan shared final components + isi existing. Child create/edit/show tetap wajib, walaupun tidak tampil di sidebar. Endpoint JSON/webhook/redirect bukan page baru.

## Halaman GET aplikasi

| URI | Named route | Fase | Acuan | UI | Fungsi | QA |
| --- | --- | --- | --- | --- | --- | --- |
| `/` | `landing` | P02 | Landing final; responsive + motion | [ ] | [ ] | [ ] |
| `/audit` | `audit.index` | P09 | Adaptasi pola final + fungsi existing | [ ] | [ ] | [ ] |
| `/audit/{auditLog}` | `audit.show` | P09 | Adaptasi pola final + fungsi existing | [ ] | [ ] | [ ] |
| `/brands` | `brands.index` | P07 | Adaptasi pola final + fungsi existing | [ ] | [ ] | [ ] |
| `/brands/create` | `brands.create` | P07 | Adaptasi pola final + fungsi existing | [ ] | [ ] | [ ] |
| `/brands/{brand}/edit` | `brands.edit` | P07 | Adaptasi pola final + fungsi existing | [ ] | [ ] | [ ] |
| `/cart` | `customer.cart` | P03/P04 | Cart sheet final + fallback checkout | [ ] | [ ] | [ ] |
| `/categories` | `categories.index` | P07 | Adaptasi pola final + fungsi existing | [ ] | [ ] | [ ] |
| `/categories/create` | `categories.create` | P07 | Adaptasi pola final + fungsi existing | [ ] | [ ] | [ ] |
| `/categories/{category}/edit` | `categories.edit` | P07 | Adaptasi pola final + fungsi existing | [ ] | [ ] | [ ] |
| `/confirm-password` | `password.confirm` | P02 | Adaptasi pola final + fungsi existing | [ ] | [ ] | [ ] |
| `/customers` | `customers.index` | P07 | Adaptasi pola final + fungsi existing | [ ] | [ ] | [ ] |
| `/customers/create` | `customers.create` | P07 | Adaptasi pola final + fungsi existing | [ ] | [ ] | [ ] |
| `/customers/{customer}` | `customers.show` | P07 | Adaptasi pola final + fungsi existing | [ ] | [ ] | [ ] |
| `/customers/{customer}/edit` | `customers.edit` | P07 | Adaptasi pola final + fungsi existing | [ ] | [ ] | [ ] |
| `/dashboard` | `dashboard` | P06 | Dashboard final | [ ] | [ ] | [ ] |
| `/debt` | `debt.index` | P09 | Adaptasi pola final + fungsi existing | [ ] | [ ] | [ ] |
| `/debt/create` | `debt.create` | P09 | Adaptasi pola final + fungsi existing | [ ] | [ ] | [ ] |
| `/debt/dashboard` | `debt.dashboard` | P09 | Adaptasi pola final + fungsi existing | [ ] | [ ] | [ ] |
| `/forgot-password` | `password.request` | P02 | Adaptasi pola final + fungsi existing | [ ] | [ ] | [ ] |
| `/inventory` | `inventory.index` | P07 | Adaptasi pola final + fungsi existing | [ ] | [ ] | [ ] |
| `/login` | `login` | P02 | Adaptasi pola final + fungsi existing | [ ] | [ ] | [ ] |
| `/my-addresses` | `customer.addresses` | P04 | Address flow final + CRUD existing | [ ] | [ ] | [ ] |
| `/my-orders` | `customer.history` | P05 | Orders final + receipt/reorder overlays | [ ] | [ ] | [ ] |
| `/order-success/{order}` | `customer.order.success` | P05 | Receipt/success pattern final | [ ] | [ ] | [ ] |
| `/orders` | `orders.index` | P09 | Adaptasi pola final + fungsi existing | [ ] | [ ] | [ ] |
| `/orders/monitor` | `orders.monitor` | P09 | Monitor final | [ ] | [ ] | [ ] |
| `/orders/{order}` | `orders.show` | P09 | Adaptasi pola final + fungsi existing | [ ] | [ ] | [ ] |
| `/payments` | `payments.index` | P09 | Adaptasi pola final + fungsi existing | [ ] | [ ] | [ ] |
| `/payments/create` | `payments.create` | P09 | Adaptasi pola final + fungsi existing | [ ] | [ ] | [ ] |
| `/payments/{payment}` | `payments.show` | P09 | Adaptasi pola final + fungsi existing | [ ] | [ ] | [ ] |
| `/pos` | `pos.index` | P08 | POS final + dialogs | [ ] | [ ] | [ ] |
| `/pos/history` | `pos.history` | P08 | Adaptasi pola final + fungsi existing | [ ] | [ ] | [ ] |
| `/pos/receipt/{sale}` | `pos.receipt` | P08 | Adaptasi pola final + fungsi existing | [ ] | [ ] | [ ] |
| `/product-types` | `product-types.index` | P07 | Adaptasi pola final + fungsi existing | [ ] | [ ] | [ ] |
| `/product-types/create` | `product-types.create` | P07 | Adaptasi pola final + fungsi existing | [ ] | [ ] | [ ] |
| `/product-types/{product_type}/edit` | `product-types.edit` | P07 | Adaptasi pola final + fungsi existing | [ ] | [ ] | [ ] |
| `/products` | `products.index` | P07 | Products final | [ ] | [ ] | [ ] |
| `/products/create` | `products.create` | P07 | Form pattern final | [ ] | [ ] | [ ] |
| `/products/{product}` | `products.show` | P07 | Detail pattern final | [ ] | [ ] | [ ] |
| `/products/{product}/edit` | `products.edit` | P07 | Form pattern final | [ ] | [ ] | [ ] |
| `/profile` | `profile.edit` | P02 | Adaptasi pola final + fungsi existing | [ ] | [ ] | [ ] |
| `/refunds` | `refunds.index` | P09 | Adaptasi pola final + fungsi existing | [ ] | [ ] | [ ] |
| `/register` | `register` | P02 | Adaptasi pola final + fungsi existing | [ ] | [ ] | [ ] |
| `/reports` | `reports.index` | P09 | Adaptasi pola final + fungsi existing | [ ] | [ ] | [ ] |
| `/reports/debt` | `reports.debt` | P09 | Adaptasi pola final + fungsi existing | [ ] | [ ] | [ ] |
| `/reports/inventory` | `reports.inventory` | P09 | Adaptasi pola final + fungsi existing | [ ] | [ ] | [ ] |
| `/reports/payments` | `reports.payments` | P09 | Adaptasi pola final + fungsi existing | [ ] | [ ] | [ ] |
| `/reports/sales` | `reports.sales` | P09 | Adaptasi pola final + fungsi existing | [ ] | [ ] | [ ] |
| `/reports/staff` | `reports.staff` | P09 | Adaptasi pola final + fungsi existing | [ ] | [ ] | [ ] |
| `/reset-password/{token}` | `password.reset` | P02 | Adaptasi pola final + fungsi existing | [ ] | [ ] | [ ] |
| `/settings` | `settings.index` | P07 | Adaptasi pola final + fungsi existing | [ ] | [ ] | [ ] |
| `/shelves` | `shelves.index` | P10 | Grid final + product assignment existing | [ ] | [ ] | [ ] |
| `/shifts` | `shifts.index` | P09 | Adaptasi pola final + fungsi existing | [ ] | [ ] | [ ] |
| `/shop` | `customer.shop` | P03 | Katalog final | [ ] | [ ] | [ ] |
| `/shop/{product}` | `customer.product` | P03 | Picker final + direct route fallback | [ ] | [ ] | [ ] |
| `/stock-opnames` | `opnames.index` | P07 | Adaptasi pola final + fungsi existing | [ ] | [ ] | [ ] |
| `/suppliers` | `suppliers.index` | P07 | Adaptasi pola final + fungsi existing | [ ] | [ ] | [ ] |
| `/suppliers/create` | `suppliers.create` | P07 | Adaptasi pola final + fungsi existing | [ ] | [ ] | [ ] |
| `/suppliers/{supplier}/edit` | `suppliers.edit` | P07 | Adaptasi pola final + fungsi existing | [ ] | [ ] | [ ] |
| `/units` | `units.index` | P07 | Adaptasi pola final + fungsi existing | [ ] | [ ] | [ ] |
| `/units/create` | `units.create` | P07 | Adaptasi pola final + fungsi existing | [ ] | [ ] | [ ] |
| `/units/{unit}/edit` | `units.edit` | P07 | Adaptasi pola final + fungsi existing | [ ] | [ ] | [ ] |
| `/users` | `users.index` | P07 | Adaptasi pola final + fungsi existing | [ ] | [ ] | [ ] |
| `/verify-email` | `verification.notice` | P02 | Adaptasi pola final + fungsi existing | [ ] | [ ] | [ ] |

## GET non-page dan infrastruktur

Uji response/auth/integration berikut melalui flow, bukan redesign sebagai page. Storage route adalah serving file; `/up` adalah healthcheck.

| URI | Peran | Fase | QA |
| --- | --- | --- | --- |
| `/checkout/continue` | `customer.checkout.continue` | P04 | [ ] |
| `/my-addresses/data` | `customer.addresses.index` | P04 | [ ] |
| `/pos/barcode` | `pos.barcode` | P08 | [ ] |
| `/pos/customers/{customer}/addresses` | `pos.addresses.index` | P08 | [ ] |
| `/pos/products` | `pos.products` | P08 | [ ] |
| `/storage/{path}` | `storage.local` | P00/P11 | [ ] |
| `/up` | `Closure` | P00/P11 | [ ] |
| `/verify-email/{id}/{hash}` | `verification.verify` | P02 | [ ] |

## Aksi backend yang tidak boleh hilang

Checklist endpoint berarti validasi/permission/error/idempotency yang relevan teruji melalui form/API. Nama path bisa sama untuk metode HTTP berbeda. Jangan membuat semua endpoint sebagai menu sidebar.

| Method | URI | Named route | Fase | Regression |
| --- | --- | --- | --- | --- |
| POST | `/_boost/browser-logs` | `boost.browser-logs` | P00/P11 | [ ] |
| POST | `/brands` | `brands.store` | P07 | [ ] |
| PUT / PATCH | `/brands/{brand}` | `brands.update` | P07 | [ ] |
| DELETE | `/brands/{brand}` | `brands.destroy` | P07 | [ ] |
| POST | `/cart/quote` | `customer.cart.quote` | P04 | [ ] |
| POST | `/categories` | `categories.store` | P07 | [ ] |
| PUT / PATCH | `/categories/{category}` | `categories.update` | P07 | [ ] |
| DELETE | `/categories/{category}` | `categories.destroy` | P07 | [ ] |
| POST | `/checkout` | `customer.checkout` | P04 | [ ] |
| POST | `/confirm-password` | `—` | P02 | [ ] |
| POST | `/customers` | `customers.store` | P07 | [ ] |
| PUT / PATCH | `/customers/{customer}` | `customers.update` | P07 | [ ] |
| DELETE | `/customers/{customer}` | `customers.destroy` | P07 | [ ] |
| POST | `/debt` | `debt.store` | P09 | [ ] |
| POST | `/debt/correction` | `debt.correction` | P09 | [ ] |
| POST | `/debt/payment` | `debt.payment` | P09 | [ ] |
| POST | `/email/verification-notification` | `verification.send` | P00/P11 | [ ] |
| POST | `/forgot-password` | `password.email` | P02 | [ ] |
| POST | `/inventory/adjust` | `inventory.adjust` | P07 | [ ] |
| POST | `/inventory/stock-in` | `inventory.stock-in` | P07 | [ ] |
| POST | `/inventory/stock-out` | `inventory.stock-out` | P07 | [ ] |
| POST | `/locale` | `locale.update` | P02 | [ ] |
| POST | `/login` | `—` | P02 | [ ] |
| POST | `/logout` | `logout` | P02 | [ ] |
| POST | `/my-addresses` | `customer.addresses.store` | P04 | [ ] |
| PUT | `/my-addresses/{address}` | `customer.addresses.update` | P04 | [ ] |
| DELETE | `/my-addresses/{address}` | `customer.addresses.destroy` | P04 | [ ] |
| PUT | `/orders/deliveries/{sale}/status` | `orders.delivery-status` | P09 | [ ] |
| POST | `/orders/{order}/payments` | `orders.payment` | P09 | [ ] |
| PUT | `/orders/{order}/status` | `orders.update-status` | P09 | [ ] |
| PUT | `/password` | `password.update` | P02 | [ ] |
| POST | `/payments` | `payments.store` | P09 | [ ] |
| POST | `/payments/webhook` | `payments.webhook` | P09 | [ ] |
| POST | `/payments/{payment}/confirm` | `payments.confirm` | P09 | [ ] |
| POST | `/payments/{payment}/refund` | `payments.refund` | P09 | [ ] |
| POST | `/pos/close-shift` | `pos.close-shift` | P08 | [ ] |
| POST | `/pos/customers` | `pos.customers` | P08 | [ ] |
| POST | `/pos/customers/{customer}/addresses` | `pos.addresses.store` | P08 | [ ] |
| POST | `/pos/open-shift` | `pos.open-shift` | P08 | [ ] |
| POST | `/pos/process-sale` | `pos.process-sale` | P08 | [ ] |
| POST | `/product-types` | `product-types.store` | P07 | [ ] |
| PUT / PATCH | `/product-types/{product_type}` | `product-types.update` | P07 | [ ] |
| DELETE | `/product-types/{product_type}` | `product-types.destroy` | P07 | [ ] |
| POST | `/products` | `products.store` | P07 | [ ] |
| PUT / PATCH | `/products/{product}` | `products.update` | P07 | [ ] |
| DELETE | `/products/{product}` | `products.destroy` | P07 | [ ] |
| PATCH | `/profile` | `profile.update` | P02 | [ ] |
| DELETE | `/profile` | `profile.destroy` | P02 | [ ] |
| POST | `/refunds` | `refunds.store` | P09 | [ ] |
| POST | `/register` | `—` | P02 | [ ] |
| POST | `/reset-password` | `password.store` | P02 | [ ] |
| PUT | `/roles/{role}` | `roles.update` | P07 | [ ] |
| PUT | `/settings` | `settings.update` | P07 | [ ] |
| POST | `/shelves` | `shelves.store` | P10 | [ ] |
| PUT | `/shelves/products/{product}/location` | `shelves.place` | P10 | [ ] |
| PUT / PATCH | `/shelves/{shelf}` | `shelves.update` | P10 | [ ] |
| DELETE | `/shelves/{shelf}` | `shelves.destroy` | P10 | [ ] |
| POST | `/stock-opnames` | `opnames.store` | P07 | [ ] |
| POST | `/stock-opnames/{opname}/approve` | `opnames.approve` | P07 | [ ] |
| PUT | `/storage/{path}` | `storage.local.upload` | P00/P11 | [ ] |
| POST | `/suppliers` | `suppliers.store` | P07 | [ ] |
| PUT / PATCH | `/suppliers/{supplier}` | `suppliers.update` | P07 | [ ] |
| DELETE | `/suppliers/{supplier}` | `suppliers.destroy` | P07 | [ ] |
| POST | `/units` | `units.store` | P07 | [ ] |
| PUT / PATCH | `/units/{unit}` | `units.update` | P07 | [ ] |
| DELETE | `/units/{unit}` | `units.destroy` | P07 | [ ] |
| POST | `/users` | `users.store` | P07 | [ ] |
| PUT | `/users/{user}` | `users.update` | P07 | [ ] |

## Inventaris Blade

Periksa seluruh file berikut. Partial/component bukan halaman mandiri, tetapi setiap consumer harus tetap berfungsi. `welcome.blade.php` mungkin tidak terpakai: buktikan referensi sebelum tandai N/A; jangan menghapusnya hanya berdasarkan nama. View baru ikut ditambahkan. Source controller lebih berwenang daripada menebak nama file untuk suatu route.

| View relatif terhadap resources/views | Review/refactor selesai |
| --- | --- |
| `audit/index.blade.php` | [ ] |
| `audit/show.blade.php` | [ ] |
| `auth/confirm-password.blade.php` | [ ] |
| `auth/forgot-password.blade.php` | [ ] |
| `auth/login.blade.php` | [ ] |
| `auth/register.blade.php` | [ ] |
| `auth/reset-password.blade.php` | [ ] |
| `auth/verify-email.blade.php` | [ ] |
| `brands/create.blade.php` | [ ] |
| `brands/edit.blade.php` | [ ] |
| `brands/index.blade.php` | [ ] |
| `categories/create.blade.php` | [ ] |
| `categories/edit.blade.php` | [ ] |
| `categories/index.blade.php` | [ ] |
| `components/account-menu.blade.php` | [ ] |
| `components/address-book.blade.php` | [ ] |
| `components/address-selector.blade.php` | [ ] |
| `components/application-logo.blade.php` | [ ] |
| `components/auth-layout.blade.php` | [ ] |
| `components/auth-session-status.blade.php` | [ ] |
| `components/barcode-camera.blade.php` | [ ] |
| `components/barcode-scan-actions.blade.php` | [ ] |
| `components/crud-action.blade.php` | [ ] |
| `components/danger-button.blade.php` | [ ] |
| `components/delivery-map-dialog.blade.php` | [ ] |
| `components/delivery-map-link.blade.php` | [ ] |
| `components/delivery-point.blade.php` | [ ] |
| `components/dropdown-link.blade.php` | [ ] |
| `components/dropdown.blade.php` | [ ] |
| `components/feedback.blade.php` | [ ] |
| `components/icon.blade.php` | [ ] |
| `components/input-error.blade.php` | [ ] |
| `components/input-label.blade.php` | [ ] |
| `components/locale-switcher.blade.php` | [ ] |
| `components/modal.blade.php` | [ ] |
| `components/nav-link.blade.php` | [ ] |
| `components/password-field.blade.php` | [ ] |
| `components/primary-button.blade.php` | [ ] |
| `components/product-location.blade.php` | [ ] |
| `components/responsive-nav-link.blade.php` | [ ] |
| `components/secondary-button.blade.php` | [ ] |
| `components/shopping-steps.blade.php` | [ ] |
| `components/text-input.blade.php` | [ ] |
| `components/transaction-status.blade.php` | [ ] |
| `components/workspace-navigation.blade.php` | [ ] |
| `customer/addresses.blade.php` | [ ] |
| `customer/cart.blade.php` | [ ] |
| `customer/history.blade.php` | [ ] |
| `customer/order-success.blade.php` | [ ] |
| `customer/product.blade.php` | [ ] |
| `customer/shop.blade.php` | [ ] |
| `customers/create.blade.php` | [ ] |
| `customers/edit.blade.php` | [ ] |
| `customers/index.blade.php` | [ ] |
| `customers/show.blade.php` | [ ] |
| `dashboard.blade.php` | [ ] |
| `debt/create.blade.php` | [ ] |
| `debt/index.blade.php` | [ ] |
| `inventory/index.blade.php` | [ ] |
| `landing.blade.php` | [ ] |
| `layouts/app.blade.php` | [ ] |
| `layouts/customer.blade.php` | [ ] |
| `layouts/guest.blade.php` | [ ] |
| `layouts/navigation.blade.php` | [ ] |
| `management/settings.blade.php` | [ ] |
| `management/users.blade.php` | [ ] |
| `operations/opnames.blade.php` | [ ] |
| `operations/refunds.blade.php` | [ ] |
| `operations/shift-table.blade.php` | [ ] |
| `operations/shifts.blade.php` | [ ] |
| `orders/index.blade.php` | [ ] |
| `orders/monitor.blade.php` | [ ] |
| `orders/show.blade.php` | [ ] |
| `payments/create.blade.php` | [ ] |
| `payments/index.blade.php` | [ ] |
| `payments/show.blade.php` | [ ] |
| `pos/history.blade.php` | [ ] |
| `pos/index.blade.php` | [ ] |
| `pos/receipt.blade.php` | [ ] |
| `product-types/create.blade.php` | [ ] |
| `product-types/edit.blade.php` | [ ] |
| `product-types/index.blade.php` | [ ] |
| `products/create.blade.php` | [ ] |
| `products/edit.blade.php` | [ ] |
| `products/index.blade.php` | [ ] |
| `products/show.blade.php` | [ ] |
| `profile/edit.blade.php` | [ ] |
| `profile/partials/delete-user-form.blade.php` | [ ] |
| `profile/partials/update-password-form.blade.php` | [ ] |
| `profile/partials/update-profile-information-form.blade.php` | [ ] |
| `reports/debt.blade.php` | [ ] |
| `reports/index.blade.php` | [ ] |
| `reports/inventory.blade.php` | [ ] |
| `reports/payments.blade.php` | [ ] |
| `reports/sales.blade.php` | [ ] |
| `reports/staff.blade.php` | [ ] |
| `shelves/index.blade.php` | [ ] |
| `suppliers/create.blade.php` | [ ] |
| `suppliers/edit.blade.php` | [ ] |
| `suppliers/index.blade.php` | [ ] |
| `units/create.blade.php` | [ ] |
| `units/index.blade.php` | [ ] |
| `welcome.blade.php` | [ ] |

## Kondisi lintas halaman

- [ ] Guest/login/logout, role permissions dan own-resource isolation.
- [ ] Loading/empty/error/success/validation/disabled dan unknown status.
- [ ] 403/404/419/422/500: halaman atau response existing ditangani; identitas/error page mengikuti shell bila perlu, jangan mengekspos stack trace. Jangan membuat route khusus hanya untuk memamerkan error.
- [ ] Long content, missing image, currency besar, ID/EN, theme, narrow/wide layouts.
- [ ] Modal/dropdown/sheet focus dan scroll; pagination/filter/back/refresh/direct links.
- [ ] Semua 29 sidebar destinations sesuai sumber CMS; action/JSON route tidak disisipkan sebagai menu.
- [ ] Tidak ada route/page baru setelah baseline yang luput dari inventaris ini.
