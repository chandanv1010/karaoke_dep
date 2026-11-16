<?php

namespace App\Support;

use App\Models\CodeSnippet;
use App\Models\Redirect;

/**
 * In cac doan ma admin khai bao cho mot vi tri: @snippet('body_end').
 *
 * Loai trang hien tai (home, post, product...) duoc gan boi view composer
 * cua view chinh. View con (@extends) render TRUOC layout nen loai trang
 * luon co san khi header/footer/layout goi @snippet.
 */
class Snippets
{
    protected static ?string $page = null;

    /**
     * View dau tien gan loai trang thi thang: view chinh render truoc, cac
     * view con no @include (vd post.catalogue.component.* trong trang bai
     * viet) khong duoc doi lai.
     */
    public static function setPage(string $page): void
    {
        self::$page ??= $page;
    }

    public static function page(): string
    {
        return self::$page ?? 'other';
    }

    public static function render(string $position): string
    {
        try {
            $items = CodeSnippet::active()[$position] ?? [];
        } catch (\Throwable $e) {
            // Bang chua tao hoac DB loi: tinh nang phu khong duoc lam sap trang.
            return '';
        }
        if (!$items) {
            return '';
        }

        $page = self::page();
        $path = null;
        $html = '';

        foreach ($items as $it) {
            if ($it['pages'] && !in_array($page, $it['pages'], true)) {
                continue;
            }
            if ($it['urls']) {
                $path ??= mb_strtolower(Redirect::normalizePath(request()->getPathInfo()));
                if (!self::matchesAny($path, $it['urls'])) {
                    continue;
                }
            }
            $html .= "\n" . $it['code'] . "\n";
        }

        return $html;
    }

    /** "/abc.html" khop dung; "/loi-bai-hat*" khop moi duong dan bat dau bang no. */
    protected static function matchesAny(string $path, array $patterns): bool
    {
        foreach ($patterns as $p) {
            if (str_ends_with($p, '*')) {
                if (str_starts_with($path, rtrim(substr($p, 0, -1), '/'))) {
                    return true;
                }
            } elseif ($path === $p) {
                return true;
            }
        }
        return false;
    }
}
