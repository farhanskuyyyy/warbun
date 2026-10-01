# WORKFLOW: build-web-app

## 1. WORKFLOW IDENTITY

**Workflow Name:** `build-web-app`

**Project Name:** `Warbun`

**Application Type:** E-Catalog + POS/Kasir + Online Order + Inventory + Debt Management + User Monitoring

**Primary Stack:**
- Laravel
- Blade
- Tailwind CSS
- MySQL
- Laravel Eloquent ORM
- Laravel Authentication
- Laravel Authorization / Policy
- Spatie Laravel Permission for roles & permissions
- Laravel Localization / Translation
- JavaScript only where necessary
- Prefer server-rendered Laravel architecture unless a specific feature genuinely requires SPA behavior

Build the application with a clean, maintainable, scalable architecture.

The application must be designed for a small-to-medium retail/warung business where multiple staff members can operate the system at the same time.

The main business problem to solve is:

> Transactions, stock, cash, online orders, and customer debt must remain synchronized even when multiple staff members operate the business.

The system must prioritize **data consistency, traceability, auditability, and ease of operation**.

---

# 2. CORE WORKFLOW PRINCIPLES

Before writing implementation code:

1. Analyze the requirements.
2. Break every major feature into sub-features.
3. Identify supporting entities/tables required by each feature.
4. Identify relationships between entities.
5. Identify required business rules.
6. Identify user roles and permissions.
7. Design database structure.
8. Define transaction/state flows.
9. Define edge cases.
10. Define reporting requirements.
11. Only then start implementation.

Do not build features in isolation.

Every feature that affects another feature must have a clearly defined relationship.

For example:

- Creating a sale must reduce stock.
- Cancelling a sale must restore stock when appropriate.
- Returning an item must adjust stock.
- Creating an online order may reserve or deduct stock according to the selected business flow.
- Creating a debt transaction must increase customer outstanding balance.
- Recording a debt payment must decrease outstanding balance.
- Cancelling/reversing a debt transaction must recalculate the balance correctly.
- Every financial transaction must have a user/staff owner.
- Every important modification must be auditable.

---

# 3. APPLICATION SCOPE

Warbun consists of these primary modules:

1. Dashboard
2. Product & Inventory Management
3. Cashier / POS
4. Online Ordering
5. Payment Management
6. Debt / Accounts Receivable Management
7. Customer Management
8. User & Staff Monitoring
9. Reports
10. Master Data
11. Settings
12. Audit Log

---

# 4. USER TYPES / ROLES

Use **Spatie Laravel Permission** as the source of truth for application roles and permissions.

Do not implement a separate custom role/permission system unless there is a specific technical reason.

Use Spatie's standard concepts:
- Roles
- Permissions
- User-role assignment
- Permission checks
- Middleware
- Policies/Gates where appropriate

At minimum provide:


### Owner / Administrator
Can access everything:
- Dashboard
- Products
- Inventory
- POS
- Orders
- Payments
- Customers
- Debt management
- Users
- Reports
- Settings
- Audit logs

### Manager
Can:
- Monitor operations
- Manage products
- Manage stock
- Monitor sales
- Monitor online orders
- Monitor customer debt
- Approve selected sensitive actions
- View reports

Should have restricted access to system-level configuration.

### Cashier / Staff
Can:
- Process POS transactions
- Create customer transactions
- Process payments
- Create eligible debt transactions
- View relevant customer information
- View own shift
- Process online-order operational steps according to permission

Should NOT automatically have permission to:
- Delete finalized transactions
- Modify historical financial records
- Modify debt balances manually
- Change system settings
- Modify users
- Perform sensitive stock adjustments without permission

### Customer
Can:
- Register/login
- Browse product catalog
- Manage profile
- Create online orders
- Make online payments
- View order history
- View payment status
- View debt information if the business allows online debt visibility
- View previous transactions

## Spatie Role & Permission Requirements

Create roles at minimum:

- `super-admin`
- `owner`
- `manager`
- `cashier`
- `customer`

Create granular permissions rather than relying only on role names.

Examples:

### Dashboard permissions
- `dashboard.view`

### Product permissions
- `products.view`
- `products.create`
- `products.update`
- `products.archive`
- `products.delete`

