@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h1 class="mb-4">Search Results for "{{ $equipment }}"</h1>
    @if ($results->isEmpty())
        <div class="alert alert-info">No results found for the selected equipment.</div>
    @else
        <div class="card mb-4">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Equipment</th>
                                <th>Tag ID</th>
                                <th>Description</th>
                                <th>Drawing</th>
                                <th>ISA Type</th>
                                <th>IO Type</th>
                                <th>Slot</th>
                                <th>Channel</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($results as $result)
                            <tr>
                                <td>{{ $result->Equipment }}</td>
                                <td>{{ $result->tag_id ?? $result->TagID ?? '' }}</td>
                                <td>{{ $result->description ?? $result->Description ?? '' }}</td>
                                <td>{{ $result->drawing ?? $result->Drawing ?? '' }}</td>
                                <td>{{ $result->isa_type ?? $result->ISAType ?? '' }}</td>
                                <td>{{ $result->io_type ?? $result->IOType ?? '' }}</td>
                                <td>{{ $result->slot ?? $result->Slot ?? '' }}</td>
                                <td>{{ $result->channel ?? $result->Channel ?? '' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="d-flex justify-content-center">
            {{ $results->links() }}
        </div>
    @endif
    <a href="{{ route('reports.index') }}" class="btn btn-secondary mt-3">Back to Search</a>
</div>
@endsection