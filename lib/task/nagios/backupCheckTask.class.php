<?php

/**
 * Check backups files have been stored correctly
 *
 * @package projectn
 * @subpackage task
 *
 * @author Peter Johnson <peterjohnson@timout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.1
 *
 * This script is intended to be run on dev #1.
 * It checks the backups folders to confirm that they were succesfully backed-up.
 *
 */

class backupCheckTask extends nagiosTask
{
    protected   $description  = 'Check backups files have been stored correctly';

    protected function executeNagiosTask( $arguments = array(), $options = array() )
    {
        // Instanciate s3cmd.
        $s3cmd = new s3cmd();

        // Check folders are all there.
        $s3cmd->fileExists( '/timeout-projectn-backups/' ) || $this->addError( 'Backups folder not found on s3.' );
        $s3cmd->fileExists( '/timeout-projectn-backups/export/' ) || $this->addError( 'Exports backup folder not found on s3.' );
        $s3cmd->fileExists( '/timeout-projectn-backups/mysql/' ) || $this->addError( 'Mysql backup folder not found on s3.' );
        $s3cmd->fileExists( '/timeout-projectn-backups/mysql/latest/' ) || $this->addError( 'Latest mysql backup folder not found on s3.' );

        // Get yesterdays datestamp.
        $yesterday = date( 'Ymd', strtotime( '-1 day' ) );

        // Check export backups for yesterday.
        $s3cmd->fileExists( '/timeout-projectn-backups/export/export_'.$yesterday ) || $this->addError( 'Yesterdays export backup folder not found on s3.' );
        $s3cmd->fileExists( '/timeout-projectn-backups/export/exports_'.$yesterday.'.tgz' ) || $this->addError( 'Yesterdays export backup tgz not found on s3.' );

        // Figure out mysql file name format for yesterdays backups.
        $mysqlBackupFileName = date('Y-m-d', strtotime( '-1 day' )) .'_21h00m.'. date('l', strtotime( '-1 day' )) .'.sql.gz';

        // Check mysql 'projectn' database backup for yesterday.
        $s3cmd->fileExists( '/timeout-projectn-backups/mysql/latest/project_n_'. $mysqlBackupFileName ) || $this->addError( 'Latest mysql backup for projectn not found.' );
        $fileinfo = $s3cmd->info( '/timeout-projectn-backups/mysql/latest/project_n_'. $mysqlBackupFileName );
        isset( $fileinfo['File size'] ) && $fileinfo['File size'] > 10000000 || $this->addError( 'Latest mysql backup for projectn under sized.' );

        // Check mysql 'projectn_data_entry' database backup for yesterday.
        $s3cmd->fileExists( '/timeout-projectn-backups/mysql/latest/project_n_data_entry_'. $mysqlBackupFileName ) || $this->addError( 'Latest mysql backup for projectn_data_entry not found.' );
        $fileinfo = $s3cmd->info( '/timeout-projectn-backups/mysql/latest/project_n_data_entry_'. $mysqlBackupFileName );
        isset( $fileinfo['File size'] ) && $fileinfo['File size'] > 1000000 || $this->addError( 'Latest mysql backup for projectn_data_entry under sized.' );
    }
}
