# PRODUCT REQUIREMENTS DOCUMENT (PRD)

## Development of an Integrated Village Digital Platform Based on BUMDes for Public Services and the Local Economy

Version 1.0 \| 7 November 2026

# Document control

Document purpose: establish the product scope, business requirements,
user permissions, workflows, logical data model, security requirements,
and Laravel implementation guidance for a single-village digital
platform operated with BUMDes as the primary assisted-service and
commercial operations center.

  ----------------------------------------------------------------------
  **Item**               **Decision**
  ---------------------- -----------------------------------------------
  Project scope          One village

  Application            Laravel modular monolith
  architecture           

  Database               MySQL 8 recommended; PostgreSQL is also viable

  Primary modules        Village correspondence; RT/RW Net and public
                         Wi-Fi; local e-commerce

  BUMDes role            Operational service center and
                         assisted-digital-service intermediary

  Correspondence         Resident → RT → RW → village administration →
  approval               village head signature

  Digital signature      Prototype signing workflow, designed for later
                         PSrE integration

  Network integration    Hybrid: simulated gateway for prototype,
                         adapter for future MikroTik API

  Payments               Payment gateway and COD; internet subscription
                         invoices also supported

  Delivery               Seller-managed delivery

  User access            Shared identity system; one account may have
                         multiple roles
  ----------------------------------------------------------------------

Terminology: BUMDes means Badan Usaha Milik Desa. RT and RW refer to
local neighborhood administrative units. PSrE refers to a Penyelenggara
Sertifikasi Elektronik.

# 1. Executive summary

The Integrated Village Digital Platform is a web-based application for
one village. It combines administrative correspondence, village internet
services, public Wi-Fi access, and a local marketplace in one platform.
BUMDes is the operational center that helps residents use digital
services, manages RT/RW Net operations, and supports local sellers.
Village officials retain authority over official administrative
approvals and issuance.

The platform supports two service channels: self-service through the
website and assisted service through BUMDes staff. Assisted service is
important because some residents may have limited digital literacy,
limited device access, or difficulty completing online forms. BUMDes
operators may help enter and submit information after verifying the
resident and recording the assistance. The resident remains the owner of
the request and must confirm the information.

The recommended architecture is a Laravel modular monolith. Identity,
roles, and regional assignments are shared. Each business module has its
own entities and workflow. This avoids unnecessary microservice
complexity while keeping the codebase organized by domain.

# 2. Product vision, goals, and success measures

## 2.1 Product vision

Provide one accessible digital entry point for village public services
and local economic activity, supported by BUMDes staff for residents who
need in-person assistance.

## 2.2 Product goals

  -----------------------------------------------------------------------
  **ID**       **Goal**           **Expected outcome**
  ------------ ------------------ ---------------------------------------
  G-01         Digitize village   Requests, reviews, decisions, and final
               correspondence     documents are traceable.

  G-02         Improve            Residents can see request status and
               transparency       receive updates.

  G-03         Operate village    Installation, subscriptions, billing,
               internet services  and complaints are managed centrally.

  G-04         Secure public      Only eligible authenticated users
               Wi-Fi              receive network authorization.

  G-05         Support local      Farmers and business owners can list
               sellers            and sell products locally.

  G-06         Improve digital    Residents can complete services
               inclusion          independently or with BUMDes
                                  assistance.
  -----------------------------------------------------------------------

## 2.3 Suggested success measures

Set baseline values during pilot deployment. Suggested measures include:
percentage of correspondence requests submitted digitally; median time
from submission to decision; percentage of residents who can track
requests; number of assisted services completed by BUMDes; active
internet subscriptions; public Wi-Fi authentication success rate; number
of approved local sellers; completed local orders; payment
reconciliation exceptions; and user-reported ease of use.

# 3. Scope and boundaries

## 3.1 In scope

-   Single-village deployment with village, RW, and RT administrative
    hierarchy.

-   Centralized registration, login, account recovery, roles, and
    permissions.

-   Resident profiles and region assignments.