### Inventory permissions
- `inventory.view`
- `inventory.stock-in`
- `inventory.stock-out`
- `inventory.adjust`
- `inventory.opname`
- `inventory.approve-adjustment`

### POS permissions
- `pos.access`
- `sales.view`
- `sales.create`
- `sales.cancel`
- `sales.refund`
- `sales.override-price`
- `sales.override-discount`

### Order permissions
- `orders.view`
- `orders.create`
- `orders.update`
- `orders.confirm`
- `orders.cancel`
- `orders.complete`

### Payment permissions
- `payments.view`
- `payments.create`
- `payments.refund`
- `payments.correct`

### Customer permissions
- `customers.view`
- `customers.create`
- `customers.update`
- `customers.block`
- `customers.view-debt`

### Debt permissions
- `debt.view`
- `debt.create`
- `debt.pay`
- `debt.adjust`
- `debt.write-off`
- `debt.override-credit-limit`

### User management permissions
- `users.view`
- `users.create`
- `users.update`
- `users.disable`
- `roles.view`
- `roles.create`
- `roles.update`
- `permissions.view`
- `permissions.assign`

### Reports
- `reports.view`
- `reports.sales`
- `reports.inventory`
- `reports.payments`
- `reports.debt`
- `reports.staff`

### Audit
- `audit.view`

Permission names may be refined during implementation, but permissions must remain granular and business-oriented.

## Authorization rules

Use Spatie middleware and Laravel Policies/Gates where appropriate.

Authorization must be enforced server-side.

Do not rely on:
- Hidden buttons
- Frontend route visibility
- JavaScript checks
- UI-only permission checks

A user without the relevant permission must not be able to execute the corresponding backend action directly.

Seed the default roles and permissions using a database seeder.

The `super-admin` role may receive unrestricted permissions through Spatie's recommended super-admin pattern.

Do not hardcode role names throughout business logic when a permission check is more appropriate.

---

# 5. AUTHENTICATION & USER MANAGEMENT

Implement authentication for customers and staff.

### Customer authentication:
- Register
- Login
- Logout
- Forgot password
- Reset password
- Email/phone identification
- Profile management

Important rule:

> A person cannot create a debt transaction using an anonymous customer record.

Anyone who wants to purchase using debt must have a registered customer account.

Customer identity must be tied to:
- User account
- Customer profile
- Phone number
- Name
- Optional address
- Optional email
- Status
- Debt eligibility
- Credit limit
- Current outstanding balance

Prevent duplicate customer accounts where reasonably possible.

---

# 7. MULTI-LANGUAGE / LOCALIZATION

The application must support **2 languages** from the beginning:

- Indonesian (`id`)
- English (`en`)

Indonesian should be the default application language.

## Localization requirements

All user-facing text must use Laravel translation files.

Do NOT hardcode user-facing strings directly into Blade templates, controllers, validation responses, notifications, or JavaScript when the text needs to be translated.

Use translation keys for:

- Navigation
- Menus
- Buttons
- Form labels
- Placeholders
- Validation messages
- Success messages
- Error messages
- Confirmation dialogs
- Empty states
- Table headers
- Status labels
- Dashboard metrics
- Order statuses
- Payment statuses
- Debt statuses
- Stock statuses
- Notifications
- Authentication pages

Recommended structure:

```text
lang/
├── id/
│   ├── auth.php
│   ├── common.php
│   ├── validation.php
│   ├── products.php
│   ├── inventory.php
│   ├── sales.php
│   ├── orders.php
│   ├── payments.php
│   ├── debt.php
│   ├── customers.php
│   ├── users.php
│   ├── reports.php
│   └── settings.php
└── en/
    ├── auth.php
    ├── common.php
    ├── validation.php
    ├── products.php
    ├── inventory.php
    ├── sales.php
    ├── orders.php
    ├── payments.php
    ├── debt.php
    ├── customers.php
    ├── users.php
    ├── reports.php
    └── settings.php
```

The exact translation-file structure may be changed when a better Laravel convention is identified.

## Language switching

Provide a language switcher for the user.

Supported values:
- `id`
- `en`

