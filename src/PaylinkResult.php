<?php
namespace GravityLegal\GravityLegalAPI;

class PaylinkResultResult {
    public array $records; // array of Paylink
    public int $totalCount;
    public int $pageNo;
    public int $pageSize;
}

class PaylinkResult {
    public PaylinkResultResult $result;
}
class GetPaylinkResult
{
    public Paylink $result;
}