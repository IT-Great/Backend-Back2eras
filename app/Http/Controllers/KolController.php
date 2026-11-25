<?php

namespace App\Http\Controllers;

use App\Models\Kol;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class KolController extends Controller
{
    // PUBLIC: Registrasi KOL -> insert ke tabel `users` (role = kol) DAN `kols`, langsung login
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'address'           => 'required|string|max:1000',
            'icon'              => 'required|string|max:255',
            'email'             => 'required|email|max:255|unique:users,email',
            'password'          => 'required|string|min:6',
            'whatsapp'          => 'required|string|max:25',
            'social_media_link' => 'nullable|string|max:500',
        ]);

        $socialLink = isset($validated['social_media_link'])
            ? trim($validated['social_media_link'])
            : null;
        if ($socialLink === '') {
            $socialLink = null;
        }

        [$user, $kol] = DB::transaction(function () use ($validated, $socialLink) {
            $user = User::create([
                'name'     => $validated['name'],
                'email'    => $validated['email'],
                'password' => $validated['password'], // otomatis di-hash oleh cast User
                'role'     => 'kol',
            ]);

            $kol = Kol::create([
                'user_id'           => $user->id,
                'name'              => $validated['name'],
                'address'           => $validated['address'],
                'icon'              => $validated['icon'],
                'email'             => $validated['email'],
                'whatsapp'          => $validated['whatsapp'],
                'social_media_link' => $socialLink,
            ]);

            return [$user, $kol];
        });

        $token = $user->createToken('visitor_auth_token')->plainTextToken;
        $canClaim = !empty($kol->social_media_link);

        return response()->json([
            'success' => true,
            'message' => $canClaim
                ? 'Pendaftaran KOL berhasil! Akun Anda aktif dan dapat mengklaim voucher.'
                : 'Pendaftaran KOL berhasil! Lengkapi link media sosial di Edit Profil agar dapat mengklaim voucher.',
            'token'   => $token,
            'user'    => [
                'id'                => $user->id,
                'name'              => $user->name,
                'email'             => $user->email,
                'role'              => 'kol',
                'whatsapp'          => $kol->whatsapp,
                'instagram'         => '',
                'address'           => $kol->address,
                'icon'              => $kol->icon,
                'social_media_link' => $kol->social_media_link ?? '',
                'can_claim_voucher' => $canClaim,
                'user_number'       => 0,
            ],
            'data'    => $kol,
        ], 201);
    }

    // ADMIN: Daftar seluruh KOL beserta status kelayakan voucher
    public function index()
    {
        $kols = Kol::withCount(['user'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function (Kol $kol) {
                $vouchersCount = $kol->user_id
                    ? \App\Models\Voucher::where('user_id', $kol->user_id)->count()
                    : 0;

                return [
                    'id'                => $kol->id,
                    'user_id'           => $kol->user_id,
                    'name'              => $kol->name,
                    'address'           => $kol->address,
                    'icon'              => $kol->icon,
                    'email'             => $kol->email,
                    'whatsapp'          => $kol->whatsapp,
                    'social_media_link' => $kol->social_media_link,
                    'can_claim_voucher' => !empty(trim((string) $kol->social_media_link)),
                    'vouchers_count'    => $vouchersCount,
                    'created_at'        => $kol->created_at,
                ];
            });

        return response()->json($kols);
    }

    // ADMIN: Tambah data KOL baru (sekaligus buatkan akun di tabel `users`)
    public function adminStore(Request $request)
    {
        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'address'           => 'required|string|max:1000',
            'icon'              => 'required|string|max:255',
            'email'             => 'required|email|max:255|unique:users,email',
            'password'          => 'nullable|string|min:6',
            'whatsapp'          => 'required|string|max:25',
            'social_media_link' => 'nullable|string|max:500',
        ]);

        $socialLink = isset($validated['social_media_link']) && trim($validated['social_media_link']) !== ''
            ? trim($validated['social_media_link'])
            : null;

        $kol = DB::transaction(function () use ($validated, $socialLink) {
            $user = User::create([
                'name'     => $validated['name'],
                'email'    => $validated['email'],
                'password' => $validated['password'] ?: 'back2eras2026',
                'role'     => 'kol',
            ]);

            return Kol::create([
                'user_id'           => $user->id,
                'name'              => $validated['name'],
                'address'           => $validated['address'],
                'icon'              => $validated['icon'],
                'email'             => $validated['email'],
                'whatsapp'          => $validated['whatsapp'],
                'social_media_link' => $socialLink,
            ]);
        });

        return response()->json([
            'message' => 'Data KOL berhasil ditambahkan.',
            'data'    => $kol,
        ], 201);
    }

    // ADMIN: Update data KOL (sinkron juga ke tabel `users`)
    public function update(Request $request, $id)
    {
        $kol = Kol::findOrFail($id);

        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'address'           => 'required|string|max:1000',
            'icon'              => 'required|string|max:255',
            'email'             => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($kol->user_id),
            ],
            'password'          => 'nullable|string|min:6',
            'whatsapp'          => 'required|string|max:25',
            'social_media_link' => 'nullable|string|max:500',
        ]);

        $socialLink = isset($validated['social_media_link']) && trim($validated['social_media_link']) !== ''
            ? trim($validated['social_media_link'])
            : null;

        DB::transaction(function () use ($kol, $validated, $socialLink) {
            $kol->update([
                'name'              => $validated['name'],
                'address'           => $validated['address'],
                'icon'              => $validated['icon'],
                'email'             => $validated['email'],
                'whatsapp'          => $validated['whatsapp'],
                'social_media_link' => $socialLink,
            ]);

            if ($kol->user_id) {
                $user = User::find($kol->user_id);
                if ($user) {
                    $user->name  = $validated['name'];
                    $user->email = $validated['email'];
                    if (!empty($validated['password'])) {
                        $user->password = $validated['password'];
                    }
                    $user->save();
                }
            }
        });

        return response()->json([
            'message' => 'Data KOL berhasil diperbarui.',
            'data'    => $kol->fresh(),
        ]);
    }

    // ADMIN: Hapus data KOL (beserta akun user ber-role `kol` terkait)
    public function destroy($id)
    {
        $kol = Kol::findOrFail($id);

        DB::transaction(function () use ($kol) {
            $userId = $kol->user_id;
            $kol->delete();

            if ($userId) {
                User::where('id', $userId)->where('role', 'kol')->delete();
            }
        });

        return response()->json([
            'message' => 'Data KOL berhasil dihapus.',
        ]);
    }
}
