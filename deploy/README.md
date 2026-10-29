# Triển khai đợt tối ưu karaokedep.com.vn

Làm đúng thứ tự. Mỗi bước đều lùi lại được, cách lùi ghi ngay dưới bước đó.

Trước khi bắt đầu: vào **aaPanel → Website → karaokedep.com.vn → Backup** bấm
backup thủ công một lần. Nhà cung cấp có snapshot 3 ngày/lần, nhưng đó là
snapshot toàn máy — không lùi riêng một file được.

---

## 1. Đưa code lên

```bash
cd /www/wwwroot/karaokedep.com.vn
git pull origin main
```

Nếu `git pull` báo vướng file đã sửa tại chỗ, **đừng** dùng `git checkout .`
trước khi xem đó là file gì — thư mục `public/uploads` và `.env` không được
đụng tới.

**Lùi lại:** `git reset --hard <commit-cũ>`

---

## 2. Dọn gói dev khỏi production

```bash
composer install --no-dev --optimize-autoloader
```

Hiện `laravel-debugbar` đang nằm trong `vendor/` trên production. Nó không bật
(vì `APP_DEBUG=false`) nhưng vẫn được nạp khi khởi động.

---

## 3. Bật cache của Laravel

```bash
php artisan config:cache && php artisan route:cache && php artisan view:cache && php artisan event:cache
```

Trước đợt này `route:cache` **không chạy được** vì có 7 cặp route trùng tên —
đã sửa trong commit `b312307`.

Đo được: cache tiết kiệm khoảng 10% thời gian mỗi request. Không nhiều, nhưng
miễn phí.

> **Nhớ:** sau mỗi lần `git pull` về sau đều phải chạy lại 4 lệnh này, nếu không
> code mới sẽ không có tác dụng.

**Lùi lại:** `php artisan config:clear route:clear view:clear event:clear`

---

## 4. Sửa `.env`

```
APP_ENV=production      # đang là local
LOG_LEVEL=error         # đang là debug
```

Giữ nguyên `APP_DEBUG=false`.

---

## 5. Thêm cấu hình nginx

Chép `deploy/nginx-karaokedep.conf` vào:

```
/www/server/panel/vhost/nginx/extension/karaokedep.com.vn/seo-perf.conf
```

Đặt trong `extension/` để aaPanel sinh lại vhost (khi đổi SSL, đổi PHP…) không
xoá mất.

```bash
nginx -t && nginx -s reload
```

File này làm 4 việc: phục vụ ảnh tĩnh không qua PHP, chuyển `www` về không-www,
bật gzip, và khai báo kiểu MIME cho ảnh `.jfif`.

**Lùi lại:** xoá file đó đi rồi `nginx -s reload`.

---

## 6. Kiểm tra ngay sau khi triển khai

```bash
curl -s -o /dev/null -w "HTML  %{time_starttransfer}s\n" https://karaokedep.com.vn/
curl -s -o /dev/null -w "sitemap %{http_code}\n" https://karaokedep.com.vn/sitemap.xml
curl -s -o /dev/null -w "www   %{http_code} -> %{redirect_url}\n" https://www.karaokedep.com.vn/
```

Ảnh phải **không** còn header `Set-Cookie` — đó là dấu hiệu nó vẫn đi qua PHP:

```bash
curl -sI "https://karaokedep.com.vn/image-cache/560x0/userfiles/image/about-1.jpg" | grep -ci set-cookie
```

Kết quả mong đợi là `0`.

Lần đầu mỗi kích thước ảnh được yêu cầu sẽ chậm (Laravel phải resize và ghi
file), từ lần thứ hai trở đi nginx lo. Có thể làm nóng trước bằng cách mở vài
trang chính.

---

## 7. Thư mục cache ảnh cũ

`public/image-cache/` trên production đang có **5.009 file, 247MB** theo sơ đồ
đặt tên cũ (bằm mã md5). Sơ đồ mới ghi vào thư mục con dạng `600x0/...` nên
không đụng nhau. Sau khi chạy ổn định vài ngày, xoá được các file md5 cũ:

```bash
find public/image-cache -maxdepth 1 -type f -delete
```

Đừng xoá vội — nếu phải lùi về code cũ thì chúng vẫn cần.

---

## 8. Việc phải làm trong Google Search Console

Code sửa xong Google không tự biết. Vào từng báo cáo bấm **"Xác thực kết quả
khắc phục"**:

| Báo cáo | Mục |
|---|---|
| Trang → Không tìm thấy (404) | 60 |
| Đoạn trích về sản phẩm | 243 |
| Chỉ số quan trọng chính (di động) | 435 |

Core Web Vitals lấy số liệu từ người dùng thật trong **28 ngày gần nhất**, nên
đừng mong thấy chuyển biến trong vài ngày — phải đợi dữ liệu mới tích đủ.

Và gửi lại sitemap: **Sơ đồ trang web → nhập `sitemap.xml` → Gửi**. Sitemap cũ
là file tĩnh 500 URL từ 30/6; bản mới sinh từ CSDL, hiện 1.164 URL.

---

## Những thứ cố ý KHÔNG làm

**`opcache.validate_timestamps=0`** — tiết kiệm 531 lệnh `stat()` mỗi request,
nhưng đổi lại mỗi lần deploy phải `systemctl reload php-fpm-84`, quên là code
mới không có tác dụng mà không báo lỗi gì. Chỉ nên bật khi quy trình deploy đã
chắc tay.

**Nâng `pm.max_children`** — máy chỉ có 2,4GB RAM, 30 worker đã là căng. Sau khi
ảnh thôi đi qua PHP thì tải giảm khoảng 98%, nhiều khả năng không cần nâng nữa.
Đo lại rồi hãy quyết.

**Redis thay cache/session dạng file** — đáng làm, nhưng thêm một dịch vụ phải
trông. Để đợt sau.
