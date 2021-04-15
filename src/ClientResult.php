<?php
namespace GravityLegal\GravityLegalAPI;

class ClientResultResult {
    public array $records; // This needs to be a list of Client
    public int $totalCount;
    public int $pageNo;
    public int $pageSize;
}

class ClientResult {
    public ClientResultResult $result;
}

class GetClientResult
{
    public Client $result;
}
