<?php


namespace MulerTech\FileManipulation\Tests\Files\Mapping;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class MtRoute
{

    /**
     * @var callable
     */
    public $callback;

    /**
     * @param string|null $path
     * @param string|null $name
     * @param string|null $access
     * @param array|null $parameters
     */
    public function __construct(
        public string|null $path = null,
        public string|null $name = null,
        public string|null $access = null,
        public array|null $parameters = null,
    )
    {}

    /**
     * @param string $class
     * @param string $method
     */
    public function setCallMethod(string $class, string $method): void
    {
        $this->callback = [$class, $method];
    }

    /**
     * @return string
     */
    public function getClass(): string
    {
        return $this->callback[0];
    }

    /**
     * @return string
     */
    public function getFunction(): string
    {
        return $this->callback[1];
    }

}