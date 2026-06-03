@extends('app')

@section('content')
<div class="p-6 max-w-7xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Categories Management</h1>
        <button onclick="openCreateModal()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition-colors">
            + Tambah Kategori
        </button>
    </div>

    {{-- Categories Table --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                        <th class="px-5 py-3 text-left font-medium">Name</th>
                        <th class="px-5 py-3 text-left font-medium">Slug</th>
                        <th class="px-5 py-3 text-left font-medium">Description</th>
                        <th class="px-5 py-3 text-left font-medium">Order</th>
                        <th class="px-5 py-3 text-left font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody id="categories-table-body" class="divide-y divide-gray-200 dark:divide-gray-600">
                    <tr><td class="px-5 py-4 text-gray-500 dark:text-gray-400" colspan="5">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Create/Edit Category Modal --}}
<div id="category-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center">
    <div class="absolute inset-0 bg-black/50" onclick="closeCategoryModal()"></div>
    <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 w-full max-w-md mx-4 border border-gray-200 dark:border-gray-700">
        <h3 id="modal-title" class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Tambah Kategori</h3>
        <form id="category-form" class="space-y-4">
            <input type="hidden" id="edit-category-id" value="">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name <span class="text-red-500">*</span></label>
                <input type="text" id="input-cat-name" required
                    class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                <textarea id="input-cat-desc" rows="3"
                    class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-indigo-500 resize-y"></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Order</label>
                <input type="number" id="input-cat-order" value="0"
                    class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="flex gap-3 justify-end pt-2">
                <button type="button" onclick="closeCategoryModal()" class="px-4 py-2 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium hover:bg-gray-300 dark:hover:bg-gray-500 transition-colors">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition-colors">Save</button>
            </div>
        </form>
    </div>
</div>

{{-- Delete Confirmation Modal --}}
<div id="delete-cat-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center">
    <div class="absolute inset-0 bg-black/50" onclick="closeDeleteCatModal()"></div>
    <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 w-full max-w-md mx-4 border border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-red-600 dark:text-red-400 mb-4">Delete Category</h3>
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Are you sure you want to delete: <span id="delete-cat-name" class="font-medium text-gray-900 dark:text-gray-100"></span>? This will also delete all associated tryouts and materials.</p>
        <div class="flex gap-3 justify-end">
            <button onclick="closeDeleteCatModal()" class="px-4 py-2 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium hover:bg-gray-300 dark:hover:bg-gray-500 transition-colors">Cancel</button>
            <button onclick="submitDeleteCat()" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium transition-colors">Delete</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    let categories = [];
    let deleteCatId = null;

    function escHtml(str) {
        const div = document.createElement('div');
        div.textContent = str || '';
        return div.innerHTML;
    }

    async function loadCategories() {
        const tbody = document.getElementById('categories-table-body');
        tbody.innerHTML = '<tr><td class="px-5 py-4 text-gray-500" colspan="5">Loading...</td></tr>';

        try {
            categories = await apiFetch('/admin/categories');

            if (categories.length) {
                tbody.innerHTML = categories.map(c => `
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-5 py-4 text-gray-900 dark:text-gray-100 font-medium">${escHtml(c.name)}</td>
                        <td class="px-5 py-4 text-gray-600 dark:text-gray-400">${escHtml(c.slug)}</td>
                        <td class="px-5 py-4 text-gray-600 dark:text-gray-400 max-w-xs truncate">${escHtml(c.description) || '-'}</td>
                        <td class="px-5 py-4 text-gray-900 dark:text-gray-100">${c.order}</td>
                        <td class="px-5 py-4">
                            <div class="flex gap-2">
                                <button onclick="openEditModal(${c.id})" class="px-2 py-1 text-xs bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 rounded hover:bg-blue-200 dark:hover:bg-blue-800 transition-colors">Edit</button>
                                <button onclick="openDeleteCatModal(${c.id}, '${escHtml(c.name)}')" class="px-2 py-1 text-xs bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300 rounded hover:bg-red-200 dark:hover:bg-red-800 transition-colors">Delete</button>
                            </div>
                        </td>
                    </tr>
                `).join('');
            } else {
                tbody.innerHTML = '<tr><td class="px-5 py-4 text-gray-500" colspan="5">No categories found</td></tr>';
            }
        } catch (err) {
            tbody.innerHTML = `<tr><td class="px-5 py-4 text-red-500" colspan="5">Error: ${escHtml(err.message)}</td></tr>`;
        }
    }

    // Create Modal
    window.openCreateModal = function() {
        document.getElementById('modal-title').textContent = 'Tambah Kategori';
        document.getElementById('edit-category-id').value = '';
        document.getElementById('input-cat-name').value = '';
        document.getElementById('input-cat-desc').value = '';
        document.getElementById('input-cat-order').value = '0';
        document.getElementById('category-modal').classList.remove('hidden');
    };

    // Edit Modal
    window.openEditModal = function(catId) {
        const cat = categories.find(c => c.id === catId);
        if (!cat) return;
        document.getElementById('modal-title').textContent = 'Edit Kategori';
        document.getElementById('edit-category-id').value = catId;
        document.getElementById('input-cat-name').value = cat.name;
        document.getElementById('input-cat-desc').value = cat.description || '';
        document.getElementById('input-cat-order').value = cat.order;
        document.getElementById('category-modal').classList.remove('hidden');
    };

    window.closeCategoryModal = function() {
        document.getElementById('category-modal').classList.add('hidden');
    };

    // Form Submit (create or update)
    document.getElementById('category-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const editId = document.getElementById('edit-category-id').value;
        const payload = {
            name: document.getElementById('input-cat-name').value,
            description: document.getElementById('input-cat-desc').value || null,
            order: parseInt(document.getElementById('input-cat-order').value) || 0,
        };

        try {
            if (editId) {
                await apiFetch('/admin/categories/' + editId, {
                    method: 'PATCH',
                    body: JSON.stringify(payload),
                });
                alert('Kategori berhasil diupdate!');
            } else {
                await apiFetch('/admin/categories', {
                    method: 'POST',
                    body: JSON.stringify(payload),
                });
                alert('Kategori berhasil dibuat!');
            }
            closeCategoryModal();
            loadCategories();
        } catch (err) {
            alert('Error: ' + err.message);
        }
    });

    // Delete Modal
    window.openDeleteCatModal = function(catId, catName) {
        deleteCatId = catId;
        document.getElementById('delete-cat-name').textContent = catName;
        document.getElementById('delete-cat-modal').classList.remove('hidden');
    };

    window.closeDeleteCatModal = function() {
        document.getElementById('delete-cat-modal').classList.add('hidden');
        deleteCatId = null;
    };

    window.submitDeleteCat = async function() {
        try {
            await apiFetch('/admin/categories/' + deleteCatId, { method: 'DELETE' });
            alert('Kategori berhasil dihapus!');
            closeDeleteCatModal();
            loadCategories();
        } catch (err) {
            alert('Error: ' + err.message);
        }
    };

    loadCategories();
})();
</script>
@endpush