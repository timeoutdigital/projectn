<?php

/**
 * EventMedia form.
 *
 * @package    sf_sandbox
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class EventMediaForm extends BaseEventMediaForm
{
  public function configure()
  {
      $this->useFields( array( 'url' ) );

      $this->setWidget('url', new sfWidgetFormInputFileEditable(array(
        'file_src'    => $this->getObject()->getFileUploadStorePathWeb() . '/'.$this->getObject()->url,
        'edit_mode'   => !$this->isNew(),
        'is_image'    => true,
        'with_delete' => true,
        'delete_label' => 'Delete',
      )));

      $this->setValidator('url', new sfValidatorFile(array(
        'mime_types' => array( 'image/jpeg' ),
        'path' => $this->getObject()->getFileUploadStorePath(),
        'required' => false,
      )));

      $this->setValidator( 'url_delete', new sfValidatorPass() );

      $this->mergePostValidator(new EventMediaDataEntryValidatorSchema());
  }

}
