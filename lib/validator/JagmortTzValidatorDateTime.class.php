<?php

/**
 * @package    Jagmort
 * @subpackage Timezone
 * @author     Alexander Pervakov <frost.nzcr4@jagmort.com>
 */
class JagmortTzValidatorDateTime extends sfValidatorDate
{

  /**
   * Constructor.
   *
   * Available options:
   *
   *  * timezone: The timzone for rendering
   *
   * @param array $options   An array of options
   * @param array $messages  An array of error messages
   *
   * @see sfValidatorBase
   */
  public function __construct($options = array(), $messages = array())
  {
    $this->addOption('timezone', sfContext::getInstance()->getUser()->getGuardUser()->getTimezone()->getName());

    parent::__construct($options, $messages);
  }

  /**
   * @see sfValidatorDate
   */
  protected function configure($options = array(), $messages = array())
  {
    parent::configure($options, $messages);

    $this->setOption('with_time', true);
  }

  protected function doClean($value)
  {
    // check date format
    if (is_string($value) && $regex = $this->getOption('date_format'))
    {
      if (!preg_match($regex, $value, $match))
      {
        throw new sfValidatorError($this, 'bad_format', array('value' => $value, 'date_format' => $this->getOption('date_format_error') ? $this->getOption('date_format_error') : $this->getOption('date_format')));
      }

      $value = $match;
    }

    // convert array to date string
    if (is_array($value))
    {
      $value = $this->convertDateArrayToString($value);
    }

    // convert timestamp to date number format
    if (is_numeric($value))
    {
      $cleanTime = (integer) $value;
      $clean     = date('YmdHis', $cleanTime);
    }
    // convert string to date number format
    else
    {
      try
      {
        $date = new DateTime($value);
        $date->setTimezone(new DateTimeZone(date_default_timezone_get()));
        $clean = $date->format('YmdHis');
      }
      catch (Exception $e)
      {
        throw new sfValidatorError($this, 'invalid', array('value' => $value));
      }
    }

    // Set unified time of model from view timezone.
    $view_dt = new DateTime($value, new DateTimeZone($this->options['timezone']));
    $model_dt = clone $view_dt;
    $model_dt->setTimezone(new DateTimeZone(sfConfig::get('sf_default_timezone')));
    $value = array('year' => $model_dt->format('Y'), 'month' => (int) $model_dt->format('n'), 'day' => (int) $model_dt->format('j'), 'hour' => (int) $model_dt->format('H'), 'minute' => (int) $model_dt->format('i'), 'second' => (int) $model_dt->format('s'));

    return parent::doClean($value);
  }
}
