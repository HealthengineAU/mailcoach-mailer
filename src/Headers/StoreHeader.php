<?php

declare(strict_types=1);

namespace Spatie\MailcoachMailer\Headers;

use Symfony\Component\Mime\Header\UnstructuredHeader;

class StoreHeader extends UnstructuredHeader
{
    public function __construct(string $value = '1')
    {
        parent::__construct('X-Mailcoach-Store', $value);
    }
}
