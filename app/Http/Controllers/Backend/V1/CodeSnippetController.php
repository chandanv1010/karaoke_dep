<?php

namespace App\Http\Controllers\Backend\V1;

use App\Http\Controllers\Controller;
use App\Models\CodeSnippet;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Vi Tri Chen Ma: admin dan ma nhung (widget, pixel, chat...) va chon vi tri,
 * loai trang, URL ap dung. Hien ra qua @snippet() trong giao dien.
 */
class CodeSnippetController extends Controller
{
    private function seo(): array
    {
        return [
            'index'  => ['title' => 'Vị Trí Chèn Mã', 'table' => 'Danh sách mã nhúng'],
            'create' => ['title' => 'Thêm mã nhúng'],
            'edit'   => ['title' => 'Sửa mã nhúng'],
            'delete' => ['title' => 'Xóa mã nhúng'],
        ];
    }

    public function index(Request $request)
    {
        $this->authorize('modules', 'snippet.index');
        $snippets = CodeSnippet::query()
            ->when($request->filled('position'), fn ($q) => $q->where('position', $request->input('position')))
            ->orderBy('position')->orderBy('sort')->orderBy('id')
            ->paginate(50)->withQueryString();

        $config = ['seo' => $this->seo(), 'model' => 'CodeSnippet'];
        $template = 'backend.snippet.index';
        return view('backend.dashboard.layout', compact('template', 'config', 'snippets'));
    }

    public function create()
    {
        $this->authorize('modules', 'snippet.create');
        $config = ['seo' => $this->seo(), 'method' => 'create'];
        $snippet = null;
        $template = 'backend.snippet.store';
        return view('backend.dashboard.layout', compact('template', 'config', 'snippet'));
    }

    public function store(Request $request)
    {
        $this->authorize('modules', 'snippet.create');
        $s = CodeSnippet::create($this->validated($request));
        return redirect()->route('snippet.index')->with('success', 'Đã thêm mã nhúng "' . $s->name . '"');
    }

    public function edit($id)
    {
        $this->authorize('modules', 'snippet.edit');
        $snippet = CodeSnippet::findOrFail($id);
        $config = ['seo' => $this->seo(), 'method' => 'edit'];
        $template = 'backend.snippet.store';
        return view('backend.dashboard.layout', compact('template', 'config', 'snippet'));
    }

    public function update(Request $request, $id)
    {
        $this->authorize('modules', 'snippet.edit');
        $s = CodeSnippet::findOrFail($id);
        $s->update($this->validated($request));
        return redirect()->route('snippet.index')->with('success', 'Đã cập nhật mã nhúng "' . $s->name . '"');
    }

    public function delete($id)
    {
        $this->authorize('modules', 'snippet.destroy');
        $snippet = CodeSnippet::findOrFail($id);
        $config = ['seo' => $this->seo()];
        $template = 'backend.snippet.delete';
        return view('backend.dashboard.layout', compact('template', 'config', 'snippet'));
    }

    public function destroy($id)
    {
        $this->authorize('modules', 'snippet.destroy');
        $s = CodeSnippet::findOrFail($id);
        $s->delete();
        return redirect()->route('snippet.index')->with('success', 'Đã xóa mã nhúng "' . $s->name . '"');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:191'],
            'position' => ['required', Rule::in(array_keys(CodeSnippet::POSITIONS))],
            'code'     => ['required', 'string', 'max:65000'],
            'pages'    => ['nullable', 'array'],
            'pages.*'  => [Rule::in(array_keys(CodeSnippet::PAGES))],
            'urls'     => ['nullable', 'string', 'max:5000'],
            'publish'  => ['nullable', 'in:1,2'],
            'sort'     => ['nullable', 'integer', 'min:0', 'max:9999'],
            'note'     => ['nullable', 'string', 'max:255'],
        ], [], [
            'name' => 'Tên', 'position' => 'Vị trí', 'code' => 'Mã nhúng',
            'pages' => 'Loại trang', 'urls' => 'Chỉ các URL',
        ]);

        $pages = $data['pages'] ?? [];
        // Chon het = khong loc, luu rong cho gon va de trang moi sau nay cung hien.
        $data['pages'] = count($pages) === count(CodeSnippet::PAGES) ? null : (implode(',', $pages) ?: null);
        $data['urls'] = trim((string) ($data['urls'] ?? '')) ?: null;
        $data['publish'] = (int) ($data['publish'] ?? 2);
        $data['sort'] = (int) ($data['sort'] ?? 0);
        return $data;
    }
}
