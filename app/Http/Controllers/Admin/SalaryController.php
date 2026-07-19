<?php

namespace App\Http\Controllers\Admin; // ⭐️ Tiyakin na ito ang tama, dahil nasa Admin subfolder

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SalaryController extends Controller
{
    /**
     * Display the salary adjustment form.
     */
    public function adjustmentForm()
    {
        // ⭐️ Inayos para ituro ang tamang view path na `resources/views/salary/adjustment.blade.php`
        return view('salary.adjustment'); 
        
        // PAALALA: Para sa mas magandang organization, mainam na ilipat ang view sa 'resources/views/admin/salary/adjustment.blade.php' at gamitin ang `view('admin.salary.adjustment')`.
    }
}