<?php
/**
 * Extract function and arguments from short URLs
 *
 * @package modules
 * @copyright (C) 2006 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 *
 * @subpackage JpGraph Module
 * @link http://xaraya.com/index.php/release/819.html
 * @author JpGraph Module Development Team
 */
/**
 * Extract function and arguments from short URLs for this module, and pass
 * them back to xarGetRequestInfo()
 *
 * @author the JpGraph module development team
 * @param  array $params array containing the different elements of the virtual path
 * @return array containing func the function to be called and args the query
 *          string arguments, or empty if it failed
 */
function jpgraph_userapi_decode_shorturl($params)
{
    /* Initialise the argument list we will return */
    $args = array();
    $module = 'jpgraph';
    /* Check and see if we have a module alias */
    $aliasisset = xarModGetVar('jpgraph', 'useModuleAlias');
    $aliasname = xarModGetVar('jpgraph','aliasname');
    if (($aliasisset) && isset($aliasname)) {
        $usealias   = true;
    } else{
        $usealias = false;
    }

    /* Analyse the different parts of the virtual path
     * $params[1] contains the first part after index.php/jpgraph
     * In general, you should be strict in encoding URLs, but as liberal
     * as possible in trying to decode them...
     */
    if ($params[0] != $module) { /* it's possibly some type of alias */
        $aliasname = xarModGetVar('jpgraph','aliasname');
    }
    if (empty($params[1])) {
        /*( nothing specified -> we'll go to the main function */
        return array('main', $args);
    } elseif (preg_match('/^index/i', $params[1])) {
        /* some search engine/someone tried using index.html (or similar)
         * -> we'll go to the main function
         */
        return array('main', $args);
    } elseif (preg_match('/^list/i', $params[1])) {
        /* something that starts with 'list' is probably for the view function
         * Note : make sure your encoding/decoding is consistent ! :-)
         */
        return array('view', $args);
    } elseif (preg_match('/^(\d+)/', $params[1], $matches)) {
        /* something that starts with a number must be for the display function
         * Note : make sure your encoding/decoding is consistent ! :-)
         */
        $exid = $matches[1];
        $args['exid'] = $exid;
        return array('display', $args);
    } else {
        /* the first part might be something variable like a category name
         * In order to match that, you'll have to retrieve all relevant
         * categories for this module, and compare against them...
         * $cid = xarModGetVar('jpgraph','mastercids');
         * if (xarModAPILoad('categories','user')) {
         * $cats = xarModAPIFunc('categories',
         * 'user',
         * 'getcat',
         * array('cid' => $cid,
         * 'return_itself' => true,
         * 'getchildren' => true));
         * // lower-case for fanciful search engines/people
         * $params[1] = strtolower($params[1]);
         * $foundcid = 0;
         * foreach ($cats as $cat) {
         * if ($params[1] == strtolower($cat['name'])) {
         * $foundcid = $cat['cid'];
         * break;
         * }
         * }
         * // check if we found a matching category
         * if (!empty($foundcid)) {
         * $args['cid'] = $foundcid;
         * // TODO: now analyse $params[2] for index, list, \d+ etc.
         * // and return array('whatever', $args);
         * }
         * }
         * we have no idea what this virtual path could be, so we'll just
         * forget about trying to decode this thing
         * you *could* return the main function here if you want to
         * return array('main', $args);
         */
    }
    /* default : return nothing -> no short URL decoded */
}
?>