The selected language should persist appropriately, for example through:
- Session
- Authenticated user preference when available
- Cookie where appropriate

The selected locale must be applied consistently across requests.

## Database/content localization

Do not translate business data such as:
- Product names
- Customer names
- Supplier names
- Transaction references

unless the business explicitly requires multilingual content for those records.

System UI language and business data language must remain separate concepts.

## Formatting

Localization must also consider:
- Date format
- Time format
- Number format
- Currency formatting
- Validation/error wording

For the Indonesian business context, IDR should be displayed appropriately.

Do not use floating-point values for financial calculations.

---

# 8. MASTER DATA

Create a proper master-data structure.

## 6.1 Product Categories

CRUD:
- Create
- Read
- Update
- Archive / deactivate
- Delete only when safe

Fields can include:
- Name
- Code/slug
- Description
- Status
- Sort order

## 6.2 Product Types

Examples:
- Food
- Beverage
- Household
- Accessories
- Other

CRUD.

## 6.3 Brands

CRUD:
- Name
- Code
- Description
- Status

## 6.4 Units

Examples:
- pcs
- box
- bottle
- pack
- kg
- gram
- liter

CRUD.

## 6.5 Suppliers

Fields:
- Name
- Contact
- Phone
- Email
- Address
- Notes
- Status

CRUD.

## 6.6 Products

Product must support:

- Product name
- SKU
- Barcode
- Category
- Type
- Brand
- Unit
- Supplier
- Description
- Cost price
- Selling price
- Minimum stock
- Current stock
- Status
- Product image
- Weight where required
- Optional online availability
- Optional featured flag

Products should support configurable pricing structure where necessary.

Do not duplicate stock values carelessly.

Prefer a proper stock movement model as the source of truth.

---

# 8. INVENTORY / STOCK MANAGEMENT

Inventory is a major module.

Do not implement stock as a simple manually editable number.

Use stock movement / inventory transaction principles.

## 7.1 Stock In

Support:
- Purchase / receiving
- Initial stock
- Restock
- Returned customer item
- Manual approved adjustment

Every stock-in transaction must have:
- Product
- Quantity
- Unit cost where applicable
- Reference number
- Date/time
- Staff/user
- Reason/source
- Notes

## 7.2 Stock Out

Stock can decrease because of:
- POS sale
- Online order
- Damaged item
- Lost item
- Manual adjustment
- Other approved reasons

Every stock-out transaction must have a source/reference.

## 7.3 Stock Adjustment

Provide controlled adjustment.

Adjustment requires:
- Product
- Previous quantity
- Adjustment quantity
- New quantity
- Reason
- User
- Timestamp
- Optional approval

Never silently modify stock.

## 7.4 Stock Opname

Implement stock opname functionality.

Flow:
1. Create opname session.
2. Select products.
3. System displays expected stock.
4. Staff enters physical stock.
5. System calculates difference.
6. User submits adjustment.
7. Authorized role confirms.
8. Stock movement is recorded.

## 7.5 Stock Alerts

Support:
- Low stock
- Out of stock
- Optional overstock warning

Dashboard should display inventory alerts.

---

# 9. CASHIER / POS

POS must be optimized for fast operation.

## POS functionality

Support:

- Search product
- Barcode scan
- Add product to cart
- Change quantity
- Remove product
- Discount where allowed
- Subtotal
- Tax/service charge if configured
- Final total
- Customer selection
- Payment method
- Cash payment
- Non-cash payment
- Debt payment
- Mixed payment if business rules allow
- Print/view receipt
- Transaction history

Every POS transaction must record:

- Transaction number
- Cashier
- Customer
- Items
- Quantity
- Unit price
- Discount
- Subtotal
- Total
- Payment method
- Paid amount
- Change
- Debt amount
- Timestamp
- Status

---

# 10. CASHIER SHIFT MANAGEMENT

Because multiple people may operate the store, implement cashier shifts.

Example:

Staff A opens shift.

During shift:
- Sales belong to Staff A
- Cash collection belongs to Staff A
- Payments processed by Staff A are traceable

At the end:
- Staff performs closing
- System calculates expected cash
- Staff enters actual cash
- System calculates variance

