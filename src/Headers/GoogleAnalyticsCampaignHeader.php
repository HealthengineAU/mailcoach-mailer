<?php

declare(strict_types=1);

namespace Spatie\MailcoachMailer\Headers;

use Symfony\Component\Mime\Header\UnstructuredHeader;

class GoogleAnalyticsCampaignHeader extends UnstructuredHeader
{
    public function __construct(string $value)
    {
        parent::__construct('X-Mailcoach-Google-Analytics-Campaign', $value);
    }
}
