create table if not exists `[prefix]products` (
  `id` int(10) unsigned not null auto_increment,
  `name` varchar(255) not null,
  `price` decimal(12,2) not null,
  `currency` text not null,
  `quantity` int(10) unsigned not null default 0,
  `dateadded` datetime not null,
  `companyid` text not null,
  `externalid` text not null,
  `notes` text not null,
  `views` int(10) unsigned not null,
  `slug` text not null,
  `weight` decimal(8,2) unsigned not null default 0,
  `image strapline` text,
  `deposit` varchar(200) not null,
  `video` varchar(200) not null,
  `code` tinyint default 0,
  `description` varchar(10) not null default '0',
  `billingnotes` varchar(255) not null,
  `showit` tinyint(1) default 1,
  `historyid` int(10) unsigned not null,
  primary key(`id`)
) DEFAULT CHARSET=utf8;

create table if not exists `[prefix]downloads` (
  `id` int(10) unsigned not null auto_increment,
  `duid` varchar(100),
  `downloaded_on` datetime null,
  `ip` varchar(50) not null,
  primary key(`id`)
) DEFAULT CHARSET=utf8;

CREATE TABLE `[prefix]productgallery` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `productid` int(255) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `baseurl` varchar(255) DEFAULT NULL,
  `cdnurl` varchar(255) DEFAULT '0',
  `filepickerurl` varchar(255) DEFAULT NULL,
  `showit` int(11) DEFAULT '1',
  `width` varchar(11) DEFAULT NULL,
  `height` varchar(11) DEFAULT NULL,
  `fit` varchar(11) DEFAULT NULL,
  `filetype` varchar(255) DEFAULT NULL,
  `hero` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3792 DEFAULT CHARSET=latin1;


CREATE TABLE `[prefix]productsmeta` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `productid` int(255) NOT NULL,
  `keyword` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14472 DEFAULT CHARSET=latin1;


create table if not exists `[prefix]shipping_rates` (
  `id` int(10) unsigned not null auto_increment,
  `product_id` int(10) unsigned not null,
  `shipping_method_id` int(10) unsigned not null,
  `shipping_rate` decimal(12,2) not null,
  `shipping_bundle_rate` decimal(12,2) not null,
  primary key(`id`)
) DEFAULT CHARSET=utf8;

create table if not exists `[prefix]shipping_rules` (
  `id` int(10) unsigned not null auto_increment,
  `min_amount` decimal(12,2),
  `shipping_method_id` int(10) unsigned not null,
  `shipping_cost` decimal(12,2),
  primary key(`id`)
) DEFAULT CHARSET=utf8;

create table if not exists `[prefix]tax_rates` (
  `id` int(10) unsigned not null auto_increment,
  `state` varchar(20) not null,
  `zip_low` mediumint unsigned not null default 0,
  `zip_high` mediumint unsigned not null default 0,
  `rate` decimal(8,3) not null,
  `tax_shipping` tinyint(1) not null default 0,
  primary key(`id`)
) DEFAULT CHARSET=utf8;

create table if not exists `[prefix]cart_settings` (
  `key` varchar(50) not null,
  `value` text not null,
  primary key(`key`)
) DEFAULT CHARSET=utf8;

create table if not exists `[prefix]orders` (
  `id` int(10) unsigned not null auto_increment,
  `bill_first_name` varchar(50) not null,
  `bill_last_name` varchar(50) not null,
  `bill_address` varchar(150) not null,
  `bill_address2` varchar(150) not null,
  `bill_city` varchar(150) not null,
  `bill_state` varchar(50) not null,
  `bill_country` varchar(50) not null default '',
  `bill_zip` varchar(150) not null,
  `ship_first_name` varchar(50) not null,
  `ship_last_name` varchar(50) not null,
  `ship_address` varchar(150) not null,
  `ship_address2` varchar(150) not null,
  `ship_city` varchar(150) not null,
  `ship_state` varchar(50) not null,
  `ship_country` varchar(50) not null default '',
  `ship_zip` varchar(150) not null,
  `phone` varchar(15) not null,
  `email` varchar(100) not null,
  `coupon` varchar(50) null,
  `discount_amount` decimal(12,2) not null,
  `trans_id` varchar(25) not null,
  `authorization` varchar(50) not null,
  `shipping` decimal(12,2) not null,
  `subtotal` decimal(12,2) not null,
  `tax` decimal(8,3) not null,
  `total` decimal(12,2) not null,
  `non_subscription_total` decimal(12,2) not null,
  `ordered_on` datetime,
  `status` varchar(50) not null,
  `ip` varchar(50) not null,
  `ouid` varchar(100) not null,
  `shipping_method` varchar(50),
  `account_id` int(10) unsigned not null default 0,
  `viewed` tinyint(1) not null default '0',
  primary key(`id`)
) DEFAULT CHARSET=utf8;

