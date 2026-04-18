CREATE TABLE IF NOT EXISTS "migrations"(
  "id" integer primary key autoincrement not null,
  "migration" varchar not null,
  "batch" integer not null
);
CREATE TABLE IF NOT EXISTS "password_reset_tokens"(
  "email" varchar not null,
  "token" varchar not null,
  "created_at" datetime,
  primary key("email")
);
CREATE TABLE IF NOT EXISTS "sessions"(
  "id" varchar not null,
  "user_id" integer,
  "ip_address" varchar,
  "user_agent" text,
  "payload" text not null,
  "last_activity" integer not null,
  primary key("id")
);
CREATE INDEX "sessions_user_id_index" on "sessions"("user_id");
CREATE INDEX "sessions_last_activity_index" on "sessions"("last_activity");
CREATE TABLE IF NOT EXISTS "cache"(
  "key" varchar not null,
  "value" text not null,
  "expiration" integer not null,
  primary key("key")
);
CREATE TABLE IF NOT EXISTS "cache_locks"(
  "key" varchar not null,
  "owner" varchar not null,
  "expiration" integer not null,
  primary key("key")
);
CREATE TABLE IF NOT EXISTS "jobs"(
  "id" integer primary key autoincrement not null,
  "queue" varchar not null,
  "payload" text not null,
  "attempts" integer not null,
  "reserved_at" integer,
  "available_at" integer not null,
  "created_at" integer not null
);
CREATE INDEX "jobs_queue_index" on "jobs"("queue");
CREATE TABLE IF NOT EXISTS "job_batches"(
  "id" varchar not null,
  "name" varchar not null,
  "total_jobs" integer not null,
  "pending_jobs" integer not null,
  "failed_jobs" integer not null,
  "failed_job_ids" text not null,
  "options" text,
  "cancelled_at" integer,
  "created_at" integer not null,
  "finished_at" integer,
  primary key("id")
);
CREATE TABLE IF NOT EXISTS "failed_jobs"(
  "id" integer primary key autoincrement not null,
  "uuid" varchar not null,
  "connection" text not null,
  "queue" text not null,
  "payload" text not null,
  "exception" text not null,
  "failed_at" datetime not null default CURRENT_TIMESTAMP
);
CREATE UNIQUE INDEX "failed_jobs_uuid_unique" on "failed_jobs"("uuid");
CREATE TABLE IF NOT EXISTS "personal_access_tokens"(
  "id" integer primary key autoincrement not null,
  "tokenable_type" varchar not null,
  "tokenable_id" integer not null,
  "name" varchar not null,
  "token" varchar not null,
  "abilities" text,
  "last_used_at" datetime,
  "expires_at" datetime,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "personal_access_tokens_tokenable_type_tokenable_id_index" on "personal_access_tokens"(
  "tokenable_type",
  "tokenable_id"
);
CREATE UNIQUE INDEX "personal_access_tokens_token_unique" on "personal_access_tokens"(
  "token"
);
CREATE INDEX "personal_access_tokens_expires_at_index" on "personal_access_tokens"(
  "expires_at"
);
CREATE TABLE IF NOT EXISTS "carts"(
  "id" integer primary key autoincrement not null,
  "user_id" integer not null,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("user_id") references "users"("id") on delete cascade
);
CREATE TABLE IF NOT EXISTS "cart_items"(
  "id" integer primary key autoincrement not null,
  "cart_id" integer not null,
  "product_id" integer not null,
  "quantity" integer not null default '1',
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("cart_id") references "carts"("id") on delete cascade,
  foreign key("product_id") references "products"("id") on delete cascade
);
CREATE TABLE IF NOT EXISTS "order_items"(
  "id" integer primary key autoincrement not null,
  "order_id" integer not null,
  "product_id" integer not null,
  "quantity" integer not null,
  "price" numeric not null,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("order_id") references "orders"("id") on delete cascade,
  foreign key("product_id") references "products"("id") on delete cascade
);
CREATE TABLE IF NOT EXISTS "addresses"(
  "id" integer primary key autoincrement not null,
  "user_id" integer not null,
  "type" varchar not null default 'Home',
  "address" varchar not null,
  "city" varchar not null,
  "state" varchar,
  "zip" varchar,
  "country" varchar not null default 'Tanzania',
  "is_default" tinyint(1) not null default '0',
  "created_at" datetime,
  "updated_at" datetime,
  "latitude" numeric,
  "longitude" numeric,
  foreign key("user_id") references "users"("id") on delete cascade
);
CREATE TABLE IF NOT EXISTS "orders"(
  "id" integer primary key autoincrement not null,
  "user_id" integer not null,
  "status" varchar not null default('pending'),
  "total_price" numeric not null,
  "shipping_address" text,
  "payment_method" varchar,
  "created_at" datetime,
  "updated_at" datetime,
  "address_id" integer,
  foreign key("user_id") references users("id") on delete cascade on update no action,
  foreign key("address_id") references "addresses"("id") on delete set null
);
CREATE TABLE IF NOT EXISTS "settings"(
  "id" integer primary key autoincrement not null,
  "key" varchar not null,
  "value" text,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "settings_key_unique" on "settings"("key");
CREATE TABLE IF NOT EXISTS "selcompays"(
  "id" integer primary key autoincrement not null,
  "transid" varchar not null,
  "order_id" varchar,
  "phone_number" varchar not null,
  "amount" numeric not null,
  "payment_status" varchar not null default 'pending',
  "local_order_id" integer,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("local_order_id") references "orders"("id") on delete cascade
);
CREATE UNIQUE INDEX "selcompays_transid_unique" on "selcompays"("transid");
CREATE TABLE IF NOT EXISTS "categories"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "created_at" datetime,
  "updated_at" datetime,
  "image" varchar
);
CREATE TABLE IF NOT EXISTS "products"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "brand" varchar not null default('Samsung'),
  "price" numeric not null,
  "stock_quantity" integer not null default('0'),
  "description" text,
  "created_at" datetime,
  "updated_at" datetime,
  "images" text,
  "rating" numeric not null default('5'),
  "category_id" integer,
  foreign key("category_id") references "categories"("id") on delete set null
);
CREATE TABLE IF NOT EXISTS "shop_records"(
  "id" integer primary key autoincrement not null,
  "product_id" integer not null,
  "opening_stock" integer not null default '0',
  "quantity_sold" integer not null default '0',
  "transfer_quantity" integer not null default '0',
  "date" date not null,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("product_id") references "products"("id") on delete cascade
);
CREATE TABLE IF NOT EXISTS "payables"(
  "id" integer primary key autoincrement not null,
  "item_name" varchar not null,
  "amount" numeric not null,
  "date" date not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE TABLE IF NOT EXISTS "users"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "email" varchar not null,
  "email_verified_at" datetime,
  "password" varchar not null,
  "remember_token" varchar,
  "created_at" datetime,
  "updated_at" datetime,
  "role" varchar not null default('customer'),
  "status" varchar not null default('active'),
  "business_name" varchar,
  "business_type" varchar,
  "category_id" integer,
  "phone" varchar,
  "how_did_you_hear" varchar,
  "referred_by" integer,
  foreign key("category_id") references categories("id") on delete no action on update no action,
  foreign key("referred_by") references "users"("id") on delete set null
);
CREATE UNIQUE INDEX "users_email_unique" on "users"("email");
CREATE TABLE IF NOT EXISTS "distribution_sales"(
  "id" integer primary key autoincrement not null,
  "dealer_name" varchar,
  "seller_name" varchar,
  "product_id" integer not null,
  "quantity_sold" integer not null,
  "purchase_price" numeric,
  "selling_price" numeric,
  "total_purchase_value" numeric,
  "total_selling_value" numeric,
  "profit" numeric,
  "to_be_paid" numeric,
  "paid_amount" numeric not null default('0'),
  "collection_date" date,
  "collected_amount" numeric,
  "balance" numeric not null default('0'),
  "date" date not null,
  "created_at" datetime,
  "updated_at" datetime,
  "dealer_id" integer,
  "order_id" integer,
  "commission" numeric not null default '0',
  "status" varchar not null default 'pending',
  foreign key("product_id") references products("id") on delete cascade on update no action,
  foreign key("dealer_id") references "users"("id") on delete set null,
  foreign key("order_id") references "orders"("id") on delete set null
);
CREATE TABLE IF NOT EXISTS "agent_assignments"(
  "id" integer primary key autoincrement not null,
  "agent_id" integer not null,
  "product_id" integer not null,
  "quantity_assigned" integer not null default '0',
  "quantity_sold" integer not null default '0',
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("agent_id") references "users"("id") on delete cascade,
  foreign key("product_id") references "products"("id") on delete cascade
);
CREATE UNIQUE INDEX "agent_assignments_agent_id_product_id_unique" on "agent_assignments"(
  "agent_id",
  "product_id"
);
CREATE TABLE IF NOT EXISTS "agent_sales"(
  "id" integer primary key autoincrement not null,
  "customer_name" varchar,
  "seller_name" varchar,
  "product_id" integer not null,
  "quantity_sold" integer not null,
  "purchase_price" numeric,
  "selling_price" numeric,
  "total_purchase_value" numeric,
  "total_selling_value" numeric,
  "profit" numeric,
  "commission_paid" numeric,
  "date_of_collection" date,
  "balance" numeric not null default('0'),
  "stock_remaining" integer,
  "date" date not null,
  "created_at" datetime,
  "updated_at" datetime,
  "agent_id" integer,
  foreign key("product_id") references products("id") on delete cascade on update no action,
  foreign key("agent_id") references "users"("id") on delete set null
);
CREATE TABLE IF NOT EXISTS "stocks"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "stock_limit" integer not null,
  "created_at" datetime,
  "updated_at" datetime,
  "default_category_id" integer,
  "default_model" varchar,
  "default_quantity" integer,
  foreign key("default_category_id") references "categories"("id") on delete set null
);
CREATE TABLE IF NOT EXISTS "purchases"(
  "id" integer primary key autoincrement not null,
  "product_id" integer not null,
  "quantity" integer not null,
  "unit_price" numeric not null,
  "distributor_name" varchar,
  "total_amount" numeric,
  "paid_date" date,
  "paid_amount" numeric not null default('0'),
  "payment_status" varchar not null default('pending'),
  "date" date not null,
  "created_at" datetime,
  "updated_at" datetime,
  "stock_id" integer,
  "limit_status" varchar not null default('pending'),
  "limit_remaining" integer,
  "sell_price" numeric,
  "name" varchar,
  "payment_receipt_image" varchar,
  "payment_option_id" integer,
  foreign key("stock_id") references stocks("id") on delete set null on update no action,
  foreign key("product_id") references products("id") on delete cascade on update no action,
  foreign key("payment_option_id") references "payment_options"("id") on delete set null
);
CREATE TABLE IF NOT EXISTS "purchase_payments"(
  "id" integer primary key autoincrement not null,
  "purchase_id" integer not null,
  "payment_option_id" integer,
  "amount" numeric not null,
  "paid_date" date,
  "notes" text,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("purchase_id") references "purchases"("id") on delete cascade,
  foreign key("payment_option_id") references "payment_options"("id") on delete set null
);
CREATE TABLE IF NOT EXISTS "payment_options"(
  "id" integer primary key autoincrement not null,
  "type" varchar not null,
  "name" varchar not null,
  "balance" numeric not null default '0',
  "created_at" datetime,
  "updated_at" datetime
);
CREATE TABLE IF NOT EXISTS "expenses"(
  "id" integer primary key autoincrement not null,
  "activity" varchar not null,
  "amount" numeric not null,
  "cash_used" varchar not null,
  "date" date not null,
  "created_at" datetime,
  "updated_at" datetime,
  "payment_option_id" integer,
  foreign key("payment_option_id") references "payment_options"("id") on delete set null
);
CREATE TABLE IF NOT EXISTS "agent_product_transfers"(
  "id" integer primary key autoincrement not null,
  "from_agent_id" integer not null,
  "to_agent_id" integer not null,
  "status" varchar not null default 'pending',
  "message" text,
  "admin_note" text,
  "decided_at" datetime,
  "decided_by" integer,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("from_agent_id") references "users"("id") on delete cascade,
  foreign key("to_agent_id") references "users"("id") on delete cascade,
  foreign key("decided_by") references "users"("id") on delete set null
);
CREATE INDEX "agent_product_transfers_status_created_at_index" on "agent_product_transfers"(
  "status",
  "created_at"
);
CREATE TABLE IF NOT EXISTS "agent_product_transfer_items"(
  "id" integer primary key autoincrement not null,
  "agent_product_transfer_id" integer not null,
  "product_list_id" integer not null,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("agent_product_transfer_id") references "agent_product_transfers"("id") on delete cascade,
  foreign key("product_list_id") references "product_list"("id") on delete cascade
);
CREATE UNIQUE INDEX "agent_product_transfer_items_agent_product_transfer_id_product_list_id_unique" on "agent_product_transfer_items"(
  "agent_product_transfer_id",
  "product_list_id"
);
CREATE TABLE IF NOT EXISTS "product_list"(
  "id" integer primary key autoincrement not null,
  "stock_id" integer not null,
  "category_id" integer not null,
  "model" varchar not null,
  "imei_number" varchar not null,
  "product_id" integer,
  "sold_at" datetime,
  "agent_sale_id" integer,
  "created_at" datetime,
  "updated_at" datetime,
  "purchase_id" integer,
  "branch_id" integer,
  foreign key("purchase_id") references purchases("id") on delete set null on update no action,
  foreign key("stock_id") references stocks("id") on delete cascade on update no action,
  foreign key("category_id") references categories("id") on delete cascade on update no action,
  foreign key("product_id") references products("id") on delete set null on update no action,
  foreign key("agent_sale_id") references agent_sales("id") on delete set null on update no action,
  foreign key("branch_id") references "branches"("id") on delete set null
);
CREATE UNIQUE INDEX "product_list_imei_number_unique" on "product_list"(
  "imei_number"
);
CREATE TABLE IF NOT EXISTS "branch_transfer_logs"(
  "id" integer primary key autoincrement not null,
  "product_list_id" integer not null,
  "from_branch_id" integer,
  "to_branch_id" integer,
  "admin_id" integer,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("product_list_id") references "product_list"("id") on delete cascade,
  foreign key("from_branch_id") references "branches"("id") on delete set null,
  foreign key("to_branch_id") references "branches"("id") on delete set null,
  foreign key("admin_id") references "users"("id") on delete set null
);
CREATE INDEX "branch_transfer_logs_created_at_index" on "branch_transfer_logs"(
  "created_at"
);
CREATE TABLE IF NOT EXISTS "pending_sales"(
  "id" integer primary key autoincrement not null,
  "customer_name" varchar,
  "seller_name" varchar,
  "product_id" integer not null,
  "quantity_sold" integer not null,
  "purchase_price" numeric,
  "selling_price" numeric,
  "total_purchase_value" numeric,
  "total_selling_value" numeric,
  "profit" numeric,
  "payment_option_id" integer,
  "date" date not null,
  "created_at" datetime,
  "updated_at" datetime,
  "seller_id" integer,
  foreign key("payment_option_id") references payment_options("id") on delete set null on update no action,
  foreign key("product_id") references products("id") on delete cascade on update no action,
  foreign key("seller_id") references "users"("id") on delete set null
);
CREATE TABLE IF NOT EXISTS "distribution_sale_payments"(
  "id" integer primary key autoincrement not null,
  "distribution_sale_id" integer not null,
  "payment_option_id" integer,
  "amount" numeric not null,
  "paid_date" date,
  "notes" text,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("distribution_sale_id") references "distribution_sales"("id") on delete cascade,
  foreign key("payment_option_id") references "payment_options"("id") on delete set null
);
CREATE TABLE IF NOT EXISTS "customer_needs"(
  "id" integer primary key autoincrement not null,
  "agent_id" integer not null,
  "category_id" integer not null,
  "product_id" integer not null,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("agent_id") references "users"("id") on delete cascade,
  foreign key("category_id") references "categories"("id") on delete cascade,
  foreign key("product_id") references "products"("id") on delete cascade
);

