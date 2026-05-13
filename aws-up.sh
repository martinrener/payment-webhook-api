#!/bin/bash
set -e

CLUSTER="payment-webhook-cluster"
REGION="us-east-1"
SERVICES=("payment-webhook-service" "payment-webhook-worker" "payment-webhook-reverb")

ALB_URL="http://payment-webhook-alb-156165760.us-east-1.elb.amazonaws.com"
REVERB_URL="payment-webhook-reverb-nlb-34b0ab443285c8fb.elb.us-east-1.amazonaws.com:8080"

echo "==> Levantando servicios en $CLUSTER..."

for SERVICE in "${SERVICES[@]}"; do
  aws ecs update-service \
    --cluster "$CLUSTER" \
    --service "$SERVICE" \
    --desired-count 1 \
    --region "$REGION" \
    --output text --query 'service.serviceName' > /dev/null
  echo "    ✓ $SERVICE → desired: 1"
done

echo ""
echo "==> Esperando que los servicios estén running..."

for SERVICE in "${SERVICES[@]}"; do
  echo -n "    $SERVICE "
  until [ "$(aws ecs describe-services \
    --cluster "$CLUSTER" \
    --services "$SERVICE" \
    --region "$REGION" \
    --query 'services[0].runningCount' \
    --output text 2>/dev/null)" -ge 1 ]; do
    echo -n "."
    sleep 5
  done
  echo " running"
done

echo ""
echo "==> Esperando health check del ALB..."
echo -n "    app "
until curl -s -o /dev/null -w "%{http_code}" "$ALB_URL/api/health" 2>/dev/null | grep -q "200"; do
  echo -n "."
  sleep 5
done
echo " healthy"

echo ""
echo "======================================"
echo "  App:    $ALB_URL"
echo "  Reverb: $REVERB_URL"
echo "======================================"
