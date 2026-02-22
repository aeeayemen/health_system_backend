<?php

namespace App\Http\Controllers;

use App\Models\MedicalFile;
use Illuminate\Http\Request;

class RefController extends Controller
{
    /**
     * Get Nutrition References and Manuals for Doctors
     * e.g., Kraus Food & Nutrition Care Process
     */
    public function nutritionManuals()
    {
        // For now, we return clinical reference files from the medical_files table
        // that are marked as 'public' or 'reference'
        $references = MedicalFile::where('file_type', 'reference')
            ->orWhere('status', 'public_ref')
            ->get();

        return response()->json([
            'title' => 'Nutrition References (كراوس وغيره)',
            'references' => $references,
            'external_links' => [
                [
                    'name' => 'Mahan & Raymond: Krause\'s Food & the Nutrition Care Process',
                    'description' => 'المرجع الأساسي في علم التغذية السريرية',
                    'url' => 'https://www.elsevier.com/books/krauses-food-and-the-nutrition-care-process/9780323340571'
                ],
                [
                    'name' => 'USDA Food Composition Database',
                    'description' => 'قاعدة بيانات مكونات الغذاء الأمريكية',
                    'url' => 'https://fdc.nal.usda.gov/'
                ]
            ]
        ]);
    }
}
