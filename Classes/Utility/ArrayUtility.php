<?php
namespace JVE\JvEvents\Utility;

class ArrayUtility
{

	/**
	 * Gets a value from an associative array by the given key
	 * If not set returns null
	 *
	 * @param array|null $arguments
	 * @param string $key
	 * @return mixed|null
	 * @author Peter Benke <pbenke@allplan.com>
	 */
	public static function getValueByKey(?array $arguments, string $key): mixed
    {

		if(empty($arguments) || !is_array($arguments)){
			return null;
		}

		if(isset($arguments[$key])){
			return $arguments[$key];
		}

		return null;

	}

	/**
	 * Put one item after another item
	 * @param array $array
	 * @param string $item
	 * @param string $afterItem
	 * @return array|bool
	 * @author Peter Benke <pbenke@allplan.com>
	 */
	public static function putItemAfterAnotherItem(array $array, string $item, string $afterItem): bool|array
    {

		if(
			key_exists($item, $array)
			&&
			key_exists($afterItem, $array)
		){

			$buffer = $array[$item];
			unset($array[$item]);

			$array = self::arrayInsertAfter(
				$afterItem,
				$array,
				$item,
				$buffer
			);

		}

		return $array;

	}

	/**
	 * @param int|string $key
	 * @param array $array
	 * @param int|string $new_key
	 * @param int|string $new_value
	 * @return array|bool
	 */
	public static function arrayInsertBefore($key, array &$array, $new_key, $new_value): bool|array
    {
		if (array_key_exists($key, $array)) {
			$new = [];
			foreach ($array as $k => $value) {
				if ($k === $key) {
					$new[$new_key] = $new_value;
				}
				$new[$k] = $value;
			}
			return $new;
		}
		return false;
	}

	/**
	 * @param int|string $key
	 * @param array $array
	 * @param int|string$new_key
	 * @param int|string $new_value
	 * @return array|bool
	 */
	public static function arrayInsertAfter($key, array &$array, $new_key, $new_value): bool|array
    {
		if (array_key_exists($key, $array)) {
			$new = [];
			foreach ($array as $k => $value) {
				$new[$k] = $value;
				if ($k === $key) {
					$new[$new_key] = $new_value;
				}
			}
			return $new;
		}
		return false;
	}

}