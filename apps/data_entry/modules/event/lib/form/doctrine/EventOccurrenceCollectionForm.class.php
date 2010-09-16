<?php


class EventOccurrenceCollectionForm extends BaseFormDoctrine
{

  public function configure()
  {
  /*
    $this->widgetSchema[ 'collection' ]      = new sfWidgetFormDate(array());
    $this->validatorSchema[ 'collection' ]   = new sfValidatorDate(array( 'required' => false ));
  */
  }

  public function getModelName()
  {
    return 'EventOccurrence';
  }

   public function __construct( $event  = null )
    {
        $this->event = $event;
        parent::__construct( );
    }

  public function setup()
  {
    $this->setWidgets( array(
      'collection_data' => new sfWidgetFormInputHidden(),
      'collection'      => new pnWidgetFormOccurrenceCollection( array( 'label' => '' ), array( 'event' => $this->event)  ),
    ));

    $this->setValidators( array(
      'collection_data' => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'id', 'required' => false)),
      'collection'      => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('occurrence-collection[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }
}
