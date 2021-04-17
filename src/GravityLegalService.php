<?php
namespace GravityLegal\GravityLegalAPI;

use DateTime;
use DateInterval;
use Unirest;
use JsonMapper;
use GravityLegal\GravityLegalAPI\Utility;
use GravityLegal\GravityLegalAPI\GetClientResult;

class GravityLegalService
{
    protected array $httpHeaders;
    protected string $baseUrl='';
    protected string $prahariBaseUrl;
    protected string $operationsUrl;
    protected string $opsUrl;
    protected string $appId;
    protected string $orgId;

    public function __construct(array $envVariables=null)
    {
        if ($envVariables) {
            $PRAHARI_BASE_URL=$envVariables['PRAHARI_BASE_URL'];
            $ENV_URL=$envVariables['ENV_URL'];
            $SYSTEM_TOKEN=$envVariables['SYSTEM_TOKEN'];
            $APP_ID=$envVariables['APP_ID'];
            $ORG_ID=$envVariables['ORG_ID'];
            $this->setBaseUrl($ENV_URL . '/entities/');
            $this->setOpsUrl($ENV_URL . '/operations/');
            $this->setPrahariBaseUrl($PRAHARI_BASE_URL . "/entities/");
            $this->setAppId($APP_ID);
            $this->setOrgId($ORG_ID);
            $httpHeaders = array(
                'Authorization' => 'Bearer ' . $SYSTEM_TOKEN,
                'X-PRAHARI-APPID' => $APP_ID,
                'X-PRAHARI-ORGID' => $ORG_ID,
                'Content-Type' => 'application/json'
            );
            $this->setHttpHeaders($httpHeaders);
        };

    }
    /// <summary>
    /// Gets the user by email.
    /// </summary>
    /// <param name="email">The email.</param>
    /// <returns>An User.</returns>
    public function GetUserByEmail(string $emailAddress): User
    {
        $url = $this->getBaseUrl() . 'User';
        $query = array("email" => $emailAddress, "select" => "customer,customer.partner,fullName","sysGen:not" => "true");
        $response = Unirest\Request::get($url, $this->getHttpHeaders(), $query);
        $json = json_decode($response->raw_body);
        $jsonMapper = new  JsonMapper();
        $userResult = $jsonMapper->map($json, new UserResult());
        $user = $jsonMapper->map($userResult->result->records[0], new User());
        $user = Utility::cast($user, 'GravityLegal\GravityLegalAPI\User');
        return ($user);
    }
    /// <summary>
    /// Gets the user by id.
    /// </summary>
    /// <param name="userId">The user id.</param>
    /// <returns>An User.</returns>
    public function GetUserById(string $userId) : User
    {
        $url = $this->getBaseUrl() . 'User/' . $userId;
        $query = array("select" => "customer,customer.partner,fullName","sysGen:not" => "true");
        $response = Unirest\Request::get($url, $this->getHttpHeaders(), $query);
        $json = json_decode($response->raw_body);
        $jsonMapper = new  JsonMapper();
        $userResult = $jsonMapper->map($json, new GetUserResult());
        $user = $jsonMapper->map($userResult->result, new User());
        $user = Utility::cast($user, 'GravityLegal\GravityLegalAPI\User');
        return ($user);
    }

