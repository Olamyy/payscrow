<?php


$installer = $this;
$installer->startSetup();

$installer->run("
    CREATE TABLE IF NOT EXISTS `{$this->getTable('payScrow_log')}` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `entry_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `version` varchar(25) NOT NULL COLLATE utf8_unicode_ci,
        `merchant_info` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
        `dev_info` text COLLATE utf8_unicode_ci DEFAULT NULL,
        `dev_info_additional` text COLLATE utf8_unicode_ci DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_general_ci;
    
    CREATE TABLE IF NOT EXISTS `{$this->getTable('payScrow_fastCheckout')}` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
        `client_id` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
        `cc_payment_id` varchar(250) COLLATE utf8_unicode_ci NULL,
        `elv_payment_id` varchar(250) COLLATE utf8_unicode_ci NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `userId` (`user_id`)
    ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_general_ci;
");
    
$installer->run("UPDATE `{$this->getTable('sales_flat_quote_payment')}` SET method = 'payScrow_creditcard' WHERE method = 'payScrowcc';");

$installer->run("UPDATE `{$this->getTable('sales_flat_quote_payment')}` SET method = 'payScrow_creditcard' WHERE method = 'payScrowcc';");

$installer->run("UPDATE `{$this->getTable('sales_flat_quote_payment')}` SET method = 'payScrow_directdebit' WHERE method = 'payScrowelv';");

$installer->run("UPDATE `{$this->getTable('sales_flat_quote_payment')}` SET method = 'payScrow_directdebit' WHERE method = 'payScrowelv';");

$installer->endSetup();