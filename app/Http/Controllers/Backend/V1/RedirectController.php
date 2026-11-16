<?php

namespace App\Http\Controllers\Backend\V1;

use App\Http\Controllers\Controller;
use App\Models\Redirect;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Validator;

/**
 * QL Chuyen Huong: admin tu khai bao 301/302 ma khong can sua file tren
 * server. Ap dung boi middleware App\Http\Middleware\ManagedRedirects.
 */
class RedirectController extends Controller
{
    private function seo(): array
    {
        return [
            'index'  => ['title' => 'QL Chuyển Hướng 301', 'table' => 'Danh sách chuyển hướng'],
            'create' => ['title' => 'Thêm chuyển hướng'],
            'edit'   => ['title' => 'Sửa chuyển hướng'],
            'delete' => ['title' => 'Xóa chuyển hướng'],
        ];
    }

    public function index(Request $request)
    {
        $this->authorize('modules', 'redirect.index');

        $keyword = trim((string) $request->input('keyword'));
        $redirects = Redirect::query()
            ->when($keyword !== '', fn ($q) => $q->where(function ($q) use ($keyword) {
                $q->where('source_path', 'like', "%{$keyword}%")
                  ->orWhere('target_url', 'like', "%{$keyword}%")
                  ->orWhere('note', 'like', "%{$keyword}%");
            }))
            ->orderByDesc('id')
            ->paginate(30)
            ->withQueryString();

        $config = ['seo' => $this->seo(), 'model' => 'Redirect'];
        $template = 'backend.redirect.index';
        return view('backend.dashboard.layout', compact('template', 'config', 'redirects', 'keyword'));
    }

    public function create()
    {
        $this->authorize('modules', 'redirect.create');
        $config = ['seo' => $this->seo(), 'method' => 'create'];
        $redirect = null;
        $template = 'backend.redirect.store';
        return view('backend.dashboard.layout', compact('template', 'config', 'redirect'));
    }

    public function store(Request $request)
    {
        $this->authorize('modules', 'redirect.create');
        $data = $this->validated($request);
        Redirect::create($data);
        return redirect()->route('redirect.index')->with('success', 'Đã thêm chuyển hướng ' . $data['source_path']);
    }

    public function edit($id)
    {
        $this->authorize('modules', 'redirect.edit');
        $redirect = Redirect::findOrFail($id);
        $config = ['seo' => $this->seo(), 'method' => 'edit'];
        $template = 'backend.redirect.store';
        return view('backend.dashboard.layout', compact('template', 'config', 'redirect'));
    }

    public function update(Request $request, $id)
    {
        $this->authorize('modules', 'redirect.edit');
        $redirect = Redirect::findOrFail($id);
        $redirect->update($this->validated($request, $redirect->id));
        return redirect()->route('redirect.index')->with('success', 'Đã cập nhật chuyển hướng ' . $redirect->source_path);
    }

    public function delete($id)
    {
        $this->authorize('modules', 'redirect.destroy');
        $redirect = Redirect::findOrFail($id);
        $config = ['seo' => $this->seo()];
        $template = 'backend.redirect.delete';
        return view('backend.dashboard.layout', compact('template', 'config', 'redirect'));
    }

    public function destroy($id)
    {
        $this->authorize('modules', 'redirect.destroy');
        $redirect = Redirect::findOrFail($id);
        $redirect->delete();
        return redirect()->route('redirect.index')->with('success', 'Đã xóa chuyển hướng ' . $redirect->source_path);
    }

    /**
     * Chuan hoa + kiem tra. Chan 4 loi de lam hong site:
     *   - nguon la trang chu "/"
     *   - nguon trung voi trang quan tri (admin se tu khoa minh o ngoai)
     *   - dich tro ve chinh nguon, hoac tao vong A -> B -> A
     *   - nguon da khai bao o dong khac
     */
    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $source = Redirect::normalizePath($request->input('source_path'));
        $target = trim((string) $request->input('target_url'));
        if ($target !== '' && !preg_match('#^https?://#i', $target)) {
            $target = Redirect::normalizePath($target);
        } elseif ($target !== '' && $this->isOwnHost($target)) {
            // Dich la link noi bo viet day du -> luu dang duong dan cho gon,
            // va de doi domain/https sau nay khong phai sua lai.
            $parts = parse_url($target);
            $target = Redirect::normalizePath($parts['path'] ?? '/')
                . (isset($parts['query']) ? '?' . $parts['query'] : '');
        }

        $request->merge(['source_path' => $source, 'target_url' => $target]);

        $validator = validator($request->all(), [
            'source_path' => ['required', 'string', 'max:500'],
            'target_url'  => ['required', 'string', 'max:1000'],
            'status_code' => ['required', 'in:301,302'],
            'publish'     => ['nullable', 'in:1,2'],
            'note'        => ['nullable', 'string', 'max:255'],
        ], [], [
            'source_path' => 'Đường dẫn cũ',
            'target_url'  => 'Đường dẫn mới',
            'status_code' => 'Kiểu chuyển hướng',
        ]);

        $validator->after(function (Validator $v) use ($source, $target, $ignoreId) {
            if ($source === '/') {
                $v->errors()->add('source_path', 'Không được chuyển hướng trang chủ.');
            }
            if ($source !== '' && $this->isAdminPath($source)) {
                $v->errors()->add('source_path', 'Đường dẫn cũ trùng với trang quản trị, không thể chuyển hướng.');
            }
            $dup = Redirect::where('source_path', $source)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->first();
            if ($dup) {
                $v->errors()->add('source_path', "Đường dẫn cũ này đã có chuyển hướng (dòng #{$dup->id}).");
            }
            $targetPath = preg_match('#^https?://#i', $target) ? null : Redirect::normalizePath($target);
            if ($targetPath !== null && mb_strtolower($targetPath) === mb_strtolower($source)) {
                $v->errors()->add('target_url', 'Đường dẫn mới trùng đường dẫn cũ - sẽ thành vòng lặp.');
            }
            if ($targetPath !== null) {
                $back = Redirect::where('source_path', $targetPath)
                    ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                    ->first();
                if ($back && mb_strtolower(Redirect::normalizePath($back->target_url)) === mb_strtolower($source)) {
                    $v->errors()->add('target_url', "Tạo vòng lặp với dòng #{$back->id} ({$back->source_path} → {$back->target_url}).");
                }
            }
        });

        $data = $validator->validate();
        $data['publish'] = (int) ($data['publish'] ?? 2);
        return $data;
    }

    private function isOwnHost(string $url): bool
    {
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $own  = strtolower((string) parse_url(config('app.url'), PHP_URL_HOST));
        $host = preg_replace('/^www\./', '', $host);
        $own  = preg_replace('/^www\./', '', $own);
        return $host !== '' && ($host === $own || $host === strtolower(request()->getHost()));
    }

    private function isAdminPath(string $path): bool
    {
        $probe = \Illuminate\Http\Request::create($path, 'GET');
        foreach (Route::getRoutes()->get('GET') as $route) {
            $mw = $route->gatherMiddleware();
            $isAdmin = in_array('admin', $mw, true) || in_array('login', $mw, true)
                || str_starts_with((string) $route->getName(), 'auth.');
            if (!$isAdmin) {
                continue;
            }
            if ($route->matches($probe)) {
                return true;
            }
        }
        return false;
    }
}
