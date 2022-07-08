<?php

namespace WSDL\Type;

abstract class AbstractTypedArray extends AbstractComplexType implements \IteratorAggregate
{

	// this property is used for defining the array type only
	// (to be overided in child classes with necessary types)
	private static string $single_item;
	
	public array $item;
	
	public function __constructor($arr = []) {
		$this->item = $arr;
	}
	
	public function add($item) {
		$this->item[] = $item;
	}
	
	public function getArrayCopy() {
		return $this->item;
	}
	
	public function getIterator(): \Traversable {
		return new \ArrayIterator($this->item());
	}
}
