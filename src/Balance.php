<?php
namespace GravityLegal\GravityLegalAPI;
use DateTime;


    class Balance {
        public string $id;
        public DateTime $createdOn;
        public DateTime $updatedOn;
        public bool $deleted;
        public int $operatingOutstanding;
        public int $operatingPaid;
        public int $totalOutstanding;
        public int $totalPaid;
        public int $trustOutstanding;
        public int $trustPaid;
    }
