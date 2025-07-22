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
      // Ambil tombol
      var payButton = document.getElementById('pay-button');
  
      // Tambahkan event listener untuk tombol
      payButton.addEventListener('click', function () {
      // Tampilkan status loading
      payButton.disabled = true;
      payButton.textContent = 'Memproses...';
  
      // Buat request ke backend untuk mendapatkan Snap Token
      fetch('/create-transaction', {
          method: 'POST',
          headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              // --- BARIS PENTING YANG HARUS ADA ---
              'X-CSRF-TOKEN': '{{ csrf_token() }}' 
          }
      }).then(response => {
          if (!response.ok) {
              // Jika respons bukan 2xx, lempar error untuk ditangkap di .catch
              throw new Error('Network response was not ok');
          }
          return response.json();
      })
      .then(data => {
          if (data.error) {
              alert('Gagal membuat transaksi: ' + data.error);
              // Kembalikan tombol ke keadaan semula
              payButton.disabled = false;
              payButton.textContent = 'Bayar Sekarang';
              return;
          }
  
          // Jika token berhasil didapat, buka popup pembayaran Snap
          window.snap.pay(data.snap_token, {
            onSuccess: function(result){
              alert("Pembayaran berhasil!"); 
              window.location.href = '/transactions'; // Arahkan ke halaman transaksi
            },
            onPending: function(result){
              alert("Menunggu pembayaran!"); 
              window.location.href = '/transactions'; // Arahkan ke halaman transaksi
            },
            onError: function(result){
              alert("Pembayaran gagal!");
              payButton.disabled = false;
              payButton.textContent = 'Bayar Sekarang';
            },
            onClose: function(){
              alert('Anda menutup popup pembayaran.');
              payButton.disabled = false;
              payButton.textContent = 'Bayar Sekarang';
            }
          });
      }).catch(error => {
          alert('Terjadi kesalahan. Halaman akan dimuat ulang. Cek console untuk detail.');
          console.error('Fetch Error:', error);
          // Muat ulang halaman untuk mendapatkan CSRF token baru jika error terjadi
          location.reload(); 
      });
      });
  </script>
</body>
</html>