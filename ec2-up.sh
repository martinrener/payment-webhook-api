#!/usr/bin/env bash
set -e

COMPOSE_FILE="docker-compose.prod.yml"
APP_PORT="${APP_PORT:-8000}"
HEALTH_URL="http://localhost:${APP_PORT}/api/health"
TIMEOUT=120   # seconds to wait for healthy
INTERVAL=5

# -----------------------------------------------------------------
# Helpers
# -----------------------------------------------------------------
public_ip() {
  curl -sf --max-time 2 http://169.254.169.254/latest/meta-data/public-ipv4 2>/dev/null \
    || hostname -I 2>/dev/null | awk '{print $1}' \
    || echo "localhost"
}

# -----------------------------------------------------------------
# Build & start
# -----------------------------------------------------------------
echo "🚀  Building and starting production stack..."
docker compose -f "$COMPOSE_FILE" up -d --build
echo "✅  Containers started."

# -----------------------------------------------------------------
# Wait for app to be healthy
# -----------------------------------------------------------------
echo ""
echo "⏳  Waiting for app to be healthy (timeout: ${TIMEOUT}s)..."

elapsed=0
until curl -sf "$HEALTH_URL" > /dev/null 2>&1; do
  if [ "$elapsed" -ge "$TIMEOUT" ]; then
    echo ""
    echo "❌  Timed out after ${TIMEOUT}s — app did not become healthy."
    echo "    Check logs with: docker compose -f $COMPOSE_FILE logs app"
    exit 1
  fi
  echo -n "."
  sleep "$INTERVAL"
  elapsed=$(( elapsed + INTERVAL ))
done

echo ""
echo "✅  App is healthy."

# -----------------------------------------------------------------
# Summary
# -----------------------------------------------------------------
IP=$(public_ip)
echo ""
echo "======================================"
echo "  🌐  App:    http://${IP}:${APP_PORT}"
echo "  ❤️   Health: ${HEALTH_URL}"
echo "======================================"
