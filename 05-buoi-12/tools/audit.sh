#!/bin/bash
# Kiem tra an toan tu ben ngoai (chay tu may host, Git Bash). Dung: bash audit.sh > minh-chung/audit.txt
# Luu y: muc [5] cố tình dang nhap sai 8 lan -> IP may host bi khoa 15 phut. Mo khoa:
#   docker exec 01-cua-hang-shop-1 wp --allow-root --path=/var/www/html transient delete --all
H=https://butchixanh.local
code() { curl -sk -o /dev/null -w '%{http_code}' "$@"; }
loc()  { curl -sk -o /dev/null -w '%{http_code} %{redirect_url}' "$@"; }

echo "### Thoi diem: $(date '+%Y-%m-%d %H:%M:%S')"

echo; echo "### [1] HTTPS tren toan bo site (ma HTTP khi mo bang https://)"
for u in / /shop/ /cart/ /checkout/ /my-account/ /wp-login.php /chinh-sach-quyen-rieng-tu/; do
  printf "%-30s %s\n" "$u" "$(code "$H$u")"
done
echo "Tai nguyen nap qua http:// (mixed content) tren trang chu + trang san pham + thanh toan:"
{ curl -sk "$H/"; curl -skL "$H/?post_type=product&p=11"; curl -sk "$H/checkout/"; } \
  | grep -oE '(src|action)="http://[^"]+' | sort -u || true
echo "(het danh sach; chi tinh src/action vi href toi trang ngoai khong tai noi dung)"

echo; echo "### [2] Chuyen huong bat buoc khi go dia chi khong co https://"
echo "http://butchixanh.local/              -> $(loc http://butchixanh.local/)"
echo "http://butchixanh.local/wp-login.php  -> $(loc http://butchixanh.local/wp-login.php)"
echo "http://butchixanh.local/checkout/     -> $(loc http://butchixanh.local/checkout/)"
for port in 8080 8081; do
  c=$(curl -s -m 5 -o /dev/null -w '%{http_code}' "http://localhost:$port/wp-login.php")
  if [ "$c" = "000" ]; then echo "http://localhost:$port (vao thang WordPress, bo qua nginx) -> khong ket noi duoc (cong da dong)"
  else echo "http://localhost:$port/wp-login.php (vao thang WordPress, HTTP tron) -> $c"; fi
done
echo "Header HSTS: $(curl -skI $H/ | grep -i '^strict-transport' | tr -d '\r' || echo 'KHONG CO')"

echo; echo "### [5] Gioi han dang nhap: thu sai mat khau 8 lan lien tiep"
for i in $(seq 1 8); do
  c=$(curl -sk -o /tmp/l.html -w '%{http_code}' -d "log=admin&pwd=sai-mat-khau-$i&wp-submit=Log+In&testcookie=1" \
      -b "wordpress_test_cookie=WP+Cookie+check" "$H/wp-login.php")
  msg=$(tr '\n' ' ' < /tmp/l.html | grep -oE 'id="login_error".{0,400}' | sed -E 's/<[^>]*>/ /g; s/^id="login_error"[^>]*>//; s/ +/ /g' | head -c 150)
  echo "lan $i: HTTP $c | $msg"
done

echo; echo "### [5b] Liet ke ten dang nhap / XML-RPC"
echo "/?author=1           -> $(loc "$H/?author=1")"
echo "/wp-json/wp/v2/users -> $(code "$H/wp-json/wp/v2/users") $(curl -sk "$H/wp-json/wp/v2/users" | head -c 120)"
echo "/xmlrpc.php (POST)   -> $(code -X POST -d '<methodCall><methodName>system.listMethods</methodName></methodCall>' $H/xmlrpc.php)"

echo; echo "### [8] Thong bao loi chi tiet / lo thong tin he thong"
echo "Header lo phien ban:"; curl -skI $H/ | grep -iE '^(server|x-powered-by):' | tr -d '\r'
echo "Trang 404 (dia chi sai): HTTP $(curl -sk -o /tmp/404.html -w '%{http_code}' "$H/duong-dan-khong-ton-tai-xyz"), lo duong dan he thong: $(grep -ciE '/var/www|fatal error' /tmp/404.html) dong"
for f in /wp-includes/theme-compat/embed.php /wp-includes/rss-functions.php /wp-content/plugins/woo-vnpay/woo-vnpay.php; do
  c=$(curl -sk -o /tmp/f.html -w '%{http_code}' "$H$f")
  echo "$f -> HTTP $c, lo duong dan/loi: $(grep -ciE '/var/www|fatal error|warning:' /tmp/f.html) dong"
done
echo "Trang loi 403 cua Apache: $(curl -sk "$H/wp-content/plugins/woo-vnpay/includes/" | grep -oE '<address>[^<]*' || echo '(khong lo phien ban)')"
for f in /readme.html /license.txt /wp-content/uploads/wc-logs/; do echo "$f -> $(code "$H$f")"; done
echo "Meta generator trong HTML: $(curl -sk $H/ | grep -oE 'generator" content="[^"]+' | tr '\n' ' ')"

echo; echo "### [10] Ten mien ben thu ba duoc nap tu trang (script/img/iframe/link stylesheet)"
for u in / "/?post_type=product&p=11" /checkout/; do
  echo "-- $u"
  curl -skL "$H$u" | grep -oE '<(script|img|iframe|link)[^>]+(src|href)="https?://[^"/]+' \
    | grep -oE 'https?://[^"/]+' | grep -v butchixanh.local | sort | uniq -c || echo "   (khong co)"
done
