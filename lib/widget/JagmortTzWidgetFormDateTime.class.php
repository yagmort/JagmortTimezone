<?php

/**
 * @package    Jagmort
 * @subpackage Timezone
 * @author     Alexander Pervakov <frost.nzcr4@jagmort.com>
 */
class JagmortTzWidgetFormDateTime extends sfWidgetFormDateTime
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
   * @see sfWidgetFormDateTime::getDateWidget()
   */
  protected function getDateWidget($attributes = array())
  {
    return new JagmortTzWidgetFormDate(array_merge(array('timezone' => $this->options['timezone']), $this->getOptionsFor('date')), $this->getAttributesFor('date', $attributes));
  }

  /**
   * (non-PHPdoc)
   * @see sfWidgetFormDateTime::getTimeWidget()
   */
  function getTimeWidget($attributes = array())
  {
    return new JagmortTzWidgetFormTime(array_merge(array('timezone' => $this->options['timezone']), $this->getOptionsFor('time')), $this->getAttributesFor('time', $attributes));
  }
}
