<?php
namespace GravityLegal\GravityLegalAPI\Tests;

use GravityLegal\GravityLegalAPI\GravityLegalService;
use PHPUnit\Framework\TestCase;
use DateTime;
use PHPUnit\Framework\Constraint\Count;
use Faker\Factory;
use GravityLegal\GravityLegalAPI\CreateClient;
use GravityLegal\GravityLegalAPI\Client;
use GravityLegal\GravityLegalAPI\Customer;
use GravityLegal\GravityLegalAPI\Operating;
use JsonMapper;
use GravityLegal\GravityLegalAPI\Utility;
use GravityLegal\GravityLegalAPI\CreatePaylink;
use GravityLegal\GravityLegalAPI\Trust;
use GravityLegal\GravityLegalAPI\CreateMatter;
use GravityLegal\GravityLegalAPI\CustomerApiToken;
use GravityLegal\GravityLegalAPI\Paylink;
use GravityLegal\GravityLegalAPI\Statement;
use GravityLegal\GravityLegalAPI\ManualPayment;
use GravityLegal\GravityLegalAPI\DefaultDepositAccounts;
use GravityLegal\GravityLegalAPI\PaymentRequest;
use GravityLegal\GravityLegalAPI\CreateUser;
use GravityLegal\GravityLegalAPI\CreateContact;
use GravityLegal\GravityLegalAPI\Currency;
use GravityLegal\GravityLegalAPI\AppData;
use GravityLegal\GravityLegalAPI\NotifPrefs;
use GravityLegal\GravityLegalAPI\CreateCustomer;

class GravityLegalAPITest extends TestCase
{
    public string $PRAHARI_BASE_URL='https://api.sandbox.gravity-legal.com/prahari/v1';
    public string $ENV_URL='https://api.sandbox.gravity-legal.com/pd/v1';
    public string $SYSTEM_TOKEN='eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzaWQiOiJzYW5kYm94Iiwic3RpZCI6ImYzMDAyNmFmLTMxMjAtNDdmYy05MmRmLWFlNmMyZmEwMThiNSIsInRva2VuX3VzZSI6InN5c3RlbV90b2siLCJybmQiOjQ2NjQyNTEzMjEsImlhdCI6MTYwODY2NjI1MH0.-gtUMBkeIhdLilUJWiCgHWfROgCtWEcx_1gLlNWmVmo';
    public string $APP_ID='soluno';
    public string $ORG_ID='781ac60e-cff5-4971-8053-cddf5eec696f';
    public array $API_KEY;
    public GravityLegalService $gService;

    public function setUp(): void {
        $this->API_KEY = array ('PRAHARI_BASE_URL' => $this->PRAHARI_BASE_URL, 
                            'ENV_URL' => $this->ENV_URL, 
                            'SYSTEM_TOKEN' => $this->SYSTEM_TOKEN, 
                            'APP_ID' => $this->APP_ID, 
                            'ORG_ID' => $this->ORG_ID );
        $this->gService = new GravityLegalService($this->API_KEY);
    }

    /** @test */
    public function IsOnlineTest() {
        $this->assertTrue($this->gService->IsOnline());
    }

    /** @test */
    public function CreateNewPaylinkTest()
    {
        $paylinkInfo = $this->gService->CreateNewPaylink("bf193ce3-f54f-40d4-b3e6-da5de83be0be",
            "896d9358-a48d-45ec-946b-6a0357f10afa", 100.00, 100.00, ["ACH", "CREDIT"],
            "This is a test", "23c47c88-a287-4962-bf92-6ff30798377c", Utility::GUIDv4());
        $this->assertTrue($paylinkInfo != null);
        $this->assertTrue($paylinkInfo->url != null);
    }

