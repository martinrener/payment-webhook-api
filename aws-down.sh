#!/bin/bash
set -e

CLUSTER="payment-webhook-cluster"
REGION="us-east-1"
SERVICES=("payment-webhook-service" "payment-webhook-worker" "payment-webhook-reverb")

echo "==> Bajando servicios en $CLUSTER..."

for SERVICE in "${SERVICES[@]}"; do
  aws ecs update-service \
    --cluster "$CLUSTER" \
    --service "$SERVICE" \
    --desired-count 0 \
    --region "$REGION" \
    --output text --query 'service.serviceName' > /dev/null
  echo "    ✓ $SERVICE → desired: 0"
done

echo ""
echo "==> Esperando que los containers terminen..."

for SERVICE in "${SERVICES[@]}"; do
  echo -n "    $SERVICE "
  until [ "$(aws ecs describe-services \
    --cluster "$CLUSTER" \
    --services "$SERVICE" \
    --region "$REGION" \
    --query 'services[0].runningCount' \
    --output text 2>/dev/null)" -eq 0 ]; do
    echo -n "."
    sleep 5
  done
  echo " stopped"
done

echo ""
echo "======================================"
echo "  Todos los servicios bajados."
echo "======================================"
