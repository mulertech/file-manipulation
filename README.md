
# FileManipulation

___

This class manipulates files and paths

___

## Installation

###### _Two methods to install FileManipulation package with composer :_

1.
Add to your "**composer.json**" file into require section :

```
"mulertech/file-manipulation": "^1.0"
```

and run the command :

```
php composer.phar update
```

2.
Run the command :

```
php composer.phar require mulertech/file-manipulation "^1.0"
```

___

## Usage

<br>

###### _Open env file : (return content of file)_

```
$envFile = new Env('path/to/envFile');
$content = $envFile->open();
```

// `key1=value1`

<br>

###### _Parse env file : (return parsed content of file)_

```
$envFile = new Env('path/to/envFile');
$content = $envFile->parseFile();
```

// `['key' => 'value', 'key2' => 'value2']`

<br>

###### _Load env file : (load env file in environment variables)_

```
$envFile = new Env('path/to/envFile');
$content = $envFile->parseFile();
```

<br>

###### _Open json file : (return content of file)_

```
$jsonFile = new Json('path/to/file.json');
$content = $jsonFile->open();
```

// `['key' => 'value', 'key2' => 'value2']`

<br>

###### _Open php file : (return content of file)_

```
$phpFile = new Php('path/to/file.php');
$content = $phpFile->open();
```

// `['key' => 'value', 'key2' => 'value2']`

<br>

###### _Open yaml file : (return content of file)_

```
$yamlFile = new Yaml('path/to/file.yaml'); // or .yml
$content = $yamlFile->open();
```

// `['key' => 'value', 'key2' => 'value2']`

<br>

###### _Open other file : (return content of file)_

```
$otherFile = new FileManipulation('path/to/file.other');
$content = $otherFile->open();
```

// `['key' => 'value', 'key2' => 'value2']`

<br>

###### _Save env/json/php/yaml file :_

```
$envFile = new Env('path/to/envFile');
$content = $envFile->saveFile('content to save');
```

<br>

###### _Save other file :_

```
$otherFile = new FileManipulation('path/to/file.other');
$content = $otherFile->saveFile('content to save');
```

<br>

###### _Php file class name :_

```
$phpFile = new Php('path/to/file.php');
$className = $phpFile->getClassName();
```

// `ClassName`

<br>

###### _Php get class names :_

```
$phpFile = new Php('path/to/file.php');
$classNames = $phpFile->getClassNames();
```

// `['ClassName', 'ClassName2']`

<br>

###### _Php get class attribute named "Attribute::class" :_

```
$phpFile = new Php('path/to/file.php');
$attribute = Php::getClassAttributeNamed(Class::class, Attribute::class);
```

// return ReflectionAttribute of `Attribute::class`

<br>

###### _Php get instance of class attribute named "Attribute::class" :_

```
$phpFile = new Php('path/to/file.php');
$attribute = Php::getInstanceOfClassAttributeNamed(Class::class, Attribute::class);
```

// return instance of `Attribute::class`

<br>

###### _Php get properties attributes :_

```
$phpFile = new Php('path/to/file.php');
$propertiesAttributes = Php::getPropertiesAttributes(Class::class);
```

// return array of ReflectionProperty of properties

<br>

###### _Php get instance of properties attributes named "Attribute::class" :_

```
$phpFile = new Php('path/to/file.php');
$propertiesAttributes = Php::getInstanceOfPropertiesAttributesNamed(Class::class, Attribute::class);
```

// return array of property name => instances of `Attribute::class`

<br>

###### _Php get methods attributes :_

```
$phpFile = new Php('path/to/file.php');
$methodsAttributes = Php::getMethodsAttributes(Class::class);
```

// return array of ReflectionMethod of methods

<br>

###### _Php get instance of methods attributes named "Attribute::class" :_

```
$phpFile = new Php('path/to/file.php');
$methodsAttributes = Php::getInstanceOfMethodsAttributesNamed(Class::class, Attribute::class);
```

// return array of method name => instances of `Attribute::class`

<br>

###### _Get first occurrence of a string in a file :_

```
$file = new FileManipulation('path/to/file'); // if for example php file : new Php('path/to/file.php')
$firstOccurrence = $file->getFirstOccurrence('string');
```

// return line of first occurrence (int)

<br>

###### _Get last occurrence of a string in a file :_

```
$file = new FileManipulation('path/to/file'); // if for example php file : new Php('path/to/file.php')
$lastOccurrence = $file->getLastOccurrence('string');
```

// return line of last occurrence (int)

<br>

###### _Get line number :_

```
$file = new FileManipulation('path/to/file'); // if for example php file : new Php('path/to/file.php')
$lineNumber = $file->getLine(number);
```

// return content of line (string)

<br>

###### _Convert file :_

```
$jsonFile = new Json('path/to/file.json');
$yamlFile = new Yaml('path/to/file.yaml');
$jsonFile->convertFile($yamlFile);
```

// convert json file to yaml file

<br>

###### _Count lines :_

```
$file = new FileManipulation('path/to/file'); // if for example php file : new Php('path/to/file.php')
$lines = $file->countLines();
```

// return number of lines of file (int)

<br>

###### _Insert content at line number :_

```
$file = new FileManipulation('path/to/file'); // if for example php file : new Php('path/to/file.php')
$file->insertContentAtLineNumber('content', 2);
```

// insert content (one or more lines) at line number 2 and move other lines after

<br>

###### _Date path :_

```
$dateStorage = new DateStorage('path');
$datePath = $dateStorage->datePath();
```

// return path of path/year/month (example : path/2022/02)

###### _Date filename :_

```
DateStorage::dateFilename('suffix');
```

// return filename with date (example : 20220201-suffix)

###### _Date time filename :_

```
DateStorage::dateTimeFilename('suffix');
```

// return filename with date and time (example : 20220201-1200-suffix)