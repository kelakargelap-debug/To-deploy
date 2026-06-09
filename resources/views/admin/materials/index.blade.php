@extends('app')

@section('content')
<div class="max-w-5xl mx-auto pb-12">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
        <div>
            <h2 class="text-headline-md font-headline-md text-[var(--text-primary)]">Manajemen Materi</h2>
            <p class="text-body-md text-[var(--text-secondary)] mt-1">Kelola dan publikasikan materi e-book ujian.</p>
        </div>
        <div class="flex items-center gap-3 ml-auto sm:ml-0">
            <button onclick="navigateTo('/admin/materials/create')" class="px-6 py-2.5 rounded-lg bg-[var(--primary)] text-white font-label-md hover:opacity-90 flex items-center gap-2 transition-all shadow-sm">
                <span class="material-symbols-outlined text-[20px]">add</span>
                <span>Tambah Materi</span>
            </button>
        </div>
    </div>

    <!-- Data Table Card -->
    <div class="bg-[var(--bg-surface)] border border-[var(--border-default)] rounded-xl shadow-[0_4px_15px_rgba(0,74,198,0.04)] overflow-hidden">
        <!-- Table Toolbar -->
        <div class="px-6 py-4 border-b border-[var(--border-default)] bg-[var(--bg-default)] flex justify-between items-center">
            <h3 class="font-label-md font-bold text-[var(--text-primary)]">Daftar Materi</h3>
            <div class="flex items-center gap-2">
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[var(--text-muted)] text-[20px]">search</span>
                    <input type="text" placeholder="Cari materi..." class="pl-10 pr-4 py-2 bg-[var(--bg-surface)] border border-[var(--border-default)] rounded-lg text-label-md focus:ring-2 focus:ring-[var(--primary)] outline-none w-64 transition-all">
                </div>
                <button class="p-2 rounded-lg border border-[var(--border-default)] text-[var(--text-secondary)] hover:bg-[var(--bg-surface-hover)] transition-colors flex items-center justify-center">
                    <span class="material-symbols-outlined text-[20px]">filter_list</span>
                </button>
            </div>
        </div>

        <!-- Table Content -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-[var(--border-default)] bg-[var(--bg-surface-hover)]">
                        <th class="px-6 py-4 font-label-sm font-bold text-[var(--text-secondary)] uppercase tracking-wider">Judul Materi</th>
                        <th class="px-6 py-4 font-label-sm font-bold text-[var(--text-secondary)] uppercase tracking-wider">Kategori</th>
                        <th class="px-6 py-4 font-label-sm font-bold text-[var(--text-secondary)] uppercase tracking-wider">Tier</th>
                        <th class="px-6 py-4 font-label-sm font-bold text-[var(--text-secondary)] uppercase tracking-wider text-center">Urutan</th>
                        <th class="px-6 py-4 font-label-sm font-bold text-[var(--text-secondary)] uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 font-label-sm font-bold text-[var(--text-secondary)] uppercase tracking-wider text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody id="materials-table-body" class="divide-y divide-[var(--border-default)] bg-[var(--bg-surface)]">
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="animate-pulse flex flex-col items-center">
                                <div class="w-8 h-8 rounded-full border-4 border-[var(--primary)] border-t-transparent animate-spin mb-4"></div>
                                <span class="text-[var(--text-muted)] font-label-md">Memuat data...</span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Table Footer / Pagination -->
        <div class="px-6 py-4 border-t border-[var(--border-default)] bg-[var(--bg-default)] flex justify-between items-center text-label-md text-[var(--text-secondary)]">
            <span>Menampilkan data materi</span>
            <div class="flex gap-1">
                <button class="w-8 h-8 flex items-center justify-center rounded border border-[var(--border-default)] hover:bg-[var(--bg-surface-hover)] disabled:opacity-50"><span class="material-symbols-outlined text-[18px]">chevron_left</span></button>
                <button class="w-8 h-8 flex items-center justify-center rounded border border-[var(--border-default)] hover:bg-[var(--bg-surface-hover)] disabled:opacity-50"><span class="material-symbols-outlined text-[18px]">chevron_right</span></button>
            </div>
        </div>
    </div>
</div>

