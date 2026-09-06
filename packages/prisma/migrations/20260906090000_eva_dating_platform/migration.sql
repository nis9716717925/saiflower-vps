-- EVA dating / connection platform tables

CREATE TYPE "eva_gender" AS ENUM ('WOMAN', 'MAN', 'NON_BINARY', 'OTHER', 'PREFER_NOT');
CREATE TYPE "eva_intent" AS ENUM ('LONG_TERM', 'SERIOUS', 'DATING', 'NEW_CONNECTIONS', 'FRIENDSHIP', 'CASUAL');
CREATE TYPE "eva_media_kind" AS ENUM ('PHOTO', 'VIDEO', 'SELFIE_VERIFY');
CREATE TYPE "eva_moderation_status" AS ENUM ('PENDING', 'APPROVED', 'REJECTED', 'FLAGGED');
CREATE TYPE "eva_like_target" AS ENUM ('PROFILE', 'PROMPT', 'INTEREST', 'PHOTO');
CREATE TYPE "eva_verification_status" AS ENUM ('NONE', 'PENDING', 'APPROVED', 'REJECTED');
CREATE TYPE "eva_event_interest_kind" AS ENUM ('SAVED', 'INTERESTED', 'JOINED');
CREATE TYPE "eva_notification_kind" AS ENUM ('MATCH', 'MESSAGE', 'LIKE', 'EVENT', 'RAVE', 'VERIFICATION', 'SAFETY', 'SYSTEM');

CREATE TABLE "eva_eligibility" (
    "id" SERIAL NOT NULL,
    "customer_id" INTEGER NOT NULL,
    "is_18_confirmed" BOOLEAN NOT NULL DEFAULT false,
    "confirmed_at" TIMESTAMPTZ(6),
    "created_at" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT "eva_eligibility_pkey" PRIMARY KEY ("id")
);

CREATE UNIQUE INDEX "eva_eligibility_customer_id_key" ON "eva_eligibility"("customer_id");

CREATE TABLE "eva_profiles" (
    "id" SERIAL NOT NULL,
    "customer_id" INTEGER NOT NULL,
    "display_name" VARCHAR(80) NOT NULL,
    "birth_year" INTEGER NOT NULL,
    "gender" "eva_gender" NOT NULL,
    "pronouns" VARCHAR(40),
    "bio" VARCHAR(600),
    "intent" "eva_intent",
    "city" VARCHAR(100),
    "latitude" DOUBLE PRECISION,
    "longitude" DOUBLE PRECISION,
    "completeness" INTEGER NOT NULL DEFAULT 0,
    "onboarding_complete" BOOLEAN NOT NULL DEFAULT false,
    "verification_status" "eva_verification_status" NOT NULL DEFAULT 'NONE',
    "photo_verified_at" TIMESTAMPTZ(6),
    "last_active_at" TIMESTAMPTZ(6),
    "is_hidden" BOOLEAN NOT NULL DEFAULT false,
    "created_at" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT "eva_profiles_pkey" PRIMARY KEY ("id")
);

CREATE UNIQUE INDEX "eva_profiles_customer_id_key" ON "eva_profiles"("customer_id");
CREATE INDEX "eva_profiles_discover_idx" ON "eva_profiles"("gender", "intent", "last_active_at");
CREATE INDEX "eva_profiles_city_idx" ON "eva_profiles"("city");

CREATE TABLE "eva_media" (
    "id" SERIAL NOT NULL,
    "profile_id" INTEGER NOT NULL,
    "url" VARCHAR(512) NOT NULL,
    "kind" "eva_media_kind" NOT NULL DEFAULT 'PHOTO',
    "sort_order" INTEGER NOT NULL DEFAULT 0,
    "is_primary" BOOLEAN NOT NULL DEFAULT false,
    "moderation_status" "eva_moderation_status" NOT NULL DEFAULT 'APPROVED',
    "created_at" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT "eva_media_pkey" PRIMARY KEY ("id")
);

