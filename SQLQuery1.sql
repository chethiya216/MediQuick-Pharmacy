CREATE TABLE	CUSTOMER(
	customer_id 	NVARCHAR(50)  NOT NULL,
    first_name     NVARCHAR(100)  NOT NULL,
    last_name      NVARCHAR(100)  NOT NULL,
    email          NVARCHAR(255)  NOT NULL,
    phone          NVARCHAR(20)   NULL,
    address        NVARCHAR(255)  NULL,
    date_of_birth  DATE         NULL,
    password_hash  NVARCHAR(255) NOT NULL,
    status         NVARCHAR(20)  NOT NULL DEFAULT 'active',
    created_at     DATETIME2     NOT NULL DEFAULT GETDATE(),

    CONSTRAINT chk_customer_status CHECK (status IN ('active', 'inactive', 'suspended'))
);
	 
	
CREATE TABLE staff (
	staff_id       NVARCHAR(50)  NOT NULL,
	first_name     NVARCHAR(100) NOT NULL,
	last_name      NVARCHAR(100) NOT NULL,
	email          NVARCHAR(255) NOT NULL,
	password_hash  NVARCHAR(255) NOT NULL,
	role           NVARCHAR(50)  NOT NULL,
	status         NVARCHAR(20)  NOT NULL DEFAULT 'active', 
	hire_date      DATE           NOT NULL DEFAULT CAST(GETDATE() AS DATE),
	created_at    DATETIME2     NOT NULL DEFAULT GETDATE(),


    CONSTRAINT chk_staff_status CHECK (status IN ('active', 'inactive', 'terminated'))
);


CREATE TABLE orders (
    order_id      NVARCHAR(50)  NOT NULL,
    customer_id   NVARCHAR(50)  NOT NULL,
    order_date    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status        NVARCHAR(20)  NOT NULL DEFAULT 'pending',
    subtotal      DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    tax_amount    DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    shipping_fee  DECIMAL(10, 2) NOT NULL DEFAULT 0.00,

    CONSTRAINT pk_orders PRIMARY KEY (order_id),
    CONSTRAINT chk_orders_status CHECK (status IN ('pending', 'processing', 'shipped', 'delivered', 'cancelled')),
    CONSTRAINT chk_orders_subtotal_nonneg CHECK (subtotal >= 0),
    CONSTRAINT chk_orders_tax_nonneg CHECK (tax_amount >= 0),
    CONSTRAINT chk_orders_shipping_nonneg CHECK (shipping_fee >= 0)
);


CREATE TABLE order_items (
    order_item_id           NVARCHAR(20) PRIMARY KEY,
    order_id                NVARCHAR(50) NOT NULL,
    product_id              INT NOT NULL,
    quantity                INT NOT NULL,
    unit_price_at_purchase  DECIMAL(10,2) NOT NULL,

    CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    CONSTRAINT chk_order_items_quantity_pos CHECK (quantity > 0),
    CONSTRAINT chk_order_items_price_nonneg CHECK (unit_price_at_purchase >= 0),
    CONSTRAINT uq_order_items_order_product UNIQUE (order_id, product_id)
);

CREATE TABLE categories (
    category_id   NVARCHAR(50) PRIMARY KEY,
    category_name NVARCHAR(100) NOT NULL
    description NVARCHAR(255) NOT NULL
);

CREATE TABLE products(
    product_id NVARCHAR(50) PRIMARY KEY,
    category_id NVARCHAR(50) NOT NULL,
    product_name NVARCHAR(100) NOT NULL,
    description NVARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE CASCADE
);

