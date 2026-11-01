# CMS workspace layout revamp

> Only [selected previews](../../PREVIEWS.md) are retained in Git. Export filenames below describe the original review run; bulk PNG/PDF evidence can be regenerated from the final native document.

Latest pass: [CMS operational redesign](../cms-operations/README.md) adds Dashboard, POS, product placement and Monitor on pages 18–19, and replaces the navbar/account dropdown on these earlier Products shells. Eight main PNGs and the layout PDF were refreshed. The earlier scope/runtime statements below describe the initial shell pass; current navigation for the four modules now opens native design frames, and local Dashboard/Monitor were inspected in the subsequent pass.

1 November 2026. Same native document: `../final.figcraft`. Pages **16 · CMS workspace · Layout & components** and **17 · CMS navigation · Route coverage**. Design/prototype scope only; no Laravel or database changes.

## Scope and decisions

Representative content is the Products list, first-row product details/edit, add form with validation, archive confirmation, filter controls and account/navigation overlays. Other pages remain represented in the complete sidebar, as requested. No dashboard metrics/charts or new admin features were added.

Two working layout structures are drawn: expanded 264 px sidebar (default) and compact 88 px rail. Expanded mode keeps all menu names visible while scrolling; compact mode offers more table space. Tablet uses a navigation drawer with a horizontally scrollable full table; mobile uses product cards retaining SKU/category, purchase/selling price, stock, status and actions. Sidebar and page content have independent scroll regions.

Typography follows Warbun: Georgia headings, System Font body/controls/data, existing lowercase wordmark. Burgundy primary, teal focus/secondary and light/dark surfaces. Local Font Awesome SVG icons reused. Sidebar navigation, buttons, inputs and feedback are native reusable components; `Warbun/CMS/Heading` and `Warbun/CMS/Body` are scoped text styles.

## Preview frames

| Layout | Light | Dark |
| --- | --- | --- |
| Desktop 1440×1000 | 20805 | 21053 |
| Tablet 834×1112 | 21315 | 21470 |
| Mobile 390×844 | 21625 | 21715 |
| Compact desktop 1440×1000 | 21805 | 21985 |
| Component library | 23565 | 23620 |
| Full sidebar navigation master | 20601 | 20703 |

Eight prototype flows are named `CMS workspace · <layout>-<theme>`. PNG previews use descriptive layout names; other frames use `frame-<id>.png`. `cms-layouts.pdf` contains the eight layouts. Form, dialog and drawer contact sheets are `review-*.png`.

## Navigation coverage

All **23 entries from the existing navigation source** are present, plus **five report subpages and the Debt Dashboard**, totaling **29 sidebar destinations**. Profile is in the account menu. Source: `resources/views/components/workspace-navigation.blade.php`, `routes/web.php`, `layouts/app.blade.php`, `components/account-menu.blade.php`, and report/product/settings views.

| Group | Menu | Path | Named route |
| --- | --- | --- | --- |
| Ringkasan | Dashboard | `/dashboard` | `dashboard` |
| Operasional | POS / Kasir | `/pos` | `pos.index` |
| Operasional | Riwayat penjualan | `/pos/history` | `pos.history` |
| Operasional | Monitoring pesanan | `/orders/monitor` | `orders.monitor` |
| Operasional | Pesanan | `/orders` | `orders.index` |
| Operasional | Pembayaran | `/payments` | `payments.index` |
| Operasional | Retur & refund | `/refunds` | `refunds.index` |
| Operasional | Shift kasir | `/shifts` | `shifts.index` |
| Katalog & stok | Produk | `/products` | `products.index` |
| Katalog & stok | Penataan produk | `/shelves` | `shelves.index` |
| Katalog & stok | Persediaan | `/inventory` | `inventory.index` |
| Katalog & stok | Stok opname | `/stock-opnames` | `opnames.index` |
| Master data | Kategori | `/categories` | `categories.index` |
| Master data | Tipe produk | `/product-types` | `product-types.index` |
| Master data | Merek | `/brands` | `brands.index` |
| Master data | Satuan | `/units` | `units.index` |
| Master data | Pemasok | `/suppliers` | `suppliers.index` |
| Pelanggan | Pelanggan | `/customers` | `customers.index` |
| Pelanggan | Piutang | `/debt` | `debt.index` |
| Pelanggan | Ringkasan piutang | `/debt/dashboard` | `debt.dashboard` |
| Laporan | Semua laporan | `/reports` | `reports.index` |
| Laporan | Penjualan | `/reports/sales` | `reports.sales` |
| Laporan | Persediaan | `/reports/inventory` | `reports.inventory` |
| Laporan | Pembayaran | `/reports/payments` | `reports.payments` |
| Laporan | Staf | `/reports/staff` | `reports.staff` |
| Laporan | Piutang | `/reports/debt` | `reports.debt` |
| Manajemen | Pengguna & peran | `/users` | `users.index` |
| Manajemen | Pengaturan toko | `/settings` | `settings.index` |
| Manajemen | Log audit | `/audit` | `audit.index` |

