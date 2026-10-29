#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Sinh resources/views/frontend/component/fonts.blade.php - cac luat @font-face
cua Google duoc nhung thang vao <head>.

    python deploy/gen-fonts.py

VI SAO CAN SCRIPT NAY
    File fonts.blade.php truoc day lam bang tay. Khi them hoac bot mot ho font
    thi phai nho lay lai dung URL voi User-Agent cua Chrome, neu khong Google
    tra ve dinh dang cu (ttf, khong co unicode-range) va file phinh len gap
    may lan. Script nay chot lai danh sach font o mot cho.

DANH SACH FONT LAY TU DAU
    Dung bang FAMILIES ben duoi, vua khop voi nhung gi CSS dang thuc su dung:
      Inter       - var(--main-font) va 9 cho goi thang, nhieu nhat
      Montserrat  - --main-font: "Montserrat", sans-serif (app.scss:66),
                    duoc dung qua var(--main-font) o 19 cho, co ca
                    font-family: var(--main-font) !important o app.scss:87
      Manrope     - $second-font, dung o components/_article-overlay-card.scss
      Playfair    - app.scss:1292
      Yeseva One  - tieu de bai viet, dat trong <style> cua ba blade:
                    post/catalogue/index, post/post/index, product/product/index

    DOI DANH SACH NAY THI PHAI CHAY LAI SCRIPT, neu khong trang se rot ve font
    du phong cho ho font vua them.

VI SAO GIU CA CYRILLIC VA GREEK
    Moi luat co unicode-range rieng, nen trinh duyet CHI tai subset no that su
    can - trang tieng Viet chi tai latin, latin-ext va vietnamese. Cat bot chi
    tiet kiem vai KB trong HTML ma them rui ro neu sau nay co noi dung ngon
    ngu khac.
"""
import io
import os
import re
import urllib.request

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
DEST = os.path.join(ROOT, 'resources/views/frontend/component/fonts.blade.php')

# Dung y nguyen chuoi ma CSS dang yeu cau, de khong doi gi ve mat hinh thuc.
FAMILIES = [
    'Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900',
    'Manrope:wght@200..800',
    'Montserrat:ital,wght@0,100..900;1,100..900',
    'Playfair:ital,opsz,wght@0,5..1200,300..900;1,5..1200,300..900',
    'Yeseva+One',
]

# Bat buoc gia lam Chrome: UA khac thi Google tra ve ttf, khong unicode-range.
UA = ('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 '
      '(KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36')

HEADER = """{{--
    CSS font cua Google, NHUNG THANG thay vi tai qua <link> hoac @import.

    SINH TU DONG boi deploy/gen-fonts.py - DUNG SUA TAY.
    Doi danh sach font thi sua FAMILIES trong script do roi chay lai.

    VI SAO NHUNG THANG
    Truoc day dung bo ba preload + media="print" onload + noscript. Cach do
    van phai di mot vong ra fonts.googleapis.com, va PageSpeed do duoc khoi
    "Google Fonts" chan hien thi 2.260ms chi de lay 7,2 KiB - gan nhu toan bo
    la chi phi DNS, bat tay TLS va vong request, khong phai thoi gian tai.

    Lan dau sua chi nhung Inter, Manrope va Yeseva One vao day, nhung VAN CON
    bon lenh @import url('https://fonts.googleapis.com/...') nam trong
    resources/css/app.scss (dong 46-49) va mot trong config.css. esbuild gom
    ca nam len dau file da build, ngay sau @charset - nen chung van hop le va
    van chay. Ket qua la mot chuoi chan hien thi NOI TIEP: tai app.css 90 KB
    -> phan tich -> moi phat hien @import -> tai tiep nam file CSS nua tu
    fonts.googleapis.com, cung chan hien thi -> roi moi den luot .woff2.
    Dua not Montserrat va Playfair vao day thi bo duoc han cac @import do.

    Khong ho font nao bi bo: ca nam ho deu dang duoc dung that (xem phan ghi
    chu trong deploy/gen-fonts.py), nen hinh thuc trang khong doi.

    Trinh duyet gio chi con phai tai .woff2 tu fonts.gstatic.com, va the
    preconnect toi do da co san trong layout.
--}}
"""


def main():
    url = ('https://fonts.googleapis.com/css2?'
           + '&'.join('family=' + f for f in FAMILIES)
           + '&display=swap')

    req = urllib.request.Request(url, headers={'User-Agent': UA})
    css = urllib.request.urlopen(req, timeout=60).read().decode('utf-8')

    faces = len(re.findall(r'@font-face', css))
    if faces == 0:
        raise SystemExit('khong nhan duoc @font-face nao - kiem tra lai URL')
    if 'unicode-range' not in css:
        raise SystemExit('thieu unicode-range - Google da tra ve dinh dang cu, '
                         'kiem tra lai User-Agent')
    if '.woff2' not in css:
        raise SystemExit('khong phai woff2 - kiem tra lai User-Agent')

    got = {m for m in re.findall(r"font-family:\s*'([^']+)'", css)}
    want = {f.split(':')[0].replace('+', ' ') for f in FAMILIES}
    missing = want - got
    if missing:
        raise SystemExit('Google khong tra ve cac ho: ' + ', '.join(sorted(missing)))

    io.open(DEST, 'w', encoding='utf-8', newline='\n').write(
        HEADER + '<style>\n' + css.strip() + '\n</style>\n')

    print('URL        :', url[:96] + ('...' if len(url) > 96 else ''))
    print('ho font    :', ', '.join(sorted(got)))
    print('@font-face :', faces)
    print('kich thuoc : %d bytes' % (len(css) + len(HEADER) + 17))
    print('ghi ra     :', os.path.relpath(DEST, ROOT))


if __name__ == '__main__':
    main()