    /// <summary>
    /// string createdSinceDateTime is an optional parameter.
    /// If createdSinceDateTime is provided the method returns only the users
    /// who were created at or after the given date-time and have accepted
    /// the invitation by resetting their password
    /// </summary>
    /// <param name="createdSinceDateTime"></param>
    /// <returns></returns>
    public function FetchUsers(DateTime $createdSinceDateTime = null): EntityQueryResult   {
        $diff1Day = new DateInterval('P1D');
        $currentTime = new DateTime();
        $endOfTimeRange = $currentTime->add($diff1Day);
        $createdUntil = str_replace('UTC', 'Z', date_format($endOfTimeRange,'Y-m-d\TH:i:s.vT'));
        $query = array();
        if ($createdSinceDateTime != null) {
            $createdSince = date_format($createdSinceDateTime, 'Y-m-d\TH:i:s.vT');
            $query = array("createdOn:range" => '[' . $createdSince . ',' .  $createdUntil . ']');
        }
        $result = new EntityQueryResult();
        $url = $this->getBaseUrl() . 'User';
        $response = Unirest\Request::get($url, $this->getHttpHeaders(), $query);
        if ($response->code == 200) {
            $json = json_decode($response->raw_body);
            $jsonMapper = new  JsonMapper();
            $userResult = $jsonMapper->map($json, new UserResult());
    
            foreach($userResult->result->records as $user) {
                $user = $jsonMapper->map($user, new User());
                $user = Utility::cast($user, 'GravityLegal\GravityLegalAPI\User');
                $result->FetchedEntities[] = $user;
            }
    
            $numPages = $userResult->result->totalCount / $userResult->result->pageSize;
            $pageSize = $userResult->result->pageSize;
            if ($userResult->result->totalCount % $pageSize != 0)
                $numPages++;
            $fetchPage = 1;
            if ($numPages > 1) {
                $fetchPage++;
                while ($fetchPage <= $numPages)
                {
                    $query = array("pageNo" => $fetchPage, "pageSize" => $pageSize);
                    if ($createdSinceDateTime != null) {
                        $createdSince = date_format($createdSinceDateTime, 'Y-m-d\TH:i:s.vT');
                        $query = array("pageNo" => $fetchPage, "pageSize" => $pageSize, "createdOn:range" => "[" . $createdSince . "," .   $createdUntil . "]");
                    }
                    $response = Unirest\Request::get($url, $this->getHttpHeaders(), $query);
                    if ($response->code == 200) {
                        $json = json_decode($response->raw_body);
                        $jsonMapper = new  JsonMapper();
                        $userResult = $jsonMapper->map($json, new UserResult());
            
                        foreach ($userResult->result->records as $user) {
                            $user = $jsonMapper->map($user, new User());
                            $user = Utility::cast($user, 'GravityLegal\GravityLegalAPI\User');
                            $result->FetchedEntities[] = $user;
                        }
                        $fetchPage++;
                    }
                    else
                        break;
                }
            }
        }
        return $result;
    }

    /// <summary>
    /// Creates the new clients.
    /// </summary>
    /// <param name="createClientList">An array containing CreateClient objects</param>
    /// <returns>A Dictionary.</returns>
    public function CreateNewClients(array $createClientList): EntityCreationResult {
        $entityCreationResult = new EntityCreationResult();
        $failedRequests = array();
        $url = $this->getBaseUrl() . 'Client';
        foreach ($createClientList  as $createClient)
        {
            $body = json_encode($createClient);
            $response = Unirest\Request::post($url, $this->getHttpHeaders(), $body);
            if ($response->code == 200) {
                $json = json_decode($response->raw_body);
                $jsonMapper = new  JsonMapper();
                $createClientResponse = $jsonMapper->map($json, new CreateClientResponse());
                $client = $jsonMapper->map($createClientResponse->result, new Client());
                $client = Utility::cast($client, 'GravityLegal\GravityLegalAPI\Client');
                $entityCreationResult->CreatedEntities[][] = array(trim($body) => $client);
            }
            else {
                $entityCreationResult->FailedRequests[][] = array(trim($body) => $response);
            }
        }
        return $entityCreationResult;
    }

    /// <summary>
    /// Gets the client.
    /// </summary>
    /// <param name="clientId">The client id.</param>
    /// <returns>A Client.</returns>
    public function GetClient(string $clientId): Client {
        $gravityClient = new Client();
        $url = $this->getBaseUrl() . 'Client/' . $clientId;
        $response = Unirest\Request::get($url, $this->getHttpHeaders());
        if ($response->code == 200) {
            $json = json_decode($response->raw_body);
            $jsonMapper = new  JsonMapper();
            $client = $jsonMapper->map($json, new GetClientResult());
            $gravityClient = Utility::cast($client->result, 'GravityLegal\GravityLegalAPI\Client');
        }
        return $gravityClient;
    }

