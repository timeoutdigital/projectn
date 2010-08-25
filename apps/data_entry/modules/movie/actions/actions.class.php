<?php

require_once dirname(__FILE__).'/../lib/movieGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/movieGeneratorHelper.class.php';

/**
 * movie actions.
 *
 * @package    sf_sandbox
 * @subpackage movie
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class movieActions extends autoMovieActions
{

  private $user;

  public function preExecute()
  {
     parent::preExecute();

     $filters = $this->getFilters() ;
     $this->user = $this->getUser();

     if ( !isset( $filters['vendor_id'] ) || !$this->user->checkIfVendorIdIsAllowed( $filters['vendor_id'] ) )
     {
          $this->setFilters( array( 'vendor_id' => $this->user->getCurrentVendorId() ) );
     }
  }

  public function executeAjaxMovieList($request)
  {
    $this->getResponse()->setContentType('application/json');

    $q = Doctrine_Query::create()
                ->select( 'id, name' )
                ->from('Movie m')
                ->where( 'vendor_id = ?', $this->user->getCurrentVendorId() )
                ->andWhere( 'name LIKE ?', '%' . $request->getParameter('q') . '%' );

    $result = $q->fetchArray();

    $pois = array();
    foreach ( $result as $poi )
    {
        $pois[ $poi['id'] ] = $poi['name'];
    }

    return $this->renderText(json_encode($pois));
  }

  public function executeEdit(sfWebRequest $request)
  {
    if ( $this->getUser()->checkIfRecordPermissionsByRequest( $request ) )
    {
        parent::executeEdit( $request );
    }
    else
    {
        $this->getUser()->setFlash ( 'error' , 'You don\' have permissions to change this record' );
        $this->redirect('@movie');
    }
  }

  public function executeDelete(sfWebRequest $request)
  {
    if ( $this->getUser()->checkIfRecordPermissionsByRequest( $request ) )
    {
        parent::executeDelete( $request );
    }
    else
    {
        $this->getUser()->setFlash ( 'error' , 'You don\' have permissions to delete this record' );
        $this->redirect('@movie');
    }
  }

  public function executeBatch(sfWebRequest $request)
  {
    if ( $this->getUser()->checkIfMultipleRecordsPermissionsByRequest( $request ) )
    {
        parent::executeBatch( $request );
    }
    else
    {
        $this->getUser()->setFlash ( 'error' , 'You don\' have permissions to change/delete some or all of the records selected' );
        $this->redirect('@movie');
    }
  }

  protected function executeBatchDelete(sfWebRequest $request)
  {
    if ( $this->getUser()->checkIfMultipleRecordsPermissionsByRequest( $request ) )
    {
        $ids = $request->getParameter('ids');

        $records = Doctrine_Query::create()
          ->from('movie')
          ->whereIn('id', $ids)
          ->execute();

        foreach ($records as $record)
        {
            $this->deleteRelations( $record );
            $record->delete();
        }

        $this->getUser()->setFlash('notice', 'The selected items have been deleted successfully.');
    }
    else
    {
        $this->getUser()->setFlash ( 'error' , 'You don\' have permissions to change/delete some or all of the records selected' );        
    }
    
    $this->redirect('@movie');
  }

  private function deleteRelations( Movie $movie )
  {
    //delete vendor category references
    $movieGenreIds = array();
    foreach( $movie[ 'MovieGenres' ] as $movieGenre )
    {
        $movieGenreIds[] =  $movieGenre['id'];
    }
    $movie->unlink( 'MovieGenres', $movieGenreIds );
    $movie->save();

    //delete meta
    $movie[ 'MovieMeta' ]->delete();

    //delete media
    foreach( $movie[ 'MovieMedia' ] as $movieMedia )
    {
        $file = $movieMedia->getFileUploadStorePath() . '/' . $movieMedia[ 'url' ];

        if ( is_file($file) )
        {
            unlink($file);
        }
        $movieMedia->delete();
    }
  }

}
