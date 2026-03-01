<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\BusinessStaff;
use App\Models\BusinessSubscription;
use App\Models\PayeReturn;
use App\Models\PayeSchedule;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\WhtReturn;
use App\Models\WhtTransaction;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class Phase2TestDataSeeder extends Seeder
{
    /**
     * Seed Phase 2 PAYE/WHT test data
     */
    public function run(): void
    {
        $now = now();

        $user = User::firstOrCreate(
            ['email' => 'phase2.business@taxmaster.test'],
            [
                'name' => 'Phase 2 Business',
                'password' => Hash::make('password'),
                'email_verified_at' => $now,
            ]
        );

        if (class_exists(Role::class)) {
            $role = Role::firstOrCreate(['name' => 'business', 'guard_name' => 'web']);
            if (!$user->hasRole('business')) {
                $user->assignRole($role);
            }
        }

        $business = Business::firstOrCreate(
            ['owner_id' => $user->id],
            [
                'name' => 'Phase 2 Test Business Ltd',
                'slug' => 'phase-2-test-business',
                'registration_number' => 'RC-P2-0001',
                'description' => 'Seeded business for Phase 2 testing',
                'business_type' => 'company',
                'email' => 'phase2.business@taxmaster.test',
                'phone' => '+2348012345678',
                'country' => 'NG',
                'state' => 'LA',
                'city' => 'Lagos',
                'address' => '123 Phase 2 Avenue, Lagos',
                'tax_identification_number' => 'P2-TIN-0001',
                'annual_revenue' => 15000000,
                'industry' => 'Technology',
                'status' => 'active',
                'email_verified' => true,
                'email_verified_at' => $now,
                'accounting_year_end' => $now->copy()->endOfYear()->toDateString(),
                'incorporation_date' => $now->copy()->subYears(3)->toDateString(),
                'has_staff' => true,
                'staff_count' => 3,
            ]
        );

        $plan = SubscriptionPlan::firstOrCreate(
            ['slug' => 'phase2-test'],
            [
                'name' => 'Phase 2 Test',
                'description' => 'Seeded plan for Phase 2 testing',
                'monthly_price' => 0,
                'annual_price' => 0,
                'max_staff_members' => 50,
                'max_returns_per_year' => 120,
                'storage_gb' => 5,
                'ai_analysis_included' => true,
                'payment_automation' => false,
                'priority_support' => false,
                'custom_branding' => false,
                'features' => ['PAYE', 'WHT', 'VAT'],
                'is_active' => true,
                'display_order' => 999,
            ]
        );

        BusinessSubscription::updateOrCreate(
            ['business_id' => $business->id, 'status' => 'active'],
            [
                'plan_id' => $plan->id,
                'plan_type' => $plan->slug,
                'monthly_price' => $plan->monthly_price,
                'annual_price' => $plan->annual_price,
                'max_staff_members' => $plan->max_staff_members,
                'max_returns_per_year' => $plan->max_returns_per_year,
                'ai_analysis_included' => $plan->ai_analysis_included,
                'payment_automation' => $plan->payment_automation,
                'billing_cycle' => 'monthly',
                'status' => 'active',
                'payment_status' => 'completed',
                'payment_method' => 'seed',
                'transaction_reference' => 'seed-' . Str::uuid(),
                'started_at' => $now->copy()->subDays(5),
                'renews_at' => $now->copy()->addDays(25),
            ]
        );

        $staffSeed = [
            [
                'first_name' => 'Ada',
                'last_name' => 'Okafor',
                'email' => 'ada.okafor@taxmaster.test',
                'phone' => '+2348010000001',
                'designation' => 'Accountant',
                'monthly_salary' => 450000,
            ],
            [
                'first_name' => 'Bola',
                'last_name' => 'Sule',
                'email' => 'bola.sule@taxmaster.test',
                'phone' => '+2348010000002',
                'designation' => 'HR Manager',
                'monthly_salary' => 380000,
            ],
            [
                'first_name' => 'Chidi',
                'last_name' => 'Ibe',
                'email' => 'chidi.ibe@taxmaster.test',
                'phone' => '+2348010000003',
                'designation' => 'Software Engineer',
                'monthly_salary' => 650000,
            ],
        ];

        $staffMembers = [];
        foreach ($staffSeed as $member) {
            $staffMembers[] = BusinessStaff::updateOrCreate(
                ['email' => $member['email']],
                [
                    'business_id' => $business->id,
                    'first_name' => $member['first_name'],
                    'last_name' => $member['last_name'],
                    'phone' => $member['phone'],
                    'monthly_salary' => $member['monthly_salary'],
                    'employment_type' => 'full_time',
                    'designation' => $member['designation'],
                    'date_employed' => $now->copy()->subYears(2)->toDateString(),
                    'status' => 'active',
                ]
            );
        }

        $period = $now->format('Y-m');
        $payeReturn = PayeReturn::updateOrCreate(
            ['business_id' => $business->id, 'period' => $period],
            [
                'status' => 'filed',
                'filed_date' => $now->toDateString(),
                'notes' => 'Seeded PAYE return for Phase 2 testing',
            ]
        );

        $totalGross = 0;
        $totalTax = 0;
        $scheduleData = [];

        foreach ($staffMembers as $staff) {
            $grossPay = (float) $staff->monthly_salary;
            $allowances = [
                'housing' => round($grossPay * 0.15, 2),
                'transport' => round($grossPay * 0.1, 2),
            ];
            $taxReliefs = [
                'cra' => round($grossPay * 0.2, 2),
                'pension' => round($grossPay * 0.08, 2),
            ];
            $taxableIncome = max($grossPay - array_sum($taxReliefs), 0);
            $payeDue = round($taxableIncome * 0.1, 2);

            PayeSchedule::updateOrCreate(
                ['paye_return_id' => $payeReturn->id, 'business_staff_id' => $staff->id],
                [
                    'gross_pay' => $grossPay,
                    'allowances' => $allowances,
                    'tax_reliefs' => $taxReliefs,
                    'taxable_income' => $taxableIncome,
                    'paye_due' => $payeDue,
                    'cumulative_gross' => $grossPay,
                    'cumulative_tax' => $payeDue,
                ]
            );

            $totalGross += $grossPay;
            $totalTax += $payeDue;

            $scheduleData[] = [
                'staff_id' => $staff->id,
                'name' => $staff->full_name,
                'gross_pay' => $grossPay,
                'taxable_income' => $taxableIncome,
                'paye_due' => $payeDue,
            ];
        }

        $payeReturn->update([
            'total_gross_pay' => $totalGross,
            'total_tax_deducted' => $totalTax,
            'staff_count' => count($staffMembers),
            'schedule_data' => $scheduleData,
        ]);

        $transactionSeed = [
            ['type' => 'contracts', 'vendor' => 'Prime Builders Ltd', 'amount' => 2000000, 'rate' => 5],
            ['type' => 'consultancy', 'vendor' => 'Insight Partners', 'amount' => 1200000, 'rate' => 10],
            ['type' => 'management_fees', 'vendor' => 'Alpha Holdings', 'amount' => 800000, 'rate' => 5],
        ];

        $whtTransactions = [];
        foreach ($transactionSeed as $item) {
            $whtAmount = round($item['amount'] * ($item['rate'] / 100), 2);
            $netAmount = $item['amount'] - $whtAmount;

            $whtTransactions[] = WhtTransaction::updateOrCreate(
                [
                    'business_id' => $business->id,
                    'transaction_date' => $now->toDateString(),
                    'transaction_type' => $item['type'],
                    'vendor_name' => $item['vendor'],
                ],
                [
                    'vendor_tin' => 'TIN-' . strtoupper(Str::random(6)),
                    'gross_amount' => $item['amount'],
                    'wht_rate' => $item['rate'],
                    'wht_amount' => $whtAmount,
                    'net_amount' => $netAmount,
                    'description' => 'Seeded transaction for Phase 2 testing',
                    'payment_reference' => 'WHT-' . Str::upper(Str::random(8)),
                ]
            );
        }

        $totalWht = 0;
        $scheduleByType = [];
        foreach ($whtTransactions as $txn) {
            $totalWht += (float) $txn->wht_amount;
            $type = $txn->transaction_type;

            if (!isset($scheduleByType[$type])) {
                $scheduleByType[$type] = [
                    'transaction_type' => $type,
                    'transaction_count' => 0,
                    'gross_amount' => 0,
                    'wht_rate' => $txn->wht_rate,
                    'wht_amount' => 0,
                ];
            }

            $scheduleByType[$type]['transaction_count'] += 1;
            $scheduleByType[$type]['gross_amount'] += (float) $txn->gross_amount;
            $scheduleByType[$type]['wht_amount'] += (float) $txn->wht_amount;
        }

        WhtReturn::updateOrCreate(
            ['business_id' => $business->id, 'period' => $period],
            [
                'status' => 'filed',
                'filed_date' => $now->toDateString(),
                'total_wht_deducted' => $totalWht,
                'transaction_count' => count($whtTransactions),
                'schedule_data' => array_values($scheduleByType),
                'notes' => 'Seeded WHT return for Phase 2 testing',
            ]
        );
    }
}
