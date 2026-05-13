#!/bin/bash
set -e

CLUSTER="payment-webhook-cluster"
REGION="us-east-1"
FRONTEND_DIR="$(dirname "$0")/frontend"
NUXT_PID_FILE="/tmp/nuxt-dev.pid"

# -----------------------------------------------------------------
# Environment definitions
# -----------------------------------------------------------------
declare -A SERVICES_PROD=(
  [app]="payment-webhook-service"
  [worker]="payment-webhook-worker"
  [reverb]="payment-webhook-reverb"
)
declare -A SERVICES_DEV=(
  [app]="payment-webhook-service-dev"
  [worker]="payment-webhook-worker-dev"
  [reverb]="payment-webhook-reverb-dev"
)
declare -A SERVICES_STAGING=(
  [app]="payment-webhook-service-staging"
  [worker]="payment-webhook-worker-staging"
  [reverb]="payment-webhook-reverb-staging"
)

ALB_PROD="http://payment-webhook-alb-156165760.us-east-1.elb.amazonaws.com"
ALB_DEV="http://payment-webhook-alb-dev-2133156852.us-east-1.elb.amazonaws.com"
ALB_STAGING="http://payment-webhook-alb-staging-974225436.us-east-1.elb.amazonaws.com"

REVERB_PROD="payment-webhook-reverb-nlb-34b0ab443285c8fb.elb.us-east-1.amazonaws.com:8080"

# -----------------------------------------------------------------
# Helpers
# -----------------------------------------------------------------
resolve_env() {
  local env="${1:-prod}"
  case "$env" in
    prod)    echo "prod" ;;
    dev)     echo "dev" ;;
    staging) echo "staging" ;;
    *)       echo "ERROR: ambiente desconocido '$env'. Usar: prod | dev | staging" >&2; exit 1 ;;
  esac
}

services_for() {
  # Prints the 3 service names for a given env
  local env="$1"
  case "$env" in
    prod)    echo "${SERVICES_PROD[app]}" "${SERVICES_PROD[worker]}" "${SERVICES_PROD[reverb]}" ;;
    dev)     echo "${SERVICES_DEV[app]}"  "${SERVICES_DEV[worker]}"  "${SERVICES_DEV[reverb]}"  ;;
    staging) echo "${SERVICES_STAGING[app]}" "${SERVICES_STAGING[worker]}" "${SERVICES_STAGING[reverb]}" ;;
  esac
}

alb_for() {
  case "$1" in
    prod)    echo "$ALB_PROD"    ;;
    dev)     echo "$ALB_DEV"     ;;
    staging) echo "$ALB_STAGING" ;;
  esac
}

scale_services() {
  local desired="$1"; shift
  local env="$1";     shift
  local services=("$@")

  echo "==> [${env}] Ajustando servicios a desired=${desired}..."
  for svc in "${services[@]}"; do
    aws ecs update-service \
      --cluster "$CLUSTER" --service "$svc" \
      --desired-count "$desired" \
      --region "$REGION" \
      --output text --query 'service.serviceName' > /dev/null
    echo "    ✓ $svc → desired: ${desired}"
  done
}

wait_running() {
  local env="$1"; shift
  local services=("$@")

  echo ""
  echo "==> [${env}] Esperando que los containers estén running..."
  for svc in "${services[@]}"; do
    echo -n "    $svc "
    until [ "$(aws ecs describe-services \
      --cluster "$CLUSTER" --services "$svc" \
      --region "$REGION" \
      --query 'services[0].runningCount' --output text 2>/dev/null)" -ge 1 ]; do
      echo -n "."; sleep 5
    done
    echo " running"
  done
}

wait_stopped() {
  local env="$1"; shift
  local services=("$@")

  echo ""
  echo "==> [${env}] Esperando que los containers terminen..."
  for svc in "${services[@]}"; do
    echo -n "    $svc "
    until [ "$(aws ecs describe-services \
      --cluster "$CLUSTER" --services "$svc" \
      --region "$REGION" \
      --query 'services[0].runningCount' --output text 2>/dev/null)" -eq 0 ]; do
      echo -n "."; sleep 5
    done
    echo " stopped"
  done
}

