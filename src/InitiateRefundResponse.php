<?php
namespace GravityLegal\GravityLegalAPI;

class InitiateRefundResponseResult {
    public string $refundType;
    public bool $success;
}

class InitiateRefundResponse {
    public InitiateRefundResponseResult $result;
}
