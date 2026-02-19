<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $banners = Banner::latest()->paginate(10);
        return view('admin.banners.index', compact('banners'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.banners.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'image_url' => 'required|url|max:500',
            'link' => 'nullable|url|max:500',
            'status' => 'boolean'
        ]);

        try {
            Banner::create([
                'title' => $request->title,
                'image_url' => $request->image_url,
                'link' => $request->link,
                'status' => $request->has('status')
            ]);

            return redirect()->route('admin.banners.index')
                ->with('success', 'Banner created successfully!');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Error creating banner: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Banner $banner)
    {
        return view('admin.banners.show', compact('banner'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Banner $banner)
    {
        return view('admin.banners.edit', compact('banner'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Banner $banner)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'image_url' => 'required|url|max:500',
            'link' => 'nullable|url|max:500',
            'status' => 'boolean'
        ]);

        try {
            $banner->update([
                'title' => $request->title,
                'image_url' => $request->image_url,
                'link' => $request->link,
                'status' => $request->has('status')
            ]);

            return redirect()->route('admin.banners.index')
                ->with('success', 'Banner updated successfully!');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Error updating banner: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Banner $banner)
    {
        try {
            $banner->delete();

            return redirect()->route('admin.banners.index')
                ->with('success', 'Banner deleted successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Error deleting banner: ' . $e->getMessage());
        }
    }
}
