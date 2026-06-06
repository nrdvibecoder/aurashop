-- Aura Shop v2.0 Database Schema & Sample Seeding
-- Designed for PostgreSQL / Supabase

-- 1. Create Tables

CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    fullname VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(50) DEFAULT 'customer', -- 'customer' or 'admin'
    is_banned BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS categories (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL
);

CREATE TABLE IF NOT EXISTS products (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL,
    subcategory VARCHAR(100),
    description TEXT,
    price INT NOT NULL, -- Stored as integer (DA)
    discount INT DEFAULT 0, -- Percentage discount (0-100)
    stock INT DEFAULT 0,
    colors JSONB DEFAULT '[]', -- JSON array of swatches: [{"name": "Noir", "hex": "#000000"}]
    sizes JSONB DEFAULT '[]', -- JSON array of sizes: ["S", "M", "L"]
    image_url TEXT,
    base64_image TEXT, -- Client-side base64 encoded main image fallback
    color_images JSONB DEFAULT '{}', -- JSON mapping colors to arrays of images: {"Noir": ["base64...", "base64..."]}
    is_new_arrival BOOLEAN DEFAULT FALSE,
    is_featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS orders (
    id SERIAL PRIMARY KEY,
    order_number VARCHAR(100) UNIQUE NOT NULL,
    user_id INT REFERENCES users(id) ON DELETE SET NULL,
    fullname VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    wilaya VARCHAR(100) NOT NULL,
    commune VARCHAR(100) NOT NULL,
    address TEXT NOT NULL,
    delivery_method VARCHAR(50) NOT NULL, -- 'home' or 'relay'
    total_amount INT NOT NULL,
    discount_amount INT DEFAULT 0,
    delivery_fee INT DEFAULT 0,
    promo_code VARCHAR(100),
    status VARCHAR(50) DEFAULT 'Pending', -- 'Pending', 'Confirmed', 'Shipped', 'Delivered', 'Cancelled'
    notes TEXT,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS order_items (
    id SERIAL PRIMARY KEY,
    order_id INT REFERENCES orders(id) ON DELETE CASCADE,
    product_id INT REFERENCES products(id) ON DELETE SET NULL,
    product_name VARCHAR(255) NOT NULL,
    quantity INT NOT NULL,
    price INT NOT NULL,
    size VARCHAR(50),
    color VARCHAR(100),
    image_url TEXT
);

CREATE TABLE IF NOT EXISTS order_status_history (
    id SERIAL PRIMARY KEY,
    order_id INT REFERENCES orders(id) ON DELETE CASCADE,
    status VARCHAR(50) NOT NULL,
    note TEXT,
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS promo_codes (
    id SERIAL PRIMARY KEY,
    code VARCHAR(100) UNIQUE NOT NULL,
    type VARCHAR(50) NOT NULL, -- 'fixed' or 'percentage'
    value INT NOT NULL,
    min_order INT DEFAULT 0,
    max_uses INT,
    used_count INT DEFAULT 0,
    expires_at TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS delivery_zones (
    id SERIAL PRIMARY KEY,
    wilaya_code INT UNIQUE NOT NULL,
    wilaya_name VARCHAR(100) UNIQUE NOT NULL,
    home_fee INT NOT NULL,
    relay_fee INT NOT NULL,
    estimated_days INT DEFAULT 3
);

-- 2. Seed Initial Categories
INSERT INTO categories (name) VALUES 
('Women'), 
('Men'), 
('Accessories'), 
('Unisex')
ON CONFLICT (name) DO NOTHING;

-- 3. Seed Initial Delivery Zones (58 Algerian Wilayas)
INSERT INTO delivery_zones (wilaya_code, wilaya_name, home_fee, relay_fee, estimated_days) VALUES
(1, 'Adrar', 1000, 600, 5),
(2, 'Chlef', 600, 350, 3),
(3, 'Laghouat', 700, 450, 4),
(4, 'Oum El Bouaghi', 600, 350, 3),
(5, 'Batna', 600, 350, 3),
(6, 'Béjaïa', 600, 350, 2),
(7, 'Biskra', 700, 400, 3),
(8, 'Béchar', 850, 500, 4),
(9, 'Blida', 400, 200, 1),
(10, 'Bouira', 500, 300, 2),
(11, 'Tamanrasset', 1200, 800, 6),
(12, 'Tébessa', 650, 400, 3),
(13, 'Tlemcen', 650, 400, 3),
(14, 'Tiaret', 650, 400, 3),
(15, 'Tizi Ouzou', 500, 300, 2),
(16, 'Alger', 350, 150, 1),
(17, 'Djelfa', 650, 400, 3),
(18, 'Jijel', 600, 350, 3),
(19, 'Sétif', 550, 350, 2),
(20, 'Saïda', 650, 400, 3),
(21, 'Skikda', 600, 350, 3),
(22, 'Sidi Bel Abbès', 650, 400, 3),
(23, 'Guelma', 600, 350, 3),
(24, 'Constantine', 550, 350, 2),
(25, 'Médéa', 500, 300, 2),
(26, 'Mostaganem', 600, 350, 3),
(27, 'M''Sila', 600, 400, 3),
(28, 'Mascara', 600, 350, 3),
(29, 'Ouargla', 750, 450, 4),
(30, 'Oran', 500, 300, 2),
(31, 'El Bayadh', 800, 500, 4),
(32, 'Illizi', 1200, 800, 6),
(33, 'Bordj Bou Arréridj', 550, 350, 2),
(34, 'Boumerdès', 400, 200, 1),
(35, 'El Tarf', 650, 400, 3),
(36, 'Tindouf', 1200, 800, 6),
(37, 'Tissemsilt', 600, 350, 3),
(38, 'El Oued', 750, 450, 4),
(39, 'Khenchela', 600, 350, 3),
(40, 'Souk Ahras', 600, 350, 3),
(41, 'Tipaza', 450, 250, 1),
(42, 'Mila', 600, 350, 3),
(43, 'Aïn Defla', 550, 300, 2),
(44, 'Naâma', 800, 500, 4),
(45, 'Aïn Témouchent', 600, 350, 3),
(46, 'Ghardaïa', 750, 450, 4),
(47, 'Relizane', 600, 350, 3),
(48, 'El M''Ghair', 750, 450, 4),
(49, 'Ouled Djellal', 700, 400, 3),
(50, 'Bordj Baji Mokhtar', 1500, 1000, 7),
(51, 'Béni Abbès', 900, 550, 5),
(52, 'In Salah', 1200, 800, 6),
(53, 'In Guezzam', 1500, 1000, 7),
(54, 'Touggourt', 750, 450, 4),
(55, 'Djanet', 1300, 900, 6),
(56, 'El M''Ghair', 750, 450, 4),
(57, 'El Meniaa', 800, 500, 4),
(58, 'Ouled Djellal', 700, 400, 3),
(59, 'Aflou', 600, 350, 3),
(60, 'El Abiodh Sidi Cheikh', 800, 500, 4),
(61, 'El Aricha', 700, 400, 3),
(62, 'El Kantara', 600, 350, 3),
(63, 'Barika', 600, 350, 3),
(64, 'Bou Saâda', 600, 350, 3),
(65, 'Bir El Ater', 700, 400, 3),
(66, 'Ksar El Boukhari', 600, 350, 3),
(67, 'Ksar Chellala', 600, 350, 3),
(68, 'Aïn Oussara', 600, 350, 3),
(69, 'Messaad', 650, 400, 3)
ON CONFLICT (wilaya_code) DO UPDATE SET 
home_fee = EXCLUDED.home_fee, 
relay_fee = EXCLUDED.relay_fee, 
estimated_days = EXCLUDED.estimated_days;
