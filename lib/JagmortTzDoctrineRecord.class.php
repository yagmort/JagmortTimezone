<?php

abstract class JagmortTzDoctrineRecord extends sfDoctrineRecord {
  /**
   * Get the Doctrine date value as a PHP DateTime object.
   *
   * @param  string                $dateFieldName    The field name to get the DateTime object for.
   * @param  boolean|DateTimeZone  $setViewTimezone  If false then return model time, if true or DateTimeZone object then set timezone to view or specific timezone accordingly.
   *
   * @return DateTime              $dateTime         The instance of PHPs DateTime.
   */
  public function getDateTimeObject($dateFieldName, $setViewTimezone = false)
  {
    $datetime = parent::getDateTimeObject($dateFieldName);

    if (!$setViewTimezone) {
      return $datetime;
    }

    if ($setViewTimezone instanceof DateTimeZone) {
      // :TRICKY: for PHP < 5.3.0 $datetime->setTimezone() return null instead of chaining object.
      $datetime->setTimezone($setViewTimezone);
      return $datetime;
    }
    else
    {
      try {
        // :TRICKY: for PHP < 5.3.0 $datetime->setTimezone() return null instead of chaining object.
        if (sfContext::getInstance()->getUser()->getGuardUser() && sfContext::getInstance()->getUser()->getGuardUser()->getTimezone()) {
            $datetime->setTimezone(new DateTimeZone(sfContext::getInstance()->getUser()->getGuardUser()->getTimezone()->getName()));
        }

        return $datetime;
      } catch (Exception $e) {
        return $datetime;
      }
    }
  }
}
