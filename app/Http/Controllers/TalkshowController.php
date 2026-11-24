<?php

namespace App\Http\Controllers;

use App\Models\Talkshow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TalkshowController extends Controller
{
    // USER: Menampilkan talkshow yang sudah di-approve
    public function publicIndex()
    {
        $talkshows = Talkshow::where('status', 'approved')
            ->orderBy('schedule', 'asc')
            ->get();

        return response()->json($talkshows);
    }

    // USER: Pendaftaran talkshow baru (status otomatis pending)
    public function publicStore(Request $request)
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
            'status'       => 'pending',
        ]);

        return response()->json([
            'message' => 'Pendaftaran talkshow berhasil dikirim dan menunggu persetujuan admin.',
            'data'    => $talkshow,
        ], 201);
    }

    // ADMIN: Menampilkan seluruh data talkshow
    public function index()
    {
        $talkshows = Talkshow::orderBy('created_at', 'desc')->get();
        return response()->json($talkshows);
    }

    // ADMIN: Tambah data talkshow langsung
    public function store(Request $request)
    {
        $validated = $request->validate([
            'poster'       => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
            'speaker_name' => 'required|string|max:255',
            'icon'         => 'required|string|max:255',
            'theme'        => 'required|string|max:255',
            'schedule'     => 'required|date',
            'status'       => 'nullable|in:pending,approved,rejected',
        ]);

        $posterPath = $request->file('poster')->store('talkshows', 'public');

        $talkshow = Talkshow::create([
            'poster'       => $posterPath,
            'speaker_name' => $validated['speaker_name'],
            'icon'         => $validated['icon'],
            'theme'        => $validated['theme'],
            'schedule'     => $validated['schedule'],
            'status'       => $validated['status'] ?? 'approved',
        ]);

        return response()->json([
            'message' => 'Data talkshow berhasil ditambahkan.',
            'data'    => $talkshow,
        ], 201);
    }

    // ADMIN: Update data talkshow (poster opsional saat edit)
    public function update(Request $request, $id)
    {
        $talkshow = Talkshow::findOrFail($id);

        $validated = $request->validate([
            'poster'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'speaker_name' => 'required|string|max:255',
            'icon'         => 'required|string|max:255',
            'theme'        => 'required|string|max:255',
            'schedule'     => 'required|date',
            'status'       => 'required|in:pending,approved,rejected',
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
        $talkshow->status       = $validated['status'];
        $talkshow->save();

        return response()->json([
            'message' => 'Data talkshow berhasil diperbarui.',
            'data'    => $talkshow,
        ]);
    }

    // ADMIN: Approval (ubah status pending / approved / rejected)
    public function updateStatus(Request $request, $id)
    {
        $talkshow = Talkshow::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:pending,approved,rejected',
        ]);

        $talkshow->status = $validated['status'];
        $talkshow->save();

        return response()->json([
            'message' => 'Status talkshow berhasil diubah menjadi ' . $validated['status'] . '.',
            'data'    => $talkshow,
        ]);
    }

    // ADMIN: Hapus data talkshow beserta file posternya
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
}
