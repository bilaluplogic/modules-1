<?php
/**
 * Purpose of File
 *
 * @package modules
 * @copyright (C) copyright-placeholder
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 *
 * @subpackage Uploads Module
 * @link http://xaraya.com/index.php/release/666.html
 * @author Uploads Module Development Team
 */

/**
 *  Purges all files with REJECTED status from the system
 *
 *  @author  Carl P. Corliss
 *  @access  public
 *  @param   boolean    confirmation    whether or not to skip confirmation
 *  @param   string     authid          the authentication id
 *  @return  void
 *
 */

xarModAPILoad('uploads', 'user');

function uploads_user_purge_rejected( $args )
{

    extract ($args);

    if (!xarSecurityCheck('ManageUploads')) return;

    if (isset($authid)) {
        $_GET['authid'] = $authid;
    }

    if (!isset($confirmation)) {
        xarVarFetch('confirmation', 'int:1:', $confirmation, '', XARVAR_NOT_REQUIRED);
    }
    // Confirm authorisation code.
    if (!xarSecConfirmAuthKey())
        return;


    if ((isset($confirmation) && $confirmation) || !xarModVars::get('uploads', 'file.delete-confirmation')) {
        $fileList = xarModAPIFunc('uploads', 'user', 'db_get_file',
                                   array('fileStatus' => _UPLOADS_STATUS_REJECTED));

        if (empty($fileList)) {
            xarController::redirect(xarModURL('uploads', 'admin', 'view'));
            return;
        } else {
            $result = xarModAPIFunc('uploads', 'user', 'purge_files',
                                     array('fileList'   => $fileList));
            if (!$result) {
                $msg = xarML('Unable to purge rejected files!');
                throw new Exception($msg);             
            }
        }
    } else {
        $fileList = xarModAPIFunc('uploads', 'user', 'db_get_file',
                                   array('fileStatus' => _UPLOADS_STATUS_REJECTED));
        if (empty($fileList)) {
            $data['fileList']   = array();
        } else {
            $data['fileList']   = $fileList;
        }
        $data['authid']     = xarSecGenAuthKey();

        return $data;
    }

    xarController::redirect(xarModURL('uploads', 'admin', 'view'));
}
?>