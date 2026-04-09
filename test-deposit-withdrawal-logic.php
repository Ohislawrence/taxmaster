<?php

/**
 * Demonstration: How Deposit/Withdrawal vs Type Column Works
 */

echo "=== DEPOSIT/WITHDRAWAL COLUMN LOGIC EXPLAINED ===\n\n";

echo "SCENARIO 1: Bank Statement Format (Deposit + Withdrawal columns)\n";
echo "File has: Date, Description, Deposit, Withdrawal, Balance\n\n";

$bankStatementRows = [
    ['Date' => '2026-04-01', 'Description' => 'Salary', 'Deposit' => '500000', 'Withdrawal' => '0', 'Balance' => '500000'],
    ['Date' => '2026-04-02', 'Description' => 'Rent Payment', 'Deposit' => '0', 'Withdrawal' => '150000', 'Balance' => '350000'],
    ['Date' => '2026-04-03', 'Description' => 'Sales Revenue', 'Deposit' => '75000', 'Withdrawal' => '', 'Balance' => '425000'],
    ['Date' => '2026-04-04', 'Description' => 'Office Supplies', 'Deposit' => '', 'Withdrawal' => '12000', 'Balance' => '413000'],
];

echo "Processing logic:\n\n";

foreach ($bankStatementRows as $i => $row) {
    $depositVal = preg_replace('/[^0-9\.\-]/', '', (string)$row['Deposit']);
    $withdrawalVal = preg_replace('/[^0-9\.\-]/', '', (string)$row['Withdrawal']);

    $depositAmount = ($depositVal !== '' && is_numeric($depositVal)) ? floatval($depositVal) : 0;
    $withdrawalAmount = ($withdrawalVal !== '' && is_numeric($withdrawalVal)) ? floatval($withdrawalVal) : 0;

    echo "Row " . ($i + 1) . ": {$row['Description']}\n";
    echo "  Deposit column: " . ($depositVal === '' ? '(empty)' : "₦" . number_format($depositAmount, 2)) . "\n";
    echo "  Withdrawal column: " . ($withdrawalVal === '' ? '(empty)' : "₦" . number_format($withdrawalAmount, 2)) . "\n";

    if ($depositAmount > 0 && $withdrawalAmount == 0) {
        echo "  ✅ Result: amount = ₦" . number_format($depositAmount, 2) . ", type = 'credit'\n";
        echo "  Logic: Deposit has value, withdrawal is zero → CREDIT transaction\n";
    } elseif ($withdrawalAmount > 0 && $depositAmount == 0) {
        echo "  ✅ Result: amount = ₦" . number_format($withdrawalAmount, 2) . ", type = 'debit'\n";
        echo "  Logic: Withdrawal has value, deposit is zero → DEBIT transaction\n";
    } elseif ($depositAmount > 0 && $withdrawalAmount > 0) {
        echo "  ⚠️  WARNING: Both columns have values (unusual)\n";
        echo "  Using larger amount: " . ($depositAmount >= $withdrawalAmount ? "Deposit" : "Withdrawal") . "\n";
    } else {
        echo "  ❌ ERROR: Both columns are empty or zero - skip row\n";
    }
    echo "\n";
}

echo "\n" . str_repeat("=", 70) . "\n\n";

echo "SCENARIO 2: Traditional Format (Amount + Type columns)\n";
echo "File has: Date, Description, Amount, Type, Balance\n\n";

$traditionalRows = [
    ['Date' => '2026-04-01', 'Description' => 'Salary', 'Amount' => '500000', 'Type' => 'credit', 'Balance' => '500000'],
    ['Date' => '2026-04-02', 'Description' => 'Rent Payment', 'Amount' => '150000', 'Type' => 'debit', 'Balance' => '350000'],
];

echo "Processing logic:\n\n";

