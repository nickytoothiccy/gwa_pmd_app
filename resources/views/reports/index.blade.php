@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h1 class="mb-4">Reports</h1>
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="card">
        <div class="card-body">
            <form action="{{ route('reports.search') }}" method="GET" id="searchForm">
                <div class="mb-3">
                    <label for="equipment" class="form-label">Search Equipment:</label>
                    <input type="text" name="equipment" id="equipment" class="form-control" required autocomplete="off">
                </div>
                <div id="searchResults" class="list-group mb-3"></div>
                <button type="submit" class="btn btn-primary">Search</button>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const equipmentInput = document.getElementById('equipment');
        const searchResults = document.getElementById('searchResults');
        const searchForm = document.getElementById('searchForm');

        let selectedEquipment = '';

        equipmentInput.addEventListener('input', function() {
            const query = this.value.trim();
            if (query.length > 0) {
                fetch(`{{ route('reports.liveSearch') }}?query=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        searchResults.innerHTML = '';
                        data.forEach(item => {
                            const a = document.createElement('a');
                            a.href = '#';
                            a.className = 'list-group-item list-group-item-action';
                            a.textContent = item;
                            a.addEventListener('click', function(e) {
                                e.preventDefault();
                                equipmentInput.value = item;
                                selectedEquipment = item;
                                searchResults.innerHTML = '';
                            });
                            searchResults.appendChild(a);
                        });
                    });
            } else {
                searchResults.innerHTML = '';
            }
        });

        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            if (selectedEquipment) {
                this.submit();
            } else {
                alert('Please select an equipment from the search results.');
            }
        });
    });
</script>
@endpush
