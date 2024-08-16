<?php

namespace App\Enums;

/**
 * Enum ProjectStatus
 *
 * @package Matrix\Domain\Enums
 */
enum ChannelType: string
{
    case SMS = 'sms';
    case MAIL = 'mail';
    case DATABASE = 'database';
}