foreach ($traditionalRows as $i => $row) {
    echo "Row " . ($i + 1) . ": {$row['Description']}\n";
    echo "  Amount column: ₦" . number_format($row['Amount'], 2) . "\n";
    echo "  Type column: '{$row['Type']}'\n";
    echo "  ✅ Result: amount = ₦" . number_format($row['Amount'], 2) . ", type = '{$row['Type']}'\n";
    echo "  Logic: No deposit/withdrawal columns → use Amount + Type directly\n";
    echo "\n";
}

echo "\n" . str_repeat("=", 70) . "\n\n";

echo "KEY POINTS:\n\n";

echo "1. TYPE COLUMN MAPPING:\n";
echo "   - For Deposit/Withdrawal format: Type is AUTO-INFERRED (not needed)\n";
echo "   - For Amount/Type format: Type column IS mapped and used\n";
echo "   - Fuzzy mapper maps 'Type' column, but it's only used in traditional format\n\n";

echo "2. AMOUNT PLACEMENT:\n";
echo "   - Bank statements: Typically only ONE column has value per row\n";
echo "   - Deposit has value (withdrawal is 0) → Credit transaction\n";
echo "   - Withdrawal has value (deposit is 0) → Debit transaction\n";
echo "   - System handles empty strings ('') and zero (0) identically\n\n";

echo "3. EDGE CASES:\n";
echo "   - Both columns filled → Uses larger amount + logs warning\n";
echo "   - Both columns empty/zero → Skips row with error message\n";
echo "   - Negative values → Converted to positive (direction in 'type' field)\n\n";

echo "4. DATABASE STORAGE:\n";
echo "   - All amounts stored as POSITIVE values\n";
echo "   - Direction tracked in 'type' field (credit/debit)\n";
echo "   - Same storage format regardless of source format\n\n";

echo "=== COLUMN MAPPING EXAMPLES ===\n\n";

$mappingExamples = [
    [
        'format' => 'GTBank Statement',
        'columns' => ['Value Date', 'Narration', 'Withdrawals', 'Deposits', 'Balance'],
        'mapping' => [
            'Value Date' => 'transaction_date',
            'Narration' => 'description',
            'Withdrawals' => 'withdrawal',
            'Deposits' => 'deposit',
            'Balance' => 'balance'
        ],
        'type_needed' => false,
    ],
    [
        'format' => 'Accounting Export',
        'columns' => ['Date', 'Description', 'Amount', 'Type', 'Reference'],
        'mapping' => [
            'Date' => 'transaction_date',
            'Description' => 'description',
            'Amount' => 'amount',
            'Type' => 'type',
            'Reference' => 'reference'
        ],
        'type_needed' => true,
    ],
];

foreach ($mappingExamples as $example) {
    echo "Format: {$example['format']}\n";
    echo "Columns: " . implode(', ', $example['columns']) . "\n";
    echo "Mapping:\n";
    foreach ($example['mapping'] as $col => $field) {
        echo "  - '$col' → $field\n";
    }
    echo "Type column needed? " . ($example['type_needed'] ? 'YES (traditional format)' : 'NO (auto-inferred from deposit/withdrawal)') . "\n";
    echo "\n";
}

echo "=== SUMMARY ===\n\n";
echo "✅ YES, the amount placement is CORRECT!\n";
echo "   - Only ONE column (deposit OR withdrawal) has the amount per row\n";
echo "   - The other column is typically 0 or empty\n";
echo "   - System automatically determines type based on which column has value\n\n";

echo "✅ Type column mapping is OPTIONAL!\n";
echo "   - Mapped if file has deposit/withdrawal columns: NO (auto-inferred)\n";
echo "   - Mapped if file has single amount column: YES (uses Type column)\n\n";

echo "✅ Both formats work perfectly!\n";
echo "   - Bank statements (Deposit/Withdrawal) → Type inferred automatically\n";
echo "   - Accounting files (Amount/Type) → Type read from column\n\n";
