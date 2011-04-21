<?php

/**
 * Simple container for DibiModifier instances.
 * 
 * @author Miloslav Hůla  
 */ 
class DibiModifierContainer implements ArrayAccess, Countable, IteratorAggregate
{
	/** @var array of DibiModifier **/
	private $modifiers = array();
	


	/** ****** interfaces ArrayAccess, Countable, IteratorAggregate ***********/
	

	/**
	 * @throws InvalidArgumentException
	 */
	final public function offsetSet($offset, $value)
	{
		if (!($value instanceof DibiModifier))
		{
			throw new InvalidArgumentException('You can store only DibiModifier instances in ' . get_class() . ', but ' . (is_object($value) ? get_class($value) : getType($value)) . ' given.');
		}
		
		$this->modifiers[$offset] = $value;
	}



	final public function offsetGet($offset)
	{
		return $this->modifiers[$offset];
	}



	final public function offsetExists($name)
	{
		return isset($this->modifiers[$name]);
	}



	final public function offsetUnset($name)
	{
		unset($this->modifiers[$name]);
	}



	final public function count()
	{
		return count($this->modifiers);
	}



	final public function getIterator()
	{
		return new ArrayIterator($this->modifiers);
	}
}




/**
 * User modifier base class.
 * 
 * <code>
 * $conn = dibi::connection($config);
 * 
 * class SumModifier extends DibiModifier
 * {
 *     public function toSql($arg)
 *     {
 *         return $this->conection->translate('%i', array_sum($arg));
 *     }
 * }
 *
 * $conn->registerModifier('sum', new SumModifier);
 * $rows = $conn->query('SELECT * FROM table WHERE pay < %{sum}', array(1, 2, 3, 4, 5))->fetchAll();
 * </code>
 */
abstract class DibiModifier
{
	/** @var string modifier name */
	private $name;

	/** @var DibiConnection **/
	private $conection;
	
	
	
	/**
	 * Return number of arguments required by toSql method.
	 * 	 
	 * @see self::toSql	 
	 * @return int
	 */
	public function getNumArgs()
	{
		return 1;
	}

	
	
	/**
	 * Convert arguments to SQL string. Parameters count
	 * depends on self::getNumArgs().
	 * 	 
	 * @see    self::getNumArgs	 
	 * @param  mixed
	 * @return string
	 */
	public function toSql($args)
	{
		return '';
	}
	
	
	
	/**
	 * Convert value fetched from database to PHP data type.
	 * 
	 * @param  string fetched value
	 * @return mixed	 
	 */	 
	public function toPhp($value)
	{
		return $value;
	}
	
	
	
	/**
	 * Called when registered with DibiConnection.
	 *
	 * @param string modifier name
	 * @param DibiConnection
	 */	 	 	 
	public function init($name, DibiConnection $connection)
	{
		$this->name = $name;
		$this->connection = $connection;
	}
	
	
	
	/**
	 * Test if modifier can handle database native type.
	 * @param  string   database native type
	 * @return boolean  TRUE if can handle, FALSE if not
	 */
	public function matchNativeType($type)
	{
		return FALSE;
	}
}
