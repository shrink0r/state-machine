<?php
/**
 * This file is part of the daikon-cqrs/state-machine project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\StateMachine\Exception;

use DomainException;

class InvalidInput extends DomainException implements ExceptionInterface
{
    private $validationErrors;

    public function __construct(array $validationErrors, string $msg = '')
    {
        $this->validationErrors = $validationErrors;
        parent::__construct($msg.PHP_EOL.$this);
    }

    public function getValidationErrors(): array
    {
        return $this->validationErrors;
    }

    public function __toString(): string
    {
        $errors = [];
        foreach ($this->getValidationErrors() as $propName => $errors) {
            $errors[] = $propName.": ".implode(', ', $errors);
        }
        return implode("\n", $errors);
    }
}
