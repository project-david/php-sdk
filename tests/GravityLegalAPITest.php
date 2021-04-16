<?php
namespace GravityLegal\GravityLegalAPI\Tests;

use GravityLegal\GravityLegalAPI\GravityLegalService;
use PHPUnit\Framework\TestCase;
use DateTime;
use PHPUnit\Framework\Constraint\Count;
use Faker\Factory;
use GravityLegal\GravityLegalAPI\CreateClient;
use GravityLegal\GravityLegalAPI\Customer;

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