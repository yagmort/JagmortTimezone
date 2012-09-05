<?php
/**
 * @see http://stackoverflow.com/questions/2532729/daylight-saving-time-and-timezone-best-practices
 * @see https://bugs.php.net/bug.php?id=51051
 */

// setup testing framework
$sf_root_dir = realpath(dirname(__FILE__)).'/../../../..';
$apps_dir    = glob($sf_root_dir.'/apps/*', GLOB_ONLYDIR);
$app = substr($apps_dir[0],
                strrpos($apps_dir[0], '/') + 1,
                strlen($apps_dir[0]));

require_once ($sf_root_dir.'/test/bootstrap/unit.php');

$plan = 23;
$t = new lime_test($plan);

if (!extension_loaded('SQLite') && !extension_loaded('pdo_SQLite'))
{
  $t->skip('SQLite needed to run these tests', $plan);
  return;
}

require_once (sfConfig::get('sf_symfony_lib_dir').'/../test/unit/sfContextMock.class.php');

$jagmort_tz_root = realpath(dirname(__FILE__).'/../..') === sfConfig::get('sf_root_dir') ? sfConfig::get('sf_root_dir') : sfConfig::get('sf_plugins_dir').'/JagmortTimezonePlugin';

$autoload = sfSimpleAutoload::getInstance(sfConfig::get('sf_cache_dir').'/project_autoload.cache');
$autoload->addDirectory(sfConfig::get('sf_symfony_lib_dir'));
$autoload->addDirectory($jagmort_tz_root.'/lib');

define('SF_DEFAULT_TIMEZONE', 'Asia/Manila');  // +08:00
sfConfig::set('sf_default_timezone', SF_DEFAULT_TIMEZONE);
date_default_timezone_set(sfConfig::get('sf_default_timezone'));

require_once ($jagmort_tz_root.'/test/TestSuite.php');
$context = sfContext::getInstance(array('user' => 'sfUser'));
$sf_user = $context->user;

// initialize storage
$database = new sfPDODatabase(array('dsn' => 'sqlite::memory:'));
$connection = $database->getConnection();
$connection->exec('SET TIMEZONE UTC');
$connection->exec('CREATE TABLE event_occurrence (id INTEGER PRIMARY KEY, datetime DATETIME, date DATE, time TIME, timestamp TIMESTAMP)');
Doctrine_Manager::connection($connection);

// initialize object
$now = date('Y-m-d H:i:s');
$EventOccurrence = new EventOccurrence();
$EventOccurrence->setDatetime($now);

/**
 * test
 */
$t->diag('->getDatetime()');
$t->is($EventOccurrence->getDatetime(), $now, '->getDatetime() model timezone equal to system default timezone');

$EventOccurrence->save();
$EventOccurrence = EventOccurrenceTable::getInstance()->get($EventOccurrence->getPrimaryKey());
$t->is($EventOccurrence->getDatetime(), $now, '->getDatetime() model timezone equal to system default timezone after save');

/**
 * test
 */
$t->diag('->getTimezone()');
$t->is($sf_user->getGuardUser()->getTimezone()->getName(), 'Europe/London', '->getTimezone() on sf_user gets the current user timezone, not default timezone');

/**
 * test
 */
$t->diag('->setTimezone()');
$sf_user->getGuardUser()->getTimezone()->setName('America/New_York'); // -04:00
$t->is(date_default_timezone_get(), sfConfig::get('sf_default_timezone'), '->setTimezone() on sf_user should not change the default timezone');

/**
 * test
 */
$eo1 = new EventOccurrence();
$eo1->setDatetime('2012-01-01 00:10:11');
$eo_list = array(
  $EventOccurrence,
  $eo1);