create table if not exists `[prefix]order_items` (
  `id` int(10) unsigned not null auto_increment,
  `order_id` int(10) unsigned not null,
  `product_id` int(10) unsigned not null,
  `item_number` varchar(50) not null,
  `product_price` decimal(12,2) not null,
  `description` text not null,
  `quantity` int(10) unsigned not null,
  `duid` varchar(100) null,
  `form_entry_ids` varchar(100) not null,
  primary key(`id`)
) DEFAULT CHARSET=utf8;

create table if not exists `[prefix]Sync` (
  `ikey` varchar(250) not null,
  `product_id` int(10) unsigned not null,
  `track` tinyint(1) unsigned not null default 0,
  `quantity` int(10) unsigned not null,
  primary key(`ikey`)
) DEFAULT CHARSET=utf8;

create table if not exists `[prefix]accounts` (
  `id` int(10) unsigned not null auto_increment,
  `first_name` varchar(100) not null,
  `last_name` varchar(100) not null,
  `email` varchar(100) not null,
  `username` varchar(50) not null,
  `password` varchar(50) not null,
  `notes` text not null,
  `created_at` datetime not null,
  `updated_at` datetime not null,
  primary key(`id`)
) DEFAULT CHARSET=utf8;

create table if not exists `[prefix]account_subscriptions` (
  `id` int(10) unsigned not null auto_increment,
  `account_id` int(10) unsigned not null,
  `billing_first_name` varchar(100),
  `billing_last_name` varchar(100),
  `feature_level` varchar(200) not null,
  `subscription_plan_name` varchar(255) not null,
  `paypal_billing_profile_id` varchar(50) not null,
  `status` varchar(20) not null default '',
  `active_until` datetime not null,
  `lifetime` tinyint(1) not null default 0,
  `subscriber_token` varchar(50) not null,
  `created_at` datetime not null,
  `updated_at` datetime not null,
  `grace_until` datetime not null,
  `ready_to_renew_since` datetime not null,
  `ready_to_renew` tinyint(1) not null default 0,
  `card_expires_before_next_auto_renew` tinyint(1) not null default 0,
  `recurring` tinyint(1) not null default 0,
  `active` tinyint(1) not null default 0,
  `billing_interval` varchar(50) not null,
  primary key(`id`)
) DEFAULT CHARSET=utf8;

create table if not exists `[prefix]membership_reminders` (
  `id` int(10) unsigned not null auto_increment,
  `enable` int(10) unsigned not null,
  `subscription_plan_id` int(10) unsigned not null,
  `interval` int(10) unsigned not null,
  `interval_unit` varchar(50) not null,
  `from_name` varchar(100),
  `from_email` varchar(100),
  `copy_to` varchar(255),
  `subject` varchar(100),
  `reminder_send_html_emails` int(10) unsigned not null,
  `reminder_html_email` longtext not null,
  `reminder_plain_email` longtext not null,
  primary key(`id`)
) DEFAULT CHARSET=utf8;

create table if not exists `[prefix]pp_recurring_payments` (
  `id` int(10) unsigned not null auto_increment,
  `account_id` int(10) unsigned not null,
  `recurring_payment_id` varchar(50) not null,
  `mc_gross` decimal(12,2) not null default 0,
  `txn_id` varchar(50) not null,
  `product_name` varchar(255) not null,
  `first_name` varchar(100) not null,
  `last_name` varchar(100) not null,
  `payer_email` varchar(255) not null,
  `ipn` text not null,
  `next_payment_date` varchar(100) not null,
  `time_created` datetime not null,
  `created_at` datetime not null, 
  primary key(`id`)
) DEFAULT CHARSET=utf8;

