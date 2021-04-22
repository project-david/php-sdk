<?php
namespace GravityLegal\GravityLegalAPI;
use DateTime;

/// <summary>
/// The payment request result.
/// </summary>
class PaymentRequestResult
{
    public ?Matter $matter;
    public ?int $trustAmount;
    public ?int $operatingAmount;
    public ?array $paymentMethods; // Array of string
    public ?bool $surchargeEnabled;
    public array $emails; // Array of string
    public ?array $ccEmails; // Array of string
    public ?array $bccEmails; // Array of string
    public string $subject;
    public string $message;
    public string $description;
    public ?AdditionalData $additionalData;
    public string $id;
    public DateTime $createdOn;
    public ?DateTime $updatedOn;
    public DateTime $latestActivity;
    public Paylink $paylink;
}

class PaymentRequestResponse
{
    public PaymentRequestResult $result;
}
