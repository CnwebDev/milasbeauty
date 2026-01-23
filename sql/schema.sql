-- ASA Parfums product admin schema
-- MySQL 8+
CREATE TABLE admins (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        email VARCHAR(150) UNIQUE NOT NULL,
                        password VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS products (
                                        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                                        name VARCHAR(160) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    short_description VARCHAR(255) NULL,
    description TEXT NULL,
    price DECIMAL(10,2) NULL,
    volume_ml INT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    main_image VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS product_images (
                                              id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                                              product_id INT UNSIGNED NOT NULL,
                                              image_path VARCHAR(255) NOT NULL,
    sort_order TINYINT UNSIGNED NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_product_images_product
    FOREIGN KEY (product_id) REFERENCES products(id)
    ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS product_sizes (
                                             id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                                             product_id INT UNSIGNED NOT NULL,
                                             size_label VARCHAR(80) NOT NULL,
    sort_order TINYINT UNSIGNED NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_product_sizes_product
    FOREIGN KEY (product_id) REFERENCES products(id)
    ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS product_colors (
                                              id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                                              product_id INT UNSIGNED NOT NULL,
                                              color_label VARCHAR(80) NOT NULL,
    sort_order TINYINT UNSIGNED NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_product_colors_product
    FOREIGN KEY (product_id) REFERENCES products(id)
    ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Relaties (many-to-many, directed: A -> B)
CREATE TABLE IF NOT EXISTS product_relations (
                                                 product_id INT UNSIGNED NOT NULL,
                                                 related_product_id INT UNSIGNED NOT NULL,
                                                 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                                 PRIMARY KEY (product_id, related_product_id),
    CONSTRAINT fk_rel_product
    FOREIGN KEY (product_id) REFERENCES products(id)
    ON DELETE CASCADE,
    CONSTRAINT fk_rel_related
    FOREIGN KEY (related_product_id) REFERENCES products(id)
    ON DELETE CASCADE,
    CONSTRAINT chk_not_self CHECK (product_id <> related_product_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_product_images_product ON product_images(product_id);
CREATE INDEX idx_product_sizes_product ON product_sizes(product_id);
CREATE INDEX idx_product_colors_product ON product_colors(product_id);
CREATE INDEX idx_products_active ON products(is_active);

CREATE TABLE IF NOT EXISTS orders (
                                       id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                                       order_token CHAR(32) NOT NULL,
                                       status VARCHAR(20) NOT NULL DEFAULT 'pending',
                                       payment_status VARCHAR(20) NULL,
                                       mollie_payment_id VARCHAR(64) NULL,
                                       subtotal DECIMAL(10,2) NOT NULL,
                                       shipping DECIMAL(10,2) NOT NULL,
                                       total DECIMAL(10,2) NOT NULL,
                                       currency CHAR(3) NOT NULL DEFAULT 'EUR',
                                       first_name VARCHAR(100) NOT NULL,
                                       last_name VARCHAR(100) NOT NULL,
                                       email VARCHAR(200) NOT NULL,
                                       phone VARCHAR(50) NULL,
                                       address VARCHAR(200) NOT NULL,
                                       zip VARCHAR(20) NOT NULL,
                                       city VARCHAR(100) NOT NULL,
                                       country VARCHAR(100) NOT NULL,
                                       notes TEXT NULL,
                                       paid_at DATETIME NULL,
                                       email_sent_at DATETIME NULL,
                                       created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                       updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                                       UNIQUE KEY idx_orders_token (order_token),
                                       KEY idx_orders_status (status),
                                       KEY idx_orders_payment (mollie_payment_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS order_items (
                                           id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                                           order_id INT UNSIGNED NOT NULL,
                                           product_id INT UNSIGNED NULL,
                                           product_name VARCHAR(160) NOT NULL,
                                           price DECIMAL(10,2) NOT NULL,
                                           qty INT UNSIGNED NOT NULL,
                                           line_total DECIMAL(10,2) NOT NULL,
                                           volume_ml INT NULL,
                                           main_image VARCHAR(255) NULL,
                                           created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                           CONSTRAINT fk_order_items_order
                                               FOREIGN KEY (order_id) REFERENCES orders(id)
                                                   ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_order_items_order ON order_items(order_id);

CREATE TABLE IF NOT EXISTS contact_details (
                                               id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                                               phone VARCHAR(50) NULL,
                                               email VARCHAR(200) NULL,
                                               whatsapp VARCHAR(50) NULL,
                                               address_line VARCHAR(200) NULL,
                                               postal_code VARCHAR(20) NULL,
                                               city VARCHAR(100) NULL,
                                               country VARCHAR(100) NULL,
                                               created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                               updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS social_media_links (
                                                  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                                                  platform VARCHAR(80) NOT NULL,
                                                  url VARCHAR(255) NOT NULL,
                                                  is_active TINYINT(1) NOT NULL DEFAULT 1,
                                                  sort_order INT UNSIGNED NOT NULL DEFAULT 0,
                                                  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                                  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                                                  UNIQUE KEY uniq_social_platform (platform)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS opening_hours (
                                             id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                                             day_of_week TINYINT UNSIGNED NOT NULL,
                                             opens_at TIME NULL,
                                             closes_at TIME NULL,
                                             is_closed TINYINT(1) NOT NULL DEFAULT 0,
                                             note VARCHAR(255) NULL,
                                             created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                             updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                                             UNIQUE KEY uniq_opening_day (day_of_week)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS services (
                                        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                                        title VARCHAR(160) NOT NULL,
                                        description TEXT NULL,
                                        image_path VARCHAR(255) NULL,
                                        is_active TINYINT(1) NOT NULL DEFAULT 1,
                                        sort_order INT UNSIGNED NOT NULL DEFAULT 0,
                                        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS faqs (
                                    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                                    question VARCHAR(255) NOT NULL,
                                    answer TEXT NOT NULL,
                                    is_active TINYINT(1) NOT NULL DEFAULT 1,
                                    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
                                    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_social_media_links_active ON social_media_links(is_active);
CREATE INDEX idx_services_active ON services(is_active);
CREATE INDEX idx_faqs_active ON faqs(is_active);
