<?php
/**
 * This file is part of the daikon-cqrs/state-machine project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\StateMachine\Builder;

use Shrink0r\PhpSchema\Factory;
use Shrink0r\PhpSchema\FactoryInterface;
use Shrink0r\PhpSchema\ResultInterface;
use Shrink0r\PhpSchema\Schema;
use Shrink0r\PhpSchema\SchemaInterface;

final class StateMachineSchema implements SchemaInterface
{
    private $internalSchema;

    public function __construct()
    {
        $this->internalSchema = new Schema('state-machine', [
            'type' => 'assoc',
            'properties' => [
                'name' => [ 'type' => 'string' ],
                'class' => [ 'type' => 'fqcn', 'required' => false ],
                'states' => [
                    'type' => 'assoc',
                    'properties' => [ ':any_name:' => $this->getStateSchema() ]
                ]
            ],
            'customTypes' => [ 'transition' => $this->getTransitionSchema() ]
        ], new Factory);
    }

    public function validate(array $data): ResultInterface
    {
        return $this->internalSchema->validate($data);
    }

    public function getName(): string
    {
        return $this->internalSchema->getName();
    }

    public function getType(): string
    {
        return $this->internalSchema->getType();
    }

    public function getCustomTypes(): array
    {
        return $this->internalSchema->getCustomTypes();
    }

    public function getProperties(): array
    {
        return $this->internalSchema->getProperties();
    }

    public function getFactory(): FactoryInterface
    {
        return $this->internalSchema->getFactory();
    }

    private function getStateSchema(): array
    {
        return [
            'type' => 'assoc' ,
            'required' => false,
            'properties' => [
                'class' => [ 'type' => 'fqcn', 'required' => false ],
                'initial' => [
                    'type' => 'bool',
                    'required' => false
                ],
                'final' => [
                    'type' => 'bool',
                    'required' => false
                ],
                'interactive' => [
                    'type' => 'bool',
                    'required' => false
                ],
                'output' => [
                    'type' => 'assoc',
                    'required' => false,
                    'properties' => [
                        ':any_name:' => [ 'type' => 'any' ]
                    ]
                ],
                'input_schema' =>  [
                    'type' => 'assoc',
                    'required' => false,
                    'properties' => [
                        ':any_name:' => [ 'type' => 'any' ]
                    ]
                ],
                'output_schema' =>  [
                    'type' => 'assoc',
                    'required' => false,
                    'properties' => [
                        ':any_name:' => [ 'type' => 'any' ]
                    ]
                ],
                'settings' =>  [
                    'type' => 'assoc',
                    'required' => false,
                    'properties' => [
                        ':any_name:' => [ 'type' => 'any' ]
                    ]
                ],
                'transitions' =>  [
                    'type' => 'assoc',
                    'required' => true,
                    'properties' => [
                        ':any_name:' => [
                            'type' => 'enum' ,
                            'required' => false,
                            'one_of' => [ 'string', '&transition' ]
                        ]
                    ]
                ]
            ]
        ];
    }

    private function getTransitionSchema(): array
    {
        return [
            'type' => 'assoc',
            'properties' => [
                'class' => [ 'type' => 'fqcn', 'required' => false ],
                'settings' =>  [
                    'type' => 'assoc',
                    'required' => false,
                    'properties' => [
                        ':any_name:' => [ 'type' => 'any' ]
                    ],
                ],
                'when' => [
                    'type' => 'any',
                    'required' => false
                ]
            ]
        ];
    }
}
