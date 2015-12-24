<?php

/**
 * An example of basic class
 */
class ClassExample 
{

   /** @var string Name */
   private $_name;
   /** @var Date Birth date */
   private $_birthDate;

   /**
   * Returns the age of a person with birth date
   * 
   * @return Age of the person
   */
   function getAge()
   {
      $today = new DateTime('today');
      return $_birthDate->diff($today)->y;
   }

   /**
   * Get name
   * 
   * @return Name
   */
   function getName()
   {
      return $this->_name;
   }

   /**
   * Set name
   * 
   * @param string Name
   * @return void
   */
   function setName($value)
   {
      $this->_name = $value;
   }
}

?>