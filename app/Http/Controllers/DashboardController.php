<?php

// namespace App\Http\Controllers;

// use App\Models\Visitor;
// use App\Models\Tenant;
// use App\Models\Partner;
// use App\Models\Media;
// use App\Models\Contact;
// use App\Models\Story;
// use App\Models\VendorCategory;
// use Illuminate\Http\Request;

// class DashboardController extends Controller
// {
//     public function getStats()
//     {
//         return response()->json([
//             'visitors'   => Visitor::count(),
//             'tenants'    => Tenant::count(),
//             'partners'   => Partner::count(),
//             'media'      => Media::count(),
//             'contacts'   => Contact::count(),
//             'stories'    => Story::count(),
//             'categories' => VendorCategory::count(),
//         ]);
//     }
// }

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Kol;
use App\Models\Media;
use App\Models\Partner;
use App\Models\Story;
use App\Models\Talkshow;
use App\Models\TalkshowRegistration;
use App\Models\Tenant;
use App\Models\VendorCategory;
use App\Models\Visitor;
use App\Models\Voucher;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function getStats()
    {
        $now = Carbon::now('Asia/Jakarta');

        // Visitor & Community Code
        $totalVisitors = Visitor::count();
        $visitorsWithCommunity = Visitor::whereNotNull('community_code')
            ->where('community_code', '!=', '')
            ->count();
        $newsletterSubscribers = Visitor::where('subscribe_newsletter', true)->count();

        // KOL
        $totalKols = Kol::count();
        $verifiedKols = Kol::whereNotNull('social_media_link')
            ->where('social_media_link', '!=', '')
            ->count();

        // Talkshow
        $totalTalkshows = Talkshow::count();
        $talkshowPending = TalkshowRegistration::where('status', 'pending')->count();
        $talkshowApproved = TalkshowRegistration::where('status', 'approved')->count();
        $talkshowRejected = TalkshowRegistration::where('status', 'rejected')->count();

        // Voucher (Reguler vs Talkshow, Used vs Unused vs Expired)
        $vouchers = Voucher::all(['source', 'is_used', 'expires_at']);
        $vouchersTotal = $vouchers->count();
        $vouchersUsed = $vouchers->where('is_used', true)->count();
        $vouchersExpired = $vouchers->filter(
            fn ($v) => !$v->is_used && $v->expires_at && $now->gt($v->expires_at)
        )->count();
        $vouchersUnused = max(0, $vouchersTotal - $vouchersUsed - $vouchersExpired);
        $vouchersRegular = $vouchers->filter(fn ($v) => ($v->source ?? 'regular') === 'regular')->count();
        $vouchersTalkshow = $vouchers->where('source', 'talkshow')->count();

        return response()->json([
            // Statistik Utama (Kompatibel dengan struktur lama)
            'visitors'   => $totalVisitors,
            'tenants'    => Tenant::count(),
            'partners'   => Partner::count(),
            'media'      => Media::count(),
            'contacts'   => Contact::count(),
            'stories'    => Story::count(),
            'categories' => VendorCategory::count(),

            // Modul Baru: KOL, Talkshow, & Voucher
            'kols'                    => $totalKols,
            'kols_verified'           => $verifiedKols,
            'kols_unverified'         => max(0, $totalKols - $verifiedKols),

            'talkshows'               => $totalTalkshows,
            'talkshow_registrations'  => $talkshowPending + $talkshowApproved + $talkshowRejected,
            'talkshow_pending'        => $talkshowPending,
            'talkshow_approved'       => $talkshowApproved,
            'talkshow_rejected'       => $talkshowRejected,

            'vouchers_total'          => $vouchersTotal,
            'vouchers_unused'         => $vouchersUnused,
            'vouchers_used'           => $vouchersUsed,
            'vouchers_expired'        => $vouchersExpired,
            'vouchers_regular'        => $vouchersRegular,
            'vouchers_talkshow'       => $vouchersTalkshow,

            // Rincian Visitor
            'visitors_community'      => $visitorsWithCommunity,
            'visitors_newsletter'     => $newsletterSubscribers,
        ]);
    }
}
