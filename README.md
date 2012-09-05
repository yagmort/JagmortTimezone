JagmortTimezonePlugin
=====================

With this module the user can set the timezone.

Requirements
------------

* PHP 5 >= 5.2.0
* Symfony 1.4
* Doctrine
* sfGuardDoctrinePlugin

Model and view timezones
------------------------

Model time is a unified time (by default your server's system time), view
is a user time, may specific for any user.

Installation
------------

Activate the plugin in the `config/ProjectConfiguration.class.php`

```php
    class ProjectConfiguration extends sfProjectConfiguration
    {
      public function setup()
      {
        $this->enablePlugins(array(
          'sfDoctrinePlugin', 
          'sfDoctrineGuardPlugin',

          'JagmortTimezonePlugin'
        ));
      }
    }
```

Setup timezone in `config/app.yml`:

```yaml
    default_timezone: UTC
```

Setup timezone in `config/ProjectConfiguration.class.php`:

```php
    class ProjectConfiguration extends sfProjectConfiguration
    {
      //...

      public function setup()
      {
        //...

        $this->dispatcher->connect(
          'doctrine.filter_model_builder_options',
          array($this, 'configureDoctrineBuilderOptions')
        );

        //...
      }

      //...

      public function configureDoctrineBuilderOptions(sfEvent $event, $options)
      {
        $options['baseClassName'] = 'JagmortTzDoctrineRecord';

        return $options;
      }

      //...
    }
```

Rebuild your models:

```ShellSession
    ./symfony doctrine:build-model
    ./symfony doctrine:build-forms
```

Testing
-------

To run tests you need SQLite extension installed:

```ShellSession
    apt-get install php5-sqlite
```

Then run:

```ShellSession
    cd /path/to/your/project/plugins/JagmortTimezonePlugin
    touch symfony
    /path/to/symfony/data/bin/symfony test:unit -t JagmortTimezonePlugin
```

or add to your `config/ProjectConfiguration.class.php`:

```php
  public function setupPlugins()
  {
    $this->pluginConfigurations['JagmortTimezonePlugin']->connectTests();
  }
```

and then run:

```ShellSession
    cd /path/to/your/project
    ./symfony test:unit -t JagmortTimezonePlugin
```

Links
-----

* [Daylight saving time and timezone best practices](http://stackoverflow.com/questions/2532729/daylight-saving-time-and-timezone-best-practices)
* [PHP DateTime bug](https://bugs.php.net/bug.php?id=51051)
