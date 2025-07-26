<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Transaction;

class MidtransController extends Controller
{
    /**
     * Membuat transaksi dan mendapatkan Snap Token.
     */
    public function createTransaction(Request $request)
    {
        // --- BAGIAN KONFIGURASI LENGKAP ---
        \Midtrans\Config::$serverKey = config('midtrans.server_key');
        \Midtrans\Config::$isProduction = config('midtrans.is_production');
        \Midtrans\Config::$isSanitized = config('midtrans.is_sanitized');
        \Midtrans\Config::$is3ds = config('midtrans.is_3ds');
        // ------------------------------------

        // Buat data transaksi
        $orderId = 'ORDER-' . uniqid();
        // Samakan jumlah dengan yang ada di view
        $grossAmount = 10000;
        $customerDetails = [
            'first_name' => 'Budi',
            'last_name' => 'Pratama',
            'email' => 'budi.pratama@example.com',
            'phone' => '081234567890',
        ];

        // Buat parameter untuk Midtrans
        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $grossAmount,
            ],
            'customer_details' => $customerDetails,
        ];

        // Dapatkan Snap Token
        $snapToken = \Midtrans\Snap::getSnapToken($params);

        // Simpan transaksi ke database
        Transaction::create([
            'order_id' => $orderId,
            'gross_amount' => $grossAmount,
            'status' => 'pending',
            'snap_token' => $snapToken,
            'customer_details' => $customerDetails,
        ]);

        return response()->json(['snap_token' => $snapToken]);
    }

    /**
     * Menangani notifikasi webhook dari Midtrans.
     */
    public function handleWebhook(Request $request)
    {
        // ==================================================
        // 1. LOGIKA VALIDASI SIGNATURE KEY
        // ==================================================
        $notificationPayload = $request->all();
        $serverKey = config('midtrans.server_key');

        $orderId = $notificationPayload['order_id'] ?? null;
        $statusCode = $notificationPayload['status_code'] ?? null;
        $grossAmount = $notificationPayload['gross_amount'] ?? null;

        $stringToHash = $orderId . $statusCode . $grossAmount . $serverKey;
        $calculatedSignature = hash('sha512', $stringToHash);

        if (empty($notificationPayload['signature_key']) || $calculatedSignature !== $notificationPayload['signature_key']) {
            Log::error('Webhook Error: Invalid signature.');
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        // ==================================================
        // 2. PROSES UPDATE SETELAH SIGNATURE VALID
        // ==================================================
        $transaction = \App\Models\Transaction::where('order_id', $orderId)->first();

        if (!$transaction) {
            // --- PERUBAHAN DI SINI ---
            // Jika ini notifikasi tes dari dashboard, abaikan & kirim OK.
            if (str_starts_with($orderId, 'payment_notif_test_')) {
                return response()->json(['message' => 'Test notification received and ignored.'], 200);
            }
            // --- AKHIR PERUBAHAN ---

            // Jika transaksi asli yang tidak ditemukan, baru catat sebagai error.
            Log::error("Webhook Error: Transaksi dengan order_id {$orderId} tidak ditemukan.");
            return response()->json(['message' => 'Transaction not found'], 404);
        }

        // Update status transaksi
        $transactionStatus = $notificationPayload['transaction_status'];
        $fraudStatus = $notificationPayload['fraud_status'] ?? null;

        if ($transactionStatus == 'capture') {
            if ($fraudStatus == 'accept') {
                $transaction->update(['status' => 'paid']);
            }
        } else if ($transactionStatus == 'settlement') {
            $transaction->update([
                'status' => 'paid',
                'payment_type' => $notificationPayload['payment_type']
            ]);
        } else if (in_array($transactionStatus, ['expire', 'deny', 'cancel'])) {
            $transaction->update(['status' => $transactionStatus]);
        }

        return response()->json(['message' => 'Webhook successfully processed'], 200);
    }
    // -- FUNGSI BARU UNTUK MENAMPILKAN DAFTAR TRANSAKSI --
    public function showTransactions()
    {
        $transactions = Transaction::latest()->get();
        return view('transactions', compact('transactions'));
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
}