    /** @test */
    public function CreateNewPaylinksTest()
    {
        $createPaylinkList = array();
        $createPaylink = new CreatePaylink();
        $createPaylink->customer = "bf193ce3-f54f-40d4-b3e6-da5de83be0be";
        $createPaylink->client = "896d9358-a48d-45ec-946b-6a0357f10afa";
        $createPaylink->matter = "23c47c88-a287-4962-bf92-6ff30798377c";
        $createPaylink->externalId = Utility::GUIDv4();
        $createPaylink->operating = new Operating();
        $createPaylink->operating->amount = 10000;
        $createPaylink->trust = new Trust();
        $createPaylink->trust->amount = 20000;
        $createPaylink->paymentMethods = ["ACH", "CREDIT"];
        $createPaylink->memo = "This is a memo";
        $createPaylink->defaultDepositAccounts = new DefaultDepositAccounts();
        $createPaylink->defaultDepositAccounts->operating = "a924b520-5cd5-4344-abb6-0da003fb7305";
        $createPaylink->defaultDepositAccounts->trust = "98965737-a1c4-4a08-821d-438538fac683";
        $createPaylinkList[] = $createPaylink;
        $paylinkCreationResults = $this->gService->CreateNewPaylinks($createPaylinkList);
        $this->assertTrue(count($paylinkCreationResults->FailedRequests) == 0);
        $this->assertTrue(count($paylinkCreationResults->CreatedEntities) == count($createPaylinkList));
    }

    /** @test */
    public function FindOrCreateCustomerTest()
    {
        $createCustomer = new CreateCustomer();
        $createCustomer->name = "Potomac Fun House";
        $currency = new Currency();
        $currency->value = 'USD';
        $currency->label = 'USD($)';
        $email =  "manoj.srivastava+potomacfunhouse@gmail.com";
        $appData = new AppData();
        $appData->currency = $currency;
        $notifPrefs = new NotifPrefs();
        $notifPrefs->ACHReturn = false;
        $notifPrefs->invoiceNotice = false;
        $notifPrefs->paymentReceipts = false;
        $notifPrefs->emails = [$email,];
        $createCustomer->appData = $appData;
        $createCustomer->notifPrefs = $notifPrefs;
        $createCustomer->partner = "eaea505e-900b-407c-9ae5-9377ce6c3e6a";
        $createCustomer->promptToCreateSPM = false;
        $cust = $this->gService->FindOrCreateCustomer($createCustomer);
        $this->assertTrue($cust != null);
        $this->assertTrue($cust->name == 'Potomac Fun House');
    }

    /** @test */
    public function CreateNewCustomersTest()
    {
        $customerResult = $this->gService->FetchCustomers();
        $initialCustomerCount = count($customerResult->FetchedEntities);
        $createCustomerList = $this->GenerateCreateCustomers(1, 'eaea505e-900b-407c-9ae5-9377ce6c3e6a');
        $customerCreationResult = $this->gService->CreateNewCustomers($createCustomerList);
        $this->assertTrue(count($customerCreationResult->FailedRequests) == 0);
        $customerResult = $this->gService->FetchCustomers();
        $this->assertTrue(count($customerResult->FetchedEntities) == $initialCustomerCount + 1);
    }

    /** @test */
    public function FindCustomerTest()
    {
        $customers = $this->gService->FindCustomer("Inc");
        $this->assertTrue($customers != null);
        $this->assertTrue(count($customers) > 0);
    }

    /** @test */
    public function MakeContactPrimaryTest()
    {
        $createConactList = $this->GenerateCreateContacts(1, "896d9358-a48d-45ec-946b-6a0357f10afa");
        $customerApiToken = $this->gService->GetCustomerApiToken("bf193ce3-f54f-40d4-b3e6-da5de83be0be", "ThisIsATestToken");
        $gs = new GravityLegalService((array)$customerApiToken);
        $contact = $gs->CreateNewContact($createConactList[0]);
        $this->assertTrue($contact != null);
        $result = $gs->MakeContactPrimary($contact);
        $this->assertTrue($result);
        $contact = $gs->GetContact($contact->id);
        $this->assertTrue($contact->isPrimaryContact);

        $entityQueryResult = $gs->FetchContacts($contact->client->id);
        foreach ($entityQueryResult->FetchedEntities as $con)
        {
            if ($con->id == $contact->id)
                continue;
            else
            {
                $result = $gs->MakeContactPrimary($con);
                $this->assertTrue($result);
                $result = $gs->DeleteContact($contact);
                $this->assertTrue($result);
                break;
            }
        }

    }

    /** @test */
    public function CreateNewContactTest()
    {
        $createConactList = $this->GenerateCreateContacts(1, "896d9358-a48d-45ec-946b-6a0357f10afa");
        $customerApiToken = $this->gService->GetCustomerApiToken("bf193ce3-f54f-40d4-b3e6-da5de83be0be", "ThisIsATestToken");
        $gs = new GravityLegalService((array)$customerApiToken);
        $contact = $gs->CreateNewContact($createConactList[0]);
        $this->assertTrue($contact != null);
        $result = $gs->DeleteContact($contact);
        $this->assertTrue($result);
    }

