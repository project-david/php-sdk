<?php
namespace GravityLegal\GravityLegalAPI;


class OpenUserAccountResponseResult {
    public string $email;
    public string $firstName;
    public string $lastName;
    public string $userId;
    public string $subId;
    public string $appId;
    public string $orgId;
    public string $orgName;
    public string $uiId;
    public array $roleIds; // Array of strings
    public string $roleScope;
    public array $orgs; // Array of Org objects
    public string $picture;
    public Customer $customer;
    public bool $showWelcomMsg;
    public User $user;
}


class OpenUserAccountResponse {
    public OpenUserAccountResponseResult $result;
}
