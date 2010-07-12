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
        'edit_mode'   => !$this->isNew(),
        'is_image'    => true,
        'with_delete' => false,
    )));

    $this->embedForm( 'newMovieMediaDataEntry', $form );
  }

  public function saveEmbeddedForms($con = null, $forms = null)
  {
      if (null === $forms)
      {
        $forms = $this->embeddedForms;

        $newMovieMediaDataEntry = $this->getValue('newMovieMediaDataEntry');
        if ( !isset( $newMovieMediaDataEntry['url']) )
        {
            unset($forms['newMovieMediaDataEntry'] );
        }

      }

      return parent::saveEmbeddedForms($con, $forms);
  }

}
