<?php

/**
 * Robustness Test for Deposit/Withdrawal Column Mapping
 * Tests various bank statement formats to ensure correct recognition
 */

echo "=== COLUMN MAPPING ROBUSTNESS TEST ===\n\n";

// Simulate the fuzzy mapping logic
function testFuzzyMapping($headers) {
    // Exact aliases
    $exactAliases = [
        'transaction_date' => ['date', 'transaction date', 'posted', 'value date', 'booking date', 'trans date'],
        'description' => ['description', 'narration', 'details', 'memo', 'particulars', 'remarks'],
        'amount' => ['amount', 'amt', 'value', 'amount (ngn)', 'transaction amount'],
        'type' => ['type', 'debit/credit', 'dr/cr', 'credit/debit', 'transaction type', 'txn type'],
        'balance' => ['balance', 'running balance', 'acct balance', 'account balance', 'closing balance'],
        'counterparty' => ['merchant', 'counterparty', 'payee', 'payer', 'beneficiary', 'beneficiary name'],
        'reference' => ['reference', 'txn ref', 'transaction id', 'transaction reference', 'ref no', 'ref'],
        'category' => ['category', 'merchant category', 'mcc', 'tag', 'expense category'],
    ];

    $depositAliases = ['deposit', 'deposits', 'credit', 'credits', 'credit amount', 'money in', 'receipts', 'cr', 'income', 'inflow'];
    $withdrawalAliases = ['withdrawal', 'withdrawals', 'debit', 'debits', 'debit amount', 'money out', 'payments', 'dr', 'expense', 'outflow'];

    $mapping = [];

    foreach ($headers as $h) {
        $norm = strtolower(trim($h));
        $found = false;

        // Step 1: Exact matches
        foreach ($exactAliases as $field => $words) {
            foreach ($words as $w) {
                if ($norm === $w) {
                    $mapping[$h] = $field;
                    $found = true;
                    break 2;
                }
            }
        }

        if ($found) continue;

        // Step 2: Deposit-specific
        foreach ($depositAliases as $w) {
            if ($norm === $w || str_contains($norm, $w)) {
                $mapping[$h] = 'deposit';
                $found = true;
                break;
            }
        }

        if ($found) continue;

        // Step 3: Withdrawal-specific
        foreach ($withdrawalAliases as $w) {
            if ($norm === $w || str_contains($norm, $w)) {
                $mapping[$h] = 'withdrawal';
                $found = true;
                break;
            }
        }

        if ($found) continue;

        // Step 4: Partial contains
        foreach ($exactAliases as $field => $words) {
            foreach ($words as $w) {
                if (str_contains($norm, $w) || str_contains($w, $norm)) {
                    $mapping[$h] = $field;
                    $found = true;
                    break 2;
                }
            }
        }

        if (!$found) {
            $mapping[$h] = 'skip';
        }
    }

    return $mapping;
}

// Test scenarios
$testCases = [
    'Nigerian Bank Standard' => ['Date', 'Description', 'Debit', 'Credit', 'Balance'],
    'GTBank Format' => ['Value Date', 'Narration', 'Withdrawals', 'Deposits', 'Running Balance'],
    'Access Bank Format' => ['Transaction Date', 'Details', 'DR', 'CR', 'Acct Balance'],
    'First Bank Format' => ['Posted', 'Particulars', 'Debits', 'Credits', 'Closing Balance'],
    'Zenith Format' => ['Date', 'Memo', 'Money Out', 'Money In', 'Balance'],
    'UBA Format' => ['Trans Date', 'Remarks', 'Payments', 'Receipts', 'Account Balance'],
    'Traditional Format' => ['Date', 'Description', 'Amount', 'Type', 'Balance'],
    'Accounting Software' => ['Transaction Date', 'Narration', 'Debit Amount', 'Credit Amount', 'Balance'],
    'Excel Export' => ['Date', 'Details', 'Expense', 'Income', 'Balance'],
    'QuickBooks Export' => ['Date', 'Description', 'Outflow', 'Inflow', 'Running Balance'],
];

$totalTests = 0;
$passedTests = 0;

foreach ($testCases as $bankName => $headers) {
    echo "TEST: $bankName\n";
    echo "Headers: " . implode(', ', $headers) . "\n";

    $mapping = testFuzzyMapping($headers);

    // Check if mapping is correct
    $hasDate = false;
    $hasDepositOrWithdrawal = false;
    $hasAmountField = false;

    foreach ($mapping as $header => $field) {
        echo "  - '$header' → $field\n";

        if ($field === 'transaction_date') $hasDate = true;
        if ($field === 'deposit' || $field === 'withdrawal') $hasDepositOrWithdrawal = true;
        if ($field === 'amount') $hasAmountField = true;
    }

    $totalTests++;

    // Validate: Must have date AND (deposit/withdrawal OR amount)
    if ($hasDate && ($hasDepositOrWithdrawal || $hasAmountField)) {
        echo "  ✅ PASS - Essential fields mapped correctly\n";
        $passedTests++;
    } else {
        echo "  ❌ FAIL - Missing essential fields:\n";
        if (!$hasDate) echo "    - No date field mapped\n";
        if (!$hasDepositOrWithdrawal && !$hasAmountField) echo "    - No amount/deposit/withdrawal field mapped\n";
    }

    echo "\n";
}

echo "=== TEST SUMMARY ===\n";
echo "Total Tests: $totalTests\n";
echo "Passed: $passedTests\n";
echo "Failed: " . ($totalTests - $passedTests) . "\n";
echo "Success Rate: " . round(($passedTests / $totalTests) * 100, 1) . "%\n\n";

// Edge case tests
echo "=== EDGE CASE TESTS ===\n\n";

$edgeCases = [
    'Ambiguous Credit Column' => [
        'headers' => ['Date', 'Credit', 'Balance'],
        'expected' => 'deposit', // Should map to deposit, not skip
    ],
    'Ambiguous Debit Column' => [
        'headers' => ['Date', 'Debit', 'Balance'],
        'expected' => 'withdrawal', // Should map to withdrawal, not skip
    ],
    'Amount with Type' => [
        'headers' => ['Date', 'Description', 'Amount', 'Type'],
        'expected' => 'amount', // Amount should be preserved
    ],
];

foreach ($edgeCases as $testName => $testData) {
    echo "EDGE CASE: $testName\n";
    $mapping = testFuzzyMapping($testData['headers']);

    $found = false;
    foreach ($mapping as $header => $field) {
        if (strtolower($header) === 'credit' || strtolower($header) === 'debit' || strtolower($header) === 'amount') {
            echo "  Column '$header' mapped to: $field\n";
            if ($field === $testData['expected']) {
                echo "  ✅ PASS - Correctly mapped\n";
                $found = true;
            } else {
                echo "  ❌ FAIL - Expected '{$testData['expected']}', got '$field'\n";
            }
        }
    }

    echo "\n";
}

echo "=== ROBUSTNESS TEST COMPLETE ===\n";
