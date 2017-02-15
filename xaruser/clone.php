<?php
/**
 * Publications Module
 *
 * @package modules
 * @subpackage publications module
 * @category Third Party Xaraya Module
 * @version 2.0.0
 * @copyright (C) 2012 Netspan AG
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @author Marc Lutolf <mfl@netspan.ch>
 */

function publications_user_clone()
{
    // Xaraya security
    if (!xarSecurityCheck('ModeratePublications')) return;

    if(!xarVarFetch('name',     'isset', $objectname,      NULL, XARVAR_DONT_SET)) {return;}
    if(!xarVarFetch('ptid',     'isset', $ptid,            NULL, XARVAR_DONT_SET)) {return;}
    if(!xarVarFetch('itemid',   'isset', $data['itemid'],  NULL, XARVAR_DONT_SET)) {return;}
    if(!xarVarFetch('confirm',  'int',   $confirm,         0, XARVAR_DONT_SET)) {return;}

    if (empty($data['itemid'])) return xarResponse::NotFound();

    // If a pubtype ID was passed, get the name of the pub object
    if (isset($ptid)) {
        $pubtypeobject = DataObjectMaster::getObject(array('name' => 'publications_types'));
        $pubtypeobject->getItem(array('itemid' => $ptid));
        $objectname = $pubtypeobject->properties['name']->value;
    }
    if (empty($objectname)) return xarResponse::NotFound();

    sys::import('modules.dynamicdata.class.objects.master');
    $data['object'] = DataObjectMaster::getObject(array('name' => $objectname));
    if (empty($data['object'])) return xarResponse::NotFound();

    $data['object']->getItem(array('itemid' => $data['itemid']));
    
    $data['authid'] = xarSecGenAuthKey();            
    $data['name'] = $data['object']->properties['name']->value;
    $data['label'] = $data['object']->label;
    xarTplSetPageTitle(xarML('Clone Publication #(1) in #(2)', $data['itemid'], $data['label']));
    
    if ($confirm) {
        if (!xarSecConfirmAuthKey()) return;
        
        // Get the name for the clone
        if(!xarVarFetch('newname',   'str', $newname,   "", XARVAR_NOT_REQUIRED)) {return;}
        if (empty($newname)) $newname = $data['name'] . "_copy";
        if ($newname == $data['name']) $newname = $data['name'] . "_copy";
        $newname = strtolower(str_ireplace(" ", "_", $newname));

        // Create the clone
        $data['object']->properties['name']->setValue($newname);
        $data['object']->properties['id']->setValue(0);
        $cloneid = $data['object']->createItem(array('itemid' => 0));

        // Create the clone's translations
        if(!xarVarFetch('clone_translations',   'int', $clone_translations,   0, XARVAR_NOT_REQUIRED)) {return;}
        if ($clone_translations) {
            // Get the info on all the objects to be cloned
            sys::import('xaraya.structures.query');
            $tables =& xarDB::getTables();
            $q = new Query();
            $q->addtable($tables['publications'], 'p');
            $q->addtable($tables['publications_types'], 'pt');
            $q->join('p.pubtype_id', 'pt.id');
            $q->eq('parent_id',$data['itemid']);
            $q->addfield('p.id AS id');
            $q->addfield('pt.name AS name');
            $q->run();
            
            // Clone each one
            foreach($q->output() as $item) {
                $object = DataObjectMaster::getObject(array('name' => $item['name']));
                $object->getItem(array('itemid' => $item['id']));
                $object->properties['parent']->value = $cloneid;
                $object->properties['id']->value = 0;
                $object->createItem(array('itemid' => 0));
            }
        }
        
        // Redirect if we came from somewhere else
        //$current_listview = xarSession::getVar('publications_current_listview');
        if (!empty($return_url)) {
            xarController::redirect($return_url);
        } elseif (!empty($current_listview)) {
            xarController::redirect($current_listview);
        } else {
            xarController::redirect(xarModURL('publications', 'user', 'modify', array('itemid' => $cloneid, 'name' => $objectname)));
        }
        return true;
    }
    return $data;
}
?>