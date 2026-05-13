#!/bin/bash
set -e

# =========================================================
# Provisions dev and staging environments in AWS ECS
# Run once. Idempotent for most resources.
# =========================================================

REGION="us-east-1"
ACCOUNT="125482556623"
CLUSTER="payment-webhook-cluster"
VPC="vpc-0d0056ae451676f30"
SUBNETS="subnet-086805d322b514997,subnet-08c37a03ac4d53ef1,subnet-05eecbe61c93bcf9a"
SUBNETS_ARR=(subnet-086805d322b514997 subnet-08c37a03ac4d53ef1 subnet-05eecbe61c93bcf9a)
SG_APP="sg-02a62b314ca7438e3"
RDS_HOST="payment-webhook-db.cu9iu8e4glvg.us-east-1.rds.amazonaws.com"
ECR="$ACCOUNT.dkr.ecr.$REGION.amazonaws.com/payment-webhook-api:latest"

# ----------------------------------------------------------
# 1. Create databases in RDS
# ----------------------------------------------------------
echo "==> Creating databases in RDS..."

# Requires mysql client installed locally and network access to RDS.
# If you can't reach RDS from your machine, run this from a bastion or skip
# and create the DBs manually via the AWS Console query editor.
for DB in payment_webhook_dev payment_webhook_staging; do
  echo "    Creating $DB (if not exists)..."
  mysql -h "$RDS_HOST" -u laravel -psecret1234 \
    -e "CREATE DATABASE IF NOT EXISTS \`$DB\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null \
    && echo "    ✓ $DB" \
    || echo "    ! Could not create $DB via mysql client — create it manually in RDS console."
done

# ----------------------------------------------------------
# 2. Create ALB for dev
# ----------------------------------------------------------
echo ""
echo "==> Creating ALB for dev..."

DEV_ALB_ARN=$(aws elbv2 create-load-balancer \
  --name payment-webhook-alb-dev \
  --subnets "${SUBNETS_ARR[@]}" \
  --security-groups "$SG_APP" \
  --scheme internet-facing \
  --type application \
  --region "$REGION" \
  --query 'LoadBalancers[0].LoadBalancerArn' --output text 2>/dev/null || \
  aws elbv2 describe-load-balancers \
    --names payment-webhook-alb-dev \
    --region "$REGION" \
    --query 'LoadBalancers[0].LoadBalancerArn' --output text)

DEV_ALB_DNS=$(aws elbv2 describe-load-balancers \
  --load-balancer-arns "$DEV_ALB_ARN" \
  --region "$REGION" \
  --query 'LoadBalancers[0].DNSName' --output text)

echo "    ✓ Dev ALB: $DEV_ALB_DNS"

# Target group for dev app
DEV_TG_ARN=$(aws elbv2 create-target-group \
  --name payment-webhook-tg-dev \
  --protocol HTTP \
  --port 8000 \
  --vpc-id "$VPC" \
  --target-type ip \
  --health-check-path /api/health \
  --health-check-interval-seconds 30 \
  --healthy-threshold-count 2 \
  --unhealthy-threshold-count 3 \
  --region "$REGION" \
  --query 'TargetGroups[0].TargetGroupArn' --output text 2>/dev/null || \
  aws elbv2 describe-target-groups \
    --names payment-webhook-tg-dev \
    --region "$REGION" \
    --query 'TargetGroups[0].TargetGroupArn' --output text)

# Listener for dev
aws elbv2 create-listener \
  --load-balancer-arn "$DEV_ALB_ARN" \
  --protocol HTTP \
  --port 80 \
  --default-actions Type=forward,TargetGroupArn="$DEV_TG_ARN" \
  --region "$REGION" \
  --output text > /dev/null 2>&1 || true

echo "    ✓ Dev target group and listener created"

# ----------------------------------------------------------
# 3. Create ALB for staging
# ----------------------------------------------------------
echo ""
echo "==> Creating ALB for staging..."

STAGING_ALB_ARN=$(aws elbv2 create-load-balancer \
  --name payment-webhook-alb-staging \
  --subnets "${SUBNETS_ARR[@]}" \
  --security-groups "$SG_APP" \
  --scheme internet-facing \
  --type application \
  --region "$REGION" \
  --query 'LoadBalancers[0].LoadBalancerArn' --output text 2>/dev/null || \
  aws elbv2 describe-load-balancers \
    --names payment-webhook-alb-staging \
    --region "$REGION" \
    --query 'LoadBalancers[0].LoadBalancerArn' --output text)

