<?php
/**
 * AddressBook admin functions
 *
 * @package modules
 * @copyright (C) 2002-2007 The Digital Development Foundation
 * @license GPL {http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 *
 * @subpackage AddressBook Module
 * @author Garrett Hunter <garrett@blacktower.com>
 * Based on pnAddressBook by Thomas Smiatek <thomas@smiatek.com>
 */
/**
 * update the categories
 *
 * @param passed in from modifycategories api
 * @return bool
 * @throws BAD_PARAM, NO_PERMISSION, DATABASE_ERROR
 */
function addressbook_adminapi_updatecategories($args)
{

    // var defines
    $dels = '';

    /**
     * Security check
     */
    if (!xarSecurityCheck('AdminAddressBook',0)) return FALSE;

    extract($args);

    $invalid = array();
    if (!isset($id)) { $invalid[] = 'id'; }
    if (!isset($del)) { $invalid[] = 'del'; }
    if (!isset($name)) { $invalid[] = 'name'; }
    if (!isset($newname)) { $invalid[] = 'newname'; }
    if (count($invalid) > 0) {
        $msg = xarML('Invalid #(1) in function #(2)() in module #(3)',
                     join(', ',$invalid), 'updatelabels', 'addressbook');
        xarErrorSet(XAR_SYSTEM_EXCEPTION, 'BAD_PARAM',
                    new SystemException($msg));
        return FALSE;
    }

    if(is_array($del)) {
        $dels = implode(',',$del);
    }
    $modID = $modName = array();

    if(isset($id) && is_array($id)) {
        foreach($id as $k=>$i) {
            $found = false;
            if(!empty($dels) && count($del)) {
                foreach($del as $d) {
                    if($i == $d) {
                        $found = true;
                        break;
                    }
                }
            }
            if(!$found) {
                array_push($modID,$i);
                array_push($modName,$name[$k]);
            }
        }
    }

    $xarTables =& xarDBGetTables();
    $cat_table = $xarTables['addressbook_categories'];

    $updates = array();
    foreach($modID as $k=>$id) {
    array_push($updates,array('sql'=>"UPDATE $cat_table
                                         SET name = ?
                                       WHERE nr = ?"
                              ,'bindvars'=>array($modName[$k],$id)));
    }

    if(xarModAPIFunc('addressbook','admin','updateitems',array('tablename'=>'categories','updates'=>$updates))) {
        xarErrorSet(XAR_USER_EXCEPTION,
                    _AB_ERR_INFO,
                    new abUserException('UPDATE - '.xarML('successful')));
    }

    if(!empty($dels)) {
        $delete = "DELETE FROM $cat_table WHERE nr IN ($dels)";
        if(xarModAPIFunc('addressbook','admin','deleteitems',array('tablename'=>'categories','delete'=>$delete))) {
            xarErrorSet(XAR_USER_EXCEPTION,
                        _AB_ERR_INFO,
                        new abUserException('DELETE - '.xarML('successful')));
        }
    }

    if( (isset($newname)) && ($newname != '') ) {
        if(xarModAPIFunc('addressbook','admin','additems',array('tablename'=>'categories','name'=>$newname))) {
            xarErrorSet(XAR_USER_EXCEPTION,
                        _AB_ERR_INFO,
                        new abUserException('INSERT - '.xarML('successful')));
        }
    }

    // Return
    return TRUE;

} // END updatecategories

?>