#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Sinh CSS toi thieu cho phan dau trang chu (critical CSS).

VI SAO CAN
    PageSpeed do "Render-blocking requests - Est savings of 1.140 ms": trinh
    duyet phai tai xong ca file CSS 91 KB roi moi ve duoc gi len man hinh.
    Nhung phan dau trang chi dung mot phan nho cua file do.

CACH DUNG
    1. Mo trang chu that, DevTools console, chay doan JS trong
       deploy/critical-selectors.md de lay danh sach selector cua cac phan tu
       nam trong man hinh dau. Dan ket qua vao SELECTORS ben duoi.
    2. python deploy/gen-critical-css.py
    3. npm run build  (neu CSS nguon co doi)

    Script doc file CSS da build (public/build/assets/app-*.css) va giu lai
    dung nhung luat co selector nam trong danh sach, ke ca luat trong @media.

VI SAO KHONG LAY THANG CHUOI CSS TU TRINH DUYET
    Cac luat .fa-*::before chua ky tu vung dung rieng (private use area) cho
    icon FontAwesome; chuyen qua dang van ban thi bi meo. Lay danh sach
    selector roi doc lai tu file tren dia thi giu nguyen byte goc.

KHI NAO PHAI CHAY LAI
    Moi khi doi bo cuc phan dau trang chu, hoac doi CSS cua header / hero /
    khoi gioi thieu. Khong chay lai thi CSS toi thieu bi cu va phan dau trang
    se hien sai trong khoang khac dau tien.
"""
import io
import os
import re
import glob

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))

# Selector cua cac phan tu nam trong man hinh dau (375x812), lay bang doan JS
# trong deploy/critical-selectors.md.
SELECTORS = """
html
.uk-article-title a, .uk-link, .uk-nav li > a, .uk-navbar-nav > li > a, .uk-panel, .uk-panel:hover, a, ins
b, dt, strong
a
.uk-link, a
.uk-link:hover, a:hover
hr, img
img
.uk-hidden-large, .uk-visible-medium, .uk-visible-small
blockquote, img, pre, tr
*, ::after, ::before
a, a:visited
.uk-slider, .uk-slideshow
.uk-slider-container, .uk-slideshow
.uk-slider:not(.uk-grid), .uk-slideshow
body
h1, h2, h3, h4, h5, h6
ol, ul
body, h1, h2, h3, h4, h5, h6, .heading-font, .banner h1, .banner h2, a, span, p
.karaoke-intro
.karaoke-intro__media
.karaoke-intro__image img
.karaoke-intro__image--1
.karaoke-intro__image--1::before
.social-icons-float .whatsapp
.karaoke-hero__bg
.karaoke-hero__overlay
.karaoke-hero__content
.karaoke-hero__stats
.karaoke-intro__image--1, .karaoke-intro__image--2
.karaoke-shell
.karaoke-hero__desc
.karaoke-hero__actions
.logo img, .header-new .logo img, .header-new .main-header-new .logo img, .header-new .mobile-header .logo img, .mobile-header-new .logo img
:root
.fa
.fa-search::before
.fa-phone::before
.fa-facebook-f::before, .fa-facebook::before
.fa-navicon::before, .fa-reorder::before, .fa-bars::before
.fa-long-arrow-right::before
.fa-whatsapp::before
.fa-commenting-o::before
a:active, a:hover
b, strong
audio, canvas, iframe, img, svg, video
audio, canvas, img, svg, video
address, blockquote, dl, fieldset, figure, ol, p, pre, ul
article, aside, details, figcaption, figure, footer, header, main, nav, section, summary
.uk-visible-small, .uk-visible-medium, .uk-hidden-large
img, tr
.uk-slidenav-position
.uk-slideshow
.uk-slideshow > li
.uk-slideshow > .uk-active
*
ul
ul li
.karaoke-home
.karaoke-section-bg
.karaoke-btn, .home-designs .btn-more
.karaoke-btn:hover, .home-designs .btn-more:hover
.karaoke-hero
.karaoke-hero__stats-wrap
.karaoke-hero__stat
.karaoke-hero__stat strong
.karaoke-hero__stat span
.karaoke-intro__grid
.karaoke-intro__image
.karaoke-intro__image::before, .karaoke-intro__image::after
.karaoke-intro__image--1::after
.header-new
.header-new a:hover, .header-new .main-nav li.active a
.social-icons-float
.social-icons-float a
.social-icons-float a:hover
.social-icons-float .zalo
.social-icons-float .messenger
.social-icons-float .phone
.header-new .mobile-header-new
.header-new .mobile-header-new .logo img
.header-new .mobile-header-new .toggle-btn
h1, h2, h3, h4, h5, h6, .title, .heading, .karaoke-section-heading h2, .karaoke-hero__eyebrow, .karaoke-hero__title, .col-header, .card-title, .tab
ul li, ol li
.karaoke-hero .uk-slideshow > li:first-child
"""


def norm(sel):
    """Bo khoang trang de so sanh: trinh duyet in 'a, b' con file minify co 'a,b'."""
    return re.sub(r'\s*([,>~+])\s*', r'\1', re.sub(r'\s+', ' ', sel)).strip()


def split_blocks(css):
    """Tach CSS thanh cac khoi cap cao nhat, giu nguyen @media lam mot khoi."""
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
    return out


def main():
    wanted = {norm(s) for s in SELECTORS.strip().split('\n') if s.strip()}

    files = sorted(glob.glob(os.path.join(ROOT, 'public/build/assets/app-*.css')))
    files = [f for f in files if 'backend' not in os.path.basename(f)]
    if not files:
        raise SystemExit('khong tim thay public/build/assets/app-*.css - chay npm run build truoc')
    src = files[-1]

    css = io.open(src, encoding='utf-8', errors='replace').read()
    css = re.sub(r'/\*.*?\*/', '', css, flags=re.S)

    kept = []
    for block in split_blocks(css):
        head = block.split('{', 1)[0].strip()

        if head.startswith('@media'):
            inner = block[block.index('{') + 1:block.rindex('}')]
            keep_inner = [b for b in split_blocks(inner)
                          if norm(b.split('{', 1)[0]) in wanted]
            if keep_inner:
                kept.append(head + '{' + ''.join(keep_inner) + '}')
        elif head.startswith('@'):
            # @font-face da duoc nhung rieng o frontend/component/fonts.blade.php,
            # @keyframes / @supports khong can cho lan ve dau tien.
            continue
        elif norm(head) in wanted:
            kept.append(block)

    out = ''.join(kept)
    out = re.sub(r'\s*([{}:;,>~+])\s*', r'\1', out)
    out = out.replace(';}', '}')

    dest = os.path.join(ROOT, 'resources/views/frontend/component/critical-css.blade.php')
    io.open(dest, 'w', encoding='utf-8', newline='\n').write(
        '{{-- SINH TU DONG boi deploy/gen-critical-css.py - DUNG SUA TAY.\n'
        '     Doi bo cuc phan dau trang chu thi phai chay lai script do. --}}\n'
        '<style>' + out + '</style>\n'
    )

    print('nguon      :', os.path.basename(src))
    print('luat giu   :', len(kept))
    print('kich thuoc :', len(out), 'bytes')
    print('ghi ra     :', os.path.relpath(dest, ROOT))


if __name__ == '__main__':
    main()
