-- SQLite Database Export
-- Generated: 2026-06-17 17:45:28

BEGIN TRANSACTION;

-- Table: inquiries
CREATE TABLE inquiries (
            id              INTEGER PRIMARY KEY AUTOINCREMENT,
            type            TEXT NOT NULL,              -- 'booking' | 'contact' | 'estimate' | 'investment'
            user_id         INTEGER REFERENCES users(id),
            name            TEXT NOT NULL,
            email           TEXT NOT NULL,
            phone           TEXT NOT NULL DEFAULT '',
            property_id     INTEGER REFERENCES properties(id),
            preferred_date  TEXT NOT NULL DEFAULT '',
            message         TEXT NOT NULL DEFAULT '',
            status          TEXT NOT NULL DEFAULT 'new', -- 'new' | 'contacted'
            created_at      TEXT NOT NULL DEFAULT (datetime('now'))
        , investment_id INTEGER REFERENCES investment_opportunities(id), spec_details TEXT NOT NULL DEFAULT '');
INSERT INTO "inquiries" ("id", "type", "user_id", "name", "email", "phone", "property_id", "preferred_date", "message", "status", "created_at", "investment_id", "spec_details") VALUES ('1', 'booking', NULL, 'Funmi Okafor', 'funmi.okafor@example.com', '+234 803 555 0192', '3', '2026-06-22', 'Interested in a site tour of the Eko Atlantic units, ideally a weekend morning.', 'new', '2026-06-17 06:52:49', NULL, '');
INSERT INTO "inquiries" ("id", "type", "user_id", "name", "email", "phone", "property_id", "preferred_date", "message", "status", "created_at", "investment_id", "spec_details") VALUES ('2', 'contact', NULL, 'Patrick Nwosu', 'patrick.nwosu@example.com', '+234 701 222 9981', NULL, '', 'Do you have any payment plans that span beyond 24 months for off-plan units?', 'contacted', '2026-06-17 06:52:49', NULL, '');
INSERT INTO "inquiries" ("id", "type", "user_id", "name", "email", "phone", "property_id", "preferred_date", "message", "status", "created_at", "investment_id", "spec_details") VALUES ('3', 'estimate', '2', 'Chiamaka Eze', 'chiamaka@example.com', '+2348108833188', NULL, '', 'Build estimate request:
3-bedroom Bungalow, 1-floor, 220 sqm, Standard finish, Lagos.

Itemised estimate:
- Foundation & Substructure: ₦5,566,000
- Structural Framing (Block & Concrete Work): ₦10,626,000
- Roofing: ₦4,554,000
- Electrical Wiring & Fittings: ₦2,783,000
- Plumbing & Water Systems: ₦2,403,500
- Walls, Plastering & Screeding: ₦4,048,000
- Doors, Windows & Glazing: ₦3,542,000
- Flooring & Tiling: ₦3,289,000
- Painting & Finishing: ₦1,897,500
- Kitchen & Fittings: ₦2,277,000
- Landscaping & External Works: ₦1,391,500

Subtotal: ₦42,377,500
Fees (8%): ₦3,390,200
Contingency (5%): ₦2,118,875
Grand Total: ₦47,886,575

Please send a formal, fixed-price quote for this specification.', 'new', '2026-06-17 10:15:45', NULL, '');
INSERT INTO "inquiries" ("id", "type", "user_id", "name", "email", "phone", "property_id", "preferred_date", "message", "status", "created_at", "investment_id", "spec_details") VALUES ('4', 'estimate', NULL, 'Ezea Ugochukwu micheal', 'spotwebdev.com@gmail.com', '+2348108833188', NULL, '', 'Build estimate request:
3-bedroom Bungalow, 1-floor, 220 sqm, Standard finish, Lagos.

Itemised estimate:
- Foundation & Substructure: ₦5,566,000
- Structural Framing (Block & Concrete Work): ₦10,626,000
- Roofing: ₦4,554,000
- Electrical Wiring & Fittings: ₦2,783,000
- Plumbing & Water Systems: ₦2,403,500
- Walls, Plastering & Screeding: ₦4,048,000
- Doors, Windows & Glazing: ₦3,542,000
- Flooring & Tiling: ₦3,289,000
- Painting & Finishing: ₦1,897,500
- Kitchen & Fittings: ₦2,277,000
- Landscaping & External Works: ₦1,391,500

Subtotal: ₦42,377,500
Fees (8%): ₦3,390,200
Contingency (5%): ₦2,118,875
Grand Total: ₦47,886,575

Please send a formal, fixed-price quote for this specification.', 'new', '2026-06-17 10:16:11', NULL, '');
INSERT INTO "inquiries" ("id", "type", "user_id", "name", "email", "phone", "property_id", "preferred_date", "message", "status", "created_at", "investment_id", "spec_details") VALUES ('5', 'investment', '3', 'Ezea Ugochukwu micheal', 'spotwebdev.com@gmail.com', '08108833189', '3', '', 'Investment reservation request via the Investor Hub calculator:
Property: Eko Atlantic Skyline Residences — Eko Atlantic City, Lagos
Investment amount: ₦25,000,000
Development type: off-plan
Expected annual rental yield: 7.5%

Projected 5-year value: ₦44,875,000 (79.5% return)
Projected 10-year value: ₦69,160,000 (176.6% return)

Please follow up to finalise the contract and payment schedule.', 'contacted', '2026-06-17 10:17:45', NULL, '');
INSERT INTO "inquiries" ("id", "type", "user_id", "name", "email", "phone", "property_id", "preferred_date", "message", "status", "created_at", "investment_id", "spec_details") VALUES ('6', 'estimate', '3', 'Ezea Ugochukwu micheal', 'spotwebdev.com@gmail.com', '+2348108833188', NULL, '', 'Build estimate request:
3-bedroom Duplex, 1-floor, 220 sqm, Standard finish, Abuja.

Itemised estimate:
- Foundation & Substructure: ₦5,749,920
- Structural Framing (Block & Concrete Work): ₦10,977,120
- Roofing: ₦4,704,480
- Electrical Wiring & Fittings: ₦2,874,960
- Plumbing & Water Systems: ₦2,482,920
- Walls, Plastering & Screeding: ₦4,181,760
- Doors, Windows & Glazing: ₦3,659,040
- Flooring & Tiling: ₦3,397,680
- Painting & Finishing: ₦1,960,200
- Kitchen & Fittings: ₦2,352,240
- Landscaping & External Works: ₦1,437,480

Subtotal: ₦43,777,800
Fees (8%): ₦3,502,224
Contingency (5%): ₦2,188,890
Grand Total: ₦49,468,914

Please send a formal, fixed-price quote for this specification.', 'new', '2026-06-17 16:27:01', NULL, '');
INSERT INTO "inquiries" ("id", "type", "user_id", "name", "email", "phone", "property_id", "preferred_date", "message", "status", "created_at", "investment_id", "spec_details") VALUES ('7', 'investment', '3', 'Ezea Ugochukwu micheal', 'spotwebdev.com@gmail.com', '+2348108833188', NULL, '', 'Investment reservation request from the Investor Hub.
Opportunity: Epe Land Bank — Phase 1
Amount: ₦5,000,000
Expected ROI: 65%
Term: 48 months

Please follow up to finalise the contract and payment schedule.', 'contacted', '2026-06-17 16:27:54', NULL, '');

-- Table: investment_opportunities
CREATE TABLE investment_opportunities (
                id              INTEGER PRIMARY KEY AUTOINCREMENT,
                type            TEXT NOT NULL,
                name            TEXT NOT NULL,
                location        TEXT NOT NULL DEFAULT '',
                description     TEXT NOT NULL DEFAULT '',
                min_investment  REAL NOT NULL DEFAULT 0,
                target_amount   REAL NOT NULL DEFAULT 0,
                amount_raised   REAL NOT NULL DEFAULT 0,
                expected_roi_pct REAL NOT NULL DEFAULT 0,
                term_months     INTEGER NOT NULL DEFAULT 0,
                image_url       TEXT NOT NULL DEFAULT '',
                status          TEXT NOT NULL DEFAULT 'open',
                created_at      TEXT NOT NULL DEFAULT (datetime('now'))
            );
INSERT INTO "investment_opportunities" ("id", "type", "name", "location", "description", "min_investment", "target_amount", "amount_raised", "expected_roi_pct", "term_months", "image_url", "status", "created_at") VALUES ('1', 'company', 'Atlantis Homes Growth Fund — Series II', '', 'A pooled investment directly in Atlantis Homes Ltd, funding land acquisition and construction across our active pipeline. Returns are paid from company-wide project profits rather than tied to any single building.', '2000000', '500000000', '184500000', '22', '36', 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=1200&q=80', 'open', '2026-06-17 16:04:41');
INSERT INTO "investment_opportunities" ("id", "type", "name", "location", "description", "min_investment", "target_amount", "amount_raised", "expected_roi_pct", "term_months", "image_url", "status", "created_at") VALUES ('2', 'property', 'Epe Land Bank — Phase 1', 'Epe, Lagos', 'A land-banking opportunity on a 12-hectare parcel along the Lagos-Epe corridor, acquired ahead of planned road and bridge infrastructure. This is a land-appreciation play, not a home purchase — no construction is planned during the holding period.', '5000000', '300000000', '95000000', '65', '48', 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=1200&q=80', 'open', '2026-06-17 16:04:41');
INSERT INTO "investment_opportunities" ("id", "type", "name", "location", "description", "min_investment", "target_amount", "amount_raised", "expected_roi_pct", "term_months", "image_url", "status", "created_at") VALUES ('3', 'property', 'Ajah Rental Income Block (Shares)', 'Ajah, Lagos', 'Fractional shares in a fully tenanted 24-unit rental block. Investors hold a share of the rental income and resale value rather than owning a unit outright — distinct from buying a home in our portfolio.', '3000000', '180000000', '180000000', '19', '24', 'https://images.unsplash.com/photo-1460317442991-0ec209397118?auto=format&fit=crop&w=1200&q=80', 'closed', '2026-06-17 16:04:41');

-- Table: payments
CREATE TABLE payments (
            id           INTEGER PRIMARY KEY AUTOINCREMENT,
            purchase_id  INTEGER NOT NULL REFERENCES purchases(id) ON DELETE CASCADE,
            amount       REAL NOT NULL,
            label        TEXT NOT NULL DEFAULT 'Installment',
            paid_on      TEXT NOT NULL DEFAULT (datetime('now'))
        );
INSERT INTO "payments" ("id", "purchase_id", "amount", "label", "paid_on") VALUES ('1', '1', '37000000', 'Initial deposit (20%)', '2026-03-19 06:52:49');
INSERT INTO "payments" ("id", "purchase_id", "amount", "label", "paid_on") VALUES ('2', '1', '27750000', 'Installment 2 of 5', '2026-04-18 06:52:49');
INSERT INTO "payments" ("id", "purchase_id", "amount", "label", "paid_on") VALUES ('3', '1', '27750000', 'Installment 3 of 5', '2026-05-27 06:52:49');
INSERT INTO "payments" ("id", "purchase_id", "amount", "label", "paid_on") VALUES ('4', '2', '20000', 'Initial deposit', '2026-06-17 10:19:07');

-- Table: properties
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
INSERT INTO "properties" ("id", "name", "location", "type", "price_naira", "bedrooms", "bathrooms", "size_sqm", "roi_5yr_pct", "roi_10yr_pct", "milestone_stage", "summary", "overview", "amenities_json", "floor_plan_url", "image_url", "featured", "created_at") VALUES ('1', 'The Atlantis Horizon Towers', 'Ikoyi, Lagos', 'under-construction', '185000000', '3', '4', '210', '38', '96', 'Roofing', 'A 24-storey waterfront residence redefining the Ikoyi skyline with private marina access.', 'The Atlantis Horizon Towers sit on reclaimed waterfront land in Ikoyi, offering unobstructed views of the Lagos lagoon. Each residence is finished with imported Italian marble, smart-home climate control, and a dedicated concierge floor. Construction is being delivered in four phases, with the first tower already at roofing stage.', '["Infinity rooftop pool","Private marina & jetty","Smart-home automation","24\/7 biometric security","Wellness spa & gym","Dedicated concierge lounge"]', 'https://images.unsplash.com/photo-1503387762-592deb58ef4e?auto=format&fit=crop&w=1200&q=80', 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?auto=format&fit=crop&w=1400&q=80', '1', '2026-06-17 06:52:49');
INSERT INTO "properties" ("id", "name", "location", "type", "price_naira", "bedrooms", "bathrooms", "size_sqm", "roi_5yr_pct", "roi_10yr_pct", "milestone_stage", "summary", "overview", "amenities_json", "floor_plan_url", "image_url", "featured", "created_at") VALUES ('2', 'Banana Island Luxury Terraces', 'Banana Island, Lagos', 'completed', '420000000', '5', '6', '480', '31', '79', 'Completed', 'Move-in ready terrace homes on Lagos'' most exclusive island, fully landscaped and furnished.', 'Banana Island Luxury Terraces is a gated enclave of nine fully detached residences, each with a private courtyard, rooftop terrace, and direct boat dock access. Interiors are turn-key furnished by an award-winning design studio, with every unit already issued a certificate of occupancy.', '["Private boat dock","Furnished interiors","Landscaped courtyards","Backup power & water treatment","Gated 24\/7 estate security","Resident clubhouse"]', 'https://images.unsplash.com/photo-1487958449943-2429e8be8625?auto=format&fit=crop&w=1200&q=80', 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=1400&q=80', '1', '2026-06-17 06:52:49');
INSERT INTO "properties" ("id", "name", "location", "type", "price_naira", "bedrooms", "bathrooms", "size_sqm", "roi_5yr_pct", "roi_10yr_pct", "milestone_stage", "summary", "overview", "amenities_json", "floor_plan_url", "image_url", "featured", "created_at") VALUES ('3', 'Eko Atlantic Skyline Residences', 'Eko Atlantic City, Lagos', 'off-plan', '95000000', '2', '3', '150', '44', '108', 'Foundation', 'Early-access off-plan units on reclaimed land, priced ahead of Eko Atlantic''s next valuation cycle.', 'Eko Atlantic Skyline Residences offers the earliest entry point into one of West Africa''s fastest-appreciating districts. Reservation now locks in pre-construction pricing, with phased payment plans across an 18-month build timeline and full title documentation handled by our legal team.', '["Sea-wall flood protection","Phased payment plan","Co-working business lounge","Rooftop sky garden","EV charging bays","On-site property management"]', 'https://images.unsplash.com/photo-1503389152951-9f343605f61e?auto=format&fit=crop&w=1200&q=80', 'https://images.unsplash.com/photo-1494522358652-f30e61a60313?auto=format&fit=crop&w=1400&q=80', '1', '2026-06-17 06:52:49');
INSERT INTO "properties" ("id", "name", "location", "type", "price_naira", "bedrooms", "bathrooms", "size_sqm", "roi_5yr_pct", "roi_10yr_pct", "milestone_stage", "summary", "overview", "amenities_json", "floor_plan_url", "image_url", "featured", "created_at") VALUES ('4', 'Lekki Pearl Court', 'Lekki Phase 1, Lagos', 'under-construction', '68000000', '3', '3', '165', '35', '88', 'Framing', 'A boutique twelve-unit terrace development minutes from Lekki''s commercial corridor.', 'Lekki Pearl Court brings boutique-scale living to one of Lagos'' busiest commercial corridors. Twelve terrace units are arranged around a shared courtyard, with framing underway across all blocks and finishing scheduled within nine months.', '["Shared courtyard garden","Solar-assisted power backup","Estate security gatehouse","Visitor parking","Children''s play area"]', 'https://images.unsplash.com/photo-1574362848149-11496d93a7c7?auto=format&fit=crop&w=1200&q=80', 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1400&q=80', '0', '2026-06-17 06:52:49');
INSERT INTO "properties" ("id", "name", "location", "type", "price_naira", "bedrooms", "bathrooms", "size_sqm", "roi_5yr_pct", "roi_10yr_pct", "milestone_stage", "summary", "overview", "amenities_json", "floor_plan_url", "image_url", "featured", "created_at") VALUES ('5', 'Victoria Crest Apartments', 'Victoria Island, Lagos', 'completed', '150000000', '2', '2', '120', '29', '71', 'Completed', 'Fully tenanted income-generating apartments in the heart of Victoria Island''s business district.', 'Victoria Crest Apartments is a fully completed and tenanted 40-unit block in Victoria Island, popular with corporate tenants. Investors purchasing here step directly into an existing rental income stream managed by our in-house letting team.', '["Existing tenant income","In-house letting management","Rooftop lounge","Gym & fitness deck","Backup generator"]', 'https://images.unsplash.com/photo-1536376072261-38c75010e6c9?auto=format&fit=crop&w=1200&q=80', 'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?auto=format&fit=crop&w=1400&q=80', '0', '2026-06-17 06:52:49');
INSERT INTO "properties" ("id", "name", "location", "type", "price_naira", "bedrooms", "bathrooms", "size_sqm", "roi_5yr_pct", "roi_10yr_pct", "milestone_stage", "summary", "overview", "amenities_json", "floor_plan_url", "image_url", "featured", "created_at") VALUES ('6', 'Abuja Maitama Heights', 'Maitama, Abuja', 'off-plan', '130000000', '4', '5', '320', '40', '99', 'Foundation', 'Hilltop family residences in Abuja''s diplomatic district, reserved on a phased deposit plan.', 'Abuja Maitama Heights sits on a hilltop parcel in the capital''s diplomatic district, with private security clearance already secured for the estate. Reservations are open on a three-tranche deposit structure ahead of groundbreaking next quarter.', '["Diplomatic-grade estate security","Private hilltop access road","Reserved staff quarters","Borehole water treatment","Three-tranche payment plan"]', 'https://images.unsplash.com/photo-1582268611958-ebfd161ef9cf?auto=format&fit=crop&w=1200&q=80', 'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?auto=format&fit=crop&w=1400&q=80', '0', '2026-06-17 06:52:49');
INSERT INTO "properties" ("id", "name", "location", "type", "price_naira", "bedrooms", "bathrooms", "size_sqm", "roi_5yr_pct", "roi_10yr_pct", "milestone_stage", "summary", "overview", "amenities_json", "floor_plan_url", "image_url", "featured", "created_at") VALUES ('7', 'Enugu', 'Enugu', 'off-plan', '3000000', '3', '3', '150', '35', '85', 'Foundation', 'a build', 'a building', '[]', '', 'assets/uploads/property-1781713773-30b4cd06.png', '1', '2026-06-17 16:29:33');

-- Table: property_updates
CREATE TABLE property_updates (
            id           INTEGER PRIMARY KEY AUTOINCREMENT,
            property_id  INTEGER NOT NULL REFERENCES properties(id) ON DELETE CASCADE,
            admin_id     INTEGER REFERENCES users(id),
            milestone    TEXT NOT NULL,
            note         TEXT NOT NULL DEFAULT '',
            photo_path   TEXT NOT NULL DEFAULT '',
            created_at   TEXT NOT NULL DEFAULT (datetime('now'))
        );

-- Table: purchases
CREATE TABLE purchases (
            id              INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id         INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
            property_id     INTEGER NOT NULL REFERENCES properties(id) ON DELETE CASCADE,
            total_price     REAL NOT NULL,
            amount_paid     REAL NOT NULL DEFAULT 0,
            created_at      TEXT NOT NULL DEFAULT (datetime('now'))
        );
INSERT INTO "purchases" ("id", "user_id", "property_id", "total_price", "amount_paid", "created_at") VALUES ('1', '2', '1', '185000000', '92500000', '2026-06-17 06:52:49');
INSERT INTO "purchases" ("id", "user_id", "property_id", "total_price", "amount_paid", "created_at") VALUES ('2', '3', '6', '130000000', '20000', '2026-06-17 10:19:07');

-- Table: reviews
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
INSERT INTO "reviews" ("id", "user_id", "guest_name", "guest_email", "rating", "title", "body", "verified_owner", "status", "created_at") VALUES ('1', '2', '', '', '5', 'Construction updates kept us at ease', 'As an investor outside Lagos, the milestone photo updates on my dashboard made the whole build feel transparent. Roofing is already done and we are ahead of the timeline they gave us.', '1', 'approved', '2026-06-17 06:52:49');
INSERT INTO "reviews" ("id", "user_id", "guest_name", "guest_email", "rating", "title", "body", "verified_owner", "status", "created_at") VALUES ('2', NULL, 'Tunde Bakare', 'tunde.b@example.com', '5', 'Professional from first call to handover', 'I toured three other developers before Atlantis. The difference was in the paperwork — every title document was ready before I asked for it.', '0', 'approved', '2026-06-17 06:52:49');
INSERT INTO "reviews" ("id", "user_id", "guest_name", "guest_email", "rating", "title", "body", "verified_owner", "status", "created_at") VALUES ('3', NULL, 'Ifeoma Nnadi', 'ifeoma.n@example.com', '4', 'Great ROI so far on Banana Island', 'Rental income on my Banana Island terrace has already outpaced the 5-year projection from the investor hub calculator. Only complaint is response time on weekends.', '1', 'approved', '2026-06-17 06:52:49');
INSERT INTO "reviews" ("id", "user_id", "guest_name", "guest_email", "rating", "title", "body", "verified_owner", "status", "created_at") VALUES ('4', NULL, 'David Okon', 'david.okon@example.com', '5', 'Eko Atlantic off-plan was worth the wait', 'Locked in pre-construction pricing eighteen months ago and the valuation has already moved well past what I paid. Recommend reserving early.', '0', 'approved', '2026-06-17 06:52:49');
INSERT INTO "reviews" ("id", "user_id", "guest_name", "guest_email", "rating", "title", "body", "verified_owner", "status", "created_at") VALUES ('5', NULL, 'Grace Adeyemi', 'grace.a@example.com', '4', 'Smooth site visit experience', 'The Lekki Pearl Court site visit was well organised, hard hats and all. Sales team answered every question about the payment plan without pressure.', '0', 'approved', '2026-06-17 06:52:49');
INSERT INTO "reviews" ("id", "user_id", "guest_name", "guest_email", "rating", "title", "body", "verified_owner", "status", "created_at") VALUES ('6', NULL, 'Emeka Chukwu', 'emeka.c@example.com', '5', 'Victoria Crest has been a reliable earner', 'Bought into an already-tenanted unit and the in-house letting team has not missed a monthly remittance yet.', '1', 'approved', '2026-06-17 06:52:49');
INSERT INTO "reviews" ("id", "user_id", "guest_name", "guest_email", "rating", "title", "body", "verified_owner", "status", "created_at") VALUES ('7', NULL, 'Aisha Mohammed', 'aisha.m@example.com', '3', 'Good product, slow email replies', 'The Maitama Heights concept is excellent and the security briefing was thorough, but I waited four days for an email response on financing options.', '0', 'pending', '2026-06-17 06:52:49');
INSERT INTO "reviews" ("id", "user_id", "guest_name", "guest_email", "rating", "title", "body", "verified_owner", "status", "created_at") VALUES ('8', NULL, 'Bola Adekunle', 'bola.a@example.com', '5', 'Considering my second purchase', 'My first experience with the Eko Atlantic unit was so smooth I am now in talks for a second reservation at Lekki Pearl Court.', '0', 'pending', '2026-06-17 06:52:49');

-- Table: users
CREATE TABLE users (
            id            INTEGER PRIMARY KEY AUTOINCREMENT,
            name          TEXT NOT NULL,
            email         TEXT NOT NULL UNIQUE,
            password_hash TEXT NOT NULL,
            role          TEXT NOT NULL DEFAULT 'client', -- 'client' | 'admin'
            created_at    TEXT NOT NULL DEFAULT (datetime('now'))
        );
INSERT INTO "users" ("id", "name", "email", "password_hash", "role", "created_at") VALUES ('1', 'Atlantis Admin', 'admin@atlantishomes.ng', '$2y$10$SjzysOd2vw4vJJWY.hcQcuUdXuXA0aPgDnc0JNyoGGFTnqn3udsZ6', 'admin', '2026-06-17 06:52:49');
INSERT INTO "users" ("id", "name", "email", "password_hash", "role", "created_at") VALUES ('2', 'Chiamaka Eze', 'chiamaka@example.com', '$2y$10$ofiJ26FF9q6i0ycrMJmg9eaZf.BLkYDxTuYx.8IwQOKadFXtsrgV2', 'client', '2026-06-17 06:52:49');
INSERT INTO "users" ("id", "name", "email", "password_hash", "role", "created_at") VALUES ('3', 'Ezea Ugochukwu micheal', 'spotwebdev.com@gmail.com', '$2y$10$MpVMxNRXeGLPIxqTnRDhtOM2fElqKpbJi32qvIwoyIogij8/SZi7W', 'client', '2026-06-17 10:17:33');

COMMIT;