CREATE INDEX "eva_media_profile_idx" ON "eva_media"("profile_id", "sort_order");

CREATE TABLE "eva_interests" (
    "id" SERIAL NOT NULL,
    "slug" VARCHAR(64) NOT NULL,
    "label" VARCHAR(80) NOT NULL,
    "category" VARCHAR(64),
    "active" BOOLEAN NOT NULL DEFAULT true,
    CONSTRAINT "eva_interests_pkey" PRIMARY KEY ("id")
);

CREATE UNIQUE INDEX "eva_interests_slug_key" ON "eva_interests"("slug");

CREATE TABLE "eva_profile_interests" (
    "profile_id" INTEGER NOT NULL,
    "interest_id" INTEGER NOT NULL,
    CONSTRAINT "eva_profile_interests_pkey" PRIMARY KEY ("profile_id","interest_id")
);

CREATE TABLE "eva_prompts" (
    "id" SERIAL NOT NULL,
    "slug" VARCHAR(64) NOT NULL,
    "text" VARCHAR(255) NOT NULL,
    "active" BOOLEAN NOT NULL DEFAULT true,
    "sort_order" INTEGER NOT NULL DEFAULT 0,
    CONSTRAINT "eva_prompts_pkey" PRIMARY KEY ("id")
);

CREATE UNIQUE INDEX "eva_prompts_slug_key" ON "eva_prompts"("slug");

CREATE TABLE "eva_profile_prompts" (
    "id" SERIAL NOT NULL,
    "profile_id" INTEGER NOT NULL,
    "prompt_id" INTEGER NOT NULL,
    "answer" VARCHAR(400) NOT NULL,
    "sort_order" INTEGER NOT NULL DEFAULT 0,
    CONSTRAINT "eva_profile_prompts_pkey" PRIMARY KEY ("id")
);

CREATE UNIQUE INDEX "eva_profile_prompts_unique" ON "eva_profile_prompts"("profile_id", "prompt_id");
CREATE INDEX "eva_profile_prompts_profile_idx" ON "eva_profile_prompts"("profile_id");

CREATE TABLE "eva_preferences" (
    "id" SERIAL NOT NULL,
    "customer_id" INTEGER NOT NULL,
    "age_min" INTEGER NOT NULL DEFAULT 18,
    "age_max" INTEGER NOT NULL DEFAULT 50,
    "max_km" INTEGER NOT NULL DEFAULT 50,
    "genders" "eva_gender"[],
    "intents" "eva_intent"[],
    "interest_ids" INTEGER[],
    "show_distance" BOOLEAN NOT NULL DEFAULT true,
    "created_at" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT "eva_preferences_pkey" PRIMARY KEY ("id")
);

CREATE UNIQUE INDEX "eva_preferences_customer_id_key" ON "eva_preferences"("customer_id");

CREATE TABLE "eva_likes" (
    "id" SERIAL NOT NULL,
    "from_profile_id" INTEGER NOT NULL,
    "to_profile_id" INTEGER NOT NULL,
    "target_type" "eva_like_target" NOT NULL DEFAULT 'PROFILE',
    "target_id" INTEGER,
    "comment" VARCHAR(280),
    "created_at" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT "eva_likes_pkey" PRIMARY KEY ("id")
);

CREATE UNIQUE INDEX "eva_likes_pair_unique" ON "eva_likes"("from_profile_id", "to_profile_id");
CREATE INDEX "eva_likes_to_idx" ON "eva_likes"("to_profile_id", "created_at");

CREATE TABLE "eva_passes" (
    "id" SERIAL NOT NULL,
    "from_profile_id" INTEGER NOT NULL,
    "to_profile_id" INTEGER NOT NULL,
    "created_at" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT "eva_passes_pkey" PRIMARY KEY ("id")
);

