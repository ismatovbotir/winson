<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\StoresImages;
use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Support\SafeUrl;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    use StoresImages;

    public function index()
    {
        return view('admin.banners.index', ['banners' => Banner::orderBy('sort_order')->orderBy('id')->get()]);
    }

    public function create()
    {
        return view('admin.banners.form', ['banner' => new Banner(['is_active' => true, 'sort_order' => Banner::max('sort_order') + 1])]);
    }

    public function store(Request $request)
    {
        $this->save($request, new Banner);

        return redirect()->route('admin.banners.index')->with('status', __('admin.common.saved'));
    }

    public function edit(Banner $banner)
    {
        return view('admin.banners.form', compact('banner'));
    }

    public function update(Request $request, Banner $banner)
    {
        $this->save($request, $banner);

        return redirect()->route('admin.banners.index')->with('status', __('admin.common.saved'));
    }

    public function destroy(Banner $banner)
    {
        $this->deleteImage($banner->image);
        $banner->delete();

        return redirect()->route('admin.banners.index')->with('status', __('admin.common.deleted'));
    }

    private function save(Request $request, Banner $banner): void
    {
        $data = $request->validate([
            'image' => [$banner->exists ? 'nullable' : 'required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'title_uz' => ['nullable', 'string', 'max:255'],
            'title_ru' => ['nullable', 'string', 'max:255'],
            'text_uz' => ['nullable', 'string', 'max:1000'],
            'text_ru' => ['nullable', 'string', 'max:1000'],
            'button_uz' => ['nullable', 'string', 'max:60'],
            'button_ru' => ['nullable', 'string', 'max:60'],
            'link' => ['nullable', 'string', 'max:500', 'regex:'.SafeUrl::PATTERN],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['sort_order'] ??= 0;
        $data['is_active'] = $request->boolean('is_active');
        unset($data['image']);

        if ($request->hasFile('image')) {
            $data['image'] = $this->storeImage($request->file('image'), 'banners', $banner->image);
        }

        $banner->fill($data)->save();
    }
}
