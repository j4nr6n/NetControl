<?php

namespace App\Gravatar\Enum;

enum ImageSet: string
{
    case NOT_FOUND = '404';
    case MP = 'mp';
    case IDENTICON = 'identicon';
    case MONSTERID = 'monsterid';
    case WAVATAR = 'wavatar';
    case RETRO = 'retro';
    case ROBOHASH = 'robohash';
    case BLANK = 'blank';
}
