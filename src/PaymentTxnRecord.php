<?php
namespace GravityLegal\GravityLegalAPI;
use DateTime;


class ManualPaymentStatement {
    public string $description;
}

class PaymentTxnRecord
{
    public Client $client;
    public ?Matter $matter;
    public int $totalAmount;
    public Paylink $paylink;
    public ?BankAccount $bankAccount;
    public Payment $payment;
    public ?string $standingLink;
    public ?string $storedPayment;
    public string $type;
    public DateTime $createdOn;
    public string $id;
    public ?DateTime $updatedOn;
    public ?string $accountCategory;
    public int $additionalAmount;
    public int $additionalAmountRefunded;
    public int $amount;
    public int $amountRefunded;
    public ?string $billingName;
    public ?string $billingZip;
    public ?BinData $binData;
    public ?string $cardBrand;
    public ?string $clientNotes;
    public ?string $internalNotes;
    public ?ManualPaymentStatement $manualPaymentStatement;
    public ?string $maskedAccount;
    public ?string $note;
    public ?string $payerEmail;
    public ?string $paymentMethod;
    public ?string $processor;
    public ?string $referenceTxnId;
    public ?string $requestMessage;
    public ?ResponseMessage $responseMessage;
    public ?DateTime $responseReceivedOn;
    public ?DateTime $settledOn;
    public string $status;
    public ?string $vericheckTransactionId;
    public ?string $vericheckStatus;
}
