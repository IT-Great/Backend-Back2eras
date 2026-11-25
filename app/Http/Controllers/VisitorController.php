<?php

// namespace App\Http\Controllers;

// use App\Models\Visitor;
// use Illuminate\Http\Request;

// class VisitorController extends Controller
// {
//     // Menampilkan daftar pengunjung (Untuk halaman Admin nanti)
//     public function index()
//     {
//         $visitors = Visitor::orderBy('created_at', 'desc')->get();
//         return response()->json($visitors);
//     }

//     // Menerima input form dari Frontend (Visitor Registration)
//     public function store(Request $request)
//     {
//         $validated = $request->validate([
//             'full_name'            => 'required|string|max:255',
//             'email'                => 'required|email|max:255',
//             'whatsapp'             => 'required|string|max:20',
//             'instagram'            => 'nullable|string|max:255',
//             'subscribe_newsletter' => 'boolean'
//         ]);

//         $visitor = Visitor::create($validated);

//         return response()->json([
//             'success' => true,
//             'message' => "Welcome to BACK2ERAS! You're on the list.",
//             'data'    => $visitor
//         ], 201);
//     }

//     // Menghapus data pengunjung (Untuk Admin)
//     public function destroy($id)
//     {
//         Visitor::destroy($id);
//         return response()->json(['message' => 'Visitor data deleted successfully']);
//     }
// }

// namespace App\Http\Controllers;

// use App\Models\User;
// use App\Models\Visitor;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\Hash;
// use Illuminate\Validation\Rule;

// class VisitorController extends Controller
// {
//     // Helper untuk menghitung urutan user ke-X berdasarkan tabel visitors
//     private function getUserNumber(?Visitor $visitor): int
//     {
//         if (!$visitor) {
//             return 0;
//         }
//         return Visitor::where('id', '<=', $visitor->id)->count();
//     }

//     // Helper format data profil gabungan untuk frontend
//     private function formatUserPayload(User $user, ?Visitor $visitor): array
//     {
//         return [
//             'id'                   => $user->id,
//             'name'                 => $user->name,
//             'email'                => $user->email,
//             'role'                 => $user->role,
//             'whatsapp'             => $visitor?->whatsapp ?? '',
//             'instagram'            => $visitor?->instagram ?? '',
//             'subscribe_newsletter' => (bool) ($visitor?->subscribe_newsletter ?? false),
//             'user_number'          => $this->getUserNumber($visitor),
//         ];
//     }

//     // ADMIN: Menampilkan daftar pengunjung
//     public function index()
//     {
//         $visitors = Visitor::orderBy('created_at', 'desc')->get();
//         return response()->json($visitors);
//     }

//     // PUBLIC: Register Visitor -> simpan ke tabel `users` DAN `visitors`, langsung login
//     public function store(Request $request)
//     {
//         $validated = $request->validate([
//             'full_name'            => 'required|string|max:255',
//             'email'                => 'required|email|max:255|unique:users,email',
//             'password'             => 'required|string|min:6',
//             'whatsapp'             => 'required|string|max:25',
//             'instagram'            => 'nullable|string|max:255',
//             'subscribe_newsletter' => 'boolean',
//         ]);

//         [$user, $visitor] = DB::transaction(function () use ($validated) {
//             // 1. Insert ke tabel `users`
//             $user = User::create([
//                 'name'     => $validated['full_name'],
//                 'email'    => $validated['email'],
//                 'password' => $validated['password'], // otomatis di-hash oleh cast User
//                 'role'     => 'visitor',
//             ]);

//             // 2. Insert ke tabel `visitors` dengan data yang sama
//             $visitor = Visitor::create([
//                 'user_id'              => $user->id,
//                 'full_name'            => $validated['full_name'],
//                 'email'                => $validated['email'],
//                 'whatsapp'             => $validated['whatsapp'],
//                 'instagram'            => $validated['instagram'] ?? null,
//                 'subscribe_newsletter' => $validated['subscribe_newsletter'] ?? false,
//             ]);

//             return [$user, $visitor];
//         });

