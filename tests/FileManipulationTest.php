<?php

namespace MulerTech\FileManipulation\Tests;

use JsonException;
use MulerTech\FileManipulation\FileManipulation;
use MulerTech\FileManipulation\FileType\Env;
use MulerTech\FileManipulation\FileType\Json;
use MulerTech\FileManipulation\FileType\Php;
use MulerTech\FileManipulation\FileType\Yaml;
use MulerTech\FileManipulation\Tests\Files\ContainMtRoutes;
use MulerTech\FileManipulation\Tests\Files\Entity\Group;
use MulerTech\FileManipulation\Tests\Files\Entity\Groups;
use MulerTech\FileManipulation\Tests\Files\Entity\ParentUser;
use MulerTech\FileManipulation\Tests\Files\Entity\Unit;
use MulerTech\FileManipulation\Tests\Files\Entity\User;
use MulerTech\FileManipulation\Tests\Files\Entity\WithoutMapping;
use MulerTech\FileManipulation\Tests\Files\FakeClass;
use MulerTech\FileManipulation\Tests\Files\Mapping\MtColumn;
use MulerTech\FileManipulation\Tests\Files\Mapping\MtEntity;
use MulerTech\FileManipulation\Tests\Files\Mapping\MtRoute;
use MulerTech\FileManipulation\Tests\Files\Repository\UserRepository;
use PHPUnit\Framework\TestCase;
use ReflectionAttribute;
use ReflectionException;
use ReflectionProperty;
use RuntimeException;

class FileManipulationTest extends TestCase
{

    private const array TEST_ARRAY = [
        'fruits' => [
            [
                'kiwis' => 3,
                'mangues' => 4,
                'bananes' => null
            ],
            [
                'panier' => true
            ]
        ],
        'legumes' => [
            'patates' => 'amandine',
            'poireaux' => false
        ],
        'viandes' => [
            'poisson',
            'poulet',
            'boeuf'
        ]
    ];

    private const string TEST_MULTILINE = 'test brut file' . PHP_EOL . 'second line' . PHP_EOL . 'third line';

    private const string TEST_MULTILINE_WITH_INSERT = 'test brut file' . PHP_EOL .
    'second line' . PHP_EOL .
    'inserted line' . PHP_EOL .
    'third line';

    private const string TEST_MULTILINE_TO_INSERT = 'first insert line' . PHP_EOL . 'second insert line' . PHP_EOL . 'third insert line';

    private const string TEST_MULTILINE_WITH_MULTILINE_INSERT = 'test brut file' . PHP_EOL .
    'second line' . PHP_EOL .
    'first insert line' . PHP_EOL .
    'second insert line' . PHP_EOL .
    'third insert line' . PHP_EOL .
    'third line';

    private const string VALUE5 = 'multiline' . PHP_EOL . 'simple quote' . PHP_EOL . 'value' . PHP_EOL;

    private const string VALUE6 = 'multiline' . PHP_EOL . 'double quote' . PHP_EOL . 'value' . PHP_EOL;

    private const string TEST_MULTILINE_WITH_INSERT_NEW_LINE = 'test brut file' . PHP_EOL . 'second line' . PHP_EOL . PHP_EOL . 'third line';

    // Test env file
    public function testParseEnvFile(): void
    {
        $envFile = new Env(
            __DIR__ . DIRECTORY_SEPARATOR . 'Files' . DIRECTORY_SEPARATOR . 'test.env'
        );
        self::assertEquals(
            [
                'key1' => 'value1',
                'key2' => 'value2',
                'key3' => 'value3',
                'key4' => 'value4',
                'key5' => self::VALUE5,
                'key6' => self::VALUE6,
                'key7' => 'value7',
                'key8' => 'value8',
                'key9' => ''
            ],
            $envFile->parseFile()
        );
    }

    public function testParseEnvFileWithNoFile(): void
    {
        $envFile = new Env('nope.env');
        self::assertNull($envFile->parseFile());
    }

    public function testLoadEnv(): void
    {
        $envFile = new Env(
            __DIR__ . DIRECTORY_SEPARATOR . 'Files' . DIRECTORY_SEPARATOR . 'test.env'
        );
        $envFile->loadEnv();
        self::assertEquals('value1', getenv('key1'));
        self::assertEquals('value2', getenv('key2'));
        self::assertEquals('value3', getenv('key3'));
        self::assertEquals('value4', getenv('key4'));
        self::assertEquals(self::VALUE5, getenv('key5'));
        self::assertEquals(self::VALUE6, getenv('key6'));
        self::assertEquals('value7', getenv('key7'));
        self::assertEquals('value8', getenv('key8'));
        self::assertEquals('', getenv('key9'));
    }

