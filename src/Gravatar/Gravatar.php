<?php

namespace App\Gravatar;

use App\Gravatar\Enum\ImageSet;
use App\Gravatar\Enum\Rating;

class Gravatar
{
    /**
     * @param string $email The email address
     * @param int $size Size in pixels, defaults to 80px [ 1 - 2048 ]
     * @param ImageSet $imageSet Default imageset to use
     * @param Rating $rating Maximum rating (inclusive)
     */
    public static function getUrl(
        string $email,
        int $size = 80,
        ImageSet $imageSet = ImageSet::ROBOHASH,
        Rating $rating = Rating::G
    ): string {
        return sprintf(
            'https://www.gravatar.com/avatar/%s?s=%d&d=%s&r=%s',
            md5(strtolower(trim($email))),
            $size,
            $imageSet->value,
            $rating->value
        );
    }
}
