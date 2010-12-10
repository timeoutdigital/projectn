<?php
/**
 * Clean phone numbers, Removed contents in bracket and extension number from telephone number
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

class phoneNumberFixer
{
    /**
     * Store phone number while formatting / fixing
     * @var string
     */
    private $phoneNumber;

    /**
     * Phone number to be fixed
     * @param string $phoneNumber
     */
    public function  __construct( $phoneNumber )
    {
        if( !is_string( $phoneNumber ) || trim( $phoneNumber ) == '' )
        {
            throw new phoneNumberFixerException( "Invalid phone number" );
        }

        $this->phoneNumber = trim( $phoneNumber );
    }

    /**
     * Get Fixed phone number
     * @return string
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * Remove any contents within Brackets () including brackets it's self from Phone number
     */
    public function removeBracketContents()
    {
        $this->phoneNumber = preg_replace( '/\s*\([^)]*\)/', '', $this->phoneNumber );
    }

    /**
     * Remove any extension or additional phone numbers after explode chars (,/?)
     */
    public function removeExtensionNumber()
    {
        // Remove everything after ,
        $explodedData = explode( ',', $this->phoneNumber );
        $this->phoneNumber = $explodedData[0]; // allways take the First one

        // Remove everything after /
        $explodedData = explode( '/', $this->phoneNumber );
        $this->phoneNumber = $explodedData[0]; // allways take the First one

        // Remove everything after ?
        $explodedData = explode( '?', $this->phoneNumber );
        $this->phoneNumber = $explodedData[0]; // allways take the First one

        $this->phoneNumber = trim( $this->phoneNumber ); // Trim to remove any white spaces
    }
    
}

class phoneNumberFixerException extends Exception{}