STAGING_ALB_DNS=$(aws elbv2 describe-load-balancers \
  --load-balancer-arns "$STAGING_ALB_ARN" \
  --region "$REGION" \
  --query 'LoadBalancers[0].DNSName' --output text)

echo "    ✓ Staging ALB: $STAGING_ALB_DNS"

# Target group for staging app
STAGING_TG_ARN=$(aws elbv2 create-target-group \
  --name payment-webhook-tg-staging \
  --protocol HTTP \
  --port 8000 \
  --vpc-id "$VPC" \
  --target-type ip \
  --health-check-path /api/health \
  --health-check-interval-seconds 30 \
  --healthy-threshold-count 2 \
  --unhealthy-threshold-count 3 \
  --region "$REGION" \
  --query 'TargetGroups[0].TargetGroupArn' --output text 2>/dev/null || \
  aws elbv2 describe-target-groups \
    --names payment-webhook-tg-staging \
    --region "$REGION" \
    --query 'TargetGroups[0].TargetGroupArn' --output text)

# Listener for staging
aws elbv2 create-listener \
  --load-balancer-arn "$STAGING_ALB_ARN" \
  --protocol HTTP \
  --port 80 \
  --default-actions Type=forward,TargetGroupArn="$STAGING_TG_ARN" \
  --region "$REGION" \
  --output text > /dev/null 2>&1 || true

echo "    ✓ Staging target group and listener created"

# ----------------------------------------------------------
# 4. Patch APP_URL in task definitions with real ALB DNS
# ----------------------------------------------------------
echo ""
echo "==> Patching task definitions with ALB URLs..."

sed -i.bak "s|PLACEHOLDER_DEV_ALB_URL|$DEV_ALB_DNS|g" task-definition-dev.json
sed -i.bak "s|PLACEHOLDER_STAGING_ALB_URL|$STAGING_ALB_DNS|g" task-definition-staging.json

# For reverb we use the ECS service discovery DNS or the same ALB (port 8080 via NLB is ideal,
# but for simplicity dev/staging reverb host will be updated after services are up)
echo "    ✓ Task definitions patched"

# ----------------------------------------------------------
# 5. Register task definitions
# ----------------------------------------------------------
echo ""
echo "==> Registering task definitions..."

for FILE in task-definition-dev task-definition-staging \
            worker-task-definition-dev worker-task-definition-staging \
            reverb-task-definition-dev reverb-task-definition-staging; do
  aws ecs register-task-definition \
    --cli-input-json "file://${FILE}.json" \
    --region "$REGION" \
    --query 'taskDefinition.family' --output text
  echo "    ✓ $FILE"
done

# ----------------------------------------------------------
# 6. Create ECS services for dev
# ----------------------------------------------------------
echo ""
echo "==> Creating ECS services for dev..."

# App service (linked to ALB)
aws ecs create-service \
  --cluster "$CLUSTER" \
  --service-name payment-webhook-service-dev \
  --task-definition payment-webhook-app-dev \
  --desired-count 1 \
  --launch-type FARGATE \
  --network-configuration "awsvpcConfiguration={subnets=[$SUBNETS],securityGroups=[$SG_APP],assignPublicIp=ENABLED}" \
  --load-balancers "targetGroupArn=$DEV_TG_ARN,containerName=app,containerPort=8000" \
  --region "$REGION" \
  --output text --query 'service.serviceName' > /dev/null 2>&1 \
  && echo "    ✓ payment-webhook-service-dev created" \
  || echo "    ~ payment-webhook-service-dev already exists, skipping"

# Worker service (no ALB)
aws ecs create-service \
  --cluster "$CLUSTER" \
  --service-name payment-webhook-worker-dev \
  --task-definition payment-webhook-worker-dev \
  --desired-count 1 \
  --launch-type FARGATE \
  --network-configuration "awsvpcConfiguration={subnets=[$SUBNETS],securityGroups=[$SG_APP],assignPublicIp=ENABLED}" \
  --region "$REGION" \
  --output text --query 'service.serviceName' > /dev/null 2>&1 \
  && echo "    ✓ payment-webhook-worker-dev created" \
  || echo "    ~ payment-webhook-worker-dev already exists, skipping"

