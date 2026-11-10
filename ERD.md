# DATABASE DESIGN DOCUMENT (ERD)

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

# 9. Logical database design and ERD

The logical design below is grouped by domain. The initial nine tables
are retained conceptually but normalized into clearer entities. The
proposed schema contains 36 core and supporting tables, including the
BUMDes assistance additions. Some optional tables may be deferred during
early implementation.

## 9.1 Initial table mapping

  --------------------------------------------------------------------------
  **Initial      **Recommended entity**    **Reason**
  outline**                                
  -------------- ------------------------- ---------------------------------
  login          users                     Stores credentials and account
                                           state; avoid naming a table after
                                           a screen.

  village        villages                  Village master data.

  Wi-Fi          wifi_networks             Network configuration and
                                           location.

  letters        letter_requests plus      Separate request, configuration,
                 letter_types, approvals,  review history, and output.
                 documents                 

  goods          products plus             Separate product records from
                 product_categories        classification.

  requests       installation_requests and Separate internet installation
                 service_requests          from complaints and other service
                                           requests.

  region         regions plus              Hierarchical RT/RW and official
                 region_officers           assignments.

  transactions   orders, payments,         Separate commercial order
                 subscription_invoices     lifecycle from financial
                                           settlement.

  roles          roles plus user_roles     Supports multiple roles per
                                           account.
  --------------------------------------------------------------------------

## 9.2 Entity inventory

  -------------------------------------------------------------------------------
  **Domain**            **Entities**
  --------------------- ---------------------------------------------------------
  Identity/master       users, roles, user_roles, villages, regions, residents,
                        region_officers, bumdes_staff

  Correspondence        letter_types, letter_requests, letter_approvals,
                        letter_attachments, letter_documents

  Internet              wifi_networks, network_devices, internet_packages,
                        installation_requests, internet_subscriptions,
                        subscription_invoices, service_requests, wifi_access_logs

  Marketplace/finance   sellers, product_categories, products, carts, cart_items,
                        checkout_groups, orders, order_items, payments,
                        payment_events, deliveries

  Operations/system     assisted_service_sessions, assisted_service_logs,
                        assisted_service_messages,
                        notifications, audit_logs
  -------------------------------------------------------------------------------

## 9.3 Identity and master-data ERD

