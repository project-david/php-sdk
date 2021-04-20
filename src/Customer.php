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
class CreateCustomer
{
    public string $name;
    public string $partner;
    public AppData $appData;
    public bool $promptToCreateSPM;
    public NotifPrefs $notifPrefs;
}
class CreateCustomerResult
{
    public Customer $result;
}
class CustomerResultResult
{
    public array $records; // This is a list of Customer
    public int $totalCount;
    public int $pageNo;
    public int $pageSize;
}
class CustomerResult {
    public CustomerResultResult $result;
}

class TxnClient {
    public string $clientName;
    public PrimaryContact $primaryContact;
    public string $id;
}
class PaylinkTxn {
    public Balance $balance;
    public Customer $customer;
    public TxnClient $client;
    public Matter $matter;
    public string $envelope;
    public string $surcharge;
    public string $status;
    public DateTime $latestActivity;
    public int $outstanding;
    public int $paid;
    public string $id;
    public DateTime $createdOn;
    public DateTime $updatedOn;
    public Balance $balanceStatement;
    public DefaultDepositAccounts $defaultDepositAccounts;
    public string $externalId;
    public bool $isProcessing;
    public string $note;
    public array $paymentMethods; //This is a list of strings
    public bool $surchargeEnabled;
    public string $url;
    public string $webhookDetails;
}

class CustomerTxnResultResult
{
    public array $records; // This is a list of PaylinkTxn
    public int $totalCount;
    public int $pageNo;
    public int $pageSize;
}

class CustomerTxnResult {
    public CustomerTxnResultResult $result;
}