    /// <summary>
    /// Finds the client.
    /// </summary>
    /// <param name="customerId">The customer id.</param>
    /// <param name="clientName">The client name.</param>
    /// <param name="partialMatch">If true, partial match.</param>
    /// <returns>A list of Clients.</returns>
    public function FindClient(string $customerId, string $clientName, bool $partialMatch = true) : array {
        $clientNameLike = $partialMatch ? "%" . $clientName . "%" : $clientName;
        $clients = array();
        $findClientResponse = null;
        $url = $this->getBaseUrl() . 'Client';
        $query = array('clientName:like' => $clientNameLike, 'customer' => $customerId);
        $response = Unirest\Request::get($url, $this->getHttpHeaders(), $query);
        if ($response->code = 200) {
            $json = json_decode($response->raw_body);
            $jsonMapper = new  JsonMapper();
            $clientResult = $jsonMapper->map($json, new ClientResult());
            foreach($clientResult->result->records as $client) {
                $clientRec = $jsonMapper->map($client, new Client());
                $clientRec = Utility::cast($clientRec, 'GravityLegal\GravityLegalAPI\Client');
                $clients[] = $clientRec;
            }
            $numPages = $clientResult->result->totalCount / $clientResult->result->pageSize;
            $pageSize = $clientResult->result->pageSize;
            if ($clientResult->result->totalCount % $pageSize != 0)
                $numPages++;
            $fetchPage = 1;
            if ($numPages > 1) {
                $fetchPage++;
                while ($fetchPage <= $numPages)
                {
                    $query = array('clientName:like' => $clientNameLike, 'customer' => $customerId, "pageNo" => $fetchPage, "pageSize" => $pageSize);
                    $response = Unirest\Request::get($url, $this->getHttpHeaders(), $query);
                    if ($response->code == 200) {
                        $json = json_decode($response->raw_body);
                        $jsonMapper = new  JsonMapper();
                        $clientResult = $jsonMapper->map($json, new ClientResult());
            
                        foreach ($clientResult->result->records as $client) {
                            $clientRec = $jsonMapper->map($client, new Client());
                            $clientRec = Utility::cast($clientRec, 'GravityLegal\GravityLegalAPI\Client');
                            $clients[] = $clientRec;
                        }
                        $fetchPage++;
                    }
                    else
                        break;
                }
            }
        }
        return $clients;
    }

    /// <summary>
    /// Finds the or create client.
    /// </summary>
    /// <param name="createClientParam">The create client param.</param>
    /// <returns>A Client.</returns>
    public function FindOrCreateClient(CreateClient $createClientParam): Client {
        $client = null;
        $matchingClients = $this->FindClient($createClientParam->customer, $createClientParam->clientName, false);
        if (count($matchingClients) >= 1)
            $client = $matchingClients[0];
        else if (count($matchingClients) == 0) {
            $url = $this->getBaseUrl() . 'Client';
            $body = json_encode($createClientParam);
            $response = Unirest\Request::post($url, $this->getHttpHeaders(), $body);
            if ($response->code == 200) {
                $json = json_decode($response->raw_body);
                $jsonMapper = new  JsonMapper();
                $createClientResponse = $jsonMapper->map($json, new CreateClientResponse());
                $client = $jsonMapper->map($createClientResponse->result, new Client());
                $client = Utility::cast($client, 'GravityLegal\GravityLegalAPI\Client');
            }
        }
        return $client;
    }

