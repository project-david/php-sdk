<?php
namespace GravityLegal\GravityLegalAPI;
use DateTime;

class NotifPrefs
{
    public array $emails; // This is a list of emails
    public bool $paymentReceipts;
    public bool $ACHReturns;
    public bool $invoiceNotice;
}
class EmailTemplate {
        public string $subject;
        public string $body;
}

class EnvelopeSettings {
    public bool $combineMatters;
    public bool $combineOperatingAndTrust;
    public EmailTemplate $emailTemplate;
}
class Partner
{
    public string $id;
    public DateTime $createdOn;
    public DateTime $updatedOn;
    public bool $deleted;
    public string $appId;
    public string $title;
    public ?AppData $appData;
    public string $webhookSecret;
    public string $webhookUrl;
}
class Customer
{
    public Partner $partner;
    public string $appId;
    public string $id;
    public DateTime $createdOn;
    public ?DateTime $updatedOn;
    public bool $deleted;
    public bool $achActivated;
    public bool $achEnabled;
    public string $achProcessor;
    public bool $agreementSentDirectly;
    public ?AppData $appData;
    public ?Balance $balanceStatement;
    public bool $ccActivated;
    public bool $ccEnabled;
    public string $ccProcessor;
    public bool $creditActivated;
    public bool $creditEnabled;
    public bool $debitActivated;
    public bool $debitEnabled;
    public bool $checkEnabled;
    public ?DefaultDepositAccounts $defaultDepositAccounts;
    public ?EnvelopeSettings $envelopeSettings;
    public ?string $externalId;
    public DateTime $latestActivity;
    public string $name;
    public ?NotifPrefs $notifPrefs;
    public string $orgId;
    public bool $promptToCreateSPM;
    public ?string $signatureMail;
    public bool $surchargeActivated;
    public bool $surchargeEnabled;
    public ?string $surchargeRate;
    public bool $sysGen;
    public string $timezone;
    public ?string $webhookSecret;
    public ?string $webhookUrl;
}
