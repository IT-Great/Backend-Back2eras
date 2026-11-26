<?php

// namespace App\Http\Controllers;

// use App\Models\Talkshow;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Storage;

// class TalkshowController extends Controller
// {
//     // USER: Menampilkan talkshow yang sudah di-approve
//     public function publicIndex()
//     {
//         $talkshows = Talkshow::where('status', 'approved')
//             ->orderBy('schedule', 'asc')
//             ->get();

//         return response()->json($talkshows);
//     }

//     // USER: Pendaftaran talkshow baru (status otomatis pending)
//     public function publicStore(Request $request)
//     {
//         $validated = $request->validate([
//             'poster'       => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
//             'speaker_name' => 'required|string|max:255',
//             'icon'         => 'required|string|max:255',
//             'theme'        => 'required|string|max:255',
//             'schedule'     => 'required|date',
//         ]);

//         $posterPath = $request->file('poster')->store('talkshows', 'public');

//         $talkshow = Talkshow::create([
//             'poster'       => $posterPath,
//             'speaker_name' => $validated['speaker_name'],
//             'icon'         => $validated['icon'],
//             'theme'        => $validated['theme'],
//             'schedule'     => $validated['schedule'],
//             'status'       => 'pending',
//         ]);

//         return response()->json([
//             'message' => 'Pendaftaran talkshow berhasil dikirim dan menunggu persetujuan admin.',
//             'data'    => $talkshow,
//         ], 201);
//     }

//     // ADMIN: Menampilkan seluruh data talkshow
//     public function index()
//     {
//         $talkshows = Talkshow::orderBy('created_at', 'desc')->get();
//         return response()->json($talkshows);
//     }

//     // ADMIN: Tambah data talkshow langsung
//     public function store(Request $request)
//     {
//         $validated = $request->validate([
//             'poster'       => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
//             'speaker_name' => 'required|string|max:255',
//             'icon'         => 'required|string|max:255',
//             'theme'        => 'required|string|max:255',
//             'schedule'     => 'required|date',
//             'status'       => 'nullable|in:pending,approved,rejected',
//         ]);

//         $posterPath = $request->file('poster')->store('talkshows', 'public');

//         $talkshow = Talkshow::create([
//             'poster'       => $posterPath,
//             'speaker_name' => $validated['speaker_name'],
//             'icon'         => $validated['icon'],
//             'theme'        => $validated['theme'],
//             'schedule'     => $validated['schedule'],
//             'status'       => $validated['status'] ?? 'approved',
//         ]);

//         return response()->json([
//             'message' => 'Data talkshow berhasil ditambahkan.',
//             'data'    => $talkshow,
//         ], 201);
//     }

//     // ADMIN: Update data talkshow (poster opsional saat edit)
//     public function update(Request $request, $id)
//     {
//         $talkshow = Talkshow::findOrFail($id);

//         $validated = $request->validate([
//             'poster'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
//             'speaker_name' => 'required|string|max:255',
//             'icon'         => 'required|string|max:255',
//             'theme'        => 'required|string|max:255',
//             'schedule'     => 'required|date',
//             'status'       => 'required|in:pending,approved,rejected',
//         ]);

//         if ($request->hasFile('poster')) {
//             if ($talkshow->poster && Storage::disk('public')->exists($talkshow->poster)) {
//                 Storage::disk('public')->delete($talkshow->poster);
//             }
//             $talkshow->poster = $request->file('poster')->store('talkshows', 'public');
//         }

//         $talkshow->speaker_name = $validated['speaker_name'];
//         $talkshow->icon         = $validated['icon'];
//         $talkshow->theme        = $validated['theme'];
//         $talkshow->schedule     = $validated['schedule'];
//         $talkshow->status       = $validated['status'];
//         $talkshow->save();

//         return response()->json([
//             'message' => 'Data talkshow berhasil diperbarui.',
//             'data'    => $talkshow,
//         ]);
//     }

//     // ADMIN: Approval (ubah status pending / approved / rejected)
//     public function updateStatus(Request $request, $id)
//     {
//         $talkshow = Talkshow::findOrFail($id);

