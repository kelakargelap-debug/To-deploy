@extends('app')

@section('content')
<div class="p-6 max-w-6xl mx-auto">
    <x-page-header title="Nilai Saya" subtitle="Riwayat percobaan tryout kamu" />

    <!-- Loading State -->
    <div id="attempts-loading" class="text-center py-8">
        <p class="text-gray-500 dark:text-gray-400">Memuat data...</p>
    </div>

    <!-- Attempts Table -->
    <div id="attempts-table" class="hidden">
        <div class="card">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Tryout</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Tanggal</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Status</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Nilai</th>
                        <th class="px-4 py-3 text-right text-sm font-semibold text-gray-700 dark:text-gray-300">Aksi</th>
                    </tr>
                </thead>
                <tbody id="attempts-list" class="divide-y divide-gray-200 dark:divide-gray-700"></tbody>
            </table>
        </div>
    </div>

    <!-- Empty State -->
    <div id="attempts-empty" class="hidden">
        <div class="card">
            <x-empty-state icon="📋" title="Belum Ada Percobaan" description="Kamu belum mengerjakan tryout. Mulai tryout pertama kamu!" />
        </div>
    </div>
</div>

<script>
(function () {
    apiFetch('/my-attempts').then(function (data) {
        var attempts = data.data || data || [];
        document.getElementById('attempts-loading').classList.add('hidden');

        if (!Array.isArray(attempts) || attempts.length === 0) {
            document.getElementById('attempts-empty').classList.remove('hidden');
            return;
        }

        document.getElementById('attempts-table').classList.remove('hidden');
        var list = document.getElementById('attempts-list');

        list.innerHTML = attempts.map(function (a) {
            var statusBadge = a.status === 'COMPLETED' || a.status === 'SUBMITTED'
                ? '<x-badge type="success" text="Selesai" />'
                : '<span class="badge badge-danger">Tidak Selesai</span>';
            // Since we can't use Blade x-badge in JS, use plain badge classes
            var statusClass = a.status === 'COMPLETED' || a.status === 'SUBMITTED' ? 'badge badge-success' : 'badge badge-danger';
            var statusText = a.status === 'COMPLETED' || a.status === 'SUBMITTED' ? 'Selesai' : 'Tidak Selesai';
            var scoreDisplay = a.score !== null && a.score !== undefined ? a.score + '%' : '-';
            var dateDisplay = a.completed_at || a.submitted_at || a.created_at || '-';
            var viewLink = '/tryouts/' + (a.tryoutSlug || a.tryout_slug || '') + '/result/' + a.id;

            return '<tr>' +
                '<td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100">' + (a.tryoutTitle || a.tryout_title || 'Tryout') + '</td>' +
                '<td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">' + dateDisplay + '</td>' +
                '<td class="px-4 py-3"><span class="' + statusClass + '">' + statusText + '</span></td>' +
                '<td class="px-4 py-3 font-semibold text-gray-900 dark:text-gray-100">' + scoreDisplay + '</td>' +
                '<td class="px-4 py-3 text-right"><a href="' + viewLink + '" class="text-blue-600 dark:text-blue-400 hover:underline text-sm">Lihat</a></td>' +
                '</tr>';
        }).join('');
    }).catch(function () {
        document.getElementById('attempts-loading').classList.add('hidden');
        document.getElementById('attempts-empty').classList.remove('hidden');
    });
})();
</script>
@endsection