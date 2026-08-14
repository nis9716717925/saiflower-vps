-- =============================================================================
-- SaiFlower → PostgreSQL on Hostinger VPS
-- Converted from MySQL dump: u977002836_Saiflower999
-- Preserves: tables, PKs, unique keys, indexes, FKs, integer IDs, data semantics
-- =============================================================================

BEGIN;

-- Enums
CREATE TYPE "comments_status" AS ENUM ('approved', 'pending');
CREATE TYPE "orders_status" AS ENUM ('Pending', 'Completed', 'Cancelled');
CREATE TYPE "product_occasions_product_type" AS ENUM ('flower', 'cake', 'gift');
CREATE TYPE "promo_codes_discount_type" AS ENUM ('percentage', 'flat');
CREATE TYPE "promo_codes_type" AS ENUM ('percentage', 'flat');
CREATE TYPE "customer_address_type" AS ENUM ('Home', 'Work', 'Other');

-- Tables
CREATE TABLE "addons" (
  "id" integer NOT NULL,
  "name" varchar(255) NOT NULL,
  "price" numeric(10,2) NOT NULL,
  "original_price" numeric(10,2) DEFAULT NULL,
  "icon" varchar(50) DEFAULT 'fa-gift',
  "status" smallint DEFAULT 1
);

CREATE TABLE "admin_tokens" (
  "id" integer NOT NULL,
  "admin_id" integer NOT NULL,
  "token" varchar(64) NOT NULL,
  "expiry" timestamptz NOT NULL
);

