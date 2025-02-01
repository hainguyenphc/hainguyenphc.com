CONTENTS OF THIS FILE
=====================

* Introduction
* Requirements
* Installation
* Configuration

INTRODUCTION
============

The Entity Reports CSV submodule provide users with the ability to export
the reports provided by its parent Entity Reports module as a CSV-formatted 
file.

* For a full description of the parent Entity Reports module, visit the 
   project page: https://www.drupal.org/project/entity_reports

* To submit bug reports and feature suggestions, or track changes:
  https://www.drupal.org/project/issues/entity_reports

REQUIREMENTS
============

This submodule requires its parent Entity Reports module to be enabled.

The parent Entity Reports module requires these Drupal core modules:

* Field (https://www.drupal.org/docs/8/core/modules/field)
* Node (https://www.drupal.org/docs/8/core/modules/node)
* Taxonomy (https://www.drupal.org/docs/8/core/modules/taxonomy)

INSTALLATION
============

Before attempting to install this submodule, its parent module Entity Report
must already be installed. Additionally, this submodules has multiple 
dependencies of its own.

If possible, using Composer will simplify installation.
In your Drupal project, use the following command:

`composer require drupal/csv_serialization`

This command will install the necessary external PHP library, `league/csv`,
as well as the Drupal module `drupal/csv_serialization`.
Afterwards, you should then be able to enable the submodule as usual:

`drush pm:enable drupal/entity_reports_csv`


CONFIGURATION
=============

There is no separate configuration for this submodule.

To configure the parent Entity Reports module, go to
Administration » Configuration » Development » Entity Reports Settings.
