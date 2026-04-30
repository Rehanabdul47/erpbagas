<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class ExcelParserController extends Controller
{
    /**
     * Display the excel parser page.
     */
    public function index()
    {
        return view('pages.tools.excel_parser', [
            'activePage' => 'excel-parser'
        ]);
    }

    /**
     * Parse the uploaded excel file and display the output for debugging.
     */
    public function parse(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv,ods',
        ]);

        $file = $request->file('excel_file');
        $path = $file->getRealPath();

        try {
            // Load the spreadsheet
            $spreadsheet = IOFactory::load($path);
            $sheet = $spreadsheet->getActiveSheet();
            $data = $sheet->toArray(null, true, true, true);

            // Optional: Get sheet names
            $sheetNames = $spreadsheet->getSheetNames();
            
            // Get some metadata for debug
            $metadata = [
                'filename' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'active_sheet' => $spreadsheet->getActiveSheetIndex(),
                'sheet_count' => $spreadsheet->getSheetCount(),
                'sheet_names' => $sheetNames,
                'row_count' => count($data),
                'column_count' => count($data[1] ?? []),
            ];

            return view('pages.tools.excel_parser', [
                'data' => $data,
                'metadata' => $metadata,
                'success' => 'File berhasil diparsing!',
                'activePage' => 'excel-parser'
            ]);

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memproses file: ' . $e->getMessage());
        }
    }
}
