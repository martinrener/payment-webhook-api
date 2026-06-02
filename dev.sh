#!/usr/bin/env bash
set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
GCP_HOST="35.225.178.162"
GCP_INSTANCE="payment-webhook"
GCP_ZONE="us-central1-a"
GCP_USER="martin_rener"
FRONTEND_DIR="$SCRIPT_DIR/frontend"

GCP_DIR="~/payment-webhook-api"

ssh_gcp() {
  gcloud compute ssh "${GCP_USER}@${GCP_INSTANCE}" --zone="${GCP_ZONE}" --command="cd ${GCP_DIR} && $1"
}

cmd_up() {
  echo "Starting backend on GCP..."
  ssh_gcp "sudo docker-compose -f docker-compose.prod.yml up -d"
  echo ""
  echo "Backend running at http://${GCP_HOST}"
  echo "API health check: http://${GCP_HOST}/api/health"
}

cmd_down() {
  echo "Stopping backend on GCP..."
  ssh_gcp "sudo docker-compose -f docker-compose.prod.yml down"
  echo ""
  echo "Backend stopped."
}

cmd_redeploy() {
  echo "Redeploying on GCP..."
  ssh_gcp "git pull origin main && sudo docker-compose -f docker-compose.prod.yml up -d"
  echo ""
  echo "Redeployed successfully."
}

cmd_frontend() {
  echo "Starting frontend locally..."
  echo "Frontend running at http://localhost:3000"
  cd "$FRONTEND_DIR" && npm run dev
}

cmd_dev() {
  echo "Starting backend on GCP + frontend locally..."
  ssh_gcp "sudo docker-compose -f docker-compose.prod.yml up -d" &
  UP_PID=$!
  (cd "$FRONTEND_DIR" && npm run dev) &
  FRONTEND_PID=$!
  wait $UP_PID
  echo ""
  echo "======================================"
  echo "  Backend:  http://${GCP_HOST}"
  echo "  API:      http://${GCP_HOST}/api/health"
  echo "  Frontend: http://localhost:3000"
  echo "======================================"
  wait $FRONTEND_PID
}

case "$1" in
  up)       cmd_up ;;
  down)     cmd_down ;;
  redeploy) cmd_redeploy ;;
  frontend) cmd_frontend ;;
  dev)      cmd_dev ;;
  *)
    echo "Usage: $0 {up|down|redeploy|frontend|dev}"
    echo ""
    echo "  $0 up        → start backend on GCP"
    echo "  $0 down      → stop backend on GCP"
    echo "  $0 redeploy  → pull + rebuild on GCP"
    echo "  $0 frontend  → start frontend locally"
    echo "  $0 dev       → start everything"
    exit 1
    ;;
esac