    /** @test */
    public function FetchContactsTest()
    {
        $contactResult = $this->gService->FetchContacts();
        $this->assertTrue(count($contactResult->FetchedEntities) > 0);
    }

    /** @test */
    public function GetMatterTest()
    {
        $matter = $this->gService->GetMatter("23c47c88-a287-4962-bf92-6ff30798377c");
        $this->assertTrue($matter != null);
        $this->assertTrue($matter->name == "New invention patent application");
    }

    /** @test */
    public function CreateNewUsersTest() {
        $invitedUsers = $this->gService->GetInvitedUsers();
        $initialInvitedUserCount = count($invitedUsers);
        $customer = new Customer();
        $customer->id = "bf193ce3-f54f-40d4-b3e6-da5de83be0be";
        $customer->orgId = "38cb2803-d197-4d2f-ba81-6e6d6bf12373";
        $customer->name = "Manoj's Firm";
        $createUsers = $this->GenerateCreateUsers(1, $customer);
        $userCreationResult = $this->gService->CreateNewUsers($createUsers);
        $this->assertTrue(count($userCreationResult->FailedRequests) == 0);
        $invitedUsers = $this->gService->GetInvitedUsers();
        $this->assertTrue(count($invitedUsers) == $initialInvitedUserCount + 1);
    }

    /** @test */
    public function FetchMattersTest()    {
        $matterResult = $this->gService->FetchMatters();
        $this->assertTrue(count($matterResult->FetchedEntities) > 0);
    }

    /** @test */
    public function FetchBankAccountsTest() {
        $entityQueryResult = $this->gService->FetchBankAccounts("7a72244e-4586-421d-85e5-a6a2d38188f0");
        $this->assertTrue(count($entityQueryResult->FetchedEntities) == 6);
    }


    /** @test */
    public function GetBankAccountTest() {
        $bankAccount = $this->gService->GetBankAccount("a924b520-5cd5-4344-abb6-0da003fb7305");
        $this->assertTrue($bankAccount != null);
    }

    /** @test */
    public function CreatePaymentRequestsTest() {
        $paymentRequest = new PaymentRequest();
        $paymentRequest->customer = "bf193ce3-f54f-40d4-b3e6-da5de83be0be";
        $paymentRequest->client = "896d9358-a48d-45ec-946b-6a0357f10afa";
        $paymentRequest->matter = "23c47c88-a287-4962-bf92-6ff30798377c";
        $paymentRequest->operatingAmount = 10000;
        $paymentRequest->trustAmount = 20000;
        $paymentRequest->emails = ["manoj.srivastava+unittest@gmail.com", ];
        $paymentRequest->description = "This is the description";
        $paymentRequest->message = "This is the message";
        $paymentRequest->subject = "This is the subject";
        $paymentRequest->surchargeEnabled = true;
        $paymentRequest->paymentMethods = ["CREDIT", "DEBIT", "ACH" ];

        $paymentRequest1 = new PaymentRequest();
        $paymentRequest1->customer = "bf193ce3-f54f-40d4-b3e6-da5de83be0be";
        $paymentRequest1->client = "896d9358-a48d-45ec-946b-6a0357f10afa";
        $paymentRequest1->matter = "23c47c88-a287-4962-bf92-6ff30798377c";
        $paymentRequest1->operatingAmount = 1000;
        $paymentRequest1->trustAmount = 2000;
        $paymentRequest1->emails = ["manoj.srivastava+unittest2@gmail.com", ];
        $paymentRequest1->description = "This is the description";
        $paymentRequest1->message = "This is the message";
        $paymentRequest1->subject = "This is the subject";
        $paymentRequest1->surchargeEnabled = true;
        $paymentRequest1->paymentMethods = ["CREDIT", "ACH" ];

        $paymentRequests = array();
        $paymentRequests[] = $paymentRequest;
        $paymentRequests[] = $paymentRequest1;

        $entityCreationResult = $this->gService->CreatePaymentRequests($paymentRequests);
        $this->assertTrue(count($entityCreationResult->CreatedEntities) == 2);
    }