Support:
- Open shift
- Active shift
- Close shift
- Expected cash
- Actual cash
- Cash variance
- Shift history

Managers/admins can review discrepancies.

This feature is important to prevent transactions from becoming difficult to reconcile when multiple staff members operate the store.

---

# 11. PAYMENT MANAGEMENT

Create a centralized payment model.

Payment methods may include:

- Cash
- Bank transfer
- E-wallet
- QR payment
- Online payment gateway
- Debt / receivable
- Other configurable methods

Payment status:

- Pending
- Paid
- Partially paid
- Failed
- Cancelled
- Refunded
- Expired where applicable

The payment system must be designed so a payment is not confused with an order.

An order represents what the customer purchased.

A payment represents how that order was paid.

---

# 12. ONLINE ORDER

Customer-facing e-catalog must allow customers to browse and order.

## Catalog

Display:
- Categories
- Products
- Product images
- Prices
- Availability
- Stock status where appropriate
- Product details
- Search
- Filtering

## Cart

Support:
- Add item
- Remove item
- Change quantity
- Calculate subtotal
- Calculate total
- Checkout

## Customer checkout

Require customer identity.

Information:
- Customer
- Contact
- Address if required
- Order notes
- Delivery/pickup option
- Payment method

## Order status

Design a proper lifecycle, for example:

`Pending`
→ `Confirmed`
→ `Preparing`
→ `Ready`
→ `Completed`

Alternative states:

`Cancelled`
`Rejected`
`Expired`
`Refunded`

Do not allow arbitrary status changes.

Each status transition must follow defined business rules and be auditable.

---

# 13. ONLINE PAYMENT

Payment gateway must be abstracted.

Do not tightly couple the entire application to one payment provider.

Create a payment architecture that can support a gateway such as:
- Midtrans
- Xendit
- Other future providers

The exact gateway should be configurable.

Implement concepts for:

- Payment creation
- Payment reference
- Payment amount
- Payment status
- Expiration
- Callback/webhook
- Signature/security validation
- Failed payment
- Successful payment
- Refund where supported

Important:

> Never trust the frontend response as proof of payment.

Payment confirmation must come from a verified backend payment notification/webhook.

---

# 14. DEBT / HUTANG MANAGEMENT

This is one of the most important modules in Warbun.

The debt system must provide strong traceability and reconciliation.

## Core rule

> Customers who want to purchase using debt MUST register/login first.

Staff cannot simply create an anonymous debt record.

Each debt must belong to an identified customer.

---

# 15. CUSTOMER CREDIT PROFILE

Customer debt profile should include:

- Customer ID
- Total debt
- Total paid
- Outstanding balance
- Credit limit
- Available credit
- Debt status
- Payment history
- Transaction history
- Due dates
- Notes
- Last transaction
- Last payment

Possible debt status:
- Eligible
- Restricted
- Suspended
- Blocked

---

# 16. CREDIT LIMIT

Support optional credit limit.

Example:

Customer credit limit = Rp 1.000.000

Current outstanding = Rp 700.000

Available credit = Rp 300.000

When creating a new debt transaction:

`new outstanding <= credit limit`

must be enforced unless an authorized user explicitly overrides it.

Record every override.

---

# 17. DEBT TRANSACTION MODEL

Never store only one mutable `total_debt` number.

Use transaction history.

A debt ledger should contain:

- Transaction ID
- Customer
- Reference transaction
- Type
- Debit amount
- Credit amount
- Balance after transaction
- Due date
- User/staff
- Timestamp
- Description
- Status

Possible transaction types:

- New debt
- Debt payment
- Refund
- Debt adjustment
- Debt write-off
- Reversal

The ledger must be immutable as much as possible.

Instead of editing historical financial records directly, create reversal/adjustment transactions.

---

# 18. DEBT PAYMENT

Support:

- Full payment
- Partial payment
- Multiple installments
- Payment by cash
- Payment by transfer
- Other configured payment methods

Every payment must record:

- Customer
- Amount
- Payment method
- Reference
- Staff
- Date/time
- Related debt/transaction
- Notes

After payment:

`outstanding debt = previous outstanding - payment`

Balance must be recalculated and verifiable from the ledger.

---

