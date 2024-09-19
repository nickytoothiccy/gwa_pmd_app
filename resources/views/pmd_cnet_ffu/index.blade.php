@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="text-2xl font-bold mb-4">GWA PMD Data</h1>

    <div class="mb-4">
        <a href="{{ route('equipment.search') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            Equip. Search
        </a>
    </div>

    <div id="parentContainer" class="mb-4">
        <h2 class="text-xl font-bold mb-2">Select Parent:</h2>
        <div id="parentButtons" class="flex flex-wrap gap-2">
            @foreach($parents as $parent)
                <button class="parent-btn bg-gray-200 hover:bg-gray-300 py-2 px-4 rounded" data-parent="{{ $parent }}">{{ $parent }}</button>
            @endforeach
        </div>
    </div>

    <div id="networkContainer" class="mb-4 hidden">
        <h2 class="text-xl font-bold mb-2">Select Network:</h2>
        <div id="networkButtons" class="flex flex-wrap gap-2"></div>
    </div>

    <div id="portContainer" class="mb-4 hidden">
        <h2 class="text-xl font-bold mb-2">Select Port:</h2>
        <div id="portButtons" class="flex flex-wrap gap-2"></div>
    </div>

    <div id="equipmentContainer" class="hidden">
        <h2 class="text-xl font-bold mb-2">Equipment List</h2>
        <table class="w-full border-collapse border">
            <thead>
                <tr>
                    <th class="border p-2">Equipment</th>
                    <th class="border p-2">Network</th>
                    <th class="border p-2">Port</th>
                    <th class="border p-2">CNX_Sequence</th>
                </tr>
            </thead>
            <tbody id="equipmentList"></tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM content loaded');

    const parentContainer = document.getElementById('parentContainer');
    const networkContainer = document.getElementById('networkContainer');
    const portContainer = document.getElementById('portContainer');
    const equipmentContainer = document.getElementById('equipmentContainer');

    let selectedParent = '{{ $selectedParent }}';
    let selectedNetwork = '{{ $selectedNetwork }}';
    let selectedPort = '{{ $selectedPort }}';
    let selectedEquipment = '{{ $selectedEquipment }}';

    console.log('Initial values:', { selectedParent, selectedNetwork, selectedPort, selectedEquipment });

    function selectButton(container, value) {
        if (value) {
            const button = container.querySelector(`[data-${container.id.replace('Container', '')}="${value}"]`);
            if (button) {
                console.log('Selecting button:', value);
                button.click();
            }
        }
    }

    parentContainer.addEventListener('click', function(e) {
        if (e.target.classList.contains('parent-btn')) {
            selectedParent = e.target.dataset.parent;
            console.log('Parent selected:', selectedParent);
            document.querySelectorAll('.parent-btn').forEach(btn => btn.classList.remove('bg-blue-500', 'text-white'));
            e.target.classList.add('bg-blue-500', 'text-white');
            updateNetworks();
        }
    });

    function updateNetworks() {
        console.log('Updating networks for parent:', selectedParent);
        return axios.get(`/get-networks?parent=${encodeURIComponent(selectedParent)}`)
            .then(response => {
                console.log('Networks received:', response.data);
                const networkButtons = document.getElementById('networkButtons');
                networkButtons.innerHTML = '';
                response.data.forEach(network => {
                    const button = document.createElement('button');
                    button.className = 'network-btn bg-gray-200 hover:bg-gray-300 py-2 px-4 rounded';
                    button.textContent = network;
                    button.dataset.network = network;
                    networkButtons.appendChild(button);
                });
                networkContainer.classList.remove('hidden');
                portContainer.classList.add('hidden');
                equipmentContainer.classList.add('hidden');
                selectButton(networkContainer, selectedNetwork);
            })
            .catch(error => {
                console.error('Error fetching networks:', error);
            });
    }

    networkContainer.addEventListener('click', function(e) {
        if (e.target.classList.contains('network-btn')) {
            selectedNetwork = e.target.dataset.network;
            console.log('Network selected:', selectedNetwork);
            document.querySelectorAll('.network-btn').forEach(btn => btn.classList.remove('bg-blue-500', 'text-white'));
            e.target.classList.add('bg-blue-500', 'text-white');
            updatePorts();
        }
    });

    function updatePorts() {
        console.log('Updating ports for parent:', selectedParent, 'and network:', selectedNetwork);
        return axios.get(`/get-ports?parent=${encodeURIComponent(selectedParent)}&network=${encodeURIComponent(selectedNetwork)}`)
            .then(response => {
                console.log('Ports received:', response.data);
                const portButtons = document.getElementById('portButtons');
                portButtons.innerHTML = '';
                response.data.forEach(port => {
                    const button = document.createElement('button');
                    button.className = 'port-btn bg-gray-200 hover:bg-gray-300 py-2 px-4 rounded';
                    button.textContent = port;
                    button.dataset.port = port;
                    portButtons.appendChild(button);
                });
                portContainer.classList.remove('hidden');
                equipmentContainer.classList.add('hidden');
                selectButton(portContainer, selectedPort);
            })
            .catch(error => {
                console.error('Error fetching ports:', error);
            });
    }

    portContainer.addEventListener('click', function(e) {
        if (e.target.classList.contains('port-btn')) {
            selectedPort = e.target.dataset.port;
            console.log('Port selected:', selectedPort);
            document.querySelectorAll('.port-btn').forEach(btn => btn.classList.remove('bg-blue-500', 'text-white'));
            e.target.classList.add('bg-blue-500', 'text-white');
            updateEquipment();
        }
    });

    function updateEquipment() {
        console.log('Updating equipment for parent:', selectedParent, 'network:', selectedNetwork, 'and port:', selectedPort);
        return axios.get(`/get-equipment?parent=${encodeURIComponent(selectedParent)}&network=${encodeURIComponent(selectedNetwork)}&port=${encodeURIComponent(selectedPort)}`)
            .then(response => {
                console.log('Equipment received:', response.data);
                const equipmentList = document.getElementById('equipmentList');
                equipmentList.innerHTML = '';
                response.data.forEach(item => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td class="border p-2">${item.Equipment}</td>
                        <td class="border p-2">${item.Network}</td>
                        <td class="border p-2">${item.Port}</td>
                        <td class="border p-2">${item.CNX_Sequence}</td>
                    `;
                    if (item.Equipment === selectedEquipment) {
                        row.classList.add('bg-yellow-200');
                    }
                    equipmentList.appendChild(row);
                });
                equipmentContainer.classList.remove('hidden');
            })
            .catch(error => {
                console.error('Error fetching equipment:', error);
            });
    }

    // Initial setup based on URL parameters
    if (selectedParent) {
        console.log('Initial parent selected:', selectedParent);
        selectButton(parentContainer, selectedParent);
        updateNetworks()
            .then(() => {
                if (selectedNetwork) {
                    console.log('Initial network selected:', selectedNetwork);
                    selectButton(networkContainer, selectedNetwork);
                    return updatePorts();
                }
            })
            .then(() => {
                if (selectedPort) {
                    console.log('Initial port selected:', selectedPort);
                    selectButton(portContainer, selectedPort);
                    return updateEquipment();
                }
            })
            .catch(error => {
                console.error('Error during initial setup:', error);
            });
    }
});
</script>
@endpush