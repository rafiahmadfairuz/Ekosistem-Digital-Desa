# Phase 2 authorization matrix

Authorization is enforced on the server through authenticated routes, role middleware, policies, and region-scoped queries. A role by itself does not grant access to records outside the scope listed here. Unlisted actions are denied.

| Role | Allowed Phase 2 actions | Data scope | Explicit denials |
|---|---|---|---|
| Anonymous | View village homepage; register as Resident; log in; verify email using a signed link; request/reset password | Own request fields only | No authenticated profiles, roles, account administration, or region data |
| Super Admin | Review pending accounts; activate verified accounts; manage non-protected roles and account status; maintain village/RW/RT master data; assign/revoke RT/RW regions; read audit events | Technical account, geographic catalog, and assignment administration | Cannot assign Super Admin or Village Head in the UI; cannot sign or approve official documents through this phase. Creating a region grants no account access by itself. |
| BUMDes Manager | Create/update BUMDes staff records and employment details | Accounts already assigned BUMDes Manager or BUMDes Operator; same village BUMDes | Cannot grant roles, impersonate users, read resident identity data, or approve/sign official correspondence |
| BUMDes Operator | No Phase 2 administrative action | None | No staff administration, resident ownership, account administration, impersonation, correspondence approval, or signature authority |
| Village Admin | Verify a resident's proposed RT region after administrative checking | Pending resident username and proposed region only; no NIK, phone, address, or birth date | Cannot change roles, bypass RT/RW scope, or sign official documents by virtue of this role |
| RT | View the limited resident name and region in verified assigned RTs | Resident profile region must be verified; an active explicit `region_officers` assignment must match an RT region | No cross-RT/RW access; no NIK, phone, address, birth date, account status, or role changes |
| RW | View the limited resident name and region in verified RTs beneath assigned RW regions | Active explicit RW assignment; RT child and parent must belong to the same village | No cross-RW access; no NIK, phone, address, birth date, account status, or role changes |
| Resident | Create and update their own profile; view their own profile | `residents.user_id` must equal the authenticated user ID | Cannot change ownership, roles, account status, or region verification; verified region cannot be edited by the resident |
| Seller | No Phase 2 action | None | Marketplace actions are out of scope and no route is granted |
| Village Head | No Phase 2 action | None | No signing UI or correspondence workflow is implemented in this phase |

## Enforcement notes

- Public registration always assigns only the Resident role and creates a pending account.
- The role assignment screen cannot change the actor's roles and excludes Super Admin and Village Head.
- RT/RW accounts without an active, matching region assignment see no resident records.
- Region verification is separate from a resident's proposed region. RT/RW queries include only verified profiles.
- Failed region record lookups return 404 to avoid confirming that an out-of-scope resident exists.
- Correspondence, internet/Wi-Fi, marketplace, payments, and signing remain unavailable in Phase 2.

## System-wide module matrix (Phase 8 review)

The first table is the original Phase 2 baseline. This summary reflects current route/policy structure reviewed for Phases 1–7. Each record is still subject to its resource policy, active-account check, ownership, assignment, and workflow state; a route role alone is not sufficient. Anything not granted below is denied.

| Role | Dashboard / resident data | Correspondence | Internet / Wi-Fi | Marketplace | Assisted services | Reports / audit / administration |
|---|---|---|---|---|---|---|
| Anonymous | Public homepage only | None | None | Public active catalog only | None | None |
| Resident | Own profile only | Own requests and permitted actions | Own subscription, payment submission, complaint and eligible Wi-Fi session | Browse, own cart/orders, own seller application/products/orders if approved seller | Create/view own help requests, confirm own session | No administrative reports, audit, or system settings |
| Seller | Own seller/product profile | Own resident permissions only | Own resident permissions only | Own products and seller orders; cannot order own product | Own resident permissions only | No other seller/customer records or global settings |
| RT | Limited verified residents in explicitly assigned RT | Assigned-region stage only when policy/workflow permits | No broad management | Public browse / own buyer scope only | No staff-only operations unless separately authorized | No manager reports, audit logs, or system settings |
| RW | Limited verified residents under explicitly assigned RW | Assigned-region stage only when policy/workflow permits | No broad management | Public browse / own buyer scope only | No staff-only operations unless separately authorized | No manager reports, audit logs, or system settings |
| Village Admin | Region verification and administrative scope provided by policies | Village review/type configuration where authorized; no signing by role alone | No unrestricted payment authority | No seller authority by default | No staff-only operations unless separately authorized | Scoped village administration; no Super Admin technical settings |
| Village Head | No general data access implied by role | Signature stage only for eligible assigned correspondence requests; prototype signature is not PSrE | No broad management | Public browse / own buyer scope only | No staff-only operations unless separately authorized | No technical super-admin access |
| BUMDes Operator | No unrestricted resident profile access | May assist through accepted resident-owned session; cannot approve/sign | Operational workflows allowed by route and service state; no router secrets | May assist but cannot impersonate buyer/seller or confirm payment/delivery | Assigned active sessions; no manager reports | No staff administration, financial report, audit or village administration |
| BUMDes Manager | Minimal operational information | No approval/sign authority by BUMDes role | Management workflows allowed by policy; no secrets exposed | Operational management explicitly authorized; not automatic seller authority | Sessions/reports within operational scope | Operational reports and staff/package management where authorized; not technical system admin or audit logs by default |
| Super Admin | Technical administration only; resident data only where a specific policy allows | No legal approval/sign authority from technical role | No payment/gateway authority from technical role alone | Moderation/admin capabilities only where explicit policy allows | No resident identity access by technical role alone | Account/region/configuration/audit administration as explicit routes/policies permit |

### Verification note

This is a source-level summary, not a full independent authorization certification. The current SQLite feature suite covers important ownership, role, region, payment, and workflow denials. MySQL integration, exhaustive route-by-route adversarial testing, and browser checks remain open; see `../SECURITY_AUDIT.md` and `../TEST_REPORT.md`.