# 19. DEBT DUE DATE

Each debt transaction may have:

- Transaction date
- Due date

Provide monitoring for:

- Not yet due
- Due today
- Overdue

Support configurable default payment terms.

Example:
- 7 days
- 14 days
- 30 days
- Custom

---

# 20. DEBT DASHBOARD

Create a dedicated debt dashboard.

Show:

- Total outstanding debt
- Total debt today
- Total payment today
- Overdue debt
- Due today
- Number of customers with outstanding debt
- Highest outstanding balances
- Recent debt transactions
- Recent debt payments

Provide filters:

- Date range
- Customer
- Status
- Staff
- Overdue
- Due date
- Amount range

---

# 21. CUSTOMER DEBT DETAIL

Each customer must have a dedicated debt detail page.

Show:

### Summary
- Credit limit
- Outstanding balance
- Available credit
- Total debt historically
- Total payments historically

### Ledger
Show chronological entries:

| Date | Type | Reference | Debit | Credit | Balance | Staff |

### Payment history

### Related purchases

### Overdue transactions

### Audit history

This page should make it possible for the business owner to answer:

> "Customer X currently owes how much, from which transactions, who recorded them, and what payments have already been made?"

---

# 22. MULTI-STAFF SYNCHRONIZATION

The application must explicitly handle multiple staff operating simultaneously.

Every sensitive business event should store:

- `created_by`
- `updated_by`
- Timestamp

For financial records also store the responsible staff/operator.

Do not allow a staff member to silently overwrite another staff member's transaction.

Implement:

- Audit logs
- Transaction history
- Staff attribution
- Shift tracking
- Approval flow for sensitive actions

Sensitive actions can include:

- Stock adjustment
- Debt adjustment
- Debt write-off
- Refund
- Transaction cancellation
- Manual payment correction
- Price override
- Discount override

---

# 23. AUDIT LOG

Create a global audit log.

Track important events such as:

- Login
- Logout
- Product creation
- Product update
- Product archive
- Stock adjustment
- Stock opname
- POS transaction
- Order creation
- Order cancellation
- Payment creation
- Refund
- Debt creation
- Debt payment
- Debt adjustment
- Credit-limit override
- User permission change
- Settings change

Audit record should include:

- User
- Action
- Entity
- Entity ID
- Previous value where appropriate
- New value where appropriate
- IP address if appropriate
- User agent if appropriate
- Timestamp

---

# 24. RETURNS / REFUNDS

Do not ignore refunds.

POS and online orders should support controlled returns/refunds.

Return flow must consider:

- Which transaction is being returned
- Which product
- Quantity returned
- Refund amount
- Stock restoration
- Payment reversal
- Debt reversal if the original purchase was on debt

Never simply delete the original transaction.

Use a reversal/refund transaction.

---

# 25. REPORTING

Build reporting for the owner/manager.

## Sales report
- Daily
- Weekly
- Monthly
- Custom date range

Metrics:
- Gross sales
- Discounts
- Net sales
- Number of transactions
- Average transaction value

## Product report
- Best-selling products
- Slow-moving products
- Current stock
- Low stock
- Out of stock

## Payment report
- Cash
- Transfer
- Online payment
- Debt
- Refund
- Outstanding

## Debt report
- Total receivable
- Collected
- Outstanding
- Overdue
- Customer aging

## Staff report
- Transactions per staff
- Sales by staff
- Payments by staff
- Debt created by staff
- Refunds by staff
- Shift variance

---

# 26. DASHBOARD

Create an operational dashboard.

Suggested sections:

### Today
- Sales today
- Transactions today
- Online orders
- Cash collected
- Debt created
- Debt collected

### Inventory
- Low stock
- Out of stock
- Recent stock movement

### Debt
- Total outstanding
- Overdue
- Due today

### Operations
- Active cashier shifts
- Recent transactions
- Recent online orders
- Recent payments

### Alerts
- Payment failed
- Order pending too long
- Overdue debt
- Low stock
- Shift discrepancy

---

# 27. NAVIGATION / ADMIN STRUCTURE

Suggested admin navigation:

Dashboard

Catalog
- Products
- Categories
- Types
- Brands
- Units

