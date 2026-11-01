# Warbun — agent instructions

This is the canonical agent entry point for this repository. Read it before making changes. [agent.md](agent.md) is a compatibility entry point, not a separate set of rules. Paths below are relative to the Warbun repository containing `artisan` and `composer.json`; no previous chat or machine-specific path is required.

## What this project does

Warbun is an Indonesian small-store application combining a customer storefront with staff operations. Customers browse products, manage a cart and saved addresses, place pickup/delivery orders and review their orders. Cashiers use POS, shifts, receipts and customer credit. Managers/owners manage catalog, inventory, payments, refunds, debts, order fulfillment, reports, users and audit logs.

The application already has business workflows and tests. This is not a greenfield build. Keep sales, stock, cash, debts and order history consistent while changing the interface.

Stack: Laravel/PHP, server-rendered Blade, Alpine.js, Tailwind, Vite, MySQL and Spatie Permission. Read `composer.json`, `composer.lock`, `package.json` and `package-lock.json` for actual versions. Do not replace the stack, install a framework/Boost, or upgrade dependencies just to begin work. Model/provider selection belongs to the agent runtime, not this repository's instructions.

## Read first and find the current task

1. [README.md](README.md): overview, setup and operational limitations.
2. [PRD.md](PRD.md) and [ARCHITECTURE.md](ARCHITECTURE.md): requirements, domain rules, permissions and transaction boundaries. Historical baseline failures are not current test results.
3. [tasklist.md](tasklist.md): existing development work and links to newer plans.
4. For the final UI implementation, [implementation index](docs/implementation/README.md), [working agreement](docs/implementation/00-WORKING-AGREEMENT.md), [STATUS](docs/implementation/STATUS.md), and the active phase document.
5. Inspect related source, tests, applicable nested/ancestor instructions and CI if present before patching.

At handoff on 1 November 2026, the final-design implementation plan P00–P11 is **TODO**. Completed items in the older tasklist do not mean the final Figcraft design has been implemented. Re-check STATUS, Git and source on every new session rather than treating this dated statement as permanent.

## Source map

| Location | Responsibility |
| --- | --- |
| `routes/web.php`, `routes/auth.php` | Customer, CMS, auth, JSON endpoints and actions |
| `app/Http/Controllers/` | Request validation, authorization and responses |
| `app/Services/` | Sales, stock, orders, payments, refunds, debt, shifts, reporting, addresses |
| `app/Models/`, `database/migrations/` | Relationships, persistence and schema evolution |
| `app/Payments/` | Gateway boundary; inspect adapters before claiming provider support |
| `resources/views/layouts/`, `resources/views/components/` | Shared shells, navigation, dialogs and controls |
| `resources/views/customer/`, `resources/views/pos/`, other feature directories | Feature screens; landing is `resources/views/landing.blade.php` |
| `resources/js/`, `resources/css/app.css` | Alpine/browser behaviors and styling |
| `tests/`, `scripts/qa-*` | Backend regression tests and browser/data/concurrency checks |
| `docs/implementation/` | Phased tasks, page coverage, execution status and QA contract |
| `design/figcraft/` | Native design, curated previews and design handoff notes |

## Design and implementation contract

Canonical design: [final.figcraft](design/figcraft/soft-neobrutalism/final.figcraft). Start with the [design map](docs/implementation/01-DESIGN-MAP.md) and [curated previews](design/figcraft/PREVIEWS.md). The file contains customer and CMS designs, reusable components, themes, responsive variants and prototype states.

- Follow Warbun's soft neobrutalism direction: burgundy/teal, Georgia headings, system-ui body and the existing lowercase wordmark. Read the design handoff before adjusting shared tokens.
- Apply the available product-design SOP and relevant UI/antislop skills if accessible. If machine-local skills are unavailable, use the self-contained implementation documents; report the limitation without claiming skill use. Do not install skills automatically.
- Inspect MCP capabilities before using them. If Figcraft access is unavailable, use retained previews and specifications, and state which visual details remain unverified.
- Native prototypes simulate interactions; they do not prove backend persistence, actual payments, free-form grid editing or browser accessibility.
- Cover every page and child flow in [PAGE-COVERAGE](docs/implementation/02-PAGE-COVERAGE.md). Pages without dedicated frames reuse final components and their real existing content. Do not build fake working buttons or replace production data with design fixtures.
- Use existing light/dark, ID/EN and permission behavior. Review responsive layouts, actual interactions and rendered screenshots; fix defects found.
- Do not modify the native design file merely because frontend implementation is requested. A request to maintain instructions/plans does not authorize app or canvas changes.

## Business and data invariants

- Enforce permissions and ownership on the server, including direct URLs. Hiding an action is not authorization.
- Preserve transactional services, lock ordering, idempotency, monetary helpers, inventory movements, debt allocations and audit history. Do not use floating-point arithmetic for money.
- Server data determines prices, stock, fees, discounts, delivery eligibility and totals. Validate again when committing a transaction; do not trust browser/localStorage amounts.
- Order state and payment state are distinct. Do not mark an order paid from a frontend success screen. Preserve existing cancellation/refund and paid-completion rules.
- Historical receipts use saved transaction values, not current prices or policies. Schema changes must preserve old records and have a tested upgrade/rollback strategy.
- The existing manual/signed-webhook payment boundary is not a live gateway integration. Design fee examples are fixtures, not verified provider tariffs. Radius/fee policies and rack persistence have explicit implementation tasks; inspect source before assuming they exist.

## Safe execution and verification

Inspect `git status` before changes. Preserve unrelated work, secrets, `.env` and user data. Do not expose credentials or include them in logs, commits or screenshots.

Use isolated test databases. Read `phpunit.xml` and the effective environment before tests; the checked-in configuration uses SQLite in-memory. Browser setup, seeding and concurrency tests can mutate data: read each script and point the server at disposable QA storage. Never run `migrate:fresh`, seed, setup scripts or test sales against the user's existing database. Validate schema upgrades on a disposable copy first.

Candidate checks, run only when the environment is ready:

```sh
php artisan test --filter=<RelevantTest>
php artisan test
vendor/bin/pint --test
npm run build
git diff --check
```

Add meaningful regression tests for changed behavior. Use isolated MySQL tests for locking/concurrency that SQLite cannot establish. Browser QA must cover interaction, theme, responsive behavior, keyboard/focus and error states; a 200 response or screenshot alone is insufficient. Follow the [full QA plan](docs/implementation/07-QA-AND-RELEASE.md).

Record actual commands/results in [QA_REPORT.md](QA_REPORT.md) and linked phase evidence. Use PASS/FAIL/BLOCKED/NOT RUN honestly. Investigate and fix bugs found within scope, then retest; do not stop at recommendations. Never claim full verification when critical checks could not run.

## Commits and handoff

For the phased implementation, commit **per phase or complete feature**, including relevant tests and documentation after verification. Stage related files explicitly; exclude unrelated changes, secrets, databases and bulk generated assets. Do not collapse the entire implementation into one large commit. Use separate `fix(scope): ...` commits for QA fixes as appropriate.

Update [STATUS.md](docs/implementation/STATUS.md) with the phase/task, tested commit, changed files, verification, blockers and next safe action before ending a session. Do not mark a phase DONE until its exit criteria pass. Another agent must be able to resume using these files alone.

Routine decisions within the authorized task do not need repeated permission. Ask only when missing information truly blocks progress. Push, deployment, DNS/SSL changes and production transactions require their own authorization. Explicit user instructions and applicable project rules take precedence over generic workflows.
