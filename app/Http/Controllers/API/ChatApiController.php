<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ChatApiController extends Controller
{
    public function send(Request $r)
    {
        $user = $r->user();
        $msg  = trim((string) $r->input('message',''));
        $payload = trim((string) $r->input('payload','')); // dari quick reply
        $ctx = $r->input('context_id'); // opsional, kalau mau multi-turn

        $out = ['messages'=>[], 'quick_replies'=>[], 'context_id'=>$ctx, 'end'=>false];

        // --- Router intent sederhana (gratis & deterministik) ---
        if ($payload === 'menu' || $msg === '' ) {
            $out['messages'][] = ['type'=>'text','text'=>"Hai {$user->name}! Pilih layanan di bawah:"];
            $out['quick_replies'] = [
                ['title'=>'Status Pesanan','payload'=>'status_prompt'],
                ['title'=>'Bengkel Terdekat','payload'=>'nearby_prompt'],
                ['title'=>'Item Belum Dirating','payload'=>'rate_list'],
                ['title'=>'FAQ','payload'=>'faq_prompt'],
            ];
            return ResponseFormatter::success($out, 'ok');
        }

        if ($payload === 'status_prompt' || preg_match('/^status\b/i',$msg)) {
            if (preg_match('/\b(TRANS-\w+)\b/i',$msg,$m)) {
                $code = $m[1];
                $trx = \App\Models\Transaction::with('detail_transactions.product')
                        ->where('transaction_code',$code)
                        ->where('user_id',$user->id)->first();
                if (!$trx) {
                    $out['messages'][]=['type'=>'text','text'=>"Pesanan {$code} tidak ditemukan."];
                } else {
                    $items = $trx->detail_transactions->pluck('product.name')->filter()->implode(', ');
                    $out['messages'][]=['type'=>'text','text'=>"Status {$code}:\n- Pembayaran: {$trx->payment_status}\n- Pengiriman: ".($trx->shipping_status ?? '-')."\n- Item: {$items}\n- Total: Rp ".number_format($trx->grand_total,0,',','.')];
                    $out['messages'][]=['type'=>'link','title'=>'Lihat detail','url'=>url('/profile-transaction/'.$trx->id)];
                }
                return ResponseFormatter::success($out, 'ok');
            }
            $out['messages'][]=['type'=>'text','text'=>'Ketik: status TRANS-123'];
            return ResponseFormatter::success($out, 'ok');
        }

        if ($payload === 'rate_list' || preg_match('/(rating|ulas)/i',$msg)) {
            $items = \App\Models\DetailTransaction::with(['product','transaction'])
                ->whereHas('transaction', fn($q)=>$q->where('user_id',$user->id)
                    ->whereIn('payment_status',['success','paid','completed']))
                ->whereDoesntHave('rating', fn($q)=>$q->where('user_id',$user->id))
                ->latest()->take(5)->get();

            if ($items->isEmpty()) {
                $out['messages'][]=['type'=>'text','text'=>'Tidak ada item yang perlu dirating. 👍'];
            } else {
                foreach ($items as $it) {
                    $name = $it->product?->name ?? 'Item';
                    $out['messages'][]=['type'=>'text','text'=>"Belum dirating: {$name} (qty {$it->qty})\nKetik: rate {$it->id} 5 \"komentar\""];
                }
            }
            return ResponseFormatter::success($out, 'ok');
        }

        if (preg_match('/^rate\s+(\d+)\s+([1-5])(?:\s+"(.*)")?$/i',$msg,$m)) {
            [$all,$detailId,$stars,$comment] = $m;
            $detail = \App\Models\DetailTransaction::with('transaction','product')->find($detailId);
            if (!$detail || $detail->transaction->user_id !== $user->id) {
                $out['messages'][]=['type'=>'text','text'=>'Item tidak valid.'];
                return ResponseFormatter::success($out,'ok');
            }
            $already = \App\Models\Rating::where([
                'user_id'=>$user->id,'detail_transaction_id'=>$detail->id
            ])->exists();
            if ($already) {
                $out['messages'][]=['type'=>'text','text'=>'Item ini sudah pernah diberi ulasan.'];
                return ResponseFormatter::success($out,'ok');
            }
            \App\Models\Rating::create([
                'user_id'=>$user->id,
                'product_id'=>$detail->product_id,
                'transaction_id'=>$detail->transaction_id,
                'detail_transaction_id'=>$detail->id,
                'stars'=> (int)$stars,
                'comment'=> $comment ?: null,
            ]);
            $out['messages'][]=['type'=>'text','text'=>'Terima kasih! Ulasan kamu tersimpan.'];
            return ResponseFormatter::success($out,'ok');
        }

        if ($payload === 'nearby_prompt' || preg_match('/^bengkel terdekat/i',$msg)) {
            $out['messages'][]=['type'=>'text','text'=>'Kirim lokasi: "bengkel terdekat -6.2,106.8"'];
            return ResponseFormatter::success($out,'ok');
        }
        if (preg_match('/^bengkel terdekat\s+([\-0-9\.]+),\s*([\-0-9\.]+)/i',$msg,$m)) {
            [$all,$lat,$lng]=$m;
            $bengkels = \App\Models\Bengkel::select('*')
                ->selectRaw('(111.045*DEGREES(ACOS(LEAST(1.0, COS(RADIANS(?))*COS(RADIANS(latitude))*COS(RADIANS(longitude)-RADIANS(?))+SIN(RADIANS(?))*SIN(RADIANS(latitude)))))) AS distance_km',[$lat,$lng,$lat])
                ->orderBy('distance_km')->limit(3)->get();
            if ($bengkels->isEmpty()) {
                $out['messages'][]=['type'=>'text','text'=>'Tidak ada bengkel terdekat.'];
            } else {
                foreach ($bengkels as $b) {
                    $out['messages'][]=[
                        'type'=>'card',
                        'title'=>$b->name,
                        'subtitle'=>$b->alamat.' • ±'.round($b->distance_km,1).' km',
                        'actions'=>[['label'=>'Detail','url'=>url('/detailbengkelpage/'.$b->id)]],
                    ];
                }
            }
            return ResponseFormatter::success($out,'ok');
        }

        // fallback
        $out['messages'][]=['type'=>'text','text'=>'Saya kurang paham. Pilih menu di bawah ya 🙂'];
        $out['quick_replies'] = [
            ['title'=>'Menu','payload'=>'menu'],
            ['title'=>'Status Pesanan','payload'=>'status_prompt'],
            ['title'=>'Bengkel Terdekat','payload'=>'nearby_prompt'],
            ['title'=>'Item Belum Dirating','payload'=>'rate_list'],
        ];
        return ResponseFormatter::success($out, 'ok');
    }
}
