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

// namespace App\Http\Controllers;

// use App\Models\Kol;
// use App\Models\Voucher;
// use Carbon\Carbon;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\DB;
// use App\Models\TalkshowRegistration;

// class VoucherController extends Controller
// {
//     private const MAX_SEQUENCE = 999;

//     /**
//      * Konfigurasi lengkap 7 Brand beserta aturan bisnis, masa berlaku, kontak, dan Syarat & Ketentuan.
//      */
//     private function getBrandsConfig(): array
//     {
//         return [
//             [
//                 'slug'             => 'back2eras',
//                 'name'             => 'Back2eras',
//                 'prefix'           => 'B2E',
//                 'category'         => 'event',
//                 'location'         => 'Event BACK2ERAS (27 - 29 November 2026)',
//                 'phone'            => null,
//                 'instagram'        => null,
//                 'validity_label'   => '27 - 29 November 2026 (Selama acara berlangsung)',
//                 'fixed_expires_at' => '2026-11-29 23:59:59',
//                 'validity_months'  => null,
//                 'rule_summary'     => 'Berlaku selama acara BACK2ERAS (27 - 29 November 2026). Tidak dapat diuangkan atau digabungkan dengan promo lain.',
//                 'terms'            => [
//                     'Voucher tidak dapat diuangkan.',
//                     'Voucher tidak dapat digabungkan dengan promosi lainnya.',
//                     'Voucher hanya bisa digunakan pada acara BACK2ERAS (27 - 29 November 2026).',
//                     'Voucher hanya berlaku selama acara berlangsung saja.',
//                     'Pihak BACK2ERAS tidak bertanggung jawab atas voucher yang dicuri, hilang, atau rusak.',
//                     'Syarat & ketentuan dapat berubah sewaktu-waktu tanpa pemberitahuan sebelumnya.',
//                 ],
//             ],
//             [
//                 'slug'             => 'solher',
//                 'name'             => 'Solher',
//                 'prefix'           => 'SLH',
//                 'category'         => 'event',
//                 'location'         => 'Event BACK2ERAS (27 - 29 November 2026)',
//                 'phone'            => null,
//                 'instagram'        => null,
//                 'validity_label'   => 'Berlaku selama event BACK2ERAS (27 - 29 November 2026)',
//                 'fixed_expires_at' => '2026-11-29 23:59:59',
//                 'validity_months'  => null,
//                 'rule_summary'     => 'Hanya berlaku valid untuk event BACK2ERAS. Tidak dapat diuangkan atau digabungkan dengan promo lainnya.',
//                 'terms'            => [
//                     'Voucher tidak dapat diuangkan atau digabungkan dengan promo lainnya.',
//                     'Voucher hanya berlaku valid untuk event BACK2ERAS.',
//                     'Solher tidak bertanggung jawab atas voucher yang dicuri, hilang, atau rusak/robek.',
//                 ],
//             ],
//             [
//                 'slug'             => 'gycora',
//                 'name'             => 'Gycora',
//                 'prefix'           => 'GYC',
//                 'category'         => 'event',
//                 'location'         => 'Event BACK2ERAS (27 - 29 November 2026)',
//                 'phone'            => null,
//                 'instagram'        => null,
//                 'validity_label'   => 'Berlaku selama event BACK2ERAS (27 - 29 November 2026)',
//                 'fixed_expires_at' => '2026-11-29 23:59:59',
//                 'validity_months'  => null,
//                 'rule_summary'     => 'Hanya berlaku valid untuk event BACK2ERAS. Tidak dapat diuangkan atau digabungkan dengan promo lainnya.',
//                 'terms'            => [
//                     'Voucher tidak dapat diuangkan atau digabungkan dengan promo lainnya.',
//                     'Voucher hanya berlaku valid untuk event BACK2ERAS.',
//                     'Gycora Essence tidak bertanggung jawab atas voucher yang dicuri, hilang, atau rusak/robek.',
//                 ],
//             ],
//             [
//                 'slug'             => 'glamoire',
//                 'name'             => 'Glamoire',
//                 'prefix'           => 'GLM',
//                 'category'         => 'event',
//                 'location'         => 'Event BACK2ERAS (27 - 29 November 2026)',
//                 'phone'            => null,
//                 'instagram'        => null,
//                 'validity_label'   => 'Berlaku selama event BACK2ERAS (27 - 29 November 2026)',
//                 'fixed_expires_at' => '2026-11-29 23:59:59',
//                 'validity_months'  => null,
//                 'rule_summary'     => 'Hanya berlaku valid untuk event BACK2ERAS. Tidak dapat diuangkan atau digabungkan dengan promo lainnya.',
//                 'terms'            => [
//                     'Voucher tidak dapat diuangkan atau digabungkan dengan promo lainnya.',
//                     'Voucher hanya berlaku valid untuk event BACK2ERAS.',
//                     'Glamoire tidak bertanggung jawab atas voucher yang dicuri, hilang, atau rusak/robek.',
//                 ],
//             ],
//             [
//                 'slug'             => 'wdp',
//                 'name'             => 'WDP',
//                 'prefix'           => 'WDP',
//                 'category'         => 'dine_in',
//                 'location'         => 'Waroeng Depe (Jl. Raya Darmo Permai III)',
//                 'phone'            => null,
//                 'instagram'        => null,
//                 'validity_label'   => 'Berlaku 3 bulan dari tanggal terbit (Dine-in)',
//                 'fixed_expires_at' => null,
//                 'validity_months'  => 3,
//                 'rule_summary'     => 'Khusus dine-in di Waroeng Depe (Jl. Raya Darmo Permai III). Berlaku 3 bulan sejak tanggal terbit, belum termasuk pajak.',
//                 'terms'            => [
//                     'Voucher tidak dapat diuangkan.',
//                     'Voucher belum termasuk pajak.',
//                     'Voucher hanya dapat digunakan untuk makan di tempat (dine-in).',
//                     'Voucher tidak dapat digabungkan dengan promosi lainnya.',
//                     'Voucher hanya bisa digunakan di Waroeng Depe (Jl. Raya Darmo Permai III).',
//                     'Voucher hanya berlaku selama 3 bulan dari tanggal yang telah diterbitkan.',
//                     'Voucher tidak dapat digunakan untuk cemilan, minuman kaleng, dan konsinyasi.',
//                     'Waroeng Depe tidak bertanggung jawab atas voucher yang dicuri, hilang, atau rusak.',
//                     'Syarat dan ketentuan dapat berubah sewaktu-waktu tanpa pemberitahuan sebelumnya.',
//                 ],
//             ],
//             [
//                 'slug'             => 'kecilung',
//                 'name'             => 'Kecilung',
//                 'prefix'           => 'KCL',
//                 'category'         => 'dine_in',
//                 'location'         => 'The Kecilung (Jl. Kecilung No. 8)',
//                 'phone'            => '0818 0311 6688',
//                 'instagram'        => '@kecilungresto',
//                 'validity_label'   => 'Berlaku 3 bulan dari tanggal terbit (Dine-in)',
//                 'fixed_expires_at' => null,
//                 'validity_months'  => 3,
//                 'rule_summary'     => 'Khusus dine-in di The Kecilung (Jl. Kecilung No. 8). Berlaku 3 bulan sejak tanggal terbit, belum termasuk pajak.',
//                 'terms'            => [
//                     'Voucher tidak dapat diuangkan.',
//                     'Voucher belum termasuk pajak.',
//                     'Voucher hanya dapat digunakan untuk makan di tempat (dine-in).',
//                     'Voucher tidak dapat digabungkan dengan promosi lainnya.',
//                     'Voucher hanya bisa digunakan di The Kecilung (Jl. Kecilung No. 8).',
//                     'Voucher hanya berlaku selama 3 bulan dari tanggal yang telah diterbitkan.',
//                     'Voucher tidak dapat digunakan untuk cemilan, minuman kaleng, dan konsinyasi.',
//                     'Kecilung tidak bertanggung jawab atas voucher yang dicuri, hilang, atau rusak.',
//                     'Syarat dan ketentuan dapat berubah sewaktu-waktu tanpa pemberitahuan sebelumnya.',
//                 ],
//             ],
//             [
//                 'slug'             => 'malaijia',
//                 'name'             => 'Malaijia',
//                 'prefix'           => 'MLJ',
//                 'category'         => 'dine_in',
//                 'location'         => 'Kopitiam Ma Lai Jia (Jl. Kecilung No. 8, Surabaya)',
//                 'phone'            => '0897-4170-999',
//                 'instagram'        => '@malaijia.kopitiam',
//                 'validity_label'   => 'Berlaku 3 bulan dari tanggal terbit (Dine-in)',
//                 'fixed_expires_at' => null,
//                 'validity_months'  => 3,
//                 'rule_summary'     => 'Khusus dine-in di Kopitiam Ma Lai Jia (Jl. Kecilung No. 8, Surabaya). Tidak dapat diuangkan atau digabung promo lain.',
//                 'terms'            => [
//                     'Voucher tidak dapat diuangkan.',
//                     'Voucher hanya dapat digunakan untuk makan di tempat (dine-in).',
//                     'Voucher tidak dapat digabungkan dengan promosi lainnya.',
//                     'Voucher hanya bisa digunakan di Kopitiam Ma Lai Jia (Jl. Kecilung No. 8, Surabaya).',
//                     'The Kecilung tidak bertanggung jawab atas voucher yang dicuri, hilang, atau rusak.',
//                     'Syarat dan ketentuan dapat berubah sewaktu-waktu tanpa pemberitahuan sebelumnya.',
//                 ],
//             ],
//         ];
//     }

//         /**
//      * Konfigurasi Voucher Free Goodie Bag khusus 5 pendaftar pertama Talkshow yang di-approve.
//      */
//     private function getTalkshowGoodieBagConfig(): array
//     {
//         return [
//             'slug'             => 'talkshow-goodiebag',
//             'name'             => 'Free Goodie Bag (Talkshow Top 5)',
//             'prefix'           => 'GBTS',
//             'start_sequence'   => 1,
//             'pad_length'       => 3,
//             'max_quota'        => self::DEFAULT_MAX_QUOTA,
//             'category'         => 'event',
//             'location'         => 'Booth Registrasi BACK2ERAS (27 - 29 November 2026)',
//             'phone'            => null,
//             'instagram'        => null,
//             'validity_label'   => 'Khusus 5 Pendaftar Pertama Talkshow (Approved)',
//             'fixed_expires_at' => '2026-11-29 23:59:59',
//             'validity_months'  => null,
//             'rule_summary'     => 'Voucher eksklusif Free Goodie Bag khusus 5 peserta pertama Talkshow yang telah disetujui admin.',
//             'terms'            => [
//                 'Khusus untuk 5 pendaftar pertama Talkshow yang status pendaftarannya telah disetujui (Approved) oleh admin.',
//                 'Tunjukkan kode unik ini saat check-in di lokasi acara BACK2ERAS (27 - 29 November 2026).',
//                 'Berlaku 1x klaim per akun dan tidak dapat diuangkan atau dipindahtangankan.',
//                 'Pihak BACK2ERAS tidak bertanggung jawab atas voucher yang hilang atau disalahgunakan.',
//             ],
//         ];
//     }

//     private function getAllBrandsConfig(): array
//     {
//         return array_merge([$this->getTalkshowGoodieBagConfig()], $this->getBrandsConfig());
//     }

//     /**
//      * Hitung tanggal kedaluwarsa berdasarkan aturan masing-masing brand.
//      */
//     private function resolveExpiresAt(array $brandConfig, Carbon $issuedAt): Carbon
//     {
//         if (!empty($brandConfig['fixed_expires_at'])) {
//             return Carbon::parse($brandConfig['fixed_expires_at'], 'Asia/Jakarta');
//         }

//         $months = $brandConfig['validity_months'] ?? 3;
//         return $issuedAt->copy()->addMonths($months);
//     }

//     /**
//      * Ambil angka urut tertinggi saat ini (001 - 999) untuk prefix brand tertentu.
//      */
//     private function getHighestSequenceForBrand(string $brandSlug, string $prefix, bool $lock = false): int
//     {
//         $query = Voucher::where('brand_slug', $brandSlug);
//         if ($lock) {
//             $query->lockForUpdate();
//         }

//         $codes = $query->pluck('code');
//         $maxSeq = 0;

//         foreach ($codes as $code) {
//             if (preg_match('/^' . preg_quote($prefix, '/') . '(\d{3})$/', $code, $matches)) {
//                 $num = (int) $matches[1];
//                 if ($num > $maxSeq) {
//                     $maxSeq = $num;
//                 }
//             }
//         }

//         return $maxSeq;
//     }

//     // PROTECTED: Menampilkan daftar 7 brand + status klaim & aturan lengkap
//     public function index(Request $request)
//     {
//         $user = $request->user();
//         $now = Carbon::now('Asia/Jakarta');

//         $claimedMap = Voucher::where('user_id', $user->id)
//             ->get()
//             ->keyBy('brand_slug');

//         $eligibility = $this->checkVoucherEligibility($user);

//         $brands = collect($this->getBrandsConfig())->map(function ($brand) use ($claimedMap, $now) {
//             $claimed = $claimedMap->get($brand['slug']);
//             $highestSeq = $this->getHighestSequenceForBrand($brand['slug'], $brand['prefix'], false);
//             $remainingQuota = max(0, self::MAX_SEQUENCE - $highestSeq);

//             $issuedAt = $claimed?->created_at?->copy()->timezone('Asia/Jakarta');
//             $expiresAt = null;

//             if ($claimed) {
//                 $expiresAt = $claimed->expires_at
//                     ? $claimed->expires_at->copy()->timezone('Asia/Jakarta')
//                     : $this->resolveExpiresAt($brand, $issuedAt ?? $now);
//             } elseif (!empty($brand['fixed_expires_at'])) {
//                 $expiresAt = Carbon::parse($brand['fixed_expires_at'], 'Asia/Jakarta');
//             }

//             $isEventClosed = !empty($brand['fixed_expires_at'])
//                 && $now->gt(Carbon::parse($brand['fixed_expires_at'], 'Asia/Jakarta'));

//             $isExpired = $claimed && $expiresAt ? $now->gt($expiresAt) : $isEventClosed;

//             return [
//                 'slug'            => $brand['slug'],
//                 'name'            => $brand['name'],
//                 'prefix'          => $brand['prefix'],
//                 'category'        => $brand['category'],
//                 'location'        => $brand['location'],
//                 'phone'           => $brand['phone'],
//                 'instagram'       => $brand['instagram'],
//                 'validity_label'  => $brand['validity_label'],
//                 'rule_summary'    => $brand['rule_summary'],
//                 'terms'           => $brand['terms'],
//                 'max_quota'       => self::MAX_SEQUENCE,
//                 'remaining_quota' => $remainingQuota,
//                 'claimed'         => (bool) $claimed,
//                 'code'            => $claimed?->code,
//                 'is_used'         => (bool) ($claimed?->is_used ?? false),
//                 'is_expired'      => $isExpired,
//                 'claimed_at'      => $issuedAt?->toIso8601String(),
//                 'expires_at'      => $expiresAt?->toIso8601String(),
//             ];
//         });

//         // return response()->json([
//         //     'brands' => $brands,
//         // ]);

//         $eligibility = $this->checkVoucherEligibility($user);

//         return response()->json([
//             'can_claim_voucher'  => $eligibility['eligible'],
//             'claim_block_reason' => $eligibility['reason'],
//             'brands'             => $brands,
//         ]);
//     }

//     // PROTECTED: Klaim kode unik format [3 HURUF][001-999] (1x per user per brand)
//     public function claim(Request $request)
//     {
//         $validated = $request->validate([
//             'brand_slug' => 'required|string',
//         ]);

//         $user = $request->user();

//         // Validasi khusus KOL: wajib mengisi link media sosial
//         $eligibility = $this->checkVoucherEligibility($user);
//         if (!$eligibility['eligible']) {
//             return response()->json([
//                 'message' => $eligibility['reason'],
//             ], 403);
//         }

//         $brandConfig = collect($this->getBrandsConfig())->firstWhere('slug', $validated['brand_slug']);

//         if (!$brandConfig) {
//             return response()->json(['message' => 'Brand tidak ditemukan.'], 404);
//         }

//         $now = Carbon::now('Asia/Jakarta');

//         // Validasi periode event untuk brand khusus event BACK2ERAS (27 - 29 November 2026)
//         if (!empty($brandConfig['fixed_expires_at'])) {
//             $eventEnd = Carbon::parse($brandConfig['fixed_expires_at'], 'Asia/Jakarta');
//             if ($now->gt($eventEnd)) {
//                 return response()->json([
//                     'message' => 'Periode acara BACK2ERAS (27 - 29 November 2026) telah berakhir. Voucher ' . $brandConfig['name'] . ' sudah tidak dapat diklaim.',
//                 ], 422);
//             }
//         }

//         $result = DB::transaction(function () use ($user, $brandConfig, $now) {
//             // 1. Cek apakah user sudah pernah klaim brand ini (dengan row lock)
//             $existing = Voucher::where('user_id', $user->id)
//                 ->where('brand_slug', $brandConfig['slug'])
//                 ->lockForUpdate()
//                 ->first();

//             if ($existing) {
//                 return [
//                     'status'  => 'already_claimed',
//                     'voucher' => $existing,
//                 ];
//             }

//             // 2. Hitung nomor urut berikutnya (001 - 999) secara aman
//             $highestSeq = $this->getHighestSequenceForBrand(
//                 $brandConfig['slug'],
//                 $brandConfig['prefix'],
//                 true
//             );

//             $nextSeq = $highestSeq + 1;

//             if ($nextSeq > self::MAX_SEQUENCE) {
//                 return [
//                     'status' => 'quota_exceeded',
//                 ];
//             }

//             // 3. Bentuk kode unik: 3 huruf prefix + 3 digit angka (contoh: KCL001, B2E002)
//             $uniqueCode = $brandConfig['prefix'] . str_pad((string) $nextSeq, 3, '0', STR_PAD_LEFT);
//             $expiresAt = $this->resolveExpiresAt($brandConfig, $now);

//             $voucher = Voucher::create([
//                 'user_id'    => $user->id,
//                 'brand_slug' => $brandConfig['slug'],
//                 'brand_name' => $brandConfig['name'],
//                 'code'       => $uniqueCode,
//                 'is_used'    => false,
//                 'expires_at' => $expiresAt,
//             ]);

//             return [
//                 'status'  => 'created',
//                 'voucher' => $voucher,
//             ];
//         });

//         if ($result['status'] === 'already_claimed') {
//             return response()->json([
//                 'message' => 'Anda sudah mengklaim voucher untuk ' . $brandConfig['name'] . '.',
//                 'voucher' => $result['voucher'],
//             ], 422);
//         }

//         if ($result['status'] === 'quota_exceeded') {
//             return response()->json([
//                 'message' => 'Maaf, kuota voucher ' . $brandConfig['name'] . ' (001 - 999) sudah habis.',
//             ], 422);
//         }

//         return response()->json([
//             'success' => true,
//             'message' => 'Kode unik ' . $brandConfig['name'] . ' berhasil diklaim!',
//             'voucher' => $result['voucher'],
//         ], 201);
//     }

//     /**
//      * Helper format payload voucher lengkap untuk kebutuhan Admin / Kasir.
//      */
//     private function formatAdminVoucher(Voucher $voucher, Carbon $now): array
//     {
//         $brandConfig = collect($this->getBrandsConfig())->firstWhere('slug', $voucher->brand_slug);
//         $issuedAt = $voucher->created_at?->copy()->timezone('Asia/Jakarta');
//         $expiresAt = $voucher->expires_at
//             ? $voucher->expires_at->copy()->timezone('Asia/Jakarta')
//             : ($brandConfig ? $this->resolveExpiresAt($brandConfig, $issuedAt ?? $now) : null);

//         $usedAt = $voucher->used_at?->copy()->timezone('Asia/Jakarta');
//         $isExpired = $expiresAt ? $now->gt($expiresAt) : false;

//         $status = 'unused';
//         if ($voucher->is_used) {
//             $status = 'used';
//         } elseif ($isExpired) {
//             $status = 'expired';
//         }

//         return [
//             'id'         => $voucher->id,
//             'code'       => $voucher->code,
//             'brand_slug' => $voucher->brand_slug,
//             'brand_name' => $voucher->brand_name,
//             'location'   => $brandConfig['location'] ?? '-',
//             'terms'      => $brandConfig['terms'] ?? [],
//             'is_used'    => (bool) $voucher->is_used,
//             'is_expired' => $isExpired,
//             'can_redeem' => !$voucher->is_used && !$isExpired,
//             'status'     => $status,
//             'claimed_at' => $issuedAt?->toIso8601String(),
//             'expires_at' => $expiresAt?->toIso8601String(),
//             'used_at'    => $usedAt?->toIso8601String(),
//             'user'       => [
//                 'id'       => $voucher->user?->id,
//                 'name'     => $voucher->user?->name ?? '-',
//                 'email'    => $voucher->user?->email ?? '-',
//                 // 'whatsapp' => $voucher->user?->visitor?->whatsapp ?? '-',
//                 'whatsapp' => $voucher->user?->visitor?->whatsapp
//                     ?? $voucher->user?->kol?->whatsapp
//                     ?? '-',
//             ],
//         ];
//     }

//     // ADMIN: Menampilkan seluruh daftar voucher beserta ringkasan statistik
//     public function adminIndex(Request $request)
//     {
//         $now = Carbon::now('Asia/Jakarta');

//         $vouchers = Voucher::with(['user.visitor', 'user.kol'])
//             ->orderBy('created_at', 'desc')
//             ->get()
//             ->map(fn (Voucher $v) => $this->formatAdminVoucher($v, $now));

//         $stats = [
//             'total'   => $vouchers->count(),
//             'unused'  => $vouchers->where('status', 'unused')->count(),
//             'used'    => $vouchers->where('status', 'used')->count(),
//             'expired' => $vouchers->where('status', 'expired')->count(),
//         ];

//         return response()->json([
//             'stats'    => $stats,
//             'vouchers' => $vouchers->values(),
//         ]);
//     }

//     // ADMIN: Cek / Verifikasi kode unik (misal: KCL001) sebelum di-redeem
//     public function adminVerify(Request $request)
//     {
//         $validated = $request->validate([
//             'code' => 'required|string|max:50',
//         ]);

//         $code = strtoupper(trim($validated['code']));
//         $now = Carbon::now('Asia/Jakarta');

//         $voucher = Voucher::with(['user.visitor', 'user.kol'])
//             ->where('code', $code)
//             ->first();

//         if (!$voucher) {
//             return response()->json([
//                 'message' => "Kode voucher \"{$code}\" tidak ditemukan di sistem.",
//             ], 404);
//         }

//         return response()->json([
//             'voucher' => $this->formatAdminVoucher($voucher, $now),
//         ]);
//     }

//     // ADMIN: Tandai voucher sebagai sudah dipakai (is_used = true, used_at = now)
//     public function adminRedeem(Request $request)
//     {
//         $validated = $request->validate([
//             'code' => 'required|string|max:50',
//         ]);

//         $code = strtoupper(trim($validated['code']));
//         $now = Carbon::now('Asia/Jakarta');

//         $result = DB::transaction(function () use ($code, $now) {
//             $voucher = Voucher::with(['user.visitor', 'user.kol'])
//                 ->where('code', $code)
//                 ->lockForUpdate()
//                 ->first();

//             if (!$voucher) {
//                 return ['state' => 'not_found'];
//             }

//             if ($voucher->is_used) {
//                 return [
//                     'state'   => 'already_used',
//                     'voucher' => $voucher,
//                 ];
//             }

//             $brandConfig = collect($this->getBrandsConfig())->firstWhere('slug', $voucher->brand_slug);
//             $issuedAt = $voucher->created_at?->copy()->timezone('Asia/Jakarta');
//             $expiresAt = $voucher->expires_at
//                 ? $voucher->expires_at->copy()->timezone('Asia/Jakarta')
//                 : ($brandConfig ? $this->resolveExpiresAt($brandConfig, $issuedAt ?? $now) : null);

//             if ($expiresAt && $now->gt($expiresAt)) {
//                 return [
//                     'state'   => 'expired',
//                     'voucher' => $voucher,
//                 ];
//             }

//             $voucher->is_used = true;
//             $voucher->used_at = $now;
//             $voucher->save();

