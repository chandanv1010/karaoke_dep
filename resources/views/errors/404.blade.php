{{--
    Trang 404 cua site.

    CO Y VIET DOC LAP, khong @extends layout chinh. Trang loi duoc render boi
    bo bat ngoai le, doi khi o trang thai ma cac bien dung chung ($system,
    menu, gio hang...) chua kip duoc nap. Neu layout tham chieu mot bien chua
    co thi trang 404 se tu no nem loi va khach nhan 500 - hong hon han cai dang
    di sua. Vi vay o day chi dung HTML + CSS tu than.

    Truoc day nginx chan san 404 bang `error_page 404 /404.html;` trong vhost,
    ma file /404.html lai khong ton tai, nen khach nhan trang mac dinh cua
    nginx: 146 byte chu den tren nen trang, co chu "nginx" o duoi. Da go dong
    do de 404 cua Laravel di thang ra.
--}}
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, follow">
    <title>Không tìm thấy trang — AZKTV</title>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    @include('frontend.component.fonts')
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background: #10131a;
            color: #e9ecf2;
            font-family: 'Montserrat', system-ui, -apple-system, 'Segoe UI', sans-serif;
            line-height: 1.6;
        }
        .box { width: 100%; max-width: 560px; text-align: center; }
        .code {
            font-family: 'Yeseva One', Georgia, serif;
            font-size: clamp(72px, 18vw, 132px);
            line-height: 1;
            margin: 0;
            color: #d4af37;
            letter-spacing: 2px;
        }
        h1 {
            font-family: 'Yeseva One', Georgia, serif;
            font-size: clamp(20px, 5vw, 28px);
            margin: 14px 0 10px;
            font-weight: 400;
        }
        p { margin: 0 auto 26px; color: #a8b0bf; max-width: 42ch; }
        .actions { display: flex; flex-wrap: wrap; gap: 12px; justify-content: center; }
        a.btn {
            display: inline-block;
            padding: 12px 26px;
            border-radius: 999px;
            text-decoration: none;
            font-weight: 600;
            font-size: 15px;
            transition: opacity .2s;
        }
        a.btn:hover { opacity: .85; }
        .btn-main { background: #d4af37; color: #10131a; }
        .btn-ghost { border: 1px solid #39404f; color: #e9ecf2; }
        .links { margin-top: 30px; font-size: 14px; color: #6f7788; }
        .links a { color: #a8b0bf; text-decoration: none; margin: 0 8px; }
        .links a:hover { color: #d4af37; }
    </style>
</head>
<body>
    <div class="box">
        <p class="code">404</p>
        <h1>Không tìm thấy trang này</h1>
        <p>Trang bạn tìm có thể đã được đổi địa chỉ hoặc gỡ xuống. Bạn thử quay về
           trang chủ, hoặc xem các mục bên dưới.</p>

        <div class="actions">
            <a class="btn btn-main" href="{{ url('/') }}">Về trang chủ</a>
            <a class="btn btn-ghost" href="{{ url('/thiet-ke-phong-karaoke.html') }}">Thiết kế phòng karaoke</a>
        </div>

        <div class="links">
            <a href="{{ url('/thi-cong-karaoke.html') }}">Thi công karaoke</a>·
            <a href="{{ url('/am-thanh-karaoke.html') }}">Âm thanh karaoke</a>·
            <a href="{{ url('/lien-he.html') }}">Liên hệ</a>
        </div>
    </div>
</body>
</html>
