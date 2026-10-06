#!/bin/bash
# SessionStart hook for WP4BD — boots a runnable Backdrop site so agents can
# verify rendering (curl/screenshot) instead of coding blind.
#
# What it does (idempotent):
#   1. Installs MariaDB server/client if missing (apt).
#   2. Starts MariaDB and grants root TCP access (settings.php expects
#      root@127.0.0.1:3306, empty password — see commit e508e22).
#   3. Creates the `backdrop` database and seeds it from DB/wp4bd-bd-dec8-db.sql.gz
#      if the database is empty.
#   4. Starts PHP's built-in server on port 8080 with a clean-URL router.
#
# The site is then reachable at http://127.0.0.1:8080 ($WP4BD_URL).

set -euo pipefail

# Only needed in remote (Claude Code on the web) sessions; local dev uses ddev.
if [ "${CLAUDE_CODE_REMOTE:-}" != "true" ]; then
  exit 0
fi

REPO="${CLAUDE_PROJECT_DIR:-/home/user/wp2bd}"
DOCROOT="$REPO/backdrop-1.30"
DB_DUMP="$REPO/DB/wp4bd-bd-dec8-db.sql.gz"
LOG=/tmp/wp4bd-session-start.log

log() { echo "[wp4bd-hook] $*" | tee -a "$LOG"; }

# --- 1. MariaDB install ------------------------------------------------------
if ! command -v mariadbd >/dev/null 2>&1 && ! command -v mysqld >/dev/null 2>&1; then
  log "Installing MariaDB (first run in this container)..."
  export DEBIAN_FRONTEND=noninteractive
  apt-get update -qq >>"$LOG" 2>&1
  apt-get install -y -qq mariadb-server mariadb-client >>"$LOG" 2>&1
fi

# --- 2. Start MariaDB --------------------------------------------------------
if ! mysqladmin ping --silent 2>/dev/null; then
  log "Starting MariaDB..."
  mkdir -p /run/mysqld
  chown mysql:mysql /run/mysqld 2>/dev/null || true
  service mariadb start >>"$LOG" 2>&1 || service mysql start >>"$LOG" 2>&1 || {
    (mysqld_safe --skip-syslog >>"$LOG" 2>&1 &)
  }
  for _ in $(seq 1 30); do
    mysqladmin ping --silent 2>/dev/null && break
    sleep 1
  done
fi
mysqladmin ping --silent 2>/dev/null || { log "ERROR: MariaDB did not start; see $LOG"; exit 1; }

# --- 3. Root TCP access (Backdrop connects to 127.0.0.1:3306 as root, no pw) --
# Debian MariaDB gives root@localhost unix_socket-only auth, which rejects TCP
# (error 1698) — and 127.0.0.1 resolves to 'localhost', so that is the account
# that must allow it. Keep socket auth AND allow an empty password over TCP.
mysql -e "ALTER USER 'root'@'localhost' IDENTIFIED VIA unix_socket OR mysql_native_password USING PASSWORD('');
          FLUSH PRIVILEGES;" 2>/dev/null || \
mysql -e "ALTER USER 'root'@'localhost' IDENTIFIED VIA mysql_native_password USING PASSWORD('');
          FLUSH PRIVILEGES;"

# --- 4. Create + seed database ------------------------------------------------
mysql -e "CREATE DATABASE IF NOT EXISTS backdrop CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;"
if ! mysql backdrop -e "SELECT 1 FROM node LIMIT 1" >/dev/null 2>&1; then
  log "Seeding backdrop database from $(basename "$DB_DUMP")..."
  zcat "$DB_DUMP" | mysql backdrop
else
  log "Database already seeded."
fi

# --- 5. Writable files directory ----------------------------------------------
chmod -R a+w "$DOCROOT/files" 2>/dev/null || true

# --- 6. PHP built-in server with clean-URL router ------------------------------
if ! curl -s -o /dev/null --max-time 2 http://127.0.0.1:8080; then
  log "Starting PHP server on http://127.0.0.1:8080 ..."
  nohup php -d error_reporting="E_ALL & ~E_DEPRECATED" \
    -S 127.0.0.1:8080 -t "$DOCROOT" "$REPO/.claude/hooks/router.php" \
    > /tmp/wp4bd-php-server.log 2>&1 &
  sleep 2
fi

# Expose the site URL to the session.
if [ -n "${CLAUDE_ENV_FILE:-}" ]; then
  echo 'export WP4BD_URL=http://127.0.0.1:8080' >> "$CLAUDE_ENV_FILE"
fi

log "Ready: site at http://127.0.0.1:8080 (front page: curl -s http://127.0.0.1:8080/)"