{{-- Delete Confirmation Modal --}}
<div id="delete-material-modal" class="fixed inset-0 z-50 hidden bg-[#141d21]/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-[var(--bg-surface)] w-full max-w-md rounded-2xl shadow-xl overflow-hidden animate-fade-in-up border border-[var(--border-default)]">
        <div class="p-6">
            <div class="w-12 h-12 rounded-full bg-[var(--danger)]/10 text-[var(--danger)] flex items-center justify-center mb-4">
                <span class="material-symbols-outlined text-[24px]">delete_forever</span>
            </div>
            <h3 class="text-headline-sm font-headline-sm text-[var(--text-primary)] mb-2">Hapus Materi?</h3>
            <p class="text-body-md text-[var(--text-secondary)] mb-6">Apakah Anda yakin ingin menghapus materi <span id="delete-material-title" class="font-bold text-[var(--text-primary)]"></span>? Semua data progress belajar user akan ikut terhapus secara permanen.</p>
            <div class="flex gap-3 justify-end">
                <button type="button" onclick="closeDeleteModal()" class="px-5 py-2.5 rounded-lg border border-[var(--border-default)] text-[var(--text-secondary)] font-label-md hover:bg-[var(--bg-surface-hover)] transition-all">Batal</button>
                <button type="button" onclick="submitDeleteMaterial()" class="px-5 py-2.5 rounded-lg bg-[var(--danger)] text-white font-label-md hover:opacity-90 transition-all flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">delete</span>
                    Hapus
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    let materials = [];
    let categories = [];
    let deleteMaterialId = null;

    function escHtml(str) {
        const div = document.createElement('div');
        div.textContent = str || '';
        return div.innerHTML;
    }

    function getCategoryName(catId) {
        const cat = categories.find(c => c.id === catId);
        return cat ? cat.name : '-';
    }

    function tierBadge(tier) {
        if(tier === 'PREMIUM') {
            return '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-gradient-to-r from-[var(--warning)] to-amber-600 text-white text-[10px] font-bold tracking-wider uppercase"><span class="material-symbols-outlined text-[12px]">workspace_premium</span> PREMIUM</span>';
        }
        return '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full border border-[var(--border-default)] text-[var(--text-secondary)] text-[10px] font-bold tracking-wider uppercase">FREE</span>';
    }

    function publishedBadge(published) {
        return published
            ? '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-[var(--success-subtle)] text-[var(--success)] border border-[var(--success)] text-[10px] font-bold tracking-wider uppercase">PUBLISHED</span>'
            : '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-[var(--bg-surface-hover)] text-[var(--text-muted)] border border-[var(--border-default)] text-[10px] font-bold tracking-wider uppercase">DRAFT</span>';
    }

    async function loadData() {
        const tbody = document.getElementById('materials-table-body');
        
        try {
            categories = await apiFetch('/admin/categories');
            // Use public endpoint, admins might see all (though normally there's an admin endpoint)
            const data = await apiFetch('/materials');
            materials = Array.isArray(data) ? data : (data.data || []);

            // Sort by ID desc or order
            materials.sort((a,b) => b.id - a.id);

            if (materials.length) {
                tbody.innerHTML = materials.map(m => `
                    <tr class="hover:bg-[var(--bg-surface-hover)] transition-colors group">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded bg-[var(--primary-subtle)] text-[var(--primary)] flex items-center justify-center font-bold text-lg">${escHtml(m.title.charAt(0))}</div>
                                <div>
                                    <div class="font-label-md font-bold text-[var(--text-primary)] group-hover:text-[var(--primary)] transition-colors">${escHtml(m.title)}</div>
                                    <div class="text-[12px] text-[var(--text-muted)] font-mono mt-0.5">/${escHtml(m.slug)}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-body-sm text-[var(--text-secondary)]">${escHtml(getCategoryName(m.category_id))}</span>
                        </td>
                        <td class="px-6 py-4">
                            ${tierBadge(m.required_tier)}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-[var(--bg-surface-hover)] text-[var(--text-secondary)] font-bold font-mono text-sm border border-[var(--border-default)]">${m.order || 0}</span>
                        </td>
                        <td class="px-6 py-4">
                            ${publishedBadge(m.is_published)}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button onclick="navigateTo('/admin/materials/${m.id}/edit')" class="w-8 h-8 rounded-full flex items-center justify-center text-[var(--primary)] hover:bg-[var(--primary-subtle)] transition-colors" title="Edit Materi">
                                    <span class="material-symbols-outlined text-[18px]">edit</span>
                                </button>
                                <button onclick="window.openDeleteMaterialModal(${m.id}, '${escHtml(m.title).replace(/'/g, "\\'")}')" class="w-8 h-8 rounded-full flex items-center justify-center text-[var(--danger)] hover:bg-[var(--danger)] hover:bg-opacity-10 transition-colors" title="Hapus Materi">
                                    <span class="material-symbols-outlined text-[18px]">delete</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                `).join('');
            } else {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center text-[var(--text-muted)]">
                                <span class="material-symbols-outlined text-5xl mb-3 opacity-50">book</span>
                                <p class="font-label-md">Belum ada materi yang ditambahkan.</p>
                            </div>
                        </td>
                    </tr>
                `;
            }
        } catch (err) {
            tbody.innerHTML = `<tr><td colspan="6" class="px-6 py-4 text-center text-[var(--danger)]">Error: ${err.message}</td></tr>`;
        }
    }

    window.openDeleteMaterialModal = function(id, title) {
        deleteMaterialId = id;
        document.getElementById('delete-material-title').textContent = title;
        document.getElementById('delete-material-modal').classList.remove('hidden');
    };

    window.closeDeleteModal = function() {
        document.getElementById('delete-material-modal').classList.add('hidden');
        deleteMaterialId = null;
    };

    window.submitDeleteMaterial = async function() {
        if (!deleteMaterialId) return;
        try {
            await apiFetch('/admin/materials/' + deleteMaterialId, { method: 'DELETE' });
            closeDeleteModal();
            loadData();
        } catch (err) {
            alert('Failed to delete material: ' + err.message);
        }
    };

    loadData();
})();
</script>
@endpush