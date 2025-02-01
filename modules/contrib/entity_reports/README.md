CONTENTS OF THIS FILE
=====================

* Introduction
* Requirements
* Installation
* Configuration

INTRODUCTION
============

The Entity Reports module provide users with insights about the entity type 
structure of a Drupal installation through an entry in the /admin/reports menu.
It currently reports:

    1. Field structure for any entity (Node, Taxonomy term, File, Media, 
       Paragraph, User, Menu and other entities);
    2. Report includes: Field (machine) name, description, type, required, 
       cardinality, translatable flag, target entity reference;
    3. Exports their structure in JSON, XML, CSV formats for use with different 
       tools;
    4. Customizable report includes reordering, and show/hide fields from the 
       output file.

* For a full description of the module, visit the project page:
  https://www.drupal.org/project/entity_reports

* To submit bug reports and feature suggestions, or track changes:
  https://www.drupal.org/project/issues/entity_reports

REQUIREMENTS
============

This module requires the Drupal core modules:

* Field (https://www.drupal.org/docs/8/core/modules/field)
* Node (https://www.drupal.org/docs/8/core/modules/node)
* Taxonomy (https://www.drupal.org/docs/8/core/modules/taxonomy)

INSTALLATION
============

* Install as you would normally install a contributed Drupal module. Visit
  https://www.drupal.org/docs/8/extending-drupal-8/installing-drupal-8-modules
  for further information. It is recommended that you use Composer to manage
  Drupal and its dependencies, if possible. For further documentation, visit
  https://www.drupal.org/docs/develop/using-composer/using-composer-to-install-drupal-and-manage-dependencies

CONFIGURATION
=============

* Configure the following module settings in
  Administration » Configuration » Development » Entity Reports Settings:

    - Reported Entity Types

      Allows administrators to enable/disable entity reports for
      each specific fieldable entity types within the site.

    - Report Fields

      Allows administrators to enable/disable as well as reorder the fields that
      are displayed within each entity report. The available report fields 
      themselves are determined by the module.
