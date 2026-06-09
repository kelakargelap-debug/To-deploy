@extends('app')

@section('content')
<div class="max-w-7xl mx-auto">
    <x-page-header title="Categories Management" subtitle="Kelola kategori tryout">
        <x-slot name="slot">
            <button onclick="openCreateModal()" class="btn-primary">
                + Tambah Kategori
            </button>
        </x-slot>
    </x-page-header>

    {{-- Categories Table --}}
    <x-data-table>
        <table class="w-full">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Description</th>
                    <th>Order</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="categories-table-body">
                <tr><td colspan="5" class="text-center">Loading...</td></tr>
            </tbody>
        </table>
    </x-data-table>
</div>

{{-- Create/Edit Category Modal --}}
<x-modal id="category-modal" title="Tambah Kategori" size="md">
    <form id="category-form" class="space-y-4">
        <input type="hidden" id="edit-category-id" value="">
        <x-form-field 
            id="input-cat-name" 
            label="Name" 
            type="text" 
            required="true" 
        />
        <x-form-field 
            id="input-cat-desc" 
            label="Description" 
            type="textarea" 
            rows="3" 
        />
        <x-form-field 
            id="input-cat-order" 
            label="Order" 
            type="number" 
            value="0" 
        />
        <div class="flex gap-3 justify-end pt-2">
            <button type="button" data-modal-close="category-modal" class="btn-secondary">Cancel</button>
            <button type="submit" class="btn-primary">Save</button>
        </div>
    </form>
</x-modal>

{{-- Delete Confirmation Modal --}}
<x-modal id="delete-cat-modal" title="Delete Category" size="sm">
    <p class="text-sm text-[var(--text-secondary)] mb-4">Are you sure you want to delete: <span id="delete-cat-name" class="font-bold text-[var(--text-primary)]"></span>? This will also delete all associated tryouts and materials.</p>
    <div class="flex gap-3 justify-end">
        <button data-modal-close="delete-cat-modal" class="btn-secondary">Cancel</button>
        <button onclick="submitDeleteCat()" class="btn-danger">Delete</button>
    </div>
</x-modal>
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
                    <tr>
                        <td><span class="font-medium text-[var(--text-primary)]">${escHtml(c.name)}</span></td>
                        <td>${escHtml(c.slug)}</td>
                        <td class="max-w-xs truncate">${escHtml(c.description) || '-'}</td>
                        <td>${c.order}</td>
                        <td>
                            <div class="flex gap-2">
                                <button onclick="openEditModal(${c.id})" class="btn-ghost btn-sm text-[var(--info)]">Edit</button>
                                <button onclick="openDeleteCatModal(${c.id}, '${escHtml(c.name)}')" class="btn-ghost btn-sm text-[var(--danger)]">Delete</button>
                            </div>
                        </td>
                    </tr>
                `).join('');
            } else {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center text-[var(--text-muted)]">No categories found</td></tr>';
            }
        } catch (err) {
            tbody.innerHTML = `<tr><td colspan="5" class="text-center text-[var(--danger)]">Error: ${escHtml(err.message)}</td></tr>`;
        }
    }

    // Create Modal
    window.openCreateModal = function() {
        document.querySelector('#category-modal h3').textContent = 'Tambah Kategori';
        document.getElementById('edit-category-id').value = '';
        document.getElementById('input-cat-name').value = '';
        document.getElementById('input-cat-desc').value = '';
        document.getElementById('input-cat-order').value = '0';
        openModal('category-modal');
    };

    // Edit Modal
    window.openEditModal = function(catId) {
        const cat = categories.find(c => c.id === catId);
        if (!cat) return;
        document.querySelector('#category-modal h3').textContent = 'Edit Kategori';
        document.getElementById('edit-category-id').value = catId;
        document.getElementById('input-cat-name').value = cat.name;
        document.getElementById('input-cat-desc').value = cat.description || '';
        document.getElementById('input-cat-order').value = cat.order;
        openModal('category-modal');
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
                showToast('Kategori berhasil diupdate!', 'success');
            } else {
                await apiFetch('/admin/categories', {
                    method: 'POST',
                    body: JSON.stringify(payload),
                });
                showToast('Kategori berhasil dibuat!', 'success');
            }
            closeModal('category-modal');
            loadCategories();
        } catch (err) {
            showToast(err.message, 'error');
        }
    });

    // Delete Modal
    window.openDeleteCatModal = function(catId, catName) {
        deleteCatId = catId;
        document.getElementById('delete-cat-name').textContent = catName;
        openModal('delete-cat-modal');
    };

    window.submitDeleteCat = async function() {
        try {
            await apiFetch('/admin/categories/' + deleteCatId, { method: 'DELETE' });
            showToast('Kategori berhasil dihapus!', 'success');
            closeModal('delete-cat-modal');
            loadCategories();
        } catch (err) {
            showToast(err.message, 'error');
        }
    };

    loadCategories();
})();
</script>
@endpush