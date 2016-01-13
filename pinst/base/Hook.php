<?php

namespace pinst\base;

class Hook extends Object{
	private static $tags = array();
	
	/**
	 * register a handel event
	 *
	 * @param string $tag
	 *            tag name
	 * @param string|array $class_name
	 *            class name ,can be an array
	 * @return boolean|Ambigous if success return the tag is bound to handel event otherwise return false
	*/
	static public function register($tag, $class_name = null)
	{
		if (empty($tag) || ! isset($tag)) {
			return false;
		}
		if (! isset(self::$tags[$tag])) {
			self::$tags[$tag] = array();
		}
		if(!empty($class_name))
			return self::bind($tag, $class_name);
		return true;
	}
	
	/**
	 * Bind a handel event to tag
	 *
	 * @param string $tag
	 *            tag name
	 * @param string|array $class_name
	 *            class name, can be an array
	 * @return boolean|number return the tag is bound to handel event otherwise return false
	 */
	static public function bind($tag, $class_name)
	{
		if (empty($tag)) {
			return false;
		}
		/**
		 * check tag is exist
		 */
		if(!isset(self::$tags[$tag])){
			self::$tags[$tag] = array();
		}
		if (is_array($class_name)) {
			self::$tags[$tag] = array_merge(self::$tags[$tag], $class_name);
		} else {
			self::$tags[$tag][] = $class_name;
		}
		return count(self::$tags[$tag]);
	}
	
	/**
	 * un register message tag
	 *
	 * @param string $tag
	 *            tag name
	 * @param string $name
	 *            the name you want unregister ,if class name is null the unregister all the event bound of this tag
	 */
	static public function unregister($tag, $class_name = NULL)
	{
		if (! isset(self::$tags[$tag])) {
			return true;
		}
		if ($class_name == null) {
			unset(self::$tags[$tag]);
		} else {
			$count = self::count($tag);
			for ($i = 0; $i < $count; $i ++) {
				if (self::$tags[$tag][$i] == $class_name) {
					unset(self::$tags[$tag][$i]);
					break;
				}
			}
		}
		return true;
	}
	
	/**
	 * unregister all the handel event
	 */
	static public function unregisterAll()
	{
		self::$tags = null;
		self::$tags = array();
	}
	
	/**
	 * check the tag is exists
	 *
	 * @param string $tag
	 *            tag name if is null return all tags count
	 * @return boolean exists return true otherwise return false
	 */
	public static function exists($tag, $class_name = NULL)
	{
		$tags = array_keys(self::$tags);
		if (! is_array($tags)) {
			return false;
		}
		if (empty($class_name)) {
			return in_array($tag, $tags);
		} else {
			if (! is_array(self::$tags[$tag])) {
				return false;
			} else {
				return in_array($class_name, self::$tags[$tag]);
			}
		}
		return false;
	}
	
	/**
	 *
	 * @param string $tag
	 * @return number
	 */
	public static function count($tag)
	{
		if (empty($tag)) {
			return count(self::$tags);
		}
		if (isset(self::$tags[$tag])) {
			return count(self::$tags[$tag]);
		}
		return 0;
	}
	
	/**
	 * get tag info
	 *
	 * @param string $tag
	 *            the tag name you want look tag info,if tag is empty return all the infos
	 * @return multitype:
	 */
	public static function tag($tag = '')
	{
		if (empty($tag)) {
			return self::$tags;
		} else {
			return self::$tags[$tag];
		}
	}
	
	/**
	 *
	 * @param string $tag
	 *            tag name
	 * @param string $params
	 * @param string $Ignore_error
	 *            if is true , if some event handel return false can be lgnore
	 */
	public static function handelMessage($tag, &$params = NULL, $Ignore_error = true)
	{
	
		if (isset(self::$tags[$tag])) {
			foreach (self::$tags[$tag] as $name) {
				if ($name !== null) {
					if ($Ignore_error === false) {
						$result = self::exec($name, $tag, $params);
						if (false === $result) {
							return false;
						}
					} else {
						self::exec($name, $tag, $params);
					}
				}
			}
		}
		return true;
	}
	
	/**
	 * exec the handel to handel message
	 *
	 * @param string $class_name
	 *            class name
	 * @param string $month
	 *            month name default run
	 * @param string $params
	 *            params
	 * @return boolean
	 */
	protected static function exec($class_name, $month='run', &$params = NULL)
	{
		if (class_exists($class_name)) {
			try {
				$c = new $class_name();
				return $c->$month($params);
			} catch (\Exception $e) {
			}
		} else {
			return false;
		}
	}
}

?>