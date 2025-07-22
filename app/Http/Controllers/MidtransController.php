<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MidtransController extends Controller
{
    /**
     * Handle Midtrans notification webhook.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function handleWebhook(Request $request)
    {
        // 1. Ambil semua data notifikasi dari Midtrans
        $notificationPayload = $request->all();
        
        // Untuk debugging, Anda bisa menyimpan payload ke log
        Log::info('Midtrans Webhook Received:', $notificationPayload);

        // 2. Set Server Key Anda
        $serverKey = config('midtrans.server_key');

        // 3. Buat Signature Key dari data notifikasi
        // Formula: sha512(order_id . status_code . gross_amount . server_key)
        $orderId = $notificationPayload['order_id'];
        $statusCode = $notificationPayload['status_code'];
        $grossAmount = $notificationPayload['gross_amount'];
        $signature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        // 4. Validasi Signature Key
        if ($signature !== $notificationPayload['signature_key']) {
            // Jika signature tidak valid, kirim response error
            Log::error('Midtrans Webhook: Invalid signature.');
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        // 5. Jika signature valid, proses status transaksi
        $transactionStatus = $notificationPayload['transaction_status'];
        $fraudStatus = $notificationPayload['fraud_status'];

        // Contoh: Cari transaksi berdasarkan order_id di database Anda
        // $transaction = Transaction::where('order_id', $orderId)->first();
        // if (!$transaction) {
        //     Log::error("Midtrans Webhook: Transaction with order_id {$orderId} not found.");
        //     return response()->json(['message' => 'Transaction not found'], 404);
        // }

        // Gunakan switch-case untuk menangani berbagai status
        switch ($transactionStatus) {
            case 'capture':
                // Untuk transaksi kartu kredit
                if ($fraudStatus == 'accept') {
                    // TODO: Update status transaksi di database Anda menjadi 'berhasil' atau 'lunas'.
                    // Contoh: $transaction->update(['status' => 'paid']);
                    Log::info("Transaction {$orderId} successfully captured.");
                }
                break;
            case 'settlement':
                // Transaksi berhasil dan dana telah masuk ke akun Midtrans Anda
                // TODO: Update status transaksi di database Anda menjadi 'berhasil' atau 'lunas'.
                // Contoh: $transaction->update(['status' => 'paid']);
                Log::info("Transaction {$orderId} has been settled.");
                break;
            case 'pending':
                // Transaksi sedang menunggu pembayaran
                // TODO: Update status transaksi di database Anda menjadi 'menunggu pembayaran'.
                // Contoh: $transaction->update(['status' => 'pending']);
                Log::info("Transaction {$orderId} is pending.");
                break;
            case 'deny':
                // Transaksi ditolak
                // TODO: Update status transaksi di database Anda menjadi 'gagal' atau 'ditolak'.
                // Contoh: $transaction->update(['status' => 'failed']);
                Log::info("Transaction {$orderId} was denied.");
                break;
            case 'expire':
                // Transaksi kadaluarsa karena tidak dibayar
                // TODO: Update status transaksi di database Anda menjadi 'kadaluarsa'.
                // Contoh: $transaction->update(['status' => 'expired']);
                Log::info("Transaction {$orderId} has expired.");
                break;
            case 'cancel':
                // Transaksi dibatalkan
                // TODO: Update status transaksi di database Anda menjadi 'dibatalkan'.
                // Contoh: $transaction->update(['status' => 'canceled']);
                Log::info("Transaction {$orderId} was canceled.");
                break;
        }

        // 6. Kirim response OK ke Midtrans
        // Penting agar Midtrans tahu notifikasi sudah diterima
        return response()->json(['message' => 'Webhook successfully processed'], 200);
    }

    /**
     * Menampilkan halaman pembayaran.
     */
    public function showPaymentPage()
    {
        // Cukup kembalikan view-nya saja
        return view('payment');
    }

    /**
     * Membuat transaksi dan mendapatkan Snap Token.
     */
    public function createTransaction(Request $request)
    {
        // 1. Set konfigurasi Midtrans
        \Midtrans\Config::$serverKey = config('midtrans.server_key');
        \Midtrans\Config::$isProduction = config('midtrans.is_production');
        \Midtrans\Config::$isSanitized = true;
        \Midtrans\Config::$is3ds = true;

        // 2. Buat parameter untuk transaksi
        $params = [
            'transaction_details' => [
                'order_id' => 'ORDER-' . uniqid(), // Buat ID order yang unik
                'gross_amount' => 10000, // Jumlah pembayaran
            ],
            'customer_details' => [
                'first_name' => 'Budi',
                'last_name' => 'Pratama',
                'email' => 'budi.pratama@example.com',
                'phone' => '081234567890',
            ],
        ];

        try {
            // 3. Dapatkan Snap Token
            $snapToken = \Midtrans\Snap::getSnapToken($params);

            // 4. Kirim token ke view
            return response()->json(['snap_token' => $snapToken]);
        } catch (\Exception $e) {
            // Tangani jika ada error
            Log::error('Midtrans Snap Error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