    /** @test */
    public function CreateNewPaylinkWithIndividualParametersTest() {
        $defaultDepositAccounts = new DefaultDepositAccounts();
        $defaultDepositAccounts->operating = "a924b520-5cd5-4344-abb6-0da003fb7305";
        $defaultDepositAccounts->trust = "98965737-a1c4-4a08-821d-438538fac683";
        $paylinkInfo = $this->gService->CreateNewPaylinkWithDefaultDepositAccounts("bf193ce3-f54f-40d4-b3e6-da5de83be0be", "896d9358-a48d-45ec-946b-6a0357f10afa", 110.15, 201.75, $defaultDepositAccounts);
        $this->assertTrue($paylinkInfo != null);
    }

    /** @test */
    public function InitiateRefundForPaymentTxnTest()
    {
        $result = $this->gService->InitiateRefundForPaymentTxn("2becab6e-5b4d-47a9-9059-d857a540aaeb", 2.00, 0.05, "manoj.srivastava+initialRefund@gmail.com");
        $this->assertTrue($result);
    }

    /** @test */
    public function MakeManualPaymentTest()
    {
        $paylinkId = "87f6dd2e-d8b4-41dd-a609-3e36e414dce2";
        $manualPayment = new ManualPayment();
        $operating = new Operating();
        $operating->amountInCents = 300;
        $operating->bankAccountId = '5d846758-82c0-42ca-b0fe-bb163dd5386c';
        $manualPayment->operating = $operating;
        $manualPayment->paidBy = 'Manoj Srivastava';
        $manualPayment->payerEmail = 'manoj.srivastava+manualpaymentunittest@gmail.com';
        $manualPayment->sendReceiptEmail = true;
        $statement = new Statement();
        $statement->description = 'This is a manual payment for $3 to ops and $7 to trust.';
        $manualPayment->statement = $statement;
        $trust = new Trust();
        $trust->amountInCents = 700;
        $trust->bankAccountId = 'c2e7f2ef-d69e-4927-a48b-0a533f20abc4';
        $manualPayment->trust = $trust;
        $result = $this->gService->MakeManualPayment($paylinkId, $manualPayment);
        $this->assertTrue($result);
    }

    /** @test */
    public function MakeManualPaymentIndividualParamTest()
    {
        $result = $this->gService->MakeManualPaymentWithIndividualParams("87f6dd2e-d8b4-41dd-a609-3e36e414dce2", 3.00, 7.00, "Manual Payer",
            "manoj.srivastava+manualpaymentunittest@gmail.com", true, "This is a manual payment for $3 to ops and $7 to trust.");
        $this->assertTrue($result);
        $result = $this->gService->MakeManualPaymentWithIndividualParams("87f6dd2e-d8b4-41dd-a609-3e36e414dce2", null, 10.00, "Null Operating",
            "manoj.srivastava+manualpaymentunittest@gmail.com", true, "This is a manual payment for $10 to trust.");
        $this->assertTrue($result);
        $result = $this->gService->MakeManualPaymentWithIndividualParams("87f6dd2e-d8b4-41dd-a609-3e36e414dce2", 10.00, null, "Null Trust",
            "manoj.srivastava+manualpaymentunittest@gmail.com", true, "This is a manual payment for $10 to opeerating.");
        $this->assertTrue($result);
    }

    /** @test */
    public function GetPaymentByIdTest() {
        $payment = $this->gService->GetPaymentById('71dca067-c1be-423d-a3a3-dba2d9dcc3cd');
        $this->assertTrue($payment != null );
    }

    /** @test */
    public function TrustToOperatingTransferTest() {
        $restResponse = $this->gService->TrustToOperatingTransfer('bf193ce3-f54f-40d4-b3e6-da5de83be0be', '896d9358-a48d-45ec-946b-6a0357f10afa', 200.00);
        $this->assertTrue($restResponse->code == 200);
    }

    /** @test */
    public function AddToPaylinkTest() {
        $paylink = $this->gService->GetPaylink("87f6dd2e-d8b4-41dd-a609-3e36e414dce2");
        $this->assertTrue($paylink != null );
        $initialBalance = $paylink->balance->totalOutstanding;
        $restResponse = $this->gService->AddToPaylink($paylink, 100.00, 200.00);
        $this->assertTrue($restResponse->code == 200);
        $paylink = $this->gService->GetPaylink("87f6dd2e-d8b4-41dd-a609-3e36e414dce2");
        $this->assertTrue($paylink != null );
        $this->assertTrue($paylink->balance->totalOutstanding == $initialBalance + 30000);
    }

