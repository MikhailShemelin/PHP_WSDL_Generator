<?php

namespace WSDL\Type;

abstract class AbstractTypedArray extends AbstractComplexType implements \IteratorAggregate
{
	// this property is used for defining the array item type only
	// it should be overided in child classes with necessary types
	private static string $single_item;
	
	protected array $item;
	
	public function add($item) {
		$this->item[] = $item;
	}
	
	public function getIterator(): \Traversable {
		return new \ArrayIterator($this->item);
	}
}