Inventory
- Stock Overview
- Stock In
- Stock Out
- Stock Adjustment
- Stock Opname
- Stock History

Sales
- POS / Cashier
- Sales History
- Returns / Refunds

Orders
- Online Orders
- Order Detail
- Order History

Payments
- Payment History
- Payment Methods
- Refunds

Customers
- Customers
- Customer Detail
- Customer Transactions

Debt
- Debt Dashboard
- Outstanding Debt
- Overdue Debt
- Debt Payments
- Debt Ledger
- Debt Adjustments

Users
- Users
- Roles
- Permissions
- Staff Activity
- Cashier Shifts

Reports
- Sales
- Inventory
- Payments
- Debt
- Staff

Settings
- Store Profile
- Payment Settings
- Order Settings
- Debt Settings
- General Settings

Audit
- Audit Logs

---

# 28. DATABASE DESIGN REQUIREMENTS

Before coding, generate an ERD/database plan.

Identify entities at minimum:

- users
- customers / customer_profiles
- roles
- permissions
- categories
- product_types
- brands
- units
- suppliers
- products
- product_images
- inventory_transactions
- stock_opnames
- stock_opname_items
- cashier_shifts
- sales
- sale_items
- orders
- order_items
- payments
- payment_methods
- refunds
- refund_items
- debt_accounts
- debt_transactions
- debt_payments where appropriate
- audit_logs

Do not blindly create every table above.

Normalize the design appropriately and merge/split entities when a better design exists.

Every table must have a clear purpose.

Avoid duplicated source-of-truth fields.

For financial calculations, use appropriate precise database types.

For IDR currency, avoid floating-point types for monetary values.

---

# 29. DATA INTEGRITY

Use database transactions for important operations.

For example, completing a sale should atomically handle:

1. Validate stock.
2. Create sale.
3. Create sale items.
4. Create payment.
5. Create inventory movement.
6. Create debt transaction when payment type is debt.
7. Update related balances if derived/cached.
8. Create audit log.

If one critical operation fails, rollback the entire transaction.

The same principle applies to:

- Online order confirmation
- Refund
- Debt payment
- Stock adjustment
- Stock opname

---

# 30. BUSINESS RULES

Implement business rules explicitly.

Examples:

### Product
- SKU should be unique where applicable.
- Barcode should be unique where applicable.
- Inactive products cannot be sold.

### Stock
- Stock cannot go negative unless explicitly configured.
- Stock movement must have source/reference.
- Manual stock changes require reason.
- Sensitive adjustments require authorization.

### POS
- Sale number must be unique.
- Finalized transactions cannot be casually edited.
- Cancellation must create a traceable reversal.

### Debt
- Debt requires registered customer.
- Debt cannot exceed credit limit unless authorized.
- Debt payment cannot exceed outstanding balance unless handling overpayment explicitly.
- Historical debt transactions should not be silently edited or deleted.
- Write-off requires authorization and audit trail.

### Online Order
- Payment status and order status are separate.
- Paid orders cannot be silently cancelled.
- Stock reservation/deduction behavior must be deterministic.
- Payment callback must be verified server-side.

---

# 31. UI / UX REQUIREMENTS

Use Tailwind CSS.

Design should be:

- Clean
- Modern
- Fast
- Easy for cashier usage
- Mobile responsive
- Desktop optimized for backoffice
- Consistent components
- Clear status badges
- Clear confirmation dialogs
- Clear validation messages

Prioritize speed for POS users.

Avoid unnecessarily complex UI.

Create reusable components for:
- Tables
- Forms
- Modal
- Drawer
- Dropdown
- Badge
- Pagination
- Alert
- Empty state
- Confirmation dialog
- Stat cards
- Filters

---

# 32. RESPONSIVE EXPERIENCE

### Customer
Mobile-first.

### Admin
Desktop-first but responsive.

### Cashier
Optimize for:
- Large clickable controls
- Fast product search
- Barcode entry/scanning
- Minimal steps
- Clear total
- Clear payment confirmation

---

# 33. SECURITY

Implement secure defaults.

At minimum:

