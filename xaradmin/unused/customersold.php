<?php
// ----------------------------------------------------------------------
// Copyright (C) 2004: Marc Lutolf (marcinmilan@xaraya.com)
// Purpose of file:  Configuration functions for commerce
// ----------------------------------------------------------------------
//  based on:
//  (c) 2003 XT-Commerce
//   Third Party contribution:
//   Customers Status v3.x  (c) 2002-2003 Copyright Elari elari@free.fr | www.unlockgsm.com/dload-osc/ | CVS : http://cvs.sourceforge.net/cgi-bin/viewcvs.cgi/elari/?sortby=date#dirlist
//  (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
//  (c) 2002-2003 osCommerce (oscommerce.sql,v 1.83); www.oscommerce.com
//  (c) 2003  nextcommerce (nextcommerce.sql,v 1.76 2003/08/25); www.nextcommerce.org
// ----------------------------------------------------------------------

function commerce_admin_customers()
{

   Released under the GNU General Public License
   --------------------------------------------------------------*/




  $customers_statuses_array = xtc_get_customers_statuses();

  if ($_GET['special'] == 'remove_memo') {
    $mID = xtc_db_prepare_input($_GET['mID']);
    new xenQuery("DELETE FROM " . TABLE_CUSTOMERS_MEMO . " WHERE memo_id = '". $mID . "'");
    xarRedirectResponse(xarModURL('commerce','admin',(FILENAME_CUSTOMERS, 'cID=' . $_GET['cID'] . '&action=edit'));
  }

  if ($_GET['action'] == 'edit' || $_GET['action'] == 'update') {
    if ($_GET['cID'] == 1 && $_SESSION['customer_id'] == 1)  {
    } else {
      if ($_GET['cID'] != 1)  {
      } else {
        xarRedirectResponse(xarModURL('commerce','admin',(FILENAME_CUSTOMERS, ''));
      }
    }
  }

  if ($_GET['action']) {
    switch ($_GET['action']) {
      case 'statusconfirm':
        $customers_id = xtc_db_prepare_input($_GET['cID']);
        $customer_updated = false;
        $check_status_query = new xenQuery("select customers_firstname, customers_lastname, customers_email_address , customers_status, member_flag from " . TABLE_CUSTOMERS . " where customers_id = '" . xtc_db_input($_GET['cID']) . "'");
      $q = new xenQuery();
      if(!$q->run()) return;
        $check_status = $q->output();
        if ($check_status['customers_status'] != $status) {
          new xenQuery("update " . TABLE_CUSTOMERS . " set customers_status = '" . xtc_db_input($_POST['status']) . "' where customers_id = '" . xtc_db_input($_GET['cID']) . "'");

    // create insert for admin access table if customers status is set to 0
    if ($_POST['status']==0) {
        new xenQuery("INSERT into ".TABLE_ADMIN_ACCESS." (customers_id,start) VALUES ('".xtc_db_input($_GET['cID'])."','1')");
    } else {
    new xenQuery("DELETE FROM ".TABLE_ADMIN_ACCESS." WHERE customers_id = '".xtc_db_input($_GET['cID'])."'");

    }
    //Temporarily set due to above commented lines
          $customer_notified = '0';
          new xenQuery("insert into " . TABLE_CUSTOMERS_STATUS_HISTORY . " (customers_id, new_value, old_value, date_added, customer_notified) values ('" . xtc_db_input($_GET['cID']) . "', '" . xtc_db_input($_POST['status']) . "', '" . $check_status['customers_status'] . "', now(), '" . $customer_notified . "')");
          $customer_updated = true;
        }
        break;

      case 'update':
        $customers_id = xtc_db_prepare_input($_GET['cID']);
        $customers_firstname = xtc_db_prepare_input($_POST['customers_firstname']);
        $customers_lastname = xtc_db_prepare_input($_POST['customers_lastname']);
        $customers_email_address = xtc_db_prepare_input($_POST['customers_email_address']);
        $customers_telephone = xtc_db_prepare_input($_POST['customers_telephone']);
        $customers_fax = xtc_db_prepare_input($_POST['customers_fax']);
        $customers_newsletter = xtc_db_prepare_input($_POST['customers_newsletter']);

        $customers_gender = xtc_db_prepare_input($_POST['customers_gender']);
        $customers_dob = xtc_db_prepare_input($_POST['customers_dob']);

        $default_address_id = xtc_db_prepare_input($_POST['default_address_id']);
        $entry_street_address = xtc_db_prepare_input($_POST['entry_street_address']);
        $entry_suburb = xtc_db_prepare_input($_POST['entry_suburb']);
        $entry_postcode = xtc_db_prepare_input($_POST['entry_postcode']);
        $entry_city = xtc_db_prepare_input($_POST['entry_city']);
        $entry_country_id = xtc_db_prepare_input($_POST['entry_country_id']);

        $entry_company = xtc_db_prepare_input($_POST['entry_company']);
        $entry_state = xtc_db_prepare_input($_POST['entry_state']);
        $entry_zone_id = xtc_db_prepare_input($_POST['entry_zone_id']);

        $memo_title = xtc_db_prepare_input($_POST['memo_title']);
        $memo_text = xtc_db_prepare_input($_POST['memo_text']);

        if ($memo_text != '' && $memo_title != '' ) {
            $q->addfield('customers_id',$_GET['cID']);
            $q->addfield('memo_date',date("Y-m-d"));
            $q->addfield('memo_title',$memo_title);
            $q->addfield('memo_text',$memo_text);
            $q->addfield('poster_id',$_SESSION['customer_id']);
          xtc_db_perform(TABLE_CUSTOMERS_MEMO, $sql_data_array);
        }
        $error = false; // reset error flag

        if (strlen($customers_firstname) < ENTRY_FIRST_NAME_MIN_LENGTH) {
          $error = true;
          $entry_firstname_error = true;
        } else {
          $entry_firstname_error = false;
        }

        if (strlen($customers_lastname) < ENTRY_LAST_NAME_MIN_LENGTH) {
          $error = true;
          $entry_lastname_error = true;
        } else {
          $entry_lastname_error = false;
        }

        if (ACCOUNT_DOB == 'true') {
          if (checkdate(substr(xtc_date_raw($customers_dob), 4, 2), substr(xtc_date_raw($customers_dob), 6, 2), substr(xtc_date_raw($customers_dob), 0, 4))) {
            $entry_date_of_birth_error = false;
          } else {
            $error = true;
            $entry_date_of_birth_error = true;
          }
        }

        if (strlen($customers_email_address) < ENTRY_EMAIL_ADDRESS_MIN_LENGTH) {
          $error = true;
          $entry_email_address_error = true;
        } else {
          $entry_email_address_error = false;
        }

        if (!xarModAPIFunc('commerce','user','validate_email',array('email' => $customers_email_address))) {
          $error = true;
          $entry_email_address_check_error = true;
        } else {
          $entry_email_address_check_error = false;
        }

        if (strlen($entry_street_address) < ENTRY_STREET_ADDRESS_MIN_LENGTH) {
          $error = true;
          $entry_street_address_error = true;
        } else {
          $entry_street_address_error = false;
        }

        if (strlen($entry_postcode) < ENTRY_POSTCODE_MIN_LENGTH) {
          $error = true;
          $entry_post_code_error = true;
        } else {
          $entry_post_code_error = false;
        }

        if (strlen($entry_city) < ENTRY_CITY_MIN_LENGTH) {
          $error = true;
          $entry_city_error = true;
        } else {
          $entry_city_error = false;
        }

        if ($entry_country_id == false) {
          $error = true;
          $entry_country_error = true;
        } else {
          $entry_country_error = false;
        }

        if (ACCOUNT_STATE == 'true') {
          if ($entry_country_error == true) {
            $entry_state_error = true;
          } else {
            $zone_id = 0;
            $entry_state_error = false;
            $check_query = new xenQuery("select count(*) as total from " . TABLE_ZONES . " where zone_country_id = '" . xtc_db_input($entry_country_id) . "'");
      $q = new xenQuery();
      if(!$q->run()) return;
            $check_value = $q->output();
            $entry_state_has_zones = ($check_value['total'] > 0);
            if ($entry_state_has_zones == true) {
              $zone_query = new xenQuery("select zone_id from " . TABLE_ZONES . " where zone_country_id = '" . xtc_db_input($entry_country_id) . "' and zone_name = '" . xtc_db_input($entry_state) . "'");
              if ($zone_query->getrows() == 1) {
      $q = new xenQuery();
      if(!$q->run()) return;
                $zone_values = $q->output();
                $entry_zone_id = $zone_values['zone_id'];
              } else {
                $zone_query = new xenQuery("select zone_id from " . TABLE_ZONES . " where zone_country_id = '" . xtc_db_input($entry_country) . "' and zone_code = '" . xtc_db_input($entry_state) . "'");
                if ($zone_query->getrows() == 1) {
      $q = new xenQuery();
      if(!$q->run()) return;
                  $zone_values = $q->output();
                  $zone_id = $zone_values['zone_id'];
                } else {
                  $error = true;
                  $entry_state_error = true;
                }
              }
            } else {
              if ($entry_state == false) {
                $error = true;
                $entry_state_error = true;
              }
            }
          }
        }

        if (strlen($customers_telephone) < ENTRY_TELEPHONE_MIN_LENGTH) {
          $error = true;
          $entry_telephone_error = true;
        } else {
          $entry_telephone_error = false;
        }

        $check_email = new xenQuery("select customers_email_address from " . TABLE_CUSTOMERS . " where customers_email_address = '" . xtc_db_input($customers_email_address) . "' and customers_id <> '" . xtc_db_input($customers_id) . "'");
        if ($check_email->getrows()) {
          $error = true;
          $entry_email_address_exists = true;
        } else {
          $entry_email_address_exists = false;
        }

        if ($error == false) {
            $q->addfield('customers_firstname',$customers_firstname);
            $q->addfield('customers_lastname',$customers_lastname);
            $q->addfield('customers_email_address',$customers_email_address);
            $q->addfield('customers_telephone',$customers_telephone);
            $q->addfield('customers_fax',$customers_fax);
            $q->addfield('customers_newsletter',$customers_newsletter);

          if (ACCOUNT_GENDER == 'true') $q->addfield('customers_gender',$customers_gender);
          if (ACCOUNT_DOB == 'true') $q->addfield('customers_dob',xtc_date_raw($customers_dob));

          xtc_db_perform(TABLE_CUSTOMERS, $sql_data_array, 'update', "customers_id = '" . xtc_db_input($customers_id) . "'");

          new xenQuery("update " . TABLE_CUSTOMERS_INFO . " set customers_info_date_account_last_modified = now() where customers_info_id = '" . xtc_db_input($customers_id) . "'");

          if ($entry_zone_id > 0) $entry_state = '';

            $q->addfield('entry_firstname',$customers_firstname);
            $q->addfield('entry_lastname',$customers_lastname);
            $q->addfield('entry_street_address',$entry_street_address);
            $q->addfield('entry_postcode',$entry_postcode);
            $q->addfield('entry_city',$entry_city);
            $q->addfield('entry_country_id',$entry_country_id);

          if (ACCOUNT_COMPANY == 'true') $q->addfield('entry_company',$entry_company);
          if (ACCOUNT_SUBURB == 'true') $q->addfield('entry_suburb',$entry_suburb);

          if (ACCOUNT_STATE == 'true') {
            if ($entry_zone_id > 0) {
              $q->addfield('entry_zone_id',$entry_zone_id);
              $q->addfield('entry_state','');
            } else {
              $q->addfield('entry_zone_id','0');
              $q->addfield('entry_state',$entry_state);
            }
          }

          xtc_db_perform(TABLE_ADDRESS_BOOK, $sql_data_array, 'update', "customers_id = '" . xtc_db_input($customers_id) . "' and address_book_id = '" . xtc_db_input($default_address_id) . "'");
          xarRedirectResponse(xarModURL('commerce','admin',(FILENAME_CUSTOMERS, xtc_get_all_get_params(array('cID', 'action')) . 'cID=' . $customers_id));
        } elseif ($error == true) {
          $cInfo = new objectInfo($_POST);
          $processed = true;
        }

        break;
      case 'deleteconfirm':
        $customers_id = xtc_db_prepare_input($_GET['cID']);

        if ($_POST['delete_reviews'] == 'on') {
          $reviews_query = new xenQuery("select reviews_id from " . TABLE_REVIEWS . " where customers_id = '" . xtc_db_input($customers_id) . "'");
      $q = new xenQuery();
      if(!$q->run()) return;
          while ($reviews = $q->output()) {
            new xenQuery("delete from " . TABLE_REVIEWS_DESCRIPTION . " where reviews_id = '" . $reviews['reviews_id'] . "'");
          }
          new xenQuery("delete from " . TABLE_REVIEWS . " where customers_id = '" . xtc_db_input($customers_id) . "'");
        } else {
          new xenQuery("update " . TABLE_REVIEWS . " set customers_id = null where customers_id = '" . xtc_db_input($customers_id) . "'");
        }

        new xenQuery("delete from " . TABLE_ADDRESS_BOOK . " where customers_id = '" . xtc_db_input($customers_id) . "'");
        new xenQuery("delete from " . TABLE_CUSTOMERS . " where customers_id = '" . xtc_db_input($customers_id) . "'");
        new xenQuery("delete from " . TABLE_CUSTOMERS_INFO . " where customers_info_id = '" . xtc_db_input($customers_id) . "'");
        new xenQuery("delete from " . TABLE_CUSTOMERS_BASKET . " where customers_id = '" . xtc_db_input($customers_id) . "'");
        new xenQuery("delete from " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . " where customers_id = '" . xtc_db_input($customers_id) . "'");
        new xenQuery("delete from " . TABLE_WHOS_ONLINE . " where customer_id = '" . xtc_db_input($customers_id) . "'");
        new xenQuery("delete from " . TABLE_CUSTOMERS_STATUS_HISTORY . " where customers_id = '" . xtc_db_input($customers_id) . "'");
        new xenQuery("delete from " . TABLE_CUSTOMERS_IP . " where customers_id = '" . xtc_db_input($customers_id) . "'");

        xarRedirectResponse(xarModURL('commerce','admin',(FILENAME_CUSTOMERS, xtc_get_all_get_params(array('cID', 'action'))));
        break;

      default:
        $customers_query = new xenQuery("select c.customers_id, c.customers_gender, c.customers_firstname, c.customers_lastname, c.customers_dob, c.customers_email_address, a.entry_company, a.entry_street_address, a.entry_suburb, a.entry_postcode, a.entry_city, a.entry_state, a.entry_zone_id, a.entry_country_id, c.customers_telephone, c.customers_fax, c.customers_newsletter, c.customers_default_address_id from " . TABLE_CUSTOMERS . " c left join " . TABLE_ADDRESS_BOOK . " a on c.customers_default_address_id = a.address_book_id where a.customers_id = c.customers_id and c.customers_id = '" . $_GET['cID'] . "'");
      $q = new xenQuery();
      if(!$q->run()) return;
        $customers = $q->output();
        $cInfo = new objectInfo($customers);
    }
  }

  if (customers_firstname == "" || customers_firstname.length < <?php echo ENTRY_FIRST_NAME_MIN_LENGTH; ?>) {
    error_message = error_message + "<?php echo JS_FIRST_NAME; ?>";
    error = 1;
  }

  if (customers_lastname == "" || customers_lastname.length < <?php echo ENTRY_LAST_NAME_MIN_LENGTH; ?>) {
    error_message = error_message + "<?php echo JS_LAST_NAME; ?>";
    error = 1;
  }

<?php if (ACCOUNT_DOB == 'true') { ?>
  if (customers_dob == "" || customers_dob.length < <?php echo ENTRY_DOB_MIN_LENGTH; ?>) {
    error_message = error_message + "<?php echo JS_DOB; ?>";
    error = 1;
  }
<?php } ?>

  if (customers_email_address == "" || customers_email_address.length < <?php echo ENTRY_EMAIL_ADDRESS_MIN_LENGTH; ?>) {
    error_message = error_message + "<?php echo JS_EMAIL_ADDRESS; ?>";
    error = 1;
  }

  if (entry_street_address == "" || entry_street_address.length < <?php echo ENTRY_STREET_ADDRESS_MIN_LENGTH; ?>) {
    error_message = error_message + "<?php echo JS_ADDRESS; ?>";
    error = 1;
  }

  if (entry_postcode == "" || entry_postcode.length < <?php echo ENTRY_POSTCODE_MIN_LENGTH; ?>) {
    error_message = error_message + "<?php echo JS_POST_CODE; ?>";
    error = 1;
  }

  if (entry_city == "" || entry_city.length < <?php echo ENTRY_CITY_MIN_LENGTH; ?>) {
    error_message = error_message + "<?php echo JS_CITY; ?>";
    error = 1;
  }

<?php
  if (ACCOUNT_STATE == 'true') {
?>
  if (document.customers.elements['entry_state'].type != "hidden") {
    if (document.customers.entry_state.value == '' || document.customers.entry_state.value.length < <?php echo ENTRY_STATE_MIN_LENGTH; ?> ) {
       error_message = error_message + "<?php echo JS_STATE; ?>";
       error = 1;
    }
  }
<?php
  }
?>

  if (document.customers.elements['entry_country_id'].type != "hidden") {
    if (document.customers.entry_country_id.value == 0) {
      error_message = error_message + "<?php echo JS_COUNTRY; ?>";
      error = 1;
    }
  }

  if (customers_telephone == "" || customers_telephone.length < <?php echo ENTRY_TELEPHONE_MIN_LENGTH; ?>) {
    error_message = error_message + "<?php echo JS_TELEPHONE; ?>";
    error = 1;
  }

  if (error == 1) {
    alert(error_message);
    return false;
  } else {
    return true;
  }
}
//--></script>
<?php
  }
?>
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF" onload="SetFocus();">
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
    <td class="columnLeft2" width="<?php echo BOX_WIDTH; ?>" valign="top"><table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="1" cellpadding="1" class="columnLeft">
<!-- left_navigation //-->
<?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
<!-- left_navigation_eof //-->
    </table></td>
<!-- body_text //-->
    <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
<?php
  if ($_GET['action'] == 'edit' || $_GET['action'] == 'update') {
    $customers_query = new xenQuery("select c.customers_gender,c.customers_status, c.member_flag, c.customers_firstname, c.customers_lastname, c.customers_dob, c.customers_email_address, a.entry_company, a.entry_street_address, a.entry_suburb, a.entry_postcode, a.entry_city, a.entry_state, a.entry_zone_id, a.entry_country_id, c.customers_telephone, c.customers_fax, c.customers_newsletter, c.customers_default_address_id from " . TABLE_CUSTOMERS . " c left join " . TABLE_ADDRESS_BOOK . " a on c.customers_default_address_id = a.address_book_id where a.customers_id = c.customers_id and c.customers_id = '" . $_GET['cID'] . "'");

      $q = new xenQuery();
      if(!$q->run()) return;
    $customers = $q->output();
    $cInfo = new objectInfo($customers);
    $newsletter_array = array(array('id' => '1', 'text' => ENTRY_NEWSLETTER_YES), array('id' => '0', 'text' => ENTRY_NEWSLETTER_NO));
?>
      <tr>
        <td>
        <table border="0" width="100%" cellspacing="0" cellpadding="0">
  <tr>
    <td width="80" rowspan="2"><?php echo xtc_image(xarTplGetImage(DIR_WS_ICONS.'heading_customers.gif'); ?></td>
    <td class="pageHeading"><?php echo $cInfo->customers_lastname.' '.$cInfo->customers_firstname; ?></td>
  </tr>
  <tr>
    <td class="main" valign="top">XT Customers</td>
  </tr>
</table>
        <table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="middle" class="pageHeading"><?php if ($customers_statuses_array[$customers['customers_status']]['csa_image'] != '') { echo xtc_image(xarTplGetImage(DIR_WS_ICONS . $customers_statuses_array[$customers['customers_status']]['csa_image']), ''); } ?></td>
            <td class="main"></td>
            <td class="pageHeading" align="right"><?php echo xtc_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
          </tr>
          <tr>
            <td colspan="3" class="main"><?php echo HEADING_TITLE_STATUS  .': ' . $customers_statuses_array[$customers['customers_status']]['text'] ; ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo xtc_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr><?php echo xtc_draw_form('customers', FILENAME_CUSTOMERS, xtc_get_all_get_params(array('action')) . 'action=update', 'post', 'onSubmit="return check_form();"') .
  <input type="hidden" name="default_address_id" value="#$cInfo->customers_default_address_id#">
        <td class="formAreaTitle"><?php echo CATEGORY_PERSONAL; ?></td>
      </tr>
      <tr>
        <td class="formArea"><table border="0" cellspacing="2" cellpadding="2">
<?php
    if (ACCOUNT_GENDER == 'true') {
?>
          <tr>
            <td class="main"><?php echo ENTRY_GENDER; ?></td>
            <td class="main"><?php
      if ($error == true) {
        if ($entry_gender_error == true) {
          echo xtc_draw_radio_field('customers_gender', 'm', false, $cInfo->customers_gender) . '&#160;&#160;' . MALE . '&#160;&#160;' . xtc_draw_radio_field('customers_gender', 'f', false, $cInfo->customers_gender) . '&#160;&#160;' . FEMALE . '&#160;' . ENTRY_GENDER_ERROR;
        } else {
          echo ($cInfo->customers_gender == 'm') ? MALE : FEMALE;
          echo xtc_draw_hidden_field('customers_gender');
        }
      } else {
        echo xtc_draw_radio_field('customers_gender', 'm', false, $cInfo->customers_gender) . '&#160;&#160;' . MALE . '&#160;&#160;' . xtc_draw_radio_field('customers_gender', 'f', false, $cInfo->customers_gender) . '&#160;&#160;' . FEMALE;
      }
?></td>
          </tr>
<?php

    }
?>
          <tr>
            <td class="main"><?php echo ENTRY_FIRST_NAME; ?></td>
            <td class="main"><?php
    if ($entry_firstname_error == true) {
      echo xtc_draw_input_field('customers_firstname',$cInfo->customers_firstname, 'maxlength="32"') . '&#160;' . ENTRY_FIRST_NAME_ERROR;
    } else {
       echo xtc_draw_input_field('customers_firstname', $cInfo->customers_firstname, 'maxlength="32"', true);
    }
?></td>
          </tr>
          <tr>
            <td class="main"><?php echo ENTRY_LAST_NAME; ?></td>
            <td class="main"><?php
    if ($error == true) {
      if ($entry_lastname_error == true) {
        echo xtc_draw_input_field('customers_lastname', $cInfo->customers_lastname, 'maxlength="32"') . '&#160;' . ENTRY_LAST_NAME_ERROR;
      } else {
        echo $cInfo->customers_lastname .
        xtc_draw_hidden_field('customers_lastname');
      }
    } else {
      echo xtc_draw_input_field('customers_lastname', $cInfo->customers_lastname, 'maxlength="32"', true);
    }
?></td>
          </tr>
<?php
    if (ACCOUNT_DOB == 'true') {
?>
          <tr>
            <td class="main"><?php echo ENTRY_DATE_OF_BIRTH; ?></td>
            <td class="main"><?php
      if ($error == true) {
        if ($entry_date_of_birth_error == true) {
          echo xtc_draw_input_field('customers_dob', xarModAPIFunc('commerce','user','date_short',array('raw_date' =>$cInfo->customers_dob)), 'maxlength="10"') . '&#160;' . ENTRY_DATE_OF_BIRTH_ERROR;
        } else {
          echo $cInfo->customers_dob . xtc_draw_hidden_field('customers_dob');
        }
      } else {
        echo xtc_draw_input_field('customers_dob', xarModAPIFunc('commerce','user','date_short',array('raw_date' =>$cInfo->customers_dob)), 'maxlength="10"', true);
      }
?></td>
          </tr>
<?php
    }
?>
          <tr>
            <td class="main"><?php echo ENTRY_EMAIL_ADDRESS; ?></td>
            <td class="main"><?php
    if ($error == true) {
      if ($entry_email_address_error == true) {
        echo xtc_draw_input_field('customers_email_address', $cInfo->customers_email_address, 'maxlength="96"') . '&#160;' . ENTRY_EMAIL_ADDRESS_ERROR;
      } elseif ($entry_email_address_check_error == true) {
        echo xtc_draw_input_field('customers_email_address', $cInfo->customers_email_address, 'maxlength="96"') . '&#160;' . ENTRY_EMAIL_ADDRESS_CHECK_ERROR;
      } elseif ($entry_email_address_exists == true) {
        echo xtc_draw_input_field('customers_email_address', $cInfo->customers_email_address, 'maxlength="96"') . '&#160;' . ENTRY_EMAIL_ADDRESS_ERROR_EXISTS;
      } else {
        echo $customers_email_address . xtc_draw_hidden_field('customers_email_address');
      }
    } else {
      echo xtc_draw_input_field('customers_email_address', $cInfo->customers_email_address, 'maxlength="96"', true);
    }
?></td>
          </tr>
        </table></td>
      </tr>
<?php
    if (ACCOUNT_COMPANY == 'true') {
?>
      <tr>
        <td><?php echo xtc_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td class="formAreaTitle"><?php echo CATEGORY_COMPANY; ?></td>
      </tr>
      <tr>
        <td class="formArea"><table border="0" cellspacing="2" cellpadding="2">
          <tr>
            <td class="main"><?php echo ENTRY_COMPANY; ?></td>
            <td class="main"><?php
      if ($error == true) {
        if ($entry_company_error == true) {
          echo xtc_draw_input_field('entry_company', $cInfo->entry_company, 'maxlength="32"') . '&#160;' . ENTRY_COMPANY_ERROR;
        } else {
          echo $cInfo->entry_company . xtc_draw_hidden_field('entry_company');
        }
      } else {
        echo xtc_draw_input_field('entry_company', $cInfo->entry_company, 'maxlength="32"');
      }
?></td>
          </tr>
        </table></td>
      </tr>
<?php
    }
?>
      <tr>
        <td><?php echo xtc_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td class="formAreaTitle"><?php echo CATEGORY_ADDRESS; ?></td>
      </tr>
      <tr>
        <td class="formArea"><table border="0" cellspacing="2" cellpadding="2">
          <tr>
            <td class="main"><?php echo ENTRY_STREET_ADDRESS; ?></td>
            <td class="main"><?php
    if ($error == true) {
      if ($entry_street_address_error == true) {
        echo xtc_draw_input_field('entry_street_address', $cInfo->entry_street_address, 'maxlength="64"') . '&#160;' . ENTRY_STREET_ADDRESS_ERROR;
      } else {
        echo $cInfo->entry_street_address . xtc_draw_hidden_field('entry_street_address');
      }
    } else {
      echo xtc_draw_input_field('entry_street_address', $cInfo->entry_street_address, 'maxlength="64"', true);
    }
?></td>
          </tr>
<?php
    if (ACCOUNT_SUBURB == 'true') {
?>
          <tr>
            <td class="main"><?php echo ENTRY_SUBURB; ?></td>
            <td class="main"><?php
      if ($error == true) {
        if ($entry_suburb_error == true) {
          echo xtc_draw_input_field('suburb', $cInfo->entry_suburb, 'maxlength="32"') . '&#160;' . ENTRY_SUBURB_ERROR;
        } else {
          echo $cInfo->entry_suburb . xtc_draw_hidden_field('entry_suburb');
        }
      } else {
        echo xtc_draw_input_field('entry_suburb', $cInfo->entry_suburb, 'maxlength="32"');
      }
?></td>
          </tr>
<?php
    }
?>
          <tr>
            <td class="main"><?php echo ENTRY_POST_CODE; ?></td>
            <td class="main"><?php
    if ($error == true) {
      if ($entry_post_code_error == true) {
        echo xtc_draw_input_field('entry_postcode', $cInfo->entry_postcode, 'maxlength="8"') . '&#160;' . ENTRY_POST_CODE_ERROR;
      } else {
        echo $cInfo->entry_postcode . xtc_draw_hidden_field('entry_postcode');
      }
    } else {
      echo xtc_draw_input_field('entry_postcode', $cInfo->entry_postcode, 'maxlength="8"', true);
    }
?></td>
          </tr>
          <tr>
            <td class="main"><?php echo ENTRY_CITY; ?></td>
            <td class="main"><?php
    if ($error == true) {
      if ($entry_city_error == true) {
        echo xtc_draw_input_field('entry_city', $cInfo->entry_city, 'maxlength="32"') . '&#160;' . ENTRY_CITY_ERROR;
      } else {
        echo $cInfo->entry_city . xtc_draw_hidden_field('entry_city');
      }
    } else {
      echo xtc_draw_input_field('entry_city', $cInfo->entry_city, 'maxlength="32"', true);
    }
?></td>
          </tr>
<?php
    if (ACCOUNT_STATE == 'true') {
?>
          <tr>
            <td class="main"><?php echo ENTRY_STATE; ?></td>
            <td class="main"><?php
      $entry_state = xtc_get_zone_name($cInfo->entry_country_id, $cInfo->entry_zone_id, $cInfo->entry_state);
      if ($error == true) {
        if ($entry_state_error == true) {
          if ($entry_state_has_zones == true) {
            $zones_array = array();
            $zones_query = new xenQuery("select zone_name from " . TABLE_ZONES . " where zone_country_id = '" . xtc_db_input($cInfo->entry_country_id) . "' order by zone_name");
      $q = new xenQuery();
      if(!$q->run()) return;
            while ($zones_values = $q->output()) {
              $zones_array[] = array('id' => $zones_values['zone_name'], 'text' => $zones_values['zone_name']);
            }
            echo commerce_userapi_draw_pull_down_menu('entry_state', $zones_array) . '&#160;' . ENTRY_STATE_ERROR;
          } else {
            echo xtc_draw_input_field('entry_state', xtc_get_zone_name($cInfo->entry_country_id, $cInfo->entry_zone_id, $cInfo->entry_state)) . '&#160;' . ENTRY_STATE_ERROR;
          }
        } else {
          echo $entry_state . xtc_draw_hidden_field('entry_zone_id') . xtc_draw_hidden_field('entry_state');
        }
      } else {
        echo xtc_draw_input_field('entry_state', xtc_get_zone_name($cInfo->entry_country_id, $cInfo->entry_zone_id, $cInfo->entry_state));
      }
?></td>
         </tr>
<?php
    }
?>
          <tr>
            <td class="main"><?php echo ENTRY_COUNTRY; ?></td>
            <td class="main"><?php
    if ($error == true) {
      if ($entry_country_error == true) {
        echo commerce_userapi_draw_pull_down_menu('entry_country_id', xtc_get_countries(), $cInfo->entry_country_id) . '&#160;' . ENTRY_COUNTRY_ERROR;
      } else {
        echo xarModAPIFunc('commerce','user','get_country_name',array('country_id' =>$cInfo->entry_country_id)) . xtc_draw_hidden_field('entry_country_id');
      }
    } else {
      echo commerce_userapi_draw_pull_down_menu('entry_country_id', xtc_get_countries(), $cInfo->entry_country_id);
    }
?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo xtc_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td class="formAreaTitle"><?php echo CATEGORY_CONTACT; ?></td>
      </tr>
      <tr>
        <td class="formArea"><table border="0" cellspacing="2" cellpadding="2">
          <tr>
            <td class="main"><?php echo ENTRY_TELEPHONE_NUMBER; ?></td>
            <td class="main"><?php
    if ($error == true) {
      if ($entry_telephone_error == true) {
        echo xtc_draw_input_field('customers_telephone', $cInfo->customers_telephone, 'maxlength="32"') . '&#160;' . ENTRY_TELEPHONE_NUMBER_ERROR;
      } else {
        echo $cInfo->customers_telephone . xtc_draw_hidden_field('customers_telephone');
      }
    } else {
      echo xtc_draw_input_field('customers_telephone', $cInfo->customers_telephone, 'maxlength="32"', true);
    }
?></td>
          </tr>
          <tr>
            <td class="main"><?php echo ENTRY_FAX_NUMBER; ?></td>
            <td class="main"><?php
    if ($processed == true) {
      echo $cInfo->customers_fax . xtc_draw_hidden_field('customers_fax');
    } else {
      echo xtc_draw_input_field('customers_fax', $cInfo->customers_fax, 'maxlength="32"');
    }
?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo xtc_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td class="formAreaTitle"><?php echo CATEGORY_OPTIONS; ?></td>
      </tr>
      <tr>
        <td class="formArea"><table border="0" cellspacing="2" cellpadding="2">
          <tr>
            <td class="main"><?php echo ENTRY_NEWSLETTER; ?></td>
            <td class="main"><?php
    if ($processed == true) {
      if ($cInfo->customers_newsletter == '1') {
        echo ENTRY_NEWSLETTER_YES;
      } else {
        echo ENTRY_NEWSLETTER_NO;
      }
      echo xtc_draw_hidden_field('customers_newsletter');
    } else {
      echo commerce_userapi_draw_pull_down_menu('customers_newsletter', $newsletter_array, $cInfo->customers_newsletter);
    }
?></td>
          </tr>
          <tr>
<?php include(DIR_WS_MODULES . FILENAME_CUSTOMER_MEMO); ?>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo xtc_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td align="right" class="main">
<input type="image" src="#xarTplGetImage('buttons/' . xarSessionGetVar('language') . '/'.'button_update.gif')#" border="0" alt=IMAGE_UPDATE>
<a href="' . xarModURL('commerce','admin',(FILENAME_CUSTOMERS, xtc_get_all_get_params(array('action'))) .'">' . xarModAPIFunc('commerce','user','image',array('src' => xarTplGetImage('buttons/' . xarSessionGetVar('language') . '/'.'button_cancel.gif'),'alt' => IMAGE_CANCEL); . '</a>'; ?></td>
      </tr></form>
<?php
  } else {
?>
      <tr>
        <td>
        <table border="0" width="100%" cellspacing="0" cellpadding="0">
  <tr>
    <td width="80" rowspan="2"><?php echo xtc_image(xarTplGetImage(DIR_WS_ICONS.'heading_customers.gif'); ?></td>
    <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
  </tr>
  <tr>
    <td class="main" valign="top">XT Customers</td>
  </tr>
</table>

        <table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr><?php echo xtc_draw_form('search', FILENAME_CUSTOMERS, '', 'get'); ?>
            <td class="pageHeading"><?php echo '<a href="' . xarModURL('commerce','admin','create_account') . '">' .
  xarModAPIFunc('commerce','user','image',array('src' => xarTplGetImage('buttons/' . xarSessionGetVar('language') . '/'.'create_account.gif'),'alt' => CREATE_ACCOUNT);
            </a>'; ?></td>
            <td class="pageHeading" align="right"><?php echo xtc_draw_separator('pixel_trans.gif', 1, HEADING_IMAGE_HEIGHT); ?></td>
            <td class="smallText" align="right"><?php echo HEADING_TITLE_SEARCH . ' ' . xtc_draw_input_field('search'); ?></td>
          </form></tr>
          <tr><?php echo xtc_draw_form('status', FILENAME_CUSTOMERS, '', 'get'); ?>
<?php
$select_data=array();
$select_data=array(array('id' => '99', 'text' => TEXT_SELECT),array('id' => '100', 'text' => TEXT_ALL_CUSTOMERS));
?>
            <td class="smallText" align="right"><?php echo HEADING_TITLE_STATUS . ' ' . commerce_userapi_draw_pull_down_menu('status',xtc_array_merge($select_data, $customers_statuses_array), '99', 'onChange="this.form.submit();"'); ?></td>




          </form></tr>
        </table></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent" width="1"><?php echo TABLE_HEADING_GROUPIMAGE; ?></td>
                <td class="dataTableHeadingContent" width="1"><?php echo TABLE_HEADING_ACCOUNT_TYPE; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_LASTNAME; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_FIRSTNAME; ?></td>
                <td class="dataTableHeadingContent" align="left"><?php echo HEADING_TITLE_STATUS; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACCOUNT_CREATED; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&#160;</td>
              </tr>
<?php
    $search = '';
    if ( ($_GET['search']) && (xarModAPIFunc('commerce','user','not_null',array('arg' => $_GET['search']))) ) {
      $keywords = xtc_db_input(xtc_db_prepare_input($_GET['search']));
      $search = "where c.customers_lastname like '%" . $keywords . "%' or c.customers_firstname like '%" . $keywords . "%' or c.customers_email_address like '%" . $keywords . "%'";
    }

    if ($_GET['status'] && $_GET['status']!='100' or $_GET['status']=='0') {
      $status = xtc_db_prepare_input($_GET['status']);
    //  echo $status;
      $search ="where c.customers_status = '". $status . "'";
    }
    $customers_query_raw = "select c.account_type,c.customers_id, c.customers_lastname, c.customers_firstname, c.customers_email_address, a.entry_country_id, c.customers_status, c.member_flag from " . TABLE_CUSTOMERS . " c left join " . TABLE_ADDRESS_BOOK . " a on c.customers_id = a.customers_id and c.customers_default_address_id = a.address_book_id " . $search . " order by c.customers_lastname, c.customers_firstname";

    $customers_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $customers_query_raw, $customers_query_numrows);
    $customers_query = new xenQuery($customers_query_raw);
      $q = new xenQuery();
      if(!$q->run()) return;
    while ($customers = $q->output()) {
      $info_query = new xenQuery("select customers_info_date_account_created as date_account_created, customers_info_date_account_last_modified as date_account_last_modified, customers_info_date_of_last_logon as date_last_logon, customers_info_number_of_logons as number_of_logons from " . TABLE_CUSTOMERS_INFO . " where customers_info_id = '" . $customers['customers_id'] . "'");
      $q = new xenQuery();
      if(!$q->run()) return;
      $info = $q->output();

      if (((!$_GET['cID']) || (@$_GET['cID'] == $customers['customers_id'])) && (!$cInfo)) {
        $country_query = new xenQuery("select countries_name from " . TABLE_COUNTRIES . " where countries_id = '" . $customers['entry_country_id'] . "'");
      $q = new xenQuery();
      if(!$q->run()) return;
        $country = $q->output();

        $reviews_query = new xenQuery("select count(*) as number_of_reviews from " . TABLE_REVIEWS . " where customers_id = '" . $customers['customers_id'] . "'");
      $q = new xenQuery();
      if(!$q->run()) return;
        $reviews = $q->output();

        $customer_info = xtc_array_merge($country, $info, $reviews);

        $cInfo_array = xtc_array_merge($customers, $customer_info);
        $cInfo = new objectInfo($cInfo_array);
      }

      if ( (is_object($cInfo)) && ($customers['customers_id'] == $cInfo->customers_id) ) {
        echo '          <tr class="dataTableRowSelected" onmouseover="this.style.cursor=\'hand\'" onclick="document.location.href=\'' . xarModURL('commerce','admin',(FILENAME_CUSTOMERS, xtc_get_all_get_params(array('cID', 'action')) . 'cID=' . $cInfo->customers_id . '&action=edit') . '\'">' . "\n";
      } else {
        echo '          <tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'hand\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . xarModURL('commerce','admin',(FILENAME_CUSTOMERS, xtc_get_all_get_params(array('cID')) . 'cID=' . $customers['customers_id']) . '\'">' . "\n";
      }
?>

                <td class="dataTableContent"><?php if ($customers_statuses_array[$customers['customers_status']]['csa_image'] != '') { echo xtc_image(xarTplGetImage(DIR_WS_ICONS . $customers_statuses_array[$customers['customers_status']]['csa_image']), ''); } ?>&#160;</td>
<?php

                 if  ($customers['account_type']==1) {


                echo '<td class="dataTableContent">';
                 echo TEXT_GUEST;



                 } else {
                 echo '<td class="dataTableContent">';
                 echo TEXT_ACCOUNT;
                 }
                 ?></td>
                <td class="dataTableContent"><b><?php echo $customers['customers_lastname']; ?></b></td>
                <td class="dataTableContent"><?php echo $customers['customers_firstname']; ?></td>
                <td class="dataTableContent" align="left"><?php echo $customers_statuses_array[$customers['customers_status']]['text'] . ' (' . $customers['customers_status'] . ')' ; ?></td>
                <td class="dataTableContent" align="right">#xarModAPIFunc('commerce','user','date_short',array('raw_date' =>$info['date_account_created']))#</td>
                <td class="dataTableContent" align="right"><?php if ( (is_object($cInfo)) && ($customers['customers_id'] == $cInfo->customers_id) ) { echo xtc_image(xarTplGetImage(DIR_WS_IMAGES . 'icon_arrow_right.gif'), ''); } else { echo '<a href="' . xarModURL('commerce','admin',(FILENAME_CUSTOMERS, xtc_get_all_get_params(array('cID')) . 'cID=' . $customers['customers_id']) . '">' . xtc_image(xarTplGetImage(DIR_WS_IMAGES . 'icon_info.gif'), IMAGE_ICON_INFO) . '</a>'; } ?>&#160;</td>
              </tr>
<?php
    }
?>
              <tr>
                <td colspan="4"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $customers_split->display_count($customers_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_CUSTOMERS); ?></td>
                    <td class="smallText" align="right"><?php echo $customers_split->display_links($customers_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page'], xtc_get_all_get_params(array('page', 'info', 'x', 'y', 'cID'))); ?></td>
                  </tr>
<?php
    if (xarModAPIFunc('commerce','user','not_null',array('arg' => $_GET['search']))) {
?>
                  <tr>
                    <td align="right" colspan="2"><?php echo '<a href="' . xarModURL('commerce','admin',(FILENAME_CUSTOMERS) . '">' .
  xarModAPIFunc('commerce','user','image',array('src' => xarTplGetImage('buttons/' . xarSessionGetVar('language') . '/'.'button_reset.gif'),'alt' => IMAGE_RESET);
                    </a>'; ?></td>
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
    case 'confirm':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_CUSTOMER . '</b>');

      $contents = array('form' => xtc_draw_form('customers', FILENAME_CUSTOMERS, xtc_get_all_get_params(array('cID', 'action')) . 'cID=' . $cInfo->customers_id . '&action=deleteconfirm'));
      $contents[] = array('text' => TEXT_DELETE_INTRO . '<br><br><b>' . $cInfo->customers_firstname . ' ' . $cInfo->customers_lastname . '</b>');
      if ($cInfo->number_of_reviews > 0) $contents[] = array('text' => '<br>' . xtc_draw_checkbox_field('delete_reviews', 'on', true) . ' ' . sprintf(TEXT_DELETE_REVIEWS, $cInfo->number_of_reviews));
      $contents[] = array('align' => 'center', 'text' => '<br>' . <input type="image" src="#xarTplGetImage('buttons/' . xarSessionGetVar('language') . '/'.'button_delete.gif')#" border="0" alt=IMAGE_DELETE> . ' <a href="' . xarModURL('commerce','admin',(FILENAME_CUSTOMERS, xtc_get_all_get_params(array('cID', 'action')) . 'cID=' . $cInfo->customers_id) . '">' . xarModAPIFunc('commerce','user','image',array('src' => xarTplGetImage('buttons/' . xarSessionGetVar('language') . '/'.'button_cancel.gif'),'alt' => IMAGE_CANCEL); . '</a>');
      break;

    case 'editstatus':
      if ($_GET['cID'] != 1) {
        $customers_history_query = new xenQuery("select new_value, old_value, date_added, customer_notified from " . TABLE_CUSTOMERS_STATUS_HISTORY . " where customers_id = '" . xtc_db_input($_GET['cID']) . "' order by customers_status_history_id desc");
        $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_STATUS_CUSTOMER . '</b>');
        $contents = array('form' => xtc_draw_form('customers', FILENAME_CUSTOMERS, xtc_get_all_get_params(array('cID', 'action')) . 'cID=' . $cInfo->customers_id . '&action=statusconfirm'));
        $contents[] = array('text' => '<br>' . commerce_userapi_draw_pull_down_menu('status', $customers_statuses_array, $cInfo->customers_status) );
        $contents[] = array('text' => '<table nowrap border="0" cellspacing="0" cellpadding="0"><tr><td style="border-bottom: 1px solid; border-color: #000000;" nowrap class="smallText" align="center"><b>' . TABLE_HEADING_NEW_VALUE .' </b></td><td style="border-bottom: 1px solid; border-color: #000000;" nowrap class="smallText" align="center"><b>' . TABLE_HEADING_DATE_ADDED . '</b></td></tr>');

        if ($customers_history_query)->getrows() {
      $q = new xenQuery();
      if(!$q->run()) return;
          while ($customers_history = $q->output()) {

            $contents[] = array('text' => '<tr>' . "\n" . '<td class="smallText">' . $customers_statuses_array[$customers_history['new_value']]['text'] . '</td>' . "\n" .'<td class="smallText" align="center">' . xtc_datetime_short($customers_history['date_added']) . '</td>' . "\n" .'<td class="smallText" align="center">');

            $contents[] = array('text' => '</tr>' . "\n");
          }
        } else {
          $contents[] = array('text' => '<tr>' . "\n" . ' <td class="smallText" colspan="2">' . TEXT_NO_CUSTOMER_HISTORY . '</td>' . "\n" . ' </tr>' . "\n");
        }
        $contents[] = array('text' => '</table>');
        $contents[] = array('align' => 'center', 'text' => '<br>' . <input type="image" src="#xarTplGetImage('buttons/' . xarSessionGetVar('language') . '/'.'button_update.gif')#" border="0" alt=IMAGE_UPDATE> . ' <a href="' . xarModURL('commerce','admin',(FILENAME_CUSTOMERS, xtc_get_all_get_params(array('cID', 'action')) . 'cID=' . $cInfo->customers_id) . '">' . xarModAPIFunc('commerce','user','image',array('src' => xarTplGetImage('buttons/' . xarSessionGetVar('language') . '/'.'button_cancel.gif'),'alt' => IMAGE_CANCEL); . '</a>');
        $status = xtc_db_prepare_input($_POST['status']);    // maybe this line not needed to recheck...
      }
      break;

    default:
      $customer_status = xtc_get_customer_status ($_GET['cID']);
      $cs_id = $customer_status['customers_status'];
      $cs_member_flag  = $customer_status['member_flag'];
      $cs_name = $customer_status['customers_status_name'];
      $cs_image = $customer_status['customers_status_image'];
      $cs_discount = $customer_status['customers_status_discount'];
      $cs_ot_discount_flag  = $customer_status['customers_status_ot_discount_flag'];
      $cs_ot_discount = $customer_status['customers_status_ot_discount'];
      $cs_staffelpreise = $customer_status['customers_status_staffelpreise'];
      $cs_payment_unallowed = $customer_status['customers_status_payment_unallowed'];

//      echo 'customer_status ' . $cID . 'variables = ' . $cs_id . $cs_member_flag . $cs_name .  $cs_discount .  $cs_image . $cs_ot_discount;

      if (is_object($cInfo)) {
        $heading[] = array('text' => '<b>' . $cInfo->customers_firstname . ' ' . $cInfo->customers_lastname . '</b>');
        if ($cInfo->customers_id != 1) {
          $contents[] = array('align' => 'center', 'text' => '<a href="' . xarModURL('commerce','admin',(FILENAME_CUSTOMERS, xtc_get_all_get_params(array('cID', 'action')) . 'cID=' . $cInfo->customers_id . '&action=edit') . '">' . xarModAPIFunc('commerce','user','image',array('src' => xarTplGetImage('buttons/' . xarSessionGetVar('language') . '/'.'button_edit.gif'),'alt' => IMAGE_EDIT); . '</a>');
        }
        if ($cInfo->customers_id == 1 && $_SESSION['customer_id'] == 1) {
          $contents[] = array('align' => 'center', 'text' => '<a href="' . xarModURL('commerce','admin',(FILENAME_CUSTOMERS, xtc_get_all_get_params(array('cID', 'action')) . 'cID=' . $cInfo->customers_id . '&action=edit') . '">' . xarModAPIFunc('commerce','user','image',array('src' => xarTplGetImage('buttons/' . xarSessionGetVar('language') . '/'.'button_edit.gif'),'alt' => IMAGE_EDIT); . '</a>');
        }
        if ($cs_id != 0) {
          $contents[] = array('align' => 'center', 'text' => '<a href="' . xarModURL('commerce','admin',(FILENAME_CUSTOMERS, xtc_get_all_get_params(array('cID', 'action')) . 'cID=' . $cInfo->customers_id . '&action=confirm') . '">' . xarModAPIFunc('commerce','user','image',array('src' => xarTplGetImage('buttons/' . xarSessionGetVar('language') . '/'.'button_delete.gif'),'alt' => IMAGE_DELETE); . '</a>');
        }
        if ($cInfo->customers_id != 1 /*&& $_SESSION['customer_id'] == 1*/) {
          $contents[] = array('align' => 'center', 'text' => '<a href="' . xarModURL('commerce','admin',(FILENAME_CUSTOMERS, xtc_get_all_get_params(array('cID', 'action')) . 'cID=' . $cInfo->customers_id . '&action=editstatus') . '">' .
        xarModAPIFunc('commerce','user','image',array('src' => xarTplGetImage('buttons/' . xarSessionGetVar('language') . '/'.'button_status.gif'),'alt' => IMAGE_STATUS);
          </a>');
        }
        // elari cs v3.x changed for added accounting module
        if ($cInfo->customers_id != 1) {
          $contents[] = array('align' => 'center', 'text' => '<a href="' . xarModURL('commerce','admin',(FILENAME_ACCOUNTING, xtc_get_all_get_params(array('cID', 'action')) . 'cID=' . $cInfo->customers_id) . '">' .
        xarModAPIFunc('commerce','user','image',array('src' => xarTplGetImage('buttons/' . xarSessionGetVar('language') . '/'.'button_accounting.gif'),'alt' => IMAGE_ACCOUNTING);
          </a>');
        }
        // elari cs v3.x changed for added iplog module
        $contents[] = array('align' => 'center', 'text' => '<a href="' . xarModURL('commerce','admin',(FILENAME_ORDERS, 'cID=' . $cInfo->customers_id) . '">' .
        xarModAPIFunc('commerce','user','image',array('src' => xarTplGetImage('buttons/' . xarSessionGetVar('language') . '/'.'button_orders.gif'),'alt' => IMAGE_ORDERS);
        </a> <a href="' . xarModURL('commerce','admin',(FILENAME_MAIL, 'selected_box=tools&customer=' . $cInfo->customers_email_address) . '">' .
         xarModAPIFunc('commerce','user','image',array('src' => xarTplGetImage('buttons/' . xarSessionGetVar('language') . '/'.'button_email.gif'),'alt' => IMAGE_EMAIL);
        </a><br><a href="' . xarModURL('commerce','admin',(FILENAME_CUSTOMERS, xtc_get_all_get_params(array('cID', 'action')) . 'cID=' . $cInfo->customers_id . '&action=iplog') . '">' .
        xarModAPIFunc('commerce','user','image',array('src' => xarTplGetImage('buttons/' . xarSessionGetVar('language') . '/'.'button_iplog.gif'),'alt' => IMAGE_IPLOG);
        </a>');

        $contents[] = array('text' => '<br>' . TEXT_DATE_ACCOUNT_CREATED . ' ' . xarModAPIFunc('commerce','user','date_short',array('raw_date' =>$cInfo->date_account_created)));
        $contents[] = array('text' => '<br>' . TEXT_DATE_ACCOUNT_LAST_MODIFIED . ' ' . xarModAPIFunc('commerce','user','date_short',array('raw_date' =>$cInfo->date_account_last_modified)));
        $contents[] = array('text' => '<br>' . TEXT_INFO_DATE_LAST_LOGON . ' '  . xarModAPIFunc('commerce','user','date_short',array('raw_date' =>$cInfo->date_last_logon)));
        $contents[] = array('text' => '<br>' . TEXT_INFO_NUMBER_OF_LOGONS . ' ' . $cInfo->number_of_logons);
        $contents[] = array('text' => '<br>' . TEXT_INFO_COUNTRY . ' ' . $cInfo->countries_name);
        $contents[] = array('text' => '<br>' . TEXT_INFO_NUMBER_OF_REVIEWS . ' ' . $cInfo->number_of_reviews);
      }

      if ($_GET['action']=='iplog') {
        $contents[] = array('text' => '<br><b>IPLOG :' );
        $customers_id = xtc_db_prepare_input($_GET['cID']);
        $customers_log_info_array = xtc_get_user_info($customers_id);
        if ($customers_log_info_array->getrows()) {
      $q = new xenQuery();
      if(!$q->run()) return;
          while ($customers_log_info = $q->output()) {
            $contents[] = array('text' => '<tr>' . "\n" . '<td class="smallText">' . $customers_log_info['customers_ip_date'] . ' ' . $customers_log_info['customers_ip']);
          }
        }
      }
      break;
  }

  if ( (xarModAPIFunc('commerce','user','not_null',array('arg' => $heading))) && (xarModAPIFunc('commerce','user','not_null',array('arg' => $contents))) ) {
    echo '            <td width="25%" valign="top">' . "\n";

    $box = new box;
    echo $box->infoBox($heading, $contents);

    echo '            </td>' . "\n";
  }
?>
          </tr>
        </table></td>
      </tr>
<?php
  }
}
?>