INSERT INTO migrations VALUES(1,'0001_01_01_000000_create_users_table',1);
INSERT INTO migrations VALUES(2,'0001_01_01_000001_create_cache_table',1);
INSERT INTO migrations VALUES(3,'0001_01_01_000002_create_jobs_table',1);
INSERT INTO migrations VALUES(4,'2019_12_14_000001_create_personal_access_tokens_table',1);
INSERT INTO migrations VALUES(5,'2026_01_19_190141_add_role_to_users_table',1);
INSERT INTO migrations VALUES(6,'2026_01_19_193406_create_products_table',1);
INSERT INTO migrations VALUES(7,'2026_01_19_194238_change_image_path_to_images_in_products_table',1);
INSERT INTO migrations VALUES(8,'2026_01_19_201008_add_rating_to_products_table',1);
INSERT INTO migrations VALUES(9,'2026_01_19_202047_create_carts_table',1);
INSERT INTO migrations VALUES(10,'2026_01_19_202048_create_cart_items_table',1);
INSERT INTO migrations VALUES(11,'2026_01_19_202050_create_orders_table',1);
INSERT INTO migrations VALUES(12,'2026_01_19_202052_create_order_items_table',1);
INSERT INTO migrations VALUES(13,'2026_01_19_213034_create_addresses_table',1);
INSERT INTO migrations VALUES(14,'2026_01_19_214006_add_status_to_users_table',1);
INSERT INTO migrations VALUES(15,'2026_01_19_221029_add_lat_long_to_addresses_table',1);
INSERT INTO migrations VALUES(16,'2026_01_19_223246_add_address_id_to_orders_table',1);
INSERT INTO migrations VALUES(17,'2026_01_21_193721_create_settings_table',1);
INSERT INTO migrations VALUES(18,'2026_01_21_201717_create_selcompays_table',1);
INSERT INTO migrations VALUES(19,'2026_01_25_161236_create_categories_table',1);
INSERT INTO migrations VALUES(20,'2026_01_25_161244_add_category_id_to_products_table',1);
INSERT INTO migrations VALUES(21,'2026_01_26_234744_add_image_to_categories_table',1);
INSERT INTO migrations VALUES(22,'2026_01_27_000353_add_dealer_fields_to_users_table',1);
INSERT INTO migrations VALUES(23,'2026_02_06_185855_create_stock_tables',1);
INSERT INTO migrations VALUES(24,'2026_02_06_201700_add_how_did_you_hear_to_users_table',1);
INSERT INTO migrations VALUES(25,'2026_02_08_000001_add_distribution_and_referrer_fields',1);
INSERT INTO migrations VALUES(26,'2026_02_08_100000_create_agent_assignments_and_agent_id_on_agent_sales',1);
INSERT INTO migrations VALUES(27,'2026_02_15_025930_create_expenses_table',1);
INSERT INTO migrations VALUES(28,'2026_02_22_100000_create_stocks_table',1);
INSERT INTO migrations VALUES(29,'2026_02_22_100001_create_product_list_table',1);
INSERT INTO migrations VALUES(30,'2026_02_22_120000_add_stock_to_purchases_and_defaults_to_stocks',1);
INSERT INTO migrations VALUES(31,'2026_02_24_100000_add_limit_status_sell_price_to_purchases',1);
INSERT INTO migrations VALUES(32,'2026_02_24_100001_add_purchase_id_to_product_list',1);
INSERT INTO migrations VALUES(33,'2026_02_24_120000_add_name_to_purchases',1);
INSERT INTO migrations VALUES(34,'2026_02_27_190651_add_payment_receipt_image_to_purchases_table',1);
INSERT INTO migrations VALUES(35,'2026_02_28_000000_add_payment_option_id_to_purchases_table',1);
INSERT INTO migrations VALUES(36,'2026_02_28_000001_create_purchase_payments_table',1);
INSERT INTO migrations VALUES(37,'2026_03_05_170829_create_payment_options_table',1);
INSERT INTO migrations VALUES(38,'2026_03_05_170832_create_pending_sales_table',1);
INSERT INTO migrations VALUES(39,'2026_04_02_100000_create_agent_product_transfers_tables',2);
INSERT INTO migrations VALUES(40,'2026_04_02_100002_create_branch_transfer_logs_table',3);
INSERT INTO migrations VALUES(41,'2026_04_02_100001_add_branch_id_to_product_list_table',4);
INSERT INTO migrations VALUES(42,'2026_04_05_120000_add_seller_id_to_pending_sales_table',5);
INSERT INTO migrations VALUES(43,'2026_04_12_000001_create_distribution_sale_payments_table',6);
INSERT INTO migrations VALUES(44,'2026_04_12_000002_create_customer_needs_table',7);