# Reverb service (no ALB - use internal DNS or update REVERB_HOST manually after deploy)
aws ecs create-service \
  --cluster "$CLUSTER" \
  --service-name payment-webhook-reverb-dev \
  --task-definition payment-webhook-reverb-dev \
  --desired-count 1 \
  --launch-type FARGATE \
  --network-configuration "awsvpcConfiguration={subnets=[$SUBNETS],securityGroups=[$SG_APP],assignPublicIp=ENABLED}" \
  --region "$REGION" \
  --output text --query 'service.serviceName' > /dev/null 2>&1 \
  && echo "    ✓ payment-webhook-reverb-dev created" \
  || echo "    ~ payment-webhook-reverb-dev already exists, skipping"

# ----------------------------------------------------------
# 7. Create ECS services for staging
# ----------------------------------------------------------
echo ""
echo "==> Creating ECS services for staging..."

aws ecs create-service \
  --cluster "$CLUSTER" \
  --service-name payment-webhook-service-staging \
  --task-definition payment-webhook-app-staging \
  --desired-count 1 \
  --launch-type FARGATE \
  --network-configuration "awsvpcConfiguration={subnets=[$SUBNETS],securityGroups=[$SG_APP],assignPublicIp=ENABLED}" \
  --load-balancers "targetGroupArn=$STAGING_TG_ARN,containerName=app,containerPort=8000" \
  --region "$REGION" \
  --output text --query 'service.serviceName' > /dev/null 2>&1 \
  && echo "    ✓ payment-webhook-service-staging created" \
  || echo "    ~ payment-webhook-service-staging already exists, skipping"

aws ecs create-service \
  --cluster "$CLUSTER" \
  --service-name payment-webhook-worker-staging \
  --task-definition payment-webhook-worker-staging \
  --desired-count 1 \
  --launch-type FARGATE \
  --network-configuration "awsvpcConfiguration={subnets=[$SUBNETS],securityGroups=[$SG_APP],assignPublicIp=ENABLED}" \
  --region "$REGION" \
  --output text --query 'service.serviceName' > /dev/null 2>&1 \
  && echo "    ✓ payment-webhook-worker-staging created" \
  || echo "    ~ payment-webhook-worker-staging already exists, skipping"

aws ecs create-service \
  --cluster "$CLUSTER" \
  --service-name payment-webhook-reverb-staging \
  --task-definition payment-webhook-reverb-staging \
  --desired-count 1 \
  --launch-type FARGATE \
  --network-configuration "awsvpcConfiguration={subnets=[$SUBNETS],securityGroups=[$SG_APP],assignPublicIp=ENABLED}" \
  --region "$REGION" \
  --output text --query 'service.serviceName' > /dev/null 2>&1 \
  && echo "    ✓ payment-webhook-reverb-staging created" \
  || echo "    ~ payment-webhook-reverb-staging already exists, skipping"

# ----------------------------------------------------------
# 8. Summary
# ----------------------------------------------------------
echo ""
echo "============================================================"
echo "  PROD    : http://payment-webhook-alb-156165760.us-east-1.elb.amazonaws.com"
echo "  DEV     : http://$DEV_ALB_DNS"
echo "  STAGING : http://$STAGING_ALB_DNS"
echo "============================================================"
echo ""
echo "NEXT STEPS:"
echo "  1. Add GitHub secrets: AWS_ACCESS_KEY_ID, AWS_SECRET_ACCESS_KEY, AWS_REGION"
echo "  2. Push to 'dev' branch → deploys -dev services"
echo "  3. Push to 'staging' branch → deploys -staging services"
echo "  4. Push to 'main' branch → deploys prod services"
echo ""
echo "NOTE: If mysql client is not available locally, create the"
echo "  databases manually via RDS Console > Query Editor:"
echo "  CREATE DATABASE payment_webhook_dev CHARACTER SET utf8mb4;"
echo "  CREATE DATABASE payment_webhook_staging CHARACTER SET utf8mb4;"
