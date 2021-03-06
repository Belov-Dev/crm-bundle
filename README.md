Installation
============

Make sure Composer is installed globally, as explained in the
[installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require a2global/crm-bundle
```

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    // ...
    A2Global\CRMBundle\A2CRMBundle::class => ['all' => true],
];
```


### Step 3: Enable the routes

Create new file `config/routes/crm.yaml`
and import crm routes by adding following lines to this file:

```yaml
# config/routes/crm.yaml

_crm:
    resource: '@A2CRMBundle/Resources/config/routes.yaml'

```