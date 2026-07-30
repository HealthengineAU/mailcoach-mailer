<?php

declare(strict_types=1);

namespace Spatie\MailcoachMailer\Headers;

use Symfony\Component\Mime\Header\UnstructuredHeader;

class GoogleAnalyticsDomainsHeader extends UnstructuredHeader
{
    public function __construct(array $value)
    {
        parent::__construct('X-Mailcoach-Google-Analytics-Domains', json_encode(array_values($value)));
    }
}
