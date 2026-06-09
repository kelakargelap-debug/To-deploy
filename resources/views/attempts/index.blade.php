@extends('app')

@section('content')
<div class="max-w-6xl mx-auto">
    <x-page-header title="Nilai Saya" subtitle="Riwayat percobaan tryout kamu" />

    <!-- Loading State -->
    <div id="attempts-loading" class="text-center py-12">
        <svg class="w-8 h-8 text-[var(--text-muted)] animate-spin mx-auto" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <p class="text-[var(--text-secondary)] mt-3">Memuat data...</p>
    </div>

    <!-- Attempts Table -->
    <div id="attempts-table" class="hidden">
        <x-data-table>
            <table class="w-full">
                <thead>
                    <tr>
                        <th>Tryout</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th>Nilai</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody id="attempts-list"></tbody>
            </table>
        </x-data-table>
    </div>

    <!-- Empty State -->
    <div id="attempts-empty" class="hidden">
        <x-empty-state 
            icon='<svg class="w-16 h-16 text-[var(--text-muted)] mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>' 
            title="Belum Ada Percobaan" 
            description="Kamu belum mengerjakan tryout. Mulai tryout pertama kamu!" 
        >
            <x-slot:action>
                <a href="{{ route('tryouts') }}" class="btn-primary mt-4">Lihat Tryout</a>
            </x-slot:action>
        </x-empty-state>
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
                '<td><span class="font-medium text-[var(--text-primary)]">' + (a.tryoutTitle || a.tryout_title || 'Tryout') + '</span></td>' +
                '<td><span class="text-[var(--text-secondary)]">' + dateDisplay + '</span></td>' +
                '<td><span class="' + statusClass + '">' + statusText + '</span></td>' +
                '<td><span class="font-mono font-bold text-[var(--text-primary)]">' + scoreDisplay + '</span></td>' +
                '<td class="text-right"><a href="' + viewLink + '" class="btn-ghost btn-sm text-[var(--accent)]">Lihat</a></td>' +
                '</tr>';
        }).join('');
    }).catch(function () {
        document.getElementById('attempts-loading').classList.add('hidden');
        document.getElementById('attempts-empty').classList.remove('hidden');
    });
})();
</script>
@endsection