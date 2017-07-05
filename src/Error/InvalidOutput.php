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

class InvalidOutput extends DomainException implements ErrorInterface
{
    private $validation_errors;

    public function __construct(array $validationErrors, $msg = '')
    {
        $this->validation_errors = $validationErrors;
        parent::__construct($msg.PHP_EOL.$this);
    }

    public function getValidationErrors()
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
