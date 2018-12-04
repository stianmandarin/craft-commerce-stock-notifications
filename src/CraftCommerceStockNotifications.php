<?php
/**
 * Craft Commerce Stock Notifications plugin for Craft CMS 3.x
 *
 * Add a signup form to out of stock products that automatically email them when the product is back in stock.
 *
 * @link      https://mandarindesign.no
 * @copyright Copyright (c) 2018 Mandarin Design
 */

namespace mandarindesign\craftcommercestocknotifications;

use mandarindesign\craftcommercestocknotifications\variables\CraftCommerceStockNotificationsVariable;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\web\UrlManager;
use craft\web\twig\variables\CraftVariable;
use craft\events\RegisterUrlRulesEvent;
use craft\commerce\elements\Product;
use mandarindesign\craftcommercestocknotifications\records\Stock;

use yii\base\Event;

/**
 * Craft plugins are very much like little applications in and of themselves. We’ve made
 * it as simple as we can, but the training wheels are off. A little prior knowledge is
 * going to be required to write a plugin.
 *
 * For the purposes of the plugin docs, we’re going to assume that you know PHP and SQL,
 * as well as some semi-advanced concepts like object-oriented programming and PHP namespaces.
 *
 * https://craftcms.com/docs/plugins/introduction
 *
 * @author    Mandarin Design
 * @package   CraftCommerceStockNotifications
 * @since     1.0.0
 *
 */
class CraftCommerceStockNotifications extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * Static property that is an instance of this plugin class so that it can be accessed via
     * CraftCommerceStockNotifications::$plugin
     *
     * @var CraftCommerceStockNotifications
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * To execute your plugin’s migrations, you’ll need to increase its schema version.
     *
     * @var string
     */
    public $schemaVersion = '1.0.0';

    // Public Methods
    // =========================================================================

    /**
     * Set our $plugin static property to this class so that it can be accessed via
     * CraftCommerceStockNotifications::$plugin
     *
     * Called after the plugin class is instantiated; do any one-time initialization
     * here such as hooks and events.
     *
     * If you have a '/vendor/autoload.php' file, it will be loaded for you automatically;
     * you do not need to load it in your init() method.
     *
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        // Register our site routes
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_SITE_URL_RULES,
            function(RegisterUrlRulesEvent $event) {
                $event->rules['siteActionTrigger1'] = 'craft-commerce-stock-notifications/stock';
            }
        );

        // Register our CP routes
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function(RegisterUrlRulesEvent $event) {
                $event->rules['cpActionTrigger1'] = 'craft-commerce-stock-notifications/stock/do-something';
            }
        );

        // Register our variables
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function(Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('craftCommerceStockNotifications', CraftCommerceStockNotificationsVariable::class);
            }
        );

        // Do something after we're installed
        /*Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function(PluginEvent $event) {
                if ($event->plugin === $this) {
                    // We were just installed
                }
            }
        );*/

        // Fulfill any notification requests when a Commerce product is saved
        Event::on(Product::class, Product::EVENT_AFTER_SAVE, function($e) {
            $siteName = Craft::$app->sites->getPrimarySite()->name;
            $productId = $e->sender->defaultVariant['productId'];
            $product = Product::find()->id($productId)->one();
            $productName = $e->sender['title'];
            $productUrl = $_SERVER['HTTP_HOST'].'/'.$e->sender['uri'];

            foreach ($product->variants as $variant) {
                $variantId = $variant->id;

                if ($variant->hasStock()) {
                    // Find emails that should be notified
                    $emails = Stock::find()
                        ->where(['variantId' => $variantId])
                        ->all();

                    // Notify each email
                    foreach ($emails as $email) {
                        Craft::$app->mailer->compose()
                            ->setFrom('noreply@'.$_SERVER['HTTP_HOST'])
                            ->setTo($email['email'])
                            ->setSubject($productName.' is back in stock!')
                            ->setHtmlBody("<p>Hey!<br><b><a href='$productUrl'>$productName</a> is back in stock!</b><br>Regards $siteName.<br><small>You're receiving this email because you asked to be notified when this product became available.</small></p>")
                            ->send();

                        // Delete the record to avoid sending duplicate emails
                        $notification = Stock::findOne(['variantId' => $variantId, 'email' => $email['email']]);
                        $notification->delete();
                    }
                }
            }
        });

        /**
         * Logging in Craft involves using one of the following methods:
         *
         * Craft::trace(): record a message to trace how a piece of code runs. This is mainly for development use.
         * Craft::info(): record a message that conveys some useful information.
         * Craft::warning(): record a warning message that indicates something unexpected has happened.
         * Craft::error(): record a fatal error that should be investigated as soon as possible.
         *
         * Unless `devMode` is on, only Craft::warning() & Craft::error() will log to `craft/storage/logs/web.log`
         *
         * It's recommended that you pass in the magic constant `__METHOD__` as the second parameter, which sets
         * the category to the method (prefixed with the fully qualified class name) where the constant appears.
         *
         * To enable the Yii debug toolbar, go to your user account in the AdminCP and check the
         * [] Show the debug toolbar on the front end & [] Show the debug toolbar on the Control Panel
         *
         * http://www.yiiframework.com/doc-2.0/guide-runtime-logging.html
         */
        Craft::info(
            Craft::t(
                'craft-commerce-stock-notifications',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    // Protected Methods
    // =========================================================================

}
