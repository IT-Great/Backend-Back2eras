<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class VoucherController extends Controller
{
    // Kerangka 7 Brand & Rule masing-masing (mudah dikustomisasi per brand nanti)
    private function getBrandsConfig(): array
    {
        return [
            [
                'slug'        => 'back2eras',
                'name'        => 'Back2eras',
                'prefix'      => 'B2E',
                'rule_summary' => 'Berlaku 1x klaim per akun untuk promo resmi BACK2ERAS 2026.',
            ],
            [
                'slug'        => 'solher',
                'name'        => 'Solher',
                'prefix'      => 'SLH',
                'rule_summary' => 'Berlaku 1x klaim per akun sesuai syarat & ketentuan Solher.',
            ],
            [
                'slug'        => 'gycora',
                'name'        => 'Gycora',
                'prefix'      => 'GYC',
                'rule_summary' => 'Berlaku 1x klaim per akun sesuai syarat & ketentuan Gycora.',
            ],
            [
                'slug'        => 'glamoire',
                'name'        => 'Glamoire',
                'prefix'      => 'GLM',
                'rule_summary' => 'Berlaku 1x klaim per akun sesuai syarat & ketentuan Glamoire.',
            ],
            [
                'slug'        => 'wdp',
                'name'        => 'WDP',
                'prefix'      => 'WDP',
                'rule_summary' => 'Berlaku 1x klaim per akun sesuai syarat & ketentuan WDP.',
            ],
            [
                'slug'        => 'kecilung',
                'name'        => 'Kecilung',
                'prefix'      => 'KCL',
                'rule_summary' => 'Berlaku 1x klaim per akun sesuai syarat & ketentuan Kecilung.',
            ],
            [
                'slug'        => 'malaijia',
                'name'        => 'Malaijia',
                'prefix'      => 'MLJ',
                'rule_summary' => 'Berlaku 1x klaim per akun sesuai syarat & ketentuan Malaijia.',
            ],
        ];
    }

    // PROTECTED: Menampilkan daftar 7 brand + status klaim milik user yang login
    public function index(Request $request)
    {
        $user = $request->user();
        $claimedMap = Voucher::where('user_id', $user->id)
            ->get()
            ->keyBy('brand_slug');

        $brands = collect($this->getBrandsConfig())->map(function ($brand) use ($claimedMap) {
            $claimed = $claimedMap->get($brand['slug']);
            return [
                'slug'         => $brand['slug'],
                'name'         => $brand['name'],
                'rule_summary' => $brand['rule_summary'],
                'claimed'      => (bool) $claimed,
                'code'         => $claimed?->code,
                'is_used'      => (bool) ($claimed?->is_used ?? false),
                'claimed_at'   => $claimed?->created_at,
            ];
        });

        return response()->json([
            'brands' => $brands,
        ]);
    }

    // PROTECTED: Klaim kode unik untuk brand tertentu (1x per user per brand)
    public function claim(Request $request)
    {
        $validated = $request->validate([
            'brand_slug' => 'required|string',
        ]);

        $user = $request->user();
        $brandConfig = collect($this->getBrandsConfig())->firstWhere('slug', $validated['brand_slug']);

        if (!$brandConfig) {
            return response()->json(['message' => 'Brand tidak ditemukan.'], 404);
        }

        // Cek apakah user sudah pernah klaim brand ini
        $existing = Voucher::where('user_id', $user->id)
            ->where('brand_slug', $brandConfig['slug'])
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Anda sudah mengklaim voucher untuk ' . $brandConfig['name'] . '.',
                'voucher' => $existing,
            ], 422);
        }

        // TODO: Validasi aturan khusus tiap brand bisa ditaruh di sini (misal kuota / syarat urutan user)
        // switch ($brandConfig['slug']) {
        //     case 'back2eras': ... break;
        // }

        // Generate kode unik yang dijamin berbeda tiap user
        do {
            $uniqueCode = sprintf(
                '%s-%s-%s',
                $brandConfig['prefix'],
                str_pad((string) $user->id, 3, '0', STR_PAD_LEFT),
                strtoupper(Str::random(5))
            );
        } while (Voucher::where('code', $uniqueCode)->exists());

        $voucher = Voucher::create([
            'user_id'    => $user->id,
            'brand_slug' => $brandConfig['slug'],
            'brand_name' => $brandConfig['name'],
            'code'       => $uniqueCode,
            'is_used'    => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Kode unik ' . $brandConfig['name'] . ' berhasil diklaim!',
            'voucher' => $voucher,
        ], 201);
    }
}
