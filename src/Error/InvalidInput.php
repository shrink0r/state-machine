<?php
/**
 * This file is part of the daikon-cqrs/state-machine project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\StateMachine\Error;

use DomainException;
use Daikon\StateMachine\Error\ErrorInterface;

class InvalidInput extends DomainException implements ErrorInterface
{
    private $validation_errors;

    public function __construct(array $validation_errors, string $msg = '')
    {
        $this->validation_errors = $validation_errors;
        parent::__construct($msg.PHP_EOL.$this);
    }

    public function getValidationErrors(): array
    {
        return $this->validation_errors;
    }

    public function __toString(): string
    {
        $errors = [];
        foreach ($this->getValidationErrors() as $prop_name => $errors) {
            $errors[] = $prop_name.": ".implode(', ', $errors);
        }
        return implode("\n", $errors);
    }
}