//         $token = $user->createToken('visitor_auth_token')->plainTextToken;

//         return response()->json([
//             'success' => true,
//             'message' => "Welcome to BACK2ERAS! You're on the list.",
//             'token'   => $token,
//             'user'    => $this->formatUserPayload($user, $visitor),
//         ], 201);
//     }

//     // PUBLIC: Login Visitor (via pop-up di Header)
//     public function login(Request $request)
//     {
//         $validated = $request->validate([
//             'email'    => 'required|email',
//             'password' => 'required|string',
//         ]);

//         $user = User::where('email', $validated['email'])->first();

//         if (!$user || !Hash::check($validated['password'], $user->password)) {
//             return response()->json([
//                 'message' => 'Email atau password salah.',
//             ], 401);
//         }

//         // Ambil data visitor terkait (fallback ke email jika akun lama belum punya user_id)
//         $visitor = Visitor::where('user_id', $user->id)
//             ->orWhere('email', $user->email)
//             ->first();

//         $token = $user->createToken('visitor_auth_token')->plainTextToken;

//         return response()->json([
//             'success' => true,
//             'message' => 'Login berhasil!',
//             'token'   => $token,
//             'user'    => $this->formatUserPayload($user, $visitor),
//         ]);
//     }

//     // PROTECTED: Ambil profil & urutan user yang sedang login
//     public function me(Request $request)
//     {
//         $user = $request->user();
//         $visitor = Visitor::where('user_id', $user->id)
//             ->orWhere('email', $user->email)
//             ->first();

//         return response()->json([
//             'user' => $this->formatUserPayload($user, $visitor),
//         ]);
//     }

//     // PROTECTED: Edit Profil (sinkronisasi ke tabel `users` dan `visitors`)
//     public function updateProfile(Request $request)
//     {
//         $user = $request->user();

//         $validated = $request->validate([
//             'full_name' => 'required|string|max:255',
//             'email'     => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
//             'whatsapp'  => 'required|string|max:25',
//             'instagram' => 'nullable|string|max:255',
//             'password'  => 'nullable|string|min:6',
//         ]);

//         $visitor = DB::transaction(function () use ($user, $validated) {
//             $oldEmail = $user->email;

//             // Update tabel users
//             $user->name  = $validated['full_name'];
//             $user->email = $validated['email'];
//             if (!empty($validated['password'])) {
//                 $user->password = $validated['password'];
//             }
//             $user->save();

//             // Update tabel visitors
//             $visitor = Visitor::where('user_id', $user->id)
//                 ->orWhere('email', $oldEmail)
//                 ->first();

//             if ($visitor) {
//                 $visitor->update([
//                     'user_id'   => $user->id,
//                     'full_name' => $validated['full_name'],
//                     'email'     => $validated['email'],
//                     'whatsapp'  => $validated['whatsapp'],
//                     'instagram' => $validated['instagram'] ?? null,
//                 ]);
//             }

//             return $visitor;
//         });

//         return response()->json([
//             'success' => true,
//             'message' => 'Profil berhasil diperbarui.',
//             'user'    => $this->formatUserPayload($user, $visitor),
//         ]);
//     }

//     // PROTECTED: Logout Visitor
//     public function logout(Request $request)
//     {
//         $request->user()->currentAccessToken()->delete();

//         return response()->json([
//             'success' => true,
//             'message' => 'Logout berhasil.',
//         ]);
//     }

//     // ADMIN: Hapus data pengunjung
//     public function destroy($id)
//     {
//         Visitor::destroy($id);
//         return response()->json(['message' => 'Visitor data deleted successfully']);
//     }
// }

namespace App\Http\Controllers;

