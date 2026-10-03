# Warbun UI direction

## Etalase POS dan penataan produk

Direction retail existing: ENERGY 2 / RHYTHM 3 / MOTION 1. Minimap bernomor membantu petugas menghubungkan produk dengan etalase fisik; urutan nomor tidak mengklaim koordinat denah. Tile berkaki garis rak mengulang motif ledger Warbun. Burgundy dan outline menandai pilihan, sementara nomor/nama tetap terbaca tanpa warna. Minimap dua kolom pada ponsel dengan scroll internal mencegah banyak etalase memperpanjang halaman tanpa batas. Heading, minimap, filter, daftar produk dan panel transaksi mempunyai fungsi dan ritme berbeda.

Dialog info memakai harga sebagai fokus, fakta produk berbaris dan tombol tambah selebar panel. Native dialog menyediakan Escape dan pengembalian fokus; penempatan produk memakai disclosure agar catatan posisi tidak membuat daftar menjadi form panjang. Padding 16–24px, target 44px, radius 5px dan dividers dipakai untuk pemindaian operasional. Icon layer-group menunjukkan lokasi, box menunjukkan belum ditata; semuanya berasal dari sprite Font Awesome existing. Lokasi pengambilan ada tepat di bawah nama barang pada pesanan. Contoh seeder diberi label Contoh, bukan klaim posisi aktual toko. Bukti: [SHELF_QA.md](SHELF_QA.md).

Reading this as a daily retail storefront and operations workspace for customers and shop staff, in a warm editorial retail style. ENERGY 2 / RHYTHM 3 / MOTION 1. Direction retains the existing burgundy identity and replaces the flat navigation and single-block landing.

