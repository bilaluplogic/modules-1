<?php

/**
 * udpate item from categories_admin_modify
 */
function categories_admin_modifycat()
{
    if (!xarVarFetch('creating', 'bool', $creating, true, XARVAR_NOT_REQUIRED)) {return;}

    if (!xarVarFetch('return_url',  'isset',  $return_url, NULL, XARVAR_DONT_SET)) {return;}
    if (!xarVarFetch('itemid', 'int', $itemid, NULL, XARVAR_DONT_SET)) return;
    if (!xarVarFetch('itemtype',    'int',    $itemtype, 2, XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('cid','int::', $cid, NULL, XARVAR_DONT_SET)) {return;}
    $cid = isset($itemid) ? $itemid : $cid;
    if (empty($cid)) {
        if(!xarVarFetch('repeat','int:1:', $repeat, 1, XARVAR_NOT_REQUIRED)) {return;}
    } else {
        $repeat = 1;
    }


    $data = array();

    $data['imageoptions'] = array();
    $data['imageoptions'][] = array('id' => '', 'name' => xarML('-- No image --'));
    $image_array = xarModAPIFunc('categories','visual','findimages');
    foreach ($image_array as $image) {
        $data['imageoptions'][] = array('id' => $image, 'name' => $image);
    }

    $data['repeat'] = $repeat;

    if (!empty($cid)) {
        //Editing an existing category

        // Security check
        if(!xarSecurityCheck('EditCategories',1,'All',"All:$cid")) return;

        // Setting up necessary data.
        $data['cid'] = $cid;
        $data['category'] = xarModAPIFunc('categories',
                                          'user',
                                          'getcatinfo',
                                          array('cid' => $cid));

        $categories = xarModAPIFunc('categories',
                                    'user',
                                    'getcat',
                                    array('cid' => false,
                                          'eid' => $cid,
                                          'getchildren' => true));

        $data['func'] = 'modify';

        $catinfo = $data['category'];
        $catinfo['module'] = 'categories';
        $catinfo['itemtype'] = $itemtype;
        $catinfo['itemid'] = $cid;
        $hooks = xarModCallHooks('item','modify',$cid,$catinfo);
        if (empty($hooks)) {
            $data['hooks'] = '';
        } else {
            $data['hooks'] = $hooks;
        }

    } else {
        // Adding a new Category

        if(!xarSecurityCheck('AddCategories')) return;

        // Setting up necessary data.
        $categories = xarModAPIFunc('categories',
                                    'user',
                                    'getcat',
                                    array('cid' => false,
                                          'getchildren' => true));

        $catinfo = array();
        $catinfo['module'] = 'categories';
        $catinfo['itemtype'] = $itemtype;
        $catinfo['itemid'] = '';
        $hooks = xarModCallHooks('item','new','',$catinfo);
        if (empty($hooks)) {
            $data['hooks'] = '';
        } else {
            $data['hooks'] = $hooks;
        }

        $data['category'] = Array('left'=>0,'right'=>0,'name'=>'','description'=>'');
        $data['func'] = 'create';
        $data['cid'] = NULL;
    }

    $category_Stack = array ();

    foreach ($categories as $key => $category) {
        $categories[$key]['slash_separated'] = '';

        while ((count($category_Stack) > 0 ) &&
               ($category_Stack[count($category_Stack)-1]['indentation'] >= $category['indentation'])
              ) {
           array_pop($category_Stack);
        }

        foreach ($category_Stack as $stack_cat) {
                $categories[$key]['slash_separated'] .= $stack_cat['name'].'&#160;/&#160;';
        }

        array_push($category_Stack, $category);
        $categories[$key]['slash_separated'] .= $category['name'];
    }

    $data['categories'] = $categories;
    $data['itemtype'] = $itemtype;
    $data['return_url'] = $return_url;

    // Return output
    return xarTplModule('categories','admin','editcat',$data);
}

?>