<?php

/**
 * @package    Jagmort
 * @subpackage Timezone
 * @author     Alexander Pervakov <frost.nzcr4@jagmort.com>
 */
class JagmortTzWidgetFormDate extends sfWidgetFormDate
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
   * @see sfWidgetFormDate::render()
   */
  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    // convert value to an array
    $default = array('year' => null, 'month' => null, 'day' => null);
    if (is_array($value))
    {
      $value = array_merge($default, $value);
    }
    else
    {
      $value = (string) $value == (string) (integer) $value ? (integer) $value : strtotime($value);
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
        $value = array('year' => $view_dt->format('Y'), 'month' => $view_dt->format('n'), 'day' => $view_dt->format('j'));
      }
    }

    return parent::render($name, $value, $attributes, $errors);
  }
}
