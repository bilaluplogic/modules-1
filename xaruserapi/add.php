<?php
/**
 * Comments module - Allows users to post comments on items
 *
 * @package modules
 * @copyright (C) 2002-2007 The copyright-placeholder
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 *
 * @subpackage comments
 * @link http://xaraya.com/index.php/release/14.html
 * @author Carl P. Corliss <rabbitt@xaraya.com>
 */
/**
 * Adds a comment to the database based on the objectid/modid pair
 *
 * @author   Carl P. Corliss (aka rabbitt)
 * @access   public
 * @param    integer     $args['modid']      the module id
 * @param    integer     $args['itemtype']   the item type
 * @param    string      $args['objectid']   the item id
 * @param    integer     $args['pid']        the parent id
 * @param    string      $args['title']    the title (title) of the comment
 * @param    string      $args['comment']    the text (body) of the comment
 * @param    integer     $args['postanon']   whether or not this post is gonna be anonymous
 * @param    integer     $args['author']     user id of the author (for API access)
 * @param    string      $args['hostname']   hostname (for API access)
 * @param    datetime    $args['date']       date of the comment (for API access)
 * @param    integer     $args['id']        comment id (for API access - import only)
 * @returns  integer     the id of the new comment
 */
function comments_userapi_add($args)
{
    extract($args);

    if (!isset($modid) || empty($modid)) {
        $msg = xarML('Missing #(1) for #(2) function #(3)() in module #(4)',
                                 'modid', 'userapi', 'add', 'comments');
        throw new BadParameterException($msg);
    }

    if (empty($itemtype) || !is_numeric($itemtype)) {
        $itemtype = 0;
    }

    if (!isset($objectid) || empty($objectid)) {
        $msg = xarML('Missing #(1) for #(2) function #(3)() in module #(4)',
                                 'objectid', 'userapi', 'add', 'comments');
        throw new BadParameterException($msg);
    }

    if (!isset($pid) || empty($pid)) {
        $pid = 0;
    }

    if (!isset($title) || empty($title)) {
        $msg = xarML('Missing #(1) for #(2) function #(3)() in module #(4)',
                                 'title', 'userapi', 'add', 'comments');
        throw new BadParameterException($msg);
    }

    if (!isset($comment) || empty($comment)) {
        $msg = xarML('Missing #(1) for #(2) function #(3)() in module #(4)',
                                 'comment text', 'userapi', 'add', 'comments');
        throw new BadParameterException($msg);
    }

    if (!isset($postanon) || empty($postanon)) {
        $postanon = 0;
    }

    if (!isset($author)) {
        $author = xarUserGetVar('id');
    }

    if (!isset($hostname)) {
        $forwarded = xarServer::getVar('HTTP_X_FORWARDED_FOR');
        if (!empty($forwarded)) {
            $hostname = preg_replace('/,.*/', '', $forwarded);
        } else {
            $hostname = xarServer::getVar('REMOTE_ADDR');
        }
    }

    // Lets check the blacklist first before we process.
    // If the comment does not pass, we will return an exception
    // Perhaps in the future we can store the comment for later
    // review, but screw it for now...
    if (xarModVars::get('comments', 'useblacklist') == true){
        $items = xarMod::apiFunc('comments', 'user', 'get_blacklist');
        foreach ($items as $item) {
            if (preg_match("/$item[domain]/i", $comment)){
                $msg = xarML('Your entry has triggered comments moderation due to suspicious URL entry');
                throw new BadParameterException($msg);
            }
        }
    }

    $dbconn = xarDB::getConn();
    $xartable = xarDB::getTables();

    // parentid == zero then we need to find the root nodes
    // left and right values cuz we're adding the new comment
    // as a top level comment
    if ($pid == 0) {
        $root_lnr = xarMod::apiFunc('comments','user','get_node_root',
                                   array('modid' => $modid,
                                         'objectid' => $objectid,
                                         'itemtype' => $itemtype));

        // ok, if the there was no root left and right values then
        // that means this is the first comment for this particular
        // modid/objectid combo -- so we need to create a dummy (root)
        // comment from which every other comment will branch from
        if (!count($root_lnr)) {
            $pid = xarMod::apiFunc('comments','user','add_rootnode',
                                  array('modid'    => $modid,
                                        'objectid' => $objectid,
                                        'itemtype' => $itemtype));
        } else {
            $pid = $root_lnr['id'];
        }
    }

    // pid should now always have a value
    assert($pid!=0 && !empty($pid));

    // grab the left and right values from the parent
    $parent_lnr = xarMod::apiFunc('comments',
                                'user',
                                'get_node_lrvalues',
                                 array('id' => $pid));

    // there should be -at-least- one affected row -- if not
    // then raise an exception. btw, at the very least,
    // the 'right' value of the parent node would have been affected.
    if (!xarMod::apiFunc('comments',
                       'user',
                       'create_gap',
                        array('startpoint' => $parent_lnr['right_id'],
                              'modid'      => $modid,
                              'objectid'   => $objectid,
                              'itemtype'   => $itemtype))) {

            $msg  = xarML('Unable to create gap in tree for comment insertion! Comments table has possibly been corrupted.');
            $msg .= xarML('Please seek help on the public-developer list xaraya_public-dev@xaraya.com, or in the #support channel on Xaraya\'s IRC network.');
            throw new Exception($msg);
    }

    $cdate    = time();
    $left     = $parent_lnr['right_id'];
    $right    = $left + 1;
    if($modid == xarMod::getID('comments')) {
        $status   = xarModVars::get('comments','AuthorizeComments') ? _COM_STATUS_OFF : _COM_STATUS_ON;
    } elseif (!isset($status) || !is_numeric($status)) {
        // no reasonable default for this, so we'll throw an error
        $msg = xarML('Missing or invalid status parameter');
        throw new BadParameterException($msg);
    }

    /*if (!isset($id)) {
        $id = $dbconn->GenId($xartable['comments']);
    }*/


	sys::import('modules.dynamicdata.class.objects.master');
	$object = DataObjectMaster::getObject(array(
							'name' => 'comments'
		));

	if (!is_object($object)) return;   

	$fields = array(
				 'text',
                 'modid',
                 'itemtype',
                 'objectid',
                 'author',
                 'title', 
                 'hostname',
                 'left_id',
                 'right_id',
				'permalink',
                 'pid',
                 'status');

	$text = $comment;
	$left_id = $left;
	$right_id = $right;

	foreach ($fields as $field) {
		$object->properties[$field]->setValue($$field);
	}
	$bdate = (isset($date)) ? $date : $cdate;
	$object->properties['date']->setValue($bdate);
	$bpostanon = isset($postanon) ? 0 : 1;
	$object->properties['anonpost']->setValue($bpostanon);

	$id = $object->createItem();

    /*$sql = "INSERT INTO $xartable[comments]
                (id,
                 modid,
                 itemtype,
                 objectid,
                 author,
                 title,
                 date,
                 hostname,
                 text,
                 left_id,
                 right_id,
                 pid,
                 status,
                 anonpost)
          VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
    $bdate = (isset($date)) ? $date : $cdate;
    $bpostanon = (empty($postanon)) ? 0 : 1;
    $bindvars = array($id, $modid, $itemtype, $objectid, $author, $title, $bdate, $hostname, $comment, $left, $right, $pid, $status, $bpostanon);

    $result = &$dbconn->Execute($sql,$bindvars);*/

    if (!is_numeric($id)) {
        return;
    } else {
        //$id = $dbconn->PO_Insert_ID($xartable['comments'], 'id');
        // CHECKME: find some cleaner way to update the page cache if necessary
        if (function_exists('xarOutputFlushCached') &&
            xarModVars::get('xarcachemanager','FlushOnNewComment')) {
            $modinfo = xarModGetInfo($modid);
            xarOutputFlushCached("$modinfo[name]-");
            xarOutputFlushCached("comments-block");
        }
        // Call create hooks for categories, hitcount etc.
        $args['module'] = 'comments';
        $args['itemtype'] = 0;
        $args['itemid'] = $id;
        // pass along the current module & itemtype for pubsub (urgh)
// FIXME: handle 2nd-level hook calls in a cleaner way - cfr. categories navigation, comments add etc.
        $args['id'] = 0; // dummy category
        $modinfo = xarModGetInfo($modid);
        $args['current_module'] = $modinfo['name'];
        $args['current_itemtype'] = $itemtype;
        $args['current_itemid'] = $objectid;
        xarModCallHooks('item', 'create', $id, $args);
        return $id;
    }
}
?>