    // Test of FileExtension : Json

    /**
     * @throws JsonException
     */
    public function testJsonSaveAndOpenFile(): void
    {
        $jsonTestFile = new Json(
            __DIR__ . DIRECTORY_SEPARATOR . 'Files' . DIRECTORY_SEPARATOR . 'jsonTest.json'
        );
        self::assertTrue($jsonTestFile->saveFile(self::TEST_ARRAY));
        self::assertEquals(self::TEST_ARRAY, $jsonTestFile->openFile());
    }

    /**
     * @throws JsonException
     */
    public function testJsonOpenEmptyFile(): void
    {
        $jsonTestFile = new Json(
            __DIR__ . DIRECTORY_SEPARATOR . 'Files' . DIRECTORY_SEPARATOR . 'empty.json'
        );
        self::assertEquals(null, $jsonTestFile->openFile());
    }

    /**
     * @throws JsonException
     */
    public function testJsonSaveAndOpenNewFile(): void
    {
        if (realpath(__DIR__ . DIRECTORY_SEPARATOR . 'Files' . DIRECTORY_SEPARATOR . 'jsonTest.json') !== false) {
            unlink(__DIR__ . DIRECTORY_SEPARATOR . 'Files' . DIRECTORY_SEPARATOR . 'jsonTest.json');
        }
        $jsonTestFile = new Json(
            __DIR__ . DIRECTORY_SEPARATOR . 'Files' . DIRECTORY_SEPARATOR . 'jsonTest.json'
        );
        self::assertTrue($jsonTestFile->saveFile(self::TEST_ARRAY));
        self::assertEquals(self::TEST_ARRAY, $jsonTestFile->openFile());
    }

    // Test of FileExtension : Php
    public function testPhpFileClassName(): void
    {
        $fakeClassFile = new Php(
            __DIR__ . DIRECTORY_SEPARATOR . 'Files' . DIRECTORY_SEPARATOR . 'FakeClass.php'
        );
        self::assertEquals(
            FakeClass::class,
            $fakeClassFile->fileClassName()
        );
    }

    public function testPhpFileClassNameWithoutNamespace(): void
    {
        $filename = __DIR__ . DIRECTORY_SEPARATOR . 'Files' . DIRECTORY_SEPARATOR . 'FileWithoutNamespace.php';
        $fakeClassFile = new Php($filename);
        $this->expectExceptionMessage('Class FileManipulation, function fileClassName. The file "' . $filename . '" does not contain namespace.');
        $fakeClassFile->fileClassName();
    }

    public function testPhpFileClassNameWithoutClass(): void
    {
        $filename = __DIR__ . DIRECTORY_SEPARATOR . 'Files' . DIRECTORY_SEPARATOR . 'FileWithoutClass.php';
        $fakeClassFile = new Php($filename);
        $this->expectExceptionMessage('Class FileManipulation, function fileClassName. The file "' . $filename . '" does not contain class name.');
        $fakeClassFile->fileClassName();
    }

    public function testPhpFileClassNameWithClassDoesntExist(): void
    {
        $filename = __DIR__ . DIRECTORY_SEPARATOR . 'Files' . DIRECTORY_SEPARATOR . 'ClassDoesntExist.php';
        $fakeClassFile = new Php($filename);
        $this->expectExceptionMessage('Class FileManipulation, function fileClassName. The class "MulerTech\FileManipulation\Tests\Files\BadNamespace\ClassDoesntExist" does not exist.');
        $fakeClassFile->fileClassName();
    }

    public function testPhpFileClassNameWithFileNotFound(): void
    {
        $filename = __DIR__ . DIRECTORY_SEPARATOR . 'Files' . DIRECTORY_SEPARATOR . 'Nope.php';
        $fakeClassFile = new Php($filename);
        $this->expectExceptionMessage('The file "' . $filename . '" does not exist.');
        $fakeClassFile->fileClassName();
    }

    public function testPhpGetClassNames(): void
    {
        $classNames = Php::getClassNames(__DIR__ . DIRECTORY_SEPARATOR . 'Files' . DIRECTORY_SEPARATOR . 'Entity');
        self::assertEquals(
            [
                Group::class,
                Groups::class,
                ParentUser::class,
                Unit::class,
                User::class,
                WithoutMapping::class
            ],
            $classNames
        );
    }

