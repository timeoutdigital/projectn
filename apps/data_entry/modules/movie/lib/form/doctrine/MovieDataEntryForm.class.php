<?php

/**
 * MovieDataEntryForm form.
 *
 * @package    sf_sandbox
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class MovieDataEntryForm extends BaseMovieForm
{
  private $user;

  private $filePath = 'media/movie';

  protected $movieMediasScheduledForDeletion = array();


  public function configure()
  {
    $this->user = sfContext::getInstance()->getUser();

    $this->widgetSchema[ 'created_at' ]      = new widgetFormFixedText( array( 'default' => 'now' ) );
    $this->widgetSchema[ 'updated_at' ]      = new widgetFormFixedText( array( 'default' => 'now' ) );
    $this->widgetSchema[ 'utf_offset' ]      = new widgetFormFixedText( array( 'default' => $this->user->getCurrentVendorUtcOffset() ) );

    $this->widgetSchema[ 'vendor_id' ]      = new widgetFormFixedVendorText( array( 'vendor_id'  => $this->user->getCurrentVendorId(), 'vendor_name'  => $this->user->getCurrentVendorCity()  ) );
    $this->validatorSchema[ 'vendor_id' ]   = new validatorSetCurrentVendorId( array( 'vendor_id' => $this->user->getCurrentVendorId() ) );

    /* images */
    $this->embedRelation('MovieMedia');

    /* new movie media */
    //@todo find issue why more than 2 imgs failing to save
    $movieMedia = new MovieMedia();
    $movieMedia->Movie = $this->getObject();

    $form = new MovieMediaForm( $movieMedia );

    $form->setValidator('url', new sfValidatorFile(array(
        'mime_types' => array( 'image/jpeg' ),
        'path' => sfConfig::get('sf_upload_dir') . '/' . $this->filePath,
        'required' => false,
    )));

    $form->setWidget('url', new sfWidgetFormInputFileEditable(array(
        'file_src'    => '/uploads/' . $this->filePath . '/'.$this->getObject()->url,
        'edit_mode'   => false,
        'is_image'    => true,
        'with_delete' => false,
    )));

    $this->embedForm( 'newMovieMediaDataEntry', $form );
  }


  protected function doBind(array $values)
  {

    if (isset($values['MovieMedia']))
    {
      foreach ($values['MovieMedia'] as $i => $movieMediaFields )
      {
        if ( isset($movieMediaFields['url_delete']) && $movieMediaFields['id'] )
        {
            $this->movieMediasScheduledForDeletion[$i] = $movieMediaFields['id'];
        }
      }
    }

    parent::doBind($values);
  }


  public function saveEmbeddedForms($con = null, $forms = null)
  {
      if (null === $con)
      {
        $con = $this->getConnection();
      }

      if (null === $forms)
      {
        $forms = $this->embeddedForms;

        $newMovieMediaDataEntry = $this->getValue('newMovieMediaDataEntry');
        if ( !isset( $newMovieMediaDataEntry['url']) )
        {
            unset($forms['newMovieMediaDataEntry'] );
        }
      }

      foreach ($forms as $form)
      {
          if ($form instanceof sfFormObject)
          {
              if ( $form->getObject() instanceof MovieMedia )
              {
                  if ( !in_array($form->getObject()->getId(), $this->movieMediasScheduledForDeletion ))
                  {
                    $form->saveEmbeddedForms($con);
                    $form->getObject()->save($con);
                  }
              }
          }
          else
          {
              $this->saveEmbeddedForms($con, $form->getEmbeddedForms());
          }
      }
  }

 protected function doUpdateObject($values)
  {
    if ( count( $this->movieMediasScheduledForDeletion ) )
     {
       foreach ( $this->movieMediasScheduledForDeletion as $index => $id )
       {
         unset( $values['MovieMedia'][$index] );
         unset( $this->object['MovieMedia'][$index] );
         Doctrine::getTable('MovieMedia')->findOneById( $id )->delete();
       }
     }

     $this->getObject()->fromArray( $values );
  }


}
