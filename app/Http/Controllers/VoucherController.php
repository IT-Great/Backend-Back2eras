<?php

// namespace App\Http\Controllers;

// use App\Models\Voucher;
// use Illuminate\Http\Request;
// use Illuminate\Support\Str;

// class VoucherController extends Controller
// {
//     // Kerangka 7 Brand & Rule masing-masing (mudah dikustomisasi per brand nanti)
//     private function getBrandsConfig(): array
//     {
//         return [
//             [
//                 'slug'        => 'back2eras',
//                 'name'        => 'Back2eras',
//                 'prefix'      => 'B2E',
//                 'rule_summary' => 'Berlaku 1x klaim per akun untuk promo resmi BACK2ERAS 2026.',
//             ],
//             [
//                 'slug'        => 'solher',
//                 'name'        => 'Solher',
//                 'prefix'      => 'SLH',
//                 'rule_summary' => 'Berlaku 1x klaim per akun sesuai syarat & ketentuan Solher.',
//             ],
//             [
//                 'slug'        => 'gycora',
//                 'name'        => 'Gycora',
//                 'prefix'      => 'GYC',
//                 'rule_summary' => 'Berlaku 1x klaim per akun sesuai syarat & ketentuan Gycora.',
//             ],
//             [
//                 'slug'        => 'glamoire',
//                 'name'        => 'Glamoire',
//                 'prefix'      => 'GLM',
//                 'rule_summary' => 'Berlaku 1x klaim per akun sesuai syarat & ketentuan Glamoire.',
//             ],
//             [
//                 'slug'        => 'wdp',
//                 'name'        => 'WDP',
//                 'prefix'      => 'WDP',
//                 'rule_summary' => 'Berlaku 1x klaim per akun sesuai syarat & ketentuan WDP.',
//             ],
//             [
//                 'slug'        => 'kecilung',
//                 'name'        => 'Kecilung',
//                 'prefix'      => 'KCL',
//                 'rule_summary' => 'Berlaku 1x klaim per akun sesuai syarat & ketentuan Kecilung.',
//             ],
//             [
//                 'slug'        => 'malaijia',
//                 'name'        => 'Malaijia',
//                 'prefix'      => 'MLJ',
//                 'rule_summary' => 'Berlaku 1x klaim per akun sesuai syarat & ketentuan Malaijia.',
//             ],
//         ];
//     }

//     // PROTECTED: Menampilkan daftar 7 brand + status klaim milik user yang login
//     public function index(Request $request)
//     {
//         $user = $request->user();
//         $claimedMap = Voucher::where('user_id', $user->id)
//             ->get()
//             ->keyBy('brand_slug');

//         $brands = collect($this->getBrandsConfig())->map(function ($brand) use ($claimedMap) {
//             $claimed = $claimedMap->get($brand['slug']);
//             return [
//                 'slug'         => $brand['slug'],
//                 'name'         => $brand['name'],
//                 'rule_summary' => $brand['rule_summary'],
//                 'claimed'      => (bool) $claimed,
//                 'code'         => $claimed?->code,
//                 'is_used'      => (bool) ($claimed?->is_used ?? false),
//                 'claimed_at'   => $claimed?->created_at,
//             ];
//         });

//         return response()->json([
//             'brands' => $brands,
//         ]);
//     }

//     // PROTECTED: Klaim kode unik untuk brand tertentu (1x per user per brand)
//     public function claim(Request $request)
//     {
//         $validated = $request->validate([
//             'brand_slug' => 'required|string',
//         ]);

//         $user = $request->user();
//         $brandConfig = collect($this->getBrandsConfig())->firstWhere('slug', $validated['brand_slug']);

//         if (!$brandConfig) {
//             return response()->json(['message' => 'Brand tidak ditemukan.'], 404);
//         }

//         // Cek apakah user sudah pernah klaim brand ini
//         $existing = Voucher::where('user_id', $user->id)
//             ->where('brand_slug', $brandConfig['slug'])
//             ->first();

//         if ($existing) {
//             return response()->json([
//                 'message' => 'Anda sudah mengklaim voucher untuk ' . $brandConfig['name'] . '.',
//                 'voucher' => $existing,
//             ], 422);
//         }

//         // TODO: Validasi aturan khusus tiap brand bisa ditaruh di sini (misal kuota / syarat urutan user)
//         // switch ($brandConfig['slug']) {
//         //     case 'back2eras': ... break;
//         // }

