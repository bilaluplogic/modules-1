<?php
// ----------------------------------------------------------------------
// Copyright (C) 2004: Marc Lutolf (marcinmilan@xaraya.com)
// Purpose of file:  Configuration functions for commerce
// ----------------------------------------------------------------------
//  based on:
//  (c) 2003 XT-Commerce
//   based on Third Party contribution:
//   Customers Status v3.x  (c) 2002-2003 Copyright Elari elari@free.fr | www.unlockgsm.com/dload-osc/ | CVS : http://cvs.sourceforge.net/cgi-bin/viewcvs.cgi/elari/?sortby=date#dirlist
//  (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
//  (c) 2002-2003 osCommerce (oscommerce.sql,v 1.83); www.oscommerce.com
//  (c) 2003  nextcommerce (nextcommerce.sql,v 1.76 2003/08/25); www.nextcommerce.org
// ----------------------------------------------------------------------

function commerce_admin_customer_status()
{

   Released under the GNU General Public License
   --------------------------------------------------------------*/



  switch ($_GET['action']) {
    case 'insert':
    case 'save':
      $customers_status_id = xtc_db_prepare_input($_GET['cID']);

      $languages = xtc_get_languages();
      for ($i=0; $i<sizeof($languages); $i++) {
        $customers_status_name_array = $_POST['customers_status_name'];
        $customers_status_show_price = $_POST['customers_status_show_price'];
        $customers_status_show_price_tax = $_POST['customers_status_show_price_tax'];
        $customers_status_public = $_POST['customers_status_public'];
        $customers_status_discount = $_POST['customers_status_discount'];
        $customers_status_ot_discount_flag = $_POST['customers_status_ot_discount_flag'];
        $customers_status_ot_discount = $_POST['customers_status_ot_discount'];
        $customers_status_graduated_prices = $_POST['customers_status_graduated_prices'];
        $customers_status_discount_attributes = $_POST['customers_status_discount_attributes'];
        $customers_status_add_tax_ot = $_POST['customers_status_add_tax_ot'];
        $customers_status_payment_unallowed = $_POST['customers_status_payment_unallowed'];
        $customers_status_shipping_unallowed = $_POST['customers_status_shipping_unallowed'];

        $language_id = $languages[$i]['id'];

          $q->addfield('customers_status_name',xtc_db_prepare_input($customers_status_name_array[$language_id]));
          $q->addfield('customers_status_public',xtc_db_prepare_input($customers_status_public));
          $q->addfield('customers_status_show_price',xtc_db_prepare_input($customers_status_show_price));
          $q->addfield('customers_status_show_price_tax',xtc_db_prepare_input($customers_status_show_price_tax));
          $q->addfield('customers_status_discount',xtc_db_prepare_input($customers_status_discount));
          $q->addfield('customers_status_ot_discount_flag',xtc_db_prepare_input($customers_status_ot_discount_flag));
          $q->addfield('customers_status_ot_discount',xtc_db_prepare_input($customers_status_ot_discount));
          $q->addfield('customers_status_graduated_prices',xtc_db_prepare_input($customers_status_graduated_prices));
          $q->addfield('customers_status_add_tax_ot',xtc_db_prepare_input($customers_status_add_tax_ot));
          $q->addfield('customers_status_payment_unallowed',xtc_db_prepare_input($customers_status_payment_unallowed));
          $q->addfield('customers_status_shipping_unallowed',xtc_db_prepare_input($customers_status_shipping_unallowed));
          $q->addfield('customers_status_discount_attributes',xtc_db_prepare_input($customers_status_discount_attributes));
        if ($_GET['action'] == 'insert') {
          if (!xarModAPIFunc('commerce','user','not_null',array('arg' => $customers_status_id))) {
            $next_id_query = new xenQuery("select max(customers_status_id) as customers_status_id from " . TABLE_CUSTOMERS_STATUS . "");
      $q = new xenQuery();
      if(!$q->run()) return;
            $next_id = $q->output();
            $customers_status_id = $next_id['customers_status_id'] + 1;
            // We want to create a personal offer table corresponding to each customers_status
            new xenQuery("create table personal_offers_by_customers_status_" . $customers_status_id . " (price_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, products_id int NOT NULL, quantity int, personal_offer decimal(15,4))");
          }

          $insert_sql_data = array('customers_status_id' => xtc_db_prepare_input($customers_status_id), 'language_id' => xtc_db_prepare_input($language_id));
          $sql_data_array = xtc_array_merge($sql_data_array, $insert_sql_data);
          xtc_db_perform(TABLE_CUSTOMERS_STATUS, $sql_data_array);

        } elseif ($_GET['action'] == 'save') {
          xtc_db_perform(TABLE_CUSTOMERS_STATUS, $sql_data_array, 'update', "customers_status_id = '" . xtc_db_input($customers_status_id) . "' and language_id = '" . $language_id . "'");
        }
      }

      if ($customers_status_image = new upload('customers_status_image', DIR_WS_ICONS)) {
        new xenQuery("update " . TABLE_CUSTOMERS_STATUS . " set customers_status_image = '" . $customers_status_image->filename . "' where customers_status_id = '" . xtc_db_input($customers_status_id) . "'");
      }

      if ($_POST['default'] == 'on') {
        new xenQuery("update " . TABLE_CONFIGURATION . " set configuration_value = '" . xtc_db_input($customers_status_id) . "' where configuration_key = 'DEFAULT_CUSTOMERS_STATUS_ID'");
      }

      xarRedirectResponse(xarModURL('commerce','admin',(FILENAME_CUSTOMERS_STATUS, 'page=' . $_GET['page'] . '&cID=' . $customers_status_id));
      break;

    case 'deleteconfirm':
      $cID = xtc_db_prepare_input($_GET['cID']);

      $customers_status_query = new xenQuery("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'DEFAULT_CUSTOMERS_STATUS_ID'");
      $q = new xenQuery();
      if(!$q->run()) return;
      $customers_status = $q->output();
      if ($customers_status['configuration_value'] == $cID) {
        new xenQuery("update " . TABLE_CONFIGURATION . " set configuration_value = '' where configuration_key = 'DEFAULT_CUSTOMERS_STATUS_ID'");
      }

      new xenQuery("delete from " . TABLE_CUSTOMERS_STATUS . " where customers_status_id = '" . xtc_db_input($cID) . "'");

      // We want to drop the existing corresponding personal_offers table
      new xenQuery("drop table IF EXISTS personal_offers_by_customers_status_" . xtc_db_input($cID) . "");
      xarRedirectResponse(xarModURL('commerce','admin',(FILENAME_CUSTOMERS_STATUS, 'page=' . $_GET['page']));
      break;

    case 'delete':
      $cID = xtc_db_prepare_input($_GET['cID']);

      $status_query = new xenQuery("select count(*) as count from " . TABLE_CUSTOMERS . " where customers_status = '" . xtc_db_input($cID) . "'");
      $q = new xenQuery();
      if(!$q->run()) return;
      $status = $q->output();

      $remove_status = true;
      if (($cID == DEFAULT_CUSTOMERS_STATUS_ID) || ($cID == DEFAULT_CUSTOMERS_STATUS_ID_GUEST) || ($cID == DEFAULT_CUSTOMERS_STATUS_ID_NEWSLETTER)) {
        $remove_status = false;
        $messageStack->add(ERROR_REMOVE_DEFAULT_CUSTOMERS_STATUS, 'error');
      } elseif ($status['count'] > 0) {
        $remove_status = false;
        $messageStack->add(ERROR_STATUS_USED_IN_CUSTOMERS, 'error');
      } else {
        $history_query = new xenQuery("select count(*) as count from " . TABLE_CUSTOMERS_STATUS_HISTORY . " where '" . xtc_db_input($cID) . "' in (new_value, old_value)");
      $q = new xenQuery();
      if(!$q->run()) return;
        $history = $q->output();
        if ($history['count'] > 0) {
          $remove_status = false;
          $messageStack->add(ERROR_STATUS_USED_IN_HISTORY, 'error');
        }
      }
      break;
  }

  $customers_status_ot_discount_flag_array = array(array('id' => '0', 'text' => ENTRY_NO), array('id' => '1', 'text' => ENTRY_YES));
  $customers_status_graduated_prices_array = array(array('id' => '0', 'text' => ENTRY_NO), array('id' => '1', 'text' => ENTRY_YES));
  $customers_status_public_array = array(array('id' => '0', 'text' => ENTRY_NO), array('id' => '1', 'text' => ENTRY_YES));
  $customers_status_show_price_array = array(array('id' => '0', 'text' => ENTRY_NO), array('id' => '1', 'text' => ENTRY_YES));
  $customers_status_show_price_tax_array = array(array('id' => '0', 'text' => ENTRY_NO), array('id' => '1', 'text' => ENTRY_YES));
  $customers_status_discount_attributes_array = array(array('id' => '0', 'text' => ENTRY_NO), array('id' => '1', 'text' => ENTRY_YES));
  $customers_status_add_tax_ot_array = array(array('id' => '0', 'text' => ENTRY_NO), array('id' => '1', 'text' => ENTRY_YES));

  $customers_status_query_raw = "select *� from " . TABLE_CUSTOMERS_STATUS . " where language_id = '" . $_SESSION['languages_id'] . "' order by customers_status_id";

  $customers_status_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $customers_status_query_raw, $customers_status_query_numrows);
  $customers_status_query = new xenQuery($customers_status_query_raw);
      $q = new xenQuery();
      if(!$q->run()) return;
  while ($customers_status = $q->output()) {
    if (((!$_GET['cID']) || ($_GET['cID'] == $customers_status['customers_status_id'])) && (!$cInfo) && (substr($_GET['action'], 0, 3) != 'new')) {
      $cInfo = new objectInfo($customers_status);
    }

    if ( (is_object($cInfo)) && ($customers_status['customers_status_id'] == $cInfo->customers_status_id) ) {
      echo '<tr class="dataTableRowSelected" onmouseover="this.style.cursor=\'hand\'" onclick="document.location.href=\'' . xarModURL('commerce','admin',(FILENAME_CUSTOMERS_STATUS, 'page=' . $_GET['page'] . '&cID=' . $cInfo->customers_status_id . '&action=edit') . '\'">' . "\n";
    } else {
      echo '<tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'hand\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . xarModURL('commerce','admin',(FILENAME_CUSTOMERS_STATUS, 'page=' . $_GET['page'] . '&cID=' . $customers_status['customers_status_id']) . '\'">' . "\n";
    }

    echo '<td class="dataTableContent" align="left">';
     if ($customers_status['customers_status_image'] != '') {
       echo xtc_image(xarTplGetImage(DIR_WS_ICONS . $customers_status['customers_status_image']), IMAGE_ICON_INFO);
     }
     echo '</td>';

     echo '<td class="dataTableContent" align="left">';
     echo xtc_get_status_users($customers_status['customers_status_id']);
     echo '</td>';

    if ($customers_status['customers_status_id'] == DEFAULT_CUSTOMERS_STATUS_ID ) {
      echo '<td class="dataTableContent" align="left"><b>' . $customers_status['customers_status_name'];
      echo ' (' . TEXT_DEFAULT . ')';
    } else {
      echo '<td class="dataTableContent" align="left">' . $customers_status['customers_status_name'];
    }
    if ($customers_status['customers_status_public'] == '1') {
      echo ' ,public ';
    }
    echo '</b></td>';

    if ($customers_status['customers_status_show_price'] == '1') {
      echo '<td nowrap class="smallText" align="center">� ';
      if ($customers_status['customers_status_show_price_tax'] == '1') {
        echo TAX_YES;
      } else {
        echo TAX_NO;
      }
    } else {
      echo '<td class="smallText" align="left"> ';
    }
    echo '</td>';

    echo '<td nowrap class="smallText" align="center">' . $customers_status['customers_status_discount'] . ' %</td>';

    echo '<td nowrap class="dataTableContent" align="center">';
    if ($customers_status['customers_status_ot_discount_flag'] == 0){
      echo '<font color="ff0000">'.$customers_status['customers_status_ot_discount'].' %</font>';
    } else {
      echo $customers_status['customers_status_ot_discount'].' %';
    }
    echo ' </td>';

    echo '<td class="dataTableContent" align="center">';
    if ($customers_status['customers_status_graduated_prices'] == 0) {
      echo NO;
    } else {
      echo YES;
    }
    echo '</td>';
    echo '<td nowrap class="smallText" align="center">' . $customers_status['customers_status_payment_unallowed'] . '</td>';
    echo '<td nowrap class="smallText" align="center">' . $customers_status['customers_status_shipping_unallowed'] . '</td>';
    echo "\n";
?>
                <td class="dataTableContent" align="right"><?php if ( (is_object($cInfo)) && ($customers_status['customers_status_id'] == $cInfo->customers_status_id) ) { echo xtc_image(xarTplGetImage(DIR_WS_IMAGES . 'icon_arrow_right.gif'), ''); } else { echo '<a href="' . xarModURL('commerce','admin',(FILENAME_CUSTOMERS_STATUS, 'page=' . $_GET['page'] . '&cID=' . $customers_status['customers_status_id']) . '">' . xtc_image(xarTplGetImage(DIR_WS_IMAGES . 'icon_info.gif'), IMAGE_ICON_INFO) . '</a>'; } ?>&#160;</td>
              </tr>
<?php
  }
?>
              <tr>
                <td colspan="6"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $customers_status_split->display_count($customers_status_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_CUSTOMERS_STATUS); ?></td>
                    <td class="smallText" align="right"><?php echo $customers_status_split->display_links($customers_status_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></td>
                  </tr>
<?php
  if (substr($_GET['action'], 0, 3) != 'new') {
?>
                  <tr>
                    <td colspan="2" align="right"><?php echo '<a href="' . xarModURL('commerce','admin',(FILENAME_CUSTOMERS_STATUS, 'page=' . $_GET['page'] . '&action=new') . '">' . xarModAPIFunc('commerce','user','image',array('src' => xarTplGetImage('buttons/' . xarSessionGetVar('language') . '/'.'button_insert.gif'),'alt' => IMAGE_INSERT); . '</a>'; ?></td>
                  </tr>
<?php
  }
?>
                </table></td>
              </tr>
            </table></td>
<?php
  $heading = array();
  $contents = array();
  switch ($_GET['action']) {
    case 'new':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_NEW_CUSTOMERS_STATUS . '</b>');
      $contents = array('form' => xtc_draw_form('status', FILENAME_CUSTOMERS_STATUS, 'page=' . $_GET['page'] . '&action=insert', 'post', 'enctype="multipart/form-data"'));
      $contents[] = array('text' => TEXT_INFO_INSERT_INTRO);
      $customers_status_inputs_string = '';
      $languages = xtc_get_languages();
      for ($i=0; $i<sizeof($languages); $i++) {
        $customers_status_inputs_string .= '<br>' . xtc_image(xarTplGetImage(DIR_WS_CATALOG.'lang/'.$languages[$i]['directory'].'/admin/images/' . $languages[$i]['image']), $languages[$i]['name']) . '&#160;' . xtc_draw_input_field('customers_status_name[' . $languages[$i]['id'] . ']');
      }
      $contents[] = array('text' => '<br>' . TEXT_INFO_CUSTOMERS_STATUS_NAME . $customers_status_inputs_string);
      $contents[] = array('text' => '<br>' . TEXT_INFO_CUSTOMERS_STATUS_IMAGE . '<br>' . xtc_draw_file_field('customers_status_image'));
      $contents[] = array('text' => '<br>' . TEXT_INFO_CUSTOMERS_STATUS_PUBLIC_INTRO . '<br>' . ENTRY_CUSTOMERS_STATUS_PUBLIC . ' ' . commerce_userapi_draw_pull_down_menu('customers_status_public', $customers_status_public_array, $cInfo->customers_status_public ));
      $contents[] = array('text' => '<br>' . TEXT_INFO_CUSTOMERS_STATUS_SHOW_PRICE_INTRO     . '<br>' . ENTRY_CUSTOMERS_STATUS_SHOW_PRICE . ' ' . commerce_userapi_draw_pull_down_menu('customers_status_show_price', $customers_status_show_price_array, $cInfo->customers_status_show_price ));
      $contents[] = array('text' => '<br>' . TEXT_INFO_CUSTOMERS_STATUS_SHOW_PRICE_TAX_INTRO . '<br>' . ENTRY_CUSTOMERS_STATUS_SHOW_PRICE_TAX . ' ' . commerce_userapi_draw_pull_down_menu('customers_status_show_price_tax', $customers_status_show_price_tax_array, $cInfo->customers_status_show_price_tax ));
      $contents[] = array('text' => '<br>' . TEXT_INFO_CUSTOMERS_STATUS_ADD_TAX_INTRO . '<br>' . ENTRY_CUSTOMERS_STATUS_ADD_TAX . ' ' . commerce_userapi_draw_pull_down_menu('customers_status_add_tax_ot', $customers_status_add_tax_ot_array, $cInfo->customers_status_add_tax_ot));
      $contents[] = array('text' => '<br>' . TEXT_INFO_CUSTOMERS_STATUS_DISCOUNT_PRICE_INTRO . '<br>' . TEXT_INFO_CUSTOMERS_STATUS_DISCOUNT_PRICE . '<br>' . xtc_draw_input_field('customers_status_discount', $cInfo->customers_status_discount));
      $contents[] = array('text' => '<br>' . TEXT_INFO_CUSTOMERS_STATUS_DISCOUNT_ATTRIBUTES_INTRO     . '<br>' . ENTRY_CUSTOMERS_STATUS_DISCOUNT_ATTRIBUTES . ' ' . commerce_userapi_draw_pull_down_menu('customers_status_discount_attributes', $customers_status_discount_attributes_array, $cInfo->customers_status_discount_attributes ));
      $contents[] = array('text' => '<br>' . TEXT_INFO_CUSTOMERS_STATUS_DISCOUNT_OT_XMEMBER_INTRO . '<br> ' . ENTRY_OT_XMEMBER . ' ' . commerce_userapi_draw_pull_down_menu('customers_status_ot_discount_flag', $customers_status_ot_discount_flag_array, $cInfo->customers_status_ot_discount_flag ). '<br>' . TEXT_INFO_CUSTOMERS_STATUS_DISCOUNT_PRICE . '<br>' . xtc_draw_input_field('customers_status_ot_discount', $cInfo->customers_status_ot_discount));
      $contents[] = array('text' => '<br>' . TEXT_INFO_CUSTOMERS_STATUS_GRADUATED_PRICES_INTRO . '<br>' . ENTRY_GRADUATED_PRICES . ' ' . commerce_userapi_draw_pull_down_menu('customers_status_graduated_prices', $customers_status_graduated_prices_array, $cInfo->customers_status_graduated_prices ));
      $contents[] = array('text' => '<br>' . TEXT_INFO_CUSTOMERS_STATUS_DISCOUNT_ATTRIBUTES_INTRO . '<br>' . ENTRY_CUSTOMERS_STATUS_DISCOUNT_ATTRIBUTES . ' ' . commerce_userapi_draw_pull_down_menu('customers_status_discount_attributes', $customers_status_discount_attributes_array, $cInfo->customers_status_discount_attributes ));
      $contents[] = array('text' => '<br>' . TEXT_INFO_CUSTOMERS_STATUS_PAYMENT_UNALLOWED_INTRO . '<br>' . ENTRY_CUSTOMERS_STATUS_PAYMENT_UNALLOWED . ' ' . xtc_draw_input_field('customers_status_payment_unallowed', $cInfo->customers_status_payment_unallowed ));
      $contents[] = array('text' => '<br>' . TEXT_INFO_CUSTOMERS_STATUS_SHIPPING_UNALLOWED_INTRO . '<br>' . ENTRY_CUSTOMERS_STATUS_SHIPPING_UNALLOWED . ' ' . xtc_draw_input_field('customers_status_shipping_unallowed', $cInfo->customers_status_shipping_unallowed ));
      $contents[] = array('text' => '<br>' . xtc_draw_checkbox_field('default') . ' ' . TEXT_SET_DEFAULT);
      $contents[] = array('align' => 'center', 'text' => '<br>' .
<input type="image" src="#xarTplGetImage('buttons/' . xarSessionGetVar('language') . '/'.'button_insert.gif')#" border="0" alt=IMAGE_INSERT>
      . ' <a href="' . xarModURL('commerce','admin',(FILENAME_CUSTOMERS_STATUS, 'page=' . $_GET['page']) . '">' . xarModAPIFunc('commerce','user','image',array('src' => xarTplGetImage('buttons/' . xarSessionGetVar('language') . '/'.'button_cancel.gif'),'alt' => IMAGE_CANCEL); . '</a>');
      break;

    case 'edit':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_EDIT_CUSTOMERS_STATUS . '</b>');
      $contents = array('form' => xtc_draw_form('status', FILENAME_CUSTOMERS_STATUS, 'page=' . $_GET['page'] . '&cID=' . $cInfo->customers_status_id  .'&action=save', 'post', 'enctype="multipart/form-data"'));
      $contents[] = array('text' => TEXT_INFO_EDIT_INTRO);
      $customers_status_inputs_string = '';
      $languages = xtc_get_languages();
      for ($i=0; $i<sizeof($languages); $i++) {
        $customers_status_inputs_string .= '<br>' . xtc_image(xarTplGetImage(DIR_WS_CATALOG.'lang/'.$languages[$i]['directory'].'/admin/images/' . $languages[$i]['image']), $languages[$i]['name']) . '&#160;' . xtc_draw_input_field('customers_status_name[' . $languages[$i]['id'] . ']', xtc_get_customers_status_name($cInfo->customers_status_id, $languages[$i]['id']));
      }

      $contents[] = array('text' => '<br>' . TEXT_INFO_CUSTOMERS_STATUS_NAME . $customers_status_inputs_string);
      $contents[] = array('text' => '<br>' . xtc_image(xarTplGetImage(DIR_WS_ICONS . $cInfo->customers_status_image), $cInfo->customers_status_name) . '<br>' . 'icons/<br><b>' . $cInfo->customers_status_image . '</b>');
      $contents[] = array('text' => '<br>' . TEXT_INFO_CUSTOMERS_STATUS_IMAGE . '<br>' . xtc_draw_file_field('customers_status_image', $cInfo->customers_status_image));
      $contents[] = array('text' => '<br>' . TEXT_INFO_CUSTOMERS_STATUS_PUBLIC_INTRO . '<br>' . ENTRY_CUSTOMERS_STATUS_PUBLIC . ' ' . commerce_userapi_draw_pull_down_menu('customers_status_public', $customers_status_public_array, $cInfo->customers_status_public ));
      $contents[] = array('text' => '<br>' . TEXT_INFO_CUSTOMERS_STATUS_SHOW_PRICE_INTRO     . '<br>' . ENTRY_CUSTOMERS_STATUS_SHOW_PRICE . ' ' . commerce_userapi_draw_pull_down_menu('customers_status_show_price', $customers_status_show_price_array, $cInfo->customers_status_show_price ));
      $contents[] = array('text' => '<br>' . TEXT_INFO_CUSTOMERS_STATUS_SHOW_PRICE_TAX_INTRO . '<br>' . ENTRY_CUSTOMERS_STATUS_SHOW_PRICE_TAX . ' ' . commerce_userapi_draw_pull_down_menu('customers_status_show_price_tax', $customers_status_show_price_tax_array, $cInfo->customers_status_show_price_tax ));
      $contents[] = array('text' => '<br>' . TEXT_INFO_CUSTOMERS_STATUS_ADD_TAX_INTRO . '<br>' . ENTRY_CUSTOMERS_STATUS_ADD_TAX . ' ' . commerce_userapi_draw_pull_down_menu('customers_status_add_tax_ot', $customers_status_add_tax_ot_array, $cInfo->customers_status_add_tax_ot));
      $contents[] = array('text' => '<br>' . TEXT_INFO_CUSTOMERS_STATUS_DISCOUNT_PRICE_INTRO . '<br>' . TEXT_INFO_CUSTOMERS_STATUS_DISCOUNT_PRICE . ' ' . xtc_draw_input_field('customers_status_discount', $cInfo->customers_status_discount));
      $contents[] = array('text' => '<br>' . TEXT_INFO_CUSTOMERS_STATUS_DISCOUNT_ATTRIBUTES_INTRO . '<br>' . ENTRY_CUSTOMERS_STATUS_DISCOUNT_ATTRIBUTES . ' ' . commerce_userapi_draw_pull_down_menu('customers_status_discount_attributes', $customers_status_discount_attributes_array, $cInfo->customers_status_discount_attributes ));
      $contents[] = array('text' => '<br>' . TEXT_INFO_CUSTOMERS_STATUS_DISCOUNT_OT_XMEMBER_INTRO . '<br> ' . ENTRY_OT_XMEMBER . ' ' . commerce_userapi_draw_pull_down_menu('customers_status_ot_discount_flag', $customers_status_ot_discount_flag_array, $cInfo->customers_status_ot_discount_flag). '<br>' . TEXT_INFO_CUSTOMERS_STATUS_DISCOUNT_PRICE . ' ' . xtc_draw_input_field('customers_status_ot_discount', $cInfo->customers_status_ot_discount));
      $contents[] = array('text' => '<br>' . TEXT_INFO_CUSTOMERS_STATUS_GRADUATED_PRICES_INTRO . '<br>' . ENTRY_GRADUATED_PRICES . ' ' . commerce_userapi_draw_pull_down_menu('customers_status_graduated_prices', $customers_status_graduated_prices_array, $cInfo->customers_status_graduated_prices));
      $contents[] = array('text' => '<br>' . TEXT_INFO_CUSTOMERS_STATUS_PAYMENT_UNALLOWED_INTRO . '<br>' . ENTRY_CUSTOMERS_STATUS_PAYMENT_UNALLOWED . ' ' . xtc_draw_input_field('customers_status_payment_unallowed', $cInfo->customers_status_payment_unallowed ));
      $contents[] = array('text' => '<br>' . TEXT_INFO_CUSTOMERS_STATUS_SHIPPING_UNALLOWED_INTRO . '<br>' . ENTRY_CUSTOMERS_STATUS_SHIPPING_UNALLOWED . ' ' . xtc_draw_input_field('customers_status_shipping_unallowed', $cInfo->customers_status_shipping_unallowed ));
      if (DEFAULT_CUSTOMERS_STATUS_ID != $cInfo->customers_status_id) $contents[] = array('text' => '<br>' . xtc_draw_checkbox_field('default') . ' ' . TEXT_SET_DEFAULT);
      $contents[] = array('align' => 'center', 'text' => '<br>' . <input type="image" src="#xarTplGetImage('buttons/' . xarSessionGetVar('language') . '/'.'button_update.gif')#" border="0" alt=IMAGE_UPDATE> . ' <a href="' . xarModURL('commerce','admin',(FILENAME_CUSTOMERS_STATUS, 'page=' . $_GET['page'] . '&cID=' . $cInfo->customers_status_id) . '">' . xarModAPIFunc('commerce','user','image',array('src' => xarTplGetImage('buttons/' . xarSessionGetVar('language') . '/'.'button_cancel.gif'),'alt' => IMAGE_CANCEL); . '</a>');
      break;

    case 'delete':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_CUSTOMERS_STATUS . '</b>');

      $contents = array('form' => xtc_draw_form('status', FILENAME_CUSTOMERS_STATUS, 'page=' . $_GET['page'] . '&cID=' . $cInfo->customers_status_id  . '&action=deleteconfirm'));
      $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
      $contents[] = array('text' => '<br><b>' . $cInfo->customers_status_name . '</b>');

      if ($remove_status) $contents[] = array('align' => 'center', 'text' => '<br>' . <input type="image" src="#xarTplGetImage('buttons/' . xarSessionGetVar('language') . '/'.'button_delete.gif')#" border="0" alt=IMAGE_DELETE> . ' <a href="' . xarModURL('commerce','admin',(FILENAME_CUSTOMERS_STATUS, 'page=' . $_GET['page'] . '&cID=' . $cInfo->customers_status_id) . '">' . xarModAPIFunc('commerce','user','image',array('src' => xarTplGetImage('buttons/' . xarSessionGetVar('language') . '/'.'button_cancel.gif'),'alt' => IMAGE_CANCEL); . '</a>');
      break;

    default:
      if (is_object($cInfo)) {
        $heading[] = array('text' => '<b>' . $cInfo->customers_status_name . '</b>');

        $contents[] = array('align' => 'center', 'text' => '<a href="' . xarModURL('commerce','admin',(FILENAME_CUSTOMERS_STATUS, 'page=' . $_GET['page'] . '&cID=' . $cInfo->customers_status_id . '&action=edit') . '">' . xarModAPIFunc('commerce','user','image',array('src' => xarTplGetImage('buttons/' . xarSessionGetVar('language') . '/'.'button_edit.gif'),'alt' => IMAGE_EDIT); . '</a> <a href="' . xarModURL('commerce','admin',(FILENAME_CUSTOMERS_STATUS, 'page=' . $_GET['page'] . '&cID=' . $cInfo->customers_status_id . '&action=delete') . '">' . xarModAPIFunc('commerce','user','image',array('src' => xarTplGetImage('buttons/' . xarSessionGetVar('language') . '/'.'button_delete.gif'),'alt' => IMAGE_DELETE); . '</a>');
        $customers_status_inputs_string = '';
        $languages = xtc_get_languages();
        for ($i=0; $i<sizeof($languages); $i++) {
          $customers_status_inputs_string .= '<br>' . xtc_image(xarTplGetImage(DIR_WS_CATALOG.'lang/'. $languages[$i]['directory'] . '/admin/images/' . $languages[$i]['image']), $languages[$i]['name']) . '&#160;' . xtc_get_customers_status_name($cInfo->customers_status_id, $languages[$i]['id']);
        }
        $contents[] = array('text' => $customers_status_inputs_string);
        $contents[] = array('text' => '<br>' . TEXT_INFO_CUSTOMERS_STATUS_DISCOUNT_PRICE_INTRO . '<br>' . TEXT_INFO_CUSTOMERS_STATUS_DISCOUNT_PRICE . ' ' . $cInfo->customers_status_discount . '%');
        $contents[] = array('text' => '<br>' . TEXT_INFO_CUSTOMERS_STATUS_DISCOUNT_OT_XMEMBER_INTRO . '<br>' . ENTRY_OT_XMEMBER . ' ' . $customers_status_ot_discount_flag_array[$cInfo->customers_status_ot_discount_flag]['text'] . ' (' . $cInfo->customers_status_ot_discount_flag . ')' . ' - ' . $cInfo->customers_status_ot_discount . '%');
        $contents[] = array('text' => '<br>' . TEXT_INFO_CUSTOMERS_STATUS_GRADUATED_PRICES_INTRO . '<br>' . ENTRY_GRADUATED_PRICES . ' ' . $customers_status_graduated_prices_array[$cInfo->customers_status_graduated_prices]['text'] . ' (' . $cInfo->customers_status_graduated_prices . ')' );
        $contents[] = array('text' => '<br>' . TEXT_INFO_CUSTOMERS_STATUS_DISCOUNT_ATTRIBUTES_INTRO . '<br>' . ENTRY_CUSTOMERS_STATUS_DISCOUNT_ATTRIBUTES . ' ' . $customers_status_discount_attributes_array[$cInfo->customers_status_discount_attributes]['text'] . ' (' . $cInfo->customers_status_discount_attributes . ')' );
        $contents[] = array('text' => '<br>' . TEXT_INFO_CUSTOMERS_STATUS_PAYMENT_UNALLOWED_INTRO . '<br>' . ENTRY_CUSTOMERS_STATUS_PAYMENT_UNALLOWED . ':<b> ' . $cInfo->customers_status_payment_unallowed.'</b>');
        $contents[] = array('text' => '<br>' . TEXT_INFO_CUSTOMERS_STATUS_SHIPPING_UNALLOWED_INTRO . '<br>' . ENTRY_CUSTOMERS_STATUS_SHIPPING_UNALLOWED . ':<b> ' . $cInfo->customers_status_shipping_unallowed.'</b>');
      }
      break;
  }

  if ( (xarModAPIFunc('commerce','user','not_null',array('arg' => $heading))) && (xarModAPIFunc('commerce','user','not_null',array('arg' => $contents))) ) {
    echo '<td width="25%" valign="top">' . "\n";

    $box = new box;
    echo $box->infoBox($heading, $contents);

    echo '</td>' . "\n";
  }
}
?>