    /**
     * @throws ReflectionException
     */
    public function testPhpGetClassAttributeNamed(): void
    {
        $ClassAttribute = Php::getClassAttributeNamed(User::class, MtEntity::class);
        self::assertInstanceOf(MtEntity::class, $ClassAttribute->newInstance());
        self::assertEquals(UserRepository::class, $ClassAttribute->newInstance()->repository);
        self::assertNull(Php::getClassAttributeNamed(User::class, MtColumn::class));
    }

    /**
     * @throws ReflectionException
     */
    public function testPhpGetInstanceOfClassAttributeNamed(): void
    {
        $ClassAttribute = Php::getInstanceOfClassAttributeNamed(User::class, MtEntity::class);
        self::assertInstanceOf(MtEntity::class, $ClassAttribute);
        self::assertEquals(UserRepository::class, $ClassAttribute->repository);
        self::assertNull(Php::getInstanceOfClassAttributeNamed(User::class, MtColumn::class));
    }

    /**
     * @throws ReflectionException
     */
    public function testPhpGetPropertiesAttributes(): void
    {
        $properties = Php::getPropertiesAttributes(User::class);
        self::assertCount(3, $properties);
        $reflectionProperty = $properties[1];
        self::assertInstanceOf(ReflectionProperty::class, $reflectionProperty);
        $reflectionAttribute = $reflectionProperty->getAttributes(MtColumn::class)[0];
        self::assertInstanceOf(ReflectionAttribute::class, $reflectionAttribute);
        self::assertEquals('John', $reflectionAttribute->newInstance()->columnDefault);
        self::assertEquals([], Php::getPropertiesAttributes(FakeClass::class));
        $propertiesWithParent = Php::getPropertiesAttributes(User::class, true);
        self::assertCount(3, $propertiesWithParent);
    }

    /**
     * @throws ReflectionException
     */
    public function testPhpGetInstanceOfPropertiesAttributesNamed(): void
    {
        $properties = Php::getInstanceOfPropertiesAttributesNamed(User::class, MtColumn::class);
        self::assertCount(3, $properties);
        self::assertInstanceOf(MtColumn::class, $properties['username']);
        self::assertEquals('John', $properties['username']->columnDefault);
        self::assertEquals(
            [],
            Php::getInstanceOfPropertiesAttributesNamed(FakeClass::class, MtColumn::class)
        );
    }

    /**
     * @throws ReflectionException
     */
    public function testPhpGetInstanceOfPropertiesAttributesNamedWithNoAttribute(): void
    {
        self::assertEquals(
            [],
            Php::getInstanceOfPropertiesAttributesNamed(User::class, MtEntity::class)
        );
    }

    /**
     * @throws ReflectionException
     */
    public function testPhpGetMethodsAttributes(): void
    {
        $methods = Php::getMethodsAttributes(ContainMtRoutes::class);
        self::assertCount(4, $methods);
        $this->assertEquals('home', $methods[0]->name);
        $methodsWithParent = Php::getMethodsAttributes(ContainMtRoutes::class, true);
        self::assertCount(5, $methodsWithParent);
    }

    /**
     * @throws ReflectionException
     */
    public function testPhpGetInstanceOfMethodsAttributesNamed(): void
    {
        $methods = Php::getInstanceOfMethodsAttributesNamed(
            ContainMtRoutes::class,
            MtRoute::class
        );
        self::assertCount(3, $methods);
        self::assertEquals('home.page', $methods['home']->name);
        self::assertEquals(1, $methods['news']->access);
        self::assertEquals('index.logout', $methods['logout']->name);
    }

    // Test of FileExtension : Yaml
    public function testYamlSaveAndOpenFile(): void
    {
        $yamlTestFile = new Yaml(
            __DIR__ . DIRECTORY_SEPARATOR . 'Files' . DIRECTORY_SEPARATOR . 'yamlTest.yaml'
        );
        self::assertTrue($yamlTestFile->saveFile(self::TEST_ARRAY));
        self::assertEquals(self::TEST_ARRAY, $yamlTestFile->openFile());
    }

    public function testYamlOpenEmptyFile(): void
    {
        $yamlTestFile = new Yaml(
            __DIR__ . DIRECTORY_SEPARATOR . 'Files' . DIRECTORY_SEPARATOR . 'empty.yml'
        );
        self::assertEquals(null, $yamlTestFile->openFile());
    }