create table if not exists`[prefix]sessions` (
  `id` int(10) unsigned not null auto_increment,
  `session_id` varchar(50) not null,
  `ip_address` varchar(55) default '0' not null,
  `user_agent` varchar(255) not null,
  `last_activity` datetime not null,
  `user_data` longtext default '' not null,
  unique key `sid` (`session_id`),
  primary key (`id`)
) DEFAULT CHARSET=utf8;

create table if not exists `[prefix]email_log` (
  `id` int(10) unsigned not null auto_increment,
  `send_date` datetime,
  `from_email` varchar(100) not null,
  `from_name` varchar(100) not null,
  `to_email` varchar(100) not null,
  `to_name` varchar(100) not null,
  `headers` varchar(255) not null,
  `subject` varchar(255) not null,
  `body` longtext not null,
  `attachments` varchar(100) not null,
  `order_id` int(10) unsigned,
  `email_type` varchar(100) not null,
  `copy` varchar(100) not null,
  `status` varchar(100) not null,
  primary key(`id`)
) DEFAULT CHARSET=utf8;

create table if not exists `[prefix]order_fulfillment` (
  `id` int(10) unsigned not null auto_increment,
  `name` varchar(100) null,
  `email` varchar(100) not null,
  `products` varchar(255) not null,
  primary key(`id`)
) DEFAULT CHARSET=utf8;

--  Upgrading to Shoprocket 1.0.1

alter table `[prefix]accounts` add column `notes` text not null;

--  Upgrading to Shoprocket 1.0.3

alter table `[prefix]products` add column `start_recurring_number` int(10) unsigned not null default 1;
alter table `[prefix]products` add column `start_recurring_unit` varchar(50) not null;
alter table `[prefix]products` add column `price_description` varchar(255) not null;

-- Upgrading to Shoprocket 1.0.6

alter table `[prefix]order_items` modify `description` text;

-- Upgrading to Shoprocket 1.0.8

alter table `[prefix]products` add column `is_membership_product` tinyint(1) not null default 0;
alter table `[prefix]products` add column `lifetime_membership` tinyint(1) not null default 0;
alter table `[prefix]products` add column `s3_bucket` varchar(200) not null;
alter table `[prefix]products` add column `s3_file` varchar(200) not null;
alter table `[prefix]account_subscriptions` add column `lifetime` tinyint(1) not null default 0;

-- Upgrading to Shoprocket 1.2.0

alter table `[prefix]products` add column `min_quantity` int(10) unsigned not null default 0;
alter table `[prefix]products` add column `is_user_price` tinyint(1) not null default 0;
alter table `[prefix]products` add column `min_price` decimal(12,2) not null default 0;
alter table `[prefix]products` add column `max_price` decimal(12,2) not null default 0;

-- Upgrading to Shoprocket 1.3
 
alter table `[prefix]promotions` add column `name` varchar(64) not null;
alter table `[prefix]promotions` add column `enable` tinyint(1) not null default 1;
alter table `[prefix]promotions` add column `apply_to` enum('products','shipping','total') not null default 'total';
alter table `[prefix]promotions` add column `auto_apply` tinyint(3) not null default 0;
alter table `[prefix]promotions` add column `maximum_redemptions` int(11) not null default 0;
alter table `[prefix]promotions` add column `max_uses_per_order` int(11) not null default 0;
alter table `[prefix]promotions` add column `min_quantity` int(11) default NULL;
alter table `[prefix]promotions` add column `max_quantity` int(11) default NULL;
alter table `[prefix]promotions` add column `redemptions` int(11) not null default 0;
alter table `[prefix]promotions` add column `effective_from` datetime default null;
alter table `[prefix]promotions` add column `effective_to` datetime default null;
alter table `[prefix]promotions` add column `products` varchar(255) not null;
alter table `[prefix]promotions` add column `stackable` tinyint(1) not null default 0;

#add viewed column with all current orders getting a 1
alter table `[prefix]orders` add column `viewed` tinyint(1) not null default '1';
#set the default back to 0
alter table `[prefix]orders` modify `viewed` tinyint(1) not null default '0';

