@extends('app')

@section('content')
<div class="p-6 max-w-7xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Materials Management</h1>
        <button onclick="navigateTo('/admin/materials/create')" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition-colors">
            + Tambah Materi
        </button>
    </div>

    {{-- Materials Table --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                        <th class="px-5 py-3 text-left font-medium">Title</th>
                        <th class="px-5 py-3 text-left font-medium">Category</th>
                        <th class="px-5 py-3 text-left font-medium">Tier</th>
                        <th class="px-5 py-3 text-left font-medium">Published</th>
                        <th class="px-5 py-3 text-left font-medium">Order</th>
                        <th class="px-5 py-3 text-left font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody id="materials-table-body" class="divide-y divide-gray-200 dark:divide-gray-600">
                    <tr><td class="px-5 py-4 text-gray-500 dark:text-gray-400" colspan="6">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Delete Confirmation Modal --}}
<div id="delete-material-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center">
    <div class="absolute inset-0 bg-black/50" onclick="closeDeleteMaterialModal()"></div>
    <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 w-full max-w-md mx-4 border border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-red-600 dark:text-red-400 mb-4">Delete Material</h3>
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Are you sure you want to delete: <span id="delete-material-title" class="font-medium text-gray-900 dark:text-gray-100"></span>? This will also delete all learning progress records.</p>
        <div class="flex gap-3 justify-end">
            <button onclick="closeDeleteMaterialModal()" class="px-4 py-2 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium hover:bg-gray-300 dark:hover:bg-gray-500 transition-colors">Cancel</button>
            <button onclick="submitDeleteMaterial()" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium transition-colors">Delete</button>
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
        const cls = tier === 'PREMIUM' ? 'bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300'
            : 'bg-gray-100 dark:bg-gray-600 text-gray-600 dark:text-gray-300';
        return '<span class="px-2 py-1 rounded text-xs font-medium ' + cls + '">' + tier + '</span>';
    }

    function publishedBadge(published) {
        return published
            ? '<span class="px-2 py-1 rounded text-xs font-medium bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300">Published</span>'
            : '<span class="px-2 py-1 rounded text-xs font-medium bg-yellow-100 dark:bg-yellow-900 text-yellow-700 dark:text-yellow-300">Draft</span>';
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
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-5 py-4 text-gray-900 dark:text-gray-100 font-medium">${escHtml(m.title)}</td>
                        <td class="px-5 py-4 text-gray-600 dark:text-gray-400">${escHtml(getCategoryName(m.category_id))}</td>
                        <td class="px-5 py-4">${tierBadge(m.required_tier)}</td>
                        <td class="px-5 py-4">${publishedBadge(m.is_published)}</td>
                        <td class="px-5 py-4 text-gray-900 dark:text-gray-100">${m.order}</td>
                        <td class="px-5 py-4">
                            <div class="flex gap-2">
                                <button onclick="navigateTo('/admin/materials/${m.id}/edit')" class="px-2 py-1 text-xs bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 rounded hover:bg-blue-200 dark:hover:bg-blue-800 transition-colors">Edit</button>
                                <button onclick="openDeleteMaterialModal(${m.id}, '${escHtml(m.title)}')" class="px-2 py-1 text-xs bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300 rounded hover:bg-red-200 dark:hover:bg-red-800 transition-colors">Delete</button>
                            </div>
                        </td>
                    </tr>
                `).join('');
            } else {
                tbody.innerHTML = '<tr><td class="px-5 py-4 text-gray-500" colspan="6">No materials found</td></tr>';
            }
        } catch (err) {
            tbody.innerHTML = `<tr><td class="px-5 py-4 text-red-500" colspan="6">Error: ${escHtml(err.message)}</td></tr>`;
        }
    }

    // Delete Modal
    window.openDeleteMaterialModal = function(matId, matTitle) {
        deleteMaterialId = matId;
        document.getElementById('delete-material-title').textContent = matTitle;
        document.getElementById('delete-material-modal').classList.remove('hidden');
    };

    window.closeDeleteMaterialModal = function() {
        document.getElementById('delete-material-modal').classList.add('hidden');
        deleteMaterialId = null;
    };

    window.submitDeleteMaterial = async function() {
        try {
            await apiFetch('/admin/materials/' + deleteMaterialId, { method: 'DELETE' });
            alert('Materi berhasil dihapus!');
            closeDeleteMaterialModal();
            loadData();
        } catch (err) {
            alert('Error: ' + err.message);
        }
    };

    loadData();
})();
</script>
@endpush