Create/edit/detail screens are child flows of their parent menu, including `/debt/create` and `/payments/create` through their page actions. API/JSON endpoints, webhook and POST/PUT/DELETE actions are not sidebar pages. Logout remains POST. Owner view demonstrates all destinations; implementation must retain current per-route permissions and own-resource checks for other roles.

## Components and states

- Navbar, independent sidebar/drawer scroll, compact navigation, breadcrumbs, theme/account controls, store shortcut.
- Search/filter toolbar, input/select/upload affordances, product table/cards, right-aligned currency and pagination with disabled states.
- Form header/footer fixed with scrollable fields, field errors and validation summary; product detail and archive confirmation.
- Primary, secondary, destructive, focus and disabled buttons; checkbox, status, success/error feedback; search empty state and loading/error patterns.

## Prototype limits and handoff

Only the first **Beras Medium 1 kg / BER-002** row demonstrates detail/edit/archive interactions; other row actions follow the documented pattern for implementation. Add opens a form and its save control demonstrates validation. Edit/save and archive confirmation close overlays without mutating data. Filter controls show the panel pattern without querying or changing the table. Inputs, uploads, locale and logout are visual/specification states, not working browser forms.

Sidebar items point to the existing local application URLs; source coverage was checked, but the local server at `127.0.0.1:8000` returned connection refused during browser audit. Runtime route permissions and account state were not verified through login.

Implement breakpoints at >=1200 px (persistent sidebar), 600–1199 px (drawer + full table scroll), <600 px (drawer + product cards). Responsive target frames are rendered; continuous browser resizing remains untested. Modal/dialog body scrolls independently; implement Escape, background scroll locking, focus trapping/return, accessible labels and keyboard/screen-reader QA. Preserve source form validation and transactional inventory/money logic. Reuse actual existing page content rather than copying demonstration data into production.

Product examples, prices and stock come from `WarungCatalogSeeder.php` and `MasterDataSeeder.php`, explicitly labeled as examples. No live business metrics are claimed. Product images reuse the existing placeholder asset.

## Verification

Skills/tools: product-design-sop, antislop core/UI, Figcraft MCP; Playwright attempted local inspection and encountered connection refused. Created/exported 45 render targets and the layout PDF. Checked all eight distinct main layouts at full size; reviewed form/dialog/navigation/component patterns with renders and contact sheets. Corrected icon bounds, pagination wrapping, select/upload affordances, populated edit values, feedback semantics and input border contrast. Final text-bounds audit of the 45 targets, including final control refinements, found no accidental immediate-parent overflow or invalid native prototype destinations; sidebar/form/table clipping is intentional scroll behavior.

Input boundary `#948B7F` against white measures 3.36:1. This and visual review do not establish accessibility compliance. Native prototype connections are inspectable; presenter interaction playback, actual CRUD/search/filter and backend authorization were not tested. Full app implementation is outside this task.
