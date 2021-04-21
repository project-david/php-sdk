<?php
namespace GravityLegal\GravityLegalAPI;

class Split {
    public string $id;
    public int $amount;
    public string $bankAccountId;
    public string $bankAccountCat;
    public bool $processed;
    public string $ledgerItemId;
    public string $paymentTxnId;
    public array $additionalAmounts; // Array of AdditionalAmount
}
