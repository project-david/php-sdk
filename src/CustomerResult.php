<?php
namespace GravityLegal\GravityLegalAPI;

class CustomerResultResult
{
    public array $records; // This is a list of Customer
    public int $totalCount;
    public int $pageNo;
    public int $pageSize;
}
class CustomerResult {
    public CustomerResultResult $result;
}
