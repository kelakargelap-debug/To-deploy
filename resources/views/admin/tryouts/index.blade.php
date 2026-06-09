@extends('app')
@section('content')
    <div class="max-w-7xl mx-auto">
        <x-page-header title="Tryout Management" subtitle="Kelola daftar tryout yang tersedia">
            <button onclick="navigateTo('/admin/tryouts/create')" class="btn-primary">
                + Tambah Tryout
            </button>
        </x-page-header>

        {{-- Tryouts Table --}}
        <x-data-table>
            <table class="w-full">
                <thead class="bg-[var(--bg-surface-hover)] border-b border-[var(--border-default)]">
                    <tr>
                        <th class="py-3 px-4 text-left font-label-sm uppercase text-[var(--text-secondary)]">Title</th>
                        <th class="py-3 px-4 text-left font-label-sm uppercase text-[var(--text-secondary)]">Category</th>
                        <th class="py-3 px-4 text-center font-label-sm uppercase text-[var(--text-secondary)]">Status</th>
                        <th class="py-3 px-4 text-center font-label-sm uppercase text-[var(--text-secondary)]">Tier</th>
                        <th class="py-3 px-4 text-center font-label-sm uppercase text-[var(--text-secondary)]">Questions</th>
                        <th class="py-3 px-4 text-center font-label-sm uppercase text-[var(--text-secondary)]">Duration</th>
                        <th class="py-3 px-4 text-center font-label-sm uppercase text-[var(--text-secondary)]">Actions</th>
                    </tr>
                </thead>
                <tbody id="tryouts-table-body">
                    <tr><td colspan="7" class="text-center">Loading...</td></tr>
                </tbody>
            </table>
        </x-data-table>
    </div>

    {{-- Delete Confirmation Modal --}}
    <x-modal id="delete-tryout-modal" title="Delete Tryout" size="sm">
        <p class="text-sm text-[var(--text-secondary)] mb-4">Are you sure you want to delete: <span id="delete-tryout-title" class="font-bold text-[var(--text-primary)]"></span>? This will also delete all associated questions and attempts.</p>
        <div class="flex gap-3 justify-end">
            <button data-modal-close="delete-tryout-modal" class="btn-secondary">Cancel</button>
            <button onclick="submitDeleteTryout()" class="btn-danger">Delete</button>
        </div>
    </x-modal>
@endsection

@push('scripts')
    <script>
        (function () {
            let tryouts = [];
            let categories = [];
            let deleteTryoutId = null;

            function escHtml(str) {
                const div = document.createElement('div');
                div.textContent = str || '';
                return div.innerHTML;
            }

            function statusBadge(status) {
                const cls = status === 'PUBLISHED' ? 'badge-success'
                    : status === 'DRAFT' ? 'badge-warning'
                        : 'badge-free';
                return '<span class="badge ' + cls + '">' + status + '</span>';
            }

            function tierBadge(tier) {
                const cls = tier === 'PREMIUM' ? 'badge-premium' : 'badge-free';
                return '<span class="badge ' + cls + '">' + tier + '</span>';
            }

            function getCategoryName(catId) {
                const cat = categories.find(c => c.id === catId);
                return cat ? cat.name : '-';
            }

            async function loadData() {
                const tbody = document.getElementById('tryouts-table-body');
                tbody.innerHTML = '<tr><td class="px-5 py-4 text-gray-500" colspan="7">Loading...</td></tr>';

                try {
                    // Load categories first for display names
                    categories = await apiFetch('/admin/categories');
                    // Load tryouts - using the admin endpoint structure
                    const data = await apiFetch('/tryouts');
                    tryouts = Array.isArray(data) ? data : (data.data || []);

                    if (tryouts && tryouts.length) {
                        tbody.innerHTML = tryouts.map(t => {
                            return '<tr class="border-b border-[var(--border-default)] hover:bg-[var(--bg-surface-hover)]">'
                                + '<td class="py-4 px-4 align-middle"><span class="font-medium text-[var(--text-primary)]">' + escHtml(t.title) + '</span></td>'
                                + '<td class="py-4 px-4 align-middle text-left">' + escHtml(getCategoryName(t.category_id)) + '</td>'
                                + '<td class="py-4 px-4 align-middle text-center">' + statusBadge(t.status) + '</td>'
                                + '<td class="py-4 px-4 align-middle text-center">' + tierBadge(t.required_tier) + '</td>'
                                + '<td class="py-4 px-4 align-middle text-center"><span class="font-mono font-bold">' + t.total_questions + '</span></td>'
                                + '<td class="py-4 px-4 align-middle text-center">' + t.duration_minutes + ' min</td>'
                                + '<td class="py-4 px-4 align-middle">'
                                + '<div class="flex gap-2 justify-center">'
                                + '<button onclick="navigateTo(\'/admin/tryouts/' + t.id + '/questions\')" class="btn-ghost btn-sm text-[var(--success)]">Questions</button>'
                                + '<button onclick="navigateTo(\'/admin/tryouts/' + t.id + '/edit\')" class="btn-ghost btn-sm text-[var(--info)]">Edit</button>'
                                + '<button onclick="openDeleteTryoutModal(' + t.id + ', \'' + escHtml(t.title) + '\')" class="btn-ghost btn-sm text-[var(--danger)]">Delete</button>'
                                + '</div>'
                                + '</td>'
                                + '</tr>';
                        }).join('');
                    } else {
                        tbody.innerHTML = '<tr><td colspan="7" class="text-center text-[var(--text-muted)]">No tryouts found</td></tr>';
                    }
                } catch (err) {
                    tbody.innerHTML = '<tr><td class="px-5 py-4 text-red-500" colspan="7">Error: ' + escHtml(err.message) + '</td></tr>';
                }
            }

            // Delete Modal
            window.openDeleteTryoutModal = function (tryoutId, tryoutTitle) {
                deleteTryoutId = tryoutId;
                document.getElementById('delete-tryout-title').textContent = tryoutTitle;
                openModal('delete-tryout-modal');
            };

            window.submitDeleteTryout = async function () {
                try {
                    await apiFetch('/admin/tryouts/' + deleteTryoutId, { method: 'DELETE' });
                    showToast('Tryout berhasil dihapus!', 'success');
                    closeModal('delete-tryout-modal');
                    loadData();
                } catch (err) {
                    showToast(err.message, 'error');
                }
            };

            loadData();
        })();
    </script>
@endpush