> erDiagram\
> VILLAGES \|\|\--o{ REGIONS : contains\
> REGIONS \|\|\--o{ REGIONS : parent_of\
> REGIONS \|\|\--o{ RESIDENTS : assigned_to\
> USERS \|\|\--o{ USER_ROLES : has\
> ROLES \|\|\--o{ USER_ROLES : assigned\
> USERS \|\|\--o\| RESIDENTS : profile\
> USERS \|\|\--o{ REGION_OFFICERS : assigned\
> REGIONS \|\|\--o{ REGION_OFFICERS : administered_by\
> USERS \|\|\--o\| BUMDES_STAFF : staff_profile\
> \
> VILLAGES {\
> bigint id PK\
> varchar name\
> varchar code UK\
> text address\
> }\
> REGIONS {\
> bigint id PK\
> bigint village_id FK\
> bigint parent_id FK\
> varchar region_type\
> varchar region_code\
> bigint code_scope_id\
> varchar name\
> }\
> USERS {\
> bigint id PK\
> varchar username UK\
> varchar email UK\
> varchar password_hash\
> varchar account_status\
> datetime created_at\
> }\
> ROLES {\
> bigint id PK\
> varchar name UK\
> text description\
> }\
> USER_ROLES {\
> bigint id PK\
> bigint user_id FK\
> bigint role_id FK\
> datetime assigned_at\
> }\
> RESIDENTS {\
> bigint id PK\
> bigint user_id FK\
> bigint region_id FK\
> text national_id_ciphertext\
> varchar national_id_blind_index\
> text full_name_ciphertext\
> text phone_ciphertext\
> text address_ciphertext\
> }\
> REGION_OFFICERS {\
> bigint id PK\
> bigint user_id FK\
> bigint region_id FK\
> datetime assigned_at\
> boolean is_active\
> }\
> BUMDES_STAFF {\
> bigint id PK\
> bigint user_id FK\
> varchar department\
> varchar position\
> varchar employment_status\
> }

## 9.4 Correspondence ERD

> erDiagram\
> RESIDENTS \|\|\--o{ LETTER_REQUESTS : owns\
> USERS \|\|\--o{ LETTER_REQUESTS : creates\
> LETTER_TYPES \|\|\--o{ LETTER_REQUESTS : defines\
> LETTER_REQUESTS \|\|\--o{ LETTER_APPROVALS : has_history\
> USERS \|\|\--o{ LETTER_APPROVALS : reviews\
> LETTER_REQUESTS \|\|\--o{ LETTER_ATTACHMENTS : contains\
> LETTER_REQUESTS \|\|\--o{ LETTER_DOCUMENTS : generates\
> USERS \|\|\--o{ LETTER_DOCUMENTS : signs\
> \
> LETTER_TYPES {\
> bigint id PK\
> varchar code UK\
> varchar name\
> json required_fields\
> boolean is_active\
> }\
> LETTER_REQUESTS {\
> bigint id PK\
> bigint resident_id FK\
> bigint created_by FK\
> bigint letter_type_id FK\
> varchar submission_channel\
> varchar request_number UK\
> text encrypted_data\
> varchar status\
> datetime submitted_at\
> }\
> LETTER_APPROVALS {\
> bigint id PK\
> bigint letter_request_id FK\
> bigint reviewer_id FK\
> varchar approval_level\
> varchar decision\
> text comments\
> datetime reviewed_at\
> }\
> LETTER_ATTACHMENTS {\
> bigint id PK\
> bigint letter_request_id FK\
> varchar private_file_path\
> varchar file_type\
> datetime uploaded_at\
> }\
> LETTER_DOCUMENTS {\
> bigint id PK\
> bigint letter_request_id FK\
> varchar document_number\
> varchar private_file_path\
> varchar document_hash\
> bigint signed_by FK\
> datetime signed_at\
> varchar signature_type\
> int version\
> }

## 9.5 Internet and public Wi-Fi ERD

> erDiagram\
> RESIDENTS \|\|\--o{ INSTALLATION_REQUESTS : submits\
> INTERNET_PACKAGES \|\|\--o{ INSTALLATION_REQUESTS : selected\
> RESIDENTS \|\|\--o{ INTERNET_SUBSCRIPTIONS : owns\
> INTERNET_PACKAGES \|\|\--o{ INTERNET_SUBSCRIPTIONS : defines\
> INSTALLATION_REQUESTS \|\|\--o\| INTERNET_SUBSCRIPTIONS : activates\
> INTERNET_SUBSCRIPTIONS \|\|\--o{ SUBSCRIPTION_INVOICES : billed\
> WIFI_NETWORKS \|\|\--o{ NETWORK_DEVICES : uses\
> WIFI_NETWORKS \|\|\--o{ WIFI_ACCESS_LOGS : records\
> USERS \|\|\--o{ WIFI_ACCESS_LOGS : accesses\
> INTERNET_SUBSCRIPTIONS \|\|\--o{ WIFI_ACCESS_LOGS : may_authorize\
> RESIDENTS \|\|\--o{ SERVICE_REQUESTS : submits\
> \
> INTERNET_PACKAGES {\
> bigint id PK\
> varchar name\
> decimal monthly_price\
> bigint bandwidth_limit\
> boolean is_active\
> }\
> INSTALLATION_REQUESTS {\
> bigint id PK\
> bigint resident_id FK\
> bigint package_id FK\
> text installation_address\
> varchar status\
> datetime requested_at\
> }\
> INTERNET_SUBSCRIPTIONS {\
> bigint id PK\
> bigint resident_id FK\
> bigint package_id FK\
> bigint installation_request_id FK\
> date start_date\
> date end_date\
> varchar status\
> }\
> SUBSCRIPTION_INVOICES {\
> bigint id PK\
> bigint subscription_id FK\
> varchar invoice_number UK\
> decimal amount\
> date due_date\
> varchar status\
> }\
> WIFI_NETWORKS {\
> bigint id PK\
> varchar name\
> varchar network_type\
> varchar location\
> varchar ssid\
> varchar status\
> }\
> NETWORK_DEVICES {\
> bigint id PK\
> bigint wifi_network_id FK\
> varchar integration_type\
> varchar device_status\
> }\
> WIFI_ACCESS_LOGS {\
> bigint id PK\
> bigint user_id FK\
> bigint wifi_network_id FK\
> bigint subscription_id FK\
> varchar authentication_status\
> datetime connected_at\
> datetime disconnected_at\
> }\
> SERVICE_REQUESTS {\
> bigint id PK\
> bigint resident_id FK\
> varchar service_type\
> text description\
> varchar status\
> datetime created_at\
> }

## 9.6 Marketplace and payment ERD

> erDiagram\
> USERS \|\|\--o{ SELLERS : applies\
> SELLERS \|\|\--o{ PRODUCTS : manages\
> PRODUCT_CATEGORIES \|\|\--o{ PRODUCTS : classifies\
> USERS \|\|\--o{ CARTS : owns\
> CARTS \|\|\--o{ CART_ITEMS : contains\
> PRODUCTS \|\|\--o{ CART_ITEMS : selected\
> USERS \|\|\--o{ CHECKOUT_GROUPS : checks_out\
> CHECKOUT_GROUPS \|\|\--o{ ORDERS : groups\
> USERS \|\|\--o{ ORDERS : buys\
> SELLERS \|\|\--o{ ORDERS : fulfills\
> ORDERS \|\|\--o{ ORDER_ITEMS : contains\
> PRODUCTS \|\|\--o{ ORDER_ITEMS : referenced\
> ORDERS \|\|\--o{ PAYMENTS : paid_by\
> SUBSCRIPTION_INVOICES \|\|\--o{ PAYMENTS : settled_by\
> PAYMENTS \|\|\--o{ PAYMENT_EVENTS : has_events\
> ORDERS \|\|\--o\| DELIVERIES : delivered_by\
> \
> SELLERS {\
> bigint id PK\
> bigint user_id FK\
> varchar business_name\
> varchar verification_status\
> }\
> PRODUCTS {\
> bigint id PK\
> bigint seller_id FK\
> bigint category_id FK\
> varchar name\
> decimal price\
> int stock\
> varchar status\
> }\
> CARTS {\
> bigint id PK\
> bigint user_id FK\
> datetime created_at\
> }\
> CART_ITEMS {\
> bigint id PK\
> bigint cart_id FK\
> bigint product_id FK\
> int quantity\
> }\
> CHECKOUT_GROUPS {\
> bigint id PK\
> bigint buyer_id FK\
> varchar checkout_number UK\
> decimal grand_total\
> }\
> ORDERS {\
> bigint id PK\
> bigint checkout_group_id FK\
> bigint buyer_id FK\
> bigint seller_id FK\
> varchar order_number UK\
> decimal total_amount\
> varchar status\
> }\
> ORDER_ITEMS {\
> bigint id PK\
> bigint order_id FK\
> bigint product_id FK\
> varchar product_name_snapshot\
> decimal unit_price\
> int quantity\
> }\
> PAYMENTS {\
> bigint id PK\
> bigint order_id FK\
> bigint invoice_id FK\
> varchar payment_method\
> varchar provider_reference\
> decimal amount\
> varchar status\
> }\
> PAYMENT_EVENTS {\
> bigint id PK\
> bigint payment_id FK\
> varchar event_type\
> varchar event_reference\
> datetime received_at\
> }\
> DELIVERIES {\
> bigint id PK\
> bigint order_id FK\
> text delivery_address\
> varchar delivery_status\
> datetime delivered_at\
> }

## 9.7 BUMDes assistance and system ERD

> erDiagram\
> RESIDENTS \|\|\--o{ ASSISTED_SERVICE_SESSIONS : receives\
> USERS \|\|\--o{ ASSISTED_SERVICE_SESSIONS : operates\
> ASSISTED_SERVICE_SESSIONS \|\|\--o{ ASSISTED_SERVICE_LOGS : records\
> USERS \|\|\--o{ NOTIFICATIONS : receives\
> USERS \|\|\--o{ AUDIT_LOGS : performs\
> \
> ASSISTED_SERVICE_SESSIONS {\
> bigint id PK\
> bigint resident_id FK\
> bigint operator_id FK\
> varchar service_type\
> varchar reference_type\
> bigint reference_id\
> varchar assistance_status\
> datetime started_at\
> datetime completed_at\
> }\
> ASSISTED_SERVICE_LOGS {\
> bigint id PK\
> bigint session_id FK\
> varchar action\
> text description\
> datetime created_at\
> }\
> NOTIFICATIONS {\
> bigint id PK\
> bigint user_id FK\
> varchar title\
> text message\
> varchar reference_type\
> bigint reference_id\
> datetime read_at\
> }\
> AUDIT_LOGS {\
> bigint id PK\
> bigint user_id FK\
> varchar action\
> varchar entity_type\
> bigint entity_id\
> json metadata\
> datetime created_at\
> }

# 10. Data dictionary and key constraints

## 10.1 Core entities and important columns

  --------------------------------------------------------------------------------------
  **Table**                   **Important columns**          **Notes**
  --------------------------- ------------------------------ ---------------------------
  users                       id, username, email,           Unique username/email;
                              password_hash, account_status  store password hash only.

  roles                       id, name, description          Unique role name.

  user_roles                  user_id, role_id               Unique pair; supports
                                                             multiple roles.

  villages                    id, name, code, address        Single active village in
                                                             initial deployment.

  regions                     id, village_id, parent_id,     Self-referencing hierarchy
                              region_type, region_code, name for RW and RT. A village can
                                                             have many RWs; each RW can
                                                             have many RTs. RW codes are
                                                             unique per village; RT codes
                                                             are unique within their RW.

  residents                   user_id, region_id, encrypted  One profile per user; use
                              identity fields                ciphertext and separate
                                                             blind index.

  region_officers             user_id, region_id, is_active  Restrict officials to
                                                             assigned region.

  bumdes_staff                user_id, department, position, BUMDes
                              employment_status              employment/assignment
                                                             metadata.

  letter_requests             resident_id, created_by,       Resident owns request;
                              letter_type_id,                actor may be operator.
                              submission_channel, status     

  letter_approvals            letter_request_id,             Append-only decision
                              reviewer_id, approval_level,   history.
                              decision                       

  letter_documents            request_id, version,           Versioned immutable issued
                              document_hash, signed_by,      document.
                              signed_at                      

  internet_subscriptions      resident_id, package_id,       Subscription lifecycle.
                              start_date, end_date, status   

  subscription_invoices       subscription_id,               Monthly billing.
                              invoice_number, amount,        
                              due_date, status               

  wifi_networks               name, network_type, location,  Public access points and
                              ssid, status                   network metadata.

  products                    seller_id, category_id, name,  Seller-owned catalog.
                              price, stock, status           

  checkout_groups             buyer_id, checkout_number,     Groups multi-seller
                              grand_total                    checkout.

  orders                      checkout_group_id, buyer_id,   One seller per order.
                              seller_id, order_number,       
                              status                         

  payments                    order_id, invoice_id, method,  Exactly one of order_id or
                              provider_reference, amount,    invoice_id must be set.
                              status                         

  deliveries                  order_id, delivery_address,    Seller-managed delivery.
                              delivery_status                

  assisted_service_sessions   resident_id, operator_id,      Tracks who assisted whom.
                              service_type, reference        

  assisted_service_messages   session_id, sender_id, body,   Encrypted, append-only
                              created_at                     private conversation.

  audit_logs                  user_id, action, entity_type,  Avoid storing plaintext
                              entity_id, metadata            sensitive data.
  --------------------------------------------------------------------------------------

## 10.2 Recommended database constraints

-   users.username and users.email: unique, subject to nullable email
    policy.

-   user_roles: unique(user_id, role_id).

-   residents.user_id: unique.

-   region_officers: enforce one active assignment for a user/region and
    validate role eligibility in application logic.

-   letter_requests.request_number: unique.

-   letter_approvals: index(letter_request_id, approval_level,
    reviewed_at).

-   products.stock: CHECK stock \>= 0.

-   cart_items: unique(cart_id, product_id).

-   orders.order_number: unique.

-   order_items.quantity: CHECK quantity \> 0.

-   payments.amount: CHECK amount \> 0.

-   payments: CHECK exactly one of order_id and invoice_id is non-null.

-   deliveries.order_id: unique if only one delivery record per seller
    order is supported.

-   Foreign-key deletion rules should generally restrict deletion of
    financial, approval, audit, and issued-document history. Prefer soft
    deletion for operational master records.

## 10.3 Payment model clarification

Use one payments table for both e-commerce orders and internet
subscription invoices, with nullable order_id and invoice_id and a
database check requiring exactly one populated reference. If future
requirements need split payments, refunds, or partial payments,
introduce payment_allocations and refunds rather than overloading the
payments row.

# Appendix B. ERD implementation notes

-   The ERD is a logical design, not a final migration script. Confirm
    exact lengths, precision, nullability, indexes, and delete/update
    rules during migration design.

-   The region hierarchy should use region_type values such as rw and
    rt. The village is represented by villages, not necessarily as a
    region row.

-   For a single village, villages can still be retained as a master
    entity to keep the model explicit and simplify future expansion,
    even though multi-village deployment is out of scope.

-   The payments table uses order_id and invoice_id with an
    exactly-one-reference constraint. A future generalized
    billable/payable polymorphic design is possible but provides weaker
    native foreign-key integrity.

-   Assisted-service reference_type/reference_id is polymorphic. For
    strict referential integrity, use dedicated nullable foreign keys or
    a separate service subject table.

-   Audit logs should be append-only at the application level,
    access-restricted, and periodically exported or protected against
    tampering.

-   Use soft deletes selectively. Never cascade-delete financial
    records, approval decisions, issued documents, or audit history.
