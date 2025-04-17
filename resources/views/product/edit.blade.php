@extends('main')
@section('title', '| Edit Product')

@section('content')

<div class="row">
    <form action="{{ route('product.update', ['id' => $item->id]) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <!-- Nama Produk -->
        <div class="mb-3">
            <label for="name" class="form-label">Nama Produk</label>
            <input type="text" class="form-control border-secondary" id="name" name="name"
                value="{{ $item->name }}" required>
        </div>

        <!-- Harga -->
        <div class="mb-3">
            <label for="price" class="form-label">Harga</label>
            <div class="input-group">
                <span class="input-group-text" id="currency" style="display: none;">Rp</span>
                <input type="text" class="form-control border-secondary" id="price_display" autocomplete="off" required>
                <input type="hidden" name="price" id="price_real" value="{{ $item->price }}">
            </div>
        </div>

        <!-- Stok -->
        <div class="mb-3">
            <label for="stock" class="form-label">Stock</label>
            <input type="number" class="form-control border-secondary" id="stock" name="stock"
                value="{{ $item->stock }}" readonly>
        </div>

        <!-- Gambar Produk -->
        <div class="mb-3">
            <label for="image" class="form-label">Gambar Produk</label>
            <input type="file" class="form-control border-secondary" id="image" name="image" accept="image/*">
        </div>

        <!-- Tombol Submit -->
        <button type="submit" class="btn btn-primary">Simpan</button>
    </form>
</div>

<script>
    // Fungsi format angka ke Rupiah
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

        rupiah = split[1] !== undefined ? rupiah + ',' + split[1] : rupiah;
        return rupiah;
    }

    const priceDisplay = document.getElementById('price_display');
    const priceReal = document.getElementById('price_real');
    const currency = document.getElementById('currency');

    priceDisplay.addEventListener('input', function (e) {
        let cleanValue = e.target.value.replace(/\D/g, '');

        // Tampilkan Rp jika ada nilai
        currency.style.display = cleanValue ? 'inline' : 'none';

        // Set format tampilannya
        e.target.value = formatRupiah(cleanValue);

        // Set nilai angka mentah ke input hidden
        priceReal.value = cleanValue;
    });

    // Saat halaman dibuka, isi field harga display-nya
    window.addEventListener('DOMContentLoaded', () => {
        const oldValue = priceReal.value;
        if (oldValue) {
            priceDisplay.value = formatRupiah(oldValue);
            currency.style.display = 'inline';
        }
    });
</script>

@endsection