-   Self-service and BUMDes-assisted village letter requests.

-   Sequential RT, RW, and village administration review.

-   Prototype village-head signing workflow and PDF issuance.

-   RT/RW Net package, installation, subscription, invoice, complaint,
    and payment management.

-   Public Wi-Fi captive portal experience and simulated network
    authorization.

-   Adapter boundary for future MikroTik RouterOS API integration.

-   Seller registration and administrator approval.

-   Product catalog, shopping cart, multi-seller checkout,
    seller-specific orders, payment, COD, and seller-managed delivery.

-   Notifications, audit trail, private file storage, and operational
    reporting.

## 3.2 Out of scope for the initial prototype

-   Multi-village or multi-tenant administration.

-   Production-grade certified electronic signatures through a PSrE. The
    data model must leave room for this integration.

-   Live MikroTik enforcement if no physical router and test network are
    available.

-   Automated logistics, courier marketplace, route optimization, or
    third-party delivery integration.

-   Automated escrow, seller payout splitting, or complex marketplace
    commission settlement unless later required.

-   Native mobile applications. The first release is a responsive web
    application.

## 3.3 Assumptions

-   The village has one BUMDes organization operating the platform.

-   Residents are registered to one RT/RW region within the village.

-   The village administration provides official letter types, required
    fields, and approval policy.

-   BUMDes has designated manager and operator accounts.

-   Payment gateway credentials and merchant onboarding will be provided
    by the project owner before live payment testing.

-   Public Wi-Fi locations and internet package terms will be configured
    by BUMDes.

# 4. Stakeholders and user roles

  --------------------------------------------------------------------------
  **Role**   **Primary responsibility**           **Key restrictions**
  ---------- ------------------------------------ --------------------------
  Super      Platform configuration, account and  No automatic authority to
  Admin      role administration, technical       approve official village
             oversight                            letters unless explicitly
                                                  assigned.

  BUMDes     Supervises BUMDes operations, staff, Does not approve
  Manager    service performance, packages,       RT/RW/village decisions or
             devices, and reports                 sign for the village head.

  BUMDes     Assists residents, creates assisted  Cannot approve official
  Operator   service sessions, enters             letters, impersonate
             information, supports seller and     residents, or bypass
             internet service workflows           resident confirmation.

  Village    Reviews requests after RW approval,  Cannot approve RT/RW
  Admin      manages letter templates, prepares   decisions on their behalf
             final documents                      unless formally assigned.

  RT         Reviews requests from residents in   Can only access requests
             assigned RT                          within assigned region.

  RW         Reviews requests from RTs within     Can only access requests
             assigned RW                          within assigned region.

  Resident   Requests letters, subscribes to      Can access own records and
             internet, uses Wi-Fi, buys products  permitted public
                                                  information.

  Seller     Manages own products, stock, orders, Can access only own seller
             and deliveries                       profile, products, and
                                                  seller orders.
  --------------------------------------------------------------------------

## 4.1 Multiple roles

A single user may hold multiple roles. For example, a resident may also
be an approved seller. Implement this through users, roles, and
user_roles rather than duplicating accounts.

## 4.2 Region-scoped authorization

Roles alone are insufficient for RT/RW access. A region_officers
assignment must connect a user to a specific region. Laravel Policies
must verify both the user\'s role and the request\'s region before
displaying or changing records.

# 5. BUMDes operating model

