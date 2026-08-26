DROP TABLE IF EXISTS cart_items;
DROP TABLE IF EXISTS carts;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS prescription_items;
DROP TABLE IF EXISTS prescriptions;
DROP TABLE IF EXISTS purchase_order_items;
DROP TABLE IF EXISTS purchase_orders;
DROP TABLE IF EXISTS product_batches;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS suppliers;
DROP TABLE IF EXISTS staff;
DROP TABLE IF EXISTS customers;

CREATE TABLE customers (
    customer_id     INT AUTO_INCREMENT PRIMARY KEY,
    first_name      VARCHAR(50)  NOT NULL,
    last_name       VARCHAR(50)  NOT NULL,
    email           VARCHAR(100) NOT NULL UNIQUE,
    password_hash   VARCHAR(255) NOT NULL,
    phone           VARCHAR(20),
    address         VARCHAR(255),
    date_of_birth   DATE,
    status          ENUM('active','inactive','banned') DEFAULT 'active',
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE staff (
    staff_id        INT AUTO_INCREMENT PRIMARY KEY,
    first_name      VARCHAR(50)  NOT NULL,
    last_name       VARCHAR(50)  NOT NULL,
    email           VARCHAR(100) NOT NULL UNIQUE,
    password_hash   VARCHAR(255) NOT NULL,
    role            ENUM('pharmacist','admin','super_admin') NOT NULL,
    status          ENUM('active','inactive') DEFAULT 'active',
    hire_date       DATE
);

CREATE TABLE suppliers (
    supplier_id     INT AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(100) NOT NULL,
    contact_person  VARCHAR(100),
    address         VARCHAR(255),
    phone           VARCHAR(20),
    email           VARCHAR(100)
);

CREATE TABLE categories (
    category_id     INT AUTO_INCREMENT PRIMARY KEY,
    category_name   VARCHAR(100) NOT NULL,
    description     TEXT
);

CREATE TABLE products (
    product_id            INT AUTO_INCREMENT PRIMARY KEY,
    product_name          VARCHAR(150) NOT NULL,
    description            TEXT,
    sku                    VARCHAR(50) NOT NULL UNIQUE,
    category_id            INT,
    dosage_form            VARCHAR(50),   -- tablet, syrup, capsule, etc.
    strength               VARCHAR(50),   -- e.g. 500mg
    unit_price             DECIMAL(10,2) NOT NULL,
    requires_prescription  BOOLEAN DEFAULT FALSE,
    reorder_level          INT DEFAULT 10,
    status                 ENUM('active','discontinued','out_of_stock') DEFAULT 'active',
    CONSTRAINT fk_products_category
        FOREIGN KEY (category_id) REFERENCES categories(category_id)
        ON DELETE SET NULL
);

CREATE TABLE product_batches (
    batch_id            INT AUTO_INCREMENT PRIMARY KEY,
    product_id          INT NOT NULL,
    batch_number        VARCHAR(50) NOT NULL,
    quantity_on_hand    INT NOT NULL DEFAULT 0,
    expiry_date         DATE NOT NULL,
    received_date       DATE NOT NULL,
    CONSTRAINT fk_batches_product
        FOREIGN KEY (product_id) REFERENCES products(product_id)
        ON DELETE CASCADE
);

CREATE TABLE purchase_orders (
    po_id           INT AUTO_INCREMENT PRIMARY KEY,
    supplier_id     INT NOT NULL,
    staff_id        INT,                 -- staff member who verified/placed PO
    order_date      DATE NOT NULL,
    expected_date   DATE,
    status          ENUM('pending','ordered','received','cancelled') DEFAULT 'pending',
    CONSTRAINT fk_po_supplier
        FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id),
    CONSTRAINT fk_po_staff
        FOREIGN KEY (staff_id) REFERENCES staff(staff_id)
);

CREATE TABLE purchase_order_items (
    po_item_id          INT AUTO_INCREMENT PRIMARY KEY,
    po_id                INT NOT NULL,
    product_id           INT NOT NULL,
    quantity_ordered     INT NOT NULL,
    unit_cost            DECIMAL(10,2) NOT NULL,
    CONSTRAINT fk_poi_po
        FOREIGN KEY (po_id) REFERENCES purchase_orders(po_id)
        ON DELETE CASCADE,
    CONSTRAINT fk_poi_product
        FOREIGN KEY (product_id) REFERENCES products(product_id)
);

CREATE TABLE prescriptions (
    prescription_id     INT AUTO_INCREMENT PRIMARY KEY,
    customer_id          INT NOT NULL,
    staff_id             INT,             -- staff/pharmacist who verified it
    doctor_name          VARCHAR(100),
    doctor_license_no    VARCHAR(50),
    file_path            VARCHAR(255),
    issue_date           DATE,
    upload_date          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    verified_date         TIMESTAMP NULL,
    rejection_reason      VARCHAR(255),
    status                ENUM('pending','verified','rejected') DEFAULT 'pending',
    CONSTRAINT fk_presc_customer
        FOREIGN KEY (customer_id) REFERENCES customers(customer_id),
    CONSTRAINT fk_presc_staff
        FOREIGN KEY (staff_id) REFERENCES staff(staff_id)
);

