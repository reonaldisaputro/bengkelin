<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Conversations\LayananBengkelConversation;
use App\Conversations\PengirimProdukConversation;
use BotMan\BotMan\BotMan;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    public function handle()
    {
        $botman = app('botman');

        $botman->hears('{message}', function (BotMan $bot, $message) {
            switch ($message) {
                case '1':
                    $this->showPengirimanProdukMenu($bot);
                    break;
                case '2':
                    $bot->startConversation(new LayananBengkelConversation());
                    break;
                case '3':
                    $this->infoRegister($bot);
                    break;
                case '0':
                    $this->showMainMenu($bot);
                    break;
                default:
                    $bot->reply('Harap masukkan angka pilihan di atas atau ketik 0 untuk kembali ke menu utama.');
            }
        });

        $botman->listen();
    }

    public function showMainMenu(BotMan $bot)
    {
        $bot->reply("Selamat datang di menu utama. Pilih opsi berikut:\n" .
            "1. Pengiriman Produk\n" .
            "2. Layanan Bengkel\n" .
            "3. Cara Mendaftar Akun\n" .
            "0. Kembali ke Menu Utama");
    }

    public function showPengirimanProdukMenu(BotMan $bot)
    {
        $message = "Layanan Pengiriman Produk:\n" .
            "1. Informasi pickup\n" .
            "2. Informasi jasa kurir\n";

        $bot->reply($message);

        $bot->ask('Ketik pilihan Anda:', function ($answer, $bot) {
            $selected = strtolower($answer->getText());

            if ($selected === '1') {
                $bot->say(
                    "❗️INFO PICKUP DI TEMPAT❗️\n\n" .
                    "1. Konsumen dapat langsung mengambil barang tanpa menunggu pengiriman.\n" .
                    "2. Tidak ada biaya tambahan.\n" .
                    "3. Bisa cek kondisi barang.\n\n" .
                    "🛑Persyaratan:\n" .
                    "- Bawa bukti pesanan\n" .
                    "- Datang tepat waktu\n" .
                    "- Lokasi di toko/gudang/cabang\n\n" .
                    "🛑Waktu Pengambilan:\n" .
                    "- Sesuai jam operasional\n" .
                    "- Hubungi penjual untuk detail"
                );
            } elseif ($selected === '2') {
                $bot->say(
                    "❗️INFO JASA KURIR❗️\n\n" .
                    "1. Barang diantar ke alamat.\n" .
                    "2. Bisa lacak status.\n" .
                    "3. Pengiriman cepat.\n\n" .
                    "🛑Biaya:\n" .
                    "- Berdasarkan jarak & berat\n" .
                    "- Bisa flat atau fleksibel\n\n" .
                    "🛑Pilihan Layanan:\n" .
                    "- Reguler, express, same-day\n" .
                    "- Bisa pakai asuransi"
                );
            } else {
                $bot->say("Pilihan tidak valid. Kembali ke menu utama.");
                $this->showMainMenu($bot);
            }
        });
    }

    public function infoRegister(BotMan $bot)
    {
        $url = url('/userregister');
        $bot->reply(
            "❗️ INFO REGISTRASI AKUN ❗️\n\n" .
            "1. Kunjungi: {$url}\n" .
            "2. Isi formulir dengan benar\n" .
            "3. Verifikasi identitas jika diminta\n" .
            "4. Klik 'Daftar' untuk menyelesaikan"
        );
    }
}