    public function testYamlOpenNonExistingFileAndThrowException(): void
    {
        $this->expectExceptionMessage('The file "yamlTest.nope" does not exist.');
        $yamlTestNopeFile = new Yaml('yamlTest.nope');
        $yamlTestNopeFile->openFile();
    }

    // Test of FileManipulation
    public function testCheckExtensionWithMultipleFormat(): void
    {
        $yamlFile = new Yaml('test.yaml');
        self::assertTrue($yamlFile->checkExtension());
        $yamlFile = new Yaml('test.yml');
        self::assertTrue($yamlFile->checkExtension());
    }

    public function testCheckExtensionWithSingleFormat(): void
    {
        $jsonFile = new Json('test.json');
        self::assertTrue($jsonFile->checkExtension());
    }

    public function testCheckYamlExtensionWithException(): void
    {
        $yamlFile = new Yaml('test.nope');
        $this->expectExceptionMessage(
            'Class FileManipulation, function checkExtension. The given filename does not have the yml or yaml extension.'
        );
        $yamlFile->checkExtension();
    }

    public function testCheckJsonExtensionWithException(): void
    {
        $jsonFile = new Json('test.nope');
        $this->expectExceptionMessage(
            'Class FileManipulation, function checkExtension. The given filename does not have the json extension.'
        );
        $jsonFile->checkExtension();
    }

    public function testExists(): void
    {
        $brutFile = new FileManipulation(
            __DIR__ . DIRECTORY_SEPARATOR . 'Files' . DIRECTORY_SEPARATOR . 'brut.file'
        );
        self::assertTrue($brutFile->exists());
    }

    public function testOpenFileAndThrowException(): void
    {
        $this->expectExceptionMessage('The file "nope.file" does not exist.');
        $nopeFile = new FileManipulation('nope.file');
        $nopeFile->openFile();
    }

    public function testOpenFile(): void
    {
        $brutFile = new FileManipulation(
            __DIR__ . DIRECTORY_SEPARATOR . 'Files' . DIRECTORY_SEPARATOR . 'brut.file'
        );
        self::assertEquals('test brut file', $brutFile->openFile());
    }

    public function testFirstOccurrenceOfPhpFile(): void
    {
        $fakeClassFile = new Php(
            __DIR__ . DIRECTORY_SEPARATOR . 'Files' . DIRECTORY_SEPARATOR . 'FakeClass.php'
        );
        self::assertEquals(4, $fakeClassFile->firstOccurrence('FakeClass'));
    }

    public function testFirstOccurrenceOfBrutFile(): void
    {
        $fakeClassFile = new FileManipulation(
            __DIR__ . DIRECTORY_SEPARATOR . 'Files' . DIRECTORY_SEPARATOR . 'multilinesOccurrence.file'
        );
        self::assertEquals(2, $fakeClassFile->firstOccurrence('third line'));
    }

    public function testFirstOccurrenceNotFound(): void
    {
        $fakeClassFile = new FileManipulation(
            __DIR__ . DIRECTORY_SEPARATOR . 'Files' . DIRECTORY_SEPARATOR . 'multilinesOccurrence.file'
        );
        self::assertNull($fakeClassFile->firstOccurrence('nope'));
    }

    public function testLastOccurrenceOfPhpFile(): void
    {
        $fakeClassFile = new Php(
            __DIR__ . DIRECTORY_SEPARATOR . 'Files' . DIRECTORY_SEPARATOR . 'FakeClass.php'
        );
        self::assertEquals(2, $fakeClassFile->firstOccurrence('namespace'));
    }

    public function testLastOccurrenceOfBrutFile(): void
    {
        $fakeClassFile = new FileManipulation(
            __DIR__ . DIRECTORY_SEPARATOR . 'Files' . DIRECTORY_SEPARATOR . 'multilinesOccurrence.file'
        );
        self::assertEquals(2, $fakeClassFile->lastOccurrence('third line'));
    }

    public function testGetLine(): void
    {
        $fakeClassFile = new Php(
            __DIR__ . DIRECTORY_SEPARATOR . 'Files' . DIRECTORY_SEPARATOR . 'FakeClass.php'
        );
        self::assertEquals(
            'namespace MulerTech\FileManipulation\Tests\Files;',
            $fakeClassFile->getLine(2)
        );
    }