CREATE UNIQUE INDEX "eva_passes_pair_unique" ON "eva_passes"("from_profile_id", "to_profile_id");

CREATE TABLE "eva_matches" (
    "id" SERIAL NOT NULL,
    "profile_a_id" INTEGER NOT NULL,
    "profile_b_id" INTEGER NOT NULL,
    "unmatched_at" TIMESTAMPTZ(6),
    "unmatched_by_id" INTEGER,
    "created_at" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT "eva_matches_pkey" PRIMARY KEY ("id")
);

CREATE UNIQUE INDEX "eva_matches_pair_unique" ON "eva_matches"("profile_a_id", "profile_b_id");
CREATE INDEX "eva_matches_created_idx" ON "eva_matches"("created_at");

CREATE TABLE "eva_conversations" (
    "id" SERIAL NOT NULL,
    "match_id" INTEGER NOT NULL,
    "archived_a" BOOLEAN NOT NULL DEFAULT false,
    "archived_b" BOOLEAN NOT NULL DEFAULT false,
    "last_message_at" TIMESTAMPTZ(6),
    "created_at" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT "eva_conversations_pkey" PRIMARY KEY ("id")
);

CREATE UNIQUE INDEX "eva_conversations_match_id_key" ON "eva_conversations"("match_id");
CREATE INDEX "eva_conversations_last_msg_idx" ON "eva_conversations"("last_message_at");

CREATE TABLE "eva_messages" (
    "id" SERIAL NOT NULL,
    "conversation_id" INTEGER NOT NULL,
    "sender_profile_id" INTEGER NOT NULL,
    "body" VARCHAR(2000) NOT NULL,
    "delivered_at" TIMESTAMPTZ(6),
    "read_at" TIMESTAMPTZ(6),
    "deleted_at" TIMESTAMPTZ(6),
    "created_at" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT "eva_messages_pkey" PRIMARY KEY ("id")
);

CREATE INDEX "eva_messages_thread_idx" ON "eva_messages"("conversation_id", "created_at");

CREATE TABLE "eva_typing" (
    "id" SERIAL NOT NULL,
    "conversation_id" INTEGER NOT NULL,
    "profile_id" INTEGER NOT NULL,
    "expires_at" TIMESTAMPTZ(6) NOT NULL,
    CONSTRAINT "eva_typing_pkey" PRIMARY KEY ("id")
);

CREATE UNIQUE INDEX "eva_typing_unique" ON "eva_typing"("conversation_id", "profile_id");

CREATE TABLE "eva_blocks" (
    "id" SERIAL NOT NULL,
    "blocker_customer_id" INTEGER NOT NULL,
    "blocked_customer_id" INTEGER NOT NULL,
    "created_at" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT "eva_blocks_pkey" PRIMARY KEY ("id")
);

CREATE UNIQUE INDEX "eva_blocks_unique" ON "eva_blocks"("blocker_customer_id", "blocked_customer_id");

CREATE TABLE "eva_reports" (
    "id" SERIAL NOT NULL,
    "reporter_customer_id" INTEGER NOT NULL,
    "reported_customer_id" INTEGER NOT NULL,
    "reason" VARCHAR(80) NOT NULL,
    "details" VARCHAR(1000),
    "created_at" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT "eva_reports_pkey" PRIMARY KEY ("id")
);

CREATE INDEX "eva_reports_reported_idx" ON "eva_reports"("reported_customer_id");

CREATE TABLE "eva_dating_events" (
    "id" SERIAL NOT NULL,
    "title" VARCHAR(160) NOT NULL,
    "slug" VARCHAR(160) NOT NULL,
    "summary" VARCHAR(400),
    "description" TEXT,
    "cover_url" VARCHAR(512),
    "city" VARCHAR(100),
    "venue_approx" VARCHAR(160),
    "starts_at" TIMESTAMPTZ(6) NOT NULL,
    "ends_at" TIMESTAMPTZ(6),
    "capacity" INTEGER,
    "active" BOOLEAN NOT NULL DEFAULT true,
    "created_at" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT "eva_dating_events_pkey" PRIMARY KEY ("id")
);

