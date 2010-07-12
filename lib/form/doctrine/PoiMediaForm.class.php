<?php

/**
 * PoiMedia form.
 *
 * @package    sf_sandbox
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class PoiMediaForm extends BasePoiMediaForm
{
  public function configure()
  {
      $this->useFields( array( 'url' ) );

      $this->setWidget('url', new sfWidgetFormInputFileEditable(array(
        'file_src'    => '/uploads/' . $this->getFileStorePath() . '/'.$this->getObject()->url,
        'edit_mode'   => !$this->isNew(),
        'is_image'    => true,
        'with_delete' => false,
        'delete_label' => 'Delete Image',
      )));

      $this->setValidator('url', new sfValidatorFile(array(
        'mime_types' => array( 'image/jpeg' ),
        'path' => sfConfig::get('sf_upload_dir').'/' . $this->getFileStorePath(),
        'required' => false,
      )));

      $this->setValidator( 'url_delete', new sfValidatorPass() );

      $this->mergePostValidator(new PoiMediaDataEntryValidatorSchema());
  }

  private function getFileStorePath()
  {
    return 'media/' . strtolower( str_replace( 'Media', '', $this->getModelName() ) );
  }
}
