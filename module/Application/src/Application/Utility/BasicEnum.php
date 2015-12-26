<?php

namespace Application\Utility;

abstract class BasicEnum 
{
   private static $constCacheArray = NULL;

   public static function getConstants() 
   {
      if (self::$constCacheArray == NULL) 
      {
         self::$constCacheArray = [];
      }
      $calledClass = get_called_class();
      if (!array_key_exists($calledClass, self::$constCacheArray)) 
      {
         $reflect = new \ReflectionClass($calledClass);
         self::$constCacheArray[$calledClass] = $reflect->getConstants();
      }
      return self::$constCacheArray[$calledClass];
   }
   
   public static function toString($val)
   {
      $constants = self::getConstants();
      $tmp = array_flip($constants);
      
      return ucfirst($tmp[$val]);
   }

   public static function isValidName($name, $strict = false) 
   {
      $constants = self::getConstants();

      if ($strict) 
      {
         return array_key_exists($name, $constants);
      }

      $keys = array_map('strtolower', array_keys($constants));
      return in_array(strtolower($name), $keys);
   }

   public static function isValidValue($value) 
   {
      $values = array_values(self::getConstants());
      return in_array($value, $values, $strict = true);
   }
}