    /// <summary>
    /// Gets the client instance info response.
    /// </summary>
    /// <param name="clientId">The client id.</param>
    /// <returns>A ClientInstanceInfoResponseResult.</returns>
    private function GetClientInstanceInfoResponse(string $clientId): ?ClientInstanceInfoResponseResult   {
        $clientInstanceInfoResponseResult = null;
        $url = $this->getBaseUrl() . 'Client/' . $clientId . '/getInstanceInfo';
        $body = '{}';
        $response = Unirest\Request::post($url, $this->getHttpHeaders(), $body);
        if ($response->code == 200) {
            $json = json_decode($response->raw_body);
            $jsonMapper = new  JsonMapper();
            $clientInfoInstanceResponse = $jsonMapper->map($json, new ClientInstanceInfoResponse());
            $clientInfoInstanceResponse = $jsonMapper->map($clientInfoInstanceResponse->result, new ClientInstanceInfoResponseResult());
            $clientInstanceInfoResponseResult = Utility::cast($clientInfoInstanceResponse, 'GravityLegal\GravityLegalAPI\ClientInstanceInfoResponseResult');
        }
        return $clientInstanceInfoResponseResult;
    }

    /// <summary>
    /// Deletes the ledger item.
    /// </summary>
    /// <param name="ledgerItemId">The ledger item id.</param>
    /// <returns>A bool.</returns>
    private function DeleteLedgerItem(string $ledgerItemId): bool {
        $deleteRequestResponse = null;
        $url = $this->getBaseUrl() . 'LedgerItem/' . $ledgerItemId . '/deleteInstance';
        $body = '{}';
        $response = Unirest\Request::post($url, $this->getHttpHeaders(), $body);
        if ($response->code == 200) {
            $json = json_decode($response->raw_body);
            $jsonMapper = new  JsonMapper();
            $deleteRequestResponse = $jsonMapper->map($json, new DeleteRequestResponse());
            $deleteRequestResponse = $jsonMapper->map($deleteRequestResponse->result, new DeleteRequestResponse());
            $deleteRequestResponse = Utility::cast($deleteRequestResponse, 'GravityLegal\GravityLegalAPI\DeleteRequestResponse');
            return $deleteRequestResponse->result->success;
        }
        else
            return false;
    }

    /// <summary>
    /// Deletes the paylink.
    /// </summary>
    /// <param name="paylinkId">The paylink id.</param>
    /// <returns>A bool.</returns>
    public function DeletePaylink(string $paylinkId): bool {
        $deleteRequestResponse = null;
        $url = $this->getBaseUrl() . 'Paylink/' . $paylinkId . '/deleteInstance';
        $body = '{}';
        $response = Unirest\Request::post($url, $this->getHttpHeaders(), $body);
        if ($response->code == 200) {
            $json = json_decode($response->raw_body);
            $jsonMapper = new  JsonMapper();
            $deleteRequestResponse = $jsonMapper->map($json, new DeleteRequestResponse());
            $deleteRequestResponse = Utility::cast($deleteRequestResponse, 'GravityLegal\GravityLegalAPI\DeleteRequestResponse');
            return $deleteRequestResponse->result->success;
        }
        else
            return false;
    }