CREATE UNIQUE INDEX "eva_dating_events_slug_key" ON "eva_dating_events"("slug");
CREATE INDEX "eva_dating_events_starts_idx" ON "eva_dating_events"("starts_at", "active");

CREATE TABLE "eva_event_interests" (
    "id" SERIAL NOT NULL,
    "event_id" INTEGER NOT NULL,
    "customer_id" INTEGER NOT NULL,
    "kind" "eva_event_interest_kind" NOT NULL DEFAULT 'INTERESTED',
    "discoverable" BOOLEAN NOT NULL DEFAULT true,
    "created_at" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT "eva_event_interests_pkey" PRIMARY KEY ("id")
);

CREATE UNIQUE INDEX "eva_event_interest_unique" ON "eva_event_interests"("event_id", "customer_id");

CREATE TABLE "eva_rave_rooms" (
    "id" SERIAL NOT NULL,
    "slug" VARCHAR(80) NOT NULL,
    "title" VARCHAR(120) NOT NULL,
    "theme" VARCHAR(80) NOT NULL,
    "description" VARCHAR(400),
    "prompt" VARCHAR(255),
    "active" BOOLEAN NOT NULL DEFAULT true,
    "created_at" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT "eva_rave_rooms_pkey" PRIMARY KEY ("id")
);

CREATE UNIQUE INDEX "eva_rave_rooms_slug_key" ON "eva_rave_rooms"("slug");

CREATE TABLE "eva_rave_presence" (
    "id" SERIAL NOT NULL,
    "room_id" INTEGER NOT NULL,
    "customer_id" INTEGER NOT NULL,
    "joined_at" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "expires_at" TIMESTAMPTZ(6) NOT NULL,
    CONSTRAINT "eva_rave_presence_pkey" PRIMARY KEY ("id")
);

CREATE UNIQUE INDEX "eva_rave_presence_unique" ON "eva_rave_presence"("room_id", "customer_id");
CREATE INDEX "eva_rave_presence_expires_idx" ON "eva_rave_presence"("expires_at");

CREATE TABLE "eva_notifications" (
    "id" SERIAL NOT NULL,
    "customer_id" INTEGER NOT NULL,
    "kind" "eva_notification_kind" NOT NULL,
    "title" VARCHAR(160) NOT NULL,
    "body" VARCHAR(400) NOT NULL,
    "data_json" JSONB,
    "read_at" TIMESTAMPTZ(6),
    "created_at" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT "eva_notifications_pkey" PRIMARY KEY ("id")
);

CREATE INDEX "eva_notifications_user_idx" ON "eva_notifications"("customer_id", "created_at");

CREATE TABLE "eva_moderation_flags" (
    "id" SERIAL NOT NULL,
    "customer_id" INTEGER NOT NULL,
    "signal" VARCHAR(80) NOT NULL,
    "score" INTEGER NOT NULL DEFAULT 1,
    "meta_json" JSONB,
    "created_at" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT "eva_moderation_flags_pkey" PRIMARY KEY ("id")
);

CREATE INDEX "eva_moderation_flags_idx" ON "eva_moderation_flags"("customer_id", "signal");

