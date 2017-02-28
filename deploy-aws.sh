#!/bin/bash

##
# This is a wrapper script that will checkout ministryofjustice/wp-aws-tools
# to deploy to the CloudFormation stack.
#
# Params: <cf-stack> <cluster> <image>
#   <cf-stack> Name of the CloudFormation stack
#   <cluster>  Name of the ECS cluster
#   <image>    Docker image name & tag to deploy
##

REPO_DIR=$(mktemp -d)

git clone --quiet git@github.com:ministryofjustice/wp-aws-tools.git $REPO_DIR

$REPO_DIR/deploy-update.sh "$@"

DEPLOYED=$?

rm -rf $REPO_DIR

exit $DEPLOYED
