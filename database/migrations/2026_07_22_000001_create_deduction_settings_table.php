<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deduction_settings', function (Blueprint $table) {
            $table->id();
            $table->string('deduction_type', 50)->unique()->comment('withholding_tax, gsis, philhealth, pag_ibig, sss');
            $table->string('rate_type', 20)->default('percentage')->comment('percentage or fixed');
            $table->decimal('rate_value', 6, 3)->default(0)->comment('Rate percentage (e.g. 2.000 for 2%) or fixed amount');
            $table->decimal('min_amount', 10, 2)->nullable()->comment('Minimum deductible amount');
            $table->decimal('max_amount', 10, 2)->nullable()->comment('Maximum deductible amount');
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Seed default deduction settings (Philippine government rates)
        $settings = [
            [
                'deduction_type' => 'withholding_tax',
                'rate_type' => 'percentage',
                'rate_value' => 0.000, // 0% default, computed via bracket
                'min_amount' => null,
                'max_amount' => null,
                'is_active' => true,
                'description' => 'Withholding Tax on compensation (per BIR schedule)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'deduction_type' => 'gsis',
                'rate_type' => 'percentage',
                'rate_value' => 9.000, // 9% for regular gov't employees
                'min_amount' => null,
                'max_amount' => null,
                'is_active' => true,
                'description' => 'Government Service Insurance System (9% of basic salary)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'deduction_type' => 'philhealth',
                'rate_type' => 'percentage',
                'rate_value' => 4.000, // 4% total (2% employee share, 2% employer) 
                'min_amount' => null,
                'max_amount' => null,
                'is_active' => true,
                'description' => 'Philippine Health Insurance Corporation (4% of salary)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'deduction_type' => 'pag_ibig',
                'rate_type' => 'percentage',
                'rate_value' => 2.000, // 2% employee share
                'min_amount' => 100.00,
                'max_amount' => 200.00,
                'is_active' => true,
                'description' => 'Pag-IBIG Fund / HDMF (2% employee share, capped ₱100-₱200)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'deduction_type' => 'sss',
                'rate_type' => 'percentage',
                'rate_value' => 4.500, // 4.5% employee share (optional for gov't but included)
                'min_amount' => null,
                'max_amount' => null,
                'is_active' => true,
                'description' => 'Social Security System (optional, 4.5% employee share)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('deduction_settings')->insert($settings);
    }

    public function down(): void
    {
        Schema::dropIfExists('deduction_settings');
    }
};

