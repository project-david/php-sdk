<?php
namespace GravityLegal\GravityLegalAPI;

class CustomerTxnResultResult
{
    public array $records; // This is a list of PaylinkTxn
    public int $totalCount;
    public int $pageNo;
    public int $pageSize;
}

class CustomerTxnResult {
    public CustomerTxnResultResult $result;
}
