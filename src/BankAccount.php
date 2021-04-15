<?php
namespace GravityLegal\GravityLegalAPI;

class DefaultDepositAccounts {
        public string $operating;
        public string $trust;
}
class BankAccount {
    public string $id;
    public string $accountHolderName;
    public string $accountNumber;
    public string $accountCategory;
    public string $accountType;
    public string $nickname;
    public int $paymentPriority;
}