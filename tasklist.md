# Warbun development

Source requirement: [PRD.md](PRD.md). Implementation decisions: [ARCHITECTURE.md](ARCHITECTURE.md). Legacy unchecked completion claims preserved in tasklist-legacy.md.

- [x] Inspect existing implementation and run baseline QA
- [x] Write architecture, ERD, business flows, permission matrix and migration/test plan
- [x] Restore auth and enforce granular backend authorization
- [x] Transactional stock/POS/debt/payment/shifts with exact money and retries
- [x] Customer identity, online orders, lifecycle, payments and verified gateway boundary
- [x] Refunds, debt reversals/allocations and stock opname
- [x] Staff/users/roles/settings/reports and audit
- [x] Indonesian/English localization and usable responsive screens
- [x] Automated regression QA, migrations/seed, build and browser checks
- [x] Record QA evidence and remaining environment limitations

## Integration boundary

Development QA is complete for the implemented scope; evidence is in [QA_REPORT.md](QA_REPORT.md). Live payment-provider session/refund integration and real email delivery require the selected provider/configuration. Deployment and DevOps are outside this task. Existing operational data must be rehearsed and reconciled on a copy before applying the new migration.
