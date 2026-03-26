<?php

namespace App\Services\EInvoice;

/**
 * UBL 3.0 Invoice Data Model (partial, for extension)
 * Add all 55 mandatory fields as per NRS/FIRS UBL 3.0 schema
 */
class UBLInvoice
{
    // Example required fields (expand to full 55 as needed)
    public string $invoiceNumber;
    public string $issueDate;
    public string $sellerName;
    public string $sellerTIN;
    public string $buyerName;
    public string $buyerTIN;
    public float $totalAmount;
    public float $vatAmount;
    public string $currency;
    public string $irn; // Invoice Reference Number
    public string $signature; // Digital signature (JAdES)
    /**
     * Seller structured data (name, tin, registration, address, contact)
     * Kept as array to allow extension to full NRS schema
     * @var array
     */
    public array $seller;
    // ... add all other mandatory UBL 3.0 fields

    public function __construct(array $data)
    {
        $this->invoiceNumber = $data['invoiceNumber'] ?? '';
        $this->issueDate = $data['issueDate'] ?? '';
        $this->sellerName = $data['sellerName'] ?? '';
        $this->sellerTIN = $data['sellerTIN'] ?? '';
        $this->buyerName = $data['buyerName'] ?? '';
        $this->buyerTIN = $data['buyerTIN'] ?? '';
        $this->totalAmount = $data['totalAmount'] ?? 0.0;
        $this->vatAmount = $data['vatAmount'] ?? 0.0;
        $this->currency = $data['currency'] ?? 'NGN';
        $this->irn = $data['irn'] ?? '';
        $this->signature = $data['signature'] ?? '';
        $this->seller = $data['seller'] ?? [
            'name' => $this->sellerName,
            'tin' => $this->sellerTIN,
            'registrationNumber' => $data['sellerRegistrationNumber'] ?? ($data['registrationNumber'] ?? ''),
            'address' => [
                'street' => $data['sellerAddressLine'] ?? ($data['address'] ?? ''),
                'city' => $data['sellerCity'] ?? ($data['city'] ?? ''),
                'state' => $data['sellerState'] ?? ($data['state'] ?? ''),
                'country' => $data['sellerCountry'] ?? ($data['country'] ?? ''),
                'postalCode' => $data['sellerPostalCode'] ?? '',
            ],
            'email' => $data['sellerEmail'] ?? ($data['email'] ?? ''),
            'phone' => $data['sellerPhone'] ?? ($data['phone'] ?? ''),
        ];
        // ... assign all other fields
    }

    public function toArray(): array
    {
        return [
            'invoiceNumber' => $this->invoiceNumber,
            'issueDate' => $this->issueDate,
            'sellerName' => $this->sellerName,
            'sellerTIN' => $this->sellerTIN,
            'seller' => $this->seller,
            'buyerName' => $this->buyerName,
            'buyerTIN' => $this->buyerTIN,
            'totalAmount' => $this->totalAmount,
            'vatAmount' => $this->vatAmount,
            'currency' => $this->currency,
            'irn' => $this->irn,
            'signature' => $this->signature,
            // ... all other fields
        ];
    }
}