    public function testGetLineNotFound(): void
    {
        $fakeClassFile = new Php(
            __DIR__ . DIRECTORY_SEPARATOR . 'Files' . DIRECTORY_SEPARATOR . 'FakeClass.php'
        );
        self::assertNull($fakeClassFile->getLine(100));
    }

    public function testSaveNewFile(): void
    {
        $testFile = new FileManipulation(
            __DIR__ . DIRECTORY_SEPARATOR . 'Files' . DIRECTORY_SEPARATOR . 'FolderTmp' . DIRECTORY_SEPARATOR . 'test.file'
        );
        self::assertTrue($testFile->saveFile('test brut file', true));
        self::assertEquals('test brut file', $testFile->openFile());
        unlink(
            __DIR__ . DIRECTORY_SEPARATOR . 'Files' . DIRECTORY_SEPARATOR . 'FolderTmp' . DIRECTORY_SEPARATOR . 'test.file'
        );
        if (is_dir(__DIR__ . DIRECTORY_SEPARATOR . 'Files' . DIRECTORY_SEPARATOR . 'FolderTmp')) {
            rmdir(__DIR__ . DIRECTORY_SEPARATOR . 'Files' . DIRECTORY_SEPARATOR . 'FolderTmp');
        }
    }

    public function testSaveNewFileWriteProtected(): void
    {
        $testFile = new FileManipulation(
            __DIR__ . DIRECTORY_SEPARATOR . 'Files' . DIRECTORY_SEPARATOR . 'FolderTmp' . DIRECTORY_SEPARATOR . 'testWriteProtected.file'
        );
        self::assertTrue($testFile->saveFile('test file', true));
        $this->expectException(RuntimeException::class);
        chmod(
            __DIR__ . DIRECTORY_SEPARATOR . 'Files' . DIRECTORY_SEPARATOR . 'FolderTmp' . DIRECTORY_SEPARATOR . 'testWriteProtected.file',
            0500
        );
        $testFile->saveFile('test write protected file');
    }

    public function testSaveExistingFile(): void
    {
        $brutFile = new FileManipulation(
            __DIR__ . DIRECTORY_SEPARATOR . 'Files' . DIRECTORY_SEPARATOR . 'brut.file'
        );
        self::assertTrue($brutFile->saveFile('test save existing file'));
        self::assertEquals('test save existing file', $brutFile->openFile());
        self::assertTrue($brutFile->saveFile('test brut file'));
        self::assertEquals('test brut file', $brutFile->openFile());
    }

    public function testSaveFileWithoutParentFolder(): void
    {
        $this->expectException(RuntimeException::class);
        $testFile = new FileManipulation(
            __DIR__ . DIRECTORY_SEPARATOR . 'Files' . DIRECTORY_SEPARATOR . 'Nope' . DIRECTORY_SEPARATOR . 'test.file'
        );
        $testFile->saveFile('test save file without parent folder and without recursive');
    }

    /**
     * @throws JsonException
     */
    public function testConvertFile(): void
    {
        $jsonTestFile = new Json(
            __DIR__ . DIRECTORY_SEPARATOR . 'Files' . DIRECTORY_SEPARATOR . 'jsonTest.json'
        );
        $yamlTestFile = new Yaml(
            __DIR__ . DIRECTORY_SEPARATOR . 'Files' . DIRECTORY_SEPARATOR . 'jsonTest.yaml'
        );
        $jsonTestFile->convertFile($yamlTestFile);
        self::assertEquals($jsonTestFile->openFile(), $yamlTestFile->openFile());
        unlink(__DIR__ . DIRECTORY_SEPARATOR . 'Files' . DIRECTORY_SEPARATOR . 'jsonTest.yaml');
    }

    public function testGetExtension(): void
    {
        $jsonTestFile = new Json(
            __DIR__ . DIRECTORY_SEPARATOR . 'Files' . DIRECTORY_SEPARATOR . 'jsonTest.json'
        );
        self::assertEquals('json', $jsonTestFile->getExtension());
    }

    public function testCountLines(): void
    {
        $multilineFile = new FileManipulation(
            __DIR__ . DIRECTORY_SEPARATOR . 'Files' . DIRECTORY_SEPARATOR . 'multilinesOccurrence.file'
        );
        self::assertEquals(4, $multilineFile->countLines());
    }

