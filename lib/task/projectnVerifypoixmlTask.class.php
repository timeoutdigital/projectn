<?php

class projectnVerifypoixmlTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('poi-xml', null, sfCommandOption::PARAMETER_REQUIRED, 'The poi xml'),
    ));

    $this->namespace        = 'projectn';
    $this->name             = 'verify-poi-xml';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [projectn:verify-poi-xml|INFO] task does things.
Call it with:

  [php symfony projectn:verify-poi-xml|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    $this->options = $options;
    $this->poiXml   = simplexml_load_file( $this->options['poi-xml'] );

    $tests = array(
      new noDuplicatePois(),
      new eachPoiHasAtleastOneVendorCategory()
    );

    foreach( $tests as $test )
    {
      $test->run( $this );
      echo PHP_EOL . '- START ' . get_class( $test ) . ' test ---------------------------------------------' . PHP_EOL;
      echo $test->getMessage();
      echo PHP_EOL . '- END ' . get_class( $test ) . ' test ---------------------------------------------' . PHP_EOL;
    }
  }

  public function getPoiXml()
  {
    return $this->poiXml;
  }

  public function getOption( $option )
  {
    return $this->options[ $option ];
  }

}