BUMDes is the platform\'s primary operational service center and digital
inclusion intermediary. It does not replace the legal or administrative
authority of RT, RW, or village administration.

  --------------------------------------------------------------------------------
  **Service area** **BUMDes responsibilities**          **Boundary**
  ---------------- ------------------------------------ --------------------------
  Digital          Help with registration, account      Never request or store
  assistance       recovery, form entry, uploads, and   passwords, PINs, OTPs, or
                   service navigation.                  payment credentials.

  Correspondence   Create requests on behalf of         Resident must authorize
  assistance       residents, check completeness,       and confirm data. BUMDes
                   explain status, help retrieve final  cannot approve official
                   documents.                           stages or sign.

  Internet         Manage packages, installation queue, Actual network access must
  services         subscriptions, invoices, complaints, be enforced by the
                   public Wi-Fi locations, and          gateway, not merely by the
                   simulated device actions.            web UI.

  Marketplace      Help sellers register, prepare       Seller remains responsible
  support          listings, upload images with         for price, stock, order
                   permission, and understand orders.   acceptance, product
                                                        quality, and delivery.

  Payment          Explain payment options, record      Gateway success must be
  assistance       authorized offline/COD               verified server-to-server.
                   confirmations, issue receipts,       Staff must not alter
                   reconcile transactions.              gateway evidence.

  Community        Provide basic digital literacy       Avoid collecting
  education        guidance and explain how to use the  unnecessary personal data.
                   platform.                            
  --------------------------------------------------------------------------------

## 5.1 Assisted-service principles

-   Verify the resident\'s identity using the village\'s approved
    process.

-   Record the operator and resident in an assisted_service_session.

-   After a BUMDes staff member accepts a help request, provide a private
    conversation between that resident and the assigned staff member.
    Preserve message sender and timestamp, encrypt message text at rest,
    and keep the conversation read-only when the session ends. Never use
    chat as a substitute for resident confirmation or official approval.

-   Explain what information will be entered and why.

-   Have the resident review and confirm the information before
    submission.

-   Record the submission channel as assisted_bumdes.

-   Preserve the resident as the owner of the request and the operator
    as the submitting actor.

-   Give the resident a receipt or reference number and explain how to
    track progress.

-   Keep sensitive personal information out of audit descriptions and
    notification text.

# 6. Functional requirements

## 6.1 Shared identity and resident management

  ------------------------------------------------------------------------------
  **ID**     **Requirement**   **Acceptance criteria**
  ---------- ----------------- -------------------------------------------------
  FR-ID-01   Account           A resident can register with required credentials
             registration      and verify the account using the configured
                               verification method.

  FR-ID-02   Authentication    Users can log in and log out securely; sessions
                               use secure cookie settings.

  FR-ID-03   Account recovery  Users can recover access through a verified
                               recovery channel without exposing passwords.

  FR-ID-04   Multiple roles    One user can hold multiple roles through
                               user_roles.

  FR-ID-05   Resident profile  Resident identity and region data are linked to
                               one user account.

  FR-ID-06   Region assignment RT/RW officials are assigned to specific regions
                               and restricted to those regions.
  ------------------------------------------------------------------------------

## 6.2 Village correspondence

  -------------------------------------------------------------------------------
  **ID**      **Requirement**   **Acceptance criteria**
  ----------- ----------------- -------------------------------------------------
  FR-LTR-01   Letter type       Village Admin can configure active letter types,
              management        descriptions, required fields, and supporting
                                documents.

  FR-LTR-02   Request           Resident can submit a request through
              submission        self-service or BUMDes-assisted service.

  FR-LTR-03   Sensitive data    Configured sensitive fields are encrypted before
              encryption        database persistence; keys are not stored in the
                                database.

  FR-LTR-04   RT review         Assigned RT can approve, reject, or request
                                revision with a reason.

  FR-LTR-05   RW review         Assigned RW can review only requests approved by
                                the relevant RT.

  FR-LTR-06   Village review    Village Admin can process only requests that
                                passed RT and RW stages.

  FR-LTR-07   Document          Approved requests can generate a versioned PDF
              generation        using the configured template.

  FR-LTR-08   Prototype         Village head account can review and sign a
              signature         document version; signer, timestamp, hash, and
                                signature type are recorded.

  FR-LTR-09   Status tracking   Resident can see current status and relevant
                                history without seeing internal-only comments.

  FR-LTR-10   Revision and      A revision request returns to the resident;
              resubmission      corrections are resubmitted and prior decisions
                                remain in history.
  -------------------------------------------------------------------------------

