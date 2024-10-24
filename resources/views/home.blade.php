@extends('layouts.app')

@section('content')
<div class="container-fluid glossy-bg" style="min-height: calc(100vh - 56px - 58px);">
    <div class="row h-100 align-items-center justify-content-center">
        <div class="col-md-6 text-center">
            <h1 class="mb-4">Welcome to GWA PMD App</h1>
            <a href="{{ route('pmd_cnet_ffu.index') }}" class="btn btn-large btn-success m-2">FFU</a>
            <a href="{{ route('reports.index') }}" class="btn btn-large btn-primary m-2">Reports</a>
            <a href="{{ route('fab_viewer.index') }}" class="btn btn-large m-2" style="background-color: #FFA500; color: white;">Fab Viewer</a>
        </div>
    </div>
</div>
@endsection
