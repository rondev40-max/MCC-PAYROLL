<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $departments = Department::with(['employees'])->get();
        return view('departments.index', compact('departments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('departments.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:departments',
            'code' => 'required|string|max:10|unique:departments',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        Department::create($request->all());

        return redirect()->route('departments.index')
            ->with('success', 'Department created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Department $department)
    {
        $department->load(['employees', 'fulltimeTimesheets', 'parttimeTimesheets', 'staffTimesheets', 'utilityTimesheets']);

        // Build "present today" list from timesheet days JSON
        $todayKey = strtolower(now()->format('l')); // monday, tuesday, ...
        $isPresent = function ($days) use ($todayKey) {
            if (empty($days)) return false;
            // If stored as associative map like ['monday'=>8,...]
            if (is_array($days) && array_key_exists($todayKey, $days)) {
                return (int)($days[$todayKey] ?? 0) > 0;
            }
            // If stored as simple array of day names like ['Monday','Tuesday']
            if (is_array($days)) {
                return in_array(ucfirst($todayKey), $days, true);
            }
            // If stored as JSON string
            if (is_string($days)) {
                $decoded = json_decode($days, true) ?: [];
                if (array_key_exists($todayKey, $decoded)) {
                    return (int)($decoded[$todayKey] ?? 0) > 0;
                }
                return in_array(ucfirst($todayKey), $decoded, true);
            }
            return false;
        };

        $presentToday = collect();
        foreach ($department->fulltimeTimesheets as $t) {
            if ($isPresent($t->days)) {
                $presentToday->push([
                    'name' => $t->employee_name,
                    'designation' => $t->designation,
                    'type' => 'Fulltime'
                ]);
            }
        }
        foreach ($department->parttimeTimesheets as $t) {
            if ($isPresent($t->days)) {
                $presentToday->push([
                    'name' => $t->employee_name,
                    'designation' => $t->designation,
                    'type' => 'Part-time'
                ]);
            }
        }

        return view('departments.show', [
            'department' => $department,
            'presentToday' => $presentToday,
            'todayLabel' => ucfirst($todayKey)
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Department $department)
    {
        return view('departments.edit', compact('department'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Department $department)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:departments,name,' . $department->id,
            'code' => 'required|string|max:10|unique:departments,code,' . $department->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $department->update($request->all());

        return redirect()->route('departments.index')
            ->with('success', 'Department updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Department $department)
    {
        // Check if department has employees
        if ($department->employees()->count() > 0) {
            return redirect()->route('departments.index')
                ->with('error', 'Cannot delete department with existing employees.');
        }

        $department->delete();

        return redirect()->route('departments.index')
            ->with('success', 'Department deleted successfully.');
    }
}
