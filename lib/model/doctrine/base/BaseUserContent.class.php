<?php

/**
 * BaseUserContent
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property string $vendor_ucid
 * @property string $comment_subject
 * @property string $comment_body
 * @property float $user_rating
 * @property integer $user_id
 * @property integer $poi_id
 * @property User $User
 * @property Poi $Poi
 * 
 * @method string      getVendorUcid()      Returns the current record's "vendor_ucid" value
 * @method string      getCommentSubject()  Returns the current record's "comment_subject" value
 * @method string      getCommentBody()     Returns the current record's "comment_body" value
 * @method float       getUserRating()      Returns the current record's "user_rating" value
 * @method integer     getUserId()          Returns the current record's "user_id" value
 * @method integer     getPoiId()           Returns the current record's "poi_id" value
 * @method User        getUser()            Returns the current record's "User" value
 * @method Poi         getPoi()             Returns the current record's "Poi" value
 * @method UserContent setVendorUcid()      Sets the current record's "vendor_ucid" value
 * @method UserContent setCommentSubject()  Sets the current record's "comment_subject" value
 * @method UserContent setCommentBody()     Sets the current record's "comment_body" value
 * @method UserContent setUserRating()      Sets the current record's "user_rating" value
 * @method UserContent setUserId()          Sets the current record's "user_id" value
 * @method UserContent setPoiId()           Sets the current record's "poi_id" value
 * @method UserContent setUser()            Sets the current record's "User" value
 * @method UserContent setPoi()             Sets the current record's "Poi" value
 * 
 * @package    sf_sandbox
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 6820 2009-11-30 17:27:49Z jwage $
 */
abstract class BaseUserContent extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('user_content');
        $this->hasColumn('vendor_ucid', 'string', 32, array(
             'type' => 'string',
             'notnull' => true,
             'length' => '32',
             ));
        $this->hasColumn('comment_subject', 'string', 512, array(
             'type' => 'string',
             'notnull' => true,
             'length' => '512',
             ));
        $this->hasColumn('comment_body', 'string', 65535, array(
             'type' => 'string',
             'notnull' => true,
             'length' => '65535',
             ));
        $this->hasColumn('user_rating', 'float', null, array(
             'type' => 'float',
             'notnull' => false,
             ));
        $this->hasColumn('user_id', 'integer', null, array(
             'type' => 'integer',
             'notnull' => true,
             ));
        $this->hasColumn('poi_id', 'integer', null, array(
             'type' => 'integer',
             'notnull' => true,
             ));
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasOne('User', array(
             'local' => 'user_id',
             'foreign' => 'id',
             'onDelete' => 'CASCADE'));

        $this->hasOne('Poi', array(
             'local' => 'poi_id',
             'foreign' => 'id',
             'onDelete' => 'CASCADE'));

        $timestampable0 = new Doctrine_Template_Timestampable();
        $this->actAs($timestampable0);
    }
}