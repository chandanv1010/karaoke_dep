# Lấy lại danh sách selector cho CSS tối thiểu

Chạy lại khi **bố cục phần đầu trang chủ thay đổi** — đổi header, đổi khối
hero, đổi khối giới thiệu. Không chạy lại thì CSS tối thiểu bị cũ và phần đầu
trang sẽ hiển thị sai trong khoảnh khắc đầu tiên, trước khi file CSS đầy đủ
tải xong.

## Cách làm

1. Mở `https://karaokedep.com.vn/` trong Chrome.
2. Bật DevTools, chuyển sang chế độ thiết bị di động **375 × 812**. Phải đúng
   bề rộng điện thoại — PageSpeed chấm điểm mobile ở kích thước này, và các
   luật `@media` khác nhau sẽ khớp ở bề rộng khác.
3. Tải lại trang, đợi ảnh và font xong.
4. Dán đoạn này vào Console:

```js
const FOLD = innerHeight * 1.3;
const above = [...document.querySelectorAll('*')].filter(e => {
    const r = e.getBoundingClientRect();
    return r.top < FOLD && r.bottom > -1 && r.width > 0;
});
const strip = s => s.split(',').map(x => x.trim().replace(
    /::?(before|after|hover|focus|active|visited|focus-within|focus-visible|placeholder|first-line|selection|-webkit-[a-z-]+|-moz-[a-z-]+)/g, ''
)).filter(Boolean);
const hit = sel => { try { return strip(sel).some(s => above.some(e => e.matches(s))); } catch (x) { return false; } };
const sels = new Set();
for (const sh of document.styleSheets) {
    let rs; try { rs = sh.cssRules; } catch (e) { continue; }
    for (const r of rs) {
        if (r.type === 1 && r.selectorText && hit(r.selectorText)) sels.add(r.selectorText);
        else if (r.type === 4) for (const ir of r.cssRules)
            if (ir.selectorText && hit(ir.selectorText)) sels.add(ir.selectorText);
    }
}
copy([...sels].join('\n'));
```

`copy()` đưa kết quả vào clipboard.

5. Dán vào biến `SELECTORS` trong `deploy/gen-critical-css.py`.
6. Chạy:

```bash
python deploy/gen-critical-css.py
```

7. Commit cả `deploy/gen-critical-css.py` lẫn
   `resources/views/frontend/component/critical-css.blade.php`.

## Vì sao chỉ lấy danh sách selector, không lấy thẳng chuỗi CSS

Các luật `.fa-*::before` chứa ký tự vùng dùng riêng (private use area) cho
icon FontAwesome. Chuyển qua dạng văn bản là bị méo và icon hiện sai. Lấy danh
sách selector rồi để script đọc lại từ file CSS trên đĩa thì giữ nguyên byte gốc.

## Vì sao `FOLD = innerHeight * 1.3`

Lấy dư 30% chiều cao màn hình làm biên an toàn: trình duyệt vẽ sớm hơn một
chút so với mép màn hình, và vài phần tử dịch nhẹ khi font về. Lấy đúng bằng
`innerHeight` thì dễ thiếu luật cho phần ngay dưới mép.

## Lưu ý

CSS tối thiểu này **chỉ dùng cho trang chủ** (`head.blade.php` kiểm tra
`request()->is('/')`). Các trang khác có bố cục khác nên vẫn tải CSS theo kiểu
chặn hiển thị như cũ — an toàn hơn là dùng chung một bộ CSS tối thiểu sai cho
chúng.
