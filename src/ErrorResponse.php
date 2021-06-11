<?php
declare(strict_types=1);

namespace Wumvi\Errors;

class ErrorResponse
{
    public function __construct(
        protected string $name,
        protected string $hint = ''
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getHint(): string
    {
        return $this->hint;
    }
}
