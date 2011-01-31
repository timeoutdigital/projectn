<?php

/**
 * duplicate_pois module configuration.
 *
 * @package    sf_sandbox
 * @subpackage duplicate_pois
 * @author     Your name here
 * @version    SVN: $Id: configuration.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class duplicate_poisGeneratorConfiguration extends BaseDuplicate_poisGeneratorConfiguration
{
    // Limit the  Number of displayed group to 1
    public function getPagerMaxPerPage()
    {
        return 1;
    }
}
