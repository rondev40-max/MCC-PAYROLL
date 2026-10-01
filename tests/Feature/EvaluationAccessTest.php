<?php

use App\Models\Evaluation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Only employees fill in the evaluation; admins only see the results.
 *
 * The admin results page used to carry a "Fill Form" button, and a generic
 * POST /evaluation route sat behind plain `auth` — so an admin or an
 * attendance checker could submit an evaluation and skew the results.
 */

/** A complete form submission: every answer 2, except all usability answers 4. */
function evaluationAnswers(array $overrides = []): array
{
    $answers = ['respondent_role' => 'Faculty'];
    foreach (range(1, 5) as $i) {
        $answers["usability_$i"] = 4;
        $answers["eff_$i"] = 2;
        $answers["sat_$i"] = 2;
    }

    return array_merge($answers, [
        'feedback_useful' => 'Payslips are easy to find.',
        'feedback_problems' => null,
        'feedback_suggestions' => null,
    ], $overrides);
}

it('lets an employee submit one evaluation and redirects back to the form', function () {
    $employee = User::factory()->create(['role' => 'employee']);

    $this->actingAs($employee)
        ->post(route('employee.evaluation.store'), evaluationAnswers())
        ->assertRedirect(route('employee.evaluation.form'))
        ->assertSessionHas('eval_success');

    $evaluation = Evaluation::sole();
    expect($evaluation->user_id)->toBe($employee->id)
        ->and($evaluation->respondent_role)->toBe('Faculty')
        ->and($evaluation->usability_scores)->toBe(['q1' => 4, 'q2' => 4, 'q3' => 4, 'q4' => 4, 'q5' => 4])
        ->and($evaluation->feedback['useful'])->toBe('Payslips are easy to find.');

    // The form now shows the "already submitted" state instead of the questions.
    $this->actingAs($employee)
        ->get(route('employee.evaluation.form'))
        ->assertOk()
        ->assertSee("You've Already Submitted!", false)
        ->assertDontSee('id="evalForm"', false);
});

it('computes the averages itself instead of trusting the hidden form fields', function () {
    $employee = User::factory()->create(['role' => 'employee']);

    $this->actingAs($employee)->post(route('employee.evaluation.store'), evaluationAnswers([
        'avg_usability' => 5, 'avg_efficiency' => 5, 'avg_satisfaction' => 5, 'overall_avg' => 5,
    ]));

    $evaluation = Evaluation::sole();
    expect((float) $evaluation->avg_usability)->toBe(4.0)
        ->and((float) $evaluation->avg_efficiency)->toBe(2.0)
        ->and((float) $evaluation->avg_satisfaction)->toBe(2.0)
        ->and((float) $evaluation->overall_avg)->toBe(2.67);
});

it('refuses a second evaluation from the same employee', function () {
    $employee = User::factory()->create(['role' => 'employee']);
    $this->actingAs($employee)->post(route('employee.evaluation.store'), evaluationAnswers());

    $this->actingAs($employee)
        ->post(route('employee.evaluation.store'), evaluationAnswers(['respondent_role' => 'Staff']))
        ->assertRedirect(route('employee.evaluation.form'))
        ->assertSessionHas('eval_error');

    expect(Evaluation::count())->toBe(1)
        ->and(Evaluation::sole()->respondent_role)->toBe('Faculty');
});

it('rejects a respondent role the form does not offer', function () {
    $employee = User::factory()->create(['role' => 'employee']);

    $this->actingAs($employee)
        ->post(route('employee.evaluation.store'), evaluationAnswers(['respondent_role' => 'Hacker']))
        ->assertSessionHasErrors('respondent_role');

    expect(Evaluation::count())->toBe(0);
});

it('does not let an admin open or submit the evaluation form', function (string $role) {
    $admin = User::factory()->create(['role' => $role]);

    $this->actingAs($admin)->get(route('employee.evaluation.form'))->assertForbidden();
    $this->actingAs($admin)->post(route('employee.evaluation.store'), evaluationAnswers())->assertForbidden();

    // The old generic route that any logged-in account could post to is gone.
    $this->actingAs($admin)->post('/evaluation', evaluationAnswers())->assertNotFound();

    expect(Evaluation::count())->toBe(0);
})->with(['admin', 'super_admin']);

it('does not let an attendance checker submit an evaluation', function () {
    $checker = User::factory()->create(['role' => 'attendance_checker']);

    $this->actingAs($checker)->post(route('employee.evaluation.store'), evaluationAnswers())->assertForbidden();
    $this->actingAs($checker)->post('/evaluation', evaluationAnswers())->assertNotFound();

    expect(Evaluation::count())->toBe(0);
});

it('shows admins the results without any way to fill in the form', function () {
    $employee = User::factory()->create(['role' => 'employee']);
    $this->actingAs($employee)->post(route('employee.evaluation.store'), evaluationAnswers());

    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('admin.evaluation.results'))
        ->assertOk()
        ->assertSee('Evaluation Results')
        ->assertSee('2.67')                      // overall average
        ->assertSee('Faculty')                   // the respondent's role
        ->assertDontSee('Fill Form')
        ->assertDontSee(route('employee.evaluation.form'), false)
        ->assertDontSee(route('employee.evaluation.store'), false)
        ->assertSee('id="logout-form-eval"', false);
});

it('keeps the results page away from employees', function () {
    $employee = User::factory()->create(['role' => 'employee']);

    $this->actingAs($employee)
        ->get(route('admin.evaluation.results'))
        ->assertRedirect('/');
});