//             return [
//                 'state'   => 'redeemed',
//                 'voucher' => $voucher,
//             ];
//         });

//         if ($result['state'] === 'not_found') {
//             return response()->json([
//                 'message' => "Kode voucher \"{$code}\" tidak ditemukan.",
//             ], 404);
//         }

//         if ($result['state'] === 'already_used') {
//             $usedTime = $result['voucher']->used_at?->copy()->timezone('Asia/Jakarta')->format('d M Y H:i') . ' WIB';
//             return response()->json([
//                 'message' => "Voucher {$code} sudah pernah digunakan pada {$usedTime}.",
//                 'voucher' => $this->formatAdminVoucher($result['voucher'], $now),
//             ], 422);
//         }

//         if ($result['state'] === 'expired') {
//             return response()->json([
//                 'message' => "Voucher {$code} sudah kedaluwarsa dan tidak dapat digunakan.",
//                 'voucher' => $this->formatAdminVoucher($result['voucher'], $now),
//             ], 422);
//         }

//         return response()->json([
//             'success' => true,
//             'message' => "Voucher {$code} berhasil diverifikasi & ditandai sudah dipakai!",
//             'voucher' => $this->formatAdminVoucher($result['voucher'], $now),
//         ]);
//     }

//     /**
//      * Cek apakah user yang login berhak mengklaim voucher.
//      * Aturan KOL: wajib mengisi `social_media_link`.
//      */
//     // private function checkVoucherEligibility($user): array
//     // {
//     //     if ($user->role === 'kol') {
//     //         $kol = Kol::where('user_id', $user->id)
//     //             ->orWhere('email', $user->email)
//     //             ->first();

//     //         $hasSocialLink = !empty(trim((string) ($kol?->social_media_link ?? '')));

//     //         if (!$hasSocialLink) {
//     //             return [
//     //                 'eligible' => false,
//     //                 'reason'   => 'Akun KOL wajib mengisi Link Media Sosial terlebih dahulu agar dapat mengklaim voucher.',
//     //             ];
//     //         }
//     //     }

//     //     return [
//     //         'eligible' => true,
//     //         'reason'   => null,
//     //     ];
//     // }

//     /**
//      * Cek kelayakan voucher untuk Visitor, KOL, maupun Peserta Talkshow.
//      */
//     private function checkVoucherEligibility($user): array
//     {
//         // Cek pendaftaran talkshow milik user (baik lewat user_id maupun email)
//         $talkshowRegs = TalkshowRegistration::where(function ($q) use ($user) {
//             $q->where('user_id', $user->id)
//               ->orWhere('email', $user->email);
//         })->get();

//         $approvedRanks = $talkshowRegs
//             ->where('status', 'approved')
//             ->pluck('approved_rank')
//             ->filter(fn ($rank) => $rank !== null)
//             ->values();

//         $bestApprovedRank = $approvedRanks->isNotEmpty() ? (int) $approvedRanks->min() : null;
//         $includeGoodieBag = $bestApprovedRank !== null && $bestApprovedRank <= 5;

//         // 1. Aturan khusus akun role = 'talkshow'
//         if ($user->role === 'talkshow') {
//             if ($bestApprovedRank === null) {
//                 $hasPending = $talkshowRegs->where('status', 'pending')->isNotEmpty();

//                 return [
//                     'eligible'          => false,
//                     'reason'            => $hasPending
//                         ? 'Pendaftaran Talkshow Anda masih menunggu persetujuan (Approve) dari admin. Voucher baru dapat diklaim setelah pendaftaran Anda diterima.'
//                         : 'Pendaftaran Talkshow Anda belum disetujui atau ditolak oleh admin sehingga tidak dapat mengklaim voucher.',
//                     'allowed_slugs'     => [],
//                     'include_goodiebag' => false,
//                     'talkshow_rank'     => null,
//                 ];
//             }

//             // Urutan 1 - 5: Free Goodie Bag + Semua 7 voucher visitor (termasuk Back2eras)
//             if ($bestApprovedRank <= 5) {
//                 return [
//                     'eligible'          => true,
//                     'reason'            => null,
//                     'allowed_slugs'     => null, // semua 7 brand
//                     'include_goodiebag' => true,
//                     'talkshow_rank'     => $bestApprovedRank,
//                 ];
//             }

//             // Urutan 6 - 25: Semua voucher visitor KECUALI Back2eras (dan tanpa Goodie Bag)
//             return [
//                 'eligible'          => true,
//                 'reason'            => null,
//                 'allowed_slugs'     => ['solher', 'gycora', 'glamoire', 'wdp', 'kecilung', 'malaijia'],
//                 'include_goodiebag' => false,
//                 'talkshow_rank'     => $bestApprovedRank,
//             ];
//         }

//         // 2. Aturan khusus akun role = 'kol'
//         if ($user->role === 'kol') {
//             $kol = Kol::where('user_id', $user->id)
//                 ->orWhere('email', $user->email)
//                 ->first();

//             $hasSocialLink = !empty(trim((string) ($kol?->social_media_link ?? '')));

//             if (!$hasSocialLink) {
//                 return [
//                     'eligible'          => false,
//                     'reason'            => 'Akun KOL wajib mengisi Link Media Sosial terlebih dahulu agar dapat mengklaim voucher.',
//                     'allowed_slugs'     => null,
//                     'include_goodiebag' => $includeGoodieBag,
//                     'talkshow_rank'     => $bestApprovedRank,
//                 ];
//             }
//         }

//         // 3. Default: Visitor (plus bonus Goodie Bag jika juga approved 1-5 di Talkshow)
//         return [
//             'eligible'          => true,
//             'reason'            => null,
//             'allowed_slugs'     => null,
//             'include_goodiebag' => $includeGoodieBag,
//             'talkshow_rank'     => $bestApprovedRank,
//         ];
//     }
// }

// namespace App\Http\Controllers;

// use App\Models\Kol;
// use App\Models\TalkshowRegistration;
// use App\Models\Voucher;
// use Carbon\Carbon;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\DB;

// class VoucherController extends Controller
// {
//     private const DEFAULT_MAX_QUOTA = 999;

//     /**
//      * Konfigurasi lengkap 7 Brand (format 3 huruf + 001-999),
//      * aturan bisnis, masa berlaku, kontak, dan Syarat & Ketentuan.
//      */
//     private function getBrandsConfig(): array
//     {
//         return [
//             [
//                 'slug'             => 'back2eras',
//                 'name'             => 'Back2eras',
//                 'prefix'           => 'B2E',
//                 'start_sequence'   => 1,
//                 'pad_length'       => 3,
//                 'max_quota'        => self::DEFAULT_MAX_QUOTA,
//                 'category'         => 'event',
//                 'location'         => 'Event BACK2ERAS (27 - 29 November 2026)',
//                 'phone'            => null,
//                 'instagram'        => null,
//                 'validity_label'   => '27 - 29 November 2026 (Selama acara berlangsung)',
//                 'fixed_expires_at' => '2026-11-29 23:59:59',
//                 'validity_months'  => null,
//                 'rule_summary'     => 'Berlaku selama acara BACK2ERAS (27 - 29 November 2026). Tidak dapat diuangkan atau digabungkan dengan promo lain.',
//                 'terms'            => [
//                     'Voucher tidak dapat diuangkan.',
//                     'Voucher tidak dapat digabungkan dengan promosi lainnya.',
//                     'Voucher hanya bisa digunakan pada acara BACK2ERAS (27 - 29 November 2026).',
//                     'Voucher hanya berlaku selama acara berlangsung saja.',
//                     'Pihak BACK2ERAS tidak bertanggung jawab atas voucher yang dicuri, hilang, atau rusak.',
//                     'Syarat & ketentuan dapat berubah sewaktu-waktu tanpa pemberitahuan sebelumnya.',
//                 ],
//             ],
//             [
//                 'slug'             => 'solher',
//                 'name'             => 'Solher',
//                 'prefix'           => 'SLH',
//                 'start_sequence'   => 1,
//                 'pad_length'       => 3,
//                 'max_quota'        => self::DEFAULT_MAX_QUOTA,
//                 'category'         => 'event',
//                 'location'         => 'Event BACK2ERAS (27 - 29 November 2026)',
//                 'phone'            => null,
//                 'instagram'        => null,
//                 'validity_label'   => 'Berlaku selama event BACK2ERAS (27 - 29 November 2026)',
//                 'fixed_expires_at' => '2026-11-29 23:59:59',
//                 'validity_months'  => null,
//                 'rule_summary'     => 'Hanya berlaku valid untuk event BACK2ERAS. Tidak dapat diuangkan atau digabungkan dengan promo lainnya.',
//                 'terms'            => [
//                     'Voucher tidak dapat diuangkan atau digabungkan dengan promo lainnya.',
//                     'Voucher hanya berlaku valid untuk event BACK2ERAS.',
//                     'Solher tidak bertanggung jawab atas voucher yang dicuri, hilang, atau rusak/robek.',
//                 ],
//             ],
//             [
//                 'slug'             => 'gycora',
//                 'name'             => 'Gycora',
//                 'prefix'           => 'GYC',
//                 'start_sequence'   => 1,
//                 'pad_length'       => 3,
//                 'max_quota'        => self::DEFAULT_MAX_QUOTA,
//                 'category'         => 'event',
//                 'location'         => 'Event BACK2ERAS (27 - 29 November 2026)',
//                 'phone'            => null,
//                 'instagram'        => null,
//                 'validity_label'   => 'Berlaku selama event BACK2ERAS (27 - 29 November 2026)',
//                 'fixed_expires_at' => '2026-11-29 23:59:59',
//                 'validity_months'  => null,
//                 'rule_summary'     => 'Hanya berlaku valid untuk event BACK2ERAS. Tidak dapat diuangkan atau digabungkan dengan promo lainnya.',
//                 'terms'            => [
//                     'Voucher tidak dapat diuangkan atau digabungkan dengan promo lainnya.',
//                     'Voucher hanya berlaku valid untuk event BACK2ERAS.',
//                     'Gycora Essence tidak bertanggung jawab atas voucher yang dicuri, hilang, atau rusak/robek.',
//                 ],
//             ],
//             [
//                 'slug'             => 'glamoire',
//                 'name'             => 'Glamoire',
//                 'prefix'           => 'GLM',
//                 'start_sequence'   => 1,
//                 'pad_length'       => 3,
//                 'max_quota'        => self::DEFAULT_MAX_QUOTA,
//                 'category'         => 'event',
//                 'location'         => 'Event BACK2ERAS (27 - 29 November 2026)',
//                 'phone'            => null,
//                 'instagram'        => null,
//                 'validity_label'   => 'Berlaku selama event BACK2ERAS (27 - 29 November 2026)',
//                 'fixed_expires_at' => '2026-11-29 23:59:59',
//                 'validity_months'  => null,
//                 'rule_summary'     => 'Hanya berlaku valid untuk event BACK2ERAS. Tidak dapat diuangkan atau digabungkan dengan promo lainnya.',
//                 'terms'            => [
//                     'Voucher tidak dapat diuangkan atau digabungkan dengan promo lainnya.',
//                     'Voucher hanya berlaku valid untuk event BACK2ERAS.',
//                     'Glamoire tidak bertanggung jawab atas voucher yang dicuri, hilang, atau rusak/robek.',
//                 ],
//             ],
//             [
//                 'slug'             => 'wdp',
//                 'name'             => 'WDP',
//                 'prefix'           => 'WDP',
//                 'start_sequence'   => 1,
//                 'pad_length'       => 3,
//                 'max_quota'        => self::DEFAULT_MAX_QUOTA,
//                 'category'         => 'dine_in',
//                 'location'         => 'Waroeng Depe (Jl. Raya Darmo Permai III)',
//                 'phone'            => null,
//                 'instagram'        => null,
//                 'validity_label'   => 'Berlaku 3 bulan dari tanggal terbit (Dine-in)',
//                 'fixed_expires_at' => null,
//                 'validity_months'  => 3,
//                 'rule_summary'     => 'Khusus dine-in di Waroeng Depe (Jl. Raya Darmo Permai III). Berlaku 3 bulan sejak tanggal terbit, belum termasuk pajak.',
//                 'terms'            => [
//                     'Voucher tidak dapat diuangkan.',
//                     'Voucher belum termasuk pajak.',
//                     'Voucher hanya dapat digunakan untuk makan di tempat (dine-in).',
//                     'Voucher tidak dapat digabungkan dengan promosi lainnya.',
//                     'Voucher hanya bisa digunakan di Waroeng Depe (Jl. Raya Darmo Permai III).',
//                     'Voucher hanya berlaku selama 3 bulan dari tanggal yang telah diterbitkan.',
//                     'Voucher tidak dapat digunakan untuk cemilan, minuman kaleng, dan konsinyasi.',
//                     'Waroeng Depe tidak bertanggung jawab atas voucher yang dicuri, hilang, atau rusak.',
//                     'Syarat dan ketentuan dapat berubah sewaktu-waktu tanpa pemberitahuan sebelumnya.',
//                 ],
//             ],
//             [
//                 'slug'             => 'kecilung',
//                 'name'             => 'Kecilung',
//                 'prefix'           => 'KCL',
//                 'start_sequence'   => 1,
//                 'pad_length'       => 3,
//                 'max_quota'        => self::DEFAULT_MAX_QUOTA,
//                 'category'         => 'dine_in',
//                 'location'         => 'The Kecilung (Jl. Kecilung No. 8)',
//                 'phone'            => '0818 0311 6688',
//                 'instagram'        => '@kecilungresto',
//                 'validity_label'   => 'Berlaku 3 bulan dari tanggal terbit (Dine-in)',
//                 'fixed_expires_at' => null,
//                 'validity_months'  => 3,
//                 'rule_summary'     => 'Khusus dine-in di The Kecilung (Jl. Kecilung No. 8). Berlaku 3 bulan sejak tanggal terbit, belum termasuk pajak.',
//                 'terms'            => [
//                     'Voucher tidak dapat diuangkan.',
//                     'Voucher belum termasuk pajak.',
//                     'Voucher hanya dapat digunakan untuk makan di tempat (dine-in).',
//                     'Voucher tidak dapat digabungkan dengan promosi lainnya.',
//                     'Voucher hanya bisa digunakan di The Kecilung (Jl. Kecilung No. 8).',
//                     'Voucher hanya berlaku selama 3 bulan dari tanggal yang telah diterbitkan.',
//                     'Voucher tidak dapat digunakan untuk cemilan, minuman kaleng, dan konsinyasi.',
//                     'Kecilung tidak bertanggung jawab atas voucher yang dicuri, hilang, atau rusak.',
//                     'Syarat dan ketentuan dapat berubah sewaktu-waktu tanpa pemberitahuan sebelumnya.',
//                 ],
//             ],
//             [
//                 'slug'             => 'malaijia',
//                 'name'             => 'Malaijia',
//                 'prefix'           => 'MLJ',
//                 'start_sequence'   => 1,
//                 'pad_length'       => 3,
//                 'max_quota'        => self::DEFAULT_MAX_QUOTA,
//                 'category'         => 'dine_in',
//                 'location'         => 'Kopitiam Ma Lai Jia (Jl. Kecilung No. 8, Surabaya)',
//                 'phone'            => '0897-4170-999',
//                 'instagram'        => '@malaijia.kopitiam',
//                 'validity_label'   => 'Berlaku 3 bulan dari tanggal terbit (Dine-in)',
//                 'fixed_expires_at' => null,
//                 'validity_months'  => 3,
//                 'rule_summary'     => 'Khusus dine-in di Kopitiam Ma Lai Jia (Jl. Kecilung No. 8, Surabaya). Tidak dapat diuangkan atau digabung promo lain.',
//                 'terms'            => [
//                     'Voucher tidak dapat diuangkan.',
//                     'Voucher hanya dapat digunakan untuk makan di tempat (dine-in).',
//                     'Voucher tidak dapat digabungkan dengan promosi lainnya.',
//                     'Voucher hanya bisa digunakan di Kopitiam Ma Lai Jia (Jl. Kecilung No. 8, Surabaya).',
//                     'The Kecilung tidak bertanggung jawab atas voucher yang dicuri, hilang, atau rusak.',
//                     'Syarat dan ketentuan dapat berubah sewaktu-waktu tanpa pemberitahuan sebelumnya.',
//                 ],
//             ],
//         ];
//     }

//     /**
//      * Konfigurasi Voucher Free Goodie Bag khusus 5 pendaftar pertama Talkshow yang di-approve.
//      */
//     private function getTalkshowGoodieBagConfig(): array
//     {
//         return [
//             'slug'             => 'talkshow-goodiebag',
//             'name'             => 'Free Goodie Bag (Talkshow Top 5)',
//             'prefix'           => 'GBTS',
//             'start_sequence'   => 1,
//             'pad_length'       => 3,
//             'max_quota'        => self::DEFAULT_MAX_QUOTA,
//             'category'         => 'event',
//             'location'         => 'Booth Registrasi BACK2ERAS (27 - 29 November 2026)',
//             'phone'            => null,
//             'instagram'        => null,
//             'validity_label'   => 'Khusus 5 Pendaftar Pertama Talkshow (Approved)',
//             'fixed_expires_at' => '2026-11-29 23:59:59',
//             'validity_months'  => null,
//             'rule_summary'     => 'Voucher eksklusif Free Goodie Bag khusus 5 peserta pertama Talkshow yang telah disetujui admin.',
//             'terms'            => [
//                 'Khusus untuk 5 pendaftar pertama Talkshow yang status pendaftarannya telah disetujui (Approved) oleh admin.',
//                 'Tunjukkan kode unik ini saat check-in di lokasi acara BACK2ERAS (27 - 29 November 2026).',
//                 'Berlaku 1x klaim per akun dan tidak dapat diuangkan atau dipindahtangankan.',
//                 'Pihak BACK2ERAS tidak bertanggung jawab atas voucher yang hilang atau disalahgunakan.',
//             ],
//         ];
//     }

//     private function getAllBrandsConfig(): array
//     {
//         return array_merge([$this->getTalkshowGoodieBagConfig()], $this->getBrandsConfig());
//     }

//     /**
//      * Hitung tanggal kedaluwarsa berdasarkan aturan masing-masing brand.
//      */
//     private function resolveExpiresAt(array $brandConfig, Carbon $issuedAt): Carbon
//     {
//         if (!empty($brandConfig['fixed_expires_at'])) {
//             return Carbon::parse($brandConfig['fixed_expires_at'], 'Asia/Jakarta');
//         }

//         $months = $brandConfig['validity_months'] ?? 3;
//         return $issuedAt->copy()->addMonths($months);
//     }

//     /**
//      * Ambil angka urut tertinggi saat ini (001 - 999) untuk prefix brand tertentu.
//      */
//     private function getHighestSequenceForBrand(array $brandConfig, bool $lock = false): int
//     {
//         $query = Voucher::where('brand_slug', $brandConfig['slug']);
//         if ($lock) {
//             $query->lockForUpdate();
//         }

//         $codes = $query->pluck('code');
//         $prefix = $brandConfig['prefix'];
//         $startSeq = (int) $brandConfig['start_sequence'];
//         $maxSeq = $startSeq - 1;

//         foreach ($codes as $code) {
//             if (preg_match('/^' . preg_quote($prefix, '/') . '(\d{3})$/', $code, $matches)) {
//                 $num = (int) $matches[1];
//                 if ($num >= $startSeq && $num > $maxSeq) {
//                     $maxSeq = $num;
//                 }
//             }
//         }

//         return $maxSeq;
//     }

//     /**
//      * Bentuk string kode voucher format [PREFIX][001-999] (contoh: B2E001, KCL001, GBTS001).
//      */
//     private function formatVoucherCode(array $brandConfig, int $sequence): string
//     {
//         $padLength = (int) ($brandConfig['pad_length'] ?? 3);
//         return $brandConfig['prefix'] . str_pad((string) $sequence, $padLength, '0', STR_PAD_LEFT);
//     }

//     // PROTECTED: Menampilkan daftar brand + status klaim & aturan sesuai role/tier user
//     public function index(Request $request)
//     {
//         $user = $request->user();
//         $now = Carbon::now('Asia/Jakarta');

//         $claimedMap = Voucher::where('user_id', $user->id)
//             ->get()
//             ->keyBy('brand_slug');

//         $eligibility = $this->checkVoucherEligibility($user);

//         $brandConfigs = collect($this->getBrandsConfig());

//         // Peserta talkshow urutan 6-25: sembunyikan voucher Back2eras
//         if (is_array($eligibility['allowed_slugs']) && count($eligibility['allowed_slugs']) > 0) {
//             $brandConfigs = $brandConfigs->filter(
//                 fn ($b) => in_array($b['slug'], $eligibility['allowed_slugs'], true)
//             )->values();
//         }

//         // Peserta talkshow urutan 1-5 (approved): tambahkan voucher Free Goodie Bag di urutan teratas
//         if ($eligibility['include_goodiebag']) {
//             $brandConfigs->prepend($this->getTalkshowGoodieBagConfig());
//         }

//         $brands = $brandConfigs->map(function ($brand) use ($claimedMap, $now) {
//             $claimed = $claimedMap->get($brand['slug']);
//             $maxQuota = (int) ($brand['max_quota'] ?? self::DEFAULT_MAX_QUOTA);
//             $maxSeqAllowed = (int) $brand['start_sequence'] + $maxQuota - 1;
//             $highestSeq = $this->getHighestSequenceForBrand($brand, false);
//             $remainingQuota = max(0, $maxSeqAllowed - $highestSeq);

//             $issuedAt = $claimed?->created_at?->copy()->timezone('Asia/Jakarta');
//             $expiresAt = null;

//             if ($claimed) {
//                 $expiresAt = $claimed->expires_at
//                     ? $claimed->expires_at->copy()->timezone('Asia/Jakarta')
//                     : $this->resolveExpiresAt($brand, $issuedAt ?? $now);
//             } elseif (!empty($brand['fixed_expires_at'])) {
//                 $expiresAt = Carbon::parse($brand['fixed_expires_at'], 'Asia/Jakarta');
//             }

//             $isEventClosed = !empty($brand['fixed_expires_at'])
//                 && $now->gt(Carbon::parse($brand['fixed_expires_at'], 'Asia/Jakarta'));

//             $isExpired = $claimed && $expiresAt ? $now->gt($expiresAt) : $isEventClosed;

//             return [
//                 'slug'             => $brand['slug'],
//                 'name'             => $brand['name'],
//                 'prefix'           => $brand['prefix'],
//                 'code_placeholder' => $brand['prefix'] . str_repeat('-', (int) $brand['pad_length']),
//                 'category'         => $brand['category'],
//                 'location'         => $brand['location'],
//                 'phone'            => $brand['phone'],
//                 'instagram'        => $brand['instagram'],
//                 'validity_label'   => $brand['validity_label'],
//                 'rule_summary'     => $brand['rule_summary'],
//                 'terms'            => $brand['terms'],
//                 'max_quota'        => $maxQuota,
//                 'remaining_quota'  => $remainingQuota,
//                 'claimed'          => (bool) $claimed,
//                 'code'             => $claimed?->code,
//                 'is_used'          => (bool) ($claimed?->is_used ?? false),
//                 'is_expired'       => $isExpired,
//                 'claimed_at'       => $issuedAt?->toIso8601String(),
//                 'expires_at'       => $expiresAt?->toIso8601String(),
//             ];
//         })->values();

//         return response()->json([
//             'can_claim_voucher'  => $eligibility['eligible'],
//             'claim_block_reason' => $eligibility['reason'],
//             'talkshow_rank'      => $eligibility['talkshow_rank'],
//             'brands'             => $brands,
//         ]);
//     }

//     // PROTECTED: Klaim kode unik format [3 HURUF][001-999] (1x per user per brand)
//     public function claim(Request $request)
//     {
//         $validated = $request->validate([
//             'brand_slug' => 'required|string',
//         ]);

//         $user = $request->user();

//         // Validasi kelayakan KOL (link medsos) & Talkshow (wajib approved)
//         $eligibility = $this->checkVoucherEligibility($user);
//         if (!$eligibility['eligible']) {
//             return response()->json([
//                 'message' => $eligibility['reason'],
//             ], 403);
//         }

//         $brandConfig = collect($this->getAllBrandsConfig())->firstWhere('slug', $validated['brand_slug']);

