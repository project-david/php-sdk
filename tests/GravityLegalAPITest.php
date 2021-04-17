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
    public function GetUserByEmailTest() {
        $userResponse = $this->gService->GetUserByEmail('manoj.srivastava@gmail.com');
        $this->assertTrue($userResponse->firstName == 'Manoj');
    }
    //'858de48a-ae71-4912-9335-20d9d33457b0'
    /** @test */
    public function GetUserByIdTest() {
        $userResponse = $this->gService->GetUserById('858de48a-ae71-4912-9335-20d9d33457b0');
        $this->assertTrue($userResponse->firstName == 'Manoj');
    }

    /** @test */
    public function FetchUsersTest() {
        $userResponse = $this->gService->FetchUsers(new DateTime('2021-01-01T00:00:00.000Z'));
        $this->assertTrue(count($userResponse->FetchedEntities) > 0);
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

        $paylinkInfo = $this->gService->CreateNewPaylink($createPaylink, $createMatter);
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
        $paylinkInfo = $this->gService->CreateNewPaylink($createPaylink);
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
            $email = $faker->email();
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

}