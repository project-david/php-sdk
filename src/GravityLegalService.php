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
    protected string $prahariUrl;
    protected string $envUrl;
    protected Unirest\Response $lastRestResponse;

    public function __construct(array $envVariables=null)
    {
        if ($envVariables) {
            $PRAHARI_BASE_URL=$envVariables['PRAHARI_BASE_URL'];
            $ENV_URL=$envVariables['ENV_URL'];
            $SYSTEM_TOKEN=$envVariables['SYSTEM_TOKEN'];
            $APP_ID=$envVariables['APP_ID'];
            $ORG_ID=$envVariables['ORG_ID'];
            $this->setEnvUrl($ENV_URL);
            $this->setPrahariUrl($PRAHARI_BASE_URL);
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

    public function IsOnline(): bool {
        $result = false;
        $diff1Day = new DateInterval('P1D');
        $currentTime = new DateTime();
        $oneDayAgo = $currentTime->sub($diff1Day);
        $invitedUsers = $this->GetInvitedUsers($oneDayAgo);
        if ($this->getLastRestResponse()->code == 200)
            $result = true;
        return $result;
    }

    /// <summary>
    /// string createdSinceDateTime is an optional parameter.
    /// If createdSinceDateTime is provided the method returns only the invited users
    /// who were created at or after the given date-time.
    /// </summary>
    /// <param name="createdSinceDateTime"></param>
    /// <returns> array of InvitedUser objects</returns>
    public function GetInvitedUsers(DateTime $createdSinceDateTime = null) : array {
        $diff1Day = new DateInterval('P1D');
        $currentTime = new DateTime();
        $endOfTimeRange = $currentTime->add($diff1Day);
        $createdUntil = str_replace('UTC', 'Z', date_format($endOfTimeRange,'Y-m-d\TH:i:s.vT'));
        $query = array();
        if ($createdSinceDateTime != null) {
            $createdSince = date_format($createdSinceDateTime, 'Y-m-d\TH:i:s.vT');
            $createdSince = str_replace('UTC', 'Z', $createdSince);
            $query = array("createdOn:range" => '[' . $createdSince . ',' .  $createdUntil . ']');
        }
        $result = array();
        $url = $this->getPrahariBaseUrl() . 'UserInvite';
        $query = array_merge($query, array('orderBy'=>'updatedOn', 'orderDir' => 'desc', 'pageNo' => '1', 'pageSize' => '25', 'select' => 'app,org,fullName,role'));
        $response = Unirest\Request::get($url, $this->getHttpHeaders(), $query);
        $this->setLastRestResponse($response);
        if ($response->code == 200) {
            $json = json_decode($response->raw_body);
            $jsonMapper = new  JsonMapper();
            $userInviteResponse = $jsonMapper->map($json, new UserInviteResponse());
            $userInviteResponse = Utility::cast($userInviteResponse, 'GravityLegal\GravityLegalAPI\UserInviteResponse');
            foreach($userInviteResponse->result->records as $invitedUser) {
                $invitedUser = $jsonMapper->map($invitedUser, new InvitedUser());                
                $invitedUser = Utility::cast($invitedUser, 'GravityLegal\GravityLegalAPI\InvitedUser');
                $result[] = $invitedUser;
            }
            $numPages = $userInviteResponse->result->totalCount / $userInviteResponse->result->pageSize;
            $pageSize = $userInviteResponse->result->pageSize;
            if ($userInviteResponse->result->totalCount % $pageSize != 0)
                $numPages++;
            $fetchPage = 1;
            if ($numPages > 1) {
                $fetchPage++;
                while ($fetchPage <= $numPages) {
                    $query = array_merge($query, array('pageNo' => $fetchPage, 'pageSize' => $pageSize));
                    $response = Unirest\Request::get($url, $this->getHttpHeaders(), $query);
                    $this->setLastRestResponse($response);
                    if ($response->code == 200) {
                        $json = json_decode($response->raw_body);
                        $jsonMapper = new  JsonMapper();
                        $userInviteResponse = $jsonMapper->map($json, new UserInviteResponse());
                        $userInviteResponse = Utility::cast($userInviteResponse, 'GravityLegal\GravityLegalAPI\UserInviteResponse');
                        foreach($userInviteResponse->result->records as $invitedUser) {
                            $invitedUser = $jsonMapper->map($invitedUser, new InvitedUser());                
                            $invitedUser = Utility::cast($invitedUser, 'GravityLegal\GravityLegalAPI\InvitedUser');
                            $result[] = $invitedUser;
                        }
                    }
                    $fetchPage++;
                }
            }
        }
        return $result;
    }


    /// <summary>
    /// Gets the user by email.
    /// </summary>
    /// <param name="email">The email.</param>
    /// <returns>An User.</returns>
    public function GetUserByEmail(string $emailAddress): ?User
    {
        $user = null;
        $url = $this->getBaseUrl() . 'User';
        $query = array("email" => $emailAddress, "select" => "customer,customer.partner,fullName","sysGen:not" => "true");
        $response = Unirest\Request::get($url, $this->getHttpHeaders(), $query);
        $this->setLastRestResponse($response);
        if ($response->code == 200) {
            $json = json_decode($response->raw_body);
            $jsonMapper = new  JsonMapper();
            $userResult = $jsonMapper->map($json, new UserResult());
            if (count($userResult->result->records) > 0) {
                $user = $jsonMapper->map($userResult->result->records[0], new User());
                $user = Utility::cast($user, 'GravityLegal\GravityLegalAPI\User');
            }
        }
        return ($user);
    }
    /// <summary>
    /// Gets the user by id.
    /// </summary>
    /// <param name="userId">The user id.</param>
    /// <returns>An User.</returns>
    public function GetUserById(string $userId) : ?User
    {
        $user = null;
        $url = $this->getBaseUrl() . 'User/' . $userId;
        $query = array("select" => "customer,customer.partner,fullName","sysGen:not" => "true");
        $response = Unirest\Request::get($url, $this->getHttpHeaders(), $query);
        $this->setLastRestResponse($response);
        if ($response->code == 200) {
            $json = json_decode($response->raw_body);
            $jsonMapper = new  JsonMapper();
            $userResult = $jsonMapper->map($json, new GetUserResult());
            $user = $jsonMapper->map($userResult->result, new User());
            $user = Utility::cast($user, 'GravityLegal\GravityLegalAPI\User');
        }
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
        $this->setLastRestResponse($response);
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
                    $this->setLastRestResponse($response);
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
    /// string createdSinceDateTime is an optional parameter.
    /// If createdSinceDateTime is provided the method returns only the Customers
    /// who were created at or after the given date-time and have accepted
    /// the invitation by resetting their password
    /// </summary>
    /// <param name="createdSinceDateTime"></param>
    /// <returns></returns>
    public function FetchCustomers(DateTime $createdSinceDateTime = null): EntityQueryResult   {
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
        $url = $this->getBaseUrl() . 'Customer';
        $response = Unirest\Request::get($url, $this->getHttpHeaders(), $query);
        $this->setLastRestResponse($response);
        if ($response->code == 200) {
            $json = json_decode($response->raw_body);
            $jsonMapper = new  JsonMapper();
            $customerResult = $jsonMapper->map($json, new CustomerResult());
    
            foreach($customerResult->result->records as $customer) {
                $customer = $jsonMapper->map($customer, new Customer());
                $customer = Utility::cast($customer, 'GravityLegal\GravityLegalAPI\Customer');
                $result->FetchedEntities[] = $customer;
            }
    
            $numPages = $customerResult->result->totalCount / $customerResult->result->pageSize;
            $pageSize = $customerResult->result->pageSize;
            if ($customerResult->result->totalCount % $pageSize != 0)
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
                    $this->setLastRestResponse($response);
                    if ($response->code == 200) {
                        $json = json_decode($response->raw_body);
                        $jsonMapper = new  JsonMapper();
                        $customerResult = $jsonMapper->map($json, new CustomerResult());
            
                        foreach($customerResult->result->records as $customer) {
                            $customer = $jsonMapper->map($customer, new Customer());
                            $customer = Utility::cast($customer, 'GravityLegal\GravityLegalAPI\Customer');
                            $result->FetchedEntities[] = $customer;
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
    /// string createdSinceDateTime is an optional parameter.
    /// If createdSinceDateTime is provided the method returns only the paylinks
    /// which were created at or after the given date-time.
    /// </summary>
    /// <param name="customerId"></param>
    /// <param name="clientId"></param>
    /// <param name="createdSinceDateTime"></param>
    /// <returns></returns>
    public function FetchPaylinks(string $customerId = null, string $clientId = null, DateTime $createdSinceDateTime = null): EntityQueryResult {
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
        $url = $this->getBaseUrl() . 'Paylink';
        $query = array_merge($query, array('select' => 'customer,client,matter'));
        if ($customerId != null)
            $query = array_merge($query, array('customer' => $customerId));
        if ($clientId != null)
            $query = array_merge($query, array('client' => $clientId));
        $response = Unirest\Request::get($url, $this->getHttpHeaders(), $query);
        $this->setLastRestResponse($response);
        if ($response->code == 200) {
            $json = json_decode($response->raw_body);
            $jsonMapper = new  JsonMapper();
            $paylinkResult = $jsonMapper->map($json, new PaylinkResult());
            $paylinkResult = Utility::cast($paylinkResult, 'GravityLegal\GravityLegalAPI\PaylinkResult');
            foreach ($paylinkResult->result->records as $paylink) {
                $paylink = $jsonMapper->map($paylink, new Paylink());
                $paylink = Utility::cast($paylink, 'GravityLegal\GravityLegalAPI\Paylink');
                $result->FetchedEntities[] = $paylink;
            }
            $numPages = $paylinkResult->result->totalCount / $paylinkResult->result->pageSize;
            $pageSize = $paylinkResult->result->pageSize;
            if ($paylinkResult->result->totalCount % $pageSize != 0) {
                $numPages++;
            }
            $fetchPage = 1;
            if ($numPages > 1) {
                $fetchPage++;
                while ($fetchPage <= $numPages) {
                    $query = array_merge($query, array('pageNo' => $fetchPage, 'pageSize' => $pageSize));
                    $response = Unirest\Request::get($url, $this->getHttpHeaders(), $query);
                    $this->setLastRestResponse($response);
                    if ($response->code == 200) {
                        $json = json_decode($response->raw_body);
                        $jsonMapper = new  JsonMapper();
                        $paylinkResult = $jsonMapper->map($json, new PaylinkResult());
                        $paylinkResult = Utility::cast($paylinkResult, 'GravityLegal\GravityLegalAPI\PaylinkResult');
                        foreach ($paylinkResult->result->records as $paylink) {
                            $paylink = $jsonMapper->map($paylink, new Paylink());
                            $paylink = Utility::cast($paylink, 'GravityLegal\GravityLegalAPI\Paylink');
                            $result->FetchedEntities[] = $paylink;
                        }
                        $fetchPage++;
                    } else {
                        break;
                    }
                }
            }
        }
        return $result;
    }

    /// <summary>
    /// Gets the customer.
    /// </summary>
    /// <param name="customerId">The customer id.</param>
    /// <returns>A Customer.</returns>
    public function GetCustomer(string $customerId): ?Customer  {
        $cust = null;
        $url = $this->getBaseUrl() . 'Customer/' . $customerId;
        $response = Unirest\Request::get($url, $this->getHttpHeaders());
        $this->setLastRestResponse($response);
        if ($response->code == 200) {
            $json = json_decode($response->raw_body);
            $jsonMapper = new  JsonMapper();
            $customerResult = $jsonMapper->map($json, new GetCustomerResult());
            $cust = $jsonMapper->map($customerResult->result, new Customer());
            $cust = Utility::cast($cust, 'GravityLegal\GravityLegalAPI\Customer');
        }
        return ($cust);
    }


    /// <summary>
    /// Gets the customer api token.
    /// </summary>
    /// <param name="customerId">The customer id.</param>
    /// <param name="tokenName">The token name.</param>
    /// <param name="createIfNotFound">If true, create if not found.</param>
    /// <returns>A CustomerApiToken.</returns>
    public function GetCustomerApiToken(string $customerId, string $tokenName, bool $createIfNotFound = true): ?CustomerApiToken  {
        $customerApiToken = null;
        $customer = $this->GetCustomer($customerId);
        if ($customer != null)
        {
            $puserEmail = 'noreply@' . $customer->orgId . '.users';
            $url = $this->getPrahariBaseUrl() . 'PUser';
            $query = array('email' => $puserEmail);
            $response = Unirest\Request::get($url, $this->getHttpHeaders(), $query);
            $this->setLastRestResponse($response);
            if ($response->code == 200) {
                $json = json_decode($response->raw_body);
                $jsonMapper = new  JsonMapper();
                $pUserResponse = $jsonMapper->map($json, new PUserResponse());
                $puser = $jsonMapper->map($pUserResponse->result->records[0], new PUser());
                $puser = Utility::cast($puser, 'GravityLegal\GravityLegalAPI\PUser');
                $pUserId = $puser->id;
                $url = $this->getPrahariBaseUrl() . 'SystemToken';
                $query = array('select' => 'token', 'user.id' => $pUserId);
                $response = Unirest\Request::get($url, $this->getHttpHeaders(), $query);
                $this->setLastRestResponse($response);
                if ($response->code == 200) {
                    $matchingTokenFound = false;
                    $json = json_decode($response->raw_body);
                    $systemTokenResponse = $jsonMapper->map($json, new SystemTokenRequestResponse());
                    if ($systemTokenResponse->result->totalCount > 0) {
                        foreach ($systemTokenResponse->result->records as $systemTokenRecord) {
                            $systemTokenRecord = $jsonMapper->map($systemTokenRecord, new SystemTokenRecord());
                            $systemTokenRecord = Utility::cast($systemTokenRecord, 'GravityLegal\GravityLegalAPI\SystemTokenRecord');
                            if ($systemTokenRecord->name == $tokenName) {
                                $matchingTokenFound = true;
                                $customerApiToken = new CustomerApiToken();
                                $customerApiToken->APP_ID = $customer->appId;
                                $customerApiToken->ORG_ID = $customer->orgId;
                                $customerApiToken->ENV_URL = $this->getEnvUrl();
                                $customerApiToken->PRAHARI_BASE_URL = $this->getPrahariUrl();
                                $customerApiToken->SYSTEM_TOKEN = $systemTokenRecord->token;
                                break;
                            }
                        }
                    }
                    if (($matchingTokenFound == false) && ($createIfNotFound)) {
                        $systemTokenCreationRequest = new SystemTokenCreationRequest();
                        $systemTokenCreationRequest->name = $tokenName;
                        $systemTokenCreationRequest->user = $pUserId;
                        $systemTokenCreationRequest->token = 'TBR';
                        $body = json_encode($systemTokenCreationRequest);
                        $url = $this->getPrahariBaseUrl() . 'SystemToken';
                        $response = Unirest\Request::post($url, $this->getHttpHeaders(), $body);
                        $this->setLastRestResponse($response);
                        if ($response->code == 200) {
                            $json = json_decode($response->raw_body);
                            $jsonMapper = new  JsonMapper();
                            $systemTokenCreationResponse = $jsonMapper->map($json, new SystemTokenCreationResponse());
                            $systemTokenCreationResponseResult = $jsonMapper->map($systemTokenCreationResponse->result, new SystemTokenCreationResponseResult());
                            $systemTokenCreationResponseResult = Utility::cast($systemTokenCreationResponseResult, 'GravityLegal\GravityLegalAPI\SystemTokenCreationResponseResult');
                            $customerApiToken = new CustomerApiToken ();
                            $customerApiToken->APP_ID = $customer->appId;
                            $customerApiToken->ORG_ID = $customer->orgId;
                            $customerApiToken->ENV_URL = $this->getEnvUrl();
                            $customerApiToken->PRAHARI_BASE_URL = $this->getPrahariUrl();
                            $customerApiToken->SYSTEM_TOKEN = $systemTokenCreationResponseResult->token;
                        }
                    }
                }
            }
        }
        return $customerApiToken;
    }

    /// <summary>
    /// string createdSinceDateTime is an optional parameter.
    /// If createdSinceDateTime is provided the method returns only the Customers
    /// who were created at or after the given date-time and have accepted
    /// the invitation by resetting their password
    /// </summary>
    /// <param name="createdSinceDateTime"></param>
    /// <returns></returns>
    public function FetchClients(DateTime $createdSinceDateTime = null): EntityQueryResult   {
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
        $url = $this->getBaseUrl() . 'Client';
        $response = Unirest\Request::get($url, $this->getHttpHeaders(), $query);
        $this->setLastRestResponse($response);
        if ($response->code == 200) {
            $json = json_decode($response->raw_body);
            $jsonMapper = new  JsonMapper();
            $clientResult = $jsonMapper->map($json, new ClientResult());
    
            foreach($clientResult->result->records as $client) {
                $client = $jsonMapper->map($client, new Client());
                $client = Utility::cast($client, 'GravityLegal\GravityLegalAPI\Client');
                $result->FetchedEntities[] = $client;
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
                    $query = array("pageNo" => $fetchPage, "pageSize" => $pageSize);
                    if ($createdSinceDateTime != null) {
                        $createdSince = date_format($createdSinceDateTime, 'Y-m-d\TH:i:s.vT');
                        $query = array("pageNo" => $fetchPage, "pageSize" => $pageSize, "createdOn:range" => "[" . $createdSince . "," .   $createdUntil . "]");
                    }
                    $response = Unirest\Request::get($url, $this->getHttpHeaders(), $query);
                    $this->setLastRestResponse($response);
                    if ($response->code == 200) {
                        $json = json_decode($response->raw_body);
                        $jsonMapper = new  JsonMapper();
                        $clientResult = $jsonMapper->map($json, new ClientResult());
            
                        foreach($clientResult->result->records as $client) {
                            $client = $jsonMapper->map($client, new Client());
                            $client = Utility::cast($client, 'GravityLegal\GravityLegalAPI\Client');
                            $result->FetchedEntities[] = $client;
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
            $this->setLastRestResponse($response);
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
    public function GetClient(string $clientId): ?Client {
        $gravityClient = null;
        $url = $this->getBaseUrl() . 'Client/' . $clientId;
        $response = Unirest\Request::get($url, $this->getHttpHeaders());
        $this->setLastRestResponse($response);
        if ($response->code == 200) {
            $json = json_decode($response->raw_body);
            $jsonMapper = new  JsonMapper();
            $client = $jsonMapper->map($json, new GetClientResult());
            $gravityClient = Utility::cast($client->result, 'GravityLegal\GravityLegalAPI\Client');
        }
        return $gravityClient;
    }


    /// <summary>
    /// Gets the contact by email.
    /// </summary>
    /// <param name="emailAddress">The email address.</param>
    /// <param name="clientId">The client id.</param>
    /// <returns>A Contact.</returns>
    public function GetContactByEmail(string $emailAddress, string $clientId = null): ?Contact  {
        $contact = null;
        $query = null;
        $url = $this->getBaseUrl() . 'Contact';
        if ($clientId == null)
            $query = array('email' => $emailAddress);
        else
            $query = array('email' => $emailAddress, 'client' => $clientId);        
        $response = Unirest\Request::get($url, $this->getHttpHeaders(), $query);
        $this->setLastRestResponse($response);
        if ($response->code == 200) {
            $json = json_decode($response->raw_body);
            $jsonMapper = new  JsonMapper();
            $contactResult = $jsonMapper->map($json, new ContactResult());
            $contactResult = Utility::cast($contactResult, 'GravityLegal\GravityLegalAPI\ContactResult');
            if (count($contactResult->result->records) > 0) {
                $contact = $jsonMapper->map($contactResult->result->records[0], new Contact());
                $contact = Utility::cast($contact, 'GravityLegal\GravityLegalAPI\Contact');
            }
        }
        return $contact;
    }

    /// <summary>
    /// Finds the client by email.
    /// </summary>
    /// <param name="emailAddress">The email address.</param>
    /// <returns>A Client.</returns>
    public function FindClientByEmail(string $emailAddress): ?Client {
        $client = null;
        $contact = null;

        $contact = $this->GetContactByEmail($emailAddress);
        if ($contact == null)
            return null;
        $client = $this->GetClient($contact->client->id);
        return $client;
    }

    /// <summary>
    /// Gets the contact.
    /// </summary>
    /// <param name="contactId">The contact id.</param>
    /// <returns>A Contact.</returns>
    public function GetContact(string $contactId): ?Contact {
        $contact = null;
        $url = $this->getBaseUrl() . 'Contact/' . $contactId;
        $query = array('select' => 'client');
        $response = Unirest\Request::get($url, $this->getHttpHeaders(), $query);
        $this->setLastRestResponse($response);
        if ($response->code == 200) {
            $json = json_decode($response->raw_body);
            $jsonMapper = new  JsonMapper();
            $contactResult = $jsonMapper->map($json, new GetContactResult());
            $contactResult = Utility::cast($contactResult, 'GravityLegal\GravityLegalAPI\GetContactResult');
            $contact = $jsonMapper->map($contactResult->result, new Contact());
            $contact = Utility::cast($contact, 'GravityLegal\GravityLegalAPI\Contact');
        }
        return $contact;
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
        $this->setLastRestResponse($response);
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
                    $this->setLastRestResponse($response);
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
    public function FindOrCreateClient(CreateClient $createClientParam): ?Client {
        $client = null;
        $matchingClients = $this->FindClient($createClientParam->customer, $createClientParam->clientName, false);
        if (count($matchingClients) >= 1)
            $client = $matchingClients[0];
        else if (count($matchingClients) == 0) {
            $url = $this->getBaseUrl() . 'Client';
            $body = json_encode($createClientParam);
            $response = Unirest\Request::post($url, $this->getHttpHeaders(), $body);
            $this->setLastRestResponse($response);
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
        $this->setLastRestResponse($response);
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
        $this->setLastRestResponse($response);
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
        $this->setLastRestResponse($response);
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
        $this->setLastRestResponse($response);
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
            $this->setLastRestResponse($response);
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
    public function FindOrCreateMatter(CreateMatter $createMatter): ?Matter {
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
        $this->setLastRestResponse($response);
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
        $this->setLastRestResponse($response);
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

    /**
     * Get the value of lastRestResponse
     */ 
    public function getLastRestResponse()
    {
        return $this->lastRestResponse;
    }

    /**
     * Set the value of lastRestResponse
     *
     * @return  self
     */ 
    public function setLastRestResponse($lastRestResponse)
    {
        $this->lastRestResponse = $lastRestResponse;

        return $this;
    }

    /**
     * Get the value of envUrl
     */ 
    public function getEnvUrl()
    {
        return $this->envUrl;
    }

    /**
     * Set the value of envUrl
     *
     * @return  self
     */ 
    public function setEnvUrl($envUrl)
    {
        $this->envUrl = $envUrl;

        return $this;
    }

    /**
     * Get the value of prahariUrl
     */ 
    public function getPrahariUrl()
    {
        return $this->prahariUrl;
    }

    /**
     * Set the value of prahariUrl
     *
     * @return  self
     */ 
    public function setPrahariUrl($prahariUrl)
    {
        $this->prahariUrl = $prahariUrl;

        return $this;
    }
}