Approval statuses: draft, submitted, rt_review, rw_review,
village_review, signature_pending, completed, revision_required,
rejected, cancelled. A rejection or revision decision must include a
reason. The system must define whether a rejection is terminal or may be
appealed; default recommendation is terminal rejection, while
revision_required permits correction and resubmission.

## 6.3 RT/RW Net and public Wi-Fi

  -----------------------------------------------------------------------------
  **ID**      **Requirement**   **Acceptance criteria**
  ----------- ----------------- -----------------------------------------------
  FR-NET-01   Internet package  BUMDes Manager can create, update, deactivate,
              management        and price packages, including bandwidth profile
                                metadata.

  FR-NET-02   Installation      Resident or BUMDes Operator can submit an
              request           installation request with location and selected
                                package.

  FR-NET-03   Installation      BUMDes can review, schedule, reject, and mark
              workflow          installation complete.

  FR-NET-04   Subscription      System records start date, end date, status,
              management        package, and linked installation request.

  FR-NET-05   Monthly invoices  System can generate subscription invoices with
                                due dates and payment status.

  FR-NET-06   Complaint         Residents can submit service complaints and
              handling          track resolution.

  FR-NET-07   Captive portal    Public Wi-Fi redirects users to a portal and
                                checks account eligibility before
                                authorization.

  FR-NET-08   Hybrid gateway    Prototype uses a simulated adapter; production
                                can use a MikroTik adapter without changing
                                module business rules.

  FR-NET-09   Access logs       Authentication result, network, account, and
                                connection timestamps are recorded with data
                                minimization.
  -----------------------------------------------------------------------------

Public Wi-Fi authorization must be separate from account registration.
The authorization policy must check account status, acceptable-use
rules, and any applicable access entitlement. A public Wi-Fi user does
not necessarily need a paid home internet subscription; configure
eligibility policy explicitly.

## 6.4 Local e-commerce

  ---------------------------------------------------------------------------
  **ID**      **Requirement**   **Acceptance criteria**
  ----------- ----------------- ---------------------------------------------
  FR-MKT-01   Seller            Resident applies to become a seller;
              application       administrator approves or rejects the
                                application.

  FR-MKT-02   Product           Approved seller can create and manage own
              management        product listings, images, prices, and stock.

  FR-MKT-03   Catalog browsing  Residents can browse active products by
                                category and view details.

  FR-MKT-04   Cart              Resident can add products and change
                                quantities before checkout.

  FR-MKT-05   Multi-seller      One checkout can create separate
              checkout          seller-specific orders grouped under one
                                checkout group.

  FR-MKT-06   Order processing  Seller can accept/reject and update own order
                                status.

  FR-MKT-07   Payment gateway   Server creates payment session and verifies
                                gateway callbacks before marking payment
                                paid.

  FR-MKT-08   COD               COD payment remains pending until an
                                authorized seller/operator confirms
                                collection.

  FR-MKT-09   Seller-managed    Seller updates delivery status; buyer can
              delivery          confirm receipt.

  FR-MKT-10   Stock integrity   Stock changes use database transactions and
                                prevent overselling.
  ---------------------------------------------------------------------------

Each seller-specific order should belong to one seller. If a buyer
purchases from multiple sellers, create one checkout_groups record and
multiple orders. This keeps fulfillment, COD collection, and delivery
responsibility clear.

## 6.5 Notifications and reporting

-   Notify residents when correspondence status changes, but do not
    include sensitive identity details in message bodies.

-   Notify BUMDes operators about new assistance or service queues.

-   Notify sellers about new orders and payment changes.

-   Notify residents about invoice due dates and subscription status.

-   Provide operational reports for BUMDes and authorized village
    administrators.

-   Provide audit history for sensitive administrative and financial
    actions.

# 7. Business workflows

## 7.1 Correspondence workflow

