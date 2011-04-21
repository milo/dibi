<?php

/**
 * User modifiers. Only examples, code needs to be tested.
 */

class MaxModifier extends DibiModifier
{
	public function toSql($arg)
	{
		return $this->connection->translate('MAX(%n)', $arg);
	}
}

class ModModifier extends DibiModifier
{
	public function getNumArgs()
	{
	    return 2;
	}

	public function toSql($foo)
	{
		$args = func_get_args();
	    return $this->connection->translate('%i', $args[0] % $args[1]);
	}
}


class PgArrayIntModifier extends DibiModifier
{
	public function toSql($arg, $lvl = 0)
	{
	    $args = is_array($arg) ? $arg : array($arg);
	    
	    $return = array();
	    foreach($args AS $arg)
	    {
	        if ($arg === NULL)
	        {
	            $return[] = 'NULL';
	            continue;
	        }
	        
	        if (is_array($arg))
	        {
				$lvl++;
				$return[] = $this->toSql($arg, $lvl);
				$lvl--;
	            continue;
	        }
	        
            $return[] = (int)$arg;
            continue;
	    }
	    
	    $return = '{' . implode(',', $return) . '}';
	    
		if ($lvl > 0)
		{
		    return $return;
		}
		
		return $this->connection->translate('%s', $return);
	}

	public function toPhp($arg)
	{
        $retval = array();
        $cursor = & $retval;
		$stack = array();
		
		preg_match_all('/{|}|,|[0-9-]+|NULL/', substr($arg, 1, -1), $match); // TODO: Errors test
		foreach($match[0] AS $match)
		{
			switch($match)
			{
				case '{':
					$stack[] = & $cursor;
					$k = count($cursor);
					$cursor[$k] = array();
					$cursor = & $cursor[$k];
					break;
				
				case '}':
					end($stack);
					$k = key($stack);
					$cursor = & $stack[$k];
					unset($stack[$k]);
					break;
				
				case ',':
					break;
				
				case 'NULL':
					$cursor[] = NULL;
					break;
				
				default:
					$cursor[] = (int) $match;
					break;
			}
		}
		
		return $retval;
	}
	
	public function matchNativeType($type)
	{
		if( strToLower($type) == '_int4' )
		{
			return true;
		}
		
		return parent::matchNativeType($type);
	}
}
