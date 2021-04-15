<?php
namespace GravityLegal\GravityLegalAPI;

class ReferencedBy  {
        public string $entity;
        public array $ids; // This is a list of string
}

class Reference {
        public string $entity;
        public array $ids; // This is a list of string
}
class ClientInstanceInfoResponseResult {
        public string $entity;
        public string $id;
        public array $contains; // This is a list of string
        public bool $isPrimary;
        public array $referencedBy; // This is a list of ReferencedBy
        public array $references; // This is a list of Reference
}

class ClientInstanceInfoResponse {
        public ClientInstanceInfoResponseResult $result;
}
