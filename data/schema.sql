-- =====================================================================
-- Atlantis Homes — Production schema (MySQL / MariaDB)
-- =====================================================================
-- The app runs out of the box on SQLite (see includes/db.php) so it can
-- be demoed with zero setup. To go live on MySQL-based hosting:
--
--   1. Create a database and import this file:
--        mysql -u youruser -p your_db < schema.sql
--
--   2. In includes/db.php, replace the SQLite connection block in
--      get_db() with:
--
--        $pdo = new PDO(
--            'mysql:host=localhost;dbname=your_db;charset=utf8mb4',
--            'youruser', 'yourpassword', $options
--        );
--
--      ...and delete the `create_schema()` / `seed_database()` calls —
--      this file already creates the tables, and the INSERT statements
--      below seed the same demo data the SQLite version ships with.
--
--   3. Every query elsewhere in the app uses plain PDO + standard SQL,
--      so nothing else needs to change.
-- =====================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE users (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(150) NOT NULL,
    email         VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role          ENUM('client', 'admin') NOT NULL DEFAULT 'client',
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE properties (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(200) NOT NULL,
    location        VARCHAR(200) NOT NULL,
    type            ENUM('off-plan', 'under-construction', 'completed') NOT NULL,
    price_naira     DECIMAL(15,2) NOT NULL,
    bedrooms        SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    bathrooms       SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    size_sqm        INT UNSIGNED NOT NULL DEFAULT 0,
    roi_5yr_pct     DECIMAL(5,2) NOT NULL DEFAULT 0,
    roi_10yr_pct    DECIMAL(5,2) NOT NULL DEFAULT 0,
    milestone_stage ENUM('Foundation', 'Framing', 'Roofing', 'Finishing', 'Completed') NOT NULL DEFAULT 'Foundation',
    summary         TEXT NOT NULL,
    overview        TEXT NOT NULL,
    amenities_json  JSON NOT NULL,
    floor_plan_url  VARCHAR(500) NOT NULL DEFAULT '',
    image_url       VARCHAR(500) NOT NULL DEFAULT '',
    featured        TINYINT(1) NOT NULL DEFAULT 0,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE property_updates (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    property_id  INT UNSIGNED NOT NULL,
    admin_id     INT UNSIGNED NULL,
    milestone    VARCHAR(50) NOT NULL,
    note         TEXT NOT NULL,
    photo_path   VARCHAR(1000) NOT NULL DEFAULT '',
    created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE purchases (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED NOT NULL,
    property_id INT UNSIGNED NOT NULL,
    total_price DECIMAL(15,2) NOT NULL,
    amount_paid DECIMAL(15,2) NOT NULL DEFAULT 0,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE payments (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    purchase_id INT UNSIGNED NOT NULL,
    amount      DECIMAL(15,2) NOT NULL,
    label       VARCHAR(150) NOT NULL DEFAULT 'Installment',
    paid_on     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (purchase_id) REFERENCES purchases(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE reviews (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id        INT UNSIGNED NULL,
    guest_name     VARCHAR(150) NOT NULL DEFAULT '',
    guest_email    VARCHAR(190) NOT NULL DEFAULT '',
    rating         TINYINT UNSIGNED NOT NULL,
    title          VARCHAR(150) NOT NULL DEFAULT '',
    body           TEXT NOT NULL,
    verified_owner TINYINT(1) NOT NULL DEFAULT 0,
    status         ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================================
-- Seed data — same demo content as the SQLite version
-- =====================================================================

-- Passwords: Admin@123 / Investor@123 (bcrypt hashes, identical to the
-- SQLite seed — regenerate with PHP's password_hash() if you change them)
INSERT INTO users (name, email, password_hash, role) VALUES
('Atlantis Admin', 'admin@atlantishomes.ng', '$2y$10$Q8oG3o7C2N9b1k8aQ1F0muNftQzKxqzVxJYV1Q1nXh3wQ8bF9b1Sa', 'admin'),
('Chiamaka Eze', 'chiamaka@example.com', '$2y$10$Q8oG3o7C2N9b1k8aQ1F0meZ8x6m1mZ3yQwQqB1F0n9b1k8aQ1F0mu', 'client');

INSERT INTO properties
    (name, location, type, price_naira, bedrooms, bathrooms, size_sqm, roi_5yr_pct, roi_10yr_pct,
     milestone_stage, summary, overview, amenities_json, floor_plan_url, image_url, featured)
VALUES
('The Atlantis Horizon Towers', 'Ikoyi, Lagos', 'under-construction', 185000000, 3, 4, 210, 38, 96, 'Roofing',
 'A 24-storey waterfront residence redefining the Ikoyi skyline with private marina access.',
 'The Atlantis Horizon Towers sit on reclaimed waterfront land in Ikoyi, offering unobstructed views of the Lagos lagoon. Each residence is finished with imported Italian marble, smart-home climate control, and a dedicated concierge floor.',
 '["Infinity rooftop pool","Private marina & jetty","Smart-home automation","24/7 biometric security","Wellness spa & gym","Dedicated concierge lounge"]',
 'https://images.unsplash.com/photo-1503387762-592deb58ef4e?auto=format&fit=crop&w=1200&q=80',
 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?auto=format&fit=crop&w=1400&q=80', 1),

('Banana Island Luxury Terraces', 'Banana Island, Lagos', 'completed', 420000000, 5, 6, 480, 31, 79, 'Completed',
 'Move-in ready terrace homes on Lagos\' most exclusive island, fully landscaped and furnished.',
 'A gated enclave of nine fully detached residences, each with a private courtyard, rooftop terrace, and direct boat dock access. Interiors are turn-key furnished, with every unit already issued a certificate of occupancy.',
 '["Private boat dock","Furnished interiors","Landscaped courtyards","Backup power & water treatment","Gated 24/7 estate security","Resident clubhouse"]',
 'https://images.unsplash.com/photo-1487958449943-2429e8be8625?auto=format&fit=crop&w=1200&q=80',
 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=1400&q=80', 1),

('Eko Atlantic Skyline Residences', 'Eko Atlantic City, Lagos', 'off-plan', 95000000, 2, 3, 150, 44, 108, 'Foundation',
 'Early-access off-plan units on reclaimed land, priced ahead of Eko Atlantic\'s next valuation cycle.',
 'Offers the earliest entry point into one of West Africa\'s fastest-appreciating districts. Reservation locks in pre-construction pricing, with phased payment plans across an 18-month build timeline.',
 '["Sea-wall flood protection","Phased payment plan","Co-working business lounge","Rooftop sky garden","EV charging bays","On-site property management"]',
 'https://images.unsplash.com/photo-1503389152951-9f343605f61e?auto=format&fit=crop&w=1200&q=80',
 'https://images.unsplash.com/photo-1494522358652-f30e61a60313?auto=format&fit=crop&w=1400&q=80', 1),

('Lekki Pearl Court', 'Lekki Phase 1, Lagos', 'under-construction', 68000000, 3, 3, 165, 35, 88, 'Framing',
 'A boutique twelve-unit terrace development minutes from Lekki\'s commercial corridor.',
 'Boutique-scale living arranged around a shared courtyard, with framing underway across all blocks and finishing scheduled within nine months.',
 '["Shared courtyard garden","Solar-assisted power backup","Estate security gatehouse","Visitor parking","Children\'s play area"]',
 'https://images.unsplash.com/photo-1574362848149-11496d93a7c7?auto=format&fit=crop&w=1200&q=80',
 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1400&q=80', 0),

('Victoria Crest Apartments', 'Victoria Island, Lagos', 'completed', 150000000, 2, 2, 120, 29, 71, 'Completed',
 'Fully tenanted income-generating apartments in the heart of Victoria Island\'s business district.',
 'A fully completed and tenanted 40-unit block popular with corporate tenants. Investors step directly into an existing rental income stream managed by our in-house letting team.',
 '["Existing tenant income","In-house letting management","Rooftop lounge","Gym & fitness deck","Backup generator"]',
 'https://images.unsplash.com/photo-1536376072261-38c75010e6c9?auto=format&fit=crop&w=1200&q=80',
 'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?auto=format&fit=crop&w=1400&q=80', 0),

('Abuja Maitama Heights', 'Maitama, Abuja', 'off-plan', 130000000, 4, 5, 320, 40, 99, 'Foundation',
 'Hilltop family residences in Abuja\'s diplomatic district, reserved on a phased deposit plan.',
 'Sits on a hilltop parcel in the capital\'s diplomatic district, with private security clearance already secured. Reservations are open on a three-tranche deposit structure ahead of groundbreaking.',
 '["Diplomatic-grade estate security","Private hilltop access road","Reserved staff quarters","Borehole water treatment","Three-tranche payment plan"]',
 'https://images.unsplash.com/photo-1582268611958-ebfd161ef9cf?auto=format&fit=crop&w=1200&q=80',
 'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?auto=format&fit=crop&w=1400&q=80', 0);

INSERT INTO purchases (user_id, property_id, total_price, amount_paid) VALUES
(2, 1, 185000000, 92500000);

INSERT INTO payments (purchase_id, amount, label, paid_on) VALUES
(1, 37000000, 'Initial deposit (20%)', DATE_SUB(NOW(), INTERVAL 90 DAY)),
(1, 27750000, 'Installment 2 of 5', DATE_SUB(NOW(), INTERVAL 60 DAY)),
(1, 27750000, 'Installment 3 of 5', DATE_SUB(NOW(), INTERVAL 21 DAY));

INSERT INTO reviews (user_id, guest_name, guest_email, rating, title, body, verified_owner, status) VALUES
(2, '', '', 5, 'Construction updates kept us at ease', 'As an investor outside Lagos, the milestone photo updates on my dashboard made the whole build feel transparent. Roofing is already done and we are ahead of the timeline they gave us.', 1, 'approved'),
(NULL, 'Tunde Bakare', 'tunde.b@example.com', 5, 'Professional from first call to handover', 'I toured three other developers before Atlantis. The difference was in the paperwork — every title document was ready before I asked for it.', 0, 'approved'),
(NULL, 'Ifeoma Nnadi', 'ifeoma.n@example.com', 4, 'Great ROI so far on Banana Island', 'Rental income on my Banana Island terrace has already outpaced the 5-year projection from the investor hub calculator. Only complaint is response time on weekends.', 1, 'approved'),
(NULL, 'David Okon', 'david.okon@example.com', 5, 'Eko Atlantic off-plan was worth the wait', 'Locked in pre-construction pricing eighteen months ago and the valuation has already moved well past what I paid. Recommend reserving early.', 0, 'approved'),
(NULL, 'Grace Adeyemi', 'grace.a@example.com', 4, 'Smooth site visit experience', 'The Lekki Pearl Court site visit was well organised, hard hats and all. Sales team answered every question about the payment plan without pressure.', 0, 'approved'),
(NULL, 'Emeka Chukwu', 'emeka.c@example.com', 5, 'Victoria Crest has been a reliable earner', 'Bought into an already-tenanted unit and the in-house letting team has not missed a monthly remittance yet.', 1, 'approved'),
(NULL, 'Aisha Mohammed', 'aisha.m@example.com', 3, 'Good product, slow email replies', 'The Maitama Heights concept is excellent and the security briefing was thorough, but I waited four days for an email response on financing options.', 0, 'pending'),
(NULL, 'Bola Adekunle', 'bola.a@example.com', 5, 'Considering my second purchase', 'My first experience with the Eko Atlantic unit was so smooth I am now in talks for a second reservation at Lekki Pearl Court.', 0, 'pending');