> flowchart TD\
> A\[Resident needs a letter\] \--\> B{Submission channel}\
> B \--\>\|Self-service\| C\[Resident submits website form\]\
> B \--\>\|Assisted\| D\[BUMDes verifies and assists\]\
> D \--\> E\[Resident reviews and confirms\]\
> C \--\> F\[Validate and encrypt sensitive data\]\
> E \--\> F\
> F \--\> G\[RT review\]\
> G \--\>\|Revision\| H\[Return to resident\]\
> G \--\>\|Reject\| X\[Rejected with reason\]\
> G \--\>\|Approve\| I\[RW review\]\
> I \--\>\|Revision\| H\
> I \--\>\|Reject\| X\
> I \--\>\|Approve\| J\[Village administration review\]\
> J \--\>\|Revision\| H\
> J \--\>\|Reject\| X\
> J \--\>\|Approve\| K\[Generate versioned PDF\]\
> K \--\> L\[Village head signs\]\
> L \--\> M\[Issue final document\]\
> M \--\> N\[Resident downloads or collects document\]

## 7.2 Internet installation and subscription

> flowchart TD\
> A\[Resident or BUMDes submits installation request\] \--\> B\[BUMDes
> reviews request\]\
> B \--\> C{Approved?}\
> C \--\>\|No\| D\[Reject with reason\]\
> C \--\>\|Yes\| E\[Schedule installation\]\
> E \--\> F\[Mark installation complete\]\
> F \--\> G\[Create subscription\]\
> G \--\> H\[Generate monthly invoice\]\
> H \--\> I\[Payment or authorized offline confirmation\]\
> I \--\> J\[Check subscription entitlement\]\
> J \--\> K\[Simulated gateway or MikroTik adapter\]\
> K \--\> L\[Grant or suspend access\]

## 7.3 Public Wi-Fi access

> flowchart TD\
> A\[User connects to public Wi-Fi\] \--\> B\[Captive portal\]\
> B \--\> C\[User authenticates\]\
> C \--\> D\[Application checks account and access policy\]\
> D \--\> E{Eligible?}\
> E \--\>\|No\| F\[Show clear access guidance\]\
> E \--\>\|Yes\| G\[Network adapter authorizes session\]\
> G \--\> H\[Record access event\]

## 7.4 Marketplace checkout

> flowchart TD\
> A\[Buyer adds products to cart\] \--\> B\[Checkout\]\
> B \--\> C\[Group items by seller\]\
> C \--\> D\[Create checkout group and seller orders\]\
> D \--\> E{Payment method}\
> E \--\>\|Gateway\| F\[Create payment session\]\
> F \--\> G\[Verify server callback\]\
> G \--\> H\[Seller processes order\]\
> E \--\>\|COD\| H\
> H \--\> I\[Seller delivers\]\
> I \--\> J\[Buyer confirms receipt\]\
> J \--\> K\[Confirm COD collection if applicable\]\
> K \--\> L\[Complete order\]

# 8. Non-functional requirements and security

  -----------------------------------------------------------------------------
  **ID**      **Category**      **Requirement**
  ----------- ----------------- -----------------------------------------------
  NFR-01      Transport         All application traffic must use HTTPS/TLS.
              security          

  NFR-02      Password security Passwords must use Argon2id or bcrypt; never
                                reversible encryption.

  NFR-03      Sensitive data    Use field-level authenticated encryption such
                                as AES-256-GCM for sensitive resident fields.

  NFR-04      Key management    Encryption keys must be outside the database,
                                access-controlled, rotated, and backed up
                                securely.

  NFR-05      Authorization     Use RBAC plus record-level policies and region
                                checks.

  NFR-06      Audit             Record actor, action, target, timestamp, and
                                outcome for important operations.

  NFR-07      File security     Store private documents outside public web
                                root; authorize every download.

  NFR-08      Payments          Verify signed gateway callbacks and enforce
                                idempotency.

  NFR-09      Availability      Set service availability target after hosting
                                and operational constraints are known.

  NFR-10      Performance       Target normal page/API response under 3 seconds
                                under agreed prototype load.

  NFR-11      Usability         Use plain-language labels, clear steps, large
                                touch targets, and mobile-responsive layouts.

  NFR-12      Accessibility     Support keyboard navigation, adequate contrast,
                                and readable form validation.

  NFR-13      Reliability       Use database transactions for order placement,
                                stock reservation, and payment state changes.

  NFR-14      Maintainability   Organize Laravel code by domain and isolate
                                external integrations behind adapters.

  NFR-15      Backup            Perform regular database and private-file
                                backups and test restoration.
  -----------------------------------------------------------------------------

