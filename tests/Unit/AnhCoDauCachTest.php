<?php

namespace Tests\Unit;

use Tests\TestCase;

/**
 * Anh co DAU CACH trong ten file.
 *
 * Loi that da xay ra tren karaokedep.com.vn: hai the trong khoi "Thi cong
 * karaoke" o trang chu bi vo anh, trong khi cung bai do o trang danh muc van
 * hien binh thuong.
 *
 * Nguyen nhan: CSDL luu duong dan da MA HOA URL
 *
 *     /userfiles/image/123/...azktv%20(1).jpg
 *
 * con ten file that tren dia co dau cach. Trang chu goi file_exists() thang
 * vao chuoi do - file_exists doc dia chu khong doc URL - nen khong bao gio
 * thay, va roi xuong anh du phong. Trang danh muc di qua getthumb(), von
 * khong kiem tra su ton tai, nen khong dinh.
 */
class AnhCoDauCachTest extends TestCase
{
    private string $thuMuc;

    private array $daTao = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->thuMuc = 'userfiles/thu-nghiem-' . bin2hex(random_bytes(4));

        if (!is_dir(public_path($this->thuMuc))) {
            mkdir(public_path($this->thuMuc), 0775, true);
        }
    }

    protected function tearDown(): void
    {
        foreach ($this->daTao as $f) {
            @unlink($f);
        }

        @rmdir(public_path($this->thuMuc));

        parent::tearDown();
    }

    /** Tao mot file anh that de phep kiem co cai ma soi. */
    private function taoAnh(string $ten): string
    {
        $duong = public_path($this->thuMuc . '/' . $ten);

        file_put_contents($duong, 'khong phai anh that, chi can file ton tai');
        $this->daTao[] = $duong;

        return '/' . $this->thuMuc . '/' . $ten;
    }

    // --- image_file_exists ----------------------------------------------------

    public function test_ten_file_binh_thuong_thi_thay(): void
    {
        $this->assertTrue(image_file_exists($this->taoAnh('anh-binh-thuong.jpg')));
    }

    /**
     * Day la chinh cai loi tren trang that.
     *
     * File tren dia ten 'azktv (1).jpg'; CSDL luu 'azktv%20(1).jpg'.
     */
    public function test_duong_dan_ma_hoa_url_van_tim_thay_file_co_dau_cach(): void
    {
        $this->taoAnh('azktv (1).jpg');

        $luuTrongCsdl = '/' . $this->thuMuc . '/azktv%20(1).jpg';

        $this->assertTrue(
            image_file_exists($luuTrongCsdl),
            'Duong dan ma hoa URL khong tim ra file co dau cach - dung loi cu',
        );
    }

    /** Ca thu muc cung co the co dau cach. */
    public function test_thu_muc_co_dau_cach(): void
    {
        $con = public_path($this->thuMuc . '/phong karaoke gia dinh');
        mkdir($con, 0775, true);

        $duong = $con . '/anh (2).jpg';
        file_put_contents($duong, 'x');
        $this->daTao[] = $duong;

        $this->assertTrue(image_file_exists(
            '/' . $this->thuMuc . '/phong%20karaoke%20gia%20dinh/anh%20(2).jpg',
        ));

        @rmdir($con);
    }

    public function test_file_khong_co_that_thi_bao_khong(): void
    {
        $this->assertFalse(image_file_exists('/' . $this->thuMuc . '/khong-he-ton-tai.jpg'));
    }

    public function test_rong_thi_bao_khong(): void
    {
        $this->assertFalse(image_file_exists(''));
        $this->assertFalse(image_file_exists(null));
    }

    /** Anh tren domain khac thi khong kiem tra duoc, coi nhu co. */
    public function test_anh_ngoai_domain_coi_nhu_co(): void
    {
        $this->assertTrue(image_file_exists('https://example.test/anh.jpg'));
    }

    /**
     * Khong duoc de duong dan di ra ngoai public/.
     *
     * rawurldecode mo them mot cua: %2e%2e%2f cung giai ma thanh '../'.
     */
    public function test_khong_voi_ra_ngoai_thu_muc_public(): void
    {
        $this->assertFalse(image_file_exists('/../.env'));
        $this->assertFalse(image_file_exists('/%2e%2e%2f.env'));
    }

    // --- image_or_fallback ----------------------------------------------------

    public function test_co_anh_thi_tra_ve_dia_chi_anh_do(): void
    {
        $duong = $this->taoAnh('co-that.jpg');

        $this->assertSame(asset(ltrim($duong, '/')), image_or_fallback($duong));
    }

    public function test_anh_ma_hoa_url_tra_ve_dung_chuoi_da_luu(): void
    {
        $this->taoAnh('azktv (1).jpg');
        $luu = '/' . $this->thuMuc . '/azktv%20(1).jpg';

        // Giu nguyen dang da ma hoa: do la dang trinh duyet can trong thuoc
        // tinh src. Giai ma roi tra ve la sinh mot URL co dau cach tho.
        $this->assertStringContainsString('azktv%20(1).jpg', image_or_fallback($luu));
    }

    /**
     * Anh thieu thi tra ve anh thay the CO THAT.
     *
     * Truoc day danh sach du phong ghi cung bon duong dan ma ca bon deu khong
     * con tren may chu - thanh ra thay vi "anh sai" thi nguoi dung thay "o vo
     * anh", va khong ai nhan ra day la anh du phong.
     */
    public function test_thieu_anh_thi_rot_ve_no_image_co_that(): void
    {
        $ra = image_or_fallback('/' . $this->thuMuc . '/khong-co.jpg');

        $this->assertStringContainsString('no-image', $ra);
        $this->assertFileExists(public_path('images/no-image.jpg'));
    }

    public function test_rong_thi_cung_rot_ve_no_image(): void
    {
        $this->assertStringContainsString('no-image', image_or_fallback(''));
        $this->assertStringContainsString('no-image', image_or_fallback(null));
    }

    public function test_tu_chon_anh_thay_the_khac(): void
    {
        $this->assertStringContainsString(
            'rieng.jpg',
            image_or_fallback('/khong-co.jpg', '/images/rieng.jpg'),
        );
    }

    public function test_anh_ngoai_domain_giu_nguyen(): void
    {
        $ngoai = 'https://example.test/anh.jpg';

        $this->assertSame($ngoai, image_or_fallback($ngoai));
    }
}
