<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

<div class="equipment-list">
    @foreach($equipment as $item)
        <div class="equipment-item">
            <a href="{{ url('/pmd_cnet_ffu/' . urlencode($item->Equipment) . '/edit') }}" class="btn btn-sm btn-link edit-btn" title="Edit">
                <i class="fas fa-cog"></i>
            </a>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Equipment</th>
                        <th>Network</th>
                        <th>Port</th>
                        <th>CNX_Sequence</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $item->Equipment }}</td>
                        <td>{{ $item->Network }}</td>
                        <td>{{ $item->Port }}</td>
                        <td>{{ $item->CNX_Sequence }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    @endforeach
</div>

<style>
    .equipment-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    .equipment-item {
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    .edit-btn {
        padding: 0;
        font-size: 1.5rem;
        color: #6c757d;
        transition: color 0.3s;
    }
    .edit-btn:hover {
        color: #007bff;
    }
    .equipment-item .table {
        margin-bottom: 0;
        flex-grow: 1;
    }
</style>