    /** @test */
    public function UpdatePaylinkTest() {
        //Paylink Id: 87f6dd2e-d8b4-41dd-a609-3e36e414dce2
        $paylink = new Paylink();
        $paylink->id = '87f6dd2e-d8b4-41dd-a609-3e36e414dce2';
        $restResponse = $this->gService->UpdatePaylink($paylink, 1234.56, 2345.67);
        $this->assertTrue($restResponse->code == 200);
        $paylink = $this->gService->GetPaylink('87f6dd2e-d8b4-41dd-a609-3e36e414dce2');
        $this->assertTrue($paylink != null);
        $this->assertTrue($paylink->balance->totalOutstanding == (123456 + 234567));
    }

    /** @test */
    public function FetchPaylinkTxnTest() {
        $dateSince = new DateTime('2020-12-15T00:00:00.000Z');
        $payment = $this->gService->GetPaymentById('71dca067-c1be-423d-a3a3-dba2d9dcc3cd');
        $entityQueryResult = $this->gService->FetchPaymentTxn($payment);
        $this->assertTrue(count($entityQueryResult->FetchedEntities) > 0);
        $customer = new Customer();
        $customer->id = 'bf193ce3-f54f-40d4-b3e6-da5de83be0be';
        $customer->orgId = '38cb2803-d197-4d2f-ba81-6e6d6bf12373';
        $customerTxnResult = $this->gService->FetchCustomerTxn($customer, $dateSince);
        $this->assertTrue(count($customerTxnResult->FetchedEntities) > 0);
        $entityQueryResult = $this->gService->FetchPaylinkTxn($customerTxnResult->FetchedEntities[0]);
        $this->assertTrue($this->gService->getLastRestResponse()->code == 200);
    }

    /** @test */
    public function FetchCustomerTxnSinceTest() {
        $customer = new Customer();
        $customer->id = 'bf193ce3-f54f-40d4-b3e6-da5de83be0be';
        $customer->name = 'Manoj\'s Firm';
        $customer->orgId = '38cb2803-d197-4d2f-ba81-6e6d6bf12373';
        $dateSince = new DateTime('2021-01-01T00:00:00.000Z');
        $customerTxnSince = $this->gService->FetchCustomerTxn($customer, $dateSince);
        $txnCount = count($customerTxnSince->FetchedEntities);
        $this->assertTrue($txnCount > 0);
    }

    /** @test */
    public function GetNewPaymentsTest() {
        $dateSince = new DateTime('2021-01-01T00:00:00.000Z');
        $payments = $this->gService->GetNewPayments("7a72244e-4586-421d-85e5-a6a2d38188f0", $dateSince);
        $count = count($payments);
        $this->assertTrue(count($payments) > 0);
        $payments = $this->gService->GetNewPayments("61c593e1-a1ec-48a9-a28a-f26956a89d32", $dateSince);
        $count = count($payments);
        $this->assertTrue(count($payments) > 0);
        $payments = $this->gService->GetNewPayments("6c32a45b-de38-48f7-a80a-294f057c10af", $dateSince);
        $count = count($payments);
        $this->assertTrue(count($payments) > 0);
        $payments = $this->gService->GetNewPayments("bf193ce3-f54f-40d4-b3e6-da5de83be0be", $dateSince);
        $count = count($payments);
        $this->assertTrue(count($payments) > 0);
    }

    /** @test */
    public function FetchPaylinksTest() {
        $paylinkResult = $this->gService->FetchPaylinks();
        $this->assertTrue(count($paylinkResult->FetchedEntities) > 0);
        $paylinkResult = $this->gService->FetchPaylinks("bf193ce3-f54f-40d4-b3e6-da5de83be0be");
        $this->assertTrue(count($paylinkResult->FetchedEntities) > 0);
        $paylinkResult = $this->gService->FetchPaylinks("bf193ce3-f54f-40d4-b3e6-da5de83be0be", "896d9358-a48d-45ec-946b-6a0357f10afa");
        $this->assertTrue(count($paylinkResult->FetchedEntities) > 0);
        $paylinkResult = $this->gService->FetchPaylinks(null, "896d9358-a48d-45ec-946b-6a0357f10afa");
        $this->assertTrue(count($paylinkResult->FetchedEntities) > 0);
        $paylinkResult = $this->gService->FetchPaylinks(null, null, new DateTime('2021-03-04T00:00:00.000Z'));
        $this->assertTrue(count($paylinkResult->FetchedEntities) > 0);
    }

