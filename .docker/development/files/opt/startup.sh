#!/bin/bash

# Run cron service.
cron

# Run command previously defined in base php-apache Dockerfile.
apache2-foreground
