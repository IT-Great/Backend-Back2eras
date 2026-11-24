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

<?php

namespace App\Http\Controllers;

use App\Models\Talkshow;
use App\Models\TalkshowRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TalkshowController extends Controller
{
    // USER: Menampilkan semua jadwal talkshow
    public function publicIndex()
    {
        $talkshows = Talkshow::withCount([
            'registrations as approved_participants_count' => function ($query) {
                $query->where('status', 'approved');
            },
        ])
            ->orderBy('schedule', 'asc')
            ->get();

        return response()->json($talkshows);
    }

    // USER: Mendaftar sebagai peserta pada salah satu talkshow (status otomatis pending)
    public function registerParticipant(Request $request, $id)
    {
        $talkshow = Talkshow::findOrFail($id);

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|max:255',
            'phone'       => 'required|string|max:30',
            'institution' => 'nullable|string|max:255',
        ]);

        // Cegah pendaftaran ganda dengan email yang sama di talkshow yang sama
        $alreadyRegistered = TalkshowRegistration::where('talkshow_id', $talkshow->id)
            ->where('email', $validated['email'])
            ->exists();

        if ($alreadyRegistered) {
            return response()->json([
                'message' => 'Email ini sudah terdaftar pada sesi talkshow tersebut.',
            ], 422);
        }

        $registration = TalkshowRegistration::create([
            'talkshow_id' => $talkshow->id,
            'name'        => $validated['name'],
            'email'       => $validated['email'],
            'phone'       => $validated['phone'],
            'institution' => $validated['institution'] ?? null,
            'status'      => 'pending',
        ]);

        return response()->json([
            'message' => 'Pendaftaran berhasil dikirim! Menunggu persetujuan admin.',
            'data'    => $registration,
        ], 201);
    }

    // ADMIN: Menampilkan semua talkshow beserta ringkasan jumlah pendaftar
    public function index()
    {
        $talkshows = Talkshow::withCount([
            'registrations',
            'registrations as pending_count' => fn ($q) => $q->where('status', 'pending'),
            'registrations as approved_count' => fn ($q) => $q->where('status', 'approved'),
        ])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($talkshows);
    }

    // ADMIN: Detail satu talkshow beserta seluruh daftar pendaftarnya
    public function show($id)
    {
        $talkshow = Talkshow::with([
            'registrations' => fn ($q) => $q->orderBy('created_at', 'desc'),
        ])->findOrFail($id);

        return response()->json($talkshow);
    }

    // ADMIN: Tambah data talkshow (tanpa status approval)
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

    // ADMIN: Update data talkshow (tanpa status approval)
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

    // ADMIN: Hapus data talkshow beserta posternya (peserta otomatis terhapus karena cascade)
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

    // ADMIN: Approve / Reject peserta yang mendaftar pada talkshow
    public function updateRegistrationStatus(Request $request, $registrationId)
    {
        $registration = TalkshowRegistration::findOrFail($registrationId);

        $validated = $request->validate([
            'status' => 'required|in:pending,approved,rejected',
        ]);

        $registration->status = $validated['status'];
        $registration->save();

        return response()->json([
            'message' => 'Status peserta berhasil diubah menjadi ' . $validated['status'] . '.',
            'data'    => $registration,
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