//         $validated = $request->validate([
//             'status' => 'required|in:pending,approved,rejected',
//         ]);

//         $talkshow->status = $validated['status'];
//         $talkshow->save();

//         return response()->json([
//             'message' => 'Status talkshow berhasil diubah menjadi ' . $validated['status'] . '.',
//             'data'    => $talkshow,
//         ]);
//     }

//     // ADMIN: Hapus data talkshow beserta file posternya
//     public function destroy($id)
//     {
//         $talkshow = Talkshow::findOrFail($id);

//         if ($talkshow->poster && Storage::disk('public')->exists($talkshow->poster)) {
//             Storage::disk('public')->delete($talkshow->poster);
//         }

//         $talkshow->delete();

//         return response()->json([
//             'message' => 'Data talkshow berhasil dihapus.',
//         ]);
//     }
// }

// namespace App\Http\Controllers;

// use App\Models\Talkshow;
// use App\Models\TalkshowRegistration;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Storage;

// class TalkshowController extends Controller
// {
//     // USER: Menampilkan semua jadwal talkshow
//     public function publicIndex()
//     {
//         $talkshows = Talkshow::withCount([
//             'registrations as approved_participants_count' => function ($query) {
//                 $query->where('status', 'approved');
//             },
//         ])
//             ->orderBy('schedule', 'asc')
//             ->get();

//         return response()->json($talkshows);
//     }

//     // USER: Mendaftar sebagai peserta pada salah satu talkshow (status otomatis pending)
//     public function registerParticipant(Request $request, $id)
//     {
//         $talkshow = Talkshow::findOrFail($id);

//         $validated = $request->validate([
//             'name'        => 'required|string|max:255',
//             'email'       => 'required|email|max:255',
//             'phone'       => 'required|string|max:30',
//             'institution' => 'nullable|string|max:255',
//         ]);

//         // Cegah pendaftaran ganda dengan email yang sama di talkshow yang sama
//         $alreadyRegistered = TalkshowRegistration::where('talkshow_id', $talkshow->id)
//             ->where('email', $validated['email'])
//             ->exists();

//         if ($alreadyRegistered) {
//             return response()->json([
//                 'message' => 'Email ini sudah terdaftar pada sesi talkshow tersebut.',
//             ], 422);
//         }

//         $registration = TalkshowRegistration::create([
//             'talkshow_id' => $talkshow->id,
//             'name'        => $validated['name'],
//             'email'       => $validated['email'],
//             'phone'       => $validated['phone'],
//             'institution' => $validated['institution'] ?? null,
//             'status'      => 'pending',
//         ]);

//         return response()->json([
//             'message' => 'Pendaftaran berhasil dikirim! Menunggu persetujuan admin.',
//             'data'    => $registration,
//         ], 201);
//     }

//     // ADMIN: Menampilkan semua talkshow beserta ringkasan jumlah pendaftar
//     public function index()
//     {
//         $talkshows = Talkshow::withCount([
//             'registrations',
//             'registrations as pending_count' => fn ($q) => $q->where('status', 'pending'),
//             'registrations as approved_count' => fn ($q) => $q->where('status', 'approved'),
//         ])
//             ->orderBy('created_at', 'desc')
//             ->get();

//         return response()->json($talkshows);
//     }

//     // ADMIN: Detail satu talkshow beserta seluruh daftar pendaftarnya
//     public function show($id)
//     {
//         $talkshow = Talkshow::with([
//             'registrations' => fn ($q) => $q->orderBy('created_at', 'desc'),
//         ])->findOrFail($id);

//         return response()->json($talkshow);
//     }

//     // ADMIN: Tambah data talkshow (tanpa status approval)
//     public function store(Request $request)
//     {
//         $validated = $request->validate([
//             'poster'       => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
//             'speaker_name' => 'required|string|max:255',
//             'icon'         => 'required|string|max:255',
//             'theme'        => 'required|string|max:255',
//             'schedule'     => 'required|date',
//         ]);

//         $posterPath = $request->file('poster')->store('talkshows', 'public');

