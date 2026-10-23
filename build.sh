#!/bin/bash
set -e

docker build -t openthc/pipe --no-cache -f ./docker/Dockerfile .
