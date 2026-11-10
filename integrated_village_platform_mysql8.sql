-- ============================================================
-- Integrated Village Digital Platform
-- MySQL 8.0+ schema
-- Version 1.0 | 2026-11-07
--
-- Create the database, then execute this file in MySQL.
-- Example:
--   mysql -u root -p < integrated_village_platform.sql
--
-- Security notes:
-- * Store password hashes only (Argon2id/bcrypt from Laravel).
-- * Encrypt sensitive resident fields in the application layer.
-- * Keep encryption keys outside this database.
-- * Enforce role + region + ownership checks in Laravel Policies.
-- ============================================================

CREATE DATABASE IF NOT EXISTS integrated_village_platform
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE integrated_village_platform;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- 1. IDENTITY AND MASTER DATA
CREATE TABLE villages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(30) NOT NULL UNIQUE,
    name VARCHAR(150) NOT NULL,
    address TEXT NULL,
    postal_code VARCHAR(10) NULL,
    phone VARCHAR(30) NULL,
    email VARCHAR(150) NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(60) NOT NULL UNIQUE,
    email VARCHAR(150) NULL UNIQUE,
    phone VARCHAR(30) NULL,
    password VARCHAR(255) NOT NULL,
    account_status ENUM('active','suspended','disabled','pending') NOT NULL DEFAULT 'pending',
    email_verified_at TIMESTAMP NULL,
    phone_verified_at TIMESTAMP NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_users_status (account_status)
) ENGINE=InnoDB;

