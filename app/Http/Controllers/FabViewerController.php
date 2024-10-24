<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class FabViewerController extends Controller
{
    public function index()
    {
        $pdfDirectory = public_path('PDF/Fab_Viewer/FFU');
        $pdfFiles = File::files($pdfDirectory);
        
        $fileNames = [];
        foreach ($pdfFiles as $file) {
            $fileName = $file->getFilename();
            $fileNames[$fileName] = $fileName;
        }

        return view('fab_viewer.index', ['pdfFiles' => $fileNames]);
    }
}
