<?php

class projectnVerifyeventxmlTask extends sfBaseTask
{
  protected function configure()
  {
    // // add your own arguments here
    // $this->addArguments(array(
    //   new sfCommandArgument('my_arg', sfCommandArgument::REQUIRED, 'My argument'),
    // ));

    // add your own options here
    $this->addOptions(array(
      new sfCommandOption('poi-xml', null, sfCommandOption::PARAMETER_REQUIRED, 'The poi xml'),
      new sfCommandOption('event-xml', null, sfCommandOption::PARAMETER_REQUIRED, 'The event xml'),
    ));

    $this->namespace        = 'projectn';
    $this->name             = 'verify-event-xml';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [projectn:verify-event-xml|INFO] task does things.
Call it with:

  [php symfony projectn:verify-event-xml|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    $this->options = $options;
    $this->poiXml   = simplexml_load_file( $this->options['poi-xml'] );
    $this->eventXml = simplexml_load_file( $this->options['event-xml'] );

    $tests = array(
      new eventPlaceIdsShouldExistInPoiXml( ),
      new eventsShouldHaveAtleastOneVendorCategory( ),
    );

    foreach( $tests as $test )
    {
      $test->run( $this );
      echo $test->getMessage();
    }
  }

  public function getPoiXml()
  {
    return $this->poiXml;
  }

  public function getEventXml()
  {
    return $this->eventXml;
  }

  public function getOption( $option )
  {
    return $this->options[ $option ];
  }

}