    /// <summary>
    /// Deletes the client.
    /// </summary>
    /// <param name="clientId">The client id.</param>
    /// <returns>A bool.</returns>
    public function DeleteClient(string $clientId): bool {
        $deleteRequestResponse = null;

        $clientInstanceInfoResponseResult = $this->GetClientInstanceInfoResponse($clientId);
        if ($clientInstanceInfoResponseResult == null)
            return false;
        if (($clientInstanceInfoResponseResult->references != null) && (count($clientInstanceInfoResponseResult->references) > 0))
        {
            foreach ($clientInstanceInfoResponseResult->references as $referencedEntity)
            {
                if ($referencedEntity->entity == 'LedgerItem') {
                    foreach($referencedEntity->ids as $ledgerItemId) {
                        if ($this->DeleteLedgerItem($ledgerItemId) == false)
                            return false;
                    }
                }
            }
        }
        if (($clientInstanceInfoResponseResult->referencedBy != null) && (count($clientInstanceInfoResponseResult->referencedBy) > 0))
        {
            foreach ($clientInstanceInfoResponseResult->referencedBy as $referencedByEntity)
            {
                if ($referencedByEntity->entity == 'Paylink') {
                    foreach($referencedByEntity->ids as $paylinkId) {
                        if ($this->DeletePaylink($paylinkId) == false)
                            return false;
                    }
                }
            }
        }

        $url = $this->getBaseUrl() . 'Client/' . $clientId . '/deleteInstance';
        $body = '{}';
        $response = Unirest\Request::post($url, $this->getHttpHeaders(), $body);
        if ($response->code == 200) {
            $json = json_decode($response->raw_body);
            $jsonMapper = new  JsonMapper();
            $deleteRequestResponse = $jsonMapper->map($json, new DeleteRequestResponse());
            $deleteRequestResponse = Utility::cast($deleteRequestResponse, 'GravityLegal\GravityLegalAPI\DeleteRequestResponse');
            return $deleteRequestResponse->result->success;
        }
        else
            return false;
    }

    /// <summary>
    /// Creates the new matters.
    /// </summary>
    /// <param name="createMatterList">The create matter list.</param>
    /// <returns>A Dictionary.</returns>
    public function CreateNewMatters(array $createMatterList): EntityCreationResult {
        $entityCreationResult = new EntityCreationResult();
        $failedRequests = array();
        $url = $this->getBaseUrl() . 'Matter';
        foreach ($createMatterList  as $createMatter)
        {
            $body = json_encode($createMatter);
            $response = Unirest\Request::post($url, $this->getHttpHeaders(), $body);
            if ($response->code == 200) {
                $json = json_decode($response->raw_body);
                $jsonMapper = new  JsonMapper();
                $createMatterResponse = $jsonMapper->map($json, new CreateMatterResponse());
                $matter = $jsonMapper->map($createMatterResponse->result, new Matter());
                $matter = Utility::cast($matter, 'GravityLegal\GravityLegalAPI\Client');
                $entityCreationResult->CreatedEntities[][] = array(trim($body) => $matter);
            }
            else {
                $entityCreationResult->FailedRequests[][] = array(trim($body) => $response);
            }
        }
        return $entityCreationResult;
    }

    /// <summary>
    /// Finds the or create matter.
    /// </summary>
    /// <param name="createMatter">The create matter.</param>
    /// <returns>A Matter.</returns>
    public function FindOrCreateMatter(CreateMatter $createMatter): Matter {
        $matter = null;
        if ($createMatter->externalId != null)
            $matter = $this->FindMatterByExternalId($createMatter->externalId);
        if ($matter == null) {
            $matterCreationResult = $this->CreateNewMatters(array($createMatter,));
            foreach($matterCreationResult->CreatedEntities as $matterTuple) {
                $matter = array_values($matterTuple[0]);
                $json = $matter[0];
                $jsonMapper = new  JsonMapper();
                $matter = $jsonMapper->map($json, new Matter());
                $matter = Utility::cast($matter, 'GravityLegal\GravityLegalAPI\Matter');
            }
        }
        return $matter;
    }

    /// <summary>
    /// Finds the matter by external id.
    /// </summary>
    /// <param name="externalId">The external id.</param>
    /// <returns>A Matter.</returns>
    public function FindMatterByExternalId(string $externalId): ?Matter  {
        $matter = null;
        $matterResult = null;
        $url = $this->getBaseUrl() . 'Matter';
        $query = array('externalId' => $externalId);
        $response = Unirest\Request::get($url, $this->getHttpHeaders(), $query);
        if ($response->code == 200) {
            $json = json_decode($response->raw_body);
            $jsonMapper = new  JsonMapper();
            $matterResult = $jsonMapper->map($json, new MatterResult());
            if (($matterResult != null) && (count($matterResult->result->records) > 0)) {
                $matterRec = $jsonMapper->map($matterResult->result->records[0], new Matter());
                $matter = Utility::cast($matterRec, 'GravityLegal\GravityLegalAPI\Matter');
            }
        }
        return $matter;
    }


