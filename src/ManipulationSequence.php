<?php

namespace Spatie\Image;

use ArrayIterator;
use IteratorAggregate;

class ManipulationSequence implements IteratorAggregate
{
    /** @var array */
    protected $groups = [];

    /** @var bool  */
    protected $startNewGroup = true;

    /**
     * @param string $operation
     * @param string $argument
     *
     * @return static
     */
    public function addManipulation(string $operation, string $argument)
    {
        if ($this->startNewGroup) {
            $this->groups[] = [];
        }

        $lastIndex = count($this->groups) - 1;

        $this->groups[$lastIndex][$operation] = $argument;

        $this->startNewGroup = false;

        return $this;
    }

    public function merge(ManipulationSequence $manipulationSets)
    {
        foreach($manipulationSets->toArray() as $manipulationSet) {
            foreach($manipulationSet as $name => $argument) {
                $this->addManipulation($name, $argument);
            }

            if(next($manipulationSets)) {
                $this->startNewGroup();
            }
        }
    }

    /**
     * @return static
     */
    public function startNewGroup()
    {
        $this->startNewGroup = true;

        return $this;
    }

    public function toArray(): array
    {
        return $this->getGroups();
    }

    public function getGroups(): array
    {
        return $this->sanitizeManipulationSets($this->groups);
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->groups);
    }

    public function removeManipulation($manipulationName)
    {
        foreach($this->groups as &$group) {
            if (array_key_exists($manipulationName, $group)) {
                unset($group[$manipulationName]);
            }
        }

        return $this;
    }

    protected function sanitizeManipulationSets(array $groups): array
    {
        return array_values(array_filter($groups, function(array $manipulationSet) {
            return count($manipulationSet);
        }));
    }
}