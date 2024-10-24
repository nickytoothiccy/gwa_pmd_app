@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="text-2xl font-bold mb-4">Equipment Search</h1>
    <div class="mb-3">
        <input type="text" id="searchInput" class="w-full p-2 border rounded" placeholder="Search for equipment...">
    </div>
    <div id="searchResults"></div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const searchResults = document.getElementById('searchResults');
        let typingTimer;
        const doneTypingInterval = 300;

        searchInput.addEventListener('input', function() {
            clearTimeout(typingTimer);
            typingTimer = setTimeout(performSearch, doneTypingInterval);
        });

        function performSearch() {
            const query = searchInput.value;
            if (query.length >= 2) {
                axios.get('{{ route("pmd_cnet_ffu.equipment_search_results") }}', {
                    params: { query: query }
                })
                .then(function(response) {
                    displayResults(response.data);
                })
                .catch(function(error) {
                    console.error('Error:', error);
                });
            } else {
                searchResults.innerHTML = '';
            }
        }

        function displayResults(results) {
            searchResults.innerHTML = '';

            if (results.length === 0) {
                searchResults.innerHTML = '<p>No results found.</p>';
                return;
            }

            const ul = document.createElement('ul');
            ul.className = 'list-group';
            results.forEach(function(item) {
                const li = document.createElement('li');
                li.className = 'list-group-item';
                const link = document.createElement('a');
                link.href = `{{ route('pmd_cnet_ffu.edit') }}?parent=${encodeURIComponent(item.Parent)}&network=${encodeURIComponent(item.Network)}&port=${encodeURIComponent(item.Port)}&equipment=${encodeURIComponent(item.Equipment)}`;
                link.textContent = `${item.Equipment} (Parent: ${item.Parent}, Network: ${item.Network}, Port: ${item.Port})`;
                li.appendChild(link);
                ul.appendChild(li);
            });

            searchResults.appendChild(ul);
        }
    });
</script>
@endpush
