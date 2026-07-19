<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class HolidayController extends Controller
{
    /**
     * Display a listing of the holidays.
     */
    public function index()
    {
        // Kunin ang lahat ng holidays at i-sort ayon sa petsa
        $holidays = Holiday::orderBy('date', 'asc')->get();
        return view('holidays.index', compact('holidays'));
    }

    /**
     * Store a newly created holiday in storage.
     */
    public function store(Request $request)
    {
        try {
            // 1. Validation
            $validatedData = $request->validate([
                'date' => 'required|date|unique:holidays,date',
                'name' => 'required|string|max:255',
            ], [
                'date.unique' => 'Ang petsang ito ay naka-set na bilang Holiday.',
                'date.required' => 'Kailangan ang Petsa.',
                'name.required' => 'Kailangan ang Pangalan ng Holiday.',
            ]);

            // 2. Creation
            Holiday::create($validatedData);

            // 3. Success Redirect: Ito na ang binagong redirect papunta sa 'fulltime.index'
            return redirect()->route('fulltime.index')->with('success', 'Holiday successfully added and updated on timesheet!');
            
        } catch (ValidationException $e) {
            // Error handling para sa validation (e.g., duplicate date)
            return redirect()->route('holidays.index')
                             ->withErrors($e->errors())
                             ->withInput()
                             ->with('error', 'Failed to add Holiday. Please check your inputs.');
        } catch (\Exception $e) {
             // Catch all other unexpected errors
             return redirect()->route('holidays.index')
                              ->with('error', 'An unexpected error occurred.');
        }
    }

    /**
     * Remove the specified holiday from storage.
     */
    public function destroy(Holiday $holiday)
    {
        try {
            $holiday->delete();
            return redirect()->route('holidays.index')->with('success', 'Holiday successfully deleted!');
        } catch (\Exception $e) {
            return redirect()->route('holidays.index')->with('error', 'Failed to delete Holiday.');
        }
    }
    
    // Hindi na kailangan ang create, show, edit, update for a simple list manager
    // Maaari mo itong i-delete o iwanang blanko muna.
    public function create() { return abort(404); }
    public function show() { return abort(404); }
    public function edit() { return abort(404); }
    public function update() { return abort(404); }
}