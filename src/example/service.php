<?php

class MyService
{
	public function returnMyString(string $str): string
    {
		return 'Your string: '.$str;
	}
	
	public function getRandomInt(): int
    {
		return rand(0, 100500);
	}
	
	public function getArrayOfRandomInts(): ArrayOfInt
    {
		$length       = rand(5, 10);
		$array_of_int = new ArrayOfInt();
		for ($i = 1; $i <= $length; $i++) {
			$array_of_int->add(rand(0, 100500));
		}
		return $array_of_int;
	}
	
	public function getTestObject(): TestObject
    {
		$test_object       = new TestObject();
		$test_object->id   = '123';
		$test_object->name = 'I am a test object';
		return $test_object;
	}
	
	public function returnMyTestObject(TestObject $test_object): TestObject
    {
		$test_object->name .= ' (name alternated)';
		return $test_object;
	}
	
	public function getArrayOfTestObjects(): ArrayOfTestObjects
    {
		
		$array_of_test_objects = new ArrayOfTestObjects();
		
		$test_object       = new TestObject();
		$test_object->id   = '1';
		$test_object->name = 'First test object';
		$array_of_test_objects->add($test_object);
		
		$test_object       = new TestObject();
		$test_object->id   = '2';
		$test_object->name = 'Second test object';
		$array_of_test_objects->add($test_object);
		
		$test_object       = new TestObject();
		$test_object->id   = '2';
		$test_object->name = 'Third test object';
		$array_of_test_objects->add($test_object);
		
		return $array_of_test_objects;
	}
}


// we placed the custom type declarations there only for the example purposes
// in live projects we recommend to use separate files defining custom types (one file - one class defining a type)

class ArrayOfInt extends WSDL\Type\AbstractTypedArray
{
    // define array type using the special private static property named "single_item"
    private static int $single_item; 
}

class TestObject extends WSDL\Type\AbstractStructure
{
    // all necessary properties should be public and have type defined
    public int $id;
    public string $name;
}

class ArrayOfTestObjects extends WSDL\Type\AbstractTypedArray
{
    // define array type using the special private static property named "single_item"
    private static TestObject $single_item; 
}