    /// <summary>
    /// Creates the new paylink.
    /// </summary>
    /// <param name="createPaylink">The create paylink.</param>
    /// <returns>A PaylinkInfo.</returns>
    public function CreateNewPaylink(CreatePaylink $createPaylink, CreateMatter $createMatter = null): PaylinkInfo {
        $createdPaylinkInfo = null;
        $createPaylinkResponse = null;
        $url = $this->getBaseUrl() . 'Paylink/createPaylink';

        if ($createMatter != null) {
            $matter = $this->FindOrCreateMatter($createMatter);
            if ($matter == null)
                return null;
            else
                $createPaylink->matter = $matter->id;
        }
        $body = json_encode($createPaylink);
        $response = Unirest\Request::post($url, $this->getHttpHeaders(), $body);
        if ($response->code == 200) {
            $json = json_decode($response->raw_body);
            $jsonMapper = new  JsonMapper();
            $createPaylinkResponse = $jsonMapper->map($json, new CreatePaylinkResponse());
            $createPaylinkResponse = Utility::cast($createPaylinkResponse, 'GravityLegal\GravityLegalAPI\CreatePaylinkResponse');
            $createdPaylinkInfo = $jsonMapper->map($createPaylinkResponse->result, new PaylinkInfo());
            $createdPaylinkInfo = Utility::cast($createdPaylinkInfo, 'GravityLegal\GravityLegalAPI\PaylinkInfo');
        }
        return $createdPaylinkInfo;
    }


    /**
     * Get the value of httpHeaders
     */ 
    public function getHttpHeaders()
    {
        return $this->httpHeaders;
    }

    /**
     * Set the value of httpHeaders
     *
     * @return  self
     */ 
    public function setHttpHeaders($httpHeaders)
    {
        $this->httpHeaders = $httpHeaders;

        return $this;
    }

    /**
     * Get the value of baseUrl
     */ 
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * Set the value of baseUrl
     *
     * @return  self
     */ 
    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;

        return $this;
    }

    /**
     * Get the value of prahariBaseUrl
     */ 
    public function getPrahariBaseUrl()
    {
        return $this->prahariBaseUrl;
    }

    /**
     * Set the value of prahariBaseUrl
     *
     * @return  self
     */ 
    public function setPrahariBaseUrl($prahariBaseUrl)
    {
        $this->prahariBaseUrl = $prahariBaseUrl;

        return $this;
    }

    /**
     * Get the value of operationsUrl
     */ 
    public function getOperationsUrl()
    {
        return $this->operationsUrl;
    }

    /**
     * Set the value of operationsUrl
     *
     * @return  self
     */ 
    public function setOperationsUrl($operationsUrl)
    {
        $this->operationsUrl = $operationsUrl;

        return $this;
    }

    /**
     * Get the value of opsUrl
     */ 
    public function getOpsUrl()
    {
        return $this->opsUrl;
    }

    /**
     * Set the value of opsUrl
     *
     * @return  self
     */ 
    public function setOpsUrl($opsUrl)
    {
        $this->opsUrl = $opsUrl;

        return $this;
    }

    /**
     * Get the value of appId
     */ 
    public function getAppId()
    {
        return $this->appId;
    }

    /**
     * Set the value of appId
     *
     * @return  self
     */ 
    public function setAppId($appId)
    {
        $this->appId = $appId;

        return $this;
    }

    /**
     * Get the value of orgId
     */ 
    public function getOrgId()
    {
        return $this->orgId;
    }

    /**
     * Set the value of orgId
     *
     * @return  self
     */ 
    public function setOrgId($orgId)
    {
        $this->orgId = $orgId;

        return $this;
    }
}