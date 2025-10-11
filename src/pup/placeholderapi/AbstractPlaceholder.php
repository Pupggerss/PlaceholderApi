<?php

namespace pup\placeholderapi;

abstract class AbstractPlaceholder implements Placeholder
{
    public function __construct(
        private readonly string $identifier
    ) {}

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function requiresPlayer(): bool
    {
        return false;
    }

    public function supportsParameters(): bool
    {
        return false;
    }
}
