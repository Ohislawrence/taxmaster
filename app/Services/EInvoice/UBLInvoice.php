<?php

namespace App\Services\EInvoice;

/**
 * FIRS-Compliant UBL 2.1 Invoice Data Model
 * Implements all mandatory fields as per FIRS e-invoicing requirements
 *
 * Reference: FIRS e-Invoicing Technical Specifications
 */
class UBLInvoice
{
    // Core Invoice Identifiers
    public string $invoiceNumber;           // Unique invoice number
    public string $issueDate;               // Invoice issue date (YYYY-MM-DD)
    public ?string $dueDate;                // Payment due date
    public string $invoiceTypeCode;         // 380 (Standard), 381 (Credit Note), 383 (Debit Note)
    public string $currency;                // Currency code (NGN)
    public string $irn;                     // Invoice Reference Number (FIRS-generated)

    // Document References
    public ?string $purchaseOrderReference; // PO reference if applicable
    public ?string $contractReference;      // Contract reference if applicable

    // Seller (Supplier) Information
    public array $seller;                   // Complete seller details
    public string $sellerName;              // Cached for convenience
    public string $sellerTIN;               // Cached for convenience

    // Buyer (Customer) Information
    public array $buyer;                    // Complete buyer details
    public string $buyerName;               // Cached for convenience
    public string $buyerTIN;                // Cached for convenience

    // Payment Information
    public array $paymentMeans;             // Payment method details
    public ?string $paymentTerms;           // Payment terms description

    // Tax Information
    public array $taxTotal;                 // Tax breakdown
    public float $vatAmount;                // Total VAT amount
    public float $vatRate;                  // VAT rate percentage

    // Monetary Totals
    public float $lineExtensionAmount;      // Sum of line amounts (before tax)
    public float $taxExclusiveAmount;       // Amount excluding tax
    public float $taxInclusiveAmount;       // Amount including tax
    public float $totalAmount;              // Payable amount
    public float $prepaidAmount;            // Amount already paid
    public float $payableAmount;            // Amount due

    // Invoice Lines (Items)
    public array $invoiceLines;             // Array of invoice line items

    // Digital Signature (JAdES)
    public string $signature;               // Digital signature

    // Additional FIRS-specific fields
    public ?string $notes;                  // Additional notes
    public ?string $accountingCost;         // Cost center or accounting code

    public function __construct(array $data)
    {
        // Core identifiers
        $this->invoiceNumber = $data['invoiceNumber'] ?? '';
        $this->issueDate = $data['issueDate'] ?? date('Y-m-d');
        $this->dueDate = $data['dueDate'] ?? null;
        $this->invoiceTypeCode = $data['invoiceTypeCode'] ?? '380'; // 380 = Standard invoice
        $this->currency = $data['currency'] ?? 'NGN';
        $this->irn = $data['irn'] ?? '';

        // Document references
        $this->purchaseOrderReference = $data['purchaseOrderReference'] ?? null;
        $this->contractReference = $data['contractReference'] ?? null;

        // Seller information (structured)
        $this->sellerName = $data['sellerName'] ?? '';
        $this->sellerTIN = $data['sellerTIN'] ?? '';
        $this->seller = $data['seller'] ?? [
            'name' => $this->sellerName,
            'tin' => $this->sellerTIN,
            'registrationNumber' => $data['sellerRegistrationNumber'] ?? '',
            'address' => [
                'streetName' => $data['sellerStreet'] ?? '',
                'cityName' => $data['sellerCity'] ?? '',
                'countrySubentity' => $data['sellerState'] ?? '',
                'postalZone' => $data['sellerPostalCode'] ?? '',
                'country' => $data['sellerCountry'] ?? 'NG',
            ],
            'contact' => [
                'telephone' => $data['sellerPhone'] ?? '',
                'electronicMail' => $data['sellerEmail'] ?? '',
            ],
            'legalEntity' => [
                'registrationName' => $this->sellerName,
                'companyID' => $data['sellerRegistrationNumber'] ?? '',
            ],
        ];

        // Buyer information (structured)
        $this->buyerName = $data['buyerName'] ?? '';
        $this->buyerTIN = $data['buyerTIN'] ?? '';
        $this->buyer = $data['buyer'] ?? [
            'name' => $this->buyerName,
            'tin' => $this->buyerTIN,
            'registrationNumber' => $data['buyerRegistrationNumber'] ?? '',
            'address' => [
                'streetName' => $data['buyerStreet'] ?? '',
                'cityName' => $data['buyerCity'] ?? '',
                'countrySubentity' => $data['buyerState'] ?? '',
                'postalZone' => $data['buyerPostalCode'] ?? '',
                'country' => $data['buyerCountry'] ?? 'NG',
            ],
            'contact' => [
                'telephone' => $data['buyerPhone'] ?? '',
                'electronicMail' => $data['buyerEmail'] ?? '',
            ],
        ];

        // Payment information
        $this->paymentMeans = $data['paymentMeans'] ?? [
            'paymentMeansCode' => '30', // 30 = Credit transfer
            'paymentChannelCode' => $data['paymentChannelCode'] ?? null,
            'instructionNote' => $data['paymentInstructionNote'] ?? null,
            'payeeFinancialAccount' => [
                'id' => $data['bankAccountNumber'] ?? '',
                'name' => $data['bankAccountName'] ?? '',
                'financialInstitutionBranch' => [
                    'id' => $data['bankCode'] ?? '',
                    'name' => $data['bankName'] ?? '',
                ],
            ],
        ];
        $this->paymentTerms = $data['paymentTerms'] ?? null;

        // Tax information
        $this->vatAmount = $data['vatAmount'] ?? 0.0;
        $this->vatRate = $data['vatRate'] ?? 7.5; // Standard VAT rate in Nigeria
        $this->taxTotal = $data['taxTotal'] ?? [
            'taxAmount' => $this->vatAmount,
            'taxSubtotal' => [
                [
                    'taxableAmount' => $data['taxableAmount'] ?? $data['lineExtensionAmount'] ?? 0.0,
                    'taxAmount' => $this->vatAmount,
                    'taxCategory' => [
                        'id' => 'S', // Standard rate
                        'percent' => $this->vatRate,
                        'taxScheme' => [
                            'id' => 'VAT',
                            'name' => 'Value Added Tax',
                        ],
                    ],
                ],
            ],
        ];

        // Monetary totals
        $this->lineExtensionAmount = $data['lineExtensionAmount'] ?? $data['subtotal'] ?? 0.0;
        $this->taxExclusiveAmount = $data['taxExclusiveAmount'] ?? $this->lineExtensionAmount;
        $this->taxInclusiveAmount = $data['taxInclusiveAmount'] ?? ($this->taxExclusiveAmount + $this->vatAmount);
        $this->totalAmount = $data['totalAmount'] ?? $this->taxInclusiveAmount;
        $this->prepaidAmount = $data['prepaidAmount'] ?? 0.0;
        $this->payableAmount = $data['payableAmount'] ?? ($this->totalAmount - $this->prepaidAmount);

        // Invoice lines
        $this->invoiceLines = $data['invoiceLines'] ?? [];

        // Digital signature
        $this->signature = $data['signature'] ?? '';

        // Additional fields
        $this->notes = $data['notes'] ?? null;
        $this->accountingCost = $data['accountingCost'] ?? null;
    }

