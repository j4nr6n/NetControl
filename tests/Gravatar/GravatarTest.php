<?php

namespace App\Tests\Gravatar;

use App\Gravatar\Gravatar;
use PHPUnit\Framework\TestCase;

class GravatarTest extends TestCase
{
    public function testItReturnsAUrl(): void
    {
        $url = Gravatar::getUrl('foo@example.com');

        self::assertSame($url, filter_var($url, FILTER_VALIDATE_URL));
    }
}
