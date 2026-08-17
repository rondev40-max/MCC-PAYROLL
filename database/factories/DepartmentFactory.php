<?php

namespace Database\Factories;

use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Department>
 */
class DepartmentFactory extends Factory
{
    protected $model = Department::class;

    public function definition(): array
    {
        // `code` is what timesheets join on (FulltimeTimesheet::department ->
        // Department::code), so it has to be unique per generated row or the
        // relationship tests would fan out across several departments.
        $code = strtoupper($this->faker->unique()->bothify('??##'));

        return [
            'name'        => $this->faker->words(3, true),
            'code'        => $code,
            'description' => $this->faker->sentence(),
            'is_active'   => true,
        ];
    }

    /** A department excluded from Department::active(). */
    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
