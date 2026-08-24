<?php

use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * The employee portal addressed real people as "Employee".
 *
 * Every name on the page read $employee->name — the master-list row — and fell
 * back to the literal string "Employee" when no row matched. A row failed to
 * match whenever the address the admin typed into the master list differed from
 * the one the account signs in with by so much as case or a trailing space, and
 * `users.name` (always set at registration) was never consulted.
 */

/** An account exactly as registration creates one. */
function account(string $name, string $email): User
{
    return User::create([
        'name'     => $name,
        'email'    => $email,
        'password' => bcrypt('irrelevant'),
        'role'     => 'employee',
    ]);
}

it('matches a master-list row whose email differs in case and spacing', function () {
    // How the admin typed Emely into the master list, versus how she signs in.
    Employee::create([
        'name'     => 'Emely Illustrisimo',
        'email'    => 'Emely@Gmail.com ',
        'position' => 'Full-time Instructor',
    ]);

    $user = account('Emely Illustrisimo', 'emely@gmail.com');

    expect(Employee::forAccount($user)?->name)->toBe('Emely Illustrisimo');
});

it('falls back to the account name when there is no master-list row', function () {
    $user = account('Juan Dela Cruz', 'juan@gmail.com');

    expect(Employee::forAccount($user))->toBeNull();

    // The portal must still greet him by name. displayName() is protected, so
    // exercise it the way the controller does.
    $controller = new App\Http\Controllers\EmployeeController();
    $displayName = (function () use ($user) {
        return $this->displayName($user, App\Models\Employee::forAccount($user));
    })->call($controller);

    expect($displayName)->toBe('Juan Dela Cruz');
});

it('never borrows an unrelated employee because the ids happen to match', function () {
    // The old lookup was `where('email', …)->orWhere('id', $employeeId)`, and
    // $employeeId falls back to users.id — so an account with no master-list row
    // picked up whichever stranger carried that number.
    $stranger = Employee::create([
        'name'     => 'Somebody Else',
        'email'    => 'somebody.else@example.com',
        'position' => 'Staff',
    ]);

    $user = account('Nameless Account', 'nomatch@gmail.com');
    // Force the collision the old code tripped over.
    $user->forceFill(['id' => $stranger->id])->save();

    expect(Employee::forAccount($user))->toBeNull();
});

it('trusts an employee_id written onto the account when no email matches', function () {
    // Their work address and their sign-in address are different mailboxes, so
    // there is nothing to match on — but the account carries an explicit link.
    $employee = Employee::create([
        'name'     => 'Linked Person',
        'email'    => 'linked.person@mcclawis.edu.ph',
        'position' => 'Staff',
    ]);

    $user = account('Linked Person', 'linked@gmail.com');
    $user->forceFill(['employee_id' => $employee->id])->save();

    expect(Employee::forAccount($user)?->name)->toBe('Linked Person');
});

it('prefers the master-list spelling of the name over the account spelling', function () {
    // The payroll office's own record is the authority on how a name is written.
    Employee::create([
        'name'     => 'Emely M. Illustrisimo',
        'email'    => 'emely@gmail.com',
        'position' => 'Full-time Instructor',
    ]);

    $user = account('emely illustrisimo', 'emely@gmail.com');

    $controller = new App\Http\Controllers\EmployeeController();
    $displayName = (function () use ($user) {
        return $this->displayName($user, App\Models\Employee::forAccount($user));
    })->call($controller);

    expect($displayName)->toBe('Emely M. Illustrisimo');
});

it('shows the real name on the portal instead of the Employee placeholder', function () {
    Employee::create([
        'name'     => 'Emely Illustrisimo',
        'email'    => 'Emely@Gmail.com ',
        'position' => 'Full-time Instructor',
    ]);

    $user = account('Emely Illustrisimo', 'emely@gmail.com');

    $response = $this->actingAs($user)->get('/employee/dashboard');

    $response->assertOk()
        ->assertSee('Welcome back, Emely Illustrisimo')
        ->assertSee('MCC Employee Portal — Emely Illustrisimo', false)
        // The placeholder must not appear as a name anywhere.
        ->assertDontSee('Welcome back, Employee');
});

it('lets an account with no master-list row submit the password form', function () {
    // Both profile forms post name and email, which portalUpdateProfile()
    // validates as required. They were seeded from the master-list row, so for
    // an unmatched account both went up empty and every password change was
    // rejected as "The name field is required".
    $user = account('Juan Dela Cruz', 'juan@gmail.com');

    $this->actingAs($user)->get('/employee/dashboard')
        ->assertOk()
        ->assertSee('value="Juan Dela Cruz"', false);

    $this->actingAs($user)->post('/employee/profile', [
        'name'                  => 'Juan Dela Cruz',
        'email'                 => 'juan@gmail.com',
        'current_password'      => 'irrelevant',
        'new_password'          => 'a-new-password',
        'new_password_confirmation' => 'a-new-password',
    ])->assertSessionHasNoErrors();
});