    /** @test */
    public function GetCustomerApiTokenTest() {
        $customerApiToken = $this->gService->GetCustomerApiToken('bf193ce3-f54f-40d4-b3e6-da5de83be0be', 'ThisIsATestTokenCreationTest');
        $jsonMapper = new  JsonMapper();
        $customerApiToken = $jsonMapper->map($customerApiToken, new CustomerApiToken());
        $customerApiToken = Utility::cast($customerApiToken, 'GravityLegal\GravityLegalAPI\CustomerApiToken');
        $this->assertTrue($customerApiToken != null);
    }

    /** @test */
    public function GetInvitedUsersTest() {
        $invitedUsers = $this->gService->GetInvitedUsers();
        $allInvitedUsersCount  = count($invitedUsers);
        //echo "All Invited Users Count = $allInvitedUsersCount";
        $this->assertTrue($allInvitedUsersCount > 0);
        $invitedUsers = $this->gService->GetInvitedUsers(new DateTime('2021-01-04T00:00:00.000Z'));
        $invitedUsersSinceFeb1Count  = count($invitedUsers);
        //echo "Invited Users Count Since Feb 1 = $invitedUsersSinceFeb1Count";
        $this->assertTrue($invitedUsersSinceFeb1Count < $allInvitedUsersCount);
    }
    /** @test */
    public function GetUserByEmailTest() {
        $userResponse = $this->gService->GetUserByEmail('manoj.srivastava@gmail.com');
        $this->assertTrue($userResponse->firstName == 'Manoj');
        $userResponse = $this->gService->GetUserByEmail('joe.shmoe@gmail.com');
        $this->assertTrue($userResponse == null);
    }
    //'858de48a-ae71-4912-9335-20d9d33457b0'
    /** @test */
    public function GetUserByIdTest() {
        $userResponse = $this->gService->GetUserById('858de48a-ae71-4912-9335-20d9d33457b0');
        $this->assertTrue($userResponse->firstName == 'Manoj');
    }

    /** @test */
    public function GetContactByEmailTest() {
        $contact = $this->gService->GetContactByEmail('Sonja65@gmail.com');
        $this->assertTrue($contact->email == 'Sonja65@gmail.com');
    }

    /** @test */
    public function GetContactTest() {
        $contact = $this->gService->GetContact('00f75e1e-aed3-43bd-b471-d3c982b9ad1a');
        $this->assertTrue($contact->email == 'Sonja65@gmail.com');
    }

    /** @test */
    public function FindClientByEmailTest() {
        $client = $this->gService->FindClientByEmail('Sonja65@gmail.com');
        $this->assertTrue($client->email == 'Sonja65@gmail.com');
        $client = $this->gService->FindClientByEmail('joe.shmoe@gmail.com');
        $this->assertTrue($client == null);
    }

    /** @test */
    public function FetchUsersTest() {
        $userResponse = $this->gService->FetchUsers(new DateTime('2021-01-01T00:00:00.000Z'));
        $this->assertTrue(count($userResponse->FetchedEntities) > 0);
    }

    /** @test */
    public function FetchCustomersTest() {
        $customerResponse = $this->gService->FetchCustomers(new DateTime('2021-01-01T00:00:00.000Z'));
        $this->assertTrue(count($customerResponse->FetchedEntities) > 0);
    }

    /** @test */
    public function FetchClientsTest() {
        $clientResponse = $this->gService->FetchClients(new DateTime('2021-01-01T00:00:00.000Z'));
        $this->assertTrue(count($clientResponse->FetchedEntities) > 0);
    }

    /** @test */
    public function CreateClientsTest() {
        $createClientList = $this->GenerateClients(2,'bf193ce3-f54f-40d4-b3e6-da5de83be0be');
        $userResponse = $this->gService->CreateNewClients($createClientList);
        $this->assertTrue(count($userResponse->CreatedEntities) == 2);
        foreach($userResponse->CreatedEntities as $clientTuple) {
            $client = array_values($clientTuple[0]);
            $json = $client[0];
            $jsonMapper = new  JsonMapper();
            $client = $jsonMapper->map($json, new Client());
            $client = Utility::cast($client, 'GravityLegal\GravityLegalAPI\Client');
            $this->gService->DeleteClient($client->id);
        }
    }

    /** @test */
    public function GetClientTest() {
        $clientResponse = $this->gService->GetClient('e2a17efe-4f95-4ee5-bf9b-8400efe88e9a');
        $this->assertTrue($clientResponse->clientName == 'Technovation Inc.');
    }