//         $talkshow = Talkshow::create([
//             'poster'       => $posterPath,
//             'speaker_name' => $validated['speaker_name'],
//             'icon'         => $validated['icon'],
//             'theme'        => $validated['theme'],
//             'schedule'     => $validated['schedule'],
//         ]);

//         return response()->json([
//             'message' => 'Data talkshow berhasil ditambahkan.',
//             'data'    => $talkshow,
//         ], 201);
//     }

//     // ADMIN: Update data talkshow (tanpa status approval)
//     public function update(Request $request, $id)
//     {
//         $talkshow = Talkshow::findOrFail($id);

//         $validated = $request->validate([
//             'poster'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
//             'speaker_name' => 'required|string|max:255',
//             'icon'         => 'required|string|max:255',
//             'theme'        => 'required|string|max:255',
//             'schedule'     => 'required|date',
//         ]);

//         if ($request->hasFile('poster')) {
//             if ($talkshow->poster && Storage::disk('public')->exists($talkshow->poster)) {
//                 Storage::disk('public')->delete($talkshow->poster);
//             }
//             $talkshow->poster = $request->file('poster')->store('talkshows', 'public');
//         }

//         $talkshow->speaker_name = $validated['speaker_name'];
//         $talkshow->icon         = $validated['icon'];
//         $talkshow->theme        = $validated['theme'];
//         $talkshow->schedule     = $validated['schedule'];
//         $talkshow->save();

//         return response()->json([
//             'message' => 'Data talkshow berhasil diperbarui.',
//             'data'    => $talkshow,
//         ]);
//     }

//     // ADMIN: Hapus data talkshow beserta posternya (peserta otomatis terhapus karena cascade)
//     public function destroy($id)
//     {
//         $talkshow = Talkshow::findOrFail($id);

//         if ($talkshow->poster && Storage::disk('public')->exists($talkshow->poster)) {
//             Storage::disk('public')->delete($talkshow->poster);
//         }

//         $talkshow->delete();

//         return response()->json([
//             'message' => 'Data talkshow berhasil dihapus.',
//         ]);
//     }

//     // ADMIN: Approve / Reject peserta yang mendaftar pada talkshow
//     public function updateRegistrationStatus(Request $request, $registrationId)
//     {
//         $registration = TalkshowRegistration::findOrFail($registrationId);

//         $validated = $request->validate([
//             'status' => 'required|in:pending,approved,rejected',
//         ]);

//         $registration->status = $validated['status'];
//         $registration->save();

//         return response()->json([
//             'message' => 'Status peserta berhasil diubah menjadi ' . $validated['status'] . '.',
//             'data'    => $registration,
//         ]);
//     }

//     // ADMIN: Hapus data pendaftaran peserta
//     public function destroyRegistration($registrationId)
//     {
//         $registration = TalkshowRegistration::findOrFail($registrationId);
//         $registration->delete();

//         return response()->json([
//             'message' => 'Data peserta berhasil dihapus.',
//         ]);
//     }
// }

namespace App\Http\Controllers;

