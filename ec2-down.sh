#!/usr/bin/env bash
set -e

COMPOSE_FILE="docker-compose.prod.yml"

# -----------------------------------------------------------------
# Stop & remove containers
# -----------------------------------------------------------------
echo "🛑  Stopping production stack..."
docker compose -f "$COMPOSE_FILE" down
echo ""
echo "✅  All containers stopped and removed."