use App\Models\Kol;
use App\Models\User;
use App\Models\Visitor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class VisitorController extends Controller
{
    private function getUserNumber(?Visitor $visitor): int
    {
        if (!$visitor) {
            return 0;
        }
        return Visitor::where('id', '<=', $visitor->id)->count();
    }

    private function formatUserPayload(User $user, ?Visitor $visitor = null, ?Kol $kol = null): array
    {
        if ($user->role === 'kol') {
            $kol = $kol ?? Kol::where('user_id', $user->id)
                ->orWhere('email', $user->email)
                ->first();

            $socialLink = trim((string) ($kol?->social_media_link ?? ''));

            return [
                'id'                   => $user->id,
                'name'                 => $user->name,
                'email'                => $user->email,
                'role'                 => 'kol',
                'whatsapp'             => $kol?->whatsapp ?? '',
                'instagram'            => '',
                'address'              => $kol?->address ?? '',
                'icon'                 => $kol?->icon ?? '',
                'social_media_link'    => $socialLink,
                'can_claim_voucher'    => $socialLink !== '',
                'subscribe_newsletter' => false,
                'user_number'          => 0,
            ];
        }

        if ($user->role === 'talkshow') {
            $reg = \App\Models\TalkshowRegistration::where('user_id', $user->id)
                ->orWhere('email', $user->email)
                ->orderBy('created_at', 'desc')
                ->first();

            $isApproved = \App\Models\TalkshowRegistration::where(function ($q) use ($user) {
                $q->where('user_id', $user->id)->orWhere('email', $user->email);
            })->where('status', 'approved')->exists();

            return [
                'id'                   => $user->id,
                'name'                 => $user->name,
                'email'                => $user->email,
                'role'                 => 'talkshow',
                'whatsapp'             => $reg?->phone ?? '',
                'instagram'            => '',
                'address'              => '',
                'icon'                 => '',
                'social_media_link'    => '',
                'can_claim_voucher'    => $isApproved,
                'subscribe_newsletter' => false,
                'user_number'          => 0,
            ];
        }

        $visitor = $visitor ?? Visitor::where('user_id', $user->id)
            ->orWhere('email', $user->email)
            ->first();

        return [
            'id'                   => $user->id,
            'name'                 => $user->name,
            'email'                => $user->email,
            'role'                 => $user->role,
            'whatsapp'             => $visitor?->whatsapp ?? '',
            'instagram'            => $visitor?->instagram ?? '',
            'address'              => '',
            'icon'                 => '',
            'social_media_link'    => '',
            'can_claim_voucher'    => true,
            'subscribe_newsletter' => (bool) ($visitor?->subscribe_newsletter ?? false),
            'user_number'          => $this->getUserNumber($visitor),
        ];
    }

    // ADMIN: Menampilkan daftar pengunjung
    public function index()
    {
        $visitors = Visitor::orderBy('created_at', 'desc')->get();
        return response()->json($visitors);
    }

    // PUBLIC: Register Visitor -> simpan ke tabel `users` DAN `visitors`, langsung login
    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name'            => 'required|string|max:255',
            'email'                => 'required|email|max:255|unique:users,email',
            'password'             => 'required|string|min:6',
            'whatsapp'             => 'required|string|max:25',
            'instagram'            => 'nullable|string|max:255',
            'subscribe_newsletter' => 'boolean',
        ]);

        [$user, $visitor] = DB::transaction(function () use ($validated) {
            $user = User::create([
                'name'     => $validated['full_name'],
                'email'    => $validated['email'],
                'password' => $validated['password'],
                'role'     => 'visitor',
            ]);

            $visitor = Visitor::create([
                'user_id'              => $user->id,
                'full_name'            => $validated['full_name'],
                'email'                => $validated['email'],
                'whatsapp'             => $validated['whatsapp'],
                'instagram'            => $validated['instagram'] ?? null,
                'subscribe_newsletter' => $validated['subscribe_newsletter'] ?? false,
            ]);

            return [$user, $visitor];
        });

        $token = $user->createToken('visitor_auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => "Welcome to BACK2ERAS! You're on the list.",
            'token'   => $token,
            'user'    => $this->formatUserPayload($user, $visitor),
        ], 201);
    }

    // PUBLIC: Login Visitor maupun KOL (via pop-up di Header)
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Email atau password salah.',
            ], 401);
        }

        $token = $user->createToken('visitor_auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil!',
            'token'   => $token,
            'user'    => $this->formatUserPayload($user),
        ]);
    }

    // PROTECTED: Ambil profil user yang sedang login (Visitor atau KOL)
    public function me(Request $request)
    {
        return response()->json([
            'user' => $this->formatUserPayload($request->user()),
        ]);
    }

    // PROTECTED: Edit Profil (sinkronisasi ke `users` + `visitors` atau `kols`)
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'kol') {
            $validated = $request->validate([
                'full_name'         => 'required|string|max:255',
                'email'             => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
                'whatsapp'          => 'required|string|max:25',
                'address'           => 'required|string|max:1000',
                'icon'              => 'required|string|max:255',
                'social_media_link' => 'nullable|string|max:500',
                'password'          => 'nullable|string|min:6',
            ]);

            $socialLink = isset($validated['social_media_link']) && trim($validated['social_media_link']) !== ''
                ? trim($validated['social_media_link'])
                : null;

            $kol = DB::transaction(function () use ($user, $validated, $socialLink) {
                $oldEmail = $user->email;

                $user->name  = $validated['full_name'];
                $user->email = $validated['email'];
                if (!empty($validated['password'])) {
                    $user->password = $validated['password'];
                }
                $user->save();

                $kol = Kol::where('user_id', $user->id)
                    ->orWhere('email', $oldEmail)
                    ->first();

                if ($kol) {
                    $kol->update([
                        'user_id'           => $user->id,
                        'name'              => $validated['full_name'],
                        'email'             => $validated['email'],
                        'whatsapp'          => $validated['whatsapp'],
                        'address'           => $validated['address'],
                        'icon'              => $validated['icon'],
                        'social_media_link' => $socialLink,
                    ]);
                }

                return $kol;
            });

            return response()->json([
                'success' => true,
                'message' => 'Profil KOL berhasil diperbarui.',
                'user'    => $this->formatUserPayload($user, null, $kol),
            ]);
        }

        if ($user->role === 'talkshow') {
            $validated = $request->validate([
                'full_name' => 'required|string|max:255',
                'email'     => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
                'whatsapp'  => 'required|string|max:25',
                'password'  => 'nullable|string|min:6',
            ]);

            DB::transaction(function () use ($user, $validated) {
                $oldEmail = $user->email;

                $user->name  = $validated['full_name'];
                $user->email = $validated['email'];
                if (!empty($validated['password'])) {
                    $user->password = $validated['password'];
                }
                $user->save();

                \App\Models\TalkshowRegistration::where('user_id', $user->id)
                    ->orWhere('email', $oldEmail)
                    ->update([
                        'user_id' => $user->id,
                        'name'    => $validated['full_name'],
                        'email'   => $validated['email'],
                        'phone'   => $validated['whatsapp'],
                    ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Profil peserta Talkshow berhasil diperbarui.',
                'user'    => $this->formatUserPayload($user->fresh()),
            ]);
        }

        // Default: Visitor
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email'     => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'whatsapp'  => 'required|string|max:25',
            'instagram' => 'nullable|string|max:255',
            'password'  => 'nullable|string|min:6',
        ]);

        $visitor = DB::transaction(function () use ($user, $validated) {
            $oldEmail = $user->email;

            $user->name  = $validated['full_name'];
            $user->email = $validated['email'];
            if (!empty($validated['password'])) {
                $user->password = $validated['password'];
            }
            $user->save();

            $visitor = Visitor::where('user_id', $user->id)
                ->orWhere('email', $oldEmail)
                ->first();

            if ($visitor) {
                $visitor->update([
                    'user_id'   => $user->id,
                    'full_name' => $validated['full_name'],
                    'email'     => $validated['email'],
                    'whatsapp'  => $validated['whatsapp'],
                    'instagram' => $validated['instagram'] ?? null,
                ]);
            }

            return $visitor;
        });

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diperbarui.',
            'user'    => $this->formatUserPayload($user, $visitor),
        ]);
    }

    // PROTECTED: Logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil.',
        ]);
    }

    // ADMIN: Hapus data pengunjung
    public function destroy($id)
    {
        Visitor::destroy($id);
        return response()->json(['message' => 'Visitor data deleted successfully']);
    }
}