//         if (!$brandConfig) {
//             return response()->json(['message' => 'Brand tidak ditemukan.'], 404);
//         }

//         // Cegah klaim Free Goodie Bag jika bukan Top 5 Talkshow yang sudah di-approve
//         if ($brandConfig['slug'] === 'talkshow-goodiebag' && !$eligibility['include_goodiebag']) {
//             return response()->json([
//                 'message' => 'Voucher Free Goodie Bag hanya berlaku untuk 5 pendaftar pertama Talkshow yang telah disetujui admin.',
//             ], 403);
//         }

//         // Cegah peserta Talkshow urutan 6-25 mengklaim Back2eras
//         if (
//             $brandConfig['slug'] !== 'talkshow-goodiebag'
//             && is_array($eligibility['allowed_slugs'])
//             && !in_array($brandConfig['slug'], $eligibility['allowed_slugs'], true)
//         ) {
//             return response()->json([
//                 'message' => 'Peserta Talkshow urutan 6 - 25 hanya berhak mengklaim voucher partner (tidak termasuk voucher Back2eras).',
//             ], 403);
//         }

//         $now = Carbon::now('Asia/Jakarta');

//         // Validasi periode event untuk brand khusus event BACK2ERAS (27 - 29 November 2026)
//         if (!empty($brandConfig['fixed_expires_at'])) {
//             $eventEnd = Carbon::parse($brandConfig['fixed_expires_at'], 'Asia/Jakarta');
//             if ($now->gt($eventEnd)) {
//                 return response()->json([
//                     'message' => 'Periode acara BACK2ERAS (27 - 29 November 2026) telah berakhir. Voucher ' . $brandConfig['name'] . ' sudah tidak dapat diklaim.',
//                 ], 422);
//             }
//         }

//         $maxQuota = (int) ($brandConfig['max_quota'] ?? self::DEFAULT_MAX_QUOTA);
//         $maxSeqAllowed = (int) $brandConfig['start_sequence'] + $maxQuota - 1;

//         $result = DB::transaction(function () use ($user, $brandConfig, $now, $maxSeqAllowed) {
//             // 1. Cek apakah user sudah pernah klaim brand ini (dengan row lock)
//             $existing = Voucher::where('user_id', $user->id)
//                 ->where('brand_slug', $brandConfig['slug'])
//                 ->lockForUpdate()
//                 ->first();

//             if ($existing) {
//                 return [
//                     'status'  => 'already_claimed',
//                     'voucher' => $existing,
//                 ];
//             }

//             // 2. Hitung nomor urut berikutnya (001 - 999) secara aman
//             $highestSeq = $this->getHighestSequenceForBrand($brandConfig, true);
//             $nextSeq = $highestSeq + 1;

//             while ($nextSeq <= $maxSeqAllowed && Voucher::where('code', $this->formatVoucherCode($brandConfig, $nextSeq))->exists()) {
//                 $nextSeq++;
//             }

//             if ($nextSeq > $maxSeqAllowed) {
//                 return [
//                     'status' => 'quota_exceeded',
//                 ];
//             }

//             // 3. Bentuk kode unik format lama (contoh: B2E001, SLH001, GYC001, GLM001, WDP001, KCL001, MLJ001, GBTS001)
//             $uniqueCode = $this->formatVoucherCode($brandConfig, $nextSeq);
//             $expiresAt = $this->resolveExpiresAt($brandConfig, $now);

//             $voucher = Voucher::create([
//                 'user_id'    => $user->id,
//                 'brand_slug' => $brandConfig['slug'],
//                 'brand_name' => $brandConfig['name'],
//                 'code'       => $uniqueCode,
//                 'is_used'    => false,
//                 'expires_at' => $expiresAt,
//             ]);

//             return [
//                 'status'  => 'created',
//                 'voucher' => $voucher,
//             ];
//         });

//         if ($result['status'] === 'already_claimed') {
//             return response()->json([
//                 'message' => 'Anda sudah mengklaim voucher untuk ' . $brandConfig['name'] . '.',
//                 'voucher' => $result['voucher'],
//             ], 422);
//         }

//         if ($result['status'] === 'quota_exceeded') {
//             return response()->json([
//                 'message' => 'Maaf, kuota voucher ' . $brandConfig['name'] . ' (001 - 999) sudah habis.',
//             ], 422);
//         }

//         return response()->json([
//             'success' => true,
//             'message' => 'Kode unik ' . $brandConfig['name'] . ' berhasil diklaim!',
//             'voucher' => $result['voucher'],
//         ], 201);
//     }

//     /**
//      * Helper format payload voucher lengkap untuk kebutuhan Admin / Kasir.
//      */
//     private function formatAdminVoucher(Voucher $voucher, Carbon $now): array
//     {
//         $brandConfig = collect($this->getAllBrandsConfig())->firstWhere('slug', $voucher->brand_slug);
//         $issuedAt = $voucher->created_at?->copy()->timezone('Asia/Jakarta');
//         $expiresAt = $voucher->expires_at
//             ? $voucher->expires_at->copy()->timezone('Asia/Jakarta')
//             : ($brandConfig ? $this->resolveExpiresAt($brandConfig, $issuedAt ?? $now) : null);

//         $usedAt = $voucher->used_at?->copy()->timezone('Asia/Jakarta');
//         $isExpired = $expiresAt ? $now->gt($expiresAt) : false;

//         $status = 'unused';
//         if ($voucher->is_used) {
//             $status = 'used';
//         } elseif ($isExpired) {
//             $status = 'expired';
//         }

//         return [
//             'id'         => $voucher->id,
//             'code'       => $voucher->code,
//             'brand_slug' => $voucher->brand_slug,
//             'brand_name' => $voucher->brand_name,
//             'location'   => $brandConfig['location'] ?? '-',
//             'terms'      => $brandConfig['terms'] ?? [],
//             'is_used'    => (bool) $voucher->is_used,
//             'is_expired' => $isExpired,
//             'can_redeem' => !$voucher->is_used && !$isExpired,
//             'status'     => $status,
//             'claimed_at' => $issuedAt?->toIso8601String(),
//             'expires_at' => $expiresAt?->toIso8601String(),
//             'used_at'    => $usedAt?->toIso8601String(),
//             'user'       => [
//                 'id'       => $voucher->user?->id,
//                 'name'     => $voucher->user?->name ?? '-',
//                 'email'    => $voucher->user?->email ?? '-',
//                 'whatsapp' => $voucher->user?->visitor?->whatsapp
//                     ?? $voucher->user?->kol?->whatsapp
//                     ?? $voucher->user?->talkshowRegistrations?->first()?->phone
//                     ?? '-',
//             ],
//         ];
//     }

//     // ADMIN: Menampilkan seluruh daftar voucher beserta ringkasan statistik
//     public function adminIndex(Request $request)
//     {
//         $now = Carbon::now('Asia/Jakarta');

//         $vouchers = Voucher::with(['user.visitor', 'user.kol', 'user.talkshowRegistrations'])
//             ->orderBy('created_at', 'desc')
//             ->get()
//             ->map(fn (Voucher $v) => $this->formatAdminVoucher($v, $now));

//         $stats = [
//             'total'   => $vouchers->count(),
//             'unused'  => $vouchers->where('status', 'unused')->count(),
//             'used'    => $vouchers->where('status', 'used')->count(),
//             'expired' => $vouchers->where('status', 'expired')->count(),
//         ];

//         return response()->json([
//             'stats'    => $stats,
//             'vouchers' => $vouchers->values(),
//         ]);
//     }

//     // ADMIN: Cek / Verifikasi kode unik (misal: B2E001, KCL001, GBTS001) sebelum di-redeem
//     public function adminVerify(Request $request)
//     {
//         $validated = $request->validate([
//             'code' => 'required|string|max:50',
//         ]);

//         $code = strtoupper(trim($validated['code']));
//         $now = Carbon::now('Asia/Jakarta');

//         $voucher = Voucher::with(['user.visitor', 'user.kol', 'user.talkshowRegistrations'])
//             ->where('code', $code)
//             ->first();

//         if (!$voucher) {
//             return response()->json([
//                 'message' => "Kode voucher \"{$code}\" tidak ditemukan di sistem.",
//             ], 404);
//         }

//         return response()->json([
//             'voucher' => $this->formatAdminVoucher($voucher, $now),
//         ]);
//     }

//     // ADMIN: Tandai voucher sebagai sudah dipakai (is_used = true, used_at = now)
//     public function adminRedeem(Request $request)
//     {
//         $validated = $request->validate([
//             'code' => 'required|string|max:50',
//         ]);

//         $code = strtoupper(trim($validated['code']));
//         $now = Carbon::now('Asia/Jakarta');

//         $result = DB::transaction(function () use ($code, $now) {
//             $voucher = Voucher::with(['user.visitor', 'user.kol', 'user.talkshowRegistrations'])
//                 ->where('code', $code)
//                 ->lockForUpdate()
//                 ->first();

//             if (!$voucher) {
//                 return ['state' => 'not_found'];
//             }

//             if ($voucher->is_used) {
//                 return [
//                     'state'   => 'already_used',
//                     'voucher' => $voucher,
//                 ];
//             }

//             $brandConfig = collect($this->getAllBrandsConfig())->firstWhere('slug', $voucher->brand_slug);
//             $issuedAt = $voucher->created_at?->copy()->timezone('Asia/Jakarta');
//             $expiresAt = $voucher->expires_at
//                 ? $voucher->expires_at->copy()->timezone('Asia/Jakarta')
//                 : ($brandConfig ? $this->resolveExpiresAt($brandConfig, $issuedAt ?? $now) : null);

//             if ($expiresAt && $now->gt($expiresAt)) {
//                 return [
//                     'state'   => 'expired',
//                     'voucher' => $voucher,
//                 ];
//             }

//             $voucher->is_used = true;
//             $voucher->used_at = $now;
//             $voucher->save();

//             return [
//                 'state'   => 'redeemed',
//                 'voucher' => $voucher,
//             ];
//         });

//         if ($result['state'] === 'not_found') {
//             return response()->json([
//                 'message' => "Kode voucher \"{$code}\" tidak ditemukan.",
//             ], 404);
//         }

//         if ($result['state'] === 'already_used') {
//             $usedTime = $result['voucher']->used_at?->copy()->timezone('Asia/Jakarta')->format('d M Y H:i') . ' WIB';
//             return response()->json([
//                 'message' => "Voucher {$code} sudah pernah digunakan pada {$usedTime}.",
//                 'voucher' => $this->formatAdminVoucher($result['voucher'], $now),
//             ], 422);
//         }

//         if ($result['state'] === 'expired') {
//             return response()->json([
//                 'message' => "Voucher {$code} sudah kedaluwarsa dan tidak dapat digunakan.",
//                 'voucher' => $this->formatAdminVoucher($result['voucher'], $now),
//             ], 422);
//         }

//         return response()->json([
//             'success' => true,
//             'message' => "Voucher {$code} berhasil diverifikasi & ditandai sudah dipakai!",
//             'voucher' => $this->formatAdminVoucher($result['voucher'], $now),
//         ]);
//     }

//     /**
//      * Cek kelayakan voucher untuk Visitor, KOL, maupun Peserta Talkshow.
//      */
//     private function checkVoucherEligibility($user): array
//     {
//         $talkshowRegs = TalkshowRegistration::where(function ($q) use ($user) {
//             $q->where('user_id', $user->id)
//               ->orWhere('email', $user->email);
//         })->get();

//         $approvedRanks = $talkshowRegs
//             ->where('status', 'approved')
//             ->pluck('approved_rank')
//             ->filter(fn ($rank) => $rank !== null)
//             ->values();

//         $bestApprovedRank = $approvedRanks->isNotEmpty() ? (int) $approvedRanks->min() : null;
//         $includeGoodieBag = $bestApprovedRank !== null && $bestApprovedRank <= 5;

//         // 1. Aturan khusus akun role = 'talkshow'
//         if ($user->role === 'talkshow') {
//             if ($bestApprovedRank === null) {
//                 $hasPending = $talkshowRegs->where('status', 'pending')->isNotEmpty();

//                 return [
//                     'eligible'          => false,
//                     'reason'            => $hasPending
//                         ? 'Pendaftaran Talkshow Anda masih menunggu persetujuan (Approve) dari admin. Voucher baru dapat diklaim setelah pendaftaran Anda diterima.'
//                         : 'Pendaftaran Talkshow Anda belum disetujui atau ditolak oleh admin sehingga tidak dapat mengklaim voucher.',
//                     'allowed_slugs'     => [],
//                     'include_goodiebag' => false,
//                     'talkshow_rank'     => null,
//                 ];
//             }

//             // Urutan 1 - 5: Free Goodie Bag + Semua 7 voucher visitor (termasuk Back2eras)
//             if ($bestApprovedRank <= 5) {
//                 return [
//                     'eligible'          => true,
//                     'reason'            => null,
//                     'allowed_slugs'     => null,
//                     'include_goodiebag' => true,
//                     'talkshow_rank'     => $bestApprovedRank,
//                 ];
//             }

//             // Urutan 6 - 25: Semua voucher visitor KECUALI Back2eras (tanpa Goodie Bag)
//             return [
//                 'eligible'          => true,
//                 'reason'            => null,
//                 'allowed_slugs'     => ['solher', 'gycora', 'glamoire', 'wdp', 'kecilung', 'malaijia'],
//                 'include_goodiebag' => false,
//                 'talkshow_rank'     => $bestApprovedRank,
//             ];
//         }

//         // 2. Aturan khusus akun role = 'kol'
//         if ($user->role === 'kol') {
//             $kol = Kol::where('user_id', $user->id)
//                 ->orWhere('email', $user->email)
//                 ->first();

//             $hasSocialLink = !empty(trim((string) ($kol?->social_media_link ?? '')));

//             if (!$hasSocialLink) {
//                 return [
//                     'eligible'          => false,
//                     'reason'            => 'Akun KOL wajib mengisi Link Media Sosial terlebih dahulu agar dapat mengklaim voucher.',
//                     'allowed_slugs'     => null,
//                     'include_goodiebag' => $includeGoodieBag,
//                     'talkshow_rank'     => $bestApprovedRank,
//                 ];
//             }
//         }

//         // 3. Default: Visitor (plus bonus Goodie Bag jika juga approved 1-5 di Talkshow)
//         return [
//             'eligible'          => true,
//             'reason'            => null,
//             'allowed_slugs'     => null,
//             'include_goodiebag' => $includeGoodieBag,
//             'talkshow_rank'     => $bestApprovedRank,
//         ];
//     }
// }

// namespace App\Http\Controllers;

// use App\Models\Kol;
// use App\Models\TalkshowRegistration;
// use App\Models\Voucher;
// use Carbon\Carbon;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\DB;

// class VoucherController extends Controller
// {
//     private const DEFAULT_MAX_QUOTA = 999;

//     /**
//      * Normalisasi role user untuk penentuan aturan voucher.
//      */
//     private function normalizeRole(?string $role): string
//     {
//         return match ($role) {
//             'kol'      => 'kol',
//             'talkshow' => 'talkshow',
//             default    => 'visitor',
//         };
//     }

//     /**
//      * Konfigurasi lengkap 7 Brand yang beradaptasi terhadap role user (visitor / talkshow / kol).
//      * Format kode tetap 3 huruf prefix + 3 digit angka (001 - 999).
//      */
//     private function getBrandsConfig(string $role = 'visitor'): array
//     {
//         $role = $this->normalizeRole($role);

//         // --- A. BACK2ERAS (Visitor memiliki syarat minimum pembelian Rp100.000) ---
//         $back2erasTerms = $role === 'visitor'
//             ? [
//                 'Voucher tidak dapat diuangkan.',
//                 'Voucher hanya bisa digunakan pada acara BACK2ERAS (27 - 29 November 2026).',
//                 'Voucher tidak dapat digabungkan dengan promosi lainnya.',
//                 'Voucher hanya berlaku selama acara berlangsung saja.',
//                 'Pihak BACK2ERAS tidak bertanggung jawab atas voucher yang dicuri, hilang, atau rusak.',
//                 'Minimum pembelian Rp100.000 dalam satu transaksi untuk dapat mengklaim.',
//                 'Syarat & ketentuan dapat berubah sewaktu-waktu tanpa pemberitahuan sebelumnya.',
//             ]
//             : [
//                 'Voucher tidak dapat diuangkan.',
//                 'Voucher hanya bisa digunakan pada acara BACK2ERAS (27 - 29 November 2026).',
//                 'Voucher tidak dapat digabungkan dengan promosi lainnya.',
//                 'Voucher hanya berlaku selama acara berlangsung saja.',
//                 'Pihak BACK2ERAS tidak bertanggung jawab atas voucher yang dicuri, hilang, atau rusak.',
//                 'Syarat & ketentuan dapat berubah sewaktu-waktu tanpa pemberitahuan sebelumnya.',
//             ];

//         $back2erasSummary = $role === 'visitor'
//             ? 'Berlaku selama acara BACK2ERAS (27 - 29 November 2026). Minimum pembelian Rp100.000 dalam satu transaksi.'
//             : 'Berlaku selama acara BACK2ERAS (27 - 29 November 2026). Tidak dapat diuangkan atau digabungkan dengan promo lain.';

//         // --- E. WDP (KOL punya masa berlaku 27-29 Nov 2026, berlaku 1 orang, & paket menu khusus) ---
//         $isKol = $role === 'kol';

//         $wdpConfig = $isKol
//             ? [
//                 'slug'             => 'wdp',
//                 'name'             => 'WDP',
//                 'prefix'           => 'WDP',
//                 'start_sequence'   => 1,
//                 'pad_length'       => 3,
//                 'max_quota'        => self::DEFAULT_MAX_QUOTA,
//                 'category'         => 'dine_in',
//                 'location'         => 'Waroeng Depe (Jl. Raya Darmo Permai III)',
//                 'phone'            => '0857 7376 9690',
//                 'instagram'        => '@waroengdepesby',
//                 'website'          => null,
//                 'promo_highlight'  => 'Khusus KOL (1 Orang) • Dine-in 27 - 29 Nov 2026',
//                 'special_menus'    => [
//                     'NASI SOTO + PERKEDEL & ES TEH',
//                     'NASI SEMARANG & ES CAO',
//                 ],
//                 'validity_label'   => 'Berlaku tanggal 27 - 29 November 2026 (Dine-in • 1 Orang)',
//                 'fixed_expires_at' => '2026-11-29 23:59:59',
//                 'validity_months'  => null,
//                 'rule_summary'     => 'Voucher khusus KOL untuk 1 orang (dine-in di Waroeng Depe, Jl. Raya Darmo Permai III) pada 27 - 29 November 2026.',
//                 'terms'            => [
//                     'Voucher tidak dapat diuangkan.',
//                     'Voucher hanya berlaku untuk 1 orang.',
//                     'Voucher hanya dapat digunakan untuk makan di tempat (dine-in).',
//                     'Voucher tidak dapat digabungkan dengan promosi lainnya.',
//                     'Voucher hanya bisa digunakan di Waroeng Depe (Jl. Raya Darmo Permai III).',
//                     'Voucher hanya berlaku tanggal 27 - 29 November 2026.',
//                     'Waroeng Depe tidak bertanggung jawab atas voucher yang dicuri, hilang, atau rusak.',
//                     'Syarat dan ketentuan dapat berubah sewaktu-waktu tanpa pemberitahuan sebelumnya.',
//                 ],
//             ]
//             : [
//                 'slug'             => 'wdp',
//                 'name'             => 'WDP',
//                 'prefix'           => 'WDP',
//                 'start_sequence'   => 1,
//                 'pad_length'       => 3,
//                 'max_quota'        => self::DEFAULT_MAX_QUOTA,
//                 'category'         => 'dine_in',
//                 'location'         => 'Waroeng Depe (Jl. Raya Darmo Permai III)',
//                 'phone'            => '0857 7376 9690',
//                 'instagram'        => '@waroengdepesby',
//                 'website'          => null,
//                 'promo_highlight'  => 'Min. pembelian 2 item makanan/snack dalam 1 transaksi',
//                 'special_menus'    => [],
//                 'validity_label'   => 'Berlaku 3 bulan dari tanggal terbit (Dine-in)',
//                 'fixed_expires_at' => null,
//                 'validity_months'  => 3,
//                 'rule_summary'     => 'Khusus dine-in di Waroeng Depe (Jl. Raya Darmo Permai III). Berlaku 3 bulan sejak tanggal terbit, min. pembelian 2 item makanan/snack.',
//                 'terms'            => [
//                     'Voucher tidak dapat diuangkan.',
//                     'Voucher belum termasuk pajak.',
//                     'Voucher hanya dapat digunakan untuk makan di tempat (dine-in).',
//                     'Voucher tidak dapat digabungkan dengan promosi lainnya.',
//                     'Voucher hanya bisa digunakan di Waroeng Depe (Jl. Raya Darmo Permai III).',
//                     'Voucher hanya berlaku selama 3 bulan dari tanggal yang telah diterbitkan.',
//                     'Voucher hanya dapat digunakan dengan minimum pembelian 2 (dua) item makanan dan/atau snack dalam satu transaksi.',
//                     'Waroeng Depe tidak bertanggung jawab atas voucher yang dicuri, hilang, atau rusak.',
//                     'Syarat dan ketentuan dapat berubah sewaktu-waktu tanpa pemberitahuan sebelumnya.',
//                 ],
//             ];

