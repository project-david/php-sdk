<?php
namespace GravityLegal\GravityLegalAPI;

class SystemTokenRequestResponseResult {
    public array $records; //array of SystemTokenRecord
    public int $totalCount;
    public int $pageNo;
    public int $pageSize;
}

class SystemTokenRequestResponse  {
    public SystemTokenRequestResponseResult $result;
}
