<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Exception;

/**
 * Resize anh theo kich thuoc khung hien thi, cache lai trong public/image-cache.
 *
 * Dung: /thumb?src=/uploads/images/abc.jpg&w=300&h=300
 *
 * BAN GOC CO 3 LO HONG KIEU TIMTHUMB, da va o day:
 *
 *  1. Path traversal: $src duoc ghep thang vao public_path() ma khong kiem tra,
 *     nen ?src=../../.env doc duoc file ngoai public/. Gio duong dan phai nam
 *     trong danh sach thu muc cho phep va duoc realpath() doi chieu lai.
 *
 *  2. Open redirect: khoi catch tra ve redirect($src). Voi ?src=https://evil.com
 *     thi ung dung tu dieu huong khach sang site lạ. Gio chi redirect ve duong
 *     dan noi bo da qua kiem tra.
 *
 *  3. DoS: w/h khong gioi han, ?w=99999&h=99999 lam can RAM. Gio chan tran
 *     kich thuoc va khong cho phong to hon anh goc.
 */
class ImageResizerController extends Controller
{
    /** Chi cho phep lay anh nguon tu cac thu muc nay (tuong doi voi public/). */
    private const ALLOWED_DIRS = ['uploads', 'userfiles', 'images', 'templates'];

    /** Tran kich thuoc dau ra. */
    private const MAX_DIMENSION = 2000;

    /** Duoi file cho phep. */
    private const ALLOWED_EXT = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    public function resize(Request $request)
    {
        $src = (string) $request->query('src', '');
        $width = $this->normalizeDimension($request->query('w'));
        $height = $this->normalizeDimension($request->query('h'));

        $originalPath = $this->resolveSafePath($src);

        // Nguon khong hop le -> 404. KHONG redirect($src) de tranh open redirect.
        if ($originalPath === null) {
            return abort(404, 'Image not found');
        }

        // Khong yeu cau kich thuoc -> tra ve chinh file goc (duong dan da an toan)
        if (!$width && !$height) {
            return response()->file($originalPath);
        }

        try {
            $info = @getimagesize($originalPath);
            if ($info === false) {
                return abort(415, 'Unsupported image type');
            }

            [$originalWidth, $originalHeight, $type] = $info;
            if ($originalWidth < 1 || $originalHeight < 1) {
                return abort(415, 'Unsupported image type');
            }

            [$newWidth, $newHeight] = $this->targetSize(
                $originalWidth, $originalHeight, $width, $height
            );

            // Anh goc da nho hon khung -> khong phong to (vua mo, vua ton dung
            // luong hon ban goc), tra ve file goc.
            if ($newWidth >= $originalWidth && $newHeight >= $originalHeight) {
                return response()->file($originalPath);
            }

            $cacheDir = public_path('image-cache');
            $ext = strtolower(pathinfo($originalPath, PATHINFO_EXTENSION));
            $cachePath = $cacheDir . '/' . md5($originalPath . $newWidth . 'x' . $newHeight) . '.' . $ext;

            if (File::exists($cachePath)) {
                return response()->file($cachePath);
            }

            if (!File::exists($cacheDir)) {
                File::makeDirectory($cacheDir, 0755, true);
            }

            $sourceImage = $this->createFrom($originalPath, $type);
            if ($sourceImage === null) {
                return abort(415, 'Unsupported image type');
            }

            $newImage = imagecreatetruecolor($newWidth, $newHeight);

            // Giu nen trong suot cho PNG va GIF
            if ($type === IMAGETYPE_PNG || $type === IMAGETYPE_GIF) {
                imagealphablending($newImage, false);
                imagesavealpha($newImage, true);
                $transparent = imagecolorallocatealpha($newImage, 0, 0, 0, 127);
                imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
            }

            imagecopyresampled(
                $newImage, $sourceImage,
                0, 0, 0, 0,
                $newWidth, $newHeight, $originalWidth, $originalHeight
            );

            $this->saveImage($newImage, $cachePath, $type);

            imagedestroy($sourceImage);
            imagedestroy($newImage);

            return response()->file($cachePath);
        } catch (Exception $e) {
            report($e);
            // Resize that bai -> tra ve anh goc (duong dan noi bo da kiem tra),
            // khong dung redirect($src).
            return response()->file($originalPath);
        }
    }

    /**
     * Doi chieu $src ve mot duong dan that su nam trong public/ va thuoc thu muc
     * cho phep. Tra ve null neu khong hop le.
     */
    private function resolveSafePath(string $src): ?string
    {
        $src = trim($src);
        if ($src === '') {
            return null;
        }

        // Chi nhan duong dan noi bo: chan URL tuyet doi va protocol-relative.
        if (preg_match('#^([a-z][a-z0-9+.-]*:)?//#i', $src)) {
            return null;
        }

        // Bo query string / fragment neu co
        $src = strtok($src, '?#') ?: '';
        $src = urldecode($src);

        // Chan ky tu NUL va cac doan di len thu muc cha
        if (str_contains($src, "\0") || str_contains($src, '..')) {
            return null;
        }

        $relative = ltrim($src, '/');

        // Phai bat dau bang mot trong cac thu muc cho phep
        $firstSegment = strtolower(explode('/', $relative)[0] ?? '');
        if (!in_array($firstSegment, self::ALLOWED_DIRS, true)) {
            return null;
        }

        $ext = strtolower(pathinfo($relative, PATHINFO_EXTENSION));
        if (!in_array($ext, self::ALLOWED_EXT, true)) {
            return null;
        }

        $full = realpath(public_path($relative));
        $root = realpath(public_path());
        if ($full === false || $root === false) {
            return null;
        }

        // Chot chan cuoi: duong dan that phai nam trong public/
        if (!str_starts_with($full, $root . DIRECTORY_SEPARATOR)) {
            return null;
        }

        return is_file($full) ? $full : null;
    }

    /** Chi nhan so nguyen duong, chan tran MAX_DIMENSION. */
    private function normalizeDimension($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (!is_numeric($value)) {
            return null;
        }
        $n = (int) $value;
        if ($n < 1) {
            return null;
        }
        return min($n, self::MAX_DIMENSION);
    }

    /** Tinh kich thuoc dau ra, giu ti le khi chi truyen mot chieu. */
    private function targetSize(int $ow, int $oh, ?int $w, ?int $h): array
    {
        if ($w && $h) {
            return [$w, $h];
        }
        if ($w) {
            return [$w, max(1, (int) round($oh * ($w / $ow)))];
        }
        return [max(1, (int) round($ow * ($h / $oh))), $h];
    }

    private function createFrom(string $path, int $type)
    {
        $im = match ($type) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
            IMAGETYPE_PNG  => @imagecreatefrompng($path),
            IMAGETYPE_GIF  => @imagecreatefromgif($path),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
            default        => false,
        };

        return $im === false ? null : $im;
    }

    private function saveImage($image, string $path, int $type): void
    {
        match ($type) {
            IMAGETYPE_JPEG => imagejpeg($image, $path, 82),
            IMAGETYPE_PNG  => imagepng($image, $path, 9),
            IMAGETYPE_GIF  => imagegif($image, $path),
            IMAGETYPE_WEBP => function_exists('imagewebp') ? imagewebp($image, $path, 82) : imagejpeg($image, $path, 82),
            default        => imagejpeg($image, $path, 82),
        };
    }
}
