<?php

/*
 * This file is part of Symfony Boilerplate.
 *
 * (c) Saif Eddin Gmati
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Exception;

use InvalidArgumentException;
use Psl\Str;
use Throwable;

final class UnsupportedMimeTypeException extends InvalidArgumentException implements ExceptionInterface
{
    /**
     * @psalm-var list<string>
     */
    private array $supportedMimeTypes;

    private string $suppliedMimeType;

    /**
     * @psalm-param list<string> $supportedMimeTypes
     */
    public function __construct(array $supportedMimeTypes, ?string $suppliedMimeType, int $code = 0, Throwable $previous = null)
    {
        $this->supportedMimeTypes = $supportedMimeTypes;
        $this->suppliedMimeType = $suppliedMimeType ?? '(unknown)';
        parent::__construct(Str\format(
            'Unsupported mime type "%s", only the following mime types are supported: %s.',
            $this->suppliedMimeType,
            Str\join($supportedMimeTypes, ',')
        ), $code, $previous);
    }

    /**
     * @psalm-return list<string>
     */
    public function getSupportedMimeTypes(): array
    {
        return $this->supportedMimeTypes;
    }

    public function getSuppliedMimeType(): string
    {
        return $this->suppliedMimeType;
    }
}