//         // Generate kode unik yang dijamin berbeda tiap user
//         do {
//             $uniqueCode = sprintf(
//                 '%s-%s-%s',
//                 $brandConfig['prefix'],
//                 str_pad((string) $user->id, 3, '0', STR_PAD_LEFT),
//                 strtoupper(Str::random(5))
//             );
//         } while (Voucher::where('code', $uniqueCode)->exists());

//         $voucher = Voucher::create([
//             'user_id'    => $user->id,
//             'brand_slug' => $brandConfig['slug'],
//             'brand_name' => $brandConfig['name'],
//             'code'       => $uniqueCode,
//             'is_used'    => false,
//         ]);

//         return response()->json([
//             'success' => true,
//             'message' => 'Kode unik ' . $brandConfig['name'] . ' berhasil diklaim!',
//             'voucher' => $voucher,
//         ], 201);
//     }
// }

namespace App\Http\Controllers;

use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VoucherController extends Controller
{
    private const MAX_SEQUENCE = 999;

    /**
     * Konfigurasi lengkap 7 Brand beserta aturan bisnis, masa berlaku, kontak, dan Syarat & Ketentuan.
     */
    private function getBrandsConfig(): array
    {
        return [
            [
                'slug'             => 'back2eras',
                'name'             => 'Back2eras',
                'prefix'           => 'B2E',
                'category'         => 'event',
                'location'         => 'Event BACK2ERAS (27 - 29 November 2026)',
                'phone'            => null,
                'instagram'        => null,
                'validity_label'   => '27 - 29 November 2026 (Selama acara berlangsung)',
                'fixed_expires_at' => '2026-11-29 23:59:59',
                'validity_months'  => null,
                'rule_summary'     => 'Berlaku selama acara BACK2ERAS (27 - 29 November 2026). Tidak dapat diuangkan atau digabungkan dengan promo lain.',
                'terms'            => [
                    'Voucher tidak dapat diuangkan.',
                    'Voucher tidak dapat digabungkan dengan promosi lainnya.',
                    'Voucher hanya bisa digunakan pada acara BACK2ERAS (27 - 29 November 2026).',
                    'Voucher hanya berlaku selama acara berlangsung saja.',
                    'Pihak BACK2ERAS tidak bertanggung jawab atas voucher yang dicuri, hilang, atau rusak.',
                    'Syarat & ketentuan dapat berubah sewaktu-waktu tanpa pemberitahuan sebelumnya.',
                ],
            ],
            [
                'slug'             => 'solher',
                'name'             => 'Solher',
                'prefix'           => 'SLH',
                'category'         => 'event',
                'location'         => 'Event BACK2ERAS (27 - 29 November 2026)',
                'phone'            => null,
                'instagram'        => null,
                'validity_label'   => 'Berlaku selama event BACK2ERAS (27 - 29 November 2026)',
                'fixed_expires_at' => '2026-11-29 23:59:59',
                'validity_months'  => null,
                'rule_summary'     => 'Hanya berlaku valid untuk event BACK2ERAS. Tidak dapat diuangkan atau digabungkan dengan promo lainnya.',
                'terms'            => [
                    'Voucher tidak dapat diuangkan atau digabungkan dengan promo lainnya.',
                    'Voucher hanya berlaku valid untuk event BACK2ERAS.',
                    'Solher tidak bertanggung jawab atas voucher yang dicuri, hilang, atau rusak/robek.',
                ],
            ],
            [
                'slug'             => 'gycora',
                'name'             => 'Gycora',
                'prefix'           => 'GYC',
                'category'         => 'event',
                'location'         => 'Event BACK2ERAS (27 - 29 November 2026)',
                'phone'            => null,
                'instagram'        => null,
                'validity_label'   => 'Berlaku selama event BACK2ERAS (27 - 29 November 2026)',
                'fixed_expires_at' => '2026-11-29 23:59:59',
                'validity_months'  => null,
                'rule_summary'     => 'Hanya berlaku valid untuk event BACK2ERAS. Tidak dapat diuangkan atau digabungkan dengan promo lainnya.',
                'terms'            => [
                    'Voucher tidak dapat diuangkan atau digabungkan dengan promo lainnya.',
                    'Voucher hanya berlaku valid untuk event BACK2ERAS.',
                    'Gycora Essence tidak bertanggung jawab atas voucher yang dicuri, hilang, atau rusak/robek.',
                ],
            ],
            [
                'slug'             => 'glamoire',
                'name'             => 'Glamoire',
                'prefix'           => 'GLM',
                'category'         => 'event',
                'location'         => 'Event BACK2ERAS (27 - 29 November 2026)',
                'phone'            => null,
                'instagram'        => null,
                'validity_label'   => 'Berlaku selama event BACK2ERAS (27 - 29 November 2026)',
                'fixed_expires_at' => '2026-11-29 23:59:59',
                'validity_months'  => null,
                'rule_summary'     => 'Hanya berlaku valid untuk event BACK2ERAS. Tidak dapat diuangkan atau digabungkan dengan promo lainnya.',
                'terms'            => [
                    'Voucher tidak dapat diuangkan atau digabungkan dengan promo lainnya.',
                    'Voucher hanya berlaku valid untuk event BACK2ERAS.',
                    'Glamoire tidak bertanggung jawab atas voucher yang dicuri, hilang, atau rusak/robek.',
                ],
            ],
            [
                'slug'             => 'wdp',
                'name'             => 'WDP',
                'prefix'           => 'WDP',
                'category'         => 'dine_in',
                'location'         => 'Waroeng Depe (Jl. Raya Darmo Permai III)',
                'phone'            => null,
                'instagram'        => null,
                'validity_label'   => 'Berlaku 3 bulan dari tanggal terbit (Dine-in)',
                'fixed_expires_at' => null,
                'validity_months'  => 3,
                'rule_summary'     => 'Khusus dine-in di Waroeng Depe (Jl. Raya Darmo Permai III). Berlaku 3 bulan sejak tanggal terbit, belum termasuk pajak.',
                'terms'            => [
                    'Voucher tidak dapat diuangkan.',
                    'Voucher belum termasuk pajak.',
                    'Voucher hanya dapat digunakan untuk makan di tempat (dine-in).',
                    'Voucher tidak dapat digabungkan dengan promosi lainnya.',
                    'Voucher hanya bisa digunakan di Waroeng Depe (Jl. Raya Darmo Permai III).',
                    'Voucher hanya berlaku selama 3 bulan dari tanggal yang telah diterbitkan.',
                    'Voucher tidak dapat digunakan untuk cemilan, minuman kaleng, dan konsinyasi.',
                    'Waroeng Depe tidak bertanggung jawab atas voucher yang dicuri, hilang, atau rusak.',
                    'Syarat dan ketentuan dapat berubah sewaktu-waktu tanpa pemberitahuan sebelumnya.',
                ],
            ],
            [
                'slug'             => 'kecilung',
                'name'             => 'Kecilung',
                'prefix'           => 'KCL',
                'category'         => 'dine_in',
                'location'         => 'The Kecilung (Jl. Kecilung No. 8)',
                'phone'            => '0818 0311 6688',
                'instagram'        => '@kecilungresto',
                'validity_label'   => 'Berlaku 3 bulan dari tanggal terbit (Dine-in)',
                'fixed_expires_at' => null,
                'validity_months'  => 3,
                'rule_summary'     => 'Khusus dine-in di The Kecilung (Jl. Kecilung No. 8). Berlaku 3 bulan sejak tanggal terbit, belum termasuk pajak.',
                'terms'            => [
                    'Voucher tidak dapat diuangkan.',
                    'Voucher belum termasuk pajak.',
                    'Voucher hanya dapat digunakan untuk makan di tempat (dine-in).',
                    'Voucher tidak dapat digabungkan dengan promosi lainnya.',
                    'Voucher hanya bisa digunakan di The Kecilung (Jl. Kecilung No. 8).',
                    'Voucher hanya berlaku selama 3 bulan dari tanggal yang telah diterbitkan.',
                    'Voucher tidak dapat digunakan untuk cemilan, minuman kaleng, dan konsinyasi.',
                    'Kecilung tidak bertanggung jawab atas voucher yang dicuri, hilang, atau rusak.',
                    'Syarat dan ketentuan dapat berubah sewaktu-waktu tanpa pemberitahuan sebelumnya.',
                ],
            ],
            [
                'slug'             => 'malaijia',
                'name'             => 'Malaijia',
                'prefix'           => 'MLJ',
                'category'         => 'dine_in',
                'location'         => 'Kopitiam Ma Lai Jia (Jl. Kecilung No. 8, Surabaya)',
                'phone'            => '0897-4170-999',
                'instagram'        => '@malaijia.kopitiam',
                'validity_label'   => 'Berlaku 3 bulan dari tanggal terbit (Dine-in)',
                'fixed_expires_at' => null,
                'validity_months'  => 3,
                'rule_summary'     => 'Khusus dine-in di Kopitiam Ma Lai Jia (Jl. Kecilung No. 8, Surabaya). Tidak dapat diuangkan atau digabung promo lain.',
                'terms'            => [
                    'Voucher tidak dapat diuangkan.',
                    'Voucher hanya dapat digunakan untuk makan di tempat (dine-in).',
                    'Voucher tidak dapat digabungkan dengan promosi lainnya.',
                    'Voucher hanya bisa digunakan di Kopitiam Ma Lai Jia (Jl. Kecilung No. 8, Surabaya).',
                    'The Kecilung tidak bertanggung jawab atas voucher yang dicuri, hilang, atau rusak.',
                    'Syarat dan ketentuan dapat berubah sewaktu-waktu tanpa pemberitahuan sebelumnya.',
                ],
            ],
        ];
    }

    /**
     * Hitung tanggal kedaluwarsa berdasarkan aturan masing-masing brand.
     */
    private function resolveExpiresAt(array $brandConfig, Carbon $issuedAt): Carbon
    {
        if (!empty($brandConfig['fixed_expires_at'])) {
            return Carbon::parse($brandConfig['fixed_expires_at'], 'Asia/Jakarta');
        }

        $months = $brandConfig['validity_months'] ?? 3;
        return $issuedAt->copy()->addMonths($months);
    }

    /**
     * Ambil angka urut tertinggi saat ini (001 - 999) untuk prefix brand tertentu.
     */
    private function getHighestSequenceForBrand(string $brandSlug, string $prefix, bool $lock = false): int
    {
        $query = Voucher::where('brand_slug', $brandSlug);
        if ($lock) {
            $query->lockForUpdate();
        }

        $codes = $query->pluck('code');
        $maxSeq = 0;

        foreach ($codes as $code) {
            if (preg_match('/^' . preg_quote($prefix, '/') . '(\d{3})$/', $code, $matches)) {
                $num = (int) $matches[1];
                if ($num > $maxSeq) {
                    $maxSeq = $num;
                }
            }
        }

        return $maxSeq;
    }

    // PROTECTED: Menampilkan daftar 7 brand + status klaim & aturan lengkap
    public function index(Request $request)
    {
        $user = $request->user();
        $now = Carbon::now('Asia/Jakarta');

        $claimedMap = Voucher::where('user_id', $user->id)
            ->get()
            ->keyBy('brand_slug');

        $brands = collect($this->getBrandsConfig())->map(function ($brand) use ($claimedMap, $now) {
            $claimed = $claimedMap->get($brand['slug']);
            $highestSeq = $this->getHighestSequenceForBrand($brand['slug'], $brand['prefix'], false);
            $remainingQuota = max(0, self::MAX_SEQUENCE - $highestSeq);

            $issuedAt = $claimed?->created_at?->copy()->timezone('Asia/Jakarta');
            $expiresAt = null;

            if ($claimed) {
                $expiresAt = $claimed->expires_at
                    ? $claimed->expires_at->copy()->timezone('Asia/Jakarta')
                    : $this->resolveExpiresAt($brand, $issuedAt ?? $now);
            } elseif (!empty($brand['fixed_expires_at'])) {
                $expiresAt = Carbon::parse($brand['fixed_expires_at'], 'Asia/Jakarta');
            }

            $isEventClosed = !empty($brand['fixed_expires_at'])
                && $now->gt(Carbon::parse($brand['fixed_expires_at'], 'Asia/Jakarta'));

            $isExpired = $claimed && $expiresAt ? $now->gt($expiresAt) : $isEventClosed;

            return [
                'slug'            => $brand['slug'],
                'name'            => $brand['name'],
                'prefix'          => $brand['prefix'],
                'category'        => $brand['category'],
                'location'        => $brand['location'],
                'phone'           => $brand['phone'],
                'instagram'       => $brand['instagram'],
                'validity_label'  => $brand['validity_label'],
                'rule_summary'    => $brand['rule_summary'],
                'terms'           => $brand['terms'],
                'max_quota'       => self::MAX_SEQUENCE,
                'remaining_quota' => $remainingQuota,
                'claimed'         => (bool) $claimed,
                'code'            => $claimed?->code,
                'is_used'         => (bool) ($claimed?->is_used ?? false),
                'is_expired'      => $isExpired,
                'claimed_at'      => $issuedAt?->toIso8601String(),
                'expires_at'      => $expiresAt?->toIso8601String(),
            ];
        });

        return response()->json([
            'brands' => $brands,
        ]);
    }

    // PROTECTED: Klaim kode unik format [3 HURUF][001-999] (1x per user per brand)
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

        $now = Carbon::now('Asia/Jakarta');

        // Validasi periode event untuk brand khusus event BACK2ERAS (27 - 29 November 2026)
        if (!empty($brandConfig['fixed_expires_at'])) {
            $eventEnd = Carbon::parse($brandConfig['fixed_expires_at'], 'Asia/Jakarta');
            if ($now->gt($eventEnd)) {
                return response()->json([
                    'message' => 'Periode acara BACK2ERAS (27 - 29 November 2026) telah berakhir. Voucher ' . $brandConfig['name'] . ' sudah tidak dapat diklaim.',
                ], 422);
            }
        }

        $result = DB::transaction(function () use ($user, $brandConfig, $now) {
            // 1. Cek apakah user sudah pernah klaim brand ini (dengan row lock)
            $existing = Voucher::where('user_id', $user->id)
                ->where('brand_slug', $brandConfig['slug'])
                ->lockForUpdate()
                ->first();

            if ($existing) {
                return [
                    'status'  => 'already_claimed',
                    'voucher' => $existing,
                ];
            }

            // 2. Hitung nomor urut berikutnya (001 - 999) secara aman
            $highestSeq = $this->getHighestSequenceForBrand(
                $brandConfig['slug'],
                $brandConfig['prefix'],
                true
            );

            $nextSeq = $highestSeq + 1;

            if ($nextSeq > self::MAX_SEQUENCE) {
                return [
                    'status' => 'quota_exceeded',
                ];
            }

            // 3. Bentuk kode unik: 3 huruf prefix + 3 digit angka (contoh: KCL001, B2E002)
            $uniqueCode = $brandConfig['prefix'] . str_pad((string) $nextSeq, 3, '0', STR_PAD_LEFT);
            $expiresAt = $this->resolveExpiresAt($brandConfig, $now);

            $voucher = Voucher::create([
                'user_id'    => $user->id,
                'brand_slug' => $brandConfig['slug'],
                'brand_name' => $brandConfig['name'],
                'code'       => $uniqueCode,
                'is_used'    => false,
                'expires_at' => $expiresAt,
            ]);

            return [
                'status'  => 'created',
                'voucher' => $voucher,
            ];
        });

        if ($result['status'] === 'already_claimed') {
            return response()->json([
                'message' => 'Anda sudah mengklaim voucher untuk ' . $brandConfig['name'] . '.',
                'voucher' => $result['voucher'],
            ], 422);
        }

        if ($result['status'] === 'quota_exceeded') {
            return response()->json([
                'message' => 'Maaf, kuota voucher ' . $brandConfig['name'] . ' (001 - 999) sudah habis.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Kode unik ' . $brandConfig['name'] . ' berhasil diklaim!',
            'voucher' => $result['voucher'],
        ], 201);
    }

    /**
     * Helper format payload voucher lengkap untuk kebutuhan Admin / Kasir.
     */
    private function formatAdminVoucher(Voucher $voucher, Carbon $now): array
    {
        $brandConfig = collect($this->getBrandsConfig())->firstWhere('slug', $voucher->brand_slug);
        $issuedAt = $voucher->created_at?->copy()->timezone('Asia/Jakarta');
        $expiresAt = $voucher->expires_at
            ? $voucher->expires_at->copy()->timezone('Asia/Jakarta')
            : ($brandConfig ? $this->resolveExpiresAt($brandConfig, $issuedAt ?? $now) : null);

        $usedAt = $voucher->used_at?->copy()->timezone('Asia/Jakarta');
        $isExpired = $expiresAt ? $now->gt($expiresAt) : false;

        $status = 'unused';
        if ($voucher->is_used) {
            $status = 'used';
        } elseif ($isExpired) {
            $status = 'expired';
        }

        return [
            'id'         => $voucher->id,
            'code'       => $voucher->code,
            'brand_slug' => $voucher->brand_slug,
            'brand_name' => $voucher->brand_name,
            'location'   => $brandConfig['location'] ?? '-',
            'terms'      => $brandConfig['terms'] ?? [],
            'is_used'    => (bool) $voucher->is_used,
            'is_expired' => $isExpired,
            'can_redeem' => !$voucher->is_used && !$isExpired,
            'status'     => $status,
            'claimed_at' => $issuedAt?->toIso8601String(),
            'expires_at' => $expiresAt?->toIso8601String(),
            'used_at'    => $usedAt?->toIso8601String(),
            'user'       => [
                'id'       => $voucher->user?->id,
                'name'     => $voucher->user?->name ?? '-',
                'email'    => $voucher->user?->email ?? '-',
                'whatsapp' => $voucher->user?->visitor?->whatsapp ?? '-',
            ],
        ];
    }

    // ADMIN: Menampilkan seluruh daftar voucher beserta ringkasan statistik
    public function adminIndex(Request $request)
    {
        $now = Carbon::now('Asia/Jakarta');

        $vouchers = Voucher::with(['user.visitor'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn (Voucher $v) => $this->formatAdminVoucher($v, $now));

        $stats = [
            'total'   => $vouchers->count(),
            'unused'  => $vouchers->where('status', 'unused')->count(),
            'used'    => $vouchers->where('status', 'used')->count(),
            'expired' => $vouchers->where('status', 'expired')->count(),
        ];

        return response()->json([
            'stats'    => $stats,
            'vouchers' => $vouchers->values(),
        ]);
    }

    // ADMIN: Cek / Verifikasi kode unik (misal: KCL001) sebelum di-redeem
    public function adminVerify(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50',
        ]);

        $code = strtoupper(trim($validated['code']));
        $now = Carbon::now('Asia/Jakarta');

        $voucher = Voucher::with(['user.visitor'])
            ->where('code', $code)
            ->first();

        if (!$voucher) {
            return response()->json([
                'message' => "Kode voucher \"{$code}\" tidak ditemukan di sistem.",
            ], 404);
        }

        return response()->json([
            'voucher' => $this->formatAdminVoucher($voucher, $now),
        ]);
    }

    // ADMIN: Tandai voucher sebagai sudah dipakai (is_used = true, used_at = now)
    public function adminRedeem(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50',
        ]);

        $code = strtoupper(trim($validated['code']));
        $now = Carbon::now('Asia/Jakarta');

        $result = DB::transaction(function () use ($code, $now) {
            $voucher = Voucher::with(['user.visitor'])
                ->where('code', $code)
                ->lockForUpdate()
                ->first();

            if (!$voucher) {
                return ['state' => 'not_found'];
            }

            if ($voucher->is_used) {
                return [
                    'state'   => 'already_used',
                    'voucher' => $voucher,
                ];
            }

            $brandConfig = collect($this->getBrandsConfig())->firstWhere('slug', $voucher->brand_slug);
            $issuedAt = $voucher->created_at?->copy()->timezone('Asia/Jakarta');
            $expiresAt = $voucher->expires_at
                ? $voucher->expires_at->copy()->timezone('Asia/Jakarta')
                : ($brandConfig ? $this->resolveExpiresAt($brandConfig, $issuedAt ?? $now) : null);

            if ($expiresAt && $now->gt($expiresAt)) {
                return [
                    'state'   => 'expired',
                    'voucher' => $voucher,
                ];
            }

            $voucher->is_used = true;
            $voucher->used_at = $now;
            $voucher->save();

            return [
                'state'   => 'redeemed',
                'voucher' => $voucher,
            ];
        });

        if ($result['state'] === 'not_found') {
            return response()->json([
                'message' => "Kode voucher \"{$code}\" tidak ditemukan.",
            ], 404);
        }

        if ($result['state'] === 'already_used') {
            $usedTime = $result['voucher']->used_at?->copy()->timezone('Asia/Jakarta')->format('d M Y H:i') . ' WIB';
            return response()->json([
                'message' => "Voucher {$code} sudah pernah digunakan pada {$usedTime}.",
                'voucher' => $this->formatAdminVoucher($result['voucher'], $now),
            ], 422);
        }

        if ($result['state'] === 'expired') {
            return response()->json([
                'message' => "Voucher {$code} sudah kedaluwarsa dan tidak dapat digunakan.",
                'voucher' => $this->formatAdminVoucher($result['voucher'], $now),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => "Voucher {$code} berhasil diverifikasi & ditandai sudah dipakai!",
            'voucher' => $this->formatAdminVoucher($result['voucher'], $now),
        ]);
    }
}