//         return [
//             [
//                 'slug'             => 'back2eras',
//                 'name'             => 'Back2eras',
//                 'prefix'           => 'B2E',
//                 'start_sequence'   => 1,
//                 'pad_length'       => 3,
//                 'max_quota'        => self::DEFAULT_MAX_QUOTA,
//                 'category'         => 'event',
//                 'location'         => 'Event BACK2ERAS (27 - 29 November 2026)',
//                 'phone'            => null,
//                 'instagram'        => null,
//                 'website'          => null,
//                 'promo_highlight'  => $role === 'visitor' ? 'Minimum pembelian Rp100.000 dalam 1 transaksi' : null,
//                 'special_menus'    => [],
//                 'validity_label'   => '27 - 29 November 2026 (Selama acara berlangsung)',
//                 'fixed_expires_at' => '2026-11-29 23:59:59',
//                 'validity_months'  => null,
//                 'rule_summary'     => $back2erasSummary,
//                 'terms'            => $back2erasTerms,
//             ],
//             [
//                 'slug'             => 'solher',
//                 'name'             => 'Solher',
//                 'prefix'           => 'SLH',
//                 'start_sequence'   => 1,
//                 'pad_length'       => 3,
//                 'max_quota'        => self::DEFAULT_MAX_QUOTA,
//                 'category'         => 'event',
//                 'location'         => 'Event BACK2ERAS (27 - 29 November 2026)',
//                 'phone'            => '0888 3888 585',
//                 'instagram'        => '@Solher.official',
//                 'website'          => 'www.solher.co.id',
//                 'promo_highlight'  => 'Special Price Rp399.000 (dari Rp1.448.000) untuk Party Bag',
//                 'special_menus'    => [],
//                 'validity_label'   => 'Berlaku selama 27 - 29 November 2026 di Acara Back2Era',
//                 'fixed_expires_at' => '2026-11-29 23:59:59',
//                 'validity_months'  => null,
//                 'rule_summary'     => 'Special price Rp399.000 (dari Rp1.448.000) untuk party bag selama 27 - 29 November 2026 di Acara Back2Era. Berlaku 1x redeem.',
//                 'terms'            => [
//                     'Voucher tidak dapat diuangkan.',
//                     'Voucher hanya bisa di-redeem satu kali.',
//                     'Voucher berlaku selama 27 - 29 November 2026 di Acara Back2Era.',
//                     'Voucher tidak dapat digabungkan dengan promosi lainnya.',
//                     'Mendapatkan special price Rp399.000 dari harga Rp1.448.000 untuk party bag.',
//                     'Syarat dan ketentuan dapat berubah sewaktu-waktu tanpa pemberitahuan sebelumnya.',
//                 ],
//             ],
//             [
//                 'slug'             => 'gycora',
//                 'name'             => 'Gycora',
//                 'prefix'           => 'GYC',
//                 'start_sequence'   => 1,
//                 'pad_length'       => 3,
//                 'max_quota'        => self::DEFAULT_MAX_QUOTA,
//                 'category'         => 'event',
//                 'location'         => 'Event BACK2ERAS (27 - 29 November 2026)',
//                 'phone'            => null,
//                 'instagram'        => null,
//                 'website'          => null,
//                 'promo_highlight'  => null,
//                 'special_menus'    => [],
//                 'validity_label'   => 'Berlaku selama event BACK2ERAS (27 - 29 November 2026)',
//                 'fixed_expires_at' => '2026-11-29 23:59:59',
//                 'validity_months'  => null,
//                 'rule_summary'     => 'Hanya berlaku valid untuk event BACK2ERAS. Tidak dapat diuangkan atau digabungkan dengan promo lainnya.',
//                 'terms'            => [
//                     'Voucher tidak dapat diuangkan atau digabungkan dengan promo lainnya.',
//                     'Voucher hanya berlaku valid untuk event BACK2ERAS.',
//                     'Gycora Essence tidak bertanggung jawab atas voucher yang dicuri, hilang, atau rusak/robek.',
//                 ],
//             ],
//             [
//                 'slug'             => 'glamoire',
//                 'name'             => 'Glamoire',
//                 'prefix'           => 'GLM',
//                 'start_sequence'   => 1,
//                 'pad_length'       => 3,
//                 'max_quota'        => self::DEFAULT_MAX_QUOTA,
//                 'category'         => 'event',
//                 'location'         => 'Event BACK2ERAS (27 - 29 November 2026)',
//                 'phone'            => null,
//                 'instagram'        => null,
//                 'website'          => null,
//                 'promo_highlight'  => null,
//                 'special_menus'    => [],
//                 'validity_label'   => 'Berlaku selama event BACK2ERAS (27 - 29 November 2026)',
//                 'fixed_expires_at' => '2026-11-29 23:59:59',
//                 'validity_months'  => null,
//                 'rule_summary'     => 'Hanya berlaku valid untuk event BACK2ERAS. Tidak dapat diuangkan atau digabungkan dengan promo lainnya.',
//                 'terms'            => [
//                     'Voucher tidak dapat diuangkan atau digabungkan dengan promo lainnya.',
//                     'Voucher hanya berlaku valid untuk event BACK2ERAS.',
//                     'Glamoire tidak bertanggung jawab atas voucher yang dicuri, hilang, atau rusak/robek.',
//                 ],
//             ],
//             $wdpConfig,
//             [
//                 'slug'             => 'kecilung',
//                 'name'             => 'Kecilung',
//                 'prefix'           => 'KCL',
//                 'start_sequence'   => 1,
//                 'pad_length'       => 3,
//                 'max_quota'        => self::DEFAULT_MAX_QUOTA,
//                 'category'         => 'dine_in',
//                 'location'         => 'The Kecilung (Jl. Kecilung No. 8)',
//                 'phone'            => '0818 0311 6688',
//                 'instagram'        => '@kecilungresto',
//                 'website'          => null,
//                 'promo_highlight'  => null,
//                 'special_menus'    => [],
//                 'validity_label'   => 'Berlaku 3 bulan dari tanggal terbit (Dine-in)',
//                 'fixed_expires_at' => null,
//                 'validity_months'  => 3,
//                 'rule_summary'     => 'Khusus dine-in di The Kecilung (Jl. Kecilung No. 8). Berlaku 3 bulan sejak tanggal terbit, belum termasuk pajak.',
//                 'terms'            => [
//                     'Voucher tidak dapat diuangkan.',
//                     'Voucher belum termasuk pajak.',
//                     'Voucher hanya dapat digunakan untuk makan di tempat (dine-in).',
//                     'Voucher tidak dapat digabungkan dengan promosi lainnya.',
//                     'Voucher hanya bisa digunakan di The Kecilung (Jl. Kecilung No. 8).',
//                     'Voucher hanya berlaku selama 3 bulan dari tanggal yang telah diterbitkan.',
//                     'Voucher tidak dapat digunakan untuk cemilan, minuman kaleng, dan konsinyasi.',
//                     'Kecilung tidak bertanggung jawab atas voucher yang dicuri, hilang, atau rusak.',
//                     'Syarat dan ketentuan dapat berubah sewaktu-waktu tanpa pemberitahuan sebelumnya.',
//                 ],
//             ],
//             [
//                 'slug'             => 'malaijia',
//                 'name'             => 'Malaijia',
//                 'prefix'           => 'MLJ',
//                 'start_sequence'   => 1,
//                 'pad_length'       => 3,
//                 'max_quota'        => self::DEFAULT_MAX_QUOTA,
//                 'category'         => 'dine_in',
//                 'location'         => 'Kopitiam Ma Lai Jia (Jl. Kecilung No. 8, Surabaya)',
//                 'phone'            => '0897-4170-999',
//                 'instagram'        => '@malaijia.kopitiam',
//                 'website'          => null,
//                 'promo_highlight'  => null,
//                 'special_menus'    => [],
//                 'validity_label'   => 'Berlaku 3 bulan dari tanggal terbit (Dine-in)',
//                 'fixed_expires_at' => null,
//                 'validity_months'  => 3,
//                 'rule_summary'     => 'Khusus dine-in di Kopitiam Ma Lai Jia (Jl. Kecilung No. 8, Surabaya). Tidak dapat diuangkan atau digabung promo lain.',
//                 'terms'            => [
//                     'Voucher tidak dapat diuangkan.',
//                     'Voucher hanya dapat digunakan untuk makan di tempat (dine-in).',
//                     'Voucher tidak dapat digabungkan dengan promosi lainnya.',
//                     'Voucher hanya bisa digunakan di Kopitiam Ma Lai Jia (Jl. Kecilung No. 8, Surabaya).',
//                     'The Kecilung tidak bertanggung jawab atas voucher yang dicuri, hilang, atau rusak.',
//                     'Syarat dan ketentuan dapat berubah sewaktu-waktu tanpa pemberitahuan sebelumnya.',
//                 ],
//             ],
//         ];
//     }

//     /**
//      * Konfigurasi Voucher Free Goodie Bag khusus 5 pendaftar pertama Talkshow yang di-approve.
//      */
//     private function getTalkshowGoodieBagConfig(): array
//     {
//         return [
//             'slug'             => 'talkshow-goodiebag',
//             'name'             => 'Free Goodie Bag (Talkshow Top 5)',
//             'prefix'           => 'GBTS',
//             'start_sequence'   => 1,
//             'pad_length'       => 3,
//             'max_quota'        => self::DEFAULT_MAX_QUOTA,
//             'category'         => 'event',
//             'location'         => 'Booth Registrasi BACK2ERAS (27 - 29 November 2026)',
//             'phone'            => null,
//             'instagram'        => null,
//             'website'          => null,
//             'promo_highlight'  => 'Eksklusif 5 Pendaftar Pertama Talkshow (Approved)',
//             'special_menus'    => [],
//             'validity_label'   => 'Khusus 5 Pendaftar Pertama Talkshow (Approved)',
//             'fixed_expires_at' => '2026-11-29 23:59:59',
//             'validity_months'  => null,
//             'rule_summary'     => 'Voucher eksklusif Free Goodie Bag khusus 5 peserta pertama Talkshow yang telah disetujui admin.',
//             'terms'            => [
//                 'Khusus untuk 5 pendaftar pertama Talkshow yang status pendaftarannya telah disetujui (Approved) oleh admin.',
//                 'Tunjukkan kode unik ini saat check-in di lokasi acara BACK2ERAS (27 - 29 November 2026).',
//                 'Berlaku 1x klaim per akun dan tidak dapat diuangkan atau dipindahtangankan.',
//                 'Pihak BACK2ERAS tidak bertanggung jawab atas voucher yang hilang atau disalahgunakan.',
//             ],
//         ];
//     }

//     private function getAllBrandsConfig(string $role = 'visitor'): array
//     {
//         return array_merge([$this->getTalkshowGoodieBagConfig()], $this->getBrandsConfig($role));
//     }

//     /**
//      * Hitung tanggal kedaluwarsa berdasarkan aturan masing-masing brand dan role user.
//      */
//     private function resolveExpiresAt(array $brandConfig, Carbon $issuedAt): Carbon
//     {
//         if (!empty($brandConfig['fixed_expires_at'])) {
//             return Carbon::parse($brandConfig['fixed_expires_at'], 'Asia/Jakarta');
//         }

//         $months = $brandConfig['validity_months'] ?? 3;
//         return $issuedAt->copy()->addMonths($months);
//     }

//     /**
//      * Menentukan expired efektif untuk voucher yang sudah terbit.
//      * Jika brand memiliki fixed_expires_at (seperti WDP khusus KOL -> 29 Nov 2026),
//      * selalu ikuti batas tanggal tetap tersebut meskipun voucher sempat terbit sebelum update aturan.
//      */
//     private function resolveEffectiveExpiresAt(?array $brandConfig, ?Voucher $voucher, Carbon $now): ?Carbon
//     {
//         if ($brandConfig && !empty($brandConfig['fixed_expires_at'])) {
//             return Carbon::parse($brandConfig['fixed_expires_at'], 'Asia/Jakarta');
//         }

//         if ($voucher && $voucher->expires_at) {
//             return $voucher->expires_at->copy()->timezone('Asia/Jakarta');
//         }

//         if ($voucher && $brandConfig) {
//             $issuedAt = $voucher->created_at?->copy()->timezone('Asia/Jakarta') ?? $now;
//             return $this->resolveExpiresAt($brandConfig, $issuedAt);
//         }

//         return null;
//     }

//     /**
//      * Ambil angka urut tertinggi saat ini (001 - 999) untuk prefix brand tertentu.
//      */
//     private function getHighestSequenceForBrand(array $brandConfig, bool $lock = false): int
//     {
//         $query = Voucher::where('brand_slug', $brandConfig['slug']);
//         if ($lock) {
//             $query->lockForUpdate();
//         }

//         $codes = $query->pluck('code');
//         $prefix = $brandConfig['prefix'];
//         $startSeq = (int) $brandConfig['start_sequence'];
//         $maxSeq = $startSeq - 1;

//         foreach ($codes as $code) {
//             if (preg_match('/^' . preg_quote($prefix, '/') . '(\d{3})$/', $code, $matches)) {
//                 $num = (int) $matches[1];
//                 if ($num >= $startSeq && $num > $maxSeq) {
//                     $maxSeq = $num;
//                 }
//             }
//         }

//         return $maxSeq;
//     }

//     /**
//      * Bentuk string kode voucher format [PREFIX][001-999].
//      */
//     private function formatVoucherCode(array $brandConfig, int $sequence): string
//     {
//         $padLength = (int) ($brandConfig['pad_length'] ?? 3);
//         return $brandConfig['prefix'] . str_pad((string) $sequence, $padLength, '0', STR_PAD_LEFT);
//     }

//     // PROTECTED: Menampilkan daftar brand + status klaim & S&K sesuai role/tier user
//     public function index(Request $request)
//     {
//         $user = $request->user();
//         $role = $this->normalizeRole($user->role);
//         $now = Carbon::now('Asia/Jakarta');

//         $claimedMap = Voucher::where('user_id', $user->id)
//             ->get()
//             ->keyBy('brand_slug');

//         $eligibility = $this->checkVoucherEligibility($user);

//         $brandConfigs = collect($this->getBrandsConfig($role));

//         // Peserta talkshow urutan 6-25: sembunyikan voucher Back2eras
//         if (is_array($eligibility['allowed_slugs']) && count($eligibility['allowed_slugs']) > 0) {
//             $brandConfigs = $brandConfigs->filter(
//                 fn($b) => in_array($b['slug'], $eligibility['allowed_slugs'], true)
//             )->values();
//         }

//         // Peserta talkshow urutan 1-5 (approved): tambahkan voucher Free Goodie Bag di urutan teratas
//         // if ($eligibility['include_goodiebag']) {
//         //     $brandConfigs->prepend($this->getTalkshowGoodieBagConfig());
//         // }

//         $brands = $brandConfigs->map(function ($brand) use ($claimedMap, $now) {
//             $claimed = $claimedMap->get($brand['slug']);
//             $maxQuota = (int) ($brand['max_quota'] ?? self::DEFAULT_MAX_QUOTA);
//             $maxSeqAllowed = (int) $brand['start_sequence'] + $maxQuota - 1;
//             $highestSeq = $this->getHighestSequenceForBrand($brand, false);
//             $remainingQuota = max(0, $maxSeqAllowed - $highestSeq);

//             $issuedAt = $claimed?->created_at?->copy()->timezone('Asia/Jakarta');
//             $expiresAt = $this->resolveEffectiveExpiresAt($brand, $claimed, $now);

//             $isEventClosed = !empty($brand['fixed_expires_at'])
//                 && $now->gt(Carbon::parse($brand['fixed_expires_at'], 'Asia/Jakarta'));

//             $isExpired = $claimed && $expiresAt ? $now->gt($expiresAt) : $isEventClosed;

//             return [
//                 'slug'             => $brand['slug'],
//                 'name'             => $brand['name'],
//                 'prefix'           => $brand['prefix'],
//                 'code_placeholder' => $brand['prefix'] . str_repeat('-', (int) $brand['pad_length']),
//                 'category'         => $brand['category'],
//                 'location'         => $brand['location'],
//                 'phone'            => $brand['phone'],
//                 'instagram'        => $brand['instagram'],
//                 'website'          => $brand['website'] ?? null,
//                 'promo_highlight'  => $brand['promo_highlight'] ?? null,
//                 'special_menus'    => $brand['special_menus'] ?? [],
//                 'validity_label'   => $brand['validity_label'],
//                 'rule_summary'     => $brand['rule_summary'],
//                 'terms'            => $brand['terms'],
//                 'max_quota'        => $maxQuota,
//                 'remaining_quota'  => $remainingQuota,
//                 'claimed'          => (bool) $claimed,
//                 'code'             => $claimed?->code,
//                 'is_used'          => (bool) ($claimed?->is_used ?? false),
//                 'is_expired'       => $isExpired,
//                 'claimed_at'       => $issuedAt?->toIso8601String(),
//                 'expires_at'       => $expiresAt?->toIso8601String(),
//             ];
//         })->values();

//         return response()->json([
//             'user_role'          => $role,
//             'can_claim_voucher'  => $eligibility['eligible'],
//             'claim_block_reason' => $eligibility['reason'],
//             'talkshow_rank'      => $eligibility['talkshow_rank'],
//             'has_talkshow_goodiebag' => $eligibility['include_goodiebag'],
//             'brands'             => $brands,
//         ]);
//     }

//     // PROTECTED: Klaim kode unik format [PREFIX][001-999] (1x per user per brand)
//     public function claim(Request $request)
//     {
//         $validated = $request->validate([
//             'brand_slug' => 'required|string',
//         ]);

//         if ($validated['brand_slug'] === 'talkshow-goodiebag') {
//             return response()->json([
//                 'message' => 'Free Goodie Bag khusus 5 pendaftar pertama Talkshow tidak memerlukan klaim kode atau redeem. Cukup tunjukkan akun Anda saat check-in di booth registrasi.',
//             ], 422);
//         }

//         $user = $request->user();
//         $role = $this->normalizeRole($user->role);

//         // Validasi kelayakan KOL (link medsos) & Talkshow (wajib approved)
//         $eligibility = $this->checkVoucherEligibility($user);
//         if (!$eligibility['eligible']) {
//             return response()->json([
//                 'message' => $eligibility['reason'],
//             ], 403);
//         }

//         // $brandConfig = collect($this->getAllBrandsConfig($role))->firstWhere('slug', $validated['brand_slug']);
//         $brandConfig = collect($this->getBrandsConfig($role))->firstWhere('slug', $validated['brand_slug']);

//         if (!$brandConfig) {
//             return response()->json(['message' => 'Brand tidak ditemukan.'], 404);
//         }

//         // Cegah klaim Free Goodie Bag jika bukan Top 5 Talkshow yang sudah di-approve
//         if ($brandConfig['slug'] === 'talkshow-goodiebag' && !$eligibility['include_goodiebag']) {
//             return response()->json([
//                 'message' => 'Voucher Free Goodie Bag hanya berlaku untuk 5 pendaftar pertama Talkshow yang telah disetujui admin.',
//             ], 403);
//         }

//         // Cegah peserta Talkshow urutan 6-25 mengklaim Back2eras
//         if (
//             $brandConfig['slug'] !== 'talkshow-goodiebag'
//             && is_array($eligibility['allowed_slugs'])
//             && !in_array($brandConfig['slug'], $eligibility['allowed_slugs'], true)
//         ) {
//             return response()->json([
//                 'message' => 'Peserta Talkshow urutan 6 - 25 hanya berhak mengklaim voucher partner (tidak termasuk voucher Back2eras).',
//             ], 403);
//         }

//         $now = Carbon::now('Asia/Jakarta');

//         // Validasi periode event untuk brand dengan fixed_expires_at (termasuk WDP khusus KOL)
//         if (!empty($brandConfig['fixed_expires_at'])) {
//             $eventEnd = Carbon::parse($brandConfig['fixed_expires_at'], 'Asia/Jakarta');
//             if ($now->gt($eventEnd)) {
//                 return response()->json([
//                     'message' => 'Periode acara BACK2ERAS (27 - 29 November 2026) telah berakhir. Voucher ' . $brandConfig['name'] . ' sudah tidak dapat diklaim.',
//                 ], 422);
//             }
//         }

//         $maxQuota = (int) ($brandConfig['max_quota'] ?? self::DEFAULT_MAX_QUOTA);
//         $maxSeqAllowed = (int) $brandConfig['start_sequence'] + $maxQuota - 1;

//         $result = DB::transaction(function () use ($user, $brandConfig, $now, $maxSeqAllowed) {
//             $existing = Voucher::where('user_id', $user->id)
//                 ->where('brand_slug', $brandConfig['slug'])
//                 ->lockForUpdate()
//                 ->first();

//             if ($existing) {
//                 return [
//                     'status'  => 'already_claimed',
//                     'voucher' => $existing,
//                 ];
//             }

//             $highestSeq = $this->getHighestSequenceForBrand($brandConfig, true);
//             $nextSeq = $highestSeq + 1;

//             while ($nextSeq <= $maxSeqAllowed && Voucher::where('code', $this->formatVoucherCode($brandConfig, $nextSeq))->exists()) {
//                 $nextSeq++;
//             }

//             if ($nextSeq > $maxSeqAllowed) {
//                 return [
//                     'status' => 'quota_exceeded',
//                 ];
//             }

//             $uniqueCode = $this->formatVoucherCode($brandConfig, $nextSeq);
//             $expiresAt = $this->resolveExpiresAt($brandConfig, $now);

//             $voucher = Voucher::create([
//                 'user_id'    => $user->id,
//                 'brand_slug' => $brandConfig['slug'],
//                 'brand_name' => $brandConfig['name'],
//                 'code'       => $uniqueCode,
//                 'is_used'    => false,
//                 'expires_at' => $expiresAt,
//             ]);

//             return [
//                 'status'  => 'created',
//                 'voucher' => $voucher,
//             ];
//         });

//         if ($result['status'] === 'already_claimed') {
//             return response()->json([
//                 'message' => 'Anda sudah mengklaim voucher untuk ' . $brandConfig['name'] . '.',
//                 'voucher' => $result['voucher'],
//             ], 422);
//         }

//         if ($result['status'] === 'quota_exceeded') {
//             return response()->json([
//                 'message' => 'Maaf, kuota voucher ' . $brandConfig['name'] . ' (001 - 999) sudah habis.',
//             ], 422);
//         }

//         return response()->json([
//             'success' => true,
//             'message' => 'Kode unik ' . $brandConfig['name'] . ' berhasil diklaim!',
//             'voucher' => $result['voucher'],
//         ], 201);
//     }

//     /**
//      * Helper format payload voucher lengkap untuk kebutuhan Admin / Kasir,
//      * otomatis menyesuaikan aturan & menu berdasarkan role pemilik voucher.
//      */
//     private function formatAdminVoucher(Voucher $voucher, Carbon $now): array
//     {
//         $ownerRole = $this->normalizeRole($voucher->user?->role);
//         $brandConfig = collect($this->getAllBrandsConfig($ownerRole))->firstWhere('slug', $voucher->brand_slug);

//         $issuedAt = $voucher->created_at?->copy()->timezone('Asia/Jakarta');
//         $expiresAt = $this->resolveEffectiveExpiresAt($brandConfig, $voucher, $now);
//         $usedAt = $voucher->used_at?->copy()->timezone('Asia/Jakarta');
//         $isExpired = $expiresAt ? $now->gt($expiresAt) : false;

//         $status = 'unused';
//         if ($voucher->is_used) {
//             $status = 'used';
//         } elseif ($isExpired) {
//             $status = 'expired';
//         }

//         return [
//             'id'              => $voucher->id,
//             'code'            => $voucher->code,
//             'brand_slug'      => $voucher->brand_slug,
//             'brand_name'      => $voucher->brand_name,
//             'location'        => $brandConfig['location'] ?? '-',
//             'promo_highlight' => $brandConfig['promo_highlight'] ?? null,
//             'special_menus'   => $brandConfig['special_menus'] ?? [],
//             'terms'           => $brandConfig['terms'] ?? [],
//             'is_used'         => (bool) $voucher->is_used,
//             'is_expired'      => $isExpired,
//             'can_redeem'      => !$voucher->is_used && !$isExpired,
//             'status'          => $status,
//             'claimed_at'      => $issuedAt?->toIso8601String(),
//             'expires_at'      => $expiresAt?->toIso8601String(),
//             'used_at'         => $usedAt?->toIso8601String(),
//             'user'            => [
//                 'id'       => $voucher->user?->id,
//                 'name'     => $voucher->user?->name ?? '-',
//                 'email'    => $voucher->user?->email ?? '-',
//                 'role'     => $ownerRole,
//                 'whatsapp' => $voucher->user?->visitor?->whatsapp
//                     ?? $voucher->user?->kol?->whatsapp
//                     ?? $voucher->user?->talkshowRegistrations?->first()?->phone
//                     ?? '-',
//             ],
//         ];
//     }

//     // ADMIN: Menampilkan seluruh daftar voucher beserta ringkasan statistik
//     public function adminIndex(Request $request)
//     {
//         $now = Carbon::now('Asia/Jakarta');

//         $vouchers = Voucher::with(['user.visitor', 'user.kol', 'user.talkshowRegistrations'])
//             ->orderBy('created_at', 'desc')
//             ->get()
//             ->map(fn(Voucher $v) => $this->formatAdminVoucher($v, $now));

//         $stats = [
//             'total'   => $vouchers->count(),
//             'unused'  => $vouchers->where('status', 'unused')->count(),
//             'used'    => $vouchers->where('status', 'used')->count(),
//             'expired' => $vouchers->where('status', 'expired')->count(),
//         ];

//         return response()->json([
//             'stats'    => $stats,
//             'vouchers' => $vouchers->values(),
//         ]);
//     }

//     // ADMIN: Cek / Verifikasi kode unik sebelum di-redeem
//     public function adminVerify(Request $request)
//     {
//         $validated = $request->validate([
//             'code' => 'required|string|max:50',
//         ]);

//         $code = strtoupper(trim($validated['code']));
//         $now = Carbon::now('Asia/Jakarta');

//         $voucher = Voucher::with(['user.visitor', 'user.kol', 'user.talkshowRegistrations'])
//             ->where('code', $code)
//             ->first();

//         if (!$voucher) {
//             return response()->json([
//                 'message' => "Kode voucher \"{$code}\" tidak ditemukan di sistem.",
//             ], 404);
//         }

//         return response()->json([
//             'voucher' => $this->formatAdminVoucher($voucher, $now),
//         ]);
//     }

//     // ADMIN: Tandai voucher sebagai sudah dipakai (is_used = true, used_at = now)
//     public function adminRedeem(Request $request)
//     {
//         $validated = $request->validate([
//             'code' => 'required|string|max:50',
//         ]);

//         $code = strtoupper(trim($validated['code']));
//         $now = Carbon::now('Asia/Jakarta');

//         $result = DB::transaction(function () use ($code, $now) {
//             $voucher = Voucher::with(['user.visitor', 'user.kol', 'user.talkshowRegistrations'])
//                 ->where('code', $code)
//                 ->lockForUpdate()
//                 ->first();

//             if (!$voucher) {
//                 return ['state' => 'not_found'];
//             }

