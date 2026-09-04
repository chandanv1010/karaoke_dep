<?php

namespace App\Traits;

/**
 * Sinh khoi JSON-LD cho SEO.
 *
 * Truoc day cac controller noi chuoi JSON bang tay. Cach do sinh ra 3 loi khien
 * Google Search Console bao "loi phan tich cu phap, thieu } hoac ten thanh vien
 * doi tuong":
 *   1. Dau phay thua truoc dau } o cuoi moi phan tu trong vong lap.
 *   2. Nhieu object o cap goc ("{...},{...}") trong cung mot the <script>.
 *      Mot tai lieu JSON chi duoc co dung mot gia tri goc.
 *   3. Ten/mo ta duoc chen tho, san pham nao chua dau " la vo JSON.
 *
 * Dung json_encode se xu ly ca ba: khong bao gio sinh phay thua, tu escape ky tu
 * dac biet, va nhieu schema duoc gom vao @graph theo dung chuan schema.org.
 */
trait RendersSchema
{
    /**
     * Lam sach text truoc khi dua vao schema.
     *
     * strip_tags() chi bo the, con giu nguyen entity (&nbsp;) va toan bo xuong
     * dong / tab cua trinh soan thao, khien description trong schema day \r\n\t.
     */
    protected function schemaText($value, int $limit = 0): string
    {
        $text = html_entity_decode(strip_tags((string) $value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        // \xC2\xA0 la non-breaking space sau khi decode &nbsp;
        $text = str_replace("\xC2\xA0", ' ', $text);
        $text = trim(preg_replace('/\s+/u', ' ', $text));

        if ($limit > 0 && mb_strlen($text, 'UTF-8') > $limit) {
            $text = rtrim(mb_substr($text, 0, $limit, 'UTF-8')) . '…';
        }

        return $text;
    }

    /**
     * @param array $nodes Danh sach node schema.org (moi node la mot mang PHP).
     */
    protected function renderSchema(array $nodes): string
    {
        $nodes = array_values(array_filter($nodes));

        if (empty($nodes)) {
            return '';
        }

        $payload = ['@context' => 'https://schema.org'];

        if (count($nodes) === 1) {
            $payload += $nodes[0];
        } else {
            $payload['@graph'] = $nodes;
        }

        $json = json_encode(
            $payload,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
        );

        if ($json === false) {
            return '';
        }

        // Chan chuoi "</script>" nam trong noi dung lam dut the script som.
        $json = str_replace('<', '<', $json);

        return '<script type="application/ld+json">' . $json . '</script>';
    }
}
