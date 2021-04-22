<?php
namespace GravityLegal\GravityLegalAPI;

class PaymentRequest
    {
        public string $customer;
        public string $client;
        public ?string $matter;
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

    }