    /** @test */
    public function FindOrCreateClientTest() {
        $createClient = new CreateClient();
        $createClient->clientName = "Potomac Unit Testing Inc.";
        $createClient->customer = "bf193ce3-f54f-40d4-b3e6-da5de83be0be";
        $createClient->email = "manoj.srivastava+potomacunittesting@gmail.com";
        $createClient->firstName = "Potomac";
        $createClient->lastName = "UnitTesting";
        $createClient->phone = "240-555-1212";
        $client = $this->gService->FindOrCreateClient($createClient);
        $this->assertTrue($client != null);
        $clientList = $this->gService->FindClient($createClient->customer, $createClient->clientName, false);
        $this->assertTrue($clientList != null);
        $this->assertTrue(count($clientList) == 1);
        $this->gService->DeleteClient($client->id);

    }

    /** @test */
    public function FindClientWithPartialMatchTest() {
        $clientList = $this->gService->FindClient('bf193ce3-f54f-40d4-b3e6-da5de83be0be', 'a', true);
        $this->assertTrue(count($clientList) > 1);
    }
    /** @test */
    public function DeleteClientTest() {
        $createClient = new CreateClient();
        $createClient->clientName = "Potomac Unit Testing Inc.";
        $createClient->customer = "bf193ce3-f54f-40d4-b3e6-da5de83be0be";
        $createClient->email = "manoj.srivastava+potomacunittesting@gmail.com";
        $createClient->firstName = "Potomac";
        $createClient->lastName = "UnitTesting";
        $createClient->phone = "240-555-1212";
        $client = $this->gService->FindOrCreateClient($createClient);
        $this->assertTrue($client != null);
        $this->gService->DeleteClient($client->id);
        $clientList = $this->gService->FindClient($createClient->customer, $createClient->clientName, false);
        $this->assertTrue($clientList == null);
    }

    /** @test */
    public function DeleteClientFailTest() {
        $result = $this->gService->DeleteClient('Non existant Id');
        $this->assertTrue($result == false);
    }

    /** @test */
    public function CreateNewPaylinkWithNewMatterTest() {
        $createPaylink = new CreatePaylink();
        $createMatter = new CreateMatter();
        $operating = new Operating();
        $operating->amount = 10000;
        $trust = new Trust();
        $trust->amount = 20000;
        $createPaylink->customer = "bf193ce3-f54f-40d4-b3e6-da5de83be0be";
        $createPaylink->client = "896d9358-a48d-45ec-946b-6a0357f10afa";
        $createPaylink->externalId = Utility::GUIDv4();
        $createPaylink->operating = $operating;
        $createPaylink->trust = $trust;

        $createMatter->client = 'be086b90-4b15-4c5d-a20b-bcb0821ec522';
        $createMatter->externalId = Utility::GUIDv4();
        $createMatter->name = 'Test Matter ' . $createMatter->externalId;
        $createMatter->status = 'Draft';
        $createMatter->secondClient = '3c41adcc-dd57-464d-987c-245d324e6d2b';

        $paylinkInfo = $this->gService->CreateNewPaylinkWithMatter($createPaylink, $createMatter);
        $this->assertTrue($paylinkInfo != null);
        $deletionResult = $this->gService->DeletePaylink($paylinkInfo->id);
        $this->assertTrue($deletionResult);

    }

    /** @test */
    public function DeletePaylinkTest() {
        $createPaylink = new CreatePaylink();
        $operating = new Operating();
        $operating->amount = 10000;
        $trust = new Trust();
        $trust->amount = 20000;
        $createPaylink->customer = "bf193ce3-f54f-40d4-b3e6-da5de83be0be";
        $createPaylink->client = "896d9358-a48d-45ec-946b-6a0357f10afa";
        $createPaylink->matter = "23c47c88-a287-4962-bf92-6ff30798377c";
        $createPaylink->externalId = Utility::GUIDv4();
        $createPaylink->operating = $operating;
        $createPaylink->trust = $trust;
        $paylinkInfo = $this->gService->CreateNewPaylinkWithMatter($createPaylink);
        $this->assertTrue($paylinkInfo != null);
        $deletionResult = $this->gService->DeletePaylink($paylinkInfo->id);
        $this->assertTrue($deletionResult);
    }

