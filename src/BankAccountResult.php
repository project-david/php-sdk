<?php
namespace GravityLegal\GravityLegalAPI;


class BankAccountResultResult {
    public  array $records; // This is a list of BankAccount objects
    public int $totalCount;
    public int $pageNo;
    public int $pageSize;
}
class BankAccountResult    {
        public BankAccountResultResult $result;
}
class GetBankAccountResult {
    public BankAccount $result;
}
