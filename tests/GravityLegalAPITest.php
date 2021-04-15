<?php
namespace GravityLegal\GravityLegalAPI\Tests;

use GravityLegal\GravityLegalAPI\GravityLegalService;
use PHPUnit\Framework\TestCase;

class GravityLegalAPITest extends TestCase
{
    /** @test */
    public function GetUserByEmailTest() {
        $PRAHARI_BASE_URL='https://api.sandbox.gravity-legal.com/prahari/v1';
        $ENV_URL='https://api.sandbox.gravity-legal.com/pd/v1';
        $SYSTEM_TOKEN='eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzaWQiOiJzYW5kYm94Iiwic3RpZCI6ImYzMDAyNmFmLTMxMjAtNDdmYy05MmRmLWFlNmMyZmEwMThiNSIsInRva2VuX3VzZSI6InN5c3RlbV90b2siLCJybmQiOjQ2NjQyNTEzMjEsImlhdCI6MTYwODY2NjI1MH0.-gtUMBkeIhdLilUJWiCgHWfROgCtWEcx_1gLlNWmVmo';
        $APP_ID='soluno';
        $ORG_ID='781ac60e-cff5-4971-8053-cddf5eec696f';

        $baseUrl = $ENV_URL . '/entities/';

        $API_KEY = array ('PRAHARI_BASE_URL' => $PRAHARI_BASE_URL, 'ENV_URL' => $ENV_URL, 
                            'SYSTEM_TOKEN' => $SYSTEM_TOKEN, 'APP_ID' => $APP_ID, 'ORG_ID' => $ORG_ID );
        $gService = new GravityLegalService($API_KEY);
        $userResponse = $gService->GetUserByEmail('manoj.srivastava@gmail.com');
        echo $userResponse;
        $this->assertTrue(ob_get_length($userResponse) >0);
    }

}