    /** @test */
    public function FindMatterByExternalIdTest() {
        $matter = $this->gService->FindMatterByExternalId("soluno_matter_id_1");
        $this->assertTrue($matter != null);
    }

    /** @test */
    public function FindOrCreateMatterTest() {
        $createMatter = new CreateMatter();
        $createMatter->client = 'be086b90-4b15-4c5d-a20b-bcb0821ec522';
        $createMatter->externalId = Utility::GUIDv4();
        $createMatter->name = 'Test Matter ' . Utility::GUIDv4();
        $createMatter->status = 'Draft';
        $createMatter->secondClient = '3c41adcc-dd57-464d-987c-245d324e6d2b';
        $matter = $this->gService->FindOrCreateMatter($createMatter);
        $this->assertTrue($matter != null);
        $matterId = $matter->id;
        $matter = $this->gService->FindOrCreateMatter($createMatter);
        $this->assertTrue($matter != null);
        $this->assertTrue($matter->id == $matterId);
    }

    public function GenerateClients(int $clientCount, string $customer): array {
        $faker = Factory::create();
        $createClientList = array();
        for ($i = 0; $i < $clientCount; $i++) {
            $clientName = $faker->company();
            $firstName = $faker->firstName();
            $lastName = $faker->lastName();
            $email = "manoj.srivastava+$firstName.$lastName.$clientName@gmail.com";
            $createClient = new CreateClient();
            $createClient->customer = $customer;
            $createClient->clientName = $clientName;
            $createClient->firstName = $firstName;
            $createClient->lastName = $lastName;
            $createClient->email = $email;
            $createClientList[] = $createClient;
        }
        return $createClientList;
    }
    public function GenerateCreateUsers(int $userCount, Customer $customer): array {
        $faker = Factory::create();
        $createUsersList = array();
        for ($i = 0; $i < $userCount; $i++) {
            $firstName = $faker->firstName();
            $lastName = $faker->lastName();
            $email = "manoj.srivastava+$firstName.$lastName@gmail.com";
            $createUser = new CreateUser();
            $createUser->org = $customer->orgId;
            $createUser->firstName = $firstName;
            $createUser->lastName = $lastName;
            $createUser->email = $email;
            $createUser->status = 'OUTSTANDING';
            $createUser->invitedBy = '3dfad5ce-57c3-4df4-8bd0-3a2be9fbcaa0';
            $createUser->role = 'user';
            $createUser->app = 'soluno';
            $createUser->website = 'https://app.sandbox.gravity-legal.com';
            $createUsersList[] = $createUser;
        }
        return $createUsersList;
    }
    public function GenerateCreateContacts(int $contactCount, string $clientId): array {
        $faker = Factory::create();
        $createContactsList = array();
        for ($i = 0; $i < $contactCount; $i++) {
            $firstName = $faker->firstName();
            $lastName = $faker->lastName();
            $email = "manoj.srivastava+$firstName.$lastName@gmail.com";
            $phone = $faker->phoneNumber();
            $createContact = new CreateContact();
            $createContact->firstName = $firstName;
            $createContact->lastName = $lastName;
            $createContact->email = $email;
            $createContact->phone = $phone;
            $createContact->client = $clientId;
            $createContactsList[] = $createContact;
        }
        return $createContactsList;
    }
    public function GenerateCreateCustomers(int $customerCount, string $partnerId): array {
        $faker = Factory::create();
        $createCustomersList = array();
        for ($i = 0; $i < $customerCount; $i++) {
            $companyName = $faker->company();
            $firstName = $faker->firstName();
            $lastName = $faker->lastName();
            $email = "manoj.srivastava+$firstName.$lastName@gmail.com";
            $currency = new Currency();
            $currency->value = 'USD';
            $currency->label = 'USD($)';
            $appData = new AppData();
            $appData->currency = $currency;
            $notifPrefs = new NotifPrefs();
            $notifPrefs->ACHReturn = false;
            $notifPrefs->invoiceNotice = false;
            $notifPrefs->paymentReceipts = false;
            $notifPrefs->emails = [$email,];
            $createCustomer = new CreateCustomer();
            $createCustomer->name = $companyName;
            $createCustomer->partner = $partnerId;
            $createCustomer->appData = $appData;
            $createCustomer->promptToCreateSPM = false;
            $createCustomer->notifPrefs = $notifPrefs;
            $createCustomersList[] = $createCustomer;
        }
        return $createCustomersList;
    }

}