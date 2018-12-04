<?php
/**
 * Craft Commerce Stock Notifications plugin for Craft CMS 3.x
 *
 * Add a signup form to out of stock products that automatically email them when the product is back in stock.
 *
 * @link      https://mandarindesign.no
 * @copyright Copyright (c) 2018 Mandarin Design
 */

namespace mandarindesign\craftcommercestocknotifications\variables;

use mandarindesign\craftcommercestocknotifications\CraftCommerceStockNotifications;

use Craft;
use mandarindesign\craftcommercestocknotifications\records\Stock;

/**
 * Craft Commerce Stock Notifications Variable
 *
 * Craft allows plugins to provide their own template variables, accessible from
 * the {{ craft }} global variable (e.g. {{ craft.craftCommerceStockNotifications }}).
 *
 * https://craftcms.com/docs/plugins/variables
 *
 * @author    Mandarin Design
 * @package   CraftCommerceStockNotifications
 * @since     1.0.0
 */
class CraftCommerceStockNotificationsVariable
{
    // Public Methods
    // =========================================================================

    /**
     * Check if a notification is already set based on a product id
     * {{ craft.craftCommerceStockNotifications.notificationSet(product.id) }}
     */
    public function notificationSet($productId)
    {
        if (!Craft::$app->user->getIsGuest()) {
            // User is logged in; check db to see if he has set a notification on this product already
            $notification = Stock::find()
                ->where(['productId' => $productId, 'userId' => Craft::$app->user->id])
                ->one();

            if ($notification) {
                // He has, return the email to be notified
                return $notification['email'];
            }
        } /*elseif (isset($_COOKIE['stock'.$productId])) {
            // The user is not logged in, but he has set a notification regardless, and it saved a cookie with his email
            // Return the email to be notified, but only if the cookie notification still exists in the db
            $email = $_COOKIE['stock'.$productId];

            $notification = Stock::find()
                ->where(['productId' => $productId, 'email' => $email])
                ->one();

            if ($notification) {
                return $email;
            } else {
                return false;
            }
        }*/

        return false;
    }

}