CREATE TABLE "admin_users" (
  "id" integer NOT NULL,
  "username" varchar(50) NOT NULL,
  "password" varchar(255) NOT NULL,
  "created_at" timestamptz DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE "blogs" (
  "id" integer NOT NULL,
  "title" varchar(255) NOT NULL,
  "slug" varchar(255) DEFAULT NULL,
  "content" text NOT NULL,
  "image" varchar(255) NOT NULL,
  "status" smallint DEFAULT 1,
  "created_at" timestamptz DEFAULT CURRENT_TIMESTAMP,
  "meta_title" varchar(255) DEFAULT NULL,
  "meta_description" text DEFAULT NULL,
  "meta_keywords" text DEFAULT NULL,
  "images_gallery" text DEFAULT NULL
);

CREATE TABLE "cakes" (
  "id" integer NOT NULL,
  "name" varchar(255) NOT NULL,
  "slug" varchar(255) NOT NULL,
  "description" text DEFAULT NULL,
  "price" numeric(10,2) NOT NULL DEFAULT 0.00,
  "original_price" numeric(10,2) DEFAULT 0.00,
  "image" varchar(255) DEFAULT NULL,
  "in_stock" smallint DEFAULT 1,
  "status" smallint DEFAULT 1,
  "meta_title" varchar(255) DEFAULT NULL,
  "meta_description" text DEFAULT NULL,
  "meta_keywords" text DEFAULT NULL,
  "created_at" timestamptz DEFAULT CURRENT_TIMESTAMP,
  "rating" numeric(2,1) NOT NULL DEFAULT 5.0,
  "delivery_sameday" smallint NOT NULL DEFAULT 1,
  "delivery_nextday" smallint NOT NULL DEFAULT 1,
  "tag" varchar(255) DEFAULT NULL,
  "model_3d" varchar(255) DEFAULT NULL,
  "images_gallery" text DEFAULT NULL
);

CREATE TABLE "cake_variants" (
  "id" integer NOT NULL,
  "cake_id" integer NOT NULL,
  "name" varchar(255) NOT NULL,
  "price" numeric(10,2) NOT NULL,
  "original_price" numeric(10,2) DEFAULT NULL
);

CREATE TABLE "categories" (
  "id" integer NOT NULL,
  "name" varchar(255) NOT NULL,
  "image" varchar(255) DEFAULT NULL,
  "status" smallint DEFAULT 1,
  "sort_order" integer DEFAULT 0,
  "created_at" timestamptz DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE "comments" (
  "id" integer NOT NULL,
  "post_slug" varchar(255) NOT NULL,
  "name" varchar(100) NOT NULL,
  "email" varchar(100) NOT NULL,
  "comment" text NOT NULL,
  "status" comments_status DEFAULT 'approved'::comments_status,
  "created_at" timestamptz DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE "customers" (
  "id" integer NOT NULL,
  "name" varchar(100) NOT NULL,
  "email" varchar(100) NOT NULL,
  "phone" varchar(20) DEFAULT NULL,
  "password" varchar(255) NOT NULL,
  "address" text DEFAULT NULL,
  "created_at" timestamptz DEFAULT CURRENT_TIMESTAMP,
  "city" varchar(100) DEFAULT NULL,
  "pincode" varchar(10) DEFAULT NULL,
  "updated_at" timestamptz DEFAULT CURRENT_TIMESTAMP,
  "is_verified" smallint DEFAULT 0,
  "verification_token" varchar(255) DEFAULT NULL,
  "google_id" varchar(255) DEFAULT NULL,
  "auth_provider" varchar(32) NOT NULL DEFAULT 'local',
  "avatar_url" varchar(512) DEFAULT NULL
);

CREATE TABLE "customer_addresses" (
  "id" integer NOT NULL,
  "customer_id" integer NOT NULL,
  "recipient_name" varchar(100) NOT NULL,
  "mobile" varchar(20) NOT NULL,
  "email" varchar(100) DEFAULT NULL,
  "flat_house_no" varchar(100) NOT NULL,
  "apartment_street_locality" varchar(255) NOT NULL,
  "pincode" varchar(10) NOT NULL,
  "address_type" customer_address_type NOT NULL DEFAULT 'Home'::customer_address_type,
  "is_default" smallint NOT NULL DEFAULT 0,
  "created_at" timestamptz DEFAULT CURRENT_TIMESTAMP,
  "updated_at" timestamptz DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE "dynamic_pages" (
  "id" integer NOT NULL,
  "title" varchar(255) NOT NULL,
  "short_description" text DEFAULT NULL,
  "slug" varchar(255) NOT NULL,
  "layout_type" varchar(50) DEFAULT 'event_info',
  "page_tag" varchar(100) DEFAULT NULL,
  "hero_image" varchar(255) DEFAULT NULL,
  "extra_images" text DEFAULT NULL,
  "content" text DEFAULT NULL,
  "meta_title" varchar(255) DEFAULT NULL,
  "meta_description" text DEFAULT NULL,
  "meta_keywords" text DEFAULT NULL,
  "status" smallint DEFAULT 1,
  "created_at" timestamptz DEFAULT CURRENT_TIMESTAMP,
  "updated_at" timestamptz DEFAULT CURRENT_TIMESTAMP,
  "faqs" text DEFAULT NULL,
  "midgrid_image" varchar(255) DEFAULT NULL,
  "midgrid_image_alt" varchar(255) DEFAULT NULL
);

CREATE TABLE "events" (
  "id" integer NOT NULL,
  "title" varchar(255) NOT NULL,
  "slug" varchar(255) DEFAULT NULL,
  "tag" varchar(255) DEFAULT NULL,
  "description" text NOT NULL,
  "cover_image" varchar(255) NOT NULL,
  "status" smallint DEFAULT 1,
  "created_at" timestamptz DEFAULT CURRENT_TIMESTAMP,
  "meta_title" varchar(255) DEFAULT NULL,
  "meta_description" text DEFAULT NULL,
  "meta_keywords" text DEFAULT NULL
);

CREATE TABLE "faqs" (
  "id" integer NOT NULL,
  "question" text NOT NULL,
  "answer" text NOT NULL,
  "page" varchar(100) DEFAULT 'general',
  "status" smallint DEFAULT 1
);

CREATE TABLE "flowers" (
  "id" integer NOT NULL,
  "category_ids" text DEFAULT NULL,
  "name" varchar(255) NOT NULL,
  "slug" varchar(255) DEFAULT NULL,
  "price" numeric(10,2) NOT NULL,
  "original_price" numeric(10,2) DEFAULT NULL,
  "description" text DEFAULT NULL,
  "image" varchar(255) NOT NULL,
  "model_3d" varchar(255) DEFAULT NULL,
  "in_stock" smallint DEFAULT 1,
  "status" smallint DEFAULT 1,
  "meta_title" varchar(255) DEFAULT NULL,
  "meta_description" text DEFAULT NULL,
  "meta_keywords" varchar(255) DEFAULT NULL,
  "created_at" timestamptz DEFAULT CURRENT_TIMESTAMP,
  "rating" numeric(2,1) NOT NULL DEFAULT 5.0,
  "delivery_sameday" smallint NOT NULL DEFAULT 1,
  "delivery_nextday" smallint NOT NULL DEFAULT 1,
  "tag" varchar(255) DEFAULT NULL,
  "images_gallery" text DEFAULT NULL,
  "faqs" text DEFAULT NULL,
  "image_alt" varchar(255) DEFAULT NULL
);

CREATE TABLE "flower_images" (
  "id" integer NOT NULL,
  "flower_id" integer NOT NULL,
  "image_path" varchar(255) NOT NULL
);

CREATE TABLE "flower_variants" (
  "id" integer NOT NULL,
  "flower_id" integer NOT NULL,
  "name" varchar(255) NOT NULL,
  "price" numeric(10,2) NOT NULL,
  "original_price" numeric(10,2) DEFAULT NULL
);

CREATE TABLE "gallery" (
  "id" integer NOT NULL,
  "title" varchar(255) NOT NULL,
  "tag" varchar(255) DEFAULT NULL,
  "image" varchar(255) NOT NULL,
  "status" smallint DEFAULT 1,
  "created_at" timestamptz DEFAULT CURRENT_TIMESTAMP,
  "meta_title" varchar(255) DEFAULT NULL,
  "meta_description" text DEFAULT NULL,
  "meta_keywords" text DEFAULT NULL
);

CREATE TABLE "gifts" (
  "id" integer NOT NULL,
  "name" varchar(255) NOT NULL,
  "slug" varchar(255) NOT NULL,
  "description" text DEFAULT NULL,
  "price" numeric(10,2) NOT NULL DEFAULT 0.00,
  "original_price" numeric(10,2) DEFAULT 0.00,
  "image" varchar(255) DEFAULT NULL,
  "in_stock" smallint DEFAULT 1,
  "status" smallint DEFAULT 1,
  "meta_title" varchar(255) DEFAULT NULL,
  "meta_description" text DEFAULT NULL,
  "meta_keywords" text DEFAULT NULL,
  "created_at" timestamptz DEFAULT CURRENT_TIMESTAMP,
  "rating" numeric(2,1) NOT NULL DEFAULT 5.0,
  "delivery_sameday" smallint NOT NULL DEFAULT 1,
  "delivery_nextday" smallint NOT NULL DEFAULT 1,
  "tag" varchar(255) DEFAULT NULL,
  "model_3d" varchar(255) DEFAULT NULL,
  "images_gallery" text DEFAULT NULL
);

CREATE TABLE "gift_variants" (
  "id" integer NOT NULL,
  "gift_id" integer NOT NULL,
  "name" varchar(255) NOT NULL,
  "price" numeric(10,2) NOT NULL,
  "original_price" numeric(10,2) DEFAULT NULL
);

CREATE TABLE "global_pricing" (
  "id" integer NOT NULL,
  "surge_percentage" numeric(5,2) NOT NULL DEFAULT 0.00,
  "flower_surge" numeric(5,2) NOT NULL DEFAULT 0.00,
  "cake_surge" numeric(5,2) NOT NULL DEFAULT 0.00,
  "gift_surge" numeric(5,2) NOT NULL DEFAULT 0.00
);

CREATE TABLE "homepage_circles" (
  "id" integer NOT NULL,
  "name" varchar(100) NOT NULL,
  "image" varchar(255) NOT NULL,
  "link" varchar(255) DEFAULT NULL,
  "sort_order" integer DEFAULT 0,
  "status" smallint DEFAULT 1
);

CREATE TABLE "homepage_sections" (
  "id" integer NOT NULL,
  "title" varchar(255) NOT NULL,
  "subtitle" varchar(255) DEFAULT NULL,
  "type" varchar(50) NOT NULL DEFAULT '',
  "sort_order" integer DEFAULT 0,
  "status" smallint DEFAULT 1
);

CREATE TABLE "homepage_section_items" (
  "id" integer NOT NULL,
  "section_id" integer NOT NULL,
  "image" varchar(255) DEFAULT NULL,
  "title" varchar(255) DEFAULT NULL,
  "subtitle" varchar(255) DEFAULT NULL,
  "price" varchar(50) DEFAULT NULL,
  "link" varchar(255) DEFAULT NULL,
  "sort_order" integer DEFAULT 0,
  "top_badge_text" varchar(255) DEFAULT NULL,
  "badge_text" varchar(255) DEFAULT NULL,
  "rating" varchar(50) DEFAULT NULL,
  "delivery_info" varchar(255) DEFAULT NULL,
  "original_price" varchar(50) DEFAULT NULL,
  "discount_label" varchar(50) DEFAULT NULL
);

CREATE TABLE "homepage_slides" (
  "id" integer NOT NULL,
  "image" varchar(255) NOT NULL,
  "mobile_image" varchar(255) DEFAULT NULL,
  "link" varchar(255) DEFAULT NULL,
  "sort_order" integer DEFAULT 0,
  "status" smallint DEFAULT 1
);

CREATE TABLE "leads" (
  "id" integer NOT NULL,
  "name" varchar(100) NOT NULL,
  "phone" varchar(20) NOT NULL,
  "email" varchar(100) DEFAULT NULL,
  "message" text DEFAULT NULL,
  "status" varchar(20) DEFAULT 'New',
  "service" varchar(100) DEFAULT NULL,
  "created_at" timestamptz DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE "occasions" (
  "id" integer NOT NULL,
  "name" varchar(255) NOT NULL,
  "icon" varchar(100) DEFAULT 'fas fa-gift',
  "status" smallint DEFAULT 1,
  "sort_order" integer DEFAULT 0,
  "created_at" timestamptz DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE "orders" (
  "id" integer NOT NULL,
  "customer_name" varchar(255) NOT NULL,
  "customer_phone" varchar(50) DEFAULT NULL,
  "customer_email" varchar(255) DEFAULT NULL,
  "delivery_address" text NOT NULL,
  "delivery_date" date DEFAULT NULL,
  "order_items" text NOT NULL,
  "total_amount" numeric(10,2) NOT NULL DEFAULT 0.00,
  "status" orders_status DEFAULT 'Pending'::orders_status,
  "created_at" timestamptz DEFAULT CURRENT_TIMESTAMP,
  "coupon_code" varchar(50) DEFAULT NULL
);

CREATE TABLE "pricing_log" (
  "id" integer NOT NULL,
  "category" varchar(50) NOT NULL,
  "percentage" numeric(5,2) NOT NULL,
  "action" varchar(50) NOT NULL,
  "created_at" timestamptz NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE "products" (
  "id" integer NOT NULL,
  "name" varchar(255) NOT NULL,
  "price" numeric(10,2) NOT NULL,
  "image" varchar(255) DEFAULT 'default.jpg',
  "description" text DEFAULT NULL,
  "category" varchar(100) DEFAULT NULL,
  "created_at" timestamptz DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE "product_occasions" (
  "id" integer NOT NULL,
  "product_type" product_occasions_product_type NOT NULL,
  "product_id" integer NOT NULL,
  "occasion_name" varchar(100) NOT NULL,
  "created_at" timestamptz DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE "promo_codes" (
  "id" integer NOT NULL,
  "code" varchar(50) NOT NULL,
  "discount_type" promo_codes_discount_type NOT NULL,
  "discount_value" numeric(10,2) NOT NULL,
  "min_order_amount" numeric(10,2) DEFAULT NULL,
  "discount_text" varchar(255) DEFAULT NULL,
  "description" text DEFAULT NULL,
  "status" smallint DEFAULT 1,
  "is_featured" smallint DEFAULT 0,
  "created_at" timestamptz DEFAULT CURRENT_TIMESTAMP,
  "type" promo_codes_type DEFAULT 'percentage'::promo_codes_type,
  "value" numeric(10,2) DEFAULT 0.00,
  "show_on_cakes" smallint DEFAULT 0,
  "show_on_gifts" smallint DEFAULT 0,
  "show_on_flowers" smallint DEFAULT 0,
  "expiry_date" date DEFAULT NULL,
  "usage_limit" integer DEFAULT NULL
);

CREATE TABLE "reviews" (
  "id" integer NOT NULL,
  "name" varchar(100) NOT NULL,
  "review_text" text NOT NULL,
  "rating" integer DEFAULT 5,
  "platform" varchar(50) DEFAULT 'Google',
  "status" smallint DEFAULT 1,
  "created_at" timestamptz DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE "seo_meta" (
  "id" integer NOT NULL,
  "page_identifier" varchar(100) NOT NULL,
  "title" varchar(255) DEFAULT NULL,
  "description" text DEFAULT NULL,
  "keywords" text DEFAULT NULL
);

CREATE TABLE "settings" (
  "id" integer NOT NULL,
  "site_title" varchar(255) DEFAULT 'Sai Flowers',
  "tagline" varchar(255) DEFAULT NULL,
  "logo" varchar(255) DEFAULT NULL,
  "phone" varchar(50) DEFAULT NULL,
  "whatsapp" varchar(50) DEFAULT NULL,
  "email" varchar(100) DEFAULT NULL,
  "address" text DEFAULT NULL,
  "theme_primary" varchar(20) DEFAULT '#2f6f4e',
  "theme_secondary" varchar(20) DEFAULT '#d4af37',
  "hero_title" varchar(255) DEFAULT NULL,
  "hero_subtitle" varchar(255) DEFAULT NULL,
  "hero_image" varchar(255) DEFAULT NULL,
  "logo_width" integer DEFAULT 150,
  "maintenance_mode" smallint DEFAULT 0,
  "footer_about" text DEFAULT NULL,
  "newsletter_text" text DEFAULT NULL
);

CREATE TABLE "tags" (
  "id" integer NOT NULL,
  "name" varchar(255) NOT NULL,
  "status" smallint DEFAULT 1,
  "created_at" timestamptz DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE "wishlist" (
  "id" integer NOT NULL,
  "user_id" integer NOT NULL,
  "product_id" integer NOT NULL,
  "type" varchar(50) DEFAULT 'flower',
  "created_at" timestamptz DEFAULT CURRENT_TIMESTAMP
);

-- Primary keys
ALTER TABLE "addons" ADD CONSTRAINT "addons_pkey" PRIMARY KEY ("id");
ALTER TABLE "admin_tokens" ADD CONSTRAINT "admin_tokens_pkey" PRIMARY KEY ("id");
ALTER TABLE "admin_users" ADD CONSTRAINT "admin_users_pkey" PRIMARY KEY ("id");
ALTER TABLE "blogs" ADD CONSTRAINT "blogs_pkey" PRIMARY KEY ("id");
ALTER TABLE "cakes" ADD CONSTRAINT "cakes_pkey" PRIMARY KEY ("id");
ALTER TABLE "cake_variants" ADD CONSTRAINT "cake_variants_pkey" PRIMARY KEY ("id");
ALTER TABLE "categories" ADD CONSTRAINT "categories_pkey" PRIMARY KEY ("id");
ALTER TABLE "comments" ADD CONSTRAINT "comments_pkey" PRIMARY KEY ("id");
ALTER TABLE "customers" ADD CONSTRAINT "customers_pkey" PRIMARY KEY ("id");
ALTER TABLE "customer_addresses" ADD CONSTRAINT "customer_addresses_pkey" PRIMARY KEY ("id");
ALTER TABLE "dynamic_pages" ADD CONSTRAINT "dynamic_pages_pkey" PRIMARY KEY ("id");
ALTER TABLE "events" ADD CONSTRAINT "events_pkey" PRIMARY KEY ("id");
ALTER TABLE "faqs" ADD CONSTRAINT "faqs_pkey" PRIMARY KEY ("id");
ALTER TABLE "flowers" ADD CONSTRAINT "flowers_pkey" PRIMARY KEY ("id");
ALTER TABLE "flower_images" ADD CONSTRAINT "flower_images_pkey" PRIMARY KEY ("id");
ALTER TABLE "flower_variants" ADD CONSTRAINT "flower_variants_pkey" PRIMARY KEY ("id");
ALTER TABLE "gallery" ADD CONSTRAINT "gallery_pkey" PRIMARY KEY ("id");
ALTER TABLE "gifts" ADD CONSTRAINT "gifts_pkey" PRIMARY KEY ("id");
ALTER TABLE "gift_variants" ADD CONSTRAINT "gift_variants_pkey" PRIMARY KEY ("id");
ALTER TABLE "global_pricing" ADD CONSTRAINT "global_pricing_pkey" PRIMARY KEY ("id");
ALTER TABLE "homepage_circles" ADD CONSTRAINT "homepage_circles_pkey" PRIMARY KEY ("id");
ALTER TABLE "homepage_sections" ADD CONSTRAINT "homepage_sections_pkey" PRIMARY KEY ("id");
ALTER TABLE "homepage_section_items" ADD CONSTRAINT "homepage_section_items_pkey" PRIMARY KEY ("id");
ALTER TABLE "homepage_slides" ADD CONSTRAINT "homepage_slides_pkey" PRIMARY KEY ("id");
ALTER TABLE "leads" ADD CONSTRAINT "leads_pkey" PRIMARY KEY ("id");
ALTER TABLE "occasions" ADD CONSTRAINT "occasions_pkey" PRIMARY KEY ("id");
ALTER TABLE "orders" ADD CONSTRAINT "orders_pkey" PRIMARY KEY ("id");
ALTER TABLE "pricing_log" ADD CONSTRAINT "pricing_log_pkey" PRIMARY KEY ("id");
ALTER TABLE "products" ADD CONSTRAINT "products_pkey" PRIMARY KEY ("id");
ALTER TABLE "product_occasions" ADD CONSTRAINT "product_occasions_pkey" PRIMARY KEY ("id");
ALTER TABLE "promo_codes" ADD CONSTRAINT "promo_codes_pkey" PRIMARY KEY ("id");
ALTER TABLE "reviews" ADD CONSTRAINT "reviews_pkey" PRIMARY KEY ("id");
ALTER TABLE "seo_meta" ADD CONSTRAINT "seo_meta_pkey" PRIMARY KEY ("id");
ALTER TABLE "settings" ADD CONSTRAINT "settings_pkey" PRIMARY KEY ("id");
ALTER TABLE "tags" ADD CONSTRAINT "tags_pkey" PRIMARY KEY ("id");
ALTER TABLE "wishlist" ADD CONSTRAINT "wishlist_pkey" PRIMARY KEY ("id");

-- Unique constraints
ALTER TABLE "admin_users" ADD CONSTRAINT "admin_users_username_key" UNIQUE ("username");
ALTER TABLE "blogs" ADD CONSTRAINT "blogs_slug_key" UNIQUE ("slug");
ALTER TABLE "cakes" ADD CONSTRAINT "cakes_slug_key" UNIQUE ("slug");
ALTER TABLE "customers" ADD CONSTRAINT "customers_email_key" UNIQUE ("email");
ALTER TABLE "customers" ADD CONSTRAINT "uniq_customers_google_id" UNIQUE ("google_id");
ALTER TABLE "dynamic_pages" ADD CONSTRAINT "dynamic_pages_slug_key" UNIQUE ("slug");
ALTER TABLE "events" ADD CONSTRAINT "events_slug_key" UNIQUE ("slug");
ALTER TABLE "gifts" ADD CONSTRAINT "gifts_slug_key" UNIQUE ("slug");
ALTER TABLE "occasions" ADD CONSTRAINT "occasions_name_key" UNIQUE ("name");
ALTER TABLE "product_occasions" ADD CONSTRAINT "unique_product_occasion" UNIQUE ("product_type", "product_id", "occasion_name");
ALTER TABLE "promo_codes" ADD CONSTRAINT "promo_codes_code_key" UNIQUE ("code");
ALTER TABLE "seo_meta" ADD CONSTRAINT "seo_meta_page_identifier_key" UNIQUE ("page_identifier");
ALTER TABLE "wishlist" ADD CONSTRAINT "unique_wishlist" UNIQUE ("user_id", "product_id", "type");

-- Indexes
CREATE INDEX "admin_tokens_admin_id_idx" ON "admin_tokens" ("admin_id");
CREATE INDEX "admin_tokens_token_idx" ON "admin_tokens" ("token");
CREATE INDEX "cake_variants_cake_id_idx" ON "cake_variants" ("cake_id");
CREATE INDEX "customer_addresses_customer_id_idx" ON "customer_addresses" ("customer_id");
CREATE INDEX "flower_images_flower_id_idx" ON "flower_images" ("flower_id");
CREATE INDEX "flower_variants_flower_id_idx" ON "flower_variants" ("flower_id");
CREATE INDEX "gift_variants_gift_id_idx" ON "gift_variants" ("gift_id");
CREATE INDEX "homepage_section_items_section_id_idx" ON "homepage_section_items" ("section_id");
CREATE INDEX "idx_occasion" ON "product_occasions" ("occasion_name");

-- Foreign keys
ALTER TABLE "cake_variants" ADD CONSTRAINT "cake_variants_ibfk_1" FOREIGN KEY ("cake_id") REFERENCES "cakes" ("id") ON DELETE CASCADE DEFERRABLE INITIALLY DEFERRED;
ALTER TABLE "customer_addresses" ADD CONSTRAINT "customer_addresses_customer_id_fkey" FOREIGN KEY ("customer_id") REFERENCES "customers" ("id") ON DELETE CASCADE ON UPDATE CASCADE DEFERRABLE INITIALLY DEFERRED;
ALTER TABLE "gift_variants" ADD CONSTRAINT "gift_variants_ibfk_1" FOREIGN KEY ("gift_id") REFERENCES "gifts" ("id") ON DELETE CASCADE DEFERRABLE INITIALLY DEFERRED;
ALTER TABLE "homepage_section_items" ADD CONSTRAINT "homepage_section_items_ibfk_1" FOREIGN KEY ("section_id") REFERENCES "homepage_sections" ("id") ON DELETE CASCADE DEFERRABLE INITIALLY DEFERRED;
ALTER TABLE "flower_images" ADD CONSTRAINT "flower_images_flower_id_fkey" FOREIGN KEY ("flower_id") REFERENCES "flowers" ("id") ON DELETE CASCADE DEFERRABLE INITIALLY DEFERRED;
ALTER TABLE "flower_variants" ADD CONSTRAINT "flower_variants_flower_id_fkey" FOREIGN KEY ("flower_id") REFERENCES "flowers" ("id") ON DELETE CASCADE DEFERRABLE INITIALLY DEFERRED;
ALTER TABLE "admin_tokens" ADD CONSTRAINT "admin_tokens_admin_id_fkey" FOREIGN KEY ("admin_id") REFERENCES "admin_users" ("id") ON DELETE CASCADE DEFERRABLE INITIALLY DEFERRED;
ALTER TABLE "wishlist" ADD CONSTRAINT "wishlist_user_id_fkey" FOREIGN KEY ("user_id") REFERENCES "customers" ("id") ON DELETE CASCADE DEFERRABLE INITIALLY DEFERRED;

-- Identity / sequences (preserve MySQL AUTO_INCREMENT next values)
ALTER TABLE "addons" ALTER COLUMN "id" ADD GENERATED BY DEFAULT AS IDENTITY (INCREMENT BY 1 START WITH 15);
ALTER TABLE "admin_tokens" ALTER COLUMN "id" ADD GENERATED BY DEFAULT AS IDENTITY (INCREMENT BY 1 START WITH 53);
ALTER TABLE "admin_users" ALTER COLUMN "id" ADD GENERATED BY DEFAULT AS IDENTITY (INCREMENT BY 1 START WITH 2);
ALTER TABLE "blogs" ALTER COLUMN "id" ADD GENERATED BY DEFAULT AS IDENTITY (INCREMENT BY 1 START WITH 168);
ALTER TABLE "cakes" ALTER COLUMN "id" ADD GENERATED BY DEFAULT AS IDENTITY (INCREMENT BY 1 START WITH 6);
ALTER TABLE "cake_variants" ALTER COLUMN "id" ADD GENERATED BY DEFAULT AS IDENTITY (INCREMENT BY 1 START WITH 10);
ALTER TABLE "categories" ALTER COLUMN "id" ADD GENERATED BY DEFAULT AS IDENTITY (INCREMENT BY 1 START WITH 23);
ALTER TABLE "comments" ALTER COLUMN "id" ADD GENERATED BY DEFAULT AS IDENTITY (INCREMENT BY 1 START WITH 13);
ALTER TABLE "customers" ALTER COLUMN "id" ADD GENERATED BY DEFAULT AS IDENTITY (INCREMENT BY 1 START WITH 23);
ALTER TABLE "customer_addresses" ALTER COLUMN "id" ADD GENERATED BY DEFAULT AS IDENTITY (INCREMENT BY 1 START WITH 1);
ALTER TABLE "dynamic_pages" ALTER COLUMN "id" ADD GENERATED BY DEFAULT AS IDENTITY (INCREMENT BY 1 START WITH 211);
ALTER TABLE "events" ALTER COLUMN "id" ADD GENERATED BY DEFAULT AS IDENTITY (INCREMENT BY 1 START WITH 8);
ALTER TABLE "faqs" ALTER COLUMN "id" ADD GENERATED BY DEFAULT AS IDENTITY (INCREMENT BY 1 START WITH 16);
ALTER TABLE "flowers" ALTER COLUMN "id" ADD GENERATED BY DEFAULT AS IDENTITY (INCREMENT BY 1 START WITH 322);
ALTER TABLE "flower_images" ALTER COLUMN "id" ADD GENERATED BY DEFAULT AS IDENTITY (INCREMENT BY 1 START WITH 53);
ALTER TABLE "flower_variants" ALTER COLUMN "id" ADD GENERATED BY DEFAULT AS IDENTITY (INCREMENT BY 1 START WITH 18);
ALTER TABLE "gallery" ALTER COLUMN "id" ADD GENERATED BY DEFAULT AS IDENTITY (INCREMENT BY 1 START WITH 4);
ALTER TABLE "gifts" ALTER COLUMN "id" ADD GENERATED BY DEFAULT AS IDENTITY (INCREMENT BY 1 START WITH 4);
ALTER TABLE "gift_variants" ALTER COLUMN "id" ADD GENERATED BY DEFAULT AS IDENTITY (INCREMENT BY 1 START WITH 2);
ALTER TABLE "homepage_circles" ALTER COLUMN "id" ADD GENERATED BY DEFAULT AS IDENTITY (INCREMENT BY 1 START WITH 10);
ALTER TABLE "homepage_sections" ALTER COLUMN "id" ADD GENERATED BY DEFAULT AS IDENTITY (INCREMENT BY 1 START WITH 36);
ALTER TABLE "homepage_section_items" ALTER COLUMN "id" ADD GENERATED BY DEFAULT AS IDENTITY (INCREMENT BY 1 START WITH 132);
ALTER TABLE "homepage_slides" ALTER COLUMN "id" ADD GENERATED BY DEFAULT AS IDENTITY (INCREMENT BY 1 START WITH 16);
ALTER TABLE "leads" ALTER COLUMN "id" ADD GENERATED BY DEFAULT AS IDENTITY (INCREMENT BY 1 START WITH 111);
ALTER TABLE "occasions" ALTER COLUMN "id" ADD GENERATED BY DEFAULT AS IDENTITY (INCREMENT BY 1 START WITH 5);
ALTER TABLE "orders" ALTER COLUMN "id" ADD GENERATED BY DEFAULT AS IDENTITY (INCREMENT BY 1 START WITH 37);
ALTER TABLE "pricing_log" ALTER COLUMN "id" ADD GENERATED BY DEFAULT AS IDENTITY (INCREMENT BY 1 START WITH 9);
ALTER TABLE "products" ALTER COLUMN "id" ADD GENERATED BY DEFAULT AS IDENTITY (INCREMENT BY 1 START WITH 7);
ALTER TABLE "product_occasions" ALTER COLUMN "id" ADD GENERATED BY DEFAULT AS IDENTITY (INCREMENT BY 1 START WITH 269);
ALTER TABLE "promo_codes" ALTER COLUMN "id" ADD GENERATED BY DEFAULT AS IDENTITY (INCREMENT BY 1 START WITH 24);
ALTER TABLE "reviews" ALTER COLUMN "id" ADD GENERATED BY DEFAULT AS IDENTITY (INCREMENT BY 1 START WITH 4);
ALTER TABLE "seo_meta" ALTER COLUMN "id" ADD GENERATED BY DEFAULT AS IDENTITY (INCREMENT BY 1 START WITH 5);
ALTER TABLE "settings" ALTER COLUMN "id" ADD GENERATED BY DEFAULT AS IDENTITY (INCREMENT BY 1 START WITH 2);
ALTER TABLE "tags" ALTER COLUMN "id" ADD GENERATED BY DEFAULT AS IDENTITY (INCREMENT BY 1 START WITH 66);
ALTER TABLE "wishlist" ALTER COLUMN "id" ADD GENERATED BY DEFAULT AS IDENTITY (INCREMENT BY 1 START WITH 14);
ALTER TABLE "global_pricing" ALTER COLUMN "id" ADD GENERATED BY DEFAULT AS IDENTITY (INCREMENT BY 1 START WITH 1);

COMMIT;