-- Foreign keys
ALTER TABLE "eva_eligibility" ADD CONSTRAINT "eva_eligibility_customer_id_fkey" FOREIGN KEY ("customer_id") REFERENCES "customers"("id") ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE "eva_profiles" ADD CONSTRAINT "eva_profiles_customer_id_fkey" FOREIGN KEY ("customer_id") REFERENCES "customers"("id") ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE "eva_media" ADD CONSTRAINT "eva_media_profile_id_fkey" FOREIGN KEY ("profile_id") REFERENCES "eva_profiles"("id") ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE "eva_profile_interests" ADD CONSTRAINT "eva_profile_interests_profile_id_fkey" FOREIGN KEY ("profile_id") REFERENCES "eva_profiles"("id") ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE "eva_profile_interests" ADD CONSTRAINT "eva_profile_interests_interest_id_fkey" FOREIGN KEY ("interest_id") REFERENCES "eva_interests"("id") ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE "eva_profile_prompts" ADD CONSTRAINT "eva_profile_prompts_profile_id_fkey" FOREIGN KEY ("profile_id") REFERENCES "eva_profiles"("id") ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE "eva_profile_prompts" ADD CONSTRAINT "eva_profile_prompts_prompt_id_fkey" FOREIGN KEY ("prompt_id") REFERENCES "eva_prompts"("id") ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE "eva_preferences" ADD CONSTRAINT "eva_preferences_customer_id_fkey" FOREIGN KEY ("customer_id") REFERENCES "customers"("id") ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE "eva_likes" ADD CONSTRAINT "eva_likes_from_profile_id_fkey" FOREIGN KEY ("from_profile_id") REFERENCES "eva_profiles"("id") ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE "eva_likes" ADD CONSTRAINT "eva_likes_to_profile_id_fkey" FOREIGN KEY ("to_profile_id") REFERENCES "eva_profiles"("id") ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE "eva_passes" ADD CONSTRAINT "eva_passes_from_profile_id_fkey" FOREIGN KEY ("from_profile_id") REFERENCES "eva_profiles"("id") ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE "eva_passes" ADD CONSTRAINT "eva_passes_to_profile_id_fkey" FOREIGN KEY ("to_profile_id") REFERENCES "eva_profiles"("id") ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE "eva_matches" ADD CONSTRAINT "eva_matches_profile_a_id_fkey" FOREIGN KEY ("profile_a_id") REFERENCES "eva_profiles"("id") ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE "eva_matches" ADD CONSTRAINT "eva_matches_profile_b_id_fkey" FOREIGN KEY ("profile_b_id") REFERENCES "eva_profiles"("id") ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE "eva_conversations" ADD CONSTRAINT "eva_conversations_match_id_fkey" FOREIGN KEY ("match_id") REFERENCES "eva_matches"("id") ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE "eva_messages" ADD CONSTRAINT "eva_messages_conversation_id_fkey" FOREIGN KEY ("conversation_id") REFERENCES "eva_conversations"("id") ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE "eva_typing" ADD CONSTRAINT "eva_typing_conversation_id_fkey" FOREIGN KEY ("conversation_id") REFERENCES "eva_conversations"("id") ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE "eva_blocks" ADD CONSTRAINT "eva_blocks_blocker_customer_id_fkey" FOREIGN KEY ("blocker_customer_id") REFERENCES "customers"("id") ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE "eva_blocks" ADD CONSTRAINT "eva_blocks_blocked_customer_id_fkey" FOREIGN KEY ("blocked_customer_id") REFERENCES "customers"("id") ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE "eva_reports" ADD CONSTRAINT "eva_reports_reporter_customer_id_fkey" FOREIGN KEY ("reporter_customer_id") REFERENCES "customers"("id") ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE "eva_event_interests" ADD CONSTRAINT "eva_event_interests_event_id_fkey" FOREIGN KEY ("event_id") REFERENCES "eva_dating_events"("id") ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE "eva_event_interests" ADD CONSTRAINT "eva_event_interests_customer_id_fkey" FOREIGN KEY ("customer_id") REFERENCES "customers"("id") ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE "eva_rave_presence" ADD CONSTRAINT "eva_rave_presence_room_id_fkey" FOREIGN KEY ("room_id") REFERENCES "eva_rave_rooms"("id") ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE "eva_rave_presence" ADD CONSTRAINT "eva_rave_presence_customer_id_fkey" FOREIGN KEY ("customer_id") REFERENCES "customers"("id") ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE "eva_notifications" ADD CONSTRAINT "eva_notifications_customer_id_fkey" FOREIGN KEY ("customer_id") REFERENCES "customers"("id") ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE "eva_moderation_flags" ADD CONSTRAINT "eva_moderation_flags_customer_id_fkey" FOREIGN KEY ("customer_id") REFERENCES "customers"("id") ON DELETE CASCADE ON UPDATE CASCADE;

