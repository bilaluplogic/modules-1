<?php
function netquery_admin_flnew()
{
    if (!xarSecurityCheck('AddNetquery')) return;
    if (!xarVarFetch('phase', 'str:1:100', $phase, 'form', XARVAR_NOT_REQUIRED, XARVAR_PREP_FOR_DISPLAY)) return;
    if (!xarVarFetch('Submit', 'str:1:100', $Submit, 'Cancel', XARVAR_NOT_REQUIRED, XARVAR_PREP_FOR_DISPLAY)) return;
    switch(strtolower($phase))
    {
        case 'form':
        default:
            $data['stylesheet'] = xarModGetVar('netquery', 'stylesheet');
            $data['authid']         = xarSecGenAuthKey();
            $data['submitlabel']    = xarML('Submit');
            $data['cancellabel']    = xarML('Cancel');
            $data['colors'] = array('black', 'blue', 'purple', 'red', 'brown', 'orange', 'yellow', 'green', 'cyan', 'violet');
            break;
        case 'update':
            if ((!isset($Submit)) || ($Submit != xarML('Submit')))
            {
                xarResponseRedirect(xarModURL('netquery', 'admin', 'flview'));
            }
            if (!xarVarFetch('flag_flagnum', 'int:1:100000', $flag_flagnum)) return;
            if (!xarVarFetch('flag_keyword', 'str:1:20', $flag_keyword)) return;
            if (!xarVarFetch('flag_fontclr', 'str:1:20', $flag_fontclr)) return;
            if (!xarVarFetch('flag_lookup_1', 'str:1:100', $flag_lookup_1, '', XARVAR_NOT_REQUIRED)) return;
            if (!xarSecConfirmAuthKey()) return;
            if (!xarModAPIFunc('netquery', 'admin', 'flcreate',
                               array('flag_flagnum'  => $flag_flagnum,
                                     'flag_keyword'  => $flag_keyword,
                                     'flag_fontclr'  => $flag_fontclr,
                                     'flag_lookup_1' => $flag_lookup_1))) return;
            xarResponseRedirect(xarModURL('netquery', 'admin', 'flview'));
            break;
    }
    return $data;
}
?>