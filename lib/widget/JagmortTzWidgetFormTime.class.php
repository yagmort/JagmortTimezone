<?php

/**
 * @package    Jagmort
 * @subpackage Timezone
 * @author     Alexander Pervakov <frost.nzcr4@jagmort.com>
 */
class JagmortTzWidgetFormTime extends sfWidgetFormTime
{

  /**
   * Constructor.
   *
   * Available options:
   *
   *  * timezone: The timzone for rendering
   *
   * @param array $options     An array of options
   * @param array $attributes  An array of default HTML attributes
   *
   * @see sfWidgetForm
   */
  public function __construct($options = array(), $attributes = array())
  {
    $this->addOption('timezone', sfContext::getInstance()->getUser()->getGuardUser()->getTimezone()->getName());
  
    parent::__construct($options, $attributes);
  }

  /**
   * (non-PHPdoc)
   * @see sfWidgetFormTime::render()
   */
  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
      // convert value to an array
      $default = array('hour' => null, 'minute' => null, 'second' => null);
      if (is_array($value))
      {
        $value = array_merge($default, $value);
      }
      else
      {
        $value = ctype_digit($value) ? (integer) $value : strtotime($value);
        if (false === $value)
        {
          $value = $default;
        }
        else
       {
          // Set view timezone to unified time of model.
          $model_dt = new DateTime(date('Y-m-d H:i:s', $value), new DateTimeZone(sfConfig::get('sf_default_timezone')));
          $view_dt = clone $model_dt;
          $view_dt->setTimezone(new DateTimeZone($this->options['timezone']));
          // int cast required to get rid of leading zeros
          $value = array('hour' => (int) $view_dt->format('H'), 'minute' => (int) $view_dt->format('i'), 'second' => (int) $view_dt->format('s'));
        }
      }

      return parent::render($name, $value, $attributes, $errors);
  }
}