-- Seed catalog data
INSERT INTO "eva_interests" ("slug", "label", "category") VALUES
('music', 'Music', 'culture'),
('travel', 'Travel', 'lifestyle'),
('food', 'Food', 'lifestyle'),
('fitness', 'Fitness', 'lifestyle'),
('movies', 'Movies', 'culture'),
('books', 'Books', 'culture'),
('gaming', 'Gaming', 'culture'),
('art', 'Art', 'culture'),
('sports', 'Sports', 'lifestyle'),
('pets', 'Pets', 'lifestyle'),
('nature', 'Nature', 'lifestyle'),
('photography', 'Photography', 'culture'),
('technology', 'Technology', 'work'),
('business', 'Business', 'work'),
('fashion', 'Fashion', 'culture'),
('cooking', 'Cooking', 'lifestyle'),
('events', 'Events', 'social'),
('nightlife', 'Nightlife', 'social');

INSERT INTO "eva_prompts" ("slug", "text", "sort_order") VALUES
('sunday_ritual', 'A perfect Sunday for me looks like…', 1),
('never_tired', 'Something I never get tired of…', 2),
('make_me_laugh', 'The quickest way to make me laugh…', 3),
('two_truths', 'Two truths and a lie…', 4),
('ideal_first_date', 'My ideal first date…', 5),
('passionate_about', 'Something I am quietly passionate about…', 6),
('green_flag', 'A green flag I always notice…', 7),
('comfort_meal', 'My comfort meal after a long week…', 8),
('travel_dream', 'A place I want to experience with someone…', 9),
('bloom_moment', 'I feel most like myself when…', 10);

INSERT INTO "eva_rave_rooms" ("slug", "title", "theme", "description", "prompt") VALUES
('bloom-hour', 'Bloom Hour', 'connection', 'Soft check-ins with people who love thoughtful conversation.', 'What made you smile today?'),
('night-bloom', 'Night Bloom', 'nightlife', 'Late-energy room for music lovers and night owls.', 'What song is on repeat right now?'),
('taste-trail', 'Taste Trail', 'food', 'Foodies sharing cravings and hidden spots.', 'Name a dish you could eat forever.'),
('trail-notes', 'Trail Notes', 'outdoors', 'Nature and movement minded people.', 'Favorite outdoor reset?');

INSERT INTO "eva_dating_events" ("title", "slug", "summary", "description", "city", "venue_approx", "starts_at", "ends_at", "capacity", "active") VALUES
('Garden Conversations', 'garden-conversations', 'An evening of slow introductions among blooms.', 'Meet people who prefer conversation over small talk in a garden lounge setting.', 'Delhi', 'Central garden district', NOW() + INTERVAL '7 days', NOW() + INTERVAL '7 days' + INTERVAL '3 hours', 40, true),
('Taste & Connect', 'taste-and-connect', 'Shared plates, shared stories.', 'A food-forward social for people who bond over flavor.', 'Mumbai', 'Bandra culinary lane', NOW() + INTERVAL '10 days', NOW() + INTERVAL '10 days' + INTERVAL '3 hours', 30, true),
('Sunset Social', 'sunset-social', 'Golden-hour mingling with soft music.', 'Casual open-air gathering for new connections.', 'Bengaluru', 'Lakeside promenade', NOW() + INTERVAL '14 days', NOW() + INTERVAL '14 days' + INTERVAL '2 hours', 50, true);
