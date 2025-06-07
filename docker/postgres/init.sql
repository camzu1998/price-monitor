CREATE DATABASE price_monitor_test;
GRANT ALL PRIVILEGES ON DATABASE price_monitor_test TO dev_user;

\c price_monitor;
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pg_trgm";

\c price_monitor_test;
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pg_trgm";
