#!/bin/bash

cd "$(dirname "$0")"

echo "Generating self-signed SSL certificate for local development..."

openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout server.key -out server.crt -subj "/C=BR/ST=State/L=City/O=Organization/OU=IT Department/CN=localhost"

echo "SSL certificate generated successfully!"