## 8.1 Encryption design

Encrypt fields such as national identity number, full name where
required by policy, phone number, address, and other sensitive
attributes. Use authenticated encryption and store the nonce/IV and
authentication tag as part of the ciphertext envelope. Do not store keys
alongside ciphertext. Laravel encrypted casts may be useful for
application-level encryption, but define key rotation and
searchable-field requirements before selecting the implementation.

For exact-match lookup of encrypted identity values, consider a keyed
HMAC blind index generated with a separate key. Do not use an ordinary
unsalted hash for low-entropy identity values. Restrict access to
decryption through service classes and policies. Keep plaintext out of
logs, analytics, notifications, and exception traces.

## 8.2 Prototype signature limitation

The prototype may capture a drawn or uploaded signature image and record
the signer, timestamp, document version, and SHA-256 document hash. A
signature image and hash do not by themselves establish a legally
certified electronic signature. Production deployment requiring legal
assurance should integrate a certified PSrE and follow applicable
Indonesian requirements.

## 8.3 Payment controls

-   Do not treat a browser redirect as payment proof.

-   Verify gateway callback signatures and transaction status
    server-to-server.

-   Store provider references with unique constraints where appropriate.

-   Make callback processing idempotent to prevent duplicate state
    changes.

-   Keep payment events for reconciliation and audit.

-   COD collection must be confirmed by an authorized actor and retain
    evidence/receipt metadata where appropriate.

# 11. Laravel implementation guidance

## 11.1 Recommended stack

  ----------------------------------------------------------------------
  **Layer**          **Recommendation**
  ------------------ ---------------------------------------------------
  Backend            Laravel (current project-supported stable version)

  Database           MySQL 8 or PostgreSQL

  Frontend           Blade with Bootstrap or another responsive UI
                     framework; optionally Livewire/Inertia if
                     appropriate

  Authentication     Laravel authentication scaffolding; secure session
                     configuration

  Authorization      Spatie Laravel Permission or custom RBAC plus
                     Laravel Policies

  Validation         Form Request classes

  Business logic     Domain services/actions; keep controllers thin

  Background work    Laravel Queues for notifications, document
                     generation, and integrations

  Files              Laravel Storage with private disk and authorized
                     download routes

  PDF                Laravel-compatible PDF generation package

  Payments           Gateway adapter and verified webhook endpoint

  Network            Network adapter interface with simulated and
                     MikroTik implementations
  ----------------------------------------------------------------------

## 11.2 Suggested application structure

> app/\
> Models/\
> Http/\
> Controllers/\
> Admin/\
> Bumdes/\
> Village/\
> Resident/\
> Internet/\
> Marketplace/\
> Requests/\
> Policies/\
> Services/\
> Correspondence/\
> Internet/\
> Marketplace/\
> Payment/\
> Encryption/\
> DigitalSignature/\
> Network/\
> Jobs/\
> Notifications/\
> Events/\
> Listeners/\
> \
> database/\
> migrations/\
> seeders/\
> factories/\
> \
> routes/\
> web.php\
> api.php

## 11.3 Laravel implementation rules

-   Use migrations for all schema changes and seeders for initial roles,
    one village, region hierarchy, and configuration.

-   Use Eloquent relationships, but enforce authorization with Policies
    and scoped queries.

-   Keep controllers thin. Put workflow transitions in services/actions
    and use database transactions.

-   Use Form Requests for validation and authorization checks.

-   Use queues for PDF generation, notifications, and external
    integration calls where asynchronous processing is appropriate.

-   Use private storage for identity attachments and generated letters.

