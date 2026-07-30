<?php

declare(strict_types=1);

namespace Spatie\MailcoachMailer\Headers;

use Symfony\Component\Mime\Header\UnstructuredHeader;

class UtmCampaignHeader extends UnstructuredHeader
{
    public function __construct(string $value)
    {
        parent::__construct('X-Mailcoach-Utm-Campaign', $value);
    }
}
