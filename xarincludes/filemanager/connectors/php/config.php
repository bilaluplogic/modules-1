<?php
/**
 * FCKeditor - The text editor for internet
 * Copyright (C) 2003-2005 Frederico Caldeira Knabben
 *
 * Licensed under the terms of the GNU Lesser General Public License:
 *      http://www.opensource.org/licenses/lgpl-license.php
 *
 * For further information visit:
 *      http://www.fckeditor.net/
 *
 * "Support Open Source software. What about a donation today?"
 *
 * File Name: config.php
 *  Configuration file for the File Manager Connector for PHP.
 *
 * File Authors:
 *      Frederico Caldeira Knabben (fredck@fckeditor.net)
 */

global $Config ;


if (is_file('../../../../../../var/tinymce/tinymceconfig.inc')) {
    include_once '../../../../../../var/tinymce/tinymceconfig.inc';
}else{
    include_once '../../../../xartemplates/includes/tinymceconfig.inc';
}
// SECURITY: You must explicitly enable this "connector". (Set it to "true").
if (!isset ($cfg['Enabled']) || $cfg['Enabled']==1) { //just in case there is an old config file with no setting
    $Config['Enabled'] =true;
}else {
    $Config['Enabled'] =false;
}

// Path to user files relative to the document root.
$Config['UserFilesPath'] = $cfg['filebrowser_dir'];//'./var/images/' ;
// Fill the following value it you prefer to specify the absolute path for the
// user files directory. Usefull if you are using a virtual directory, symbolic
// link or alias. Examples: 'C:\\MySite\\UserFiles\\' or '/root/mysite/UserFiles/'.
// Attention: The 'UserFilesPath' must point to the same directory.


$Config['UserFilesAbsolutePath']        = $cfg['root_dir'].$Config['UserFilesPath'];

$Config['AllowedResources']['Types']    = array('File','Image','Flash','Media');

$Config['AllowedExtensions']['File']    = $Config['AllowedExtensions']['File'];//array() ;
$Config['DeniedExtensions']['File']     = $Config['DeniedExtensions']['File'];  //array('php','php3','php5','phtml','asp','aspx','ascx','jsp','cfm','cfc','pl','bat','exe','dll','reg','cgi') ;

$Config['AllowedExtensions']['Image']   = $Config['AllowedExtensions']['Image'];//array('jpg','gif','jpeg','png') ;
$Config['DeniedExtensions']['Image']    = $Config['DeniedExtensions']['Image'];//array() ;

$Config['AllowedExtensions']['Flash']   = $Config['AllowedExtensions']['Flash'];//array('swf','fla') ;
$Config['DeniedExtensions']['Flash']    = $Config['DeniedExtensions']['Flash'];//array() ;

$Config['AllowedExtensions']['Media']   = $Config['AllowedExtensions']['Media'];//array('swf','fla','jpg','gif','jpeg','png','avi','mpg','mpeg') ;
$Config['DeniedExtensions']['Media']    = $Config['DeniedExtensions']['Media'];//array() ;

?>