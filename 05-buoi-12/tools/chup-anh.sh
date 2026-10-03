#!/bin/bash
# Chup anh minh chung bang Chrome headless. Chay tu thu muc 05-th12: bash tools/chup-anh.sh
CHROME="/c/Program Files/Google/Chrome/Application/chrome.exe"
cd "$(dirname "$0")/../minh-chung" || exit 1
shot() { # shot <ten-anh.png> <url> [cao]
  "$CHROME" --headless=new --disable-gpu --hide-scrollbars --ignore-certificate-errors --window-size=1280,${3:-1400} \
    --screenshot="$(cygpath -w "$PWD/$1")" "$2" >/dev/null 2>&1 && echo "-> $1"
}
# Trang cong khai
shot nv1-trang-san-pham-chi-phi.png "https://butchixanh.local/?post_type=product&p=11" 1500
shot nv3-chinh-sach-quyen-rieng-tu.png "https://butchixanh.local/chinh-sach-quyen-rieng-tu/" 2600
shot nv3-dieu-khoan-ban-hang.png "https://butchixanh.local/dieu-khoan-ban-hang/" 2600
shot nv3-chinh-sach-doi-tra.png "https://butchixanh.local/chinh-sach-doi-tra/" 2000
# Trang da luu trong luc kiem thu (giu nguyen phien cua khach: thong bao loi, trang cam on)
for f in nv1-tc*.html; do [ -f "$f" ] && shot "${f%.html}.png" "file:///$(cygpath -m "$PWD/$f")" 1300; done
