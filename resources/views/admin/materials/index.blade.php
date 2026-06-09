@extends('app')

@section('content')
<div class="max-w-7xl mx-auto">
    <x-page-header title="Materials Management" subtitle="Kelola materi pembelajaran">
        <x-slot:action>
            <button onclick="navigateTo('/admin/materials/create')" class="btn-primary">
                + Tambah Materi
            </button>
        </x-slot:action>
    </x-page-header>

    {{-- Materials Table --}}
    <x-data-table>
        <table class="w-full">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Tier</th>
                    <th>Published</th>
                    <th>Order</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="materials-table-body">
                <tr><td colspan="6" class="text-center">Loading...</td></tr>
            </tbody>
        </table>
    </x-data-table>
</div>

{{-- Delete Confirmation Modal --}}
<x-modal id="delete-material-modal" title="Delete Material" size="sm">
    <p class="text-sm text-[var(--text-secondary)] mb-4">Are you sure you want to delete: <span id="delete-material-title" class="font-bold text-[var(--text-primary)]"></span>? This will also delete all learning progress records.</p>
    <div class="flex gap-3 justify-end">
        <button data-modal-close="delete-material-modal" class="btn-secondary">Cancel</button>
        <button onclick="submitDeleteMaterial()" class="btn-danger">Delete</button>
    </div>
</x-modal>
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
        const cls = tier === 'PREMIUM' ? 'badge-premium' : 'badge-free';
        return '<span class="badge ' + cls + '">' + tier + '</span>';
    }

    function publishedBadge(published) {
        return published
            ? '<span class="badge badge-success">Published</span>'
            : '<span class="badge badge-warning">Draft</span>';
    }

    async function loadData() {
        const tbody = document.getElementById('materials-table-body');
        tbody.innerHTML = '<tr><td class="px-5 py-4 text-gray-500" colspan="6">Loading...</td></tr>';

        try {
            categories = await apiFetch('/admin/categories');
            // There's no admin materials list endpoint; use the public endpoint
            const data = await apiFetch('/materials');
            materials = Array.isArray(data) ? data : (data.data || []);

            if (materials.length) {
                tbody.innerHTML = materials.map(m => `
                    <tr>
                        <td><span class="font-medium text-[var(--text-primary)]">${escHtml(m.title)}</span></td>
                        <td>${escHtml(getCategoryName(m.category_id))}</td>
                        <td>${tierBadge(m.required_tier)}</td>
                        <td>${publishedBadge(m.is_published)}</td>
                        <td><span class="font-mono font-bold">${m.order}</span></td>
                        <td>
                            <div class="flex gap-2">
                                <button onclick="navigateTo('/admin/materials/${m.id}/edit')" class="btn-ghost btn-sm text-[var(--info)]">Edit</button>
                                <button onclick="openDeleteMaterialModal(${m.id}, '${escHtml(m.title)}')" class="btn-ghost btn-sm text-[var(--danger)]">Delete</button>
                            </div>
                        </td>
                    </tr>
                `).join('');
            } else {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center text-[var(--text-muted)]">No materials found</td></tr>';
            }
        } catch (err) {
            tbody.innerHTML = `<tr><td colspan="6" class="text-center text-[var(--danger)]">Error: ${escHtml(err.message)}</td></tr>`;
        }
    }

    // Delete Modal
    window.openDeleteMaterialModal = function(matId, matTitle) {
        deleteMaterialId = matId;
        document.getElementById('delete-material-title').textContent = matTitle;
        openModal('delete-material-modal');
    };

    window.submitDeleteMaterial = async function() {
        try {
            await apiFetch('/admin/materials/' + deleteMaterialId, { method: 'DELETE' });
            showToast('Materi berhasil dihapus!', 'success');
            closeModal('delete-material-modal');
            loadData();
        } catch (err) {
            showToast(err.message, 'error');
        }
    };

    loadData();
})();
</script>
@endpush