CREATE TABLE prescription_items (
    prescription_item_id   INT AUTO_INCREMENT PRIMARY KEY,
    prescription_id         INT NOT NULL,
    product_id               INT NOT NULL,
    prescribed_quantity       INT NOT NULL,
    prescribed_dosage         VARCHAR(100),
    CONSTRAINT fk_pi_prescription
        FOREIGN KEY (prescription_id) REFERENCES prescriptions(prescription_id)
        ON DELETE CASCADE,
    CONSTRAINT fk_pi_product
        FOREIGN KEY (product_id) REFERENCES products(product_id)
);

CREATE TABLE orders (
    order_id        INT AUTO_INCREMENT PRIMARY KEY,
    customer_id      INT NOT NULL,
    prescription_id   INT NULL,          -- nullable: not all orders need a prescription
    order_date        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status             ENUM('pending','confirmed','shipped','delivered','cancelled') DEFAULT 'pending',
    subtotal            DECIMAL(10,2) NOT NULL DEFAULT 0,      -- derived
    tax_amount           DECIMAL(10,2) NOT NULL DEFAULT 0,
    shipping_fee          DECIMAL(10,2) NOT NULL DEFAULT 0,
    total_amount           DECIMAL(10,2) NOT NULL DEFAULT 0,   -- derived
    CONSTRAINT fk_orders_customer
        FOREIGN KEY (customer_id) REFERENCES customers(customer_id),
    CONSTRAINT fk_orders_prescription
        FOREIGN KEY (prescription_id) REFERENCES prescriptions(prescription_id)
        ON DELETE SET NULL
);

CREATE TABLE order_items (
    order_item_id           INT AUTO_INCREMENT PRIMARY KEY,
    order_id                 INT NOT NULL,
    product_id                INT NOT NULL,
    quantity                   INT NOT NULL,
    unit_price_at_purchase      DECIMAL(10,2) NOT NULL,
    item_subtotal                 DECIMAL(10,2) NOT NULL,   -- derived: quantity * unit_price_at_purchase
    CONSTRAINT fk_oi_order
        FOREIGN KEY (order_id) REFERENCES orders(order_id)
        ON DELETE CASCADE,
    CONSTRAINT fk_oi_product
        FOREIGN KEY (product_id) REFERENCES products(product_id)
);

CREATE TABLE payments (
    payment_id              INT AUTO_INCREMENT PRIMARY KEY,
    order_id                 INT NOT NULL,
    payment_method             ENUM('card','cash','bank_transfer','mobile_wallet') NOT NULL,
    amount                       DECIMAL(10,2) NOT NULL,
    transaction_reference          VARCHAR(100),
    payment_status                  ENUM('pending','completed','failed','refunded') DEFAULT 'pending',
    paid_at                          TIMESTAMP NULL,
    CONSTRAINT fk_payments_order
        FOREIGN KEY (order_id) REFERENCES orders(order_id)
);

CREATE TABLE carts (
    cart_id        INT AUTO_INCREMENT PRIMARY KEY,
    customer_id     INT NOT NULL UNIQUE,   -- 1:1 with customers per diagram
    updated_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_carts_customer
        FOREIGN KEY (customer_id) REFERENCES customers(customer_id)
        ON DELETE CASCADE
);

CREATE TABLE cart_items (
    cart_item_id     INT AUTO_INCREMENT PRIMARY KEY,
    cart_id           INT NOT NULL,
    product_id         INT NOT NULL,
    quantity            INT NOT NULL DEFAULT 1,
    CONSTRAINT fk_ci_cart
        FOREIGN KEY (cart_id) REFERENCES carts(cart_id)
        ON DELETE CASCADE,
    CONSTRAINT fk_ci_product
        FOREIGN KEY (product_id) REFERENCES products(product_id),
    CONSTRAINT uq_cart_product UNIQUE (cart_id, product_id)
);

CREATE INDEX idx_products_category ON products(category_id);
CREATE INDEX idx_batches_product ON product_batches(product_id);
CREATE INDEX idx_batches_expiry ON product_batches(expiry_date);
CREATE INDEX idx_po_supplier ON purchase_orders(supplier_id);
CREATE INDEX idx_poi_po ON purchase_order_items(po_id);
CREATE INDEX idx_poi_product ON purchase_order_items(product_id);
CREATE INDEX idx_prescriptions_customer ON prescriptions(customer_id);
CREATE INDEX idx_prescription_items_prescription ON prescription_items(prescription_id);
CREATE INDEX idx_orders_customer ON orders(customer_id);
CREATE INDEX idx_orders_prescription ON orders(prescription_id);
CREATE INDEX idx_order_items_order ON order_items(order_id);
CREATE INDEX idx_order_items_product ON order_items(product_id);
CREATE INDEX idx_payments_order ON payments(order_id);
CREATE INDEX idx_cart_items_cart ON cart_items(cart_id);

CREATE VIEW customer_ages AS
SELECT
    customer_id,
    first_name,
    last_name,
    date_of_birth,
    TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) AS age
FROM customers;

