#!/bin/bash

set -ex

# Run the functional tests
BEHAT_TAGS=$(php utils/behat-tags.php)
behat --format progress $BEHAT_TAGS --strict
