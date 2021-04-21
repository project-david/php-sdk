<?php
namespace GravityLegal\GravityLegalAPI;


class PaymentTxnResultResult {
    public array $records; // Array of PaymentTxnRecord
    public int $totalCount;
    public int $pageNo;
    public int $pageSize;
}


class PaymentTxnResult {
        public PaymentTxnResultResult $result;
}
