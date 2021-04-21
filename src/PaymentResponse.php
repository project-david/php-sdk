<?php
namespace GravityLegal\GravityLegalAPI;

class PaymentResponseResult {
    public array $records; // array of Payment
    public int $totalCount;
    public int $pageNo;
    public int $pageSize;
}

class PaymentResponse {
    public PaymentResponseResult $result;
}