CREATE TABLE roles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(60) NOT NULL UNIQUE,
    description VARCHAR(255) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE user_roles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    role_id BIGINT UNSIGNED NOT NULL,
    assigned_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    assigned_by BIGINT UNSIGNED NULL,
    UNIQUE KEY uq_user_roles_pair (user_id, role_id),
    CONSTRAINT fk_user_roles_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT,
    CONSTRAINT fk_user_roles_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT,
    CONSTRAINT fk_user_roles_assigner FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE regions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    village_id BIGINT UNSIGNED NOT NULL,
    parent_id BIGINT UNSIGNED NULL,
    region_type ENUM('rw','rt') NOT NULL,
    region_code VARCHAR(30) NOT NULL,
    name VARCHAR(120) NOT NULL,
    description VARCHAR(255) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_region_village_code (village_id, region_code),
    INDEX idx_regions_parent (parent_id),
    CONSTRAINT fk_regions_village FOREIGN KEY (village_id) REFERENCES villages(id) ON DELETE RESTRICT,
    CONSTRAINT fk_regions_parent FOREIGN KEY (parent_id) REFERENCES regions(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE residents (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL UNIQUE,
    region_id BIGINT UNSIGNED NOT NULL,
    national_id_ciphertext TEXT NULL,
    national_id_nonce VARCHAR(64) NULL,
    national_id_tag VARCHAR(64) NULL,
    national_id_blind_index CHAR(64) NULL,
    full_name_ciphertext TEXT NOT NULL,
    full_name_nonce VARCHAR(64) NULL,
    full_name_tag VARCHAR(64) NULL,
    phone_ciphertext TEXT NULL,
    phone_nonce VARCHAR(64) NULL,
    phone_tag VARCHAR(64) NULL,
    address_ciphertext TEXT NULL,
    address_nonce VARCHAR(64) NULL,
    address_tag VARCHAR(64) NULL,
    birth_date DATE NULL,
    gender ENUM('male','female','other','undisclosed') NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_residents_region (region_id),
    INDEX idx_residents_nik_blind_index (national_id_blind_index),
    CONSTRAINT fk_residents_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT,
    CONSTRAINT fk_residents_region FOREIGN KEY (region_id) REFERENCES regions(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE region_officers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    region_id BIGINT UNSIGNED NOT NULL,
    assigned_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    assigned_by BIGINT UNSIGNED NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    ended_at TIMESTAMP NULL,
    INDEX idx_region_officers_scope (user_id, region_id, is_active),
    CONSTRAINT fk_region_officers_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT,
    CONSTRAINT fk_region_officers_region FOREIGN KEY (region_id) REFERENCES regions(id) ON DELETE RESTRICT,
    CONSTRAINT fk_region_officers_assigner FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE bumdes_staff (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL UNIQUE,
    department VARCHAR(100) NULL,
    position VARCHAR(100) NULL,
    employment_status ENUM('active','inactive','suspended') NOT NULL DEFAULT 'active',
    joined_at DATE NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_bumdes_staff_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 2. VILLAGE CORRESPONDENCE
CREATE TABLE letter_types (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(40) NOT NULL UNIQUE,
    name VARCHAR(150) NOT NULL,
    description TEXT NULL,
    required_fields JSON NULL,
    required_attachments JSON NULL,
    template_path VARCHAR(500) NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE letter_requests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    resident_id BIGINT UNSIGNED NOT NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    letter_type_id BIGINT UNSIGNED NOT NULL,
    request_number VARCHAR(60) NOT NULL UNIQUE,
    submission_channel ENUM('self_service','assisted_bumdes') NOT NULL,
    encrypted_data JSON NOT NULL,
    status ENUM('draft','submitted','rt_review','rw_review','village_review','signature_pending','completed','revision_required','rejected','cancelled') NOT NULL DEFAULT 'draft',
    resident_confirmed_at TIMESTAMP NULL,
    submitted_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    rejection_reason TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_letter_requests_resident_status (resident_id, status),
    INDEX idx_letter_requests_status_created (status, created_at),
    CONSTRAINT fk_letter_requests_resident FOREIGN KEY (resident_id) REFERENCES residents(id) ON DELETE RESTRICT,
    CONSTRAINT fk_letter_requests_creator FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    CONSTRAINT fk_letter_requests_type FOREIGN KEY (letter_type_id) REFERENCES letter_types(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE letter_approvals (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    letter_request_id BIGINT UNSIGNED NOT NULL,
    reviewer_id BIGINT UNSIGNED NOT NULL,
    approval_level ENUM('rt','rw','village_admin','village_head') NOT NULL,
    decision ENUM('approved','rejected','revision_required','signed') NOT NULL,
    comments TEXT NULL,
    reviewed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_letter_approvals_history (letter_request_id, approval_level, reviewed_at),
    CONSTRAINT fk_letter_approvals_request FOREIGN KEY (letter_request_id) REFERENCES letter_requests(id) ON DELETE RESTRICT,
    CONSTRAINT fk_letter_approvals_reviewer FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE letter_attachments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    letter_request_id BIGINT UNSIGNED NOT NULL,
    uploaded_by BIGINT UNSIGNED NOT NULL,
    private_file_path VARCHAR(500) NOT NULL,
    original_filename VARCHAR(255) NULL,
    mime_type VARCHAR(120) NULL,
    file_size BIGINT UNSIGNED NULL,
    uploaded_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_letter_attachments_request FOREIGN KEY (letter_request_id) REFERENCES letter_requests(id) ON DELETE RESTRICT,
    CONSTRAINT fk_letter_attachments_uploader FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE letter_documents (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    letter_request_id BIGINT UNSIGNED NOT NULL,
    document_number VARCHAR(80) NULL,
    private_file_path VARCHAR(500) NOT NULL,
    document_hash CHAR(64) NOT NULL,
    version INT UNSIGNED NOT NULL,
    signed_by BIGINT UNSIGNED NULL,
    signed_at TIMESTAMP NULL,
    signature_type ENUM('prototype','psre') NULL,
    issued_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_letter_document_version (letter_request_id, version),
    INDEX idx_letter_documents_hash (document_hash),
    CONSTRAINT fk_letter_documents_request FOREIGN KEY (letter_request_id) REFERENCES letter_requests(id) ON DELETE RESTRICT,
    CONSTRAINT fk_letter_documents_signer FOREIGN KEY (signed_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 3. RT/RW NET AND PUBLIC WI-FI
CREATE TABLE internet_packages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    description TEXT NULL,
    monthly_price DECIMAL(14,2) NOT NULL,
    bandwidth_download_kbps INT UNSIGNED NULL,
    bandwidth_upload_kbps INT UNSIGNED NULL,
    validity_days SMALLINT UNSIGNED NOT NULL DEFAULT 30,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT chk_internet_packages_price CHECK (monthly_price >= 0)
) ENGINE=InnoDB;

CREATE TABLE wifi_networks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    network_type ENUM('public_wifi','residential','office','other') NOT NULL,
    location VARCHAR(255) NOT NULL,
    ssid VARCHAR(100) NOT NULL,
    status ENUM('active','inactive','maintenance') NOT NULL DEFAULT 'active',
    access_policy JSON NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_wifi_networks_type_status (network_type, status)
) ENGINE=InnoDB;

CREATE TABLE network_devices (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    wifi_network_id BIGINT UNSIGNED NOT NULL,
    device_name VARCHAR(120) NOT NULL,
    integration_type ENUM('simulated','mikrotik','other') NOT NULL DEFAULT 'simulated',
    device_identifier VARCHAR(150) NULL,
    device_status ENUM('online','offline','maintenance','unknown') NOT NULL DEFAULT 'unknown',
    configuration JSON NULL,
    last_seen_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_network_devices_network (wifi_network_id),
    CONSTRAINT fk_network_devices_network FOREIGN KEY (wifi_network_id) REFERENCES wifi_networks(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE installation_requests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    resident_id BIGINT UNSIGNED NOT NULL,
    package_id BIGINT UNSIGNED NOT NULL,
    installation_address TEXT NOT NULL,
    contact_phone VARCHAR(30) NULL,
    requested_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    scheduled_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    status ENUM('submitted','reviewing','approved','rejected','scheduled','installed','cancelled') NOT NULL DEFAULT 'submitted',
    reviewed_by BIGINT UNSIGNED NULL,
    review_note TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_installation_requests_status (status, requested_at),
    CONSTRAINT fk_installation_requests_resident FOREIGN KEY (resident_id) REFERENCES residents(id) ON DELETE RESTRICT,
    CONSTRAINT fk_installation_requests_package FOREIGN KEY (package_id) REFERENCES internet_packages(id) ON DELETE RESTRICT,
    CONSTRAINT fk_installation_requests_reviewer FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE internet_subscriptions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    resident_id BIGINT UNSIGNED NOT NULL,
    package_id BIGINT UNSIGNED NOT NULL,
    installation_request_id BIGINT UNSIGNED NULL UNIQUE,
    start_date DATE NOT NULL,
    end_date DATE NULL,
    status ENUM('pending','active','suspended','expired','cancelled') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_internet_subscriptions_resident_status (resident_id, status),
    CONSTRAINT fk_internet_subscriptions_resident FOREIGN KEY (resident_id) REFERENCES residents(id) ON DELETE RESTRICT,
    CONSTRAINT fk_internet_subscriptions_package FOREIGN KEY (package_id) REFERENCES internet_packages(id) ON DELETE RESTRICT,
    CONSTRAINT fk_internet_subscriptions_installation FOREIGN KEY (installation_request_id) REFERENCES installation_requests(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE subscription_invoices (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    subscription_id BIGINT UNSIGNED NOT NULL,
    invoice_number VARCHAR(60) NOT NULL UNIQUE,
    billing_period_start DATE NOT NULL,
    billing_period_end DATE NOT NULL,
    amount DECIMAL(14,2) NOT NULL,
    due_date DATE NOT NULL,
    paid_at TIMESTAMP NULL,
    status ENUM('unpaid','pending','paid','overdue','cancelled') NOT NULL DEFAULT 'unpaid',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_subscription_invoices_due (status, due_date),
    CONSTRAINT chk_subscription_invoice_amount CHECK (amount >= 0),
    CONSTRAINT chk_subscription_invoice_period CHECK (billing_period_end >= billing_period_start),
    CONSTRAINT fk_subscription_invoices_subscription FOREIGN KEY (subscription_id) REFERENCES internet_subscriptions(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE service_requests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    resident_id BIGINT UNSIGNED NOT NULL,
    service_type ENUM('complaint','technical_support','billing_question','wifi_access','other') NOT NULL,
    subject VARCHAR(180) NOT NULL,
    description TEXT NOT NULL,
    status ENUM('submitted','in_progress','waiting_resident','resolved','rejected','closed') NOT NULL DEFAULT 'submitted',
    assigned_to BIGINT UNSIGNED NULL,
    resolution_note TEXT NULL,
    resolved_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_service_requests_status (status, created_at),
    CONSTRAINT fk_service_requests_resident FOREIGN KEY (resident_id) REFERENCES residents(id) ON DELETE RESTRICT,
    CONSTRAINT fk_service_requests_assignee FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE wifi_access_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    wifi_network_id BIGINT UNSIGNED NOT NULL,
    subscription_id BIGINT UNSIGNED NULL,
    authentication_status ENUM('success','failed','blocked','expired') NOT NULL,
    session_reference VARCHAR(150) NULL,
    client_identifier_hash CHAR(64) NULL,
    ip_address VARBINARY(16) NULL,
    connected_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    disconnected_at TIMESTAMP NULL,
    INDEX idx_wifi_access_user_time (user_id, connected_at),
    INDEX idx_wifi_access_network_time (wifi_network_id, connected_at),
    CONSTRAINT fk_wifi_access_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT,
    CONSTRAINT fk_wifi_access_network FOREIGN KEY (wifi_network_id) REFERENCES wifi_networks(id) ON DELETE RESTRICT,
    CONSTRAINT fk_wifi_access_subscription FOREIGN KEY (subscription_id) REFERENCES internet_subscriptions(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 4. MARKETPLACE AND FINANCE
CREATE TABLE sellers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL UNIQUE,
    business_name VARCHAR(160) NOT NULL,
    business_description TEXT NULL,
    verification_status ENUM('pending','approved','rejected','suspended') NOT NULL DEFAULT 'pending',
    verified_by BIGINT UNSIGNED NULL,
    verified_at TIMESTAMP NULL,
    rejection_reason TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_sellers_verification (verification_status),
    CONSTRAINT fk_sellers_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT,
    CONSTRAINT fk_sellers_verifier FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE product_categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    parent_id BIGINT UNSIGNED NULL,
    name VARCHAR(120) NOT NULL,
    slug VARCHAR(140) NOT NULL UNIQUE,
    description TEXT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_product_categories_parent FOREIGN KEY (parent_id) REFERENCES product_categories(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE products (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    seller_id BIGINT UNSIGNED NOT NULL,
    category_id BIGINT UNSIGNED NOT NULL,
    sku VARCHAR(80) NULL,
    name VARCHAR(180) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    description TEXT NULL,
    price DECIMAL(14,2) NOT NULL,
    stock INT UNSIGNED NOT NULL DEFAULT 0,
    unit VARCHAR(40) NOT NULL DEFAULT 'item',
    image_path VARCHAR(500) NULL,
    status ENUM('draft','pending_review','active','rejected','inactive','out_of_stock') NOT NULL DEFAULT 'draft',
    approved_by BIGINT UNSIGNED NULL,
    approved_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_products_seller_sku (seller_id, sku),
    INDEX idx_products_catalog (status, category_id, created_at),
    CONSTRAINT chk_products_price CHECK (price >= 0),
    CONSTRAINT chk_products_stock CHECK (stock >= 0),
    CONSTRAINT fk_products_seller FOREIGN KEY (seller_id) REFERENCES sellers(id) ON DELETE RESTRICT,
    CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES product_categories(id) ON DELETE RESTRICT,
    CONSTRAINT fk_products_approver FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE carts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    status ENUM('active','converted','abandoned') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_carts_user_status (user_id, status),
    CONSTRAINT fk_carts_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE cart_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cart_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    added_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_cart_product (cart_id, product_id),
    CONSTRAINT chk_cart_item_quantity CHECK (quantity > 0),
    CONSTRAINT fk_cart_items_cart FOREIGN KEY (cart_id) REFERENCES carts(id) ON DELETE RESTRICT,
    CONSTRAINT fk_cart_items_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE checkout_groups (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    buyer_id BIGINT UNSIGNED NOT NULL,
    checkout_number VARCHAR(60) NOT NULL UNIQUE,
    grand_total DECIMAL(14,2) NOT NULL,
    status ENUM('pending','processing','completed','cancelled','partially_cancelled') NOT NULL DEFAULT 'pending',
    checked_out_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT chk_checkout_grand_total CHECK (grand_total >= 0),
    CONSTRAINT fk_checkout_groups_buyer FOREIGN KEY (buyer_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE orders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    checkout_group_id BIGINT UNSIGNED NOT NULL,
    buyer_id BIGINT UNSIGNED NOT NULL,
    seller_id BIGINT UNSIGNED NOT NULL,
    order_number VARCHAR(60) NOT NULL UNIQUE,
    subtotal DECIMAL(14,2) NOT NULL,
    delivery_fee DECIMAL(14,2) NOT NULL DEFAULT 0,
    total_amount DECIMAL(14,2) NOT NULL,
    payment_method ENUM('gateway','cod') NOT NULL,
    status ENUM('pending_payment','paid','processing','ready_to_ship','shipped','delivered','completed','cancelled','refunded') NOT NULL DEFAULT 'pending_payment',
    buyer_note TEXT NULL,
    placed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_orders_seller_status (seller_id, status, placed_at),
    INDEX idx_orders_buyer_status (buyer_id, status, placed_at),
    CONSTRAINT chk_orders_amounts CHECK (subtotal >= 0 AND delivery_fee >= 0 AND total_amount >= 0),
    CONSTRAINT fk_orders_checkout_group FOREIGN KEY (checkout_group_id) REFERENCES checkout_groups(id) ON DELETE RESTRICT,
    CONSTRAINT fk_orders_buyer FOREIGN KEY (buyer_id) REFERENCES users(id) ON DELETE RESTRICT,
    CONSTRAINT fk_orders_seller FOREIGN KEY (seller_id) REFERENCES sellers(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE order_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NULL,
    product_name_snapshot VARCHAR(180) NOT NULL,
    sku_snapshot VARCHAR(80) NULL,
    unit_price DECIMAL(14,2) NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    line_total DECIMAL(14,2) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_order_items_product (product_id),
    CONSTRAINT chk_order_items_quantity CHECK (quantity > 0),
    CONSTRAINT chk_order_items_amounts CHECK (unit_price >= 0 AND line_total >= 0),
    CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE RESTRICT,
    CONSTRAINT fk_order_items_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT UNSIGNED NULL,
    invoice_id BIGINT UNSIGNED NULL,
    payment_method ENUM('gateway','cod','bank_transfer','cash','other') NOT NULL,
    payment_provider VARCHAR(80) NULL,
    provider_reference VARCHAR(180) NULL,
    amount DECIMAL(14,2) NOT NULL,
    status ENUM('pending','authorized','paid','failed','expired','cancelled','refunded') NOT NULL DEFAULT 'pending',
    paid_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_payments_order (order_id),
    INDEX idx_payments_invoice (invoice_id),
    INDEX idx_payments_provider_reference (provider_reference),
    INDEX idx_payments_status_created (status, created_at),
    CONSTRAINT chk_payments_one_target CHECK (
      (order_id IS NOT NULL AND invoice_id IS NULL) OR
      (order_id IS NULL AND invoice_id IS NOT NULL)
    ),
    CONSTRAINT chk_payments_amount CHECK (amount > 0),
    CONSTRAINT fk_payments_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE RESTRICT,
    CONSTRAINT fk_payments_invoice FOREIGN KEY (invoice_id) REFERENCES subscription_invoices(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE payment_events (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    payment_id BIGINT UNSIGNED NOT NULL,
    event_type VARCHAR(100) NOT NULL,
    event_reference VARCHAR(180) NULL,
    payload JSON NULL,
    signature_verified BOOLEAN NOT NULL DEFAULT FALSE,
    received_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_payment_events_payment_time (payment_id, received_at),
    UNIQUE KEY uq_payment_event_reference (event_reference),
    CONSTRAINT fk_payment_events_payment FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE deliveries (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT UNSIGNED NOT NULL UNIQUE,
    delivery_address TEXT NOT NULL,
    recipient_name VARCHAR(150) NOT NULL,
    recipient_phone VARCHAR(30) NULL,
    delivery_status ENUM('pending','preparing','shipped','delivered','failed','returned') NOT NULL DEFAULT 'pending',
    tracking_reference VARCHAR(120) NULL,
    seller_note TEXT NULL,
    shipped_at TIMESTAMP NULL,
    delivered_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_deliveries_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 5. BUMDES ASSISTANCE AND SYSTEM OPERATIONS
CREATE TABLE assisted_service_sessions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    resident_id BIGINT UNSIGNED NOT NULL,
    operator_id BIGINT UNSIGNED NOT NULL,
    service_type ENUM('letter','internet','marketplace','account','payment','other') NOT NULL,
    reference_type VARCHAR(100) NULL,
    reference_id BIGINT UNSIGNED NULL,
    assistance_status ENUM('opened','in_progress','waiting_resident_confirmation','submitted','completed','cancelled') NOT NULL DEFAULT 'opened',
    started_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_assisted_sessions_resident (resident_id, started_at),
    INDEX idx_assisted_sessions_operator (operator_id, started_at),
    INDEX idx_assisted_sessions_reference (reference_type, reference_id),
    CONSTRAINT fk_assisted_sessions_resident FOREIGN KEY (resident_id) REFERENCES residents(id) ON DELETE RESTRICT,
    CONSTRAINT fk_assisted_sessions_operator FOREIGN KEY (operator_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE assisted_service_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    session_id BIGINT UNSIGNED NOT NULL,
    actor_id BIGINT UNSIGNED NULL,
    action VARCHAR(100) NOT NULL,
    description TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_assisted_logs_session_time (session_id, created_at),
    CONSTRAINT fk_assisted_logs_session FOREIGN KEY (session_id) REFERENCES assisted_service_sessions(id) ON DELETE RESTRICT,
    CONSTRAINT fk_assisted_logs_actor FOREIGN KEY (actor_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(180) NOT NULL,
    message TEXT NOT NULL,
    reference_type VARCHAR(100) NULL,
    reference_id BIGINT UNSIGNED NULL,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_notifications_user_read (user_id, read_at, created_at),
    CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    action VARCHAR(120) NOT NULL,
    entity_type VARCHAR(120) NOT NULL,
    entity_id BIGINT UNSIGNED NULL,
    metadata JSON NULL,
    ip_address VARBINARY(16) NULL,
    user_agent VARCHAR(500) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_audit_entity (entity_type, entity_id, created_at),
    INDEX idx_audit_user_time (user_id, created_at),
    CONSTRAINT fk_audit_logs_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;

-- 6. INITIAL ROLE SEED DATA
INSERT INTO roles (name, description) VALUES
('Super Admin', 'Technical platform administration and configuration'),
('BUMDes Manager', 'Supervises BUMDes operations, staff, packages, devices, and reports'),
('BUMDes Operator', 'Assists residents and handles permitted operational workflows'),
('Village Admin', 'Manages letter types, village review, and document preparation'),
('RT', 'Reviews correspondence requests within assigned RT region'),
('RW', 'Reviews correspondence requests within assigned RW region'),
('Resident', 'Uses village services and purchases marketplace products'),
('Seller', 'Manages own products, orders, and seller-managed delivery')
ON DUPLICATE KEY UPDATE description = VALUES(description);

-- NOTE:
-- 1. Add the village row and RT/RW region hierarchy through an authorized
--    setup process. Do not insert fictitious village or resident data.
-- 2. The schema cannot enforce workflow order, role eligibility, seller
--    ownership, region scope, stock reservation, or payment callback
--    authenticity by itself. Implement these in Laravel services/policies.
-- 3. For assisted_service_sessions and notifications, reference_type +
--    reference_id is polymorphic and therefore has no native FK.
-- 4. Do not store plaintext NIK, phone, address, passwords, OTP, PIN,
--    payment credentials, or encryption keys in this database.