use App\Models\Talkshow;
use App\Models\TalkshowRegistration;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TalkshowController extends Controller
{
    private const MAX_PARTICIPANTS = 25;
    private const GOODIE_BAG_QUOTA = 5;

    // USER: Menampilkan semua jadwal talkshow beserta sisa kuota (maks 25)
    // public function publicIndex()
    // {
    //     $talkshows = Talkshow::withCount([
    //         'registrations as active_registrations_count' => fn ($q) => $q->where('status', '!=', 'rejected'),
    //         'registrations as approved_participants_count' => fn ($q) => $q->where('status', 'approved'),
    //     ])
    //         ->orderBy('schedule', 'asc')
    //         ->get()
    //         ->map(function (Talkshow $t) {
    //             $active = (int) $t->active_registrations_count;
    //             $approved = (int) $t->approved_participants_count;

    //             $t->max_participants = self::MAX_PARTICIPANTS;
    //             $t->remaining_slots = max(0, self::MAX_PARTICIPANTS - $active);
    //             $t->is_full = $active >= self::MAX_PARTICIPANTS;
    //             $t->goodie_bag_remaining = max(0, self::GOODIE_BAG_QUOTA - $approved);

    //             return $t;
    //         });

    //     return response()->json($talkshows);
    // }

    // USER: Menampilkan semua jadwal talkshow beserta sisa kuota (maks 25) & status daftar milik user yang login
    public function publicIndex()
    {
        $authUser = auth('sanctum')->user();

        $myRegistrations = collect();
        if ($authUser) {
            $myRegistrations = TalkshowRegistration::where(function ($q) use ($authUser) {
                $q->where('user_id', $authUser->id)
                  ->orWhere('email', $authUser->email);
            })
                ->orderBy('created_at', 'desc')
                ->get()
                ->keyBy('talkshow_id');
        }

        $talkshows = Talkshow::withCount([
            'registrations as active_registrations_count' => fn ($q) => $q->where('status', '!=', 'rejected'),
            'registrations as approved_participants_count' => fn ($q) => $q->where('status', 'approved'),
        ])
            ->orderBy('schedule', 'asc')
            ->get()
            ->map(function (Talkshow $t) use ($myRegistrations) {
                $active = (int) $t->active_registrations_count;
                $approved = (int) $t->approved_participants_count;
                $myReg = $myRegistrations->get($t->id);

                $t->max_participants = self::MAX_PARTICIPANTS;
                $t->remaining_slots = max(0, self::MAX_PARTICIPANTS - $active);
                $t->is_full = $active >= self::MAX_PARTICIPANTS;
                $t->goodie_bag_remaining = max(0, self::GOODIE_BAG_QUOTA - $approved);
                $t->my_registration_status = $myReg?->status;
                $t->my_approved_rank = $myReg?->approved_rank;

                return $t;
            });

        return response()->json($talkshows);
    }

    // USER: Mendaftar sebagai peserta talkshow (maks 25 orang, otomatis buat akun user jika belum login)
    public function registerParticipant(Request $request, $id)
    {
        $authUser = auth('sanctum')->user();

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|max:255',
            'phone'       => 'required|string|max:30',
            'institution' => 'nullable|string|max:255',
            'password'    => $authUser ? 'nullable|string|min:6' : 'required|string|min:6',
        ]);

        // Jika belum login tetapi email sudah terdaftar di tabel users, arahkan untuk login dulu
        if (!$authUser && User::where('email', $validated['email'])->exists()) {
            return response()->json([
                'message' => 'Email ini sudah memiliki akun. Silakan login terlebih dahulu lewat ikon profil di kanan atas sebelum mendaftar talkshow.',
            ], 422);
        }

        $result = DB::transaction(function () use ($id, $validated, $authUser) {
            // Kunci baris talkshow agar kuota 25 tidak pernah tembus saat request bersamaan
            $talkshow = Talkshow::lockForUpdate()->findOrFail($id);

            // 1. Cek batas maksimal 25 pendaftar aktif (pending + approved)
            $activeCount = TalkshowRegistration::where('talkshow_id', $talkshow->id)
                ->where('status', '!=', 'rejected')
                ->count();

            if ($activeCount >= self::MAX_PARTICIPANTS) {
                return ['state' => 'full'];
            }

            // 2. Cegah pendaftaran ganda pada talkshow yang sama
            $duplicateQuery = TalkshowRegistration::where('talkshow_id', $talkshow->id)
                ->where(function ($q) use ($validated, $authUser) {
                    $q->where('email', $validated['email']);
                    if ($authUser) {
                        $q->orWhere('user_id', $authUser->id);
                    }
                });

            if ($duplicateQuery->exists()) {
                return ['state' => 'duplicate'];
            }

            // 3. Pakai akun yang sedang login, atau buat akun baru role = 'talkshow'
            $user = $authUser;
            $newToken = null;

            if (!$user) {
                $user = User::create([
                    'name'     => $validated['name'],
                    'email'    => $validated['email'],
                    'password' => $validated['password'], // otomatis di-hash oleh cast User
                    'role'     => 'talkshow',
                ]);
                $newToken = $user->createToken('visitor_auth_token')->plainTextToken;
            }

            $registration = TalkshowRegistration::create([
                'talkshow_id' => $talkshow->id,
                'user_id'     => $user->id,
                'name'        => $validated['name'],
                'email'       => $validated['email'],
                'phone'       => $validated['phone'],
                'institution' => $validated['institution'] ?? null,
                'status'      => 'pending',
            ]);

            return [
                'state'        => 'created',
                'registration' => $registration,
                'user'         => $user,
                'token'        => $newToken,
                'queue_pos'    => $activeCount + 1,
            ];
        });

        if ($result['state'] === 'full') {
            return response()->json([
                'message' => 'Maaf, kuota peserta talkshow ini sudah penuh (maksimal 25 orang).',
            ], 422);
        }

        if ($result['state'] === 'duplicate') {
            return response()->json([
                'message' => 'Akun atau email ini sudah terdaftar pada sesi talkshow tersebut.',
            ], 422);
        }

        $payload = [
            'message'        => 'Pendaftaran berhasil dikirim! Voucher akan otomatis terbuka setelah status pendaftaran Anda disetujui (Accept) oleh admin.',
            'data'           => $result['registration'],
            'queue_position' => $result['queue_pos'],
            'has_talkshow_registration' => true,
        ];

        // Kirim token auto-login jika pendaftar baru saja dibuatkan akun
        if (!empty($result['token'])) {
            $payload['token'] = $result['token'];
            $payload['user'] = [
                'id'                => $result['user']->id,
                'name'              => $result['user']->name,
                'email'             => $result['user']->email,
                'role'              => $result['user']->role,
                'whatsapp'          => $validated['phone'],
                'instagram'         => '',
                'can_claim_voucher' => false,
                'has_talkshow_registration' => true,
                'user_number'       => 0,
            ];
        }

        return response()->json($payload, 201);
    }

    // ADMIN: Menampilkan semua talkshow beserta ringkasan kuota & jumlah pendaftar
    public function index()
    {
        $talkshows = Talkshow::withCount([
            'registrations',
            'registrations as active_count'   => fn ($q) => $q->where('status', '!=', 'rejected'),
            'registrations as pending_count'  => fn ($q) => $q->where('status', 'pending'),
            'registrations as approved_count' => fn ($q) => $q->where('status', 'approved'),
        ])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function (Talkshow $t) {
                $t->max_participants = self::MAX_PARTICIPANTS;
                return $t;
            });

        return response()->json($talkshows);
    }

    // ADMIN: Detail satu talkshow beserta daftar pendaftar (urut dari pendaftar terawal)
    public function show($id)
    {
        $talkshow = Talkshow::with([
            'registrations' => fn ($q) => $q->orderBy('created_at', 'asc'),
        ])->findOrFail($id);

        $talkshow->max_participants = self::MAX_PARTICIPANTS;

        return response()->json($talkshow);
    }

    // ADMIN: Tambah data talkshow
    public function store(Request $request)
    {
        $validated = $request->validate([
            'poster'       => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
            'speaker_name' => 'required|string|max:255',
            'icon'         => 'required|string|max:255',
            'theme'        => 'required|string|max:255',
            'schedule'     => 'required|date',
        ]);

        $posterPath = $request->file('poster')->store('talkshows', 'public');

        $talkshow = Talkshow::create([
            'poster'       => $posterPath,
            'speaker_name' => $validated['speaker_name'],
            'icon'         => $validated['icon'],
            'theme'        => $validated['theme'],
            'schedule'     => $validated['schedule'],
        ]);

        return response()->json([
            'message' => 'Data talkshow berhasil ditambahkan.',
            'data'    => $talkshow,
        ], 201);
    }

    // ADMIN: Update data talkshow
    public function update(Request $request, $id)
    {
        $talkshow = Talkshow::findOrFail($id);

        $validated = $request->validate([
            'poster'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'speaker_name' => 'required|string|max:255',
            'icon'         => 'required|string|max:255',
            'theme'        => 'required|string|max:255',
            'schedule'     => 'required|date',
        ]);

        if ($request->hasFile('poster')) {
            if ($talkshow->poster && Storage::disk('public')->exists($talkshow->poster)) {
                Storage::disk('public')->delete($talkshow->poster);
            }
            $talkshow->poster = $request->file('poster')->store('talkshows', 'public');
        }

        $talkshow->speaker_name = $validated['speaker_name'];
        $talkshow->icon         = $validated['icon'];
        $talkshow->theme        = $validated['theme'];
        $talkshow->schedule     = $validated['schedule'];
        $talkshow->save();

        return response()->json([
            'message' => 'Data talkshow berhasil diperbarui.',
            'data'    => $talkshow,
        ]);
    }

    // ADMIN: Hapus data talkshow beserta posternya
    public function destroy($id)
    {
        $talkshow = Talkshow::findOrFail($id);

        if ($talkshow->poster && Storage::disk('public')->exists($talkshow->poster)) {
            Storage::disk('public')->delete($talkshow->poster);
        }

        $talkshow->delete();

        return response()->json([
            'message' => 'Data talkshow berhasil dihapus.',
        ]);
    }

    // ADMIN: Approve / Reject peserta & otomatis tetapkan urutan approved_rank (1-5 vs 6-25)
    public function updateRegistrationStatus(Request $request, $registrationId)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,approved,rejected',
        ]);

        $result = DB::transaction(function () use ($registrationId, $validated) {
            $registration = TalkshowRegistration::lockForUpdate()->findOrFail($registrationId);

            // Kunci baris talkshow agar urutan approved_rank 1..25 tidak bentrok
            Talkshow::lockForUpdate()->findOrFail($registration->talkshow_id);

            // Pastikan user_id tersinkron jika email sudah punya akun
            if (!$registration->user_id) {
                $matchedUser = User::where('email', $registration->email)->first();
                if ($matchedUser) {
                    $registration->user_id = $matchedUser->id;
                }
            }

            if ($validated['status'] === 'approved') {
                if ($registration->status !== 'approved' || !$registration->approved_rank) {
                    $usedRanks = TalkshowRegistration::where('talkshow_id', $registration->talkshow_id)
                        ->where('status', 'approved')
                        ->whereNotNull('approved_rank')
                        ->pluck('approved_rank')
                        ->all();

                    if (count($usedRanks) >= self::MAX_PARTICIPANTS) {
                        return ['state' => 'approved_full'];
                    }

                    // Cari nomor urut terkecil yang belum terpakai antara 1 sampai 25
                    $assignedRank = null;
                    for ($i = 1; $i <= self::MAX_PARTICIPANTS; $i++) {
                        if (!in_array($i, $usedRanks, true)) {
                            $assignedRank = $i;
                            break;
                        }
                    }

                    $registration->approved_rank = $assignedRank;
                }
                $registration->status = 'approved';
            } else {
                $registration->status = $validated['status'];
                $registration->approved_rank = null;
            }

            $registration->save();

            return [
                'state'        => 'updated',
                'registration' => $registration,
            ];
        });

        if ($result['state'] === 'approved_full') {
            return response()->json([
                'message' => 'Kuota peserta yang disetujui untuk talkshow ini sudah mencapai batas maksimal (25 orang).',
            ], 422);
        }

        $reg = $result['registration'];
        $tierMsg = '';
        if ($reg->status === 'approved') {
            $tierMsg = $reg->approved_rank <= self::GOODIE_BAG_QUOTA
                ? " (Urutan #{$reg->approved_rank}: Berhak Free Goodie Bag + Semua 7 Voucher)"
                : " (Urutan #{$reg->approved_rank}: Berhak 6 Voucher Partner tanpa Back2eras)";
        }

        return response()->json([
            'message' => 'Status peserta berhasil diubah menjadi ' . $reg->status . $tierMsg . '.',
            'data'    => $reg,
        ]);
    }

    // ADMIN: Hapus data pendaftaran peserta
    public function destroyRegistration($registrationId)
    {
        $registration = TalkshowRegistration::findOrFail($registrationId);
        $registration->delete();

        return response()->json([
            'message' => 'Data peserta berhasil dihapus.',
        ]);
    }
}
