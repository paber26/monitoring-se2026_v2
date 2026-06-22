<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Assignment;
use App\Imports\AssignmentImport;
use Maatwebsite\Excel\Facades\Excel;

class AssignmentController extends Controller
{
    public function index()
    {
        $assignments = Assignment::orderBy('updated_at', 'desc')->paginate(10);
        $totalFasih = Assignment::sum('total_assignment_fasih');
        $totalSls = Assignment::count();

        return view('dashboard', compact('assignments', 'totalFasih', 'totalSls'));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,xlsx,xls|max:10240',
        ]);

        try {
            Excel::import(new AssignmentImport, $request->file('file'));
            return redirect()->back()->with('success', 'Data berhasil diunggah dan diperbarui!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
