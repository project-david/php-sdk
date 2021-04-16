<?php
namespace GravityLegal\GravityLegalAPI;

use DateTime;
use DateInterval;
use Unirest;
use JsonMapper;
use GravityLegal\GravityLegalAPI\Utility;

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
                $json = json_decode($response->raw_body);
                $jsonMapper = new  JsonMapper();
                $userResult = $jsonMapper->map($json, new UserResult());
        
                foreach($userResult->result->records as $user) {
                    $user = $jsonMapper->map($user, new User());
                    $user = Utility::cast($user, 'GravityLegal\GravityLegalAPI\User');
                    $result->FetchedEntities[] = $user;
                }
                $fetchPage++;
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