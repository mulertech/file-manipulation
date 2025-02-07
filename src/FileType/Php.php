<?php

namespace MulerTech\FileManipulation\FileType;

use MulerTech\FileManipulation\FileManipulation;
use MulerTech\FileManipulation\PathManipulation;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;
use RuntimeException;

/**
 * Class Php - Handles PHP file manipulation and reflection operations
 * @package MulerTech\FileManipulation\FileType
 * @author Sébastien Muler
 */
class Php extends FileManipulation
{
    private const string EXTENSION = 'php';
    private const string NAMESPACE_PREFIX = 'namespace ';
    private const string CLASS_PREFIX = 'class ';

    public function __construct(string $filename)
    {
        parent::__construct($filename, self::EXTENSION);
    }

    /**
     * Returns the fully qualified class name from the PHP file
     * @throws RuntimeException When namespace or class name cannot be found
     * @return class-string
     */
    public function fileClassName(): string
    {
        $namespace = $this->extractNamespace();
        $className = $this->extractClassName();

        /** @var class-string $fqcn */
        $fqcn = $namespace === null ? $className : $namespace . '\\' . $className;

        return $fqcn;
    }

    /**
     * Gets class names from PHP files in a directory
     * @param string $path Directory path to scan
     * @param bool $recursive Whether to scan subdirectories
     * @return array<int, class-string>
     */
    public static function getClassNames(string $path, bool $recursive = false): array
    {
        $classNames = array_map(
            static fn (string $filename) => (new self($filename))->fileClassName(),
            PathManipulation::fileList($path, $recursive)
        );

        sort($classNames);

        return $classNames;
    }

    /**
     * @template T of object
     * @param class-string $class
     * @param class-string<T> $attributeClassName
     * @return ReflectionAttribute<T>|null
     * @throws ReflectionException
     */
    public static function getClassAttributeNamed(string $class, string $attributeClassName): ?ReflectionAttribute
    {
        return (new ReflectionClass($class))->getAttributes($attributeClassName)[0] ?? null;
    }

    /**
     * @param class-string $class
     * @param class-string $attributeClassName
     * @return object|null
     * @throws ReflectionException
     */
    public static function getInstanceOfClassAttributeNamed(string $class, string $attributeClassName): ?object
    {
        return self::getClassAttributeNamed($class, $attributeClassName)?->newInstance();
    }

    /**
     * @param class-string $class
     * @param bool $withParent Include parent class properties
     * @return array<int, ReflectionProperty>
     * @throws ReflectionException
     */
    public static function getPropertiesAttributes(string $class, bool $withParent = false): array
    {
        $reflectionClass = new ReflectionClass($class);
        $properties = $reflectionClass->getProperties();

        if ($withParent) {
            return $properties;
        }

        return array_filter($properties, static fn ($property) => $property->class === $class);
    }

    /**
     * @param class-string $class
     * @param string $attributeClassName
     * @return array<string, object> return [propertyName => mappingObject]
     * @throws ReflectionException
     */
    public static function getInstanceOfPropertiesAttributesNamed(string $class, string $attributeClassName): array
    {
        return self::getAttributeInstances(self::getPropertiesAttributes($class), $attributeClassName);
    }

    /**
     * @param class-string $class
     * @param bool $withParent Include parent class methods
     * @return array<int, ReflectionMethod>
     * @throws ReflectionException
     */
    public static function getMethodsAttributes(string $class, bool $withParent = false): array
    {
        $reflectionClass = new ReflectionClass($class);
        $methods = $reflectionClass->getMethods();

        if ($withParent) {
            return $methods;
        }

        return array_filter($methods, static fn ($method) => $method->class === $class);
    }

    /**
     * @param class-string $class
     * @param string $attributeClassName
     * @return array<string, object> return [methodName => mappingObject]
     * @throws ReflectionException
     */
    public static function getInstanceOfMethodsAttributesNamed(string $class, string $attributeClassName): array
    {
        return self::getAttributeInstances(self::getMethodsAttributes($class), $attributeClassName);
    }

    /**
     * @return string|null
     * @throws RuntimeException
     */
    private function extractNamespace(): ?string
    {
        $namespaceLine = $this->findLineStartsWith(self::NAMESPACE_PREFIX);

        if (is_null($namespaceLine)) {
            return null;
        }

        return rtrim(substr($namespaceLine, strlen(self::NAMESPACE_PREFIX)), ';');
    }

    /**
     * @return string
     * @throws RuntimeException
     */
    private function extractClassName(): string
    {
        $classLine = $this->findLineStartsWith(self::CLASS_PREFIX);

        if (is_null($classLine)) {
            throw new RuntimeException(
                sprintf('File "%s" does not contain a class declaration.', $this->getFilename())
            );
        }

        $className = substr($classLine, strlen(self::CLASS_PREFIX));
        if (str_contains($className, ' ')) {
            return explode(' ', $className)[0];
        }

        return $className;
    }

    /**
     * Helper method to get attribute instances from reflection objects
     * @param array<int, ReflectionMethod|ReflectionProperty> $reflectionObjects
     * @param string $attributeClassName
     * @return array<string, object>
     */
    private static function getAttributeInstances(array $reflectionObjects, string $attributeClassName): array
    {
        $result = [];

        foreach ($reflectionObjects as $object) {
            $attributes = $object->getAttributes($attributeClassName);

            if (isset($attributes[0])) {
                $result[$object->getName()] = $attributes[0]->newInstance();
            }
        }

        return $result;
    }
}
