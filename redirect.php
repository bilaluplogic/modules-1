<?php
/* -----------------------------------------------------------------------------------------
   $Id: redirect.php,v 1.2 2003/09/09 18:25:28 fanta2k Exp $

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(redirect.php,v 1.9 2003/02/13); www.oscommerce.com
   (c) 2003  nextcommerce (redirect.php,v 1.7 2003/08/17); www.nextcommerce.org

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  include( 'includes/application_top.php');

  require_once(DIR_FS_INC . 'xtc_update_banner_click_count.inc.php');

  switch ($_GET['action']) {
    case 'banner':
      $banner_query = new xenQuery("select banners_url from " . TABLE_BANNERS . " where banners_id = '" . $_GET['goto'] . "'");
      if ($banner_query->getrows()) {
      $q = new xenQuery();
      if(!$q->run()) return;
        $banner = $q->output();
        xtc_update_banner_click_count($_GET['goto']);

        xtc_redirect($banner['banners_url']);
      } else {
        xarRedirectResponse(xarModURL('commerce','user','default'));
      }
      break;

    case 'url':
      if (isset($_GET['goto'])) {
        xtc_redirect('http://' . $_GET['goto']);
      } else {
        xarRedirectResponse(xarModURL('commerce','user','default'));
      }
      break;

    case 'manufacturer':
      if (isset($_GET['manufacturers_id'])) {
        $manufacturer_query = new xenQuery("select manufacturers_url from " . TABLE_MANUFACTURERS_INFO . " where manufacturers_id = '" . (int)$_GET['manufacturers_id'] . "' and languages_id = '" . $_SESSION['languages_id'] . "'");
        if (!$manufacturer_query->getrows()) {
          // no url exists for the selected language, lets use the default language then
          $manufacturer_query = new xenQuery("select mi.languages_id, mi.manufacturers_url from " . TABLE_MANUFACTURERS_INFO . " mi, " . TABLE_LANGUAGES . " l where mi.manufacturers_id = '" . (int)$_GET['manufacturers_id'] . "' and mi.languages_id = l.languages_id and l.code = '" . DEFAULT_LANGUAGE . "'");
          if (!$manufacturer_query->getrows()) {
            // no url exists, return to the site
            xarRedirectResponse(xarModURL('commerce','user','default'));
          } else {
      $q = new xenQuery();
      if(!$q->run()) return;
            $manufacturer = $q->output();
            new xenQuery("update " . TABLE_MANUFACTURERS_INFO . " set url_clicked = url_clicked+1, date_last_click = now() where manufacturers_id = '" . (int)$_GET['manufacturers_id'] . "' and languages_id = '" . $manufacturer['languages_id'] . "'");
          }
        } else {
          // url exists in selected language
      $q = new xenQuery();
      if(!$q->run()) return;
          $manufacturer = $q->output();
          new xenQuery("update " . TABLE_MANUFACTURERS_INFO . " set url_clicked = url_clicked+1, date_last_click = now() where manufacturers_id = '" . (int)$_GET['manufacturers_id'] . "' and languages_id = '" . $_SESSION['languages_id'] . "'");
        }

        xtc_redirect($manufacturer['manufacturers_url']);
      } else {
        xarRedirectResponse(xarModURL('commerce','user','default'));
      }
      break;

    default:
      xarRedirectResponse(xarModURL('commerce','user','default'));
      break;
  }
?>