- Authentication
- Authorization
- Policy checks
- CSRF protection
- Input validation
- Mass-assignment protection
- Secure password handling
- Rate limiting where appropriate
- Payment webhook verification
- Access control for financial data
- Audit logging

Never rely solely on frontend authorization.

Every sensitive action must also be checked server-side.

---

# 34. API / BACKEND ARCHITECTURE

Even if most pages are Blade-rendered, structure backend logic cleanly.

Separate:

- Controllers
- Requests / validation
- Services
- Models
- Policies
- Events/listeners where useful
- Jobs for asynchronous operations
- Notifications
- Queries/reporting services where necessary

Do not put complex business logic directly inside controllers.

For critical flows, use dedicated service classes.

Examples:

- `ProcessSaleService`
- `ProcessPaymentService`
- `ProcessDebtPaymentService`
- `AdjustStockService`
- `ProcessRefundService`
- `ProcessOnlineOrderService`
- `CloseCashierShiftService`

Naming can be changed if a better Laravel architecture is identified.

---

# 35. NOTIFICATION SYSTEM

Prepare notification architecture for:

Customer:
- Order created
- Payment successful
- Payment failed
- Order status changed
- Debt reminder
- Payment receipt

Admin/staff:
- Low stock
- New online order
- Failed payment
- Overdue debt

Do not hardcode notification channels into core business logic.

---

# 36. SEARCH / FILTER / PAGINATION

All large datasets must support appropriate:

- Search
- Filtering
- Pagination
- Sorting

Especially:

- Products
- Customers
- Sales
- Orders
- Payments
- Debt transactions
- Audit logs
- Inventory movements

---

# 37. SOFT DELETE / ARCHIVING

Do not blindly hard-delete business data.

For master data and non-financial entities, consider:
- Soft delete
- Archive
- Active/inactive status

Financial transactions should generally remain traceable.

Use reversal/cancellation flows instead of deletion.

---

# 38. SEEDERS / DEMO DATA

Create useful development seeders.

Seed:

- Admin user
- Manager
- Cashier
- Customer
- Categories
- Brands
- Units
- Product samples
- Payment methods
- Example sales
- Example debt transactions

Demo data must be realistic enough for testing all major flows.

---

# 39. TESTING REQUIREMENTS

Create tests for critical business logic.

At minimum test:

### Inventory
- Stock in
- Stock out
- Prevent negative stock
- Stock adjustment
- Stock opname

### POS
- Sale creation
- Sale with multiple items
- Stock reduction
- Payment
- Debt purchase

### Debt
- Customer must exist
- Credit limit validation
- Create debt
- Partial payment
- Full payment
- Overdue calculation
- Refund/reversal
- Debt balance consistency

### Online order
- Order creation
- Payment pending
- Payment success
- Payment failure
- Order status transition
- Stock handling

### Security
- Unauthorized staff cannot access restricted features
- Unauthorized staff cannot modify sensitive records

---

# 40. OBSERVABILITY / ERROR HANDLING

Implement proper application logging.

For critical operations log enough context to troubleshoot:

- User
- Request/action
- Entity
- Transaction/reference number
- Error message
- Timestamp

Do not expose sensitive information in logs.

User-facing errors should be understandable.

Do not show raw stack traces in production.

---

# 41. IMPLEMENTATION PHASES

Build in this order.

## Phase 1 — Foundation
- Laravel setup
- MySQL setup
- Tailwind setup
- Authentication
- Role/permission
- Layout
- Navigation
- Base components

## Phase 2 — Master Data
- Categories
- Types
- Brands
- Units
- Suppliers
- Products

## Phase 3 — Inventory
- Stock ledger
- Stock in
- Stock out
- Adjustments
- Stock opname
- Inventory dashboard

## Phase 4 — Customer
- Customer registration
- Customer profile
- Customer management
- Customer transaction history

## Phase 5 — POS
- Cart
- Checkout
- Payment
- Receipt
- Sales history
- Cashier shifts

## Phase 6 — Debt
- Customer credit profile
- Credit limit
- Debt ledger
- Debt creation
- Debt payment
- Due dates
- Overdue monitoring
- Debt dashboard
- Debt reporting

## Phase 7 — Online Order
- Customer catalog
- Cart
- Checkout
- Order management
- Order statuses

