#!/bin/bash

set -ex

docker build -t doctor-command-test -f Dockerfile_for_testing .
docker run -it doctor-command-test
