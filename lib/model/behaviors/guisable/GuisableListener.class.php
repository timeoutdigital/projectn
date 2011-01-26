<?php
/**
 * Listener for the Guisable behaviour
 *
 * @package projectn
 * @subpackage guisable.behaviours.model.lib
 *
 * @author Ralph Schwaninger <ralphschwaninger@timeout.com>
 * @copyright Timeout Communications Ltd
 * @version 1.0.0
 *
 */
class GuisableListener extends Doctrine_Record_Listener
{

    /**
     * listener to prevent save if in guisable state
     *
     * @param Doctrine_Event $event
     */
    public function preSave(Doctrine_Event $event)
    {
        if ( $event->getInvoker()->isInGuise() )
        {
           throw new GuiseException( 'Save failed, prevented, due to record in guise' );
        }
    }

    /**
     * listener to prevent delete if in guisable state
     *
     * @param Doctrine_Event $event
     */
    public function preDelete(Doctrine_Event $event)
    {
        if ( $event->getInvoker()->isInGuise() )
        {
            throw new GuiseException( 'Delete failed, prevented, due to record in guise' );
        }
    }

}

