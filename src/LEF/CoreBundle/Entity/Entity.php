<?php
namespace LEF\CoreBundle\Entity;

abstract class Entity implements \ArrayAccess {
	use Hydrator;  
	
	public function __construct(array $data = array()) {
	    if(!empty($data))
	        $this->hydrate($data);
	}
	
	public function offsetGet($offset) {
	    $method = 'get' .ucfirst($offset);
		return isset($this->$offset) && is_callable([$this, $method]) ? $this->$offset : null;
	}
	
	public function offsetSet($var, $value) {
		$method = 'set'.ucfirst($var);
		if(is_callable([$this, $method]))
			$this->$method($value);
	}
	
	public function offsetExists($var) {
	    $method = 'get' .ucfirst($var);
		return isset($this->$var) && is_callable([$this, $method]);
	}
	
	public function offsetUnset($var) {
		throw new \RuntimeException('Impossible de supprimer une quelconque valeur');
	}
	
	public function getClass() {
	    return static::class;
	}
}