#make coupon codes text for multiple code support
alter table `[prefix]promotions` modify `code` text;
#update ip address to support IPV6
alter table `[prefix]sessions` modify `ip_address` varchar(55) default '0' not null;

-- Upgrading to Shoprocket 1.3.1
alter table `[prefix]sessions` modify `user_data` longtext default '' not null;

-- Upgrading to Shoprocket 1.3.4
alter table `[prefix]downloads` add column `order_item_id` int(10) unsigned not null;

-- Upgrading to Shoprocket 1.3.7
#update prices
alter table `[prefix]products` modify `price` decimal(12,2) not null;
alter table `[prefix]products` modify `setup_fee` decimal(12,2) not null;
alter table `[prefix]products` modify `trial_price` decimal(12,2) not null;
alter table `[prefix]products` modify `min_price` decimal(12,2) not null;
alter table `[prefix]products` modify `max_price` decimal(12,2) not null;
alter table `[prefix]order_items` modify `product_price` decimal(12,2) not null;
alter table `[prefix]orders` modify `discount_amount` decimal(12,2) not null;
alter table `[prefix]orders` modify `shipping` decimal(12,2) not null;
alter table `[prefix]orders` modify `subtotal` decimal(12,2) not null;
alter table `[prefix]orders` modify `tax` decimal(12,3) not null;
alter table `[prefix]orders` modify `total` decimal(12,2) not null;
alter table `[prefix]orders` modify `non_subscription_total` decimal(12,2) not null;
alter table `[prefix]pp_recurring_payments` modify `mc_gross` decimal(12,2) not null;
alter table `[prefix]promotions` modify `amount` decimal(12,2) not null;
alter table `[prefix]promotions` modify `min_order` decimal(12,2) not null;
alter table `[prefix]shipping_methods` modify `default_rate` decimal(12,2) not null;
alter table `[prefix]shipping_methods` modify `default_bundle_rate` decimal(12,2) not null;
alter table `[prefix]shipping_rates` modify `shipping_rate` decimal(12,2) not null;
alter table `[prefix]shipping_rates` modify `shipping_bundle_rate` decimal(12,2) not null;
alter table `[prefix]shipping_rules` modify `min_amount` decimal(12,2) not null;
alter table `[prefix]shipping_rules` modify `shipping_cost` decimal(12,2) not null;
alter table `[prefix]tax_rates` modify `rate` decimal(12,3) not null;

-- Upgrading to Shoprocket 1.3.4
alter table `[prefix]promotions` modify `products` longtext not null;

-- Upgrading to Shoprocket 1.5.0
alter table `[prefix]orders` add column `tracking_number` varchar(255);
alter table `[prefix]orders` add column `notes` text not null;
alter table `[prefix]orders` add column `authorization` varchar(50) not null;
alter table `[prefix]products` add column `gravity_form_pricing` tinyint(1) unsigned not null default 0;
alter table `[prefix]products` modify `gravity_form_qty_id` varchar(10) not null default '0';
alter table `[prefix]orders` add column `authorization` varchar(50) not null;
alter table `[prefix]promotions` add column `max_order` decimal(12,2) not null;
alter table `[prefix]promotions` add column `exclude_from_products` tinyint(3) not null default 0;
alter table `[prefix]accounts` add column `opt_out` tinyint(1) not null default 0;
alter table `[prefix]account_subscriptions` add column `product_id` int(10) unsigned not null;
alter table `[prefix]membership_reminders` modify `interval` varchar(255);
alter table `[prefix]email_log` modify `headers` text not null;

-- Upgrading to Shoprocket 1.5.0.4
alter table `[prefix]products` add column `custom_required` tinyint(1) unsigned not null default 0;

-- Upgrading to Shoprocket 1.5.0.5
alter table `[prefix]promotions` modify `apply_to` enum('products','shipping','subtotal','total') not null default 'total';
alter table `[prefix]shipping_methods` add column `countries` longtext default '' not null;
alter table `[prefix]orders` add column `custom_field` text default '' not null;

-- Upgrading to Shoprocket 1.5.1.4
alter table `[prefix]orders` add column `additional_fields` longtext default '' not null;