<?php

class Timezone
{
  private $name = 'Europe/London';

  public function getName()
  {
    return $this->name;
  }

  public function setName($name)
  {
    $this->name = $name;
  }
}

class sfGuardUser
{
  private $timezone = null;

  public function getTimezone()
  {
    if (!$this->timezone) {
      $this->timezone = new Timezone();
    }

    return $this->timezone;
  }

  public function setTimezone($timezone)
  {
    $this->timezone = $timezone;
  }
}

class sfUser
{
  private $sf_guard_user = null;

  public function getGuardUser()
  {
    if (!$this->sf_guard_user) {
      $this->sf_guard_user = new sfGuardUser();
    }

    return $this->sf_guard_user;
  }
}

/**
 * EventOccurrence
 *
 * This class has been auto-generated by the Doctrine ORM Framework
 *
 * @property DateTime $datetime
 *
 * @method DateTime                getDatetime()                Returns the current record's "datetime" value
 * @method Date                    getDate()                    Returns the current record's "date" value
 * @method Time                    getTime()                    Returns the current record's "time" value
 * @method DateTime                getTimestamp()               Returns the current record's "timestamp" value
 *
 * @package    Jagmort
 * @subpackage Timezone
 * @author     Alexander Pervakov <frost.nzcr4@jagmort.com>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class EventOccurrence extends JagmortTzDoctrineRecord
{
  public function setTableDefinition()
  {
    $this->setTableName('event_occurrence');
    $this->hasColumn('datetime', 'datetime', null, array(
      'type' => 'datetime',
      'notnull' => true,
    ));
    $this->hasColumn('date', 'date', null, array(
      'type' => 'date',
      'notnull' => true,
    ));
    $this->hasColumn('time', 'time', null, array(
      'type' => 'time',
      'notnull' => true,
    ));
    $this->hasColumn('timestamp', 'timestamp', null, array(
      'type' => 'timestamp',
      'notnull' => true,
    ));
  }
}

class EventOccurrenceTable extends Doctrine_Table
{
  /**
   * Returns an instance of this class.
   *
   * @return object EventOccurrenceTable
   */
  public static function getInstance()
  {
    return Doctrine_Core::getTable('EventOccurrence');
  }

  /**
   * @return Doctrine_Query
   */
  public function getQuery()
  {
    return $this->createQuery('e');
  }

  /**
   * @return EventOccurrence|null
   */
  public function get($pk) {
    $q = $this->getQuery();
    $q->where('id = ?', $pk);

    return $q->fetchOne();
  }
}

class EventOccurrenceForm extends sfFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'             => new sfWidgetFormInputHidden(),
      'datetime'       => new JagmortTzWidgetFormDateTime(array('time' => array('with_seconds' => true)))
    ));

    $this->setValidators(array(
      'id'             => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'datetime'       => new JagmortTzValidatorDateTime()
    ));

    $this->widgetSchema->setNameFormat('event_occurrence[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'EventOccurrence';
  }
}
