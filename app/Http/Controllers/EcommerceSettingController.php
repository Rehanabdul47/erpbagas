<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EcommerceIntegration;

class EcommerceSettingController extends Controller
{
    public function index()
    {
        $integration = EcommerceIntegration::first() ?? new EcommerceIntegration();
        return view('pages.ecommerce-settings.index', compact('integration'))->with('activePage', 'ecommerce-settings');
    }

    public function update(Request $request)
    {
        $request->validate([
            'platform_name' => 'required',
            'app_key' => 'required',
            'app_secret' => 'required',
            'access_token' => 'required',
        ]);

        $integration = EcommerceIntegration::first();
        if (!$integration) {
            $integration = new EcommerceIntegration();
            $integration->platform_name = $request->platform_name;
        }

        $integration->app_key = $request->app_key;
        $integration->app_secret = $request->app_secret;
        $integration->access_token = $request->access_token;
        $integration->shop_cipher = $request->shop_cipher;
        $integration->is_active = $request->has('is_active');
        $integration->save();

        return redirect()->back()->with('success', 'Pengaturan integrasi berhasil disimpan.');
    }
}
