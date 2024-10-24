@extends('layouts.app')

@section('content')
<div class="container-fluid p-0 d-flex flex-column vh-100">
    <div class="p-2 w-100 bg-light">
        <select id="pdfSelect" class="form-select form-select-sm">
            <option value="">Select a PDF</option>
            @foreach ($pdfFiles as $fileName)
                <option value="{{ $fileName }}">{{ $fileName }}</option>
            @endforeach
        </select>
    </div>

    <div id="viewerContainer" class="flex-grow-1 position-relative mt-2">
        <div id="pdfViewer" class="pdfViewer"></div>
    </div>
    <div id="errorMessage" class="alert alert-danger" style="display: none;"></div>
    <div id="debugInfo" class="alert alert-info mt-2" style="display: none;"></div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/pdfjs/build/pdf.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const pdfSelect = document.getElementById('pdfSelect');
    const container = document.getElementById('viewerContainer');
    const viewer = document.getElementById('pdfViewer');
    const errorMessage = document.getElementById('errorMessage');
    const debugInfo = document.getElementById('debugInfo');

    let pdfDoc = null;
    let pageNum = 1;
    let pageRendering = false;
    let pageNumPending = null;
    let scale = 1.5;

    // Set worker source
    pdfjsLib.GlobalWorkerOptions.workerSrc = '{{ asset('js/pdfjs/build/pdf.worker.js') }}';

    function loadPDF(url) {
        debugInfo.textContent = 'Attempting to load PDF: ' + url;
        debugInfo.style.display = 'block';

        pdfjsLib.getDocument(url).promise.then(function(pdf) {
            pdfDoc = pdf;
            debugInfo.textContent += '\nPDF loaded successfully. Number of pages: ' + pdf.numPages;
            
            // Render the first page
            renderPage(pageNum);
        }).catch(function(error) {
            console.error('Error loading PDF:', error);
            errorMessage.textContent = 'Error loading PDF: ' + error.message;
            errorMessage.style.display = 'block';
            debugInfo.textContent += '\nError: ' + error.message;
        });
    }

    function renderPage(num) {
        pageRendering = true;
        pdfDoc.getPage(num).then(function(page) {
            const viewport = page.getViewport({scale: scale});
            const canvas = document.createElement('canvas');
            const context = canvas.getContext('2d');
            canvas.height = viewport.height;
            canvas.width = viewport.width;

            const renderContext = {
                canvasContext: context,
                viewport: viewport
            };
            
            viewer.innerHTML = ''; // Clear previous content
            viewer.appendChild(canvas);
            
            page.render(renderContext).promise.then(function() {
                pageRendering = false;
                debugInfo.textContent += '\nPage ' + num + ' rendered';
                if (pageNumPending !== null) {
                    renderPage(pageNumPending);
                    pageNumPending = null;
                }
            });
        });
    }

    pdfSelect.addEventListener('change', function() {
        if (this.value) {
            const pdfUrl = '{{ asset('PDF/Fab_Viewer/FFU') }}/' + encodeURIComponent(this.value);
            loadPDF(pdfUrl);
        }
    });

    // Check if PDF.js is loaded correctly
    if (typeof pdfjsLib === 'undefined') {
        console.error('PDF.js library is not loaded');
        errorMessage.textContent = 'PDF.js library is not loaded. Please check the console for more details.';
        errorMessage.style.display = 'block';
    } else {
        debugInfo.textContent = 'PDF.js library loaded successfully';
        debugInfo.style.display = 'block';
    }
});
</script>
@endpush

@push('styles')
<style>
    body, html {
        height: 100%;
        margin: 0;
        padding: 0;
        overflow: hidden;
    }
    #viewerContainer {
        overflow: auto;
        position: relative;
        width: 100%;
        height: calc(100vh - 60px);
    }
    #pdfViewer {
        position: absolute;
        width: 100%;
        height: 100%;
    }
    #errorMessage, #debugInfo {
        position: fixed;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 1000;
        width: 80%;
        max-width: 600px;
    }
</style>
@endpush
