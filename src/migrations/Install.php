<?php
/**
 * Craft Commerce Stock Notifications plugin for Craft CMS 3.x
 *
 * Add a signup form to out of stock products that automatically email them when the product is back in stock.
 *
 * @link      https://mandarindesign.no
 * @copyright Copyright (c) 2018 Mandarin Design
 */

namespace mandarindesign\craftcommercestocknotifications\migrations;

use mandarindesign\craftcommercestocknotifications\CraftCommerceStockNotifications;

use Craft;
use craft\config\DbConfig;
use craft\db\Migration;

/**
 * Craft Commerce Stock Notifications Install Migration
 *
 * If your plugin needs to create any custom database tables when it gets installed,
 * create a migrations/ folder within your plugin folder, and save an Install.php file
 * within it using the following template:
 *
 * If you need to perform any additional actions on install/uninstall, override the
 * safeUp() and safeDown() methods.
 *
 * @author    Mandarin Design
 * @package   CraftCommerceStockNotifications
 * @since     1.0.0
 */
class Install extends Migration
{
    // Public Properties
    // =========================================================================

    /**
     * @var string The database driver to use
     */
    public $driver;

    // Public Methods
    // =========================================================================

    /**
     * This method contains the logic to be executed when applying this migration.
     * This method differs from [[up()]] in that the DB logic implemented here will
     * be enclosed within a DB transaction.
     * Child classes may implement this method instead of [[up()]] if the DB logic
     * needs to be within a transaction.
     *
     * @return boolean return a false value to indicate the migration fails
     * and should not proceed further. All other return values mean the migration succeeds.
     */
    public function safeUp()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        if ($this->createTables()) {
            $this->createIndexes();
            $this->addForeignKeys();
            // Refresh the db schema caches
            Craft::$app->db->schema->refresh();
            $this->insertDefaultData();
        }

        return true;
    }

    /**
     * This method contains the logic to be executed when removing this migration.
     * This method differs from [[down()]] in that the DB logic implemented here will
     * be enclosed within a DB transaction.
     * Child classes may implement this method instead of [[down()]] if the DB logic
     * needs to be within a transaction.
     *
     * @return boolean return a false value to indicate the migration fails
     * and should not proceed further. All other return values mean the migration succeeds.
     */
    public function safeDown()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        $this->removeTables();

        return true;
    }

    // Protected Methods
    // =========================================================================

    /**
     * Creates the tables needed for the Records used by the plugin
     *
     * @return bool
     */
    protected function createTables()
    {
        $tablesCreated = false;

        // craftcommercestocknotifications_stock table
        $tableSchema = Craft::$app->db->schema->getTableSchema('{{%craftcommercestocknotifications_stock}}');
        if ($tableSchema === null) {
            $tablesCreated = true;
            $this->createTable(
                '{{%craftcommercestocknotifications_stock}}',
                [
                    'id' => $this->primaryKey(),
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'uid' => $this->uid(),
                    'productId' => $this->integer()->notNull(),
                    'variantId' => $this->integer(),
                    'userId' => $this->integer(),
                    'email' => $this->string(255)->notNull()->defaultValue('')
                ]
            );
        }

        return $tablesCreated;
    }

    /**
     * Creates the indexes needed for the Records used by the plugin
     *
     * @return void
     */
    protected function createIndexes()
    {
        // craftcommercestocknotifications_stock table
        $this->createIndex(
            $this->db->getIndexName(
                '{{%craftcommercestocknotifications_stock}}',
                'productId',
                false
            ),
            '{{%craftcommercestocknotifications_stock}}',
            'productId',
            false
        );
        $this->createIndex(
            $this->db->getIndexName(
                '{{%craftcommercestocknotifications_stock}}',
                'variantId',
                false
            ),
            '{{%craftcommercestocknotifications_stock}}',
            'variantId',
            false
        );
        $this->createIndex(
            $this->db->getIndexName(
                '{{%craftcommercestocknotifications_stock}}',
                'userId',
                false
            ),
            '{{%craftcommercestocknotifications_stock}}',
            'userId',
            false
        );
        $this->createIndex(
            $this->db->getIndexName(
                '{{%craftcommercestocknotifications_stock}}',
                'email',
                false
            ),
            '{{%craftcommercestocknotifications_stock}}',
            'email',
            false
        );
        // Additional commands depending on the db driver
        switch ($this->driver) {
            case DbConfig::DRIVER_MYSQL:
                break;
            case DbConfig::DRIVER_PGSQL:
                break;
        }
    }

    /**
     * Creates the foreign keys needed for the Records used by the plugin
     *
     * @return void
     */
    protected function addForeignKeys()
    {
        // craftcommercestocknotifications_stock table
        /*$this->addForeignKey(
            $this->db->getForeignKeyName('{{%craftcommercestocknotifications_stock}}', 'siteId'),
            '{{%craftcommercestocknotifications_stock}}',
            'siteId',
            '{{%sites}}',
            'id',
            'CASCADE',
            'CASCADE'
        );*/
    }

    /**
     * Populates the DB with the default data.
     *
     * @return void
     */
    protected function insertDefaultData()
    {
    }

    /**
     * Removes the tables needed for the Records used by the plugin
     *
     * @return void
     */
    protected function removeTables()
    {
        // craftcommercestocknotifications_stock table
        $this->dropTableIfExists('{{%craftcommercestocknotifications_stock}}');
    }
}