-   Use events/listeners for status-change notifications, but keep
    critical state changes transactional.

-   Use a network adapter interface so the simulated gateway can be
    replaced with MikroTik API implementation.

-   Use payment provider adapters and verify callbacks server-side.

-   Write feature tests for every role and region boundary.

## 11.4 Suggested Laravel packages and caveats

-   Spatie Laravel Permission: useful for role/permission management and
    multiple roles. Still implement region-level Policies.

-   Laravel Sanctum: useful if the project later exposes authenticated
    APIs or a separate frontend. It is not required for a traditional
    server-rendered application.

-   A Laravel-compatible PDF package: select based on deployment
    requirements and font/layout needs.

-   Use Laravel\'s encryption facilities or a dedicated encryption
    service with a documented key-rotation strategy. Do not assume
    default application encryption alone solves searchable
    encrypted-field requirements.

# 12. Testing and acceptance plan

  ----------------------------------------------------------------------
  **Test area**       **Minimum test scenarios**
  ------------------- --------------------------------------------------
  Authentication      Registration, login, logout, password recovery,
                      inactive account.

  RBAC                Multiple roles, denied actions, role changes,
                      unauthorized access.

  Region scope        RT sees only assigned RT requests; RW sees only
                      assigned RW requests.

  Assisted services   Operator creates session, resident confirmation
                      recorded, actor and owner distinguished.

  Correspondence      Sequential approval, revision, rejection,
                      resubmission, document versioning, signature
                      audit.

  Encryption          Ciphertext at rest, decryption authorization, key
                      separation, no sensitive plaintext in logs.

  Internet            Installation request, package changes,
                      subscription activation/expiry, invoice
                      generation.

  Wi-Fi               Captive portal flow, eligible/ineligible accounts,
                      simulated gateway failure, access logging.

  Marketplace         Seller approval, stock validation, multi-seller
                      checkout, seller data isolation.

  Payments            Valid/invalid callback, duplicate callback,
                      pending/failed/success states, COD confirmation.

  Delivery            Seller-only status update, buyer receipt
                      confirmation, cancelled order handling.

  Recovery            Database backup restore and private-file restore.
  ----------------------------------------------------------------------

## 12.1 Definition of done

-   All acceptance criteria for the release are met.

-   No user can access another region\'s restricted records.

-   No seller can access another seller\'s products or orders.

-   No operator can approve or sign official correspondence without
    explicit authority.

-   All sensitive data is encrypted according to the agreed field
    inventory.

-   All payment callbacks are verified and idempotent.

-   Critical workflows have automated tests.

-   Backup and restore procedure is documented and tested.

# 13. Delivery roadmap

  ----------------------------------------------------------------------------------
  **Phase**        **Focus**        **Deliverables**
  ---------------- ---------------- ------------------------------------------------
  1\. Foundation   Identity and     Laravel project setup, users, roles, user_roles,
                   master data      villages, regions, residents, region_officers,
                                    BUMDes staff, audit baseline.

  2\.              Public           Letter types, assisted/self-service submission,
  Correspondence   administration   approvals, revision, PDF generation, prototype
                                    signature, tracking.

  3\. Internet     RT/RW Net        Packages, installation, subscriptions, invoices,
  services                          complaints, captive portal UI, simulated network
                                    adapter.

  4\. Marketplace  Local economy    Seller approval, products, cart, multi-seller
                                    checkout, orders, payment gateway sandbox, COD,
                                    delivery.

  5\. Integration  Quality and      Security tests, callback verification,
  and hardening    deployment       performance checks, backups, operational
                                    training, deployment.
  ----------------------------------------------------------------------------------

Develop the foundation first. The role, region, and ownership model
affects every module. Do not start by building three separate dashboards
with duplicated authorization logic.

