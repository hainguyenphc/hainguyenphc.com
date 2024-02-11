<?php

use Drupal\DrupalExtension\Context\RawDrupalContext;

/**
 * Defines application features from the specific context.
 * @see https://www.drupal.org/project/drupalextension/issues/2685951#comment-10960907
 */
class FeatureContext extends RawDrupalContext {
    
    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct() {
        // Do something
    }

}
