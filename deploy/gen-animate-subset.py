#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Cat animate.css xuong con nhung hieu ung site that su dung.

    python deploy/gen-animate-subset.py

VI SAO
    animate.css day du nang 58 KB va dinh nghia 61 hieu ung. Doi chieu toan bo
    ma nguon (view, scss, js) thi site chi dung 9 cai. PageSpeed bao mobile co
    85 KiB CSS khong dung tren tong 90 KiB nen - day la mot phan cua con so do.

    File nay doc animate.css goc va giu lai dung:
      - cac luat .animated* (lop nen, bat buoc)
      - voi moi hieu ung trong USED: ca @keyframes, @-webkit-keyframes va luat
        .ten-hieu-ung

    File goc KHONG bi dong vao. Ket qua ghi ra resources/css/vendor-subset/
    va app.scss tro toi do.

KHI NAO PHAI CHAY LAI
    Khi them mot hieu ung animate.css moi vao giao dien. Them lop vao USED roi
    chay lai, neu khong hieu ung do se khong co gi xay ra.

    Kiem tra lai danh sach bang:
      grep -rhoE '\\b(fadeIn|fadeInUp|zoomIn|...)\\b' resources/views ...
    hoac doc phan ghi chu trong commit da them file nay.
"""
import io
import os
import re

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
SRC = os.path.join(ROOT, 'resources/vendor/frontend/resources/plugins/wow/css/libs/animate.css')
DEST_DIR = os.path.join(ROOT, 'resources/css/vendor-subset')
DEST = os.path.join(DEST_DIR, 'animate.used.css')

# Dem bang grep tren resources/views, resources/css, public/templates/.../js,
# function.js va core/library. So lan dem duoc ghi kem de sau nay doi chieu.
USED = [
    'fadeInRight',   # 66 lan
    'fadeInUp',      # 19 lan
    'fadeInLeft',    #  5 lan
    'fadeInDown',    #  3 lan
    'fadeIn',        #  2 lan
    'flash',         #  2 lan
    'pulse',         #  2 lan
    'slideInLeft',   #  2 lan
]


def split_blocks(css):
    """Tach CSS thanh cac khoi cap cao nhat, giu @keyframes lam mot khoi."""
    out, depth, buf = [], 0, ''
    for ch in css:
        buf += ch
        if ch == '{':
            depth += 1
        elif ch == '}':
            depth -= 1
            if depth == 0:
                out.append(buf.strip())
                buf = ''
    if buf.strip():
        out.append(buf.strip())
    return out


def main():
    css = io.open(SRC, encoding='utf-8', errors='replace').read()
    head = css.split('.animated')[0]          # @charset + giay phep MIT

    wanted = set(USED)
    kept, seen_kf, seen_cls = [], set(), set()

    for block in split_blocks(css):
        sel = block.split('{', 1)[0].strip()

        m = re.match(r'@(?:-\w+-)?keyframes\s+([\w-]+)', sel)
        if m:
            if m.group(1) in wanted:
                kept.append(block)
                seen_kf.add(m.group(1))
            continue

        if sel.startswith('@'):
            continue

        names = [s.strip() for s in sel.split(',')]
        # Giu moi luat .animated* (lop nen) va luat cua cac hieu ung can dung
        keep = False
        for n in names:
            if n.startswith('.animated'):
                keep = True
            cm = re.match(r'^\.([\w-]+)$', n)
            if cm and cm.group(1) in wanted:
                keep = True
                seen_cls.add(cm.group(1))
        if keep:
            kept.append(block)

    missing_kf = wanted - seen_kf
    missing_cls = wanted - seen_cls
    if missing_kf:
        raise SystemExit('thieu @keyframes cho: ' + ', '.join(sorted(missing_kf)))
    if missing_cls:
        raise SystemExit('thieu luat .lop cho: ' + ', '.join(sorted(missing_cls)))

    banner = (
        '/* SINH TU DONG boi deploy/gen-animate-subset.py - DUNG SUA TAY.\n'
        '   animate.css goc co 61 hieu ung / 58 KB; site chi dung %d cai:\n'
        '   %s\n'
        '   Them hieu ung moi thi phai them vao USED trong script roi chay lai. */\n'
        % (len(USED), ', '.join(USED))
    )

    out = banner + head.strip() + '\n\n' + '\n\n'.join(kept) + '\n'
    if not os.path.isdir(DEST_DIR):
        os.makedirs(DEST_DIR)
    io.open(DEST, 'w', encoding='utf-8', newline='\n').write(out)

    print('nguon      : %.0f KB' % (len(css) / 1024))
    print('giu lai    : %d khoi (%d hieu ung)' % (len(kept), len(USED)))
    print('ket qua    : %.1f KB' % (len(out) / 1024))
    print('tiet kiem  : %.0f KB' % ((len(css) - len(out)) / 1024))
    print('ghi ra     :', os.path.relpath(DEST, ROOT))


if __name__ == '__main__':
    main()
