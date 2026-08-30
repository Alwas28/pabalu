<?php

namespace App\Services\WhatsApp;

interface WhatsAppGatewayContract
{
    /**
     * Kirim pesan WhatsApp ke satu nomor.
     *
     * @param string $phoneNumber Format internasional tanpa "+", mis. "62812xxxxxxx"
     *                            (sudah dinormalisasi oleh WhatsAppGatewayManager sebelum sampai sini)
     * @return array{success: bool, message: string} `message` berisi alasan gagal (buat ditampilkan
     *                                                 ke admin) kalau `success` false, atau pesan sukses generik kalau true.
     */
    public function send(string $phoneNumber, string $message): array;
}
