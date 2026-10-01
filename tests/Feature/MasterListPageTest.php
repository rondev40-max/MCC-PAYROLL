<?php

use App\Models\FulltimeTimesheet;
use App\Models\StaffTimesheet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * The admin master list is a DataTable with type tabs, a department filter,
 * a details modal and a delete-confirmation modal.
 *
 * It used to load the DataTables 2 bundle without jQuery, so `new DataTable`
 * threw on page load: no paging, sorting, search, filters, CSV or print. It
 * also labelled per-day staff rates as "Rate/Hour", and its Education filter
 * matched nothing because rows are stored as BSED / BEED.
 */

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);

    FulltimeTimesheet::create([
        'employee_name' => 'Grace Bautista', 'email' => 'grace@example.com',
        'designation' => 'Full-time Instructor', 'department' => 'BSED',
        'rate_per_hour' => 185, 'total_hour' => 0, 'deduction' => 0, 'total_honorarium' => 0, 'days' => '[]',
    ]);
    StaffTimesheet::create([
        'employee_name' => 'Benjie Cruz', 'designation' => 'Cashier',
        'rate_per_day' => 540, 'total_days' => 0, 'deduction' => 0, 'total_honorarium' => 0, 'days' => '[]',
    ]);
});

it('loads a DataTables bundle that includes jQuery', function () {
    $html = $this->actingAs($this->admin)->get(route('master.list'))->assertOk()->getContent();

    // DataTables 2 is a jQuery plugin: without jq-* in the bundle nothing initialises.
    expect($html)->toMatch('#cdn\.datatables\.net/v/bs5/jq-[\d.]+/[^"]*datatables\.min\.js#')
        ->and($html)->toContain('new DataTable(tableEl');
});

it('renders each employee with the data the modals and filters rely on', function () {
    $response = $this->actingAs($this->admin)->get(route('master.list'))->assertOk();

    // Type tabs with counts.
    $response->assertSeeInOrder(['data-type="all"', '2', 'data-type="fulltime"', '1', 'data-type="staff"', '1'], false);

    // BSED is filed under Education for the department filter.
    $response->assertSee('data-search="EDUCATION BSED Education"', false)
        ->assertSee('ml-badge ml-badge-dept dept-education', false);

    // Instructors are paid per hour, staff per day.
    $response->assertSee('data-name="Grace Bautista"', false)
        ->assertSee('data-rate-unit="hr"', false)
        ->assertSee('data-name="Benjie Cruz"', false)
        ->assertSee('data-rate-unit="day"', false)
        ->assertSee('₱540.00 / day', false)
        ->assertDontSee('Rate/Hour');

    // Edit links carry the timesheet type, and the delete endpoint is wired in.
    $response->assertSee(route('master.list.edit', ['id' => StaffTimesheet::first()->id, 'type' => 'staff']), false)
        ->assertSee(json_encode(route('master.list.delete')), false);

    // Both modals are on the page.
    $response->assertSee('id="detailsModal"', false)->assertSee('id="deleteModal"', false);
});

it('keeps the master list away from employees', function () {
    $employee = User::factory()->create(['role' => 'employee']);

    $this->actingAs($employee)->get(route('master.list'))->assertRedirect('/');
});
