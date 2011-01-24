<?php

/**
 * Movie form.
 *
 * @package    sf_sandbox
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class MovieForm extends BaseMovieForm
{
  public function configure()
  {
    $this->widgetSchema[ 'vendor_movie_id' ] = new widgetFormFixedText();
    $this->widgetSchema[ 'created_at' ]      = new widgetFormFixedText();
    $this->widgetSchema[ 'updated_at' ]      = new widgetFormFixedText();
    $this->widgetSchema[ 'vendor_id' ]       = new widgetFormFixedText();

    $this->widgetSchema[ 'import_error_id' ] = new sfWidgetFormInputHidden();
    $this->validatorSchema[ 'import_error_id' ] = new sfValidatorPass();

    $this->widgetSchema[ 'movie_genres_list' ] = new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'MovieGenre', 'method' => 'getGenre', 'order_by' => array( 'genre', 'asc' ) ));
  }

  protected function doUpdateObject( $values = null )
  {
    parent::doUpdateObject( $values );

    if ( isset( $values[ 'import_error_id' ] ) && is_numeric( $values[ 'import_error_id' ] ) )
    {
        $feedRecord = LogImportErrorHelper::getErrorObjectByImportErrorId( $values[ 'import_error_id' ] );

        if ( $feedRecord !== false )
        {
            $excludeFieldsFromOverrides = array();

            foreach ( $feedRecord as $feedKey => $feedValue )
            {
               if ( isset($values[ $feedKey ] ) && $feedValue != $values[ $feedKey ] )
                {
                    $excludeFieldsFromOverrides[ $feedKey ] = array ( 'currentReceivedValue' => $feedValue, 'editedValue' => $values[ $feedKey ] );
                }
            }
        }
    }

    $record   = $this->getObject();
    $override = new recordFieldOverrideManager( $record );

    if ( isset( $excludeFieldsFromOverrides ) )
    {
        foreach( $excludeFieldsFromOverrides as $field => $data )
        {
            $override->saveModificationAsOverride( $field, $data[ 'currentReceivedValue' ], $data[ 'editedValue' ]  );
        }

        $excludeFromOverridesParam = array_keys( $excludeFieldsFromOverrides );
    }
    else
    {
        $excludeFromOverridesParam = array();
    }

    $override->saveRecordModificationsAsOverrides( $excludeFromOverridesParam );
  }

}
