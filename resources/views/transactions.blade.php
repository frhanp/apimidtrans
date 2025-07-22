<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Daftar Transaksi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Daftar Transaksi</h1>
        <a href="/pay" class="btn btn-primary mb-3">Buat Transaksi Baru</a>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Jumlah</th>
                    <th>Pelanggan</th>
                    <th>Tipe Pembayaran</th>
                    <th>Status</th>
                    <th>Waktu</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($transactions as $transaction)
                    <tr>
                        <td>{{ $transaction->order_id }}</td>
                        <td>Rp {{ number_format($transaction->gross_amount, 0, ',', '.') }}</td>
                        <td>{{ $transaction->customer_details['first_name'] }}</td>
                        <td>{{ $transaction->payment_type ?? '-' }}</td>
                        <td>
                            @php
                                $statusClass = 'text-bg-secondary';
                                if ($transaction->status == 'paid') $statusClass = 'text-bg-success';
                                if ($transaction->status == 'pending') $statusClass = 'text-bg-warning';
                                if (in_array($transaction->status, ['expire', 'cancel', 'deny'])) $statusClass = 'text-bg-danger';
                            @endphp
                            <span class="badge {{ $statusClass }}">{{ ucfirst($transaction->status) }}</span>
                        </td>
                        <td>{{ $transaction->created_at->format('d M Y, H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">Belum ada transaksi.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>