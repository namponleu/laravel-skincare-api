<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    /**
     * Get all active banners
     */
    public function index()
    {
        try {
            $banners = Banner::active()
                ->select('id', 'title', 'image_url', 'link', 'status', 'created_at')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Banners retrieved successfully',
                'data' => $banners
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving banners',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific banner by ID
     */
    public function show($id)
    {
        try {
            $banner = Banner::active()
                ->select('id', 'title', 'image_url', 'link', 'status', 'created_at')
                ->find($id);

            if (!$banner) {
                return response()->json([
                    'success' => false,
                    'message' => 'Banner not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Banner retrieved successfully',
                'data' => $banner
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving banner',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get banners by status (active/inactive)
     */
    public function getByStatus($status)
    {
        try {
            $isActive = $status === 'active' ? true : false;
            
            $banners = Banner::where('status', $isActive)
                ->select('id', 'title', 'image_url', 'link', 'status', 'created_at')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Banners retrieved successfully',
                'data' => $banners
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving banners',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get featured banners (latest active banners)
     */
    public function featured($limit = 5)
    {
        try {
            $banners = Banner::active()
                ->select('id', 'title', 'image_url', 'link', 'status', 'created_at')
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Featured banners retrieved successfully',
                'data' => $banners
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving featured banners',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