Applied skills: [anti-slop](https://github.com/miqdadbadjuber/anti-slop) and [UI UX Pro Max](https://github.com/nextlevelbuilder/ui-ux-pro-max-skill). Pro Max research was adapted to this existing Blade product: navigation hierarchy, progressive disclosure, responsive controls and accessibility. Its generic green palette, video hero and social-proof recommendations were not adopted because they have no basis in Warbun's identity or content.

| Decision | Reason |
| --- | --- |
| Burgundy #8B1E2D | Existing brand color identifies the primary action and current navigation location. |
| Ivory #F6F3ED, charcoal #282724, parchment panels | Warm retail surfaces separate shell, catalog and records without decorative gradients. |
| System sans controls, Georgia storefront headings | Controls remain familiar and readable; editorial headings give the storefront a distinct retail voice without external font requests. |
| Five navigation groups and nested master data | Staff can find frequent operational tasks before less frequent catalog configuration. |
| Native mobile dialog | Navigation gets a separate focused surface with Escape, backdrop dismissal and focus restoration. |
| Account and language disclosure | Less frequent actions no longer compete with each page's main task. |
| Asymmetric hero, actual shelf list, category strip, purchase guide | Sections follow real store content and change rhythm without fictional imagery or testimonials. |
| Large sales summary and smaller supporting metrics | Today's sales become the dashboard focal point while all existing financial and operational data remain available. |
| 44px control targets, 24–32px workspace padding, larger storefront gaps | Staff can operate compact tables while shopping content gets more reading space. |
| Subtle panel radius and plain category edges | Group related records while avoiding pill-shaped controls everywhere. |
| Shelf offset shadow; account-menu elevation | Shelf treatment echoes a paper stock list; the popover shadow indicates overlap. |
| Semantic navigation icons, count badge and directional arrows | Icons identify CMS tasks and common controls; the badge reports actual cart quantity, while editorial arrows mark destinations. |
| Existing wordmark and neutral account icon | No fabricated logos, product photos or profile photos introduced; missing product images use honest category/unit placeholders. |
| Hover and focus only | Routine retail operations do not benefit from entrance animations or scroll choreography; reduced-motion settings remain respected. |

The recurring motif is a retail ledger: shelf rows, real prices, tabular totals, fine dividers and numbered items. Landing data comes from active online products and active categories with available products. Empty catalog, empty search and empty cart remain explicit. Feedback uses the existing server component; cart additions use a live status region and update the real count. No dark-mode toggle or theme dependency was added.

## Auth and shopping refinement

The existing direction and dials also apply to authentication, checkout and order details.

| Decision | Reason |
| --- | --- |
| Editorial auth introduction beside a readable form | Connect sign-in to the storefront identity while keeping the form as the main task; the introduction becomes compact on phones. |
| Password visibility control with explicit text | Customers can check input without interpreting an unfamiliar icon; labels and pressed state remain accessible. |
| Mobile grid/list choice below 768px | Default two-column grid makes products easy to browse; optional single-column rows give names more space. Both retain 44px actions, and browser storage remembers the preference. |
| Native category select and labeled search | All categories fit on a phone without a scrolling strip competing with the products. |
| Category/unit image placeholders | Identify unpictured products honestly without repeating their full name or inventing photography. |
| Persistent cart summary after adding products | Shows actual quantity and subtotal and provides an obvious next destination. |
| Shop, cart and order progress | Represents the real purchase flow, with links back to the earlier shopping tasks. |
| Item controls followed by a separate checkout summary | Customers can review quantities before choosing fulfillment and payment; totals remain visually distinct. |
| Server quote, loading, retry and stock errors | Current prices and availability are checked before the order action becomes available; checkout still validates transactionally. |
| Saved checkout draft and intended auth return | Login or registration preserves address, fulfillment and payment choices instead of restarting shopping. |
| Padded order ledger beside the next-step panel | Separates purchased items from status, fulfillment and payment instructions; the panels stack on phones. |
| Primary order-history action and outlined shopping action | Makes the next task clear without placing competing filled buttons together. |
| Responsive order-history cards | Reference, status, total and order link remain visible on narrow screens. |
| Optional store contact and payment instructions | Gives customers actual shop guidance when configured; no bank details or contact information are fabricated. |

The order page clears the cart only for the matching checkout request. Opening a previous order preserves the customer's current cart. Demo catalog assumptions are disclosed in [SEED_DATA.md](SEED_DATA.md); UI evidence is in [SHOPPING_QA_REPORT.md](SHOPPING_QA_REPORT.md).

## Barcode POS and receipt

POS category filtering uses actual active categories as 44px buttons in one non-wrapping, horizontally scrolling row. Burgundy and aria-pressed identify the selected category; an All categories button resets the category while retaining search text. Eight pixels of scroll-container padding leave room for keyboard focus. Horizontal scrolling stays inside the product panel, avoiding page overflow. Category requests combine with text search and cancel stale responses; neither category changes nor search mutate the cart, and barcode scanning remains independent. Evidence: [POS_CATEGORY_QA.md](POS_CATEGORY_QA.md).

The existing retail direction and dials apply to POS. A dedicated scan field sits before manual search so scanner Enter adds a product without submitting payment. A persistent live status names the added product or explains a lookup/stock error. Checkout stays separate and waits for pending scans. The workbench uses two columns when item browsing and payment fit, and stacks below 1024px; scan controls stack below 480px with 44px targets. Printing controls remain outside the receipt and disappear in print media. Black receipt text, dashed ledger dividers, actual item prices and 4mm inner paper margins favor legibility on 58/80mm rolls. The store heading uses configured store data; no emoji or fabricated imagery is used. A paper selector and manual reprint action retain control when automatic print is cancelled. Reasons and verification: [BARCODE_QA_REPORT.md](BARCODE_QA_REPORT.md).

## Navigation icons

Status colors now use one shared transaction badge across monitoring, CMS order/sale views and customer order views. Amber waiting, blue confirmed, violet preparing, teal ready, green completed/paid, red cancelled/failed, slate refunded and orange debt/partial make operational states distinguishable at first glance, as requested. Text labels remain, color is redundant, and order/payment states stay separate. Monitoring uses a thin matching top border; selected filters use the same semantic tint. These extra colors serve state recognition, while ivory/charcoal/burgundy continue to define the brand. Reasons and measured contrast: [STATUS_COLOR_QA.md](STATUS_COLOR_QA.md).

Monitoring and delivery retain ENERGY 2 / RHYTHM 3 / MOTION 1. Oldest active orders come first, with a horizontal status strip, responsive ledger cards, and a single valid next action. Delivery fields appear only when selected; a native customer dialog preserves the cart and supports Escape. Credit information distinguishes deposit, credit allowance and debt remainder. Burgundy marks action/selection, ivory groups addresses, Georgia heads the queue and system sans keeps details legible. Spacing, icon relevance and financial separation are documented in [ORDER_MONITORING.md](ORDER_MONITORING.md), with full [QA and delivery gate](ORDER_MONITOR_QA.md).

Font Awesome Free 6.7.2 Solid icons are served from a local SVG sprite. The shared Blade component inherits text color and hides decorative SVGs from assistive technology. CMS labels remain beside 18px icons because symbols alone do not clearly distinguish debt, payments, refunds and stock tasks. Cart uses a 22px icon with a screen-reader label and visible quantity. Language retains a native select with a language icon and labeled 44px apply control. Drawer/account controls use the same family. Original paths are wrapped in symbols for reuse, with attribution and license in public/icons. No font package, CDN or dependency added. Evidence: [ICON_QA_REPORT.md](ICON_QA_REPORT.md).
