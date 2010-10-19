<?php

class PoiMediaFormDataEntry extends BasePoiMediaForm
{
    public function configure()
  {
      $this->useFields( array( 'url' ) );

      $poiMedia = $this->getObject();

      $fileUrl = ( $poiMedia[ 'status']  == 'valid' ) ?
        'http://projectn.s3.amazonaws.com/' . $poiMedia[ 'Poi' ][ 'Vendor' ][ 'city' ] . '/poi/media/' . $this->getObject()->url :
        '/projectn/uploads/media/poi/'.   $this->getObject()->url; // /projectn/ required for LIVE DataEntry #742

      $this->setWidget('url', new sfWidgetFormInputFileEditable(array(
        'file_src'    =>  $fileUrl,
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

      $this->mergePostValidator(new PoiMediaDataEntryValidatorSchema());
  }
}
?>
