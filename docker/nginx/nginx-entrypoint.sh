#!/bin/bash

# Exit on error
set -e

echo "Starting Nginx..."
nginx -g 'daemon off;'

# Keep container running
exec "$@"
