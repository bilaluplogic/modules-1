<?php
/**
 * Utility function to count the number of items held by this module
 *
 * @package modules
 * @copyright (C) 2007 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 *
 * @subpackage ITSP Module
 * @link http://xaraya.com/index.php/release/572.html
 * @author ITSP Module Development Team
 */
/**
 * Utility function to count the number of credits that are have been obtained
 *
 * @author MichelV <michelv@xarayahosting.nl>
 * @since 06 Feb 2007
 * @param int pitemid The plan item ID; needed when a total is required OPTIONAL
 * @param int itspid The ITSP ID OPTIONAL
 * @param int userid The user ID of the ITSP
 * @param int lcourseid OPTIONAL
 * @param int icourseid OPTIONAL
 * @return integer number of obtained credits for this plan item
 * @throws BAD_PARAM, DATABASE_ERROR
 */
function itsp_userapi_countobtained($args)
{
    extract ($args);
    if ((!isset($pitemid) || !is_numeric($pitemid)) && (!isset($itspid))) {
        $msg = xarML('Invalid #(1) for #(2) function #(3)() in module #(4)',
            'plan item ID', 'user', 'countobtained', 'ITSP');
        xarErrorSet(XAR_SYSTEM_EXCEPTION, 'BAD_PARAM',
            new SystemException($msg));
        return;
    }
    if (!isset($userid) && !isset($itspid)) {
        $msg = xarML('Invalid #(1) for #(2) function #(3)() in module #(4)',
            'user ID', 'user', 'countobtained', 'ITSP');
        xarErrorSet(XAR_SYSTEM_EXCEPTION, 'BAD_PARAM',
            new SystemException($msg));
        return;
    }
    // Set to zero, so we always return an integer
    $credits = 0;
    // See where we will get the credits from: this is deducted from the source

    if (isset($pitemid)) {
        $pitem = xarModApiFunc('itsp','user','splitrules',array('pitemid' => $pitemid));
        switch ($pitem['rule_source']) {
            case 'mix':
            case 'courses':
                $lcourses = xarModApiFunc('itsp','user','getall_courselinks',array('itspid'=>$itspid,'pitemid'=>$pitemid));
                $obtained = 0;
                foreach($lcourses as $lcourse) {
                    // Get all planned course that a student has been enrolled in
                    $plannedcourses = xarModApiFunc('courses','user','check_enrollstatus',array('courseid' => $lcourse['lcourseid'], 'userid' => $userid));
                    // Get the standard status for passed
                    $statusid = xarModGetVar('itsp','PassedStatus');
                    // get the credits for the courses
                    foreach($plannedcourses as $lcourse) {
                        if ($lcourse['studstatus'] == $statusid) {
                            $obtained = $lcourse['credits'];
                        }
                    }
                    $credits = $credits + $obtained;
                }
            break;
        }
    }

    if (isset($lcourseid) && is_numeric($lcourseid)) {
        // Linked course: get info for passed status
        // Get all planned course that a student has been enrolled in
        $plannedcourses = xarModApiFunc('courses','user','check_enrollstatus',array('courseid' => $lcourseid, 'userid' => $userid));
        // Get the standard status for passed
        $statusid = xarModGetVar('itsp','PassedStatus');
        // get the credits for the courses
        foreach($plannedcourses as $lcourse) {
            if ($lcourse['studstatus'] == $statusid) {
                $credits = $lcourse['credits'];
            }
        }
    }

    /* Get database setup */
    $dbconn =& xarDBGetConn();
    $xartable =& xarDBGetTables();
    // we can only count directly in our own courses table
    $table = $xartable['itsp_itsp_courses'];
    $now = time();
    if (isset($icourseid) && is_numeric($icourseid)) {

        $query = "SELECT xar_icoursecredits
                  FROM $table
                  WHERE xar_icourseid = ?
                  AND   xar_dateappr > ?
                  AND   xar_icoursedate < ?";
        $bindvars = array($icourseid, 0, $now);
    } else {
        $query = "SELECT SUM(xar_icoursecredits)
                  FROM $table
                  WHERE xar_pitemid = ?
                  AND   xar_itspid = ?
                  AND   xar_dateappr > ?
                  AND   xar_icoursedate < ?";
        $bindvars = array($pitemid, $itspid, 0,$now);
    }
    $result = &$dbconn->Execute($query,$bindvars);
    /* Check for an error with the database code, adodb has already raised
     * the exception so we just return
     */
    if (!$result) return;
    /* Obtain the number of items */
    list($icredits) = $result->fields;
    /* All successful database queries produce a result set, and that result
     * set should be closed when it has been finished with
     */
    $result->Close();

    // We do not want to return an empty value
    if (empty($icredits)) {
        $credits = $credits;
    } else {
        $credits = $credits + $icredits;
    }
    /* Return the number of credits */
    return $credits;
}
?>