#!/bin/bash
# Sinh chung chi tu ky cho butchixanh.local (moi may tu sinh, khong dua len git).
# Chay trong thu muc 01-cua-hang:  bash certs/tao-chung-chi.sh
set -euo pipefail
cd "$(dirname "$0")"
MSYS_NO_PATHCONV=1 openssl req -x509 -nodes -newkey rsa:2048 -days 825 \
  -keyout butchixanh.local-key.pem -out butchixanh.local.pem \
  -subj "/CN=butchixanh.local/O=But Chi Xanh (Nhom 25)" \
  -addext "subjectAltName=DNS:butchixanh.local,DNS:*.butchixanh.local"
echo "Da tao certs/butchixanh.local.pem va certs/butchixanh.local-key.pem"