    public function testInsertContent(): void
    {
        $multilineFile = new FileManipulation(
            __DIR__ . DIRECTORY_SEPARATOR . 'Files' . DIRECTORY_SEPARATOR . 'multilines.file'
        );
        $multilineFile->saveFile(self::TEST_MULTILINE);
        $multilineFile->insertContent(2, 'inserted line');
        self::assertEquals(self::TEST_MULTILINE_WITH_INSERT, $multilineFile->openFile());
        unlink(__DIR__ . DIRECTORY_SEPARATOR . 'Files' . DIRECTORY_SEPARATOR . 'multilines.file');
    }

    public function testInsertContentJustNewLine(): void
    {
        $multilineFile = new FileManipulation(
            __DIR__ . DIRECTORY_SEPARATOR . 'Files' . DIRECTORY_SEPARATOR . 'multilines.file'
        );
        $multilineFile->saveFile(self::TEST_MULTILINE);
        $multilineFile->insertContent(2, PHP_EOL);
        self::assertEquals(self::TEST_MULTILINE_WITH_INSERT_NEW_LINE, $multilineFile->openFile());
        unlink(__DIR__ . DIRECTORY_SEPARATOR . 'Files' . DIRECTORY_SEPARATOR . 'multilines.file');
    }

    public function testInsertContentWithMultiline(): void
    {
        $multilineFile = new FileManipulation(
            __DIR__ . DIRECTORY_SEPARATOR . 'Files' . DIRECTORY_SEPARATOR . 'multilines.file'
        );
        $multilineFile->saveFile(self::TEST_MULTILINE);
        $multilineFile->insertContent(2, self::TEST_MULTILINE_TO_INSERT);
        self::assertEquals(self::TEST_MULTILINE_WITH_MULTILINE_INSERT, $multilineFile->openFile());
        unlink(__DIR__ . DIRECTORY_SEPARATOR . 'Files' . DIRECTORY_SEPARATOR . 'multilines.file');
    }

    public function testFindLineContains(): void
    {
        $multilineFile = new FileManipulation(
            __DIR__ . DIRECTORY_SEPARATOR . 'Files' . DIRECTORY_SEPARATOR . 'multilines.file'
        );
        $multilineFile->saveFile(self::TEST_MULTILINE);
        self::assertEquals('second line', $multilineFile->findLineContains('second'));
        unlink(__DIR__ . DIRECTORY_SEPARATOR . 'Files' . DIRECTORY_SEPARATOR . 'multilines.file');
    }

    public function testFindLineContainsWithFileNotFound(): void
    {
        $nopeFilename = __DIR__ . DIRECTORY_SEPARATOR . 'Files' . DIRECTORY_SEPARATOR . 'nope.file';
        $nopeFile = new FileManipulation($nopeFilename);
        $this->expectExceptionMessage('The file "' . $nopeFilename . '" does not exist.');
        $nopeFile->findLineContains('second');
    }

    public function testFindLineContainsNotFound(): void
    {
        $multilineFile = new FileManipulation(
            __DIR__ . DIRECTORY_SEPARATOR . 'Files' . DIRECTORY_SEPARATOR . 'multilines.file'
        );
        $multilineFile->saveFile(self::TEST_MULTILINE);
        self::assertEquals(null, $multilineFile->findLineContains('line not found'));
        unlink(__DIR__ . DIRECTORY_SEPARATOR . 'Files' . DIRECTORY_SEPARATOR . 'multilines.file');
    }

    public function tearDown(): void
    {
        if (is_file(
            __DIR__ . DIRECTORY_SEPARATOR . 'Files' . DIRECTORY_SEPARATOR . 'FolderTmp' . DIRECTORY_SEPARATOR . 'testWriteProtected.file'
        )) {
            chmod(
                __DIR__ . DIRECTORY_SEPARATOR . 'Files' . DIRECTORY_SEPARATOR . 'FolderTmp' . DIRECTORY_SEPARATOR . 'testWriteProtected.file',
                0777
            );
            unlink(
                __DIR__ . DIRECTORY_SEPARATOR . 'Files' . DIRECTORY_SEPARATOR . 'FolderTmp' . DIRECTORY_SEPARATOR . 'testWriteProtected.file'
            );
            if (is_dir(__DIR__ . DIRECTORY_SEPARATOR . 'Files' . DIRECTORY_SEPARATOR . 'FolderTmp')) {
                rmdir(__DIR__ . DIRECTORY_SEPARATOR . 'Files' . DIRECTORY_SEPARATOR . 'FolderTmp');
            }
        }
    }
}