# AI Financial Statements - Quick Reference

**🎯 One-Liner**: AI automatically generates Balance Sheet, P&L, and Cash Flow from your transactions.

---

## ✅ What's Automated

### Income Statement (100% Automated)
```
✅ Revenue          - All credit transactions
✅ Cost of Sales    - Raw materials, inventory purchases
✅ Salaries         - Payroll expenses
✅ Rent             - Office/shop rent
✅  Utilities       - Electricity, water, internet
✅ Other Expenses   - All categorized debits
✅ Net Profit       - Automatically calculated
```

### Balance Sheet (Mostly Automated)
```
✅ Cash & Bank            - Latest transaction balance
🤖 Trade Receivables      - AI estimates 15% of revenue
🤖 Inventory              - AI estimates 20% of purchases
✅ Property & Equipment   - Detected from asset purchases
🤖 Trade Payables         - AI estimates 10% of expenses
✅ Loans                  - Receipts minus repayments
✅ Retained Earnings      - Prior profit + current year
⚠️ Share Capital          - Manual (defaults to ₦1M)
```

### Cash Flow (100% Automated)
```
✅ Operating Activities   - From P&L + working capital changes
✅ Investing Activities   - Asset purchases & sales
✅ Financing Activities   - Loans, equity, dividends
✅ Net Cash Change        - Opening → Closing
```

---

## 🚀 How to Use

### 1. Import Transactions
```
Transactions Page → Import CSV/Excel → AI categorizes automatically
```

### 2. Generate Statements
```
Reports → Financial Statements → Select Year → Review
```

### 3. Override Estimates (Optional)
```
If you have exact figures, override AI estimates:
- Receivables (from customer invoices)
- Inventory (from physical count)
- Payables (from supplier statements)
```

### 4. Download PDF
```
Click "Download PDF" → Auto-saves snapshot for next year
```

---

## 🤖 New Transaction Categories

### Revenue (Credit)
- `LOAN_RECEIVED` - Loan from bank/lender
- `CAPITAL_CONTRIBUTION` - Owner injects capital
- `ASSET_SALE` - Selling equipment/vehicle
- `INTEREST_RECEIVED` - Bank interest

### Expenses (Debit)
- `ASSET_PURCHASE` - Machinery, vehicles, generators
- `EQUIPMENT_PURCHASE` - Computers, furniture
- `INVENTORY_PURCHASE` - Goods for resale
- `RAW_MATERIALS` - Production materials
- `LOAN_REPAYMENT` - Paying back loans
- `INTEREST` - Loan interest payments
- `DIVIDEND` - Paying shareholders

---

## 📊 AI Confidence Levels

| Item | Confidence | How It's Calculated |
|------|-----------|-------------------|
| Cash | ✅ High (100%) | Actual transaction balance |
| Loans | ✅ High (95%) | Detected from descriptions |
| Receivables | 🤖 Medium (75%) | 15% of annual revenue |
| Inventory | 🤖 Medium (75%) | 20% of purchases |
| Payables | 🤖 Medium (70%) | 10% of expenses |
| PPE | ✅ High (90%) | Sum of asset purchases |

---

## 💡 Smart Insights

AI automatically calculates:

### Profitability
- **Net Profit Margin** = Net Profit ÷ Revenue
  - Good: >15% ✅
  - Fair: 5-15% ⚠️
  - Poor: <5% 🔴

### Liquidity
- **Current Ratio** = Current Assets ÷ Current Liabilities
  - Good: >1.5 ✅
  - Fair: 1.0-1.5 ⚠️
  - Poor: <1.0 🔴 (liquidity concerns)

---

## 🔄 Historical Tracking

Every time you download a PDF:
- ✅ Balance sheet snapshot is saved
- ✅ Available for next year's opening balances
- ✅ Year-over-year comparison enabled

**View History:**
```php
$business->financialPositions()
    ->orderBy('position_date', 'desc')
    ->get();
```

---

## ⚠️ Important Notes

### AI Cannot Detect
- Share capital (needs manual input or company docs)
- Intangible assets (patents, trademarks)
- Contingent liabilities
- Off-balance-sheet items

### Override When You Have Exact Data
AI estimates are **useful starting points** but:
- Replace receivables with actual invoice aging report
- Replace inventory with physical stock count
- Replace payables with actual supplier statements

### Best Results
1. ✅ Categorize ALL transactions (better data = better automation)
2. ✅ Use descriptive transaction names (helps AI detection)
3. ✅ Review and correct low-confidence categories
4. ✅ Save snapshots regularly
5. ✅ Get professional review from accountant

---

## 📝 Common Scenarios

### Scenario 1: First Year Business
```
- No opening balances needed
- AI generates from current year transactions only
- Manual input for share capital
- All P&L items automated
```

### Scenario 2: Established Business
```
- Load prior year snapshot
- AI uses prior balances for comparisons
- Working capital changes auto-calculated
- Cash flow fully automated
```

### Scenario 3: No Inventory Business (Services)
```
- Set inventory = 0
- Cost of sales = 0
- Focus on operating expenses
- Simpler balance sheet
```

---

## 🛠️ For Developers

### Generate Statements
```php
use App\Services\EnhancedFinancialAnalysisService;

$service = app(EnhancedFinancialAnalysisService::class);
$statements = $service->generateStatements($business, '2026');

// Returns:
[
    'balance_sheet' => [...],
    'profit_loss' => [...],
    'cash_flow' => [...],
    'ai_insights' => [...]
]
```

### Save Snapshot
```php
use App\Models\FinancialPosition;

$position = FinancialPosition::create([
    'business_id' => $business->id,
    'position_date' => now()->endOfYear(),
    // ... balance sheet items
]);

// Check if balanced
$position->isBalanced(); // true/false
$position->currentRatio(); // 1.5
$position->totalAssets(); // 5000000.00
```

### Retrieve History
```php
// Get last year's position
$prior = $business->financialPositions()
    ->where('position_date', '<', $currentYearEnd)
    ->orderBy('position_date', 'desc')
    ->first();

// Use for opening balances
$openingCash = $prior?->cash_and_bank ?? 0;
```

---

## 📞 Need Help?

**Full Guide**: See `AI_FINANCIAL_STATEMENTS_ENHANCED.md`  
**Support**: support@taxmaster.ng  
**Accountant Review**: Recommended for tax filing

---

**Version**: 2.0 | **Updated**: April 4, 2026
