<?php
namespace GravityLegal\GravityLegalAPI;

use DateTime;
use Unirest;

class GravityLegalService
{
    protected array $httpHeaders;
    protected string $baseUrl='';
    protected string $prahariBaseUrl;
    protected string $operationsUrl;
    protected $jokes = [
        'Chuck Norris doesn\’t read books. He stares them down until he gets the information he wants.',
        'Time waits for no man. Unless that man is Chuck Norris.',
        'When God said, “Let there be light!” Chuck said, “Say Please.”'
    ];

    public function __construct(array $envVariables=null)
    {
        if ($envVariables) {
            $PRAHARI_BASE_URL=$envVariables['PRAHARI_BASE_URL'];
            $ENV_URL=$envVariables['ENV_URL'];
            $SYSTEM_TOKEN=$envVariables['SYSTEM_TOKEN'];
            $APP_ID=$envVariables['APP_ID'];
            $ORG_ID=$envVariables['ORG_ID'];
        };

        foreach($envVariables as $key => $value) {
            echo "$key : $value \n";
        }

        $this->setBaseUrl($ENV_URL . '/entities/');
        $httpHeaders = array(
            'Authorization' => 'Bearer ' . $SYSTEM_TOKEN,
            'X-PRAHARI-APPID' => $APP_ID,
            'X-PRAHARI-ORGID' => $ORG_ID,
            'Content-Type' => 'application/json'
        );
        $this->setHttpHeaders($httpHeaders);
        foreach($httpHeaders as $key => $value) {
            echo "$key : $value \n";
        }
    }
    public function GetUserByEmail(string $emailAddress)
    {
        $url = $this->getBaseUrl() . 'User';
        echo $url;    
        $query = array("email" => $emailAddress, "select" => "customer,customer.partner,fullName","sysGen:not" => "true");
        foreach($query as $key => $value) {
            echo "$key : $value \n";
        }
        //$body = Unirest\Request\Body::json($query);
        $response = Unirest\Request::get($url, $this->getHttpHeaders(), $query);
        var_dump($response->headers);     // Headers
        var_dump($response->body);        // Parsed body
        echo $response->raw_body;    // Unparsed body
        return $response->raw_body;
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
}