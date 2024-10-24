@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-4">Edit Equipment: {{ $item->Equipment }}</h1>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('pmd_cnet_ffu.update_equipment', $item->Equipment) }}" method="POST" class="space-y-4">
        @csrf
        @method('PUT')

        <div>
            <label for="Description" class="block text-sm font-medium text-gray-700">Description</label>
            <input type="text" name="Description" id="Description" value="{{ old('Description', $item->Description) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
        </div>

        <div>
            <label for="Location" class="block text-sm font-medium text-gray-700">Location</label>
            <input type="text" name="Location" id="Location" value="{{ old('Location', $item->Location) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
        </div>

        <div>
            <label for="Panel_Type" class="block text-sm font-medium text-gray-700">Panel Type</label>
            <input type="text" name="Panel_Type" id="Panel_Type" value="{{ old('Panel_Type', $item->Panel_Type) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
        </div>

        <div>
            <label for="Network" class="block text-sm font-medium text-gray-700">Network</label>
            <input type="text" name="Network" id="Network" value="{{ old('Network', $item->Network) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
        </div>

        <div>
            <label for="Port" class="block text-sm font-medium text-gray-700">Port</label>
            <input type="text" name="Port" id="Port" value="{{ old('Port', $item->Port) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
        </div>

        <div>
            <label for="CNX_Sequence" class="block text-sm font-medium text-gray-700">CNX Sequence</label>
            <input type="number" name="CNX_Sequence" id="CNX_Sequence" value="{{ old('CNX_Sequence', $item->CNX_Sequence) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
        </div>

        <div>
            <label for="Node" class="block text-sm font-medium text-gray-700">Node</label>
            <input type="text" name="Node" id="Node" value="{{ old('Node', $item->Node) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
        </div>

        <div>
            <label for="CNX_Type" class="block text-sm font-medium text-gray-700">CNX Type</label>
            <input type="text" name="CNX_Type" id="CNX_Type" value="{{ old('CNX_Type', $item->CNX_Type) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
        </div>

        <div>
            <label for="Comment" class="block text-sm font-medium text-gray-700">Comment</label>
            <textarea name="Comment" id="Comment" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('Comment', $item->Comment) }}</textarea>
        </div>

        <div class="flex items-center justify-between">
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                Update Equipment
            </button>
            <a href="{{ url()->previous() }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 active:bg-gray-500 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                Back
            </a>
        </div>
    </form>
</div>
@endsection