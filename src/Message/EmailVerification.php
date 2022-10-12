<?php

namespace App\Message;

class EmailVerification implements AsyncMessageInterface
{
    public function __construct(
        readonly private int $userId
    ) {
    }

    public function getUserId(): int
    {
        return $this->userId;
    }
}
