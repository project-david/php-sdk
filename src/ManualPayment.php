<?php
namespace GravityLegal\GravityLegalAPI;

class Statement {
    public string $description;
}


class ManualPayment    {
    public Operating $operating;
    public Trust $trust;
    public Statement $statement;
    public string $paidBy;
    public string $payerEmail;
    public bool $sendReceiptEmail;
}
