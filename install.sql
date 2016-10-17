CREATE TABLE `oc_credit` (
  `credit_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `description` varchar(255) NOT NULL,
  `customer_transaction_id` int(11) NOT NULL,
  `amount` decimal(15,4) NOT NULL,
  `code` varchar(10) NOT NULL,
  PRIMARY KEY (`credit_id`)
);
ALTER TABLE oc_customer_transaction add credit_id int(11);
INSERT INTO oc_setting (oc_setting.code,oc_setting.key,oc_setting.value,oc_setting.serialized) VALUES ('config','config_credit_min',100,0);
INSERT INTO oc_setting (oc_setting.code,oc_setting.key,oc_setting.value,oc_setting.serialized) VALUES ('config','config_credit_max',1000000,0);
