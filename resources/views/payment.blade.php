<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Midtrans Snap</title>
    <script type="text/javascript"
      src="https://app.sandbox.midtrans.com/snap/snap.js"
      data-client-key="{{ config('midtrans.client_key') }}"></script>
</head>
<body>

    <h1>Tes Pembayaran</h1>
    <p>Klik tombol di bawah untuk memulai pembayaran sebesar Rp 10.000.</p>

    <button id="pay-button">Bayar Sekarang</button>

    <script type="text/javascript">
      // 3. Ambil tombol
      var payButton = document.getElementById('pay-button');

      // 4. Tambahkan event listener untuk tombol
      payButton.addEventListener('click', function () {
        // Tampilkan status loading
        payButton.disabled = true;
        payButton.textContent = 'Memproses...';

        // 5. Buat request ke backend untuk mendapatkan Snap Token
        fetch('/create-transaction', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}' // Penting untuk keamanan Laravel
            }
        }).then(response => response.json())
          .then(data => {
            if (data.error) {
                alert('Gagal membuat transaksi: ' + data.error);
                payButton.disabled = false;
                payButton.textContent = 'Bayar Sekarang';
                return;
            }

            // 6. Jika token berhasil didapat, buka popup pembayaran Snap
            window.snap.pay(data.snap_token, {
              onSuccess: function(result){
                /* Anda bisa menangani hasil sukses di sini */
                alert("Pembayaran berhasil!"); console.log(result);
                payButton.disabled = false;
                payButton.textContent = 'Bayar Sekarang';
              },
              onPending: function(result){
                /* Anda bisa menangani hasil pending di sini */
                alert("Menunggu pembayaran!"); console.log(result);
                payButton.disabled = false;
                payButton.textContent = 'Bayar Sekarang';
              },
              onError: function(result){
                /* Anda bisa menangani hasil error di sini */
                alert("Pembayaran gagal!"); console.log(result);
                payButton.disabled = false;
                payButton.textContent = 'Bayar Sekarang';
              },
              onClose: function(){
                /* Jika pelanggan menutup popup tanpa menyelesaikan pembayaran */
                alert('Anda menutup popup pembayaran.');
                payButton.disabled = false;
                payButton.textContent = 'Bayar Sekarang';
              }
            });
        }).catch(error => {
            alert('Terjadi kesalahan. Cek console untuk detail.');
            console.error(error);
            payButton.disabled = false;
            payButton.textContent = 'Bayar Sekarang';
        });
      });
    </script>
</body>
</html>