<?php
require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__).'/../../bootstrap/unit.php';
require_once dirname(__FILE__).'/../bootstrap.php';

/**
 * test PhoneNumberFixer
 *
 * @package projectn
 * @subpackage lib
 *
 * @author Rajeevan kumarathasan <rajeevankumarathasan@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 */

class phoneNumberFixerTest  extends PHPUnit_Framework_TestCase
{

    public function testRemoveBracketContents()
    {
        $fixer = new phoneNumberFixer( '756 826 (доп. 2058)' );
        $fixer->removeBracketContents();
        $this->assertEquals( '756 826', $fixer->getPhoneNumber() );

        $fixer = new phoneNumberFixer( '237-90-35 (36)' );
        $fixer->removeBracketContents();
        $this->assertEquals( '237-90-35', $fixer->getPhoneNumber() );

        $fixer = new phoneNumberFixer( '+7 701 498 4670 (контактный)' );
        $fixer->removeBracketContents();
        $this->assertEquals( '+7 701 498 4670', $fixer->getPhoneNumber() );
    }

    public function testRemoveExtensionNumber()
    {
        $fixer = new phoneNumberFixer( '03 2273 4301 / 4303 / 5484' );
        $fixer->removeExtensionNumber();
        $this->assertEquals( '03 2273 4301', $fixer->getPhoneNumber() );

        $fixer = new phoneNumberFixer( '272 1317, 272 5427' );
        $fixer->removeExtensionNumber();
        $this->assertEquals( '272 1317', $fixer->getPhoneNumber() );

        $fixer = new phoneNumberFixer( '261 2211? 261 2200,' );
        $fixer->removeExtensionNumber();
        $this->assertEquals( '261 2211', $fixer->getPhoneNumber(), 'Invalid CHAR may result in ?, this number from ALMATY, we should consider and remove numbers after ?' );
    }
    
}