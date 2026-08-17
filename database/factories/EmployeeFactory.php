<?php

namespace Database\Factories;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Employee>
 */
class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        return [
            'name'          => $this->faker->name(),
            'email'         => $this->faker->unique()->safeEmail(),
            // `hours` is NOT NULL with no default on the employees table, so it
            // has to be generated even though nothing in the app writes it.
            'hours'         => $this->faker->numberBetween(0, 40),
            // The column is an enum; anything outside these three is rejected.
            'position'      => $this->faker->randomElement(['Instructor', 'Staff', 'Parttime']),
            'hourly_salary' => $this->faker->randomFloat(2, 150, 600),
            'department_id' => null,
        ];
    }
}
