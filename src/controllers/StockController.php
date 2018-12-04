<?php
/**
 * Craft Commerce Stock Notifications plugin for Craft CMS 3.x
 *
 * Add a signup form to out of stock products that automatically email them when the product is back in stock.
 *
 * @link      https://mandarindesign.no
 * @copyright Copyright (c) 2018 Mandarin Design
 */

namespace mandarindesign\craftcommercestocknotifications\controllers;

use craft\commerce\widgets\Orders;
use mandarindesign\craftcommercestocknotifications\CraftCommerceStockNotifications;

use Craft;
use craft\web\Controller;
use mandarindesign\craftcommercestocknotifications\records\Stock;

/**
 * Stock Controller
 *
 * Generally speaking, controllers are the middlemen between the front end of
 * the CP/website and your plugin’s services. They contain action methods which
 * handle individual tasks.
 *
 * A common pattern used throughout Craft involves a controller action gathering
 * post data, saving it on a model, passing the model off to a service, and then
 * responding to the request appropriately depending on the service method’s response.
 *
 * Action methods begin with the prefix “action”, followed by a description of what
 * the method does (for example, actionSaveIngredient()).
 *
 * https://craftcms.com/docs/plugins/controllers
 *
 * @author    Mandarin Design
 * @package   CraftCommerceStockNotifications
 * @since     1.0.0
 */
class StockController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected $allowAnonymous = ['index', 'save-notification'];

    // Public Methods
    // =========================================================================

    /**
     * Handle a request going to our plugin's actionDoSomething URL,
     * e.g.: actions/craft-commerce-stock-notifications/stock/save-notification
     *
     * @return mixed
     */
    public function actionSaveNotification()
    {
        $request = Craft::$app->request;
        $productId = $request->post('productId');
        $variantId = $request->post('variantId');
        $email = $request->post('email');
        $userId = Craft::$app->user->id;

        $notification = Stock::find()
            ->where(['productId' => $productId, 'userId' => $userId])
            ->one();

        // Save notification to db
        if (!$notification) {
            $new = new Stock();
            $new->productId = $productId;
            $new->variantId = $variantId;
            $new->userId = $userId;
            $new->email = $email;
            $new->save();
        } else {
            $update = Stock::findOne(['productId' => $productId, 'userId' => $userId]);
            $update->email = $email;
            $update->save();
        }

        // Redirect to referrer
        $referrer = preg_replace('/\?.*/', '', Craft::$app->request->getReferrer());
        return $this->redirect($referrer.'?notification=saved');
    }

    /**
     * Delete notification for logged in user
     * actions/craft-commerce-stock-notifications/stock/delete-notification?productId=x&email=x
     */
    public function actionDeleteNotification($productId, $email)
    {
        if (!Craft::$app->user->getIsGuest()) {
            // Delete record if it matches the logged in user
            $customer = Stock::findOne(['productId' => $productId, 'email' => $email, 'userId' => Craft::$app->user->id]);
            $customer->delete();

            // Redirect to referrer with optional URL param
            $referrer = preg_replace('/\?.*/', '', Craft::$app->request->getReferrer());
            return $this->redirect($referrer.'?notification=deleted');
        }
    }
}
