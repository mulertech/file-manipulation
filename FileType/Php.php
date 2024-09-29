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
 * Class Php
 * @package MulerTech\FileManipulation\FileType
 * @author Sébastien Muler
 */
class Php extends FileManipulation
{
    private const string EXTENSION = 'php';

    /**
     * @param string $filename
     */
    public function __construct(string $filename)
    {
        parent::__construct($filename, self::EXTENSION);
    }

    /**
     * Return the complete class name of this file.
     * @return string
     */
    public function fileClassName(): string
    {
        $namespaceLine = $this->findLineStartsWith('namespace ');

        if (is_null($namespaceLine)) {
            throw new RuntimeException(
                sprintf(
                    'Class FileManipulation, function fileClassName. The file "%s" does not contain namespace.',
                    $this->getFilename()
                )
            );
        }

        $namespace = rtrim(ltrim($namespaceLine, 'namespace '), ';');

        $classNameLine = $this->findLineStartsWith('class ');

        if (is_null($classNameLine)) {
            throw new RuntimeException(
                sprintf(
                    'Class FileManipulation, function fileClassName. The file "%s" does not contain class name.',
                    $this->getFilename()
                )
            );
        }

        $className = ltrim($this->findLineStartsWith('class '), 'class ');
        if (str_contains($className, ' ')) {
            $className = explode(' ', $className)[0];
        }

        return $namespace . '\\' . $className;
    }

    /**
     * @param string $path
     * @param bool $recursive
     * @return array
     */
    public static function getClassNames(string $path, bool $recursive = false): array
    {
        $fileList = PathManipulation::fileList($path, $recursive);

        $classNames = [];
        foreach ($fileList as $filename) {
            $php = new Php($filename);
            $classNames[] = $php->fileClassName();
        }

        sort($classNames);

        return $classNames;
    }

    /**
     * @param string $class
     * @param class-string $attributeClassName
     * @return ReflectionAttribute|null
     * @throws ReflectionException
     */
    public static function getClassAttributeNamed(string $class, string $attributeClassName): ?ReflectionAttribute
    {
        return (new ReflectionClass($class))->getAttributes($attributeClassName)[0] ?? null;
    }

    /**
     * @param string $class
     * @param class-string $attributeClassName
     * @return object|null
     * @throws ReflectionException
     */
    public static function getInstanceOfClassAttributeNamed(string $class, string $attributeClassName): ?object
    {
        return self::getClassAttributeNamed($class, $attributeClassName)?->newInstance();
    }

    /**
     * @param string $class
     * @param bool $withParent
     * @return array<int, ReflectionProperty>
     * @throws ReflectionException
     */
    public static function getPropertiesAttributes(string $class, bool $withParent = false): array
    {
        $reflectionClass = new ReflectionClass($class);
        $classAndParentProperties = $reflectionClass->getProperties();

        if ($withParent) {
            return $classAndParentProperties;
        }

        return array_filter($classAndParentProperties, static function ($property) use ($class) {
            return $property->class === $class;
        });
    }

    /**
     * @param string $class
     * @param string $attributeClassName
     * @return array<string, object> return [propertyName => mappingObject]
     * @throws ReflectionException
     */
    public static function getInstanceOfPropertiesAttributesNamed(string $class, string $attributeClassName): array
    {
        $properties = self::getPropertiesAttributes($class);

        $result = [];
        foreach ($properties as $property) {
            $attributes = $property->getAttributes($attributeClassName);


            if (!isset($attributes[0])) {
                continue;
            }

            $result[$property->getName()] = $attributes[0]->newInstance();
        }

        return $result;
    }

    /**
     * @param string $class
     * @param bool $withParent
     * @return array<int, ReflectionMethod>
     * @throws ReflectionException
     */
    public static function getMethodsAttributes(string $class, bool $withParent = false): array
    {
        $reflectionClass = new ReflectionClass($class);
        $classAndParentMethods = $reflectionClass->getMethods();

        if ($withParent) {
            return $classAndParentMethods;
        }

        return array_filter($classAndParentMethods, static function ($method) use ($class) {
            return $method->class === $class;
        });
    }

    /**
     * @param string $class
     * @param string $attributeClassName
     * @return array<string, object> return [methodName => mappingObject]
     * @throws ReflectionException
     */
    public static function getInstanceOfMethodsAttributesNamed(string $class, string $attributeClassName): array
    {
        $methods = self::getMethodsAttributes($class);

        $result = [];
        foreach ($methods as $method) {
            $attributes = $method->getAttributes($attributeClassName);


            if (!isset($attributes[0])) {
                continue;
            }

            $result[$method->getName()] = $attributes[0]->newInstance();
        }

        return $result;
    }
}