    /**
     * Convert invoice to FIRS-compliant array format
     */
    public function toArray(): array
    {
        return [
            // UBL Header
            'ublVersionID' => '2.1',
            'customizationID' => 'urn:firs.gov.ng:einvoice:ver1.1',

            // Invoice Identification
            'id' => $this->invoiceNumber,
            'issueDate' => $this->issueDate,
            'dueDate' => $this->dueDate,
            'invoiceTypeCode' => $this->invoiceTypeCode,
            'documentCurrencyCode' => $this->currency,

            // Invoice Reference Number (FIRS)
            'irn' => $this->irn,

            // Document References
            'orderReference' => $this->purchaseOrderReference ? [
                'id' => $this->purchaseOrderReference,
            ] : null,
            'contractReference' => $this->contractReference ? [
                'id' => $this->contractReference,
            ] : null,

            // Notes
            'note' => $this->notes,

            // Supplier Party (Seller)
            'accountingSupplierParty' => [
                'party' => [
                    'partyIdentification' => [
                        ['id' => $this->seller['tin'] ?? ''],
                    ],
                    'partyName' => [
                        ['name' => $this->seller['name'] ?? ''],
                    ],
                    'postalAddress' => $this->seller['address'] ?? [],
                    'contact' => $this->seller['contact'] ?? [
                        'telephone' => '',
                        'electronicMail' => '',
                    ],
                    'partyLegalEntity' => [
                        $this->seller['legalEntity'] ?? [
                            'registrationName' => $this->seller['name'] ?? '',
                            'companyID' => $this->seller['registrationNumber'] ?? '',
                        ],
                    ],
                ],
            ],

            // Customer Party (Buyer)
            'accountingCustomerParty' => [
                'party' => [
                    'partyIdentification' => [
                        ['id' => $this->buyer['tin'] ?? ''],
                    ],
                    'partyName' => [
                        ['name' => $this->buyer['name'] ?? ''],
                    ],
                    'postalAddress' => $this->buyer['address'] ?? [],
                    'contact' => $this->buyer['contact'] ?? [
                        'telephone' => '',
                        'electronicMail' => '',
                    ],
                ],
            ],

            // Payment Means
            'paymentMeans' => [$this->paymentMeans],

            // Payment Terms
            'paymentTerms' => $this->paymentTerms ? [
                'note' => $this->paymentTerms,
            ] : null,

            // Tax Total
            'taxTotal' => [$this->taxTotal],

            // Legal Monetary Total
            'legalMonetaryTotal' => [
                'lineExtensionAmount' => number_format($this->lineExtensionAmount, 2, '.', ''),
                'taxExclusiveAmount' => number_format($this->taxExclusiveAmount, 2, '.', ''),
                'taxInclusiveAmount' => number_format($this->taxInclusiveAmount, 2, '.', ''),
                'prepaidAmount' => number_format($this->prepaidAmount, 2, '.', ''),
                'payableAmount' => number_format($this->payableAmount, 2, '.', ''),
            ],

            // Invoice Lines
            'invoiceLine' => $this->invoiceLines,

            // Digital Signature (JAdES)
            'signature' => $this->signature,

            // Accounting Cost Center
            'accountingCost' => $this->accountingCost,
        ];
    }

    /**
     * Validate invoice data completeness
     */
    public function validate(): array
    {
        $errors = [];

        if (empty($this->invoiceNumber)) {
            $errors[] = 'Invoice number is required';
        }

        if (empty($this->issueDate)) {
            $errors[] = 'Issue date is required';
        }

        if (empty($this->sellerTIN)) {
            $errors[] = 'Seller TIN is required';
        }

        if (empty($this->sellerName)) {
            $errors[] = 'Seller name is required';
        }

        if (empty($this->buyerName)) {
            $errors[] = 'Buyer name is required';
        }

        if ($this->totalAmount <= 0) {
            $errors[] = 'Total amount must be greater than zero';
        }

        if (empty($this->invoiceLines)) {
            $errors[] = 'At least one invoice line is required';
        }

        return $errors;
    }
}
