<?php
// ----------------------------------------------------------------------
// Copyright (C) 2004: Marc Lutolf (marcinmilan@xaraya.com)
// Purpose of file:  Configuration functions for commerce
// ----------------------------------------------------------------------
//  based on:
//  (c) 2003 XT-Commerce
//  (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
//  (c) 2002-2003 osCommerce (oscommerce.sql,v 1.83); www.oscommerce.com
//  (c) 2003  nextcommerce (nextcommerce.sql,v 1.76 2003/08/25); www.nextcommerce.org
// ----------------------------------------------------------------------

function commerce_userapi_childs_in_category_count($args)
{
    sys::import('modules.xen.xarclasses.xenquery');
    $xartables = xarDBGetTables();
    extract($args);
    $q = new xenQuery('SELECT', $xartables['commerce_categories'], 'categories_id');
    $q->eq('parent_id', $categories_id);
    if(!$q->run()) return;
    $categories_count = 0;
    foreach ($q->output() as $categories) {
        $categories_count++;
        $categories_count += xarModAPIFunc('commerce', 'user', 'childs_in_category_count', array('categories_id' => $categories['categories_id']));
    }
    return $categories_count;
}
 ?>