//             if ($voucher->is_used) {
//                 return [
//                     'state'   => 'already_used',
//                     'voucher' => $voucher,
//                 ];
//             }

//             $ownerRole = $this->normalizeRole($voucher->user?->role);
//             $brandConfig = collect($this->getAllBrandsConfig($ownerRole))->firstWhere('slug', $voucher->brand_slug);
//             $expiresAt = $this->resolveEffectiveExpiresAt($brandConfig, $voucher, $now);

//             if ($expiresAt && $now->gt($expiresAt)) {
//                 return [
//                     'state'   => 'expired',
//                     'voucher' => $voucher,
//                 ];
//             }

//             $voucher->is_used = true;
//             $voucher->used_at = $now;
//             $voucher->save();

//             return [
//                 'state'   => 'redeemed',
//                 'voucher' => $voucher,
//             ];
//         });

//         if ($result['state'] === 'not_found') {
//             return response()->json([
//                 'message' => "Kode voucher \"{$code}\" tidak ditemukan.",
//             ], 404);
//         }

//         if ($result['state'] === 'already_used') {
//             $usedTime = $result['voucher']->used_at?->copy()->timezone('Asia/Jakarta')->format('d M Y H:i') . ' WIB';
//             return response()->json([
//                 'message' => "Voucher {$code} sudah pernah digunakan pada {$usedTime}.",
//                 'voucher' => $this->formatAdminVoucher($result['voucher'], $now),
//             ], 422);
//         }

//         if ($result['state'] === 'expired') {
//             return response()->json([
//                 'message' => "Voucher {$code} sudah kedaluwarsa dan tidak dapat digunakan.",
//                 'voucher' => $this->formatAdminVoucher($result['voucher'], $now),
//             ], 422);
//         }

//         return response()->json([
//             'success' => true,
//             'message' => "Voucher {$code} berhasil diverifikasi & ditandai sudah dipakai!",
//             'voucher' => $this->formatAdminVoucher($result['voucher'], $now),
//         ]);
//     }

//     /**
//      * Cek kelayakan voucher untuk Visitor, KOL, maupun Peserta Talkshow.
//      */
//     private function checkVoucherEligibility($user): array
//     {
//         $talkshowRegs = TalkshowRegistration::where(function ($q) use ($user) {
//             $q->where('user_id', $user->id)
//                 ->orWhere('email', $user->email);
//         })->get();

//         $approvedRanks = $talkshowRegs
//             ->where('status', 'approved')
//             ->pluck('approved_rank')
//             ->filter(fn($rank) => $rank !== null)
//             ->values();

//         $bestApprovedRank = $approvedRanks->isNotEmpty() ? (int) $approvedRanks->min() : null;
//         $includeGoodieBag = $bestApprovedRank !== null && $bestApprovedRank <= 5;

//         // 1. Aturan khusus akun role = 'talkshow'
//         if ($user->role === 'talkshow') {
//             if ($bestApprovedRank === null) {
//                 $hasPending = $talkshowRegs->where('status', 'pending')->isNotEmpty();

//                 return [
//                     'eligible'          => false,
//                     'reason'            => $hasPending
//                         ? 'Pendaftaran Talkshow Anda masih menunggu persetujuan (Approve) dari admin. Voucher baru dapat diklaim setelah pendaftaran Anda diterima.'
//                         : 'Pendaftaran Talkshow Anda belum disetujui atau ditolak oleh admin sehingga tidak dapat mengklaim voucher.',
//                     'allowed_slugs'     => [],
//                     'include_goodiebag' => false,
//                     'talkshow_rank'     => null,
//                 ];
//             }

//             // Urutan 1 - 5: Free Goodie Bag + Semua 7 voucher visitor (termasuk Back2eras)
//             if ($bestApprovedRank <= 5) {
//                 return [
//                     'eligible'          => true,
//                     'reason'            => null,
//                     'allowed_slugs'     => null,
//                     'include_goodiebag' => true,
//                     'talkshow_rank'     => $bestApprovedRank,
//                 ];
//             }

//             // Urutan 6 - 25: Semua voucher visitor KECUALI Back2eras (tanpa Goodie Bag)
//             return [
//                 'eligible'          => true,
//                 'reason'            => null,
//                 'allowed_slugs'     => ['solher', 'gycora', 'glamoire', 'wdp', 'kecilung', 'malaijia'],
//                 'include_goodiebag' => false,
//                 'talkshow_rank'     => $bestApprovedRank,
//             ];
//         }

//         // 2. Aturan khusus akun role = 'kol'
//         if ($user->role === 'kol') {
//             $kol = Kol::where('user_id', $user->id)
//                 ->orWhere('email', $user->email)
//                 ->first();

//             $hasSocialLink = !empty(trim((string) ($kol?->social_media_link ?? '')));

//             if (!$hasSocialLink) {
//                 return [
//                     'eligible'          => false,
//                     'reason'            => 'Akun KOL wajib mengisi Link Media Sosial terlebih dahulu agar dapat mengklaim voucher.',
//                     'allowed_slugs'     => null,
//                     'include_goodiebag' => $includeGoodieBag,
//                     'talkshow_rank'     => $bestApprovedRank,
//                 ];
//             }
//         }

//         // 3. Default: Visitor (plus bonus Goodie Bag jika juga approved 1-5 di Talkshow)
//         return [
//             'eligible'          => true,
//             'reason'            => null,
//             'allowed_slugs'     => null,
//             'include_goodiebag' => $includeGoodieBag,
//             'talkshow_rank'     => $bestApprovedRank,
//         ];
//     }
// }

// namespace App\Http\Controllers;

// use App\Models\Kol;
// use App\Models\TalkshowRegistration;
// use App\Models\Voucher;
// use Carbon\Carbon;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\DB;

// class VoucherController extends Controller
// {
//     private const MAX_SEQUENCE = 999;
//     private const VISITOR_START_SEQUENCE = 1;
//     private const TALKSHOW_START_SEQUENCE = 101;

//     /**
//      * Normalisasi role user untuk penentuan aturan voucher.
//      */
//     private function normalizeRole(?string $role): string
//     {
//         return match ($role) {
//             'kol'      => 'kol',
//             'talkshow' => 'talkshow',
//             default    => 'visitor',
//         };
//     }

//     /**
//      * Konfigurasi lengkap 7 Brand yang beradaptasi terhadap role user (visitor / talkshow / kol).
//      * Prefix huruf dan aturan bisnis tetap sama; urutan kode Talkshow mulai dari 101 s/d 999.
//      */
//     private function getBrandsConfig(string $role = 'visitor'): array
//     {
//         $role = $this->normalizeRole($role);
//         $startSequence = $role === 'talkshow'
//             ? self::TALKSHOW_START_SEQUENCE
//             : self::VISITOR_START_SEQUENCE;
//         $maxSequence = self::MAX_SEQUENCE;
//         $maxQuota = $maxSequence - $startSequence + 1;

//         // --- A. BACK2ERAS (Visitor memiliki syarat minimum pembelian Rp100.000) ---
//         $back2erasTerms = $role === 'visitor'
//             ? [
//                 'Voucher tidak dapat diuangkan.',
//                 'Voucher hanya bisa digunakan pada acara BACK2ERAS (27 - 29 November 2026).',
//                 'Voucher tidak dapat digabungkan dengan promosi lainnya.',
//                 'Voucher hanya berlaku selama acara berlangsung saja.',
//                 'Pihak BACK2ERAS tidak bertanggung jawab atas voucher yang dicuri, hilang, atau rusak.',
//                 'Minimum pembelian Rp100.000 dalam satu transaksi untuk dapat mengklaim.',
//                 'Syarat & ketentuan dapat berubah sewaktu-waktu tanpa pemberitahuan sebelumnya.',
//             ]
//             : [
//                 'Voucher tidak dapat diuangkan.',
//                 'Voucher hanya bisa digunakan pada acara BACK2ERAS (27 - 29 November 2026).',
//                 'Voucher tidak dapat digabungkan dengan promosi lainnya.',
//                 'Voucher hanya berlaku selama acara berlangsung saja.',
//                 'Pihak BACK2ERAS tidak bertanggung jawab atas voucher yang dicuri, hilang, atau rusak.',
//                 'Syarat & ketentuan dapat berubah sewaktu-waktu tanpa pemberitahuan sebelumnya.',
//             ];

//         $back2erasSummary = $role === 'visitor'
//             ? 'Berlaku selama acara BACK2ERAS (27 - 29 November 2026). Minimum pembelian Rp100.000 dalam satu transaksi.'
//             : 'Berlaku selama acara BACK2ERAS (27 - 29 November 2026). Tidak dapat diuangkan atau digabungkan dengan promo lain.';

//         // --- E. WDP (KOL punya masa berlaku 27-29 Nov 2026, berlaku 1 orang, & paket menu khusus) ---
//         $isKol = $role === 'kol';

//         $wdpConfig = $isKol
//             ? [
//                 'slug'             => 'wdp',
//                 'name'             => 'WDP',
//                 'prefix'           => 'WDP',
//                 'start_sequence'   => $startSequence,
//                 'max_sequence'     => $maxSequence,
//                 'pad_length'       => 3,
//                 'max_quota'        => $maxQuota,
//                 'category'         => 'dine_in',
//                 'location'         => 'Waroeng Depe (Jl. Raya Darmo Permai III)',
//                 'phone'            => '0857 7376 9690',
//                 'instagram'        => '@waroengdepesby',
//                 'website'          => null,
//                 'promo_highlight'  => 'Khusus KOL (1 Orang) • Dine-in 27 - 29 Nov 2026',
//                 'special_menus'    => [
//                     'NASI SOTO + PERKEDEL & ES TEH',
//                     'NASI SEMARANG & ES CAO',
//                 ],
//                 'validity_label'   => 'Berlaku tanggal 27 - 29 November 2026 (Dine-in • 1 Orang)',
//                 'fixed_expires_at' => '2026-11-29 23:59:59',
//                 'validity_months'  => null,
//                 'rule_summary'     => 'Voucher khusus KOL untuk 1 orang (dine-in di Waroeng Depe, Jl. Raya Darmo Permai III) pada 27 - 29 November 2026.',
//                 'terms'            => [
//                     'Voucher tidak dapat diuangkan.',
//                     'Voucher hanya berlaku untuk 1 orang.',
//                     'Voucher hanya dapat digunakan untuk makan di tempat (dine-in).',
//                     'Voucher tidak dapat digabungkan dengan promosi lainnya.',
//                     'Voucher hanya bisa digunakan di Waroeng Depe (Jl. Raya Darmo Permai III).',
//                     'Voucher hanya berlaku tanggal 27 - 29 November 2026.',
//                     'Waroeng Depe tidak bertanggung jawab atas voucher yang dicuri, hilang, atau rusak.',
//                     'Syarat dan ketentuan dapat berubah sewaktu-waktu tanpa pemberitahuan sebelumnya.',
//                 ],
//             ]
//             : [
//                 'slug'             => 'wdp',
//                 'name'             => 'WDP',
//                 'prefix'           => 'WDP',
//                 'start_sequence'   => $startSequence,
//                 'max_sequence'     => $maxSequence,
//                 'pad_length'       => 3,
//                 'max_quota'        => $maxQuota,
//                 'category'         => 'dine_in',
//                 'location'         => 'Waroeng Depe (Jl. Raya Darmo Permai III)',
//                 'phone'            => '0857 7376 9690',
//                 'instagram'        => '@waroengdepesby',
//                 'website'          => null,
//                 'promo_highlight'  => 'Min. pembelian 2 item makanan/snack dalam 1 transaksi',
//                 'special_menus'    => [],
//                 'validity_label'   => 'Berlaku 3 bulan dari tanggal terbit (Dine-in)',
//                 'fixed_expires_at' => null,
//                 'validity_months'  => 3,
//                 'rule_summary'     => 'Khusus dine-in di Waroeng Depe (Jl. Raya Darmo Permai III). Berlaku 3 bulan sejak tanggal terbit, min. pembelian 2 item makanan/snack.',
//                 'terms'            => [
//                     'Voucher tidak dapat diuangkan.',
//                     'Voucher belum termasuk pajak.',
//                     'Voucher hanya dapat digunakan untuk makan di tempat (dine-in).',
//                     'Voucher tidak dapat digabungkan dengan promosi lainnya.',
//                     'Voucher hanya bisa digunakan di Waroeng Depe (Jl. Raya Darmo Permai III).',
//                     'Voucher hanya berlaku selama 3 bulan dari tanggal yang telah diterbitkan.',
//                     'Voucher hanya dapat digunakan dengan minimum pembelian 2 (dua) item makanan dan/atau snack dalam satu transaksi.',
//                     'Waroeng Depe tidak bertanggung jawab atas voucher yang dicuri, hilang, atau rusak.',
//                     'Syarat dan ketentuan dapat berubah sewaktu-waktu tanpa pemberitahuan sebelumnya.',
//                 ],
//             ];

//         return [
//             [
//                 'slug'             => 'back2eras',
//                 'name'             => 'Back2eras',
//                 'prefix'           => 'B2E',
//                 'start_sequence'   => $startSequence,
//                 'max_sequence'     => $maxSequence,
//                 'pad_length'       => 3,
//                 'max_quota'        => $maxQuota,
//                 'category'         => 'event',
//                 'location'         => 'Event BACK2ERAS (27 - 29 November 2026)',
//                 'phone'            => null,
//                 'instagram'        => null,
//                 'website'          => null,
//                 'promo_highlight'  => $role === 'visitor' ? 'Minimum pembelian Rp100.000 dalam 1 transaksi' : null,
//                 'special_menus'    => [],
//                 'validity_label'   => '27 - 29 November 2026 (Selama acara berlangsung)',
//                 'fixed_expires_at' => '2026-11-29 23:59:59',
//                 'validity_months'  => null,
//                 'rule_summary'     => $back2erasSummary,
//                 'terms'            => $back2erasTerms,
//             ],
//             [
//                 'slug'             => 'solher',
//                 'name'             => 'Solher',
//                 'prefix'           => 'SLH',
//                 'start_sequence'   => $startSequence,
//                 'max_sequence'     => $maxSequence,
//                 'pad_length'       => 3,
//                 'max_quota'        => $maxQuota,
//                 'category'         => 'event',
//                 'location'         => 'Event BACK2ERAS (27 - 29 November 2026)',
//                 'phone'            => '0888 3888 585',
//                 'instagram'        => '@Solher.official',
//                 'website'          => 'www.solher.co.id',
//                 'promo_highlight'  => 'Special Price Rp399.000 (dari Rp1.448.000) untuk Party Bag',
//                 'special_menus'    => [],
//                 'validity_label'   => 'Berlaku selama 27 - 29 November 2026 di Acara Back2Era',
//                 'fixed_expires_at' => '2026-11-29 23:59:59',
//                 'validity_months'  => null,
//                 'rule_summary'     => 'Special price Rp399.000 (dari Rp1.448.000) untuk party bag selama 27 - 29 November 2026 di Acara Back2Era. Berlaku 1x redeem.',
//                 'terms'            => [
//                     'Voucher tidak dapat diuangkan.',
//                     'Voucher hanya bisa di-redeem satu kali.',
//                     'Voucher berlaku selama 27 - 29 November 2026 di Acara Back2Era.',
//                     'Voucher tidak dapat digabungkan dengan promosi lainnya.',
//                     'Mendapatkan special price Rp399.000 dari harga Rp1.448.000 untuk party bag.',
//                     'Syarat dan ketentuan dapat berubah sewaktu-waktu tanpa pemberitahuan sebelumnya.',
//                 ],
//             ],
//             [
//                 'slug'             => 'gycora',
//                 'name'             => 'Gycora',
//                 'prefix'           => 'GYC',
//                 'start_sequence'   => $startSequence,
//                 'max_sequence'     => $maxSequence,
//                 'pad_length'       => 3,
//                 'max_quota'        => $maxQuota,
//                 'category'         => 'event',
//                 'location'         => 'Event BACK2ERAS (27 - 29 November 2026)',
//                 'phone'            => null,
//                 'instagram'        => null,
//                 'website'          => null,
//                 'promo_highlight'  => null,
//                 'special_menus'    => [],
//                 'validity_label'   => 'Berlaku selama event BACK2ERAS (27 - 29 November 2026)',
//                 'fixed_expires_at' => '2026-11-29 23:59:59',
//                 'validity_months'  => null,
//                 'rule_summary'     => 'Hanya berlaku valid untuk event BACK2ERAS. Tidak dapat diuangkan atau digabungkan dengan promo lainnya.',
//                 'terms'            => [
//                     'Voucher tidak dapat diuangkan atau digabungkan dengan promo lainnya.',
//                     'Voucher hanya berlaku valid untuk event BACK2ERAS.',
//                     'Gycora Essence tidak bertanggung jawab atas voucher yang dicuri, hilang, atau rusak/robek.',
//                 ],
//             ],
//             [
//                 'slug'             => 'glamoire',
//                 'name'             => 'Glamoire',
//                 'prefix'           => 'GLM',
//                 'start_sequence'   => $startSequence,
//                 'max_sequence'     => $maxSequence,
//                 'pad_length'       => 3,
//                 'max_quota'        => $maxQuota,
//                 'category'         => 'event',
//                 'location'         => 'Event BACK2ERAS (27 - 29 November 2026)',
//                 'phone'            => null,
//                 'instagram'        => null,
//                 'website'          => null,
//                 'promo_highlight'  => null,
//                 'special_menus'    => [],
//                 'validity_label'   => 'Berlaku selama event BACK2ERAS (27 - 29 November 2026)',
//                 'fixed_expires_at' => '2026-11-29 23:59:59',
//                 'validity_months'  => null,
//                 'rule_summary'     => 'Hanya berlaku valid untuk event BACK2ERAS. Tidak dapat diuangkan atau digabungkan dengan promo lainnya.',
//                 'terms'            => [
//                     'Voucher tidak dapat diuangkan atau digabungkan dengan promo lainnya.',
//                     'Voucher hanya berlaku valid untuk event BACK2ERAS.',
//                     'Glamoire tidak bertanggung jawab atas voucher yang dicuri, hilang, atau rusak/robek.',
//                 ],
//             ],
//             $wdpConfig,
//             [
//                 'slug'             => 'kecilung',
//                 'name'             => 'Kecilung',
//                 'prefix'           => 'KCL',
//                 'start_sequence'   => $startSequence,
//                 'max_sequence'     => $maxSequence,
//                 'pad_length'       => 3,
//                 'max_quota'        => $maxQuota,
//                 'category'         => 'dine_in',
//                 'location'         => 'The Kecilung (Jl. Kecilung No. 8)',
//                 'phone'            => '0818 0311 6688',
//                 'instagram'        => '@kecilungresto',
//                 'website'          => null,
//                 'promo_highlight'  => null,
//                 'special_menus'    => [],
//                 'validity_label'   => 'Berlaku 3 bulan dari tanggal terbit (Dine-in)',
//                 'fixed_expires_at' => null,
//                 'validity_months'  => 3,
//                 'rule_summary'     => 'Khusus dine-in di The Kecilung (Jl. Kecilung No. 8). Berlaku 3 bulan sejak tanggal terbit, belum termasuk pajak.',
//                 'terms'            => [
//                     'Voucher tidak dapat diuangkan.',
//                     'Voucher belum termasuk pajak.',
//                     'Voucher hanya dapat digunakan untuk makan di tempat (dine-in).',
//                     'Voucher tidak dapat digabungkan dengan promosi lainnya.',
//                     'Voucher hanya bisa digunakan di The Kecilung (Jl. Kecilung No. 8).',
//                     'Voucher hanya berlaku selama 3 bulan dari tanggal yang telah diterbitkan.',
//                     'Voucher tidak dapat digunakan untuk cemilan, minuman kaleng, dan konsinyasi.',
//                     'Kecilung tidak bertanggung jawab atas voucher yang dicuri, hilang, atau rusak.',
//                     'Syarat dan ketentuan dapat berubah sewaktu-waktu tanpa pemberitahuan sebelumnya.',
//                 ],
//             ],
//             [
//                 'slug'             => 'malaijia',
//                 'name'             => 'Malaijia',
//                 'prefix'           => 'MLJ',
//                 'start_sequence'   => $startSequence,
//                 'max_sequence'     => $maxSequence,
//                 'pad_length'       => 3,
//                 'max_quota'        => $maxQuota,
//                 'category'         => 'dine_in',
//                 'location'         => 'Kopitiam Ma Lai Jia (Jl. Kecilung No. 8, Surabaya)',
//                 'phone'            => '0897-4170-999',
//                 'instagram'        => '@malaijia.kopitiam',
//                 'website'          => null,
//                 'promo_highlight'  => null,
//                 'special_menus'    => [],
//                 'validity_label'   => 'Berlaku 3 bulan dari tanggal terbit (Dine-in)',
//                 'fixed_expires_at' => null,
//                 'validity_months'  => 3,
//                 'rule_summary'     => 'Khusus dine-in di Kopitiam Ma Lai Jia (Jl. Kecilung No. 8, Surabaya). Tidak dapat diuangkan atau digabung promo lain.',
//                 'terms'            => [
//                     'Voucher tidak dapat diuangkan.',
//                     'Voucher hanya dapat digunakan untuk makan di tempat (dine-in).',
//                     'Voucher tidak dapat digabungkan dengan promosi lainnya.',
//                     'Voucher hanya bisa digunakan di Kopitiam Ma Lai Jia (Jl. Kecilung No. 8, Surabaya).',
//                     'The Kecilung tidak bertanggung jawab atas voucher yang dicuri, hilang, atau rusak.',
//                     'Syarat dan ketentuan dapat berubah sewaktu-waktu tanpa pemberitahuan sebelumnya.',
//                 ],
//             ],
//         ];
//     }

//     /**
//      * Konfigurasi lama Goodie Bag (dipertahankan hanya untuk kompatibilitas data lama di Admin).
//      */
//     private function getTalkshowGoodieBagConfig(): array
//     {
//         return [
//             'slug'             => 'talkshow-goodiebag',
//             'name'             => 'Free Goodie Bag (Talkshow Top 5)',
//             'prefix'           => 'GBTS',
//             'start_sequence'   => 1,
//             'max_sequence'     => self::MAX_SEQUENCE,
//             'pad_length'       => 3,
//             'max_quota'        => self::MAX_SEQUENCE,
//             'category'         => 'event',
//             'location'         => 'Booth Registrasi BACK2ERAS (27 - 29 November 2026)',
//             'phone'            => null,
//             'instagram'        => null,
//             'website'          => null,
//             'promo_highlight'  => 'Eksklusif 5 Pendaftar Pertama Talkshow (Approved)',
//             'special_menus'    => [],
//             'validity_label'   => 'Khusus 5 Pendaftar Pertama Talkshow (Approved)',
//             'fixed_expires_at' => '2026-11-29 23:59:59',
//             'validity_months'  => null,
//             'rule_summary'     => 'Voucher eksklusif Free Goodie Bag khusus 5 peserta pertama Talkshow yang telah disetujui admin.',
//             'terms'            => [
//                 'Khusus untuk 5 pendaftar pertama Talkshow yang status pendaftarannya telah disetujui (Approved) oleh admin.',
//                 'Tunjukkan kode unik ini saat check-in di lokasi acara BACK2ERAS (27 - 29 November 2026).',
//                 'Berlaku 1x klaim per akun dan tidak dapat diuangkan atau dipindahtangankan.',
//                 'Pihak BACK2ERAS tidak bertanggung jawab atas voucher yang hilang atau disalahgunakan.',
//             ],
//         ];
//     }

//     private function getAllBrandsConfig(string $role = 'visitor'): array
//     {
//         return array_merge([$this->getTalkshowGoodieBagConfig()], $this->getBrandsConfig($role));
//     }

//     /**
//      * Hitung tanggal kedaluwarsa berdasarkan aturan masing-masing brand dan role user.
//      */
//     private function resolveExpiresAt(array $brandConfig, Carbon $issuedAt): Carbon
//     {
//         if (!empty($brandConfig['fixed_expires_at'])) {
//             return Carbon::parse($brandConfig['fixed_expires_at'], 'Asia/Jakarta');
//         }

//         $months = $brandConfig['validity_months'] ?? 3;
//         return $issuedAt->copy()->addMonths($months);
//     }

//     /**
//      * Menentukan expired efektif untuk voucher yang sudah terbit.
//      */
//     private function resolveEffectiveExpiresAt(?array $brandConfig, ?Voucher $voucher, Carbon $now): ?Carbon
//     {
//         if ($brandConfig && !empty($brandConfig['fixed_expires_at'])) {
//             return Carbon::parse($brandConfig['fixed_expires_at'], 'Asia/Jakarta');
//         }

//         if ($voucher && $voucher->expires_at) {
//             return $voucher->expires_at->copy()->timezone('Asia/Jakarta');
//         }

//         if ($voucher && $brandConfig) {
//             $issuedAt = $voucher->created_at?->copy()->timezone('Asia/Jakarta') ?? $now;
//             return $this->resolveExpiresAt($brandConfig, $issuedAt);
//         }

//         return null;
//     }

//     /**
//      * Ambil angka urut tertinggi saat ini untuk brand berdasarkan kelompok role:
//      * - Talkshow      : rentang 101 - 999 (hanya membaca kode milik role talkshow)
//      * - Visitor & KOL : rentang 001 - 999 (mengabaikan kode milik role talkshow agar tidak loncat ke 102)
//      */
//     private function getHighestSequenceForBrand(array $brandConfig, string $role = 'visitor', bool $lock = false): int
//     {
//         $query = Voucher::with('user:id,role')
//             ->where('brand_slug', $brandConfig['slug']);

//         if ($lock) {
//             $query->lockForUpdate();
//         }

//         $vouchers = $query->get(['id', 'user_id', 'code']);
//         $prefix = $brandConfig['prefix'];
//         $startSeq = (int) $brandConfig['start_sequence'];
//         $maxSeqAllowed = (int) ($brandConfig['max_sequence'] ?? self::MAX_SEQUENCE);
//         $isTalkshow = $this->normalizeRole($role) === 'talkshow';
//         $maxSeq = $startSeq - 1;

//         foreach ($vouchers as $voucher) {
//             $ownerIsTalkshow = $this->normalizeRole($voucher->user?->role) === 'talkshow';
//             if ($ownerIsTalkshow !== $isTalkshow) {
//                 continue;
//             }

//             if (preg_match('/^' . preg_quote($prefix, '/') . '(\d{3})$/', $voucher->code, $matches)) {
//                 $num = (int) $matches[1];
//                 if ($num >= $startSeq && $num <= $maxSeqAllowed && $num > $maxSeq) {
//                     $maxSeq = $num;
//                 }
//             }
//         }

//         return $maxSeq;
//     }

//     /**
//      * Hitung total kode yang sudah terpakai pada rentang urutan brand (001-999 atau 101-999)
//      * agar tampilan sisa kuota akurat tanpa terpengaruh selisih nomor urut antar-role.
//      */
//     private function countUsedCodesInRange(array $brandConfig): int
//     {
//         $prefix = $brandConfig['prefix'];
//         $startSeq = (int) $brandConfig['start_sequence'];
//         $maxSeqAllowed = (int) ($brandConfig['max_sequence'] ?? self::MAX_SEQUENCE);

//         $codes = Voucher::where('brand_slug', $brandConfig['slug'])->pluck('code');
//         $usedCount = 0;

//         foreach ($codes as $code) {
//             if (preg_match('/^' . preg_quote($prefix, '/') . '(\d{3})$/', $code, $matches)) {
//                 $num = (int) $matches[1];
//                 if ($num >= $startSeq && $num <= $maxSeqAllowed) {
//                     $usedCount++;
//                 }
//             }
//         }

//         return $usedCount;
//     }

//     /**
//      * Bentuk string kode voucher format [PREFIX][001-999] / [PREFIX][101-999].
//      */
//     private function formatVoucherCode(array $brandConfig, int $sequence): string
//     {
//         $padLength = (int) ($brandConfig['pad_length'] ?? 3);
//         return $brandConfig['prefix'] . str_pad((string) $sequence, $padLength, '0', STR_PAD_LEFT);
//     }

//     // PROTECTED: Menampilkan daftar brand + status klaim & S&K sesuai role/tier user
//     public function index(Request $request)
//     {
//         $user = $request->user();
//         $role = $this->normalizeRole($user->role);
//         $now = Carbon::now('Asia/Jakarta');

//         $claimedMap = Voucher::where('user_id', $user->id)
//             ->get()
//             ->keyBy('brand_slug');

//         $eligibility = $this->checkVoucherEligibility($user);

//         $brandConfigs = collect($this->getBrandsConfig($role));

//         // Peserta talkshow urutan 6-25: sembunyikan voucher Back2eras
//         if (is_array($eligibility['allowed_slugs']) && count($eligibility['allowed_slugs']) > 0) {
//             $brandConfigs = $brandConfigs->filter(
//                 fn ($b) => in_array($b['slug'], $eligibility['allowed_slugs'], true)
//             )->values();
//         }

//         $brands = $brandConfigs->map(function ($brand) use ($claimedMap, $now) {
//             $claimed = $claimedMap->get($brand['slug']);
//             $maxQuota = (int) ($brand['max_quota'] ?? self::MAX_SEQUENCE);
//             $usedInRange = $this->countUsedCodesInRange($brand);
//             $remainingQuota = max(0, $maxQuota - $usedInRange);

//             $issuedAt = $claimed?->created_at?->copy()->timezone('Asia/Jakarta');
//             $expiresAt = $this->resolveEffectiveExpiresAt($brand, $claimed, $now);

//             $isEventClosed = !empty($brand['fixed_expires_at'])
//                 && $now->gt(Carbon::parse($brand['fixed_expires_at'], 'Asia/Jakarta'));

//             $isExpired = $claimed && $expiresAt ? $now->gt($expiresAt) : $isEventClosed;

//             return [
//                 'slug'             => $brand['slug'],
//                 'name'             => $brand['name'],
//                 'prefix'           => $brand['prefix'],
//                 'code_placeholder' => $brand['prefix'] . str_repeat('-', (int) $brand['pad_length']),
//                 'category'         => $brand['category'],
//                 'location'         => $brand['location'],
//                 'phone'            => $brand['phone'],
//                 'instagram'        => $brand['instagram'],
//                 'website'          => $brand['website'] ?? null,
//                 'promo_highlight'  => $brand['promo_highlight'] ?? null,
//                 'special_menus'    => $brand['special_menus'] ?? [],
//                 'validity_label'   => $brand['validity_label'],
//                 'rule_summary'     => $brand['rule_summary'],
//                 'terms'            => $brand['terms'],
//                 'max_quota'        => $maxQuota,
//                 'remaining_quota'  => $remainingQuota,
//                 'claimed'          => (bool) $claimed,
//                 'code'             => $claimed?->code,
//                 'is_used'          => (bool) ($claimed?->is_used ?? false),
//                 'is_expired'       => $isExpired,
//                 'claimed_at'       => $issuedAt?->toIso8601String(),
//                 'expires_at'       => $expiresAt?->toIso8601String(),
//             ];
//         })->values();

//         return response()->json([
//             'user_role'              => $role,
//             'can_claim_voucher'      => $eligibility['eligible'],
//             'claim_block_reason'     => $eligibility['reason'],
//             'talkshow_rank'          => $eligibility['talkshow_rank'],
//             'has_talkshow_goodiebag' => $eligibility['include_goodiebag'],
//             'brands'                 => $brands,
//         ]);
//     }

//     // PROTECTED: Klaim kode unik (Visitor/KOL: 001-999, Talkshow: 101-999; 1x per user per brand)
//     public function claim(Request $request)
//     {
//         $validated = $request->validate([
//             'brand_slug' => 'required|string',
//         ]);

//         if ($validated['brand_slug'] === 'talkshow-goodiebag') {
//             return response()->json([
//                 'message' => 'Free Goodie Bag khusus 5 pendaftar pertama Talkshow tidak memerlukan klaim kode atau redeem. Cukup tunjukkan akun Anda saat check-in di booth registrasi.',
//             ], 422);
//         }

//         $user = $request->user();
//         $role = $this->normalizeRole($user->role);

//         // Validasi kelayakan KOL (link medsos) & Talkshow (wajib approved)
//         $eligibility = $this->checkVoucherEligibility($user);
//         if (!$eligibility['eligible']) {
//             return response()->json([
//                 'message' => $eligibility['reason'],
//             ], 403);
//         }

//         $brandConfig = collect($this->getBrandsConfig($role))->firstWhere('slug', $validated['brand_slug']);

//         if (!$brandConfig) {
//             return response()->json(['message' => 'Brand tidak ditemukan.'], 404);
//         }

//         // Cegah peserta Talkshow urutan 6-25 mengklaim Back2eras
//         if (
//             is_array($eligibility['allowed_slugs'])
//             && !in_array($brandConfig['slug'], $eligibility['allowed_slugs'], true)
//         ) {
//             return response()->json([
//                 'message' => 'Peserta Talkshow urutan 6 - 25 hanya berhak mengklaim voucher partner (tidak termasuk voucher Back2eras).',
//             ], 403);
//         }

//         $now = Carbon::now('Asia/Jakarta');

//         // Validasi periode event untuk brand dengan fixed_expires_at (termasuk WDP khusus KOL)
//         if (!empty($brandConfig['fixed_expires_at'])) {
//             $eventEnd = Carbon::parse($brandConfig['fixed_expires_at'], 'Asia/Jakarta');
//             if ($now->gt($eventEnd)) {
//                 return response()->json([
//                     'message' => 'Periode acara BACK2ERAS (27 - 29 November 2026) telah berakhir. Voucher ' . $brandConfig['name'] . ' sudah tidak dapat diklaim.',
//                 ], 422);
//             }
//         }

//         $maxSeqAllowed = (int) ($brandConfig['max_sequence'] ?? self::MAX_SEQUENCE);
//         $rangeLabel = str_pad((string) $brandConfig['start_sequence'], 3, '0', STR_PAD_LEFT)
//             . ' - '
//             . str_pad((string) $maxSeqAllowed, 3, '0', STR_PAD_LEFT);

//         $result = DB::transaction(function () use ($user, $role, $brandConfig, $now, $maxSeqAllowed) {
//             $existing = Voucher::where('user_id', $user->id)
//                 ->where('brand_slug', $brandConfig['slug'])
//                 ->lockForUpdate()
//                 ->first();

//             if ($existing) {
//                 return [
//                     'status'  => 'already_claimed',
//                     'voucher' => $existing,
//                 ];
//             }

//             $highestSeq = $this->getHighestSequenceForBrand($brandConfig, $role, true);
//             $nextSeq = $highestSeq + 1;

//             // Lewati kode jika sudah terpakai oleh role lain agar tidak pernah bentrok unique key
//             while (
//                 $nextSeq <= $maxSeqAllowed
//                 && Voucher::where('code', $this->formatVoucherCode($brandConfig, $nextSeq))->exists()
//             ) {
//                 $nextSeq++;
//             }

//             if ($nextSeq > $maxSeqAllowed) {
//                 return [
//                     'status' => 'quota_exceeded',
//                 ];
//             }

//             $uniqueCode = $this->formatVoucherCode($brandConfig, $nextSeq);
//             $expiresAt = $this->resolveExpiresAt($brandConfig, $now);

//             $voucher = Voucher::create([
//                 'user_id'    => $user->id,
//                 'brand_slug' => $brandConfig['slug'],
//                 'brand_name' => $brandConfig['name'],
//                 'code'       => $uniqueCode,
//                 'is_used'    => false,
//                 'expires_at' => $expiresAt,
//             ]);

//             return [
//                 'status'  => 'created',
//                 'voucher' => $voucher,
//             ];
//         });

//         if ($result['status'] === 'already_claimed') {
//             return response()->json([
//                 'message' => 'Anda sudah mengklaim voucher untuk ' . $brandConfig['name'] . '.',
//                 'voucher' => $result['voucher'],
//             ], 422);
//         }

//         if ($result['status'] === 'quota_exceeded') {
//             return response()->json([
//                 'message' => 'Maaf, kuota voucher ' . $brandConfig['name'] . " ({$rangeLabel}) sudah habis.",
//             ], 422);
//         }

//         return response()->json([
//             'success' => true,
//             'message' => 'Kode unik ' . $brandConfig['name'] . ' berhasil diklaim!',
//             'voucher' => $result['voucher'],
//         ], 201);
//     }

//     /**
//      * Helper format payload voucher lengkap untuk kebutuhan Admin / Kasir,
//      * otomatis menyesuaikan aturan & menu berdasarkan role pemilik voucher.
//      */
//     private function formatAdminVoucher(Voucher $voucher, Carbon $now): array
//     {
//         $ownerRole = $this->normalizeRole($voucher->user?->role);
//         $brandConfig = collect($this->getAllBrandsConfig($ownerRole))->firstWhere('slug', $voucher->brand_slug);

//         $issuedAt = $voucher->created_at?->copy()->timezone('Asia/Jakarta');
//         $expiresAt = $this->resolveEffectiveExpiresAt($brandConfig, $voucher, $now);
//         $usedAt = $voucher->used_at?->copy()->timezone('Asia/Jakarta');
//         $isExpired = $expiresAt ? $now->gt($expiresAt) : false;

//         $status = 'unused';
//         if ($voucher->is_used) {
//             $status = 'used';
//         } elseif ($isExpired) {
//             $status = 'expired';
//         }

//         return [
//             'id'              => $voucher->id,
//             'code'            => $voucher->code,
//             'brand_slug'      => $voucher->brand_slug,
//             'brand_name'      => $voucher->brand_name,
//             'location'        => $brandConfig['location'] ?? '-',
//             'promo_highlight' => $brandConfig['promo_highlight'] ?? null,
//             'special_menus'   => $brandConfig['special_menus'] ?? [],
//             'terms'           => $brandConfig['terms'] ?? [],
//             'is_used'         => (bool) $voucher->is_used,
//             'is_expired'      => $isExpired,
//             'can_redeem'      => !$voucher->is_used && !$isExpired,
//             'status'          => $status,
//             'claimed_at'      => $issuedAt?->toIso8601String(),
//             'expires_at'      => $expiresAt?->toIso8601String(),
//             'used_at'         => $usedAt?->toIso8601String(),
//             'user'            => [
//                 'id'       => $voucher->user?->id,
//                 'name'     => $voucher->user?->name ?? '-',
//                 'email'    => $voucher->user?->email ?? '-',
//                 'role'     => $ownerRole,
//                 'whatsapp' => $voucher->user?->visitor?->whatsapp
//                     ?? $voucher->user?->kol?->whatsapp
//                     ?? $voucher->user?->talkshowRegistrations?->first()?->phone
//                     ?? '-',
//             ],
//         ];
//     }

//     // ADMIN: Menampilkan seluruh daftar voucher beserta ringkasan statistik
//     public function adminIndex(Request $request)
//     {
//         $now = Carbon::now('Asia/Jakarta');

//         $vouchers = Voucher::with(['user.visitor', 'user.kol', 'user.talkshowRegistrations'])
//             ->orderBy('created_at', 'desc')
//             ->get()
//             ->map(fn (Voucher $v) => $this->formatAdminVoucher($v, $now));

//         $stats = [
//             'total'   => $vouchers->count(),
//             'unused'  => $vouchers->where('status', 'unused')->count(),
//             'used'    => $vouchers->where('status', 'used')->count(),
//             'expired' => $vouchers->where('status', 'expired')->count(),
//         ];

//         return response()->json([
//             'stats'    => $stats,
//             'vouchers' => $vouchers->values(),
//         ]);
//     }

//     // ADMIN: Cek / Verifikasi kode unik sebelum di-redeem
//     public function adminVerify(Request $request)
//     {
//         $validated = $request->validate([
//             'code' => 'required|string|max:50',
//         ]);

//         $code = strtoupper(trim($validated['code']));
//         $now = Carbon::now('Asia/Jakarta');

//         $voucher = Voucher::with(['user.visitor', 'user.kol', 'user.talkshowRegistrations'])
//             ->where('code', $code)
//             ->first();

//         if (!$voucher) {
//             return response()->json([
//                 'message' => "Kode voucher \"{$code}\" tidak ditemukan di sistem.",
//             ], 404);
//         }

//         return response()->json([
//             'voucher' => $this->formatAdminVoucher($voucher, $now),
//         ]);
//     }

//     // ADMIN: Tandai voucher sebagai sudah dipakai (is_used = true, used_at = now)
//     public function adminRedeem(Request $request)
//     {
//         $validated = $request->validate([
//             'code' => 'required|string|max:50',
//         ]);

//         $code = strtoupper(trim($validated['code']));
//         $now = Carbon::now('Asia/Jakarta');

//         $result = DB::transaction(function () use ($code, $now) {
//             $voucher = Voucher::with(['user.visitor', 'user.kol', 'user.talkshowRegistrations'])
//                 ->where('code', $code)
//                 ->lockForUpdate()
//                 ->first();

//             if (!$voucher) {
//                 return ['state' => 'not_found'];
//             }

//             if ($voucher->is_used) {
//                 return [
//                     'state'   => 'already_used',
//                     'voucher' => $voucher,
//                 ];
//             }

//             $ownerRole = $this->normalizeRole($voucher->user?->role);
//             $brandConfig = collect($this->getAllBrandsConfig($ownerRole))->firstWhere('slug', $voucher->brand_slug);
//             $expiresAt = $this->resolveEffectiveExpiresAt($brandConfig, $voucher, $now);

//             if ($expiresAt && $now->gt($expiresAt)) {
//                 return [
//                     'state'   => 'expired',
//                     'voucher' => $voucher,
//                 ];
//             }

//             $voucher->is_used = true;
//             $voucher->used_at = $now;
//             $voucher->save();

//             return [
//                 'state'   => 'redeemed',
//                 'voucher' => $voucher,
//             ];
//         });

//         if ($result['state'] === 'not_found') {
//             return response()->json([
//                 'message' => "Kode voucher \"{$code}\" tidak ditemukan.",
//             ], 404);
//         }

//         if ($result['state'] === 'already_used') {
//             $usedTime = $result['voucher']->used_at?->copy()->timezone('Asia/Jakarta')->format('d M Y H:i') . ' WIB';
//             return response()->json([
//                 'message' => "Voucher {$code} sudah pernah digunakan pada {$usedTime}.",
//                 'voucher' => $this->formatAdminVoucher($result['voucher'], $now),
//             ], 422);
//         }

//         if ($result['state'] === 'expired') {
//             return response()->json([
//                 'message' => "Voucher {$code} sudah kedaluwarsa dan tidak dapat digunakan.",
//                 'voucher' => $this->formatAdminVoucher($result['voucher'], $now),
//             ], 422);
//         }

//         return response()->json([
//             'success' => true,
//             'message' => "Voucher {$code} berhasil diverifikasi & ditandai sudah dipakai!",
//             'voucher' => $this->formatAdminVoucher($result['voucher'], $now),
//         ]);
//     }

//     /**
//      * Cek kelayakan voucher untuk Visitor, KOL, maupun Peserta Talkshow.
//      */
//     private function checkVoucherEligibility($user): array
//     {
//         $talkshowRegs = TalkshowRegistration::where(function ($q) use ($user) {
//             $q->where('user_id', $user->id)
//                 ->orWhere('email', $user->email);
//         })->get();

//         $approvedRanks = $talkshowRegs
//             ->where('status', 'approved')
//             ->pluck('approved_rank')
//             ->filter(fn ($rank) => $rank !== null)
//             ->values();

//         $bestApprovedRank = $approvedRanks->isNotEmpty() ? (int) $approvedRanks->min() : null;
//         $includeGoodieBag = $bestApprovedRank !== null && $bestApprovedRank <= 5;

//         // 1. Aturan khusus akun role = 'talkshow'
//         if ($user->role === 'talkshow') {
//             if ($bestApprovedRank === null) {
//                 $hasPending = $talkshowRegs->where('status', 'pending')->isNotEmpty();

//                 return [
//                     'eligible'          => false,
//                     'reason'            => $hasPending
//                         ? 'Pendaftaran Talkshow Anda masih menunggu persetujuan (Approve) dari admin. Voucher baru dapat diklaim setelah pendaftaran Anda diterima.'
//                         : 'Pendaftaran Talkshow Anda belum disetujui atau ditolak oleh admin sehingga tidak dapat mengklaim voucher.',
//                     'allowed_slugs'     => [],
//                     'include_goodiebag' => false,
//                     'talkshow_rank'     => null,
//                 ];
//             }

//             // Urutan 1 - 5: Free Goodie Bag (info) + Semua 7 voucher visitor (termasuk Back2eras)
//             if ($bestApprovedRank <= 5) {
//                 return [
//                     'eligible'          => true,
//                     'reason'            => null,
//                     'allowed_slugs'     => null,
//                     'include_goodiebag' => true,
//                     'talkshow_rank'     => $bestApprovedRank,
//                 ];
//             }

//             // Urutan 6 - 25: Semua voucher visitor KECUALI Back2eras (tanpa Goodie Bag)
//             return [
//                 'eligible'          => true,
//                 'reason'            => null,
//                 'allowed_slugs'     => ['solher', 'gycora', 'glamoire', 'wdp', 'kecilung', 'malaijia'],
//                 'include_goodiebag' => false,
//                 'talkshow_rank'     => $bestApprovedRank,
//             ];
//         }

//         // 2. Aturan khusus akun role = 'kol'
//         if ($user->role === 'kol') {
//             $kol = Kol::where('user_id', $user->id)
//                 ->orWhere('email', $user->email)
//                 ->first();

//             $hasSocialLink = !empty(trim((string) ($kol?->social_media_link ?? '')));

//             if (!$hasSocialLink) {
//                 return [
//                     'eligible'          => false,
//                     'reason'            => 'Akun KOL wajib mengisi Link Media Sosial terlebih dahulu agar dapat mengklaim voucher.',
//                     'allowed_slugs'     => null,
//                     'include_goodiebag' => $includeGoodieBag,
//                     'talkshow_rank'     => $bestApprovedRank,
//                 ];
//             }
//         }

//         // 3. Default: Visitor (plus bonus Goodie Bag jika juga approved 1-5 di Talkshow)
//         return [
//             'eligible'          => true,
//             'reason'            => null,
//             'allowed_slugs'     => null,
//             'include_goodiebag' => $includeGoodieBag,
//             'talkshow_rank'     => $bestApprovedRank,
//         ];
//     }
// }

namespace App\Http\Controllers;