# 14. Risks and mitigations

  ------------------------------------------------------------------------
  **Risk**        **Impact**             **Mitigation**
  --------------- ---------------------- ---------------------------------
  Low digital     Residents abandon      BUMDes assisted-service channel,
  literacy        self-service flows or  simple interface, confirmation
                  submit incorrect       step, printed/reference receipts.
                  information.           

  Excessive       Unauthorized access or Least privilege, separate
  BUMDes          administrative         manager/operator roles, audit
  permissions     decisions.             logs, no approval/signature
                                         authority by default.

  Sensitive data  Privacy and identity   Field encryption, key separation,
  exposure        misuse.                private file storage, strict
                                         policies, minimal logs.

  Incorrect RT/RW Requests routed to     Validate region hierarchy and
  mapping         wrong officials.       officer assignments; test
                                         region-scoped access.

  Fake payment    Unpaid order marked    Verify gateway signature and
  notification    paid.                  server-side transaction status;
                                         idempotency.

  Stock           Seller cannot fulfill  Database transaction and atomic
  overselling     orders.                stock reservation/decrement.

  Prototype       Legal and trust risk.  Label prototype clearly and plan
  signature                              PSrE integration for production.
  misunderstood                          
  as certified                           

  Simulated Wi-Fi Unauthorized network   Clearly label prototype;
  mistaken for    access.                production must enforce access at
  real                                   gateway/router.
  enforcement                            

  Overly broad    Delayed delivery and   Build in phases and validate each
  initial scope   poor quality.          module before expanding.
  ------------------------------------------------------------------------

# 15. Open implementation decisions

The product direction and logical ERD are defined. The following
operational details should be confirmed with the village/BUMDes before
production configuration. They do not block the initial schema design.

-   Exact letter types, required fields, and supporting documents.

-   Whether village administration review is a separate approval stage
    or a verification/processing stage after RW approval.

-   Official policy for rejected requests, appeals, and revision limits.

-   Village head signing method and whether PSrE integration is required
    before production.

-   Public Wi-Fi eligibility policy, session duration, bandwidth limits,
    and acceptable-use terms.

-   Internet package pricing, billing cycle, late-payment grace period,
    and suspension policy.

-   Payment gateway provider, merchant account, supported payment
    methods, refund rules, and COD evidence.

-   Seller verification documents, product moderation policy,
    cancellation/refund rules, and any BUMDes marketplace fee.

-   Data retention periods for identity documents, approval history,
    payment events, and access logs.

-   Hosting, backup frequency, disaster recovery target, and expected
    concurrent users.

# Appendix A. Status and enum recommendations

  --------------------------------------------------------------------------------------------------
  **Entity**                                    **Suggested values**
  --------------------------------------------- ----------------------------------------------------
  users.account_status                          pending, active, suspended, disabled

  sellers.verification_status                   pending, approved, rejected, suspended

  letter_requests.status                        draft, submitted, rt_review, rw_review,
                                                village_review, signature_pending, completed,
                                                revision_required, rejected, cancelled

  letter_approvals.decision                     approved, rejected, revision_required

  installation_requests.status                  submitted, under_review, approved, rejected,
                                                scheduled, installed, cancelled

  internet_subscriptions.status                 pending, active, overdue, suspended, expired,
                                                cancelled

  subscription_invoices.status                  unpaid, pending, paid, overdue, cancelled, refunded

  wifi_access_logs.authentication_status        success, denied, failed, expired

  products.status                               draft, pending_review, active, inactive,
                                                out_of_stock, rejected

  orders.status                                 pending_seller, accepted, rejected,
                                                awaiting_payment, processing, ready_for_delivery,
                                                in_delivery, delivered, completed, cancelled

  payments.status                               pending, authorized, paid, failed, expired,
                                                cancelled, refunded

  deliveries.delivery_status                    preparing, ready, in_delivery, delivered, confirmed,
                                                failed, cancelled

  assisted_service_sessions.assistance_status   in_progress, awaiting_resident_confirmation,
                                                submitted, completed, cancelled
  --------------------------------------------------------------------------------------------------

Use database enums only if the team accepts migration overhead when
values change. VARCHAR with application-level backed enums and database
CHECK constraints can be more flexible.