## Phase 8 — Online Payment
- Payment abstraction
- Gateway integration structure
- Webhook
- Payment verification
- Refund support

## Phase 9 — Monitoring & Reports
- Dashboard
- Staff monitoring
- Sales reports
- Inventory reports
- Payment reports
- Debt reports
- Shift reports

## Phase 10 — Audit & Hardening
- Audit logs
- Security review
- Authorization review
- Data integrity review
- Automated tests
- Performance review
- UX review

---

# 42. DEVELOPMENT AGENT BEHAVIOR

When this workflow is executed:

### Step 1
Inspect the current project/repository.

### Step 2
Determine whether the project already contains:
- Laravel
- Tailwind
- Authentication
- Database structure
- Existing modules

Do not overwrite existing functionality unnecessarily.

### Step 3
Create a technical implementation plan before making large changes.

### Step 4
Identify dependencies between modules.

### Step 5
Implement one complete vertical feature at a time.

Example:

Do not only create Product CRUD.

Also make sure:
- Product is usable by inventory
- Product is usable by POS
- Product is usable by online order
- Product has correct stock behavior

### Step 6
After every major module:
- Run migrations
- Run tests
- Check authorization
- Check UI
- Check database relationships
- Check edge cases

### Step 7
Before finalizing, perform a consistency audit across:
- Inventory
- Sales
- Payments
- Orders
- Debt
- Customers
- Staff
- Audit logs

---

# 43. IMPORTANT RESTRICTIONS

Do NOT:

- Build fake dashboard numbers
- Hardcode business balances
- Store debt only as a single manually editable field
- Modify stock without inventory movement
- Modify financial history silently
- Allow anonymous debt transactions
- Trust frontend payment status
- Put all business logic inside controllers
- Delete financial history without trace
- Give cashier unrestricted admin permissions
- Duplicate business logic across controllers
- Create unnecessary SPA complexity

---

# 44. ACCEPTANCE CRITERIA

The application is considered functionally complete when:

1. Owner can see the overall business condition from the dashboard.
2. Products and stock can be managed correctly.
3. Multiple staff can perform transactions without losing traceability.
4. Every transaction identifies the responsible staff.
5. POS transactions automatically affect inventory.
6. Online orders have a clear lifecycle.
7. Online payments are independently tracked from orders.
8. Customers can register and order online.
9. Customers who want debt must be registered.
10. Debt can be monitored per customer.
11. Debt has a complete transaction ledger.
12. Partial and full payments are supported.
13. Overdue debt can be identified.
14. Credit limits can be enforced.
15. Financial corrections use traceable adjustments/reversals.
16. Cashier shifts can be reconciled.
17. Stock opname can reconcile physical vs system stock.
18. Managers can monitor staff activity.
19. Audit logs can explain important changes.
20. Reports provide enough information to reconcile daily operations.

---

# 45. EXPECTED OUTPUT BEFORE IMPLEMENTATION

Before writing the first significant code change, produce:

1. System architecture overview
2. Feature breakdown
3. User roles & permission matrix
4. Database ERD
5. Database migration plan
6. Main business rules
7. POS flow
8. Online order flow
9. Payment flow
10. Debt flow
11. Inventory flow
12. Cashier shift flow
13. Audit strategy
14. Testing strategy
15. Localization strategy
16. Role & permission matrix using Spatie Laravel Permission
17. Implementation roadmap

Then begin implementation.

---

# 46. FINAL QUALITY STANDARD

The goal is NOT to create a simple CRUD application.

The goal is to create a reliable operational system for Warbun.

Every feature must answer three questions:

1. **What business problem does this solve?**
2. **What data must be stored to prove what happened?**
3. **How can the owner verify that the transaction is correct later?**

The system should be designed around:

**Transaction Traceability → Data Consistency → Stock Accuracy → Payment Accuracy → Debt Accuracy → Staff Accountability**

Prioritize correctness, maintainability, localization consistency, and authorization correctness over unnecessary visual or technical complexity.

For every new feature, ensure:
- Indonesian translation exists.
- English translation exists.
- Relevant Spatie permissions exist.
- Authorization is enforced server-side.
- Sensitive operations are covered by tests.