foreach ($eo_list as $EO) {
  $EOForm = new EventOccurrenceForm($EO, array(), false);

  $model_dt = $EO->getDateTimeObject('datetime');
  $view_dt = clone $model_dt;
  $view_dt->setTimezone(new DateTimeZone($sf_user->getGuardUser()->getTimezone()->getName()));

  $t->diag(sprintf('jagmortWidgetDateTime->render(%s) => %s', $model_dt->format('Y-m-d H:i:s'), $view_dt->format('Y-m-d H:i:s')));

  $dom = new DomDocument('1.0', 'utf-8');
  $dom->validateOnParse = true;

  $dom->loadHTML('<table>'.$EOForm->render().'</table>');
  $css = new sfDomCssSelector($dom);

  $t->is($css->matchSingle('#event_occurrence_datetime_month option[selected=selected]')->getValue(), $view_dt->format('m'), '->render() should set month from model to view timezone');
  $t->is($css->matchSingle('#event_occurrence_datetime_day option[selected=selected]')->getValue(), $view_dt->format('d'), '->render() should set day from model to view timezone');
  $t->is($css->matchSingle('#event_occurrence_datetime_year option[selected=selected]')->getValue(), $view_dt->format('Y'), '->render() should set year from model to view timezone');
  // There is an known bug when DST is used
  // @see https://bugs.php.net/bug.php?id=51051
  if ($model_dt->format('I') !== $view_dt->format('I')) {
    $t->is($css->matchSingle('#event_occurrence_datetime_hour option[selected=selected]')->getValue(), $view_dt->format('H'), '->render() should set hour from model to view timezone (Known PHP bug, always fail with +1 or -1 hour caused by wrong DST conversion)');
  } else {
    $t->is($css->matchSingle('#event_occurrence_datetime_hour option[selected=selected]')->getValue(), $view_dt->format('H'), '->render() should set hour from model to view timezone');
  }
  $t->is($css->matchSingle('#event_occurrence_datetime_minute option[selected=selected]')->getValue(), $view_dt->format('i'), '->render() should set minute from model to view timezone');
  $t->is($css->matchSingle('#event_occurrence_datetime_second option[selected=selected]')->getValue(), $view_dt->format('s'), '->render() should set second from model to view timezone');
}

/**
 * test
 */
$eo1 = new EventOccurrence();
$eo1->setDatetime('2012-01-01 00:10:11');
$eo_list = array(
  $EventOccurrence,
  $eo1);
foreach ($eo_list as $EO) {
  $t->diag('jagmortWidgetDateTime->bind()');
  $EOForm = new EventOccurrenceForm($EO, array(), false);

  $view_dt = new DateTime('2012-08-31 00:10:11', new DateTimeZone($sf_user->getGuardUser()->getTimezone()->getName()));
  $model_dt = clone $view_dt;
  $model_dt->setTimezone(new DateTimeZone(sfConfig::get('sf_default_timezone')));

  $EOForm->bind(array(
    'id' => $EO->getId(),
    'datetime' => array(
      'year'   => $view_dt->format('Y'),
      'month'  => $view_dt->format('n'),
      'day'    => $view_dt->format('j'),
      'hour'   => $view_dt->format('G'),
      'minute' => (int)$view_dt->format('i'),   // (int) remove leading zero
      'second' => (int)$view_dt->format('s')),  // (int) remove leading zero
  ));
/*
  $e = $EOForm->getErrorSchema();
  foreach ($e as $v1) {
    if ('sfValidatorError' === get_class($v1)) {
      echo $v1;
    }
  }
*/
  $values = $EOForm->getValues();
  $t->is($values['datetime'], $model_dt->format('Y-m-d H:i:s'), '->bind() should set datetime from view to model timezone');
}

/**
 * test
 */
$t->diag('->fetch()');
$doctrine = Doctrine_Manager::getInstance()->getCurrentConnection()->getDbh();
$res = $doctrine->query('SELECT * FROM `event_occurrence` WHERE id = 1 LIMIT 1')->fetch();

$t->is($res['datetime'], $EventOccurrence->getDatetime('Y-m-d H:i:s'), '->fetch() should return unified model time when doing a raw SQL query');

/**
 * test
 */
$t->diag('->fetchOne()');
$q = Doctrine_Query::create()
  ->from('EventOccurrence e')
  ->where('e.datetime = ?', $now);
$event = $q->fetchOne();
$t->is($event ? $event->getId() : $event, $EventOccurrence->getId(), '->fetchOne() query should not affect date');

/**
 * test
 */
$t->diag('->getDateTimeObject()');
$t->is($EventOccurrence->getDateTimeObject('datetime')->format('Y-m-d H:i:s'), $now, '->getDateTimeObject() should return correct model datetime');
$model_td = new DateTime($EventOccurrence->getDatetime());
// :TRICKY: for PHP < 5.3.0 DateTime->setTimezone() return null instead of chaining object.
$model_td->setTimezone(new DateTimeZone($sf_user->getGuardUser()->getTimezone()->getName()));
$t->is($EventOccurrence->getDateTimeObject('datetime', true)->format('Y-m-d H:i:s'), $model_td->format('Y-m-d H:i:s'), '->getDateTimeObject() should return correct view datetime');
$model_td = new DateTime($EventOccurrence->getDatetime());
// :TRICKY: for PHP < 5.3.0 DateTime->setTimezone() return null instead of chaining object.
$model_td->setTimezone(new DateTimeZone('Europe/Moscow'));
$t->is($EventOccurrence->getDateTimeObject('datetime', new DateTimeZone('Europe/Moscow'))->format('Y-m-d H:i:s'), $model_td->format('Y-m-d H:i:s'), '->getDateTimeObject() should return correct datetime for specific timezone');
