<?php
/**
 * Database bootstrap
 * -------------------
 * Default driver: SQLite (zero configuration — perfect for local/dev use
 * and for spinning the whole app up with nothing but `php -S`).
 *
 * To switch to MySQL for production (e.g. on cPanel/shared hosting):
 *   1. Import schema.sql into a MySQL database.
 *   2. Replace the DSN block below with:
 *        $dsn = "mysql:host=localhost;dbname=atlantis_homes;charset=utf8mb4";
 *        $pdo = new PDO($dsn, 'db_user', 'db_password', $options);
 *   3. Remove the seed_database() call (seed data ships via schema.sql instead).
 *
 * Every other file in the app talks to $pdo only through PDO's portable
 * API (prepared statements, no driver-specific SQL), so the swap above
 * is the only change required.
 */

define('DB_PATH', __DIR__ . '/../data/atlantis.sqlite');

function get_db(): PDO
{
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $isNewDatabase = !file_exists(DB_PATH);

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];

    $pdo = new PDO('sqlite:' . DB_PATH, null, null, $options);
    $pdo->exec('PRAGMA foreign_keys = ON');

    if ($isNewDatabase) {
        create_schema($pdo);
        seed_database($pdo);
    }

    return $pdo;
}

function create_schema(PDO $pdo): void
{
    $pdo->exec("
        CREATE TABLE users (
            id            INTEGER PRIMARY KEY AUTOINCREMENT,
            name          TEXT NOT NULL,
            email         TEXT NOT NULL UNIQUE,
            password_hash TEXT NOT NULL,
            role          TEXT NOT NULL DEFAULT 'client', -- 'client' | 'admin'
            created_at    TEXT NOT NULL DEFAULT (datetime('now'))
        );
    ");

    $pdo->exec("
        CREATE TABLE properties (
            id              INTEGER PRIMARY KEY AUTOINCREMENT,
            name            TEXT NOT NULL,
            location        TEXT NOT NULL,
            type            TEXT NOT NULL,         -- 'off-plan' | 'under-construction' | 'completed'
            price_naira     REAL NOT NULL,
            bedrooms        INTEGER NOT NULL DEFAULT 0,
            bathrooms       INTEGER NOT NULL DEFAULT 0,
            size_sqm        INTEGER NOT NULL DEFAULT 0,
            roi_5yr_pct     REAL NOT NULL DEFAULT 0,
            roi_10yr_pct    REAL NOT NULL DEFAULT 0,
            milestone_stage TEXT NOT NULL DEFAULT 'Foundation', -- Foundation|Framing|Roofing|Finishing|Completed
            summary         TEXT NOT NULL DEFAULT '',
            overview        TEXT NOT NULL DEFAULT '',
            amenities_json  TEXT NOT NULL DEFAULT '[]',
            floor_plan_url  TEXT NOT NULL DEFAULT '',
            image_url       TEXT NOT NULL DEFAULT '',
            featured        INTEGER NOT NULL DEFAULT 0,
            created_at      TEXT NOT NULL DEFAULT (datetime('now'))
        );
    ");

    $pdo->exec("
        CREATE TABLE property_updates (
            id           INTEGER PRIMARY KEY AUTOINCREMENT,
            property_id  INTEGER NOT NULL REFERENCES properties(id) ON DELETE CASCADE,
            admin_id     INTEGER REFERENCES users(id),
            milestone    TEXT NOT NULL,
            note         TEXT NOT NULL DEFAULT '',
            photo_path   TEXT NOT NULL DEFAULT '',
            created_at   TEXT NOT NULL DEFAULT (datetime('now'))
        );
    ");

    $pdo->exec("
        CREATE TABLE purchases (
            id              INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id         INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
            property_id     INTEGER NOT NULL REFERENCES properties(id) ON DELETE CASCADE,
            total_price     REAL NOT NULL,
            amount_paid     REAL NOT NULL DEFAULT 0,
            created_at      TEXT NOT NULL DEFAULT (datetime('now'))
        );
    ");

    $pdo->exec("
        CREATE TABLE payments (
            id           INTEGER PRIMARY KEY AUTOINCREMENT,
            purchase_id  INTEGER NOT NULL REFERENCES purchases(id) ON DELETE CASCADE,
            amount       REAL NOT NULL,
            label        TEXT NOT NULL DEFAULT 'Installment',
            paid_on      TEXT NOT NULL DEFAULT (datetime('now'))
        );
    ");

    $pdo->exec("
        CREATE TABLE reviews (
            id              INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id         INTEGER REFERENCES users(id),
            guest_name      TEXT NOT NULL DEFAULT '',
            guest_email     TEXT NOT NULL DEFAULT '',
            rating          INTEGER NOT NULL,
            title           TEXT NOT NULL DEFAULT '',
            body            TEXT NOT NULL,
            verified_owner  INTEGER NOT NULL DEFAULT 0,
            status          TEXT NOT NULL DEFAULT 'pending', -- pending | approved | rejected
            created_at      TEXT NOT NULL DEFAULT (datetime('now'))
        );
    ");

    $pdo->exec("
        CREATE TABLE inquiries (
            id              INTEGER PRIMARY KEY AUTOINCREMENT,
            type            TEXT NOT NULL,              -- 'booking' | 'contact' | 'estimate'
            name            TEXT NOT NULL,
            email           TEXT NOT NULL,
            phone           TEXT NOT NULL DEFAULT '',
            property_id     INTEGER REFERENCES properties(id),
            preferred_date  TEXT NOT NULL DEFAULT '',
            message         TEXT NOT NULL DEFAULT '',
            status          TEXT NOT NULL DEFAULT 'new', -- 'new' | 'contacted'
            created_at      TEXT NOT NULL DEFAULT (datetime('now'))
        );
    ");
}

function seed_database(PDO $pdo): void
{
    // --- Users -----------------------------------------------------------
    $admin = $pdo->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, 'admin')");
    $admin->execute(['Atlantis Admin', 'admin@atlantishomes.ng', password_hash('Admin@123', PASSWORD_DEFAULT)]);
    $adminId = (int) $pdo->lastInsertId();

    $client = $pdo->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, 'client')");
    $client->execute(['Chiamaka Eze', 'chiamaka@example.com', password_hash('Investor@123', PASSWORD_DEFAULT)]);
    $clientId = (int) $pdo->lastInsertId();

    // --- Properties --------------------------------------------------------
    $properties = [
        [
            'name' => 'The Atlantis Horizon Towers',
            'location' => 'Ikoyi, Lagos',
            'type' => 'under-construction',
            'price_naira' => 185000000,
            'bedrooms' => 3, 'bathrooms' => 4, 'size_sqm' => 210,
            'roi_5yr_pct' => 38, 'roi_10yr_pct' => 96,
            'milestone_stage' => 'Roofing',
            'summary' => 'A 24-storey waterfront residence redefining the Ikoyi skyline with private marina access.',
            'overview' => "The Atlantis Horizon Towers sit on reclaimed waterfront land in Ikoyi, offering unobstructed views of the Lagos lagoon. Each residence is finished with imported Italian marble, smart-home climate control, and a dedicated concierge floor. Construction is being delivered in four phases, with the first tower already at roofing stage.",
            'amenities' => ['Infinity rooftop pool', 'Private marina & jetty', 'Smart-home automation', '24/7 biometric security', 'Wellness spa & gym', 'Dedicated concierge lounge'],
            'floor_plan_url' => 'https://images.unsplash.com/photo-1503387762-592deb58ef4e?auto=format&fit=crop&w=1200&q=80',
            'image_url' => 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?auto=format&fit=crop&w=1400&q=80',
            'featured' => 1,
        ],
        [
            'name' => 'Banana Island Luxury Terraces',
            'location' => 'Banana Island, Lagos',
            'type' => 'completed',
            'price_naira' => 420000000,
            'bedrooms' => 5, 'bathrooms' => 6, 'size_sqm' => 480,
            'roi_5yr_pct' => 31, 'roi_10yr_pct' => 79,
            'milestone_stage' => 'Completed',
            'summary' => 'Move-in ready terrace homes on Lagos\' most exclusive island, fully landscaped and furnished.',
            'overview' => "Banana Island Luxury Terraces is a gated enclave of nine fully detached residences, each with a private courtyard, rooftop terrace, and direct boat dock access. Interiors are turn-key furnished by an award-winning design studio, with every unit already issued a certificate of occupancy.",
            'amenities' => ['Private boat dock', 'Furnished interiors', 'Landscaped courtyards', 'Backup power & water treatment', 'Gated 24/7 estate security', 'Resident clubhouse'],
            'floor_plan_url' => 'https://images.unsplash.com/photo-1487958449943-2429e8be8625?auto=format&fit=crop&w=1200&q=80',
            'image_url' => 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=1400&q=80',
            'featured' => 1,
        ],
        [
            'name' => 'Eko Atlantic Skyline Residences',
            'location' => 'Eko Atlantic City, Lagos',
            'type' => 'off-plan',
            'price_naira' => 95000000,
            'bedrooms' => 2, 'bathrooms' => 3, 'size_sqm' => 150,
            'roi_5yr_pct' => 44, 'roi_10yr_pct' => 108,
            'milestone_stage' => 'Foundation',
            'summary' => 'Early-access off-plan units on reclaimed land, priced ahead of Eko Atlantic\'s next valuation cycle.',
            'overview' => "Eko Atlantic Skyline Residences offers the earliest entry point into one of West Africa's fastest-appreciating districts. Reservation now locks in pre-construction pricing, with phased payment plans across an 18-month build timeline and full title documentation handled by our legal team.",
            'amenities' => ['Sea-wall flood protection', 'Phased payment plan', 'Co-working business lounge', 'Rooftop sky garden', 'EV charging bays', 'On-site property management'],
            'floor_plan_url' => 'https://images.unsplash.com/photo-1503389152951-9f343605f61e?auto=format&fit=crop&w=1200&q=80',
            'image_url' => 'https://images.unsplash.com/photo-1494522358652-f30e61a60313?auto=format&fit=crop&w=1400&q=80',
            'featured' => 1,
        ],
        [
            'name' => 'Lekki Pearl Court',
            'location' => 'Lekki Phase 1, Lagos',
            'type' => 'under-construction',
            'price_naira' => 68000000,
            'bedrooms' => 3, 'bathrooms' => 3, 'size_sqm' => 165,
            'roi_5yr_pct' => 35, 'roi_10yr_pct' => 88,
            'milestone_stage' => 'Framing',
            'summary' => 'A boutique twelve-unit terrace development minutes from Lekki\'s commercial corridor.',
            'overview' => "Lekki Pearl Court brings boutique-scale living to one of Lagos' busiest commercial corridors. Twelve terrace units are arranged around a shared courtyard, with framing underway across all blocks and finishing scheduled within nine months.",
            'amenities' => ['Shared courtyard garden', 'Solar-assisted power backup', 'Estate security gatehouse', 'Visitor parking', 'Children\'s play area'],
            'floor_plan_url' => 'https://images.unsplash.com/photo-1574362848149-11496d93a7c7?auto=format&fit=crop&w=1200&q=80',
            'image_url' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1400&q=80',
            'featured' => 0,
        ],
        [
            'name' => 'Victoria Crest Apartments',
            'location' => 'Victoria Island, Lagos',
            'type' => 'completed',
            'price_naira' => 150000000,
            'bedrooms' => 2, 'bathrooms' => 2, 'size_sqm' => 120,
            'roi_5yr_pct' => 29, 'roi_10yr_pct' => 71,
            'milestone_stage' => 'Completed',
            'summary' => 'Fully tenanted income-generating apartments in the heart of Victoria Island\'s business district.',
            'overview' => "Victoria Crest Apartments is a fully completed and tenanted 40-unit block in Victoria Island, popular with corporate tenants. Investors purchasing here step directly into an existing rental income stream managed by our in-house letting team.",
            'amenities' => ['Existing tenant income', 'In-house letting management', 'Rooftop lounge', 'Gym & fitness deck', 'Backup generator'],
            'floor_plan_url' => 'https://images.unsplash.com/photo-1536376072261-38c75010e6c9?auto=format&fit=crop&w=1200&q=80',
            'image_url' => 'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?auto=format&fit=crop&w=1400&q=80',
            'featured' => 0,
        ],
        [
            'name' => 'Abuja Maitama Heights',
            'location' => 'Maitama, Abuja',
            'type' => 'off-plan',
            'price_naira' => 130000000,
            'bedrooms' => 4, 'bathrooms' => 5, 'size_sqm' => 320,
            'roi_5yr_pct' => 40, 'roi_10yr_pct' => 99,
            'milestone_stage' => 'Foundation',
            'summary' => 'Hilltop family residences in Abuja\'s diplomatic district, reserved on a phased deposit plan.',
            'overview' => "Abuja Maitama Heights sits on a hilltop parcel in the capital's diplomatic district, with private security clearance already secured for the estate. Reservations are open on a three-tranche deposit structure ahead of groundbreaking next quarter.",
            'amenities' => ['Diplomatic-grade estate security', 'Private hilltop access road', 'Reserved staff quarters', 'Borehole water treatment', 'Three-tranche payment plan'],
            'floor_plan_url' => 'https://images.unsplash.com/photo-1582268611958-ebfd161ef9cf?auto=format&fit=crop&w=1200&q=80',
            'image_url' => 'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?auto=format&fit=crop&w=1400&q=80',
            'featured' => 0,
        ],
    ];

    $insertProperty = $pdo->prepare("
        INSERT INTO properties
            (name, location, type, price_naira, bedrooms, bathrooms, size_sqm, roi_5yr_pct, roi_10yr_pct,
             milestone_stage, summary, overview, amenities_json, floor_plan_url, image_url, featured)
        VALUES
            (:name, :location, :type, :price_naira, :bedrooms, :bathrooms, :size_sqm, :roi_5yr_pct, :roi_10yr_pct,
             :milestone_stage, :summary, :overview, :amenities_json, :floor_plan_url, :image_url, :featured)
    ");

    $propertyIds = [];
    foreach ($properties as $p) {
        $insertProperty->execute([
            ':name' => $p['name'], ':location' => $p['location'], ':type' => $p['type'],
            ':price_naira' => $p['price_naira'], ':bedrooms' => $p['bedrooms'], ':bathrooms' => $p['bathrooms'],
            ':size_sqm' => $p['size_sqm'], ':roi_5yr_pct' => $p['roi_5yr_pct'], ':roi_10yr_pct' => $p['roi_10yr_pct'],
            ':milestone_stage' => $p['milestone_stage'], ':summary' => $p['summary'], ':overview' => $p['overview'],
            ':amenities_json' => json_encode($p['amenities']), ':floor_plan_url' => $p['floor_plan_url'],
            ':image_url' => $p['image_url'], ':featured' => $p['featured'],
        ]);
        $propertyIds[] = (int) $pdo->lastInsertId();
    }

    // --- Demo purchase + ledger for the seeded client ---------------------
    $purchasePropertyId = $propertyIds[0]; // Atlantis Horizon Towers
    $pdo->prepare("INSERT INTO purchases (user_id, property_id, total_price, amount_paid) VALUES (?, ?, ?, ?)")
        ->execute([$clientId, $purchasePropertyId, 185000000, 92500000]);
    $purchaseId = (int) $pdo->lastInsertId();

    $paymentRows = [
        [37000000, 'Initial deposit (20%)', '-90 days'],
        [27750000, 'Installment 2 of 5', '-60 days'],
        [27750000, 'Installment 3 of 5', '-21 days'],
    ];
    $insertPayment = $pdo->prepare("INSERT INTO payments (purchase_id, amount, label, paid_on) VALUES (?, ?, ?, datetime('now', ?))");
    foreach ($paymentRows as $row) {
        $insertPayment->execute([$purchaseId, $row[0], $row[1], $row[2]]);
    }

    // --- Reviews ------------------------------------------------------------
    $reviews = [
        ['user_id' => $clientId, 'guest_name' => '', 'guest_email' => '', 'rating' => 5, 'verified_owner' => 1, 'status' => 'approved',
         'title' => 'Construction updates kept us at ease', 'body' => 'As an investor outside Lagos, the milestone photo updates on my dashboard made the whole build feel transparent. Roofing is already done and we are ahead of the timeline they gave us.'],
        ['user_id' => null, 'guest_name' => 'Tunde Bakare', 'guest_email' => 'tunde.b@example.com', 'rating' => 5, 'verified_owner' => 0, 'status' => 'approved',
         'title' => 'Professional from first call to handover', 'body' => 'I toured three other developers before Atlantis. The difference was in the paperwork — every title document was ready before I asked for it.'],
        ['user_id' => null, 'guest_name' => 'Ifeoma Nnadi', 'guest_email' => 'ifeoma.n@example.com', 'rating' => 4, 'verified_owner' => 1, 'status' => 'approved',
         'title' => 'Great ROI so far on Banana Island', 'body' => 'Rental income on my Banana Island terrace has already outpaced the 5-year projection from the investor hub calculator. Only complaint is response time on weekends.'],
        ['user_id' => null, 'guest_name' => 'David Okon', 'guest_email' => 'david.okon@example.com', 'rating' => 5, 'verified_owner' => 0, 'status' => 'approved',
         'title' => 'Eko Atlantic off-plan was worth the wait', 'body' => 'Locked in pre-construction pricing eighteen months ago and the valuation has already moved well past what I paid. Recommend reserving early.'],
        ['user_id' => null, 'guest_name' => 'Grace Adeyemi', 'guest_email' => 'grace.a@example.com', 'rating' => 4, 'verified_owner' => 0, 'status' => 'approved',
         'title' => 'Smooth site visit experience', 'body' => 'The Lekki Pearl Court site visit was well organised, hard hats and all. Sales team answered every question about the payment plan without pressure.'],
        ['user_id' => null, 'guest_name' => 'Emeka Chukwu', 'guest_email' => 'emeka.c@example.com', 'rating' => 5, 'verified_owner' => 1, 'status' => 'approved',
         'title' => 'Victoria Crest has been a reliable earner', 'body' => 'Bought into an already-tenanted unit and the in-house letting team has not missed a monthly remittance yet.'],
        ['user_id' => null, 'guest_name' => 'Aisha Mohammed', 'guest_email' => 'aisha.m@example.com', 'rating' => 3, 'verified_owner' => 0, 'status' => 'pending',
         'title' => 'Good product, slow email replies', 'body' => 'The Maitama Heights concept is excellent and the security briefing was thorough, but I waited four days for an email response on financing options.'],
        ['user_id' => null, 'guest_name' => 'Bola Adekunle', 'guest_email' => 'bola.a@example.com', 'rating' => 5, 'verified_owner' => 0, 'status' => 'pending',
         'title' => 'Considering my second purchase', 'body' => 'My first experience with the Eko Atlantic unit was so smooth I am now in talks for a second reservation at Lekki Pearl Court.'],
    ];

    $insertReview = $pdo->prepare("
        INSERT INTO reviews (user_id, guest_name, guest_email, rating, title, body, verified_owner, status)
        VALUES (:user_id, :guest_name, :guest_email, :rating, :title, :body, :verified_owner, :status)
    ");
    foreach ($reviews as $r) {
        $insertReview->execute([
            ':user_id' => $r['user_id'], ':guest_name' => $r['guest_name'], ':guest_email' => $r['guest_email'],
            ':rating' => $r['rating'], ':title' => $r['title'], ':body' => $r['body'],
            ':verified_owner' => $r['verified_owner'], ':status' => $r['status'],
        ]);
    }

    // --- Inquiries (bookings / contact messages / quote requests) ---------
    $insertInquiry = $pdo->prepare("
        INSERT INTO inquiries (type, name, email, phone, property_id, preferred_date, message, status)
        VALUES (:type, :name, :email, :phone, :property_id, :preferred_date, :message, :status)
    ");
    $insertInquiry->execute([
        ':type' => 'booking', ':name' => 'Funmi Okafor', ':email' => 'funmi.okafor@example.com',
        ':phone' => '+234 803 555 0192', ':property_id' => $propertyIds[2], ':preferred_date' => date('Y-m-d', strtotime('+5 days')),
        ':message' => 'Interested in a site tour of the Eko Atlantic units, ideally a weekend morning.', ':status' => 'new',
    ]);
    $insertInquiry->execute([
        ':type' => 'contact', ':name' => 'Patrick Nwosu', ':email' => 'patrick.nwosu@example.com',
        ':phone' => '+234 701 222 9981', ':property_id' => null, ':preferred_date' => '',
        ':message' => 'Do you have any payment plans that span beyond 24 months for off-plan units?', ':status' => 'contacted',
    ]);
}
