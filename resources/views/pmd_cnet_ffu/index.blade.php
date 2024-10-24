@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="text-2xl font-bold mb-4">GWA PMD Data</h1>

    <div class="mb-4">
        <a href="{{ route('pmd_cnet_ffu.equipment_search') }}" class="btn btn-primary">
            Equip. Search
        </a>
    </div>

    <div id="parentContainer" class="mb-4">
        <h2 class="text-xl font-bold mb-2">Select Parent:</h2>
        <div id="parentButtons" class="d-flex flex-wrap gap-2">
            @foreach($parents as $parent)
                <button class="parent-btn btn btn-outline-secondary" data-parent="{{ $parent }}">{{ $parent }}</button>
            @endforeach
        </div>
    </div>

    <div id="networkContainer" class="mb-4 d-none">
        <h2 class="text-xl font-bold mb-2">Select Network:</h2>
        <div id="networkButtons" class="d-flex flex-wrap gap-2"></div>
    </div>

    <div id="portContainer" class="mb-4 d-none">
        <h2 class="text-xl font-bold mb-2">Select Port:</h2>
        <div id="portButtons" class="d-flex flex-wrap gap-2"></div>
    </div>

    <div id="equipmentContainer" class="d-none">
        <h2 class="text-xl font-bold mb-2">Equipment List</h2>
        <div id="equipmentList" class="equipment-list"></div>
    </div>

    <div id="errorContainer" class="alert alert-danger d-none" role="alert">
        <strong>Error!</strong>
        <span id="errorMessage"></span>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var parentContainer = document.getElementById('parentContainer');
    var networkContainer = document.getElementById('networkContainer');
    var portContainer = document.getElementById('portContainer');
    var equipmentContainer = document.getElementById('equipmentContainer');
    var errorContainer = document.getElementById('errorContainer');
    var errorMessage = document.getElementById('errorMessage');

    var selectedParent = '{{ $selectedParent }}';
    var selectedNetwork = '{{ $selectedNetwork }}';
    var selectedPort = '{{ $selectedPort }}';
    var selectedEquipment = '{{ $selectedEquipment }}';

    function selectButton(container, value) {
        if (value) {
            var button = container.querySelector('[data-' + container.id.replace('Container', '') + '="' + value + '"]');
            if (button) {
                button.click();
            }
        }
    }

    function showError(message) {
        errorMessage.textContent = message;
        errorContainer.classList.remove('d-none');
    }

    function hideError() {
        errorContainer.classList.add('d-none');
    }

    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('parent-btn')) {
            selectedParent = e.target.getAttribute('data-parent');
            var parentButtons = document.querySelectorAll('.parent-btn');
            for (var i = 0; i < parentButtons.length; i++) {
                parentButtons[i].classList.remove('btn-primary');
            }
            e.target.classList.add('btn-primary');
            updateNetworks();
        } else if (e.target.classList.contains('network-btn')) {
            selectedNetwork = e.target.getAttribute('data-network');
            var networkButtons = document.querySelectorAll('.network-btn');
            for (var i = 0; i < networkButtons.length; i++) {
                networkButtons[i].classList.remove('btn-primary');
            }
            e.target.classList.add('btn-primary');
            updatePorts();
        } else if (e.target.classList.contains('port-btn')) {
            selectedPort = e.target.getAttribute('data-port');
            var portButtons = document.querySelectorAll('.port-btn');
            for (var i = 0; i < portButtons.length; i++) {
                portButtons[i].classList.remove('btn-primary');
            }
            e.target.classList.add('btn-primary');
            updateEquipment();
        }
    });

    function updateNetworks() {
        hideError();
        var xhr = new XMLHttpRequest();
        xhr.open('GET', '{{ route("pmd_cnet_ffu.get_networks") }}?parent=' + encodeURIComponent(selectedParent), true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                var response = JSON.parse(xhr.responseText);
                var networkButtons = document.getElementById('networkButtons');
                networkButtons.innerHTML = '';
                response.forEach(function(network) {
                    var button = document.createElement('button');
                    button.className = 'network-btn btn btn-outline-secondary';
                    button.textContent = network;
                    button.setAttribute('data-network', network);
                    networkButtons.appendChild(button);
                });
                networkContainer.classList.remove('d-none');
                portContainer.classList.add('d-none');
                equipmentContainer.classList.add('d-none');
                selectButton(networkContainer, selectedNetwork);
            } else {
                showError('Failed to fetch networks. Please try again.');
            }
        };
        xhr.onerror = function() {
            showError('Failed to fetch networks. Please try again.');
        };
        xhr.send();
    }

    function updatePorts() {
        hideError();
        var xhr = new XMLHttpRequest();
        xhr.open('GET', '{{ route("pmd_cnet_ffu.get_ports") }}?parent=' + encodeURIComponent(selectedParent) + '&network=' + encodeURIComponent(selectedNetwork), true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                var response = JSON.parse(xhr.responseText);
                var portButtons = document.getElementById('portButtons');
                portButtons.innerHTML = '';
                response.forEach(function(port) {
                    var button = document.createElement('button');
                    button.className = 'port-btn btn btn-outline-secondary';
                    button.textContent = port;
                    button.setAttribute('data-port', port);
                    portButtons.appendChild(button);
                });
                portContainer.classList.remove('d-none');
                equipmentContainer.classList.add('d-none');
                selectButton(portContainer, selectedPort);
            } else {
                showError('Failed to fetch ports. Please try again.');
            }
        };
        xhr.onerror = function() {
            showError('Failed to fetch ports. Please try again.');
        };
        xhr.send();
    }

    function updateEquipment() {
        hideError();
        var xhr = new XMLHttpRequest();
        xhr.open('GET', '{{ route("pmd_cnet_ffu.get_equipment") }}?parent=' + encodeURIComponent(selectedParent) + '&network=' + encodeURIComponent(selectedNetwork) + '&port=' + encodeURIComponent(selectedPort), true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                var response = JSON.parse(xhr.responseText);
                var equipmentList = document.getElementById('equipmentList');
                equipmentList.innerHTML = '';
                response.forEach(function(item) {
                    var equipmentItem = document.createElement('div');
                    equipmentItem.className = 'equipment-item';
                    equipmentItem.innerHTML = '<a href="{{ route('pmd_cnet_ffu.edit') }}?equipment=' + encodeURIComponent(item.Equipment) + '&parent=' + encodeURIComponent(selectedParent) + '&network=' + encodeURIComponent(selectedNetwork) + '&port=' + encodeURIComponent(selectedPort) + '" class="btn btn-sm btn-link edit-btn" title="Edit"><i class="fas fa-cog cog-icon"></i></a>' +
                        '<table class="table table-bordered">' +
                        '<thead><tr><th>Equipment</th><th>Network</th><th>Port</th><th>CNX_Sequence</th></tr></thead>' +
                        '<tbody><tr><td>' + item.Equipment + '</td><td>' + item.Network + '</td><td>' + item.Port + '</td><td>' + item.CNX_Sequence + '</td></tr></tbody>' +
                        '</table>';
                    if (item.Equipment === selectedEquipment) {
                        equipmentItem.classList.add('selected-equipment');
                    }
                    equipmentList.appendChild(equipmentItem);
                });
                equipmentContainer.classList.remove('d-none');
            } else {
                showError('Failed to fetch equipment. Please try again.');
            }
        };
        xhr.onerror = function() {
            showError('Failed to fetch equipment. Please try again.');
        };
        xhr.send();
    }

    if (selectedParent) {
        selectButton(parentContainer, selectedParent);
        updateNetworks();
    }
});
</script>
@endpush

@push('styles')
<style>
    .d-flex {
        display: flex;
    }
    .flex-wrap {
        flex-wrap: wrap;
    }
    .gap-2 > * {
        margin-right: 0.5rem;
        margin-bottom: 0.5rem;
    }
    .d-none {
        display: none;
    }
    .equipment-list {
        display: flex;
        flex-direction: column;
    }
    .equipment-item {
        display: flex;
        align-items: center;
        margin-bottom: 1rem;
    }
    .edit-btn {
        padding: 0;
        font-size: 1.5rem;
        color: #6c757d;
        transition: color 0.3s;
        margin-right: 1rem;
    }
    .edit-btn:hover {
        color: #007bff;
    }
    .equipment-item .table {
        margin-bottom: 0;
        flex-grow: 1;
    }
    .selected-equipment {
        background-color: #fff3cd;
    }
    .cog-icon {
        display: inline-block;
        width: 1em;
        height: 1em;
        vertical-align: middle;
        font-size: inherit;
        color: inherit;
    }
</style>
@endpush
