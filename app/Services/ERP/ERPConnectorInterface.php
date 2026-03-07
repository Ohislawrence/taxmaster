<?php

namespace App\Services\ERP;

interface ERPConnectorInterface
{
    public function syncCustomers();
    public function syncInvoices();
    public function syncPayments();
}