wait_healthy() {
  local env="$1"
  local alb_url="$2"

  echo ""
  echo "==> [${env}] Esperando health check del ALB..."
  echo -n "    app "
  until curl -s -o /dev/null -w "%{http_code}" "${alb_url}/api/health" 2>/dev/null | grep -q "200"; do
    echo -n "."; sleep 5
  done
  echo " healthy"
}

start_frontend() {
  local alb_url="$1"

  echo ""
  echo "==> Arrancando frontend Nuxt..."
  cd "$FRONTEND_DIR"
  npm run dev > /tmp/nuxt-dev.log 2>&1 &
  echo $! > "$NUXT_PID_FILE"
  echo -n "    frontend "
  until curl -s -o /dev/null -w "%{http_code}" http://localhost:3000/login 2>/dev/null | grep -q "200"; do
    echo -n "."; sleep 3
  done
  echo " ready"
}

stop_frontend() {
  echo "==> Matando frontend Nuxt..."
  if [ -f "$NUXT_PID_FILE" ]; then
    PID=$(cat "$NUXT_PID_FILE")
    kill "$PID" 2>/dev/null \
      && echo "    ✓ Nuxt (pid $PID) terminado" \
      || echo "    ! Nuxt ya no estaba corriendo"
    rm -f "$NUXT_PID_FILE"
  else
    pkill -f "nuxt dev" 2>/dev/null \
      && echo "    ✓ Nuxt terminado" \
      || echo "    ! Nuxt no estaba corriendo"
  fi
}

print_urls() {
  local env="$1"
  local alb_url="$2"
  echo ""
  echo "======================================"
  if [ "$env" = "prod" ]; then
    echo "  Ambiente: PROD"
    echo "  App:      $alb_url"
    echo "  Reverb:   $REVERB_PROD"
  else
    echo "  Ambiente: $(echo "$env" | tr '[:lower:]' '[:upper:]')"
    echo "  App:      $alb_url"
  fi
  echo "  Frontend: http://localhost:3000/login"
  echo "======================================"
}

# -----------------------------------------------------------------
# Commands
# -----------------------------------------------------------------
cmd_up() {
  local env
  env=$(resolve_env "${1:-prod}")
  local alb_url
  alb_url=$(alb_for "$env")
  local services
  IFS=' ' read -r -a services <<< "$(services_for "$env")"

  scale_services 1 "$env" "${services[@]}"
  wait_running "$env" "${services[@]}"
  wait_healthy "$env" "$alb_url"
  start_frontend "$alb_url"
  print_urls "$env" "$alb_url"
}

cmd_down() {
  local target="${1:-prod}"

  if [ "$target" = "all" ]; then
    stop_frontend
    for env in prod dev staging; do
      local services
      IFS=' ' read -r -a services <<< "$(services_for "$env")"
      scale_services 0 "$env" "${services[@]}"
    done
    for env in prod dev staging; do
      local services
      IFS=' ' read -r -a services <<< "$(services_for "$env")"
      wait_stopped "$env" "${services[@]}"
    done
    echo ""
    echo "======================================"
    echo "  Todos los ambientes bajados."
    echo "======================================"
  else
    local env
    env=$(resolve_env "$target")
    local services
    IFS=' ' read -r -a services <<< "$(services_for "$env")"

    stop_frontend
    scale_services 0 "$env" "${services[@]}"
    wait_stopped "$env" "${services[@]}"
    echo ""
    echo "======================================"
    echo "  [${env}] bajado."
    echo "======================================"
  fi
}

# -----------------------------------------------------------------
# Entry point
# -----------------------------------------------------------------
case "$1" in
  up)   cmd_up   "${2:-}" ;;
  down) cmd_down "${2:-}" ;;
  *)
    echo "Uso: $0 {up|down} [prod|dev|staging|all]"
    echo ""
    echo "  $0 up              → levanta prod"
    echo "  $0 up dev          → levanta dev"
    echo "  $0 up staging      → levanta staging"
    echo "  $0 down            → baja prod"
    echo "  $0 down dev        → baja dev"
    echo "  $0 down staging    → baja staging"
    echo "  $0 down all        → baja los 3 ambientes"
    exit 1
    ;;
esac
