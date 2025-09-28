<?php

namespace TextFly\Sdk\Exceptions;

class RateLimitException extends ApiException
{
    private ?int $retryAfterSeconds;

    private ?int $retryAtTimestamp;

    public function __construct(string $message, int $code = 0, ?\Throwable $previous = null, ?int $retryAfterSeconds = null, ?int $retryAtTimestamp = null)
    {
        parent::__construct($message, $code, $previous);

        $this->retryAfterSeconds = $retryAfterSeconds;
        $this->retryAtTimestamp = $retryAtTimestamp;
    }

    public function getRetryAfterSeconds(): ?int
    {
        return $this->retryAfterSeconds;
    }

    public function getRetryAt(): ?int
    {
        if ($this->retryAtTimestamp !== null) {
            return $this->retryAtTimestamp;
        }

        if ($this->retryAfterSeconds !== null) {
            return time() + $this->retryAfterSeconds;
        }

        return null;
    }
}
