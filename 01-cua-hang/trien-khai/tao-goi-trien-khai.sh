#!/bin/bash
# Tao goi dua cua hang len hosting (InfinityFree...): CSDL da doi dia chi + wp-content da loc file nhay cam.
# Chay tu thu muc 01-cua-hang:   bash trien-khai/tao-goi-trien-khai.sh butchixanh.infinityfree.me
# Ket qua o trien-khai/goi/ (da gitignore, KHONG commit, KHONG gui qua chat).
set -euo pipefail

TEN_MIEN="${1:-}"
if [[ ! "$TEN_MIEN" =~ ^[a-z0-9.-]+\.[a-z]{2,}$ ]]; then
    echo "Cach dung: bash trien-khai/tao-goi-trien-khai.sh <ten-mien>   (vi du butchixanh.infinityfree.me)"
    exit 1
fi
cd "$(dirname "$0")/.."
GOI=trien-khai/goi
rm -rf "$GOI" && mkdir -p "$GOI"
export MSYS_NO_PATHCONV=1

echo "== 1. Quet bi mat trong wp-content (phan se tai len hosting)"
LOI=0
# Khoa bi mat dang dung trong .env khong duoc xuat hien trong bat ky file nao se tai len
if [ -f .env ]; then
    while IFS='=' read -r k v; do
        v="${v%$'\r'}"
        case "$k" in VNP_HASH_SECRET|VNP_TMN_CODE|MYSQL_PASSWORD|MYSQL_ROOT_PASSWORD)
            if [ ${#v} -ge 6 ] && grep -rlF --exclude-dir=wc-logs --exclude-dir=woocommerce_uploads -- "$v" wp-content >/dev/null 2>&1; then
                echo "   LOI: gia tri cua $k nam trong wp-content"; LOI=1
            fi;;
        esac
    done < .env
fi
if grep -rlE --include=*.php --include=*.txt --include=*.json -- "-----BEGIN [A-Z ]*PRIVATE KEY-----" wp-content >/dev/null 2>&1; then
    echo "   LOI: co khoa rieng (private key) trong wp-content"; LOI=1
fi
[ "$LOI" -eq 0 ] || { echo "Dung lai: xu ly cac loi tren truoc khi dong goi."; exit 1; }
echo "   Khong thay bi mat."

echo "== 2. Xuat CSDL, doi butchixanh.local -> $TEN_MIEN (moi dang: http, https, co dau \/) (CSDL tren may giu nguyen)"
# /scripts trong container chi doc: xuat ra stdout roi ghi file tu phia may
docker compose run --rm -T wpcli wp search-replace 'butchixanh.local' "$TEN_MIEN" \
    --all-tables --precise --skip-columns=guid --export 2>/dev/null > "$GOI/csdl.sql"
head -c 300 "$GOI/csdl.sql" | grep -q "^-- \|CREATE\|DROP\|/\*" || { echo "   LOI: xuat CSDL that bai"; exit 1; }
# Xoa phien dang nhap dang luu: ai co ban CSDL nay cung khong dung lai duoc phien cu
sed -i -E "s/('session_tokens', ?)'[^']*'/\1''/g" "$GOI/csdl.sql"
if grep -q "'session_tokens', \?'a:" "$GOI/csdl.sql"; then echo "   LOI: chua xoa duoc phien dang nhap"; exit 1; fi
echo "   $(du -h "$GOI/csdl.sql" | cut -f1)  $GOI/csdl.sql"
if grep -q "butchixanh\.local" "$GOI/csdl.sql"; then
    echo "   Con $(grep -o 'butchixanh\.local' "$GOI/csdl.sql" | wc -l) cho ghi butchixanh.local (thuong la guid bai viet, khong anh huong)."
fi

echo "== 3. Dong goi wp-content (bo nhat ky, ban tam, thu muc cap nhat do dang)"
python -I - "$GOI/wp-content.zip" <<'PY'
import os, sys, zipfile
out = sys.argv[1]
bo_thu_muc = {'wc-logs', 'woocommerce_uploads', 'upgrade', 'upgrade-temp-backup', 'cache', '.staging', '__pycache__'}
n = 0
with zipfile.ZipFile(out, 'w', zipfile.ZIP_DEFLATED) as z:
    for root, dirs, files in os.walk('wp-content'):
        dirs[:] = [d for d in dirs if d not in bo_thu_muc and not d.startswith('.old-')]
        for f in files:
            if f.endswith(('.log', '.sql', '.gz', '.bak')) or f in ('.env', 'debug.log'):
                continue
            p = os.path.join(root, f)
            z.write(p, p); n += 1
print(f'   {n} file -> {out} ({os.path.getsize(out)//1024//1024} MB)')
PY

echo "== 4. File cau hinh cho hosting"
cp trien-khai/htaccess-goc.txt "$GOI/htaccess-goc.txt"
cp trien-khai/htaccess-uploads.txt "$GOI/htaccess-uploads.txt"
sed "s/TEN-MIEN/$TEN_MIEN/g" trien-khai/wp-config-them.php > "$GOI/wp-config-them.php"
echo "   Xong. Lam tiep theo trien-khai/TRIEN-KHAI.md"
ls -la "$GOI"
