<?php
namespace GravityLegal\GravityLegalAPI;

class QBAccountsInfo
{
    public string $customerId;
    public string $accountId;
    public string $depositToAccountId;
}

class AdditionalData
{
    public string $tag;
    public QBAccountsInfo $qbAccountsInfo;
}
