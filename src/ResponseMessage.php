<?php
namespace GravityLegal\GravityLegalAPI;

class Payload {
    public string $id;
}


class ResponseMessage {
        public bool $success;
        public Payload $payload;
}