use App\Models\Kol;
use App\Models\TalkshowRegistration;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VoucherController extends Controller
{
    private const MAX_SEQUENCE = 999;
    private const REGULAR_START_SEQUENCE = 1;
    private const TALKSHOW_START_SEQUENCE = 101;
    private const TALKSHOW_ALLOWED_SLUGS = [
        'solher',
        'gycora',
        'glamoire',
        'wdp',
        'kecilung',
        'malaijia',
    ];

    private function normalizeRole(?string $role): string
    {
        return match ($role) {
            'kol'      => 'kol',
            'talkshow' => 'talkshow',
            default    => 'visitor',
        };
    }

    /**
     * Konfigurasi 7 Brand berdasarkan konteks role ('visitor' / 'kol' / 'talkshow').
     * Prefix huruf dan aturan bisnis tetap sama; kode Talkshow selalu mulai 101 - 999.
     */
    private function getBrandsConfig(string $contextRole = 'visitor'): array
    {
        $role = $this->normalizeRole($contextRole);
        $startSequence = $role === 'talkshow'
            ? self::TALKSHOW_START_SEQUENCE
            : self::REGULAR_START_SEQUENCE;
        $maxSequence = self::MAX_SEQUENCE;
        $maxQuota = $maxSequence - $startSequence + 1;

        // --- A. BACK2ERAS (Visitor memiliki syarat minimum pembelian Rp100.000) ---
        // $back2erasTerms = $role === 'visitor'
        //     ? [
        //         'Voucher tidak dapat diuangkan.',
        //         'Voucher hanya bisa digunakan pada acara BACK2ERAS (27 - 29 November 2026).',
        //         'Voucher tidak dapat digabungkan dengan promosi lainnya.',
        //         'Voucher hanya berlaku selama acara berlangsung saja.',
        //         'Pihak BACK2ERAS tidak bertanggung jawab atas voucher yang dicuri, hilang, atau rusak.',
        //         'Minimum pembelian Rp100.000 dalam satu transaksi untuk dapat mengklaim.',
        //         'Syarat & ketentuan dapat berubah sewaktu-waktu tanpa pemberitahuan sebelumnya.',
        //     ]
        //     : [
        //         'Voucher tidak dapat diuangkan.',
        //         'Voucher hanya bisa digunakan pada acara BACK2ERAS (27 - 29 November 2026).',
        //         'Voucher tidak dapat digabungkan dengan promosi lainnya.',
        //         'Voucher hanya berlaku selama acara berlangsung saja.',
        //         'Pihak BACK2ERAS tidak bertanggung jawab atas voucher yang dicuri, hilang, atau rusak.',
        //         'Syarat & ketentuan dapat berubah sewaktu-waktu tanpa pemberitahuan sebelumnya.',
        //     ];

        // $back2erasSummary = $role === 'visitor'
        //     ? 'Berlaku selama acara BACK2ERAS (27 - 29 November 2026). Minimum pembelian Rp100.000 dalam satu transaksi.'
        //     : 'Berlaku selama acara BACK2ERAS (27 - 29 November 2026). Tidak dapat diuangkan atau digabungkan dengan promo lain.';

        // --- A. BACK2ERAS (Visitor: min. Rp100.000 | KOL: Hadiah Goodie Bag + Disc 10% min. Rp50.000) ---
        $back2erasPromoHighlight = match ($role) {
            'visitor' => 'Minimum pembelian Rp100.000 dalam 1 transaksi',
            'kol'     => 'Hadiah Goodie Bag + Disc 10% (Minimal belanja Rp50.000)',
            default   => null,
        };

        $back2erasTerms = match ($role) {
            'visitor' => [
                'Voucher tidak dapat diuangkan.',
                'Voucher hanya bisa digunakan pada acara BACK2ERAS (27 - 29 November 2026).',
                'Voucher tidak dapat digabungkan dengan promosi lainnya.',
                'Voucher hanya berlaku selama acara berlangsung saja.',
                'Pihak BACK2ERAS tidak bertanggung jawab atas voucher yang dicuri, hilang, atau rusak.',
                'Minimum pembelian Rp100.000 dalam satu transaksi untuk dapat mengklaim.',
                'Syarat & ketentuan dapat berubah sewaktu-waktu tanpa pemberitahuan sebelumnya.',
            ],
            'kol' => [
                'Khusus KOL: Hadiah Goodie Bag dari voucher BACK2ERAS + Disc 10% dengan minimal belanja Rp50.000 dalam satu transaksi.',
                'Voucher tidak dapat diuangkan.',
                'Voucher hanya bisa digunakan pada acara BACK2ERAS (27 - 29 November 2026).',
                'Voucher tidak dapat digabungkan dengan promosi lainnya.',
                'Voucher hanya berlaku selama acara berlangsung saja.',
                'Pihak BACK2ERAS tidak bertanggung jawab atas voucher yang dicuri, hilang, atau rusak.',
                'Syarat & ketentuan dapat berubah sewaktu-waktu tanpa pemberitahuan sebelumnya.',
            ],
            default => [
                'Voucher tidak dapat diuangkan.',
                'Voucher hanya bisa digunakan pada acara BACK2ERAS (27 - 29 November 2026).',
                'Voucher tidak dapat digabungkan dengan promosi lainnya.',
                'Voucher hanya berlaku selama acara berlangsung saja.',
                'Pihak BACK2ERAS tidak bertanggung jawab atas voucher yang dicuri, hilang, atau rusak.',
                'Syarat & ketentuan dapat berubah sewaktu-waktu tanpa pemberitahuan sebelumnya.',
            ],
        };

        $back2erasSummary = match ($role) {
            'visitor' => 'Berlaku selama acara BACK2ERAS (27 - 29 November 2026). Minimum pembelian Rp100.000 dalam satu transaksi.',
            'kol'     => 'Khusus KOL: Hadiah Goodie Bag dari voucher BACK2ERAS + Disc 10% dengan minimal belanja Rp50.000 selama acara berlangsung (27 - 29 November 2026).',
            default   => 'Berlaku selama acara BACK2ERAS (27 - 29 November 2026). Tidak dapat diuangkan atau digabungkan dengan promo lain.',
        };

        // --- E. WDP (KOL: 27-29 Nov 2026, 1 orang, menu khusus; Visitor/Talkshow: 3 bulan, min 2 item) ---
        $isKol = $role === 'kol';

        $wdpConfig = $isKol
            // ? [
            //     'slug'             => 'wdp',
            //     'name'             => 'WDP',
            //     'prefix'           => 'WDP',
            //     'start_sequence'   => $startSequence,
            //     'max_sequence'     => $maxSequence,
            //     'pad_length'       => 3,
            //     'max_quota'        => $maxQuota,
            //     'category'         => 'dine_in',
            //     'location'         => 'Waroeng Depe (Jl. Raya Darmo Permai III)',
            //     'phone'            => '0857 7376 9690',
            //     'instagram'        => '@waroengdepesby',
            //     'website'          => null,
            //     'promo_highlight'  => 'Khusus KOL (1 Orang) • Dine-in 27 - 29 Nov 2026',
            //     'special_menus'    => [
            //         'NASI SOTO + PERKEDEL & ES TEH',
            //         'NASI SEMARANG & ES CAO',
            //     ],
            //     'validity_label'   => 'Berlaku tanggal 27 - 29 November 2026 (Dine-in • 1 Orang)',
            //     'fixed_expires_at' => '2026-11-29 23:59:59',
            //     'validity_months'  => null,
            //     'rule_summary'     => 'Voucher khusus KOL untuk 1 orang (dine-in di Waroeng Depe, Jl. Raya Darmo Permai III) pada 27 - 29 November 2026.',
            //     'terms'            => [
            //         'Voucher tidak dapat diuangkan.',
            //         'Voucher hanya berlaku untuk 1 orang.',
            //         'Voucher hanya dapat digunakan untuk makan di tempat (dine-in).',
            //         'Voucher tidak dapat digabungkan dengan promosi lainnya.',
            //         'Voucher hanya bisa digunakan di Waroeng Depe (Jl. Raya Darmo Permai III).',
            //         'Voucher hanya berlaku tanggal 27 - 29 November 2026.',
            //         'Waroeng Depe tidak bertanggung jawab atas voucher yang dicuri, hilang, atau rusak.',
            //         'Syarat dan ketentuan dapat berubah sewaktu-waktu tanpa pemberitahuan sebelumnya.',
            //     ],
            // ]
            ? [
                'slug'             => 'wdp',
                'name'             => 'WDP',
                'prefix'           => 'WDP',
                'start_sequence'   => $startSequence,
                'max_sequence'     => $maxSequence,
                'pad_length'       => 3,
                'max_quota'        => $maxQuota,
                'category'         => 'dine_in',
                'location'         => 'Waroeng Depe (Jl. Raya Darmo Permai III)',
                'phone'            => '0857 7376 9690',
                'instagram'        => '@waroengdepesby',
                'website'          => null,
                'promo_highlight'  => 'Khusus KOL (1 Orang) • Hanya berlaku 27 - 29 November 2026 saja',
                'special_menus'    => [
                    'NASI SOTO + PERKEDEL & ES TEH',
                    'NASI SEMARANG & ES CAO',
                ],
                'validity_label'   => 'Hanya berlaku pada 27 - 29 November 2026 saja (Dine-in • 1 Orang)',
                'fixed_expires_at' => '2026-11-29 23:59:59',
                'validity_months'  => null,
                'rule_summary'     => 'Voucher khusus KOL untuk 1 orang (dine-in di Waroeng Depe, Jl. Raya Darmo Permai III). Hanya berlaku pada tanggal 27 - 29 November 2026 saja.',
                'terms'            => [
                    'KHUSUS KOL: Voucher ini HANYA berlaku pada tanggal 27 - 29 November 2026 saja (tidak berlaku di luar tanggal tersebut).',
                    'Voucher tidak dapat diuangkan.',
                    'Voucher hanya berlaku untuk 1 orang.',
                    'Voucher hanya dapat digunakan untuk makan di tempat (dine-in).',
                    'Voucher tidak dapat digabungkan dengan promosi lainnya.',
                    'Voucher hanya bisa digunakan di Waroeng Depe (Jl. Raya Darmo Permai III).',
                    'Waroeng Depe tidak bertanggung jawab atas voucher yang dicuri, hilang, atau rusak.',
                    'Syarat dan ketentuan dapat berubah sewaktu-waktu tanpa pemberitahuan sebelumnya.',
                ],
            ]
            : [
                'slug'             => 'wdp',
                'name'             => 'WDP',
                'prefix'           => 'WDP',
                'start_sequence'   => $startSequence,
                'max_sequence'     => $maxSequence,
                'pad_length'       => 3,
                'max_quota'        => $maxQuota,
                'category'         => 'dine_in',
                'location'         => 'Waroeng Depe (Jl. Raya Darmo Permai III)',
                'phone'            => '0857 7376 9690',
                'instagram'        => '@waroengdepesby',
                'website'          => null,
                'promo_highlight'  => 'Min. pembelian 2 item makanan/snack dalam 1 transaksi',
                'special_menus'    => [],
                'validity_label'   => 'Berlaku 3 bulan dari tanggal terbit (Dine-in)',
                'fixed_expires_at' => null,
                'validity_months'  => 3,
                'rule_summary'     => 'Khusus dine-in di Waroeng Depe (Jl. Raya Darmo Permai III). Berlaku 3 bulan sejak tanggal terbit, min. pembelian 2 item makanan/snack.',
                'terms'            => [
                    'Voucher tidak dapat diuangkan.',
                    'Voucher belum termasuk pajak.',
                    'Voucher hanya dapat digunakan untuk makan di tempat (dine-in).',
                    'Voucher tidak dapat digabungkan dengan promosi lainnya.',
                    'Voucher hanya bisa digunakan di Waroeng Depe (Jl. Raya Darmo Permai III).',
                    'Voucher hanya berlaku selama 3 bulan dari tanggal yang telah diterbitkan.',
                    'Voucher hanya dapat digunakan dengan minimum pembelian 2 (dua) item makanan dan/atau snack dalam satu transaksi.',
                    'Waroeng Depe tidak bertanggung jawab atas voucher yang dicuri, hilang, atau rusak.',
                    'Syarat dan ketentuan dapat berubah sewaktu-waktu tanpa pemberitahuan sebelumnya.',
                ],
            ];

        return [
            [
                'slug'             => 'back2eras',
                'name'             => 'Back2eras',
                'prefix'           => 'B2E',
                'start_sequence'   => $startSequence,
                'max_sequence'     => $maxSequence,
                'pad_length'       => 3,
                'max_quota'        => $maxQuota,
                'category'         => 'event',
                'location'         => 'Event BACK2ERAS (27 - 29 November 2026)',
                'phone'            => null,
                'instagram'        => null,
                'website'          => null,
                // 'promo_highlight'  => $role === 'visitor' ? 'Minimum pembelian Rp100.000 dalam 1 transaksi' : null,
                'promo_highlight'  => $back2erasPromoHighlight,
                'special_menus'    => [],
                'validity_label'   => '27 - 29 November 2026 (Selama acara berlangsung)',
                'fixed_expires_at' => '2026-11-29 23:59:59',
                'validity_months'  => null,
                'rule_summary'     => $back2erasSummary,
                'terms'            => $back2erasTerms,
            ],
            [
                'slug'             => 'solher',
                'name'             => 'Solher',
                'prefix'           => 'SLH',
                'start_sequence'   => $startSequence,
                'max_sequence'     => $maxSequence,
                'pad_length'       => 3,
                'max_quota'        => $maxQuota,
                'category'         => 'event',
                'location'         => 'Event BACK2ERAS (27 - 29 November 2026)',
                'phone'            => '0888 3888 585',
                'instagram'        => '@Solher.official',
                'website'          => 'www.solher.co.id',
                'promo_highlight'  => 'Special Price Rp399.000 (dari Rp1.448.000) untuk Party Bag',
                'special_menus'    => [],
                'validity_label'   => 'Berlaku selama 27 - 29 November 2026 di Acara Back2Era',
                'fixed_expires_at' => '2026-11-29 23:59:59',
                'validity_months'  => null,
                'rule_summary'     => 'Special price Rp399.000 (dari Rp1.448.000) untuk party bag selama 27 - 29 November 2026 di Acara Back2Era. Berlaku 1x redeem.',
                'terms'            => [
                    'Voucher tidak dapat diuangkan.',
                    'Voucher hanya bisa di-redeem satu kali.',
                    'Voucher berlaku selama 27 - 29 November 2026 di Acara Back2Era.',
                    'Voucher tidak dapat digabungkan dengan promosi lainnya.',
                    'Mendapatkan special price Rp399.000 dari harga Rp1.448.000 untuk party bag.',
                    'Syarat dan ketentuan dapat berubah sewaktu-waktu tanpa pemberitahuan sebelumnya.',
                ],
            ],
            [
                'slug'             => 'gycora',
                'name'             => 'Gycora',
                'prefix'           => 'GYC',
                'start_sequence'   => $startSequence,
                'max_sequence'     => $maxSequence,
                'pad_length'       => 3,
                'max_quota'        => $maxQuota,
                'category'         => 'event',
                'location'         => 'Event BACK2ERAS (27 - 29 November 2026)',
                'phone'            => null,
                'instagram'        => null,
                'website'          => null,
                'promo_highlight'  => null,
                'special_menus'    => [],
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
            // [
            //     'slug'             => 'glamoire',
            //     'name'             => 'Glamoire',
            //     'prefix'           => 'GLM',
            //     'start_sequence'   => $startSequence,
            //     'max_sequence'     => $maxSequence,
            //     'pad_length'       => 3,
            //     'max_quota'        => $maxQuota,
            //     'category'         => 'event',
            //     'location'         => 'Event BACK2ERAS (27 - 29 November 2026)',
            //     'phone'            => null,
            //     'instagram'        => null,
            //     'website'          => null,
            //     'promo_highlight'  => null,
            //     'special_menus'    => [],
            //     'validity_label'   => 'Berlaku selama event BACK2ERAS (27 - 29 November 2026)',
            //     'fixed_expires_at' => '2026-11-29 23:59:59',
            //     'validity_months'  => null,
            //     'rule_summary'     => 'Hanya berlaku valid untuk event BACK2ERAS. Tidak dapat diuangkan atau digabungkan dengan promo lainnya.',
            //     'terms'            => [
            //         'Voucher tidak dapat diuangkan atau digabungkan dengan promo lainnya.',
            //         'Voucher hanya berlaku valid untuk event BACK2ERAS.',
            //         'Glamoire tidak bertanggung jawab atas voucher yang dicuri, hilang, atau rusak/robek.',
            //     ],
            // ],

            [
                'slug'             => 'glamoire',
                'name'             => 'Glamoire',
                'prefix'           => 'GLM',
                'start_sequence'   => $startSequence,
                'max_sequence'     => $maxSequence,
                'pad_length'       => 3,
                'max_quota'        => $maxQuota,
                'category'         => 'event',
                'location'         => $isKol
                    ? 'Booth Glamoire • Event BACK2ERAS (27 - 29 November 2026)'
                    : 'Event BACK2ERAS (27 - 29 November 2026)',
                'phone'            => null,
                'instagram'        => null,
                'website'          => null,
                'promo_highlight'  => $isKol
                    ? 'Khusus KOL: Free Redeem Parfum di Booth Glamoire'
                    : null,
                'special_menus'    => [],
                'validity_label'   => 'Berlaku selama event BACK2ERAS (27 - 29 November 2026)',
                'fixed_expires_at' => '2026-11-29 23:59:59',
                'validity_months'  => null,
                'rule_summary'     => $isKol
                    ? 'Khusus KOL: Free redeem parfum di Booth Glamoire selama event BACK2ERAS (27 - 29 November 2026). Tidak dapat diuangkan atau digabungkan dengan promo lainnya.'
                    : 'Hanya berlaku valid untuk event BACK2ERAS. Tidak dapat diuangkan atau digabungkan dengan promo lainnya.',
                'terms'            => $isKol
                    ? [
                        'Khusus KOL: Berhak mendapatkan Free Redeem Parfum di Booth Glamoire selama event BACK2ERAS (27 - 29 November 2026).',
                        'Voucher tidak dapat diuangkan atau digabungkan dengan promo lainnya.',
                        'Voucher hanya berlaku valid untuk event BACK2ERAS.',
                        'Glamoire tidak bertanggung jawab atas voucher yang dicuri, hilang, atau rusak/robek.',
                    ]
                    : [
                        'Voucher tidak dapat diuangkan atau digabungkan dengan promo lainnya.',
                        'Voucher hanya berlaku valid untuk event BACK2ERAS.',
                        'Glamoire tidak bertanggung jawab atas voucher yang dicuri, hilang, atau rusak/robek.',
                    ],
            ],
            $wdpConfig,
            // [
            //     'slug'             => 'kecilung',
            //     'name'             => 'Kecilung',
            //     'prefix'           => 'KCL',
            //     'start_sequence'   => $startSequence,
            //     'max_sequence'     => $maxSequence,
            //     'pad_length'       => 3,
            //     'max_quota'        => $maxQuota,
            //     'category'         => 'dine_in',
            //     'location'         => 'The Kecilung (Jl. Kecilung No. 8)',
            //     'phone'            => '0818 0311 6688',
            //     'instagram'        => '@kecilungresto',
            //     'website'          => null,
            //     'promo_highlight'  => null,
            //     'special_menus'    => [],
            //     'validity_label'   => 'Berlaku 3 bulan dari tanggal terbit (Dine-in)',
            //     'fixed_expires_at' => null,
            //     'validity_months'  => 3,
            //     'rule_summary'     => 'Khusus dine-in di The Kecilung (Jl. Kecilung No. 8). Berlaku 3 bulan sejak tanggal terbit, belum termasuk pajak.',
            //     'terms'            => [
            //         'Voucher tidak dapat diuangkan.',
            //         'Voucher belum termasuk pajak.',
            //         'Voucher hanya dapat digunakan untuk makan di tempat (dine-in).',
            //         'Voucher tidak dapat digabungkan dengan promosi lainnya.',
            //         'Voucher hanya bisa digunakan di The Kecilung (Jl. Kecilung No. 8).',
            //         'Voucher hanya berlaku selama 3 bulan dari tanggal yang telah diterbitkan.',
            //         'Voucher tidak dapat digunakan untuk cemilan, minuman kaleng, dan konsinyasi.',
            //         'Kecilung tidak bertanggung jawab atas voucher yang dicuri, hilang, atau rusak.',
            //         'Syarat dan ketentuan dapat berubah sewaktu-waktu tanpa pemberitahuan sebelumnya.',
            //     ],
            // ],
            // [
            //     'slug'             => 'malaijia',
            //     'name'             => 'Malaijia',
            //     'prefix'           => 'MLJ',
            //     'start_sequence'   => $startSequence,
            //     'max_sequence'     => $maxSequence,
            //     'pad_length'       => 3,
            //     'max_quota'        => $maxQuota,
            //     'category'         => 'dine_in',
            //     'location'         => 'Kopitiam Ma Lai Jia (Jl. Kecilung No. 8, Surabaya)',
            //     'phone'            => '0897-4170-999',
            //     'instagram'        => '@malaijia.kopitiam',
            //     'website'          => null,
            //     'promo_highlight'  => null,
            //     'special_menus'    => [],
            //     'validity_label'   => 'Berlaku 3 bulan dari tanggal terbit (Dine-in)',
            //     'fixed_expires_at' => null,
            //     'validity_months'  => 3,
            //     'rule_summary'     => 'Khusus dine-in di Kopitiam Ma Lai Jia (Jl. Kecilung No. 8, Surabaya). Tidak dapat diuangkan atau digabung promo lain.',
            //     'terms'            => [
            //         'Voucher tidak dapat diuangkan.',
            //         'Voucher hanya dapat digunakan untuk makan di tempat (dine-in).',
            //         'Voucher tidak dapat digabungkan dengan promosi lainnya.',
            //         'Voucher hanya bisa digunakan di Kopitiam Ma Lai Jia (Jl. Kecilung No. 8, Surabaya).',
            //         'The Kecilung tidak bertanggung jawab atas voucher yang dicuri, hilang, atau rusak.',
            //         'Syarat dan ketentuan dapat berubah sewaktu-waktu tanpa pemberitahuan sebelumnya.',
            //     ],
            // ],

            [
                'slug'             => 'kecilung',
                'name'             => 'Kecilung',
                'prefix'           => 'KCL',
                'start_sequence'   => $startSequence,
                'max_sequence'     => $maxSequence,
                'pad_length'       => 3,
                'max_quota'        => $maxQuota,
                'category'         => 'dine_in',
                'location'         => 'The Kecilung (Jl. Kecilung No. 8)',
                'phone'            => '0818 0311 6688',
                'instagram'        => '@kecilungresto',
                'website'          => null,
                'promo_highlight'  => 'Tanpa minimum order (Dine-in)',
                'special_menus'    => [],
                'validity_label'   => 'Berlaku 3 bulan dari tanggal terbit (Dine-in)',
                'fixed_expires_at' => null,
                'validity_months'  => 3,
                'rule_summary'     => 'Tanpa minimum order. Khusus dine-in di The Kecilung (Jl. Kecilung No. 8). Berlaku 3 bulan sejak tanggal terbit, belum termasuk pajak.',
                'terms'            => [
                    'Voucher berlaku tanpa minimum order (tanpa minimal pembelian).',
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
                'start_sequence'   => $startSequence,
                'max_sequence'     => $maxSequence,
                'pad_length'       => 3,
                'max_quota'        => $maxQuota,
                'category'         => 'dine_in',
                'location'         => 'Kopitiam Ma Lai Jia (Jl. Kecilung No. 8, Surabaya)',
                'phone'            => '0897-4170-999',
                'instagram'        => '@malaijia.kopitiam',
                'website'          => null,
                'promo_highlight'  => 'Tanpa minimum order (Dine-in)',
                'special_menus'    => [],
                'validity_label'   => 'Berlaku 3 bulan dari tanggal terbit (Dine-in)',
                'fixed_expires_at' => null,
                'validity_months'  => 3,
                'rule_summary'     => 'Tanpa minimum order. Khusus dine-in di Kopitiam Ma Lai Jia (Jl. Kecilung No. 8, Surabaya). Berlaku 3 bulan sejak tanggal terbit.',
                'terms'            => [
                    'Voucher berlaku tanpa minimum order (tanpa minimal pembelian).',
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

    private function getTalkshowGoodieBagConfig(): array
    {
        return [
            'slug'             => 'talkshow-goodiebag',
            'name'             => 'Free Goodie Bag (Talkshow Top 5)',
            'prefix'           => 'GBTS',
            'start_sequence'   => 1,
            'max_sequence'     => self::MAX_SEQUENCE,
            'pad_length'       => 3,
            'max_quota'        => self::MAX_SEQUENCE,
            'category'         => 'event',
            'location'         => 'Booth Registrasi BACK2ERAS (27 - 29 November 2026)',
            'phone'            => null,
            'instagram'        => null,
            'website'          => null,
            'promo_highlight'  => 'Eksklusif 5 Pendaftar Pertama Talkshow (Approved)',
            'special_menus'    => [],
            'validity_label'   => 'Khusus 5 Pendaftar Pertama Talkshow (Approved)',
            'fixed_expires_at' => '2026-11-29 23:59:59',
            'validity_months'  => null,
            'rule_summary'     => 'Voucher eksklusif Free Goodie Bag khusus 5 peserta pertama Talkshow yang telah disetujui admin.',
            'terms'            => [
                'Khusus untuk 5 pendaftar pertama Talkshow yang status pendaftarannya telah disetujui (Approved) oleh admin.',
                'Tunjukkan kode unik ini saat check-in di lokasi acara BACK2ERAS (27 - 29 November 2026).',
                'Berlaku 1x klaim per akun dan tidak dapat diuangkan atau dipindahtangankan.',
                'Pihak BACK2ERAS tidak bertanggung jawab atas voucher yang hilang atau disalahgunakan.',
            ],
        ];
    }

    private function getAllBrandsConfig(string $contextRole = 'visitor'): array
    {
        return array_merge([$this->getTalkshowGoodieBagConfig()], $this->getBrandsConfig($contextRole));
    }

    private function resolveExpiresAt(array $brandConfig, Carbon $issuedAt): Carbon
    {
        if (!empty($brandConfig['fixed_expires_at'])) {
            return Carbon::parse($brandConfig['fixed_expires_at'], 'Asia/Jakarta');
        }

        $months = $brandConfig['validity_months'] ?? 3;
        return $issuedAt->copy()->addMonths($months);
    }

    private function resolveEffectiveExpiresAt(?array $brandConfig, ?Voucher $voucher, Carbon $now): ?Carbon
    {
        if ($brandConfig && !empty($brandConfig['fixed_expires_at'])) {
            return Carbon::parse($brandConfig['fixed_expires_at'], 'Asia/Jakarta');
        }

        if ($voucher && $voucher->expires_at) {
            return $voucher->expires_at->copy()->timezone('Asia/Jakarta');
        }

        if ($voucher && $brandConfig) {
            $issuedAt = $voucher->created_at?->copy()->timezone('Asia/Jakarta') ?? $now;
            return $this->resolveExpiresAt($brandConfig, $issuedAt);
        }

        return null;
    }

    /**
     * Ambil angka urut tertinggi saat ini untuk brand berdasarkan source ('regular' vs 'talkshow').
     */
    private function getHighestSequenceForBrand(array $brandConfig, string $source = 'regular', bool $lock = false): int
    {
        $query = Voucher::where('brand_slug', $brandConfig['slug'])
            ->where('source', $source);

        if ($lock) {
            $query->lockForUpdate();
        }

        $codes = $query->pluck('code');
        $prefix = $brandConfig['prefix'];
        $startSeq = (int) $brandConfig['start_sequence'];
        $maxSeqAllowed = (int) ($brandConfig['max_sequence'] ?? self::MAX_SEQUENCE);
        $maxSeq = $startSeq - 1;

        foreach ($codes as $code) {
            if (preg_match('/^' . preg_quote($prefix, '/') . '(\d{3})$/', $code, $matches)) {
                $num = (int) $matches[1];
                if ($num >= $startSeq && $num <= $maxSeqAllowed && $num > $maxSeq) {
                    $maxSeq = $num;
                }
            }
        }

        return $maxSeq;
    }

    /**
     * Hitung total kode terpakai pada rentang (001-999 atau 101-999) agar sisa kuota akurat.
     */
    private function countUsedCodesInRange(array $brandConfig): int
    {
        $prefix = $brandConfig['prefix'];
        $startSeq = (int) $brandConfig['start_sequence'];
        $maxSeqAllowed = (int) ($brandConfig['max_sequence'] ?? self::MAX_SEQUENCE);

        $codes = Voucher::where('brand_slug', $brandConfig['slug'])->pluck('code');
        $usedCount = 0;

        foreach ($codes as $code) {
            if (preg_match('/^' . preg_quote($prefix, '/') . '(\d{3})$/', $code, $matches)) {
                $num = (int) $matches[1];
                if ($num >= $startSeq && $num <= $maxSeqAllowed) {
                    $usedCount++;
                }
            }
        }

        return $usedCount;
    }

    private function formatVoucherCode(array $brandConfig, int $sequence): string
    {
        $padLength = (int) ($brandConfig['pad_length'] ?? 3);
        return $brandConfig['prefix'] . str_pad((string) $sequence, $padLength, '0', STR_PAD_LEFT);
    }

    private function mapBrandsForResponse($brandConfigs, $claimedMap, Carbon $now)
    {
        return $brandConfigs->map(function ($brand) use ($claimedMap, $now) {
            $claimed = $claimedMap->get($brand['slug']);
            $maxQuota = (int) ($brand['max_quota'] ?? self::MAX_SEQUENCE);
            $usedInRange = $this->countUsedCodesInRange($brand);
            $remainingQuota = max(0, $maxQuota - $usedInRange);

            $issuedAt = $claimed?->created_at?->copy()->timezone('Asia/Jakarta');
            $expiresAt = $this->resolveEffectiveExpiresAt($brand, $claimed, $now);

            $isEventClosed = !empty($brand['fixed_expires_at'])
                && $now->gt(Carbon::parse($brand['fixed_expires_at'], 'Asia/Jakarta'));

            $isExpired = $claimed && $expiresAt ? $now->gt($expiresAt) : $isEventClosed;

            return [
                'slug'             => $brand['slug'],
                'name'             => $brand['name'],
                'prefix'           => $brand['prefix'],
                'code_placeholder' => $brand['prefix'] . str_repeat('-', (int) $brand['pad_length']),
                'category'         => $brand['category'],
                'location'         => $brand['location'],
                'phone'            => $brand['phone'],
                'instagram'        => $brand['instagram'],
                'website'          => $brand['website'] ?? null,
                'promo_highlight'  => $brand['promo_highlight'] ?? null,
                'special_menus'    => $brand['special_menus'] ?? [],
                'validity_label'   => $brand['validity_label'],
                'rule_summary'     => $brand['rule_summary'],
                'terms'            => $brand['terms'],
                'max_quota'        => $maxQuota,
                'remaining_quota'  => $remainingQuota,
                'claimed'          => (bool) $claimed,
                'code'             => $claimed?->code,
                'is_used'          => (bool) ($claimed?->is_used ?? false),
                'is_expired'       => $isExpired,
                'claimed_at'       => $issuedAt?->toIso8601String(),
                'expires_at'       => $expiresAt?->toIso8601String(),
            ];
        })->values();
    }

    /**
     * Eksekusi transaksi klaim voucher yang aman untuk source 'regular' (001-999) maupun 'talkshow' (101-999).
     */
    private function executeClaimTransaction($user, array $brandConfig, string $source, Carbon $now)
    {
        if (!empty($brandConfig['fixed_expires_at'])) {
            $eventEnd = Carbon::parse($brandConfig['fixed_expires_at'], 'Asia/Jakarta');
            if ($now->gt($eventEnd)) {
                return response()->json([
                    'message' => 'Periode acara BACK2ERAS (27 - 29 November 2026) telah berakhir. Voucher ' . $brandConfig['name'] . ' sudah tidak dapat diklaim.',
                ], 422);
            }
        }

        $maxSeqAllowed = (int) ($brandConfig['max_sequence'] ?? self::MAX_SEQUENCE);
        $rangeLabel = str_pad((string) $brandConfig['start_sequence'], 3, '0', STR_PAD_LEFT)
            . ' - '
            . str_pad((string) $maxSeqAllowed, 3, '0', STR_PAD_LEFT);

        $result = DB::transaction(function () use ($user, $brandConfig, $source, $now, $maxSeqAllowed) {
            $existing = Voucher::where('user_id', $user->id)
                ->where('brand_slug', $brandConfig['slug'])
                ->where('source', $source)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                return [
                    'status'  => 'already_claimed',
                    'voucher' => $existing,
                ];
            }

            $highestSeq = $this->getHighestSequenceForBrand($brandConfig, $source, true);
            $nextSeq = $highestSeq + 1;

            // Lewati kode yang sudah terpakai oleh source lain agar tidak pernah bentrok unique key
            while (
                $nextSeq <= $maxSeqAllowed
                && Voucher::where('code', $this->formatVoucherCode($brandConfig, $nextSeq))->exists()
            ) {
                $nextSeq++;
            }

            if ($nextSeq > $maxSeqAllowed) {
                return ['status' => 'quota_exceeded'];
            }

            $uniqueCode = $this->formatVoucherCode($brandConfig, $nextSeq);
            $expiresAt = $this->resolveExpiresAt($brandConfig, $now);

            $voucher = Voucher::create([
                'user_id'    => $user->id,
                'brand_slug' => $brandConfig['slug'],
                'source'     => $source,
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
                'message' => 'Maaf, kuota voucher ' . $brandConfig['name'] . " ({$rangeLabel}) sudah habis.",
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Kode unik ' . $brandConfig['name'] . ' berhasil diklaim!',
            'voucher' => $result['voucher'],
        ], 201);
    }

    // =========================================================================
    // 1. REGULER VOUCHER (/voucher) — VISITOR & KOL (Kode 001 - 999)
    // =========================================================================

    public function index(Request $request)
    {
        $user = $request->user();
        $role = $this->normalizeRole($user->role);
        $now = Carbon::now('Asia/Jakarta');

        // Akun murni Talkshow diarahkan ke halaman /voucher-talkshow
        if ($role === 'talkshow') {
            return response()->json([
                'user_role'                   => 'talkshow',
                'redirect_to_talkshow_page'   => true,
                'can_claim_voucher'           => false,
                'claim_block_reason'          => 'Akun Anda terdaftar sebagai Peserta Talkshow. Silakan buka halaman Voucher Talkshow untuk mengklaim voucher Anda.',
                'brands'                      => [],
            ]);
        }

        $claimedMap = Voucher::where('user_id', $user->id)
            ->where('source', 'regular')
            ->get()
            ->keyBy('brand_slug');

        $eligibility = $this->checkRegularEligibility($user);
        $brandConfigs = collect($this->getBrandsConfig($role));
        $brands = $this->mapBrandsForResponse($brandConfigs, $claimedMap, $now);

        return response()->json([
            'user_role'                 => $role,
            'redirect_to_talkshow_page' => false,
            'can_claim_voucher'         => $eligibility['eligible'],
            'claim_block_reason'        => $eligibility['reason'],
            'brands'                    => $brands,
        ]);
    }

    public function claim(Request $request)
    {
        $validated = $request->validate([
            'brand_slug' => 'required|string',
        ]);

        if ($validated['brand_slug'] === 'talkshow-goodiebag') {
            return response()->json([
                'message' => 'Free Goodie Bag khusus 5 pendaftar pertama Talkshow tidak memerlukan klaim kode atau redeem.',
            ], 422);
        }

        $user = $request->user();
        $role = $this->normalizeRole($user->role);

        if ($role === 'talkshow') {
            return response()->json([
                'message' => 'Peserta Talkshow silakan mengklaim voucher melalui halaman khusus Voucher Talkshow.',
            ], 403);
        }

        $eligibility = $this->checkRegularEligibility($user);
        if (!$eligibility['eligible']) {
            return response()->json(['message' => $eligibility['reason']], 403);
        }

        $brandConfig = collect($this->getBrandsConfig($role))->firstWhere('slug', $validated['brand_slug']);
        if (!$brandConfig) {
            return response()->json(['message' => 'Brand tidak ditemukan.'], 404);
        }

        return $this->executeClaimTransaction($user, $brandConfig, 'regular', Carbon::now('Asia/Jakarta'));
    }

    // =========================================================================
    // 2. TALKSHOW VOUCHER (/voucher-talkshow) — SEMUA PESERTA TALKSHOW (Kode 101 - 999)
    // =========================================================================

    // public function talkshowIndex(Request $request)
    // {
    //     $user = $request->user();
    //     $now = Carbon::now('Asia/Jakarta');

    //     $eligibility = $this->checkTalkshowEligibility($user);

    //     if (!$eligibility['is_participant']) {
    //         return response()->json([
    //             'is_talkshow_participant' => false,
    //             'can_claim_voucher'       => false,
    //             'claim_block_reason'      => $eligibility['reason'],
    //             'talkshow_rank'           => null,
    //             'has_talkshow_goodiebag'  => false,
    //             'brands'                  => [],
    //         ]);
    //     }

    //     $claimedMap = Voucher::where('user_id', $user->id)
    //         ->where('source', 'talkshow')
    //         ->get()
    //         ->keyBy('brand_slug');

    //     $brandConfigs = collect($this->getBrandsConfig('talkshow'));

    //     // Urutan approved 6 - 25: sembunyikan Back2eras (hanya 6 brand partner)
    //     if (is_array($eligibility['allowed_slugs']) && count($eligibility['allowed_slugs']) > 0) {
    //         $brandConfigs = $brandConfigs->filter(
    //             fn ($b) => in_array($b['slug'], $eligibility['allowed_slugs'], true)
    //         )->values();
    //     }

    //     $brands = $this->mapBrandsForResponse($brandConfigs, $claimedMap, $now);

    //     return response()->json([
    //         'is_talkshow_participant' => true,
    //         'can_claim_voucher'       => $eligibility['eligible'],
    //         'claim_block_reason'      => $eligibility['reason'],
    //         'talkshow_rank'           => $eligibility['talkshow_rank'],
    //         'has_talkshow_goodiebag'  => $eligibility['include_goodiebag'],
    //         'brands'                  => $brands,
    //     ]);
    // }

    // public function talkshowClaim(Request $request)
    // {
    //     $validated = $request->validate([
    //         'brand_slug' => 'required|string',
    //     ]);

    //     if ($validated['brand_slug'] === 'talkshow-goodiebag') {
    //         return response()->json([
    //             'message' => 'Free Goodie Bag khusus 5 pendaftar pertama Talkshow tidak memerlukan klaim kode atau redeem. Cukup tunjukkan akun Anda saat check-in di booth registrasi.',
    //         ], 422);
    //     }

    //     $user = $request->user();
    //     $eligibility = $this->checkTalkshowEligibility($user);

    //     if (!$eligibility['is_participant'] || !$eligibility['eligible']) {
    //         return response()->json(['message' => $eligibility['reason']], 403);
    //     }

    //     $brandConfig = collect($this->getBrandsConfig('talkshow'))->firstWhere('slug', $validated['brand_slug']);
    //     if (!$brandConfig) {
    //         return response()->json(['message' => 'Brand tidak ditemukan.'], 404);
    //     }

    //     // Cegah peserta Talkshow urutan 6 - 25 mengklaim Back2eras
    //     if (
    //         is_array($eligibility['allowed_slugs'])
    //         && !in_array($brandConfig['slug'], $eligibility['allowed_slugs'], true)
    //     ) {
    //         return response()->json([
    //             'message' => 'Peserta Talkshow urutan 6 - 25 hanya berhak mengklaim 6 voucher brand partner (tidak termasuk voucher Back2eras).',
    //         ], 403);
    //     }

    //     return $this->executeClaimTransaction($user, $brandConfig, 'talkshow', Carbon::now('Asia/Jakarta'));
    // }

    public function talkshowIndex(Request $request)
    {
        $user = $request->user();
        $now = Carbon::now('Asia/Jakarta');

        $eligibility = $this->checkTalkshowEligibility($user);

        if (!$eligibility['is_participant']) {
            return response()->json([
                'is_talkshow_participant' => false,
                'can_claim_voucher'       => false,
                'claim_block_reason'      => $eligibility['reason'],
                'talkshow_rank'           => null,
                'has_talkshow_goodiebag'  => false,
                'brands'                  => [],
            ]);
        }

        $claimedMap = Voucher::where('user_id', $user->id)
            ->where('source', 'talkshow')
            ->get()
            ->keyBy('brand_slug');

        // Hanya tampilkan 6 brand partner (tanpa Back2eras) untuk seluruh peserta Talkshow
        $brandConfigs = collect($this->getBrandsConfig('talkshow'))
            ->filter(fn ($b) => in_array($b['slug'], self::TALKSHOW_ALLOWED_SLUGS, true))
            ->values();

        $brands = $this->mapBrandsForResponse($brandConfigs, $claimedMap, $now);

        return response()->json([
            'is_talkshow_participant' => true,
            'can_claim_voucher'       => $eligibility['eligible'],
            'claim_block_reason'      => $eligibility['reason'],
            'talkshow_rank'           => $eligibility['talkshow_rank'],
            'has_talkshow_goodiebag'  => $eligibility['include_goodiebag'],
            'brands'                  => $brands,
        ]);
    }

    public function talkshowClaim(Request $request)
    {
        $validated = $request->validate([
            'brand_slug' => 'required|string',
        ]);

        if ($validated['brand_slug'] === 'talkshow-goodiebag') {
            return response()->json([
                'message' => 'Free Goodie Bag khusus 5 pendaftar pertama Talkshow tidak memerlukan klaim kode atau redeem. Cukup tunjukkan akun Anda saat check-in di booth registrasi.',
            ], 422);
        }

        if ($validated['brand_slug'] === 'back2eras' || !in_array($validated['brand_slug'], self::TALKSHOW_ALLOWED_SLUGS, true)) {
            return response()->json([
                'message' => 'Voucher Back2eras tidak tersedia untuk klaim di halaman Voucher Talkshow.',
            ], 403);
        }

        $user = $request->user();
        $eligibility = $this->checkTalkshowEligibility($user);

        if (!$eligibility['is_participant'] || !$eligibility['eligible']) {
            return response()->json(['message' => $eligibility['reason']], 403);
        }

        $brandConfig = collect($this->getBrandsConfig('talkshow'))->firstWhere('slug', $validated['brand_slug']);
        if (!$brandConfig) {
            return response()->json(['message' => 'Brand tidak ditemukan.'], 404);
        }

        return $this->executeClaimTransaction($user, $brandConfig, 'talkshow', Carbon::now('Asia/Jakarta'));
    }

    // =========================================================================
    // 3. ELIGIBILITY HELPERS
    // =========================================================================

    private function checkRegularEligibility($user): array
    {
        if ($user->role === 'kol') {
            $kol = Kol::where('user_id', $user->id)
                ->orWhere('email', $user->email)
                ->first();

            $hasSocialLink = !empty(trim((string) ($kol?->social_media_link ?? '')));

            if (!$hasSocialLink) {
                return [
                    'eligible' => false,
                    'reason'   => 'Akun KOL wajib mengisi Link Media Sosial terlebih dahulu agar dapat mengklaim voucher.',
                ];
            }
        }

        return [
            'eligible' => true,
            'reason'   => null,
        ];
    }

    private function checkTalkshowEligibility($user): array
    {
        $talkshowRegs = TalkshowRegistration::where(function ($q) use ($user) {
            $q->where('user_id', $user->id)
              ->orWhere('email', $user->email);
        })->get();

        if ($talkshowRegs->isEmpty()) {
            return [
                'is_participant'    => false,
                'eligible'          => false,
                'reason'            => 'Akun Anda belum terdaftar pada sesi Talkshow mana pun. Silakan mendaftar terlebih dahulu di halaman Talkshow.',
                'allowed_slugs'     => [],
                'include_goodiebag' => false,
                'talkshow_rank'     => null,
            ];
        }

        $approvedRanks = $talkshowRegs
            ->where('status', 'approved')
            ->pluck('approved_rank')
            ->filter(fn ($rank) => $rank !== null)
            ->values();

        $bestApprovedRank = $approvedRanks->isNotEmpty() ? (int) $approvedRanks->min() : null;

        if ($bestApprovedRank === null) {
            $hasPending = $talkshowRegs->where('status', 'pending')->isNotEmpty();

            return [
                'is_participant'    => true,
                'eligible'          => false,
                'reason'            => $hasPending
                    ? 'Pendaftaran Talkshow Anda masih menunggu persetujuan (Approve) dari admin. Voucher Talkshow baru dapat diklaim setelah pendaftaran Anda diterima.'
                    : 'Pendaftaran Talkshow Anda ditolak oleh admin sehingga tidak dapat mengklaim voucher Talkshow.',
                'allowed_slugs'     => [],
                'include_goodiebag' => false,
                'talkshow_rank'     => null,
            ];
        }

        // // Urutan 1 - 5: Free Goodie Bag (info) + Semua 7 brand (termasuk Back2eras)
        // if ($bestApprovedRank <= 5) {
        //     return [
        //         'is_participant'    => true,
        //         'eligible'          => true,
        //         'reason'            => null,
        //         'allowed_slugs'     => null,
        //         'include_goodiebag' => true,
        //         'talkshow_rank'     => $bestApprovedRank,
        //     ];
        // }

        // // Urutan 6 - 25: 6 brand partner saja (tanpa Free Goodie Bag & tanpa Back2eras)
        // return [
        //     'is_participant'    => true,
        //     'eligible'          => true,
        //     'reason'            => null,
        //     'allowed_slugs'     => ['solher', 'gycora', 'glamoire', 'wdp', 'kecilung', 'malaijia'],
        //     'include_goodiebag' => false,
        //     'talkshow_rank'     => $bestApprovedRank,
        // ];

        // Urutan 1 - 5: Free Goodie Bag (info) + 6 brand partner (tanpa Back2eras)
        if ($bestApprovedRank <= 5) {
            return [
                'is_participant'    => true,
                'eligible'          => true,
                'reason'            => null,
                'allowed_slugs'     => self::TALKSHOW_ALLOWED_SLUGS,
                'include_goodiebag' => true,
                'talkshow_rank'     => $bestApprovedRank,
            ];
        }

        // Urutan 6 - 25: 6 brand partner (tanpa Free Goodie Bag & tanpa Back2eras)
        return [
            'is_participant'    => true,
            'eligible'          => true,
            'reason'            => null,
            'allowed_slugs'     => self::TALKSHOW_ALLOWED_SLUGS,
            'include_goodiebag' => false,
            'talkshow_rank'     => $bestApprovedRank,
        ];
    }

    // =========================================================================
    // 4. ADMIN VERIFY & REDEEM
    // =========================================================================

    private function formatAdminVoucher(Voucher $voucher, Carbon $now): array
    {
        $ownerRole = $this->normalizeRole($voucher->user?->role);
        $source = $voucher->source ?? 'regular';
        // Jika voucher diklaim dari halaman Talkshow, selalu gunakan S&K Talkshow
        $effectiveRole = $source === 'talkshow' ? 'talkshow' : $ownerRole;
        $brandConfig = collect($this->getAllBrandsConfig($effectiveRole))->firstWhere('slug', $voucher->brand_slug);

        $issuedAt = $voucher->created_at?->copy()->timezone('Asia/Jakarta');
        $expiresAt = $this->resolveEffectiveExpiresAt($brandConfig, $voucher, $now);
        $usedAt = $voucher->used_at?->copy()->timezone('Asia/Jakarta');
        $isExpired = $expiresAt ? $now->gt($expiresAt) : false;

        $status = 'unused';
        if ($voucher->is_used) {
            $status = 'used';
        } elseif ($isExpired) {
            $status = 'expired';
        }

        return [
            'id'              => $voucher->id,
            'code'            => $voucher->code,
            'source'          => $source,
            'brand_slug'      => $voucher->brand_slug,
            'brand_name'      => $voucher->brand_name,
            'location'        => $brandConfig['location'] ?? '-',
            'promo_highlight' => $brandConfig['promo_highlight'] ?? null,
            'special_menus'   => $brandConfig['special_menus'] ?? [],
            'terms'           => $brandConfig['terms'] ?? [],
            'is_used'         => (bool) $voucher->is_used,
            'is_expired'      => $isExpired,
            'can_redeem'      => !$voucher->is_used && !$isExpired,
            'status'          => $status,
            'claimed_at'      => $issuedAt?->toIso8601String(),
            'expires_at'      => $expiresAt?->toIso8601String(),
            'used_at'         => $usedAt?->toIso8601String(),
            'user'            => [
                'id'       => $voucher->user?->id,
                'name'     => $voucher->user?->name ?? '-',
                'email'    => $voucher->user?->email ?? '-',
                'role'     => $effectiveRole,
                'whatsapp' => $voucher->user?->visitor?->whatsapp
                    ?? $voucher->user?->kol?->whatsapp
                    ?? $voucher->user?->talkshowRegistrations?->first()?->phone
                    ?? '-',
            ],
        ];
    }

    public function adminIndex(Request $request)
    {
        $now = Carbon::now('Asia/Jakarta');

        $vouchers = Voucher::with(['user.visitor', 'user.kol', 'user.talkshowRegistrations'])
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

    public function adminVerify(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50',
        ]);

        $code = strtoupper(trim($validated['code']));
        $now = Carbon::now('Asia/Jakarta');

        $voucher = Voucher::with(['user.visitor', 'user.kol', 'user.talkshowRegistrations'])
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

    public function adminRedeem(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50',
        ]);

        $code = strtoupper(trim($validated['code']));
        $now = Carbon::now('Asia/Jakarta');

        $result = DB::transaction(function () use ($code, $now) {
            $voucher = Voucher::with(['user.visitor', 'user.kol', 'user.talkshowRegistrations'])
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

            $ownerRole = $this->normalizeRole($voucher->user?->role);
            $effectiveRole = ($voucher->source ?? 'regular') === 'talkshow' ? 'talkshow' : $ownerRole;
            $brandConfig = collect($this->getAllBrandsConfig($effectiveRole))->firstWhere('slug', $voucher->brand_slug);
            $expiresAt = $this->resolveEffectiveExpiresAt($brandConfig, $voucher, $now);

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
