@extends('main')
@section('title', '| Product Create')

@section('content')

<div class="row">
    <form action="{{ route('product.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @error('image')
        <div class="alert alert-danger">{{ $message }}</div>
        @enderror

        <!-- Nama Produk -->
        <div class="mb-3">
            <label for="name" class="form-label">Nama Produk</label>
            <input type="text" class="form-control border-secondary" id="name" name="name" required value="{{ old('name') }}">
        </div>

        <!-- Harga -->
<div class="mb-3">
  <label for="price" class="form-label">Harga</label>
  <div class="input-group">
      <span class="input-group-text" id="currency" style="display: none;">Rp</span>
      <input type="text" class="form-control border-secondary" id="price_display" autocomplete="off" required>
      <input type="hidden" name="price" id="price_real" value="{{ old('price') }}">
  </div>
</div>


        <!-- Stok -->
        <div class="mb-3">
            <label for="stock" class="form-label">Stock</label>
            <input type="number" class="form-control border-secondary" id="stock" name="stock" required max="9999999999"
            oninput="this.value = this.value.slice(0, 10)" value="{{ old('stock') }}">
        </div>

        <!-- Gambar Produk -->
        <div class="mb-3">
            <label for="image" class="form-label">Gambar Produk</label>
            <input type="file" class="form-control border-secondary" id="image" name="image" accept="image/*" required>
        </div>

        <!-- Tombol Submit -->
        <button type="submit" class="btn btn-primary">Simpan</button>
    </form>
</div>

<script>
  function formatRupiah(angka) {
      let number_string = angka.replace(/[^,\d]/g, '').toString(),
          split = number_string.split(','),
          sisa = split[0].length % 3,
          rupiah = split[0].substr(0, sisa),
          ribuan = split[0].substr(sisa).match(/\d{3}/gi);

      if (ribuan) {
          let separator = sisa ? '.' : '';
          rupiah += separator + ribuan.join('.');
      }

      rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
      return rupiah;
  }

  const priceDisplay = document.getElementById('price_display');
  const priceReal = document.getElementById('price_real');
  const currency = document.getElementById('currency');

  priceDisplay.addEventListener('input', function (e) {
      let cleanValue = e.target.value.replace(/\D/g, '');

      // Tampilkan 'Rp' kalau ada nilai
      currency.style.display = cleanValue ? 'inline' : 'none';

      // Format tampilan
      priceDisplay.value = formatRupiah(cleanValue);

      // Isi hidden input dengan angka murni
      priceReal.value = cleanValue;
  });

  // Restore old value saat reload (jika pakai old)
  window.addEventListener('DOMContentLoaded', () => {
      const oldValue = document.getElementById('price_real').value;
      if (oldValue) {
          priceDisplay.value = formatRupiah(oldValue);
          currency.style.display = 'inline';
      }
  });
</script>


@endsection
