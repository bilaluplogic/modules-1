<?php
/**
 * Helpdesk Module
 *
 * @package modules
 * @copyright (C) 2002-2006 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 *
 * @subpackage Helpdesk Module
 * @link http://www.abraisontechnoloy.com/
 * @author Brian McGilligan <brianmcgilligan@gmail.com>
 */
/**
  Modify a ticket item

  @author Brian McGilligan
  @return Template data
*/
function helpdesk_user_modify($args)
{
    if( !xarModAPILoad('helpdesk', 'user') ){ return false; }
    if( !xarModAPILoad('security', 'user') ){ return false; }

    if( !xarVarFetch('tid',        'int:1:',  $tid,        null,  XARVAR_NOT_REQUIRED) ) { return false; }
    if( !xarVarFetch('confirm',    'isset',   $confirm,    null,  XARVAR_NOT_REQUIRED) ) { return false; }
    if( !xarVarFetch('itemtype',   'int',     $itemtype,   TICKET_ITEMTYPE, XARVAR_NOT_REQUIRED) ) { return false; }
    extract($args);

    /*
        Security check to prevent un authorized users from modifying it
    */
    if( !Security::check(SECURITY_WRITE, 'helpdesk', $itemtype, $tid) ){ return false; }

    /*
        Get the ticket Data
    */
    $ticket = xarModAPIFunc('helpdesk','user','getticket',
        array(
            'tid'            => $tid,
            'security_level' => SECURITY_WRITE
        )
    );

    // If we have confirmation do the update
    if( !empty($confirm) )
    {
        if ( !xarSecConfirmAuthKey() ){ return false; }

        if( !xarVarFetch('userid',     'str:1:',  $userid,  null,  XARVAR_NOT_REQUIRED) ){ return false; }
        if( !xarVarFetch('name',       'str:1:',  $name,  null,  XARVAR_NOT_REQUIRED) ){ return false; }
        if( !xarVarFetch('phone',      'str:1:',  $phone,  null,  XARVAR_NOT_REQUIRED) ){ return false; }
        if( !xarVarFetch('subject',    'str:1:',  $subject,  null,  XARVAR_NOT_REQUIRED) ){ return false; }
        if( !xarVarFetch('domain',     'str:1:',  $domain,  null,  XARVAR_NOT_REQUIRED) ){ return false; }
        if( !xarVarFetch('priority',   'str:1:',  $priority,  null,  XARVAR_NOT_REQUIRED) ){ return false; }
        if( !xarVarFetch('status',     'str:1:',  $statusid,  null,  XARVAR_NOT_REQUIRED) ){ return false; }
        if( !xarVarFetch('openedby',   'str:1:',  $openedby,  null,  XARVAR_NOT_REQUIRED) ){ return false; }
        if( !xarVarFetch('assignedto', 'str:1:',  $assignedto,  null,  XARVAR_NOT_REQUIRED) ){ return false; }
        if( !xarVarFetch('source',     'str:1:',  $source,  null,  XARVAR_NOT_REQUIRED) ){ return false; }
        if( !xarVarFetch('closedby',   'str:1:',  $closedby,  null,  XARVAR_NOT_REQUIRED) ){ return false; }

        $params = array(
            'tid'        => $tid,
            'userid'     => $userid,
            'name'       => $name,
            'subject'    => $subject,
            'phone'      => $phone,
            'domain'     => $domain,
            'priority'   => $priority,
            'statusid'   => $statusid,
            'openedby'   => $openedby,
            'assignedto' => $assignedto,
            'source'     => $source,
            'closedby'   => $closedby
        );
        $result = xarModAPIFunc('helpdesk', 'user', 'update', $params);

        /**
            Send an e-mail to user when the ticket is closed
            @author MichelV.
            $mail needs to be set
        */
        $resolved_statuses = xarModAPIFunc('helpdesk', 'user', 'get_resolved_statuses');
        if( in_array($statusid, $resolved_statuses) == true )
        {
            $mailaction = 'closed';
            $params['mailaction'] = $mailaction;
            // This breaks if an anon user.  Nee
            $params['email']      = $ticket['email'];
            $params['status']     = $statusid;
            $mail = xarModFunc('helpdesk','user','sendmail', $params);
            // Check if the email has been sent.
            if( $mail === false ){ return false; }
        }

        $item = array();
        $item['module'] = 'helpdesk';
        $item['itemtype'] = $itemtype;
        $hooks = xarModCallHooks('item', 'update', $tid, $item);

        xarResponseRedirect(xarModURL('helpdesk', 'user', 'view'));

        return true;
    }

    $data = array();
    $data['ticketdata'] = $ticket;

    /*
        These funcs should be rethought once we get the rest working
    */
    $data['priority'] = xarModAPIFunc('helpdesk', 'user', 'gets',
        array(
            'itemtype' => PRIORITY_ITEMTYPE
        )
    );
    $data['status'] = xarModAPIFunc('helpdesk', 'user', 'gets',
        array(
            'itemtype' => STATUS_ITEMTYPE
        )
    );
    $data['sources'] = xarModAPIFunc('helpdesk', 'user', 'gets',
        array(
            'itemtype' => SOURCE_ITEMTYPE
        )
    );
    $data['reps'] = xarModAPIFunc('helpdesk', 'user', 'gets',
        array(
            'itemtype' => REPRESENTATIVE_ITEMTYPE
        )
    );

    $data['users'] = xarModAPIFunc('roles', 'user', 'getall');

    $item = array();
    $item['module'] = 'helpdesk';
    $item['itemtype'] = $itemtype;
    $data['hooks'] = xarModCallHooks('item', 'modify', $tid, $item);

    $data['module']         = 'helpdesk';
    $data['itemtype']       = $itemtype;
    $data['tid']            = $tid;
    //$data['menu']           = xarModFunc('helpdesk', 'user', 'menu');
    //$data['UserLoggedIn']   = xarUserIsLoggedIn();
    $data['enabledimages']  = xarModGetVar('helpdesk', 'Enable Images');
    //$data['summary']        = xarModFunc('helpdesk', 'user', 'summaryfooter');

    return xarTplModule('helpdesk', 'user', 'modify', $data);
}
?>