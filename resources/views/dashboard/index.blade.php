@extends('main')
@section('title', '| Dashboard')

@section('content')

<div class="row">
    <div class="col-lg-12 p-3">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Selamat Datang di Dashboard</h4>

                <div class="card w-100">
                    <ul class="list-group list-group-flush d-flex flex-column" style="min-height: 200px;">
                        <li class="list-group-item bg-light d-flex justify-content-center align-items-center">
                            Total Penjualan Hari Ini
                        </li>
                        <li class="list-group-item flex-grow-1 d-flex flex-column justify-content-center align-items-center" style="min-height: 100px;">
                            <b style="font-size: 2rem;">15</b>
                            <span>Data terjual hari ini:</span>
                        </li>
                        <li class="list-group-item bg-light d-flex justify-content-center align-items-center">
                            Terakhir diperbarui: {{ now()->format('d M Y H:i') }}
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Grafik Penjualan</h4>
                <canvas id="salesChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Persentase Penjualan Produk</h4>
                <div class="chart-container">
                    <canvas id="salesPieChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Pie Chart - Statis
        const pieCtx = document.getElementById('salesPieChart');
        if(pieCtx) {
            new Chart(pieCtx, {
                type: 'pie',
                data: {
                    labels: ['Kopi', 'Teh', 'Susu', 'Coklat', 'Air Mineral'],
                    datasets: [{
                        data: [35, 25, 15, 15, 10],
                        backgroundColor: [
                            '#FF6384',
                            '#36A2EB',
                            '#FFCE56',
                            '#4BC0C0',
                            '#9966FF'
                        ],
                        borderColor: '#fff',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20
                            }
                        },
                        tooltip: {
                            enabled: true,
                            callbacks: {
                                label: function(context) {
                                    return `${context.label}: ${context.raw}%`;
                                }
                            }
                        }
                    }
                }
            });
        }

        // Line Chart - Statis
        const lineCtx = document.getElementById('salesChart');
        if(lineCtx) {
            new Chart(lineCtx, {
                type: 'line',
                data: {
                    labels: ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'],
                    datasets: [{
                        label: 'Jumlah Penjualan',
                        data: [10, 12, 8, 14, 9, 11, 15],
                        backgroundColor: 'rgba(54, 162, 235, 0.6)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 5
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'top'
                        }
                    }
                }
            });
        }
    });
</script>

@endsection
