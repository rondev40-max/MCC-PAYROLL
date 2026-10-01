<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;

uses(RefreshDatabase::class);

/**
 * Every list in the admin portal pages by 8 — the same page size the
 * attendance dashboard already used — instead of a mix of 10, 15, 20 and 25.
 */

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
});

it('pages the server-side admin lists by 8', function (string $route, string $variable) {
    $this->actingAs($this->admin)
        ->get(route($route))
        ->assertOk()
        ->assertViewHas($variable, fn ($list) => $list instanceof LengthAwarePaginator && $list->perPage() === 8);
})->with([
    'activity log'       => ['admin.activity-log', 'activities'],
    'payslip history'    => ['admin.history', 'histories'],
    'history trash'      => ['admin.history.trash', 'histories'],
    'payroll history'    => ['admin.payroll.history', 'records'],
    'attendance payroll' => ['admin.attendance-payroll.index', 'employees'],
]);

it('only puts 8 rows on a page and carries the rest to page 2', function () {
    foreach (range(1, 11) as $i) {
        \App\Models\PayslipHistory::create([
            'employee_name' => "Employee $i",
            'email'         => "employee$i@example.com",
            'sent_at'       => now()->subMinutes($i),
        ]);
    }

    $this->actingAs($this->admin)->get(route('admin.history'))
        ->assertViewHas('histories', fn ($p) => $p->count() === 8 && $p->total() === 11 && $p->lastPage() === 2);

    $this->actingAs($this->admin)->get(route('admin.history', ['page' => 2]))
        ->assertViewHas('histories', fn ($p) => $p->count() === 3);
});

it('pages the master list DataTable by 8', function () {
    $this->actingAs($this->admin)
        ->get(route('master.list'))
        ->assertOk()
        ->assertSee('pageLength: 8', false)
        ->assertSee("lengthMenu: [8, 16, 24, 32, { label: 'All', value: -1 }]", false);
});
