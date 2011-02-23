#
# Table structure for table 'tx_ptgsavoucher_voucher'
# Id: $
#
CREATE TABLE tx_ptgsavoucher_voucher (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	code varchar(100) DEFAULT '' NOT NULL,
	pdfdoc_uid int(11) DEFAULT '0' NOT NULL,
    order_wrapper_uid int(11) DEFAULT '0' NOT NULL,
	gsa_uid int(11) DEFAULT '0' NOT NULL,
	related_doc_no varchar(100) DEFAULT '' NOT NULL,
	category_confinement varchar(100) DEFAULT '' NOT NULL,
	article_confinement varchar(100) DEFAULT '' NOT NULL,
	amount double(12,2) DEFAULT '0.00' NOT NULL,
	is_percent tinyint(3) DEFAULT '0' NOT NULL,
    once_per_order tinyint(3) DEFAULT '0' NOT NULL,
    is_encashed tinyint(3) DEFAULT '0' NOT NULL,
	encashed_amount double(12,2) DEFAULT '0.00' NOT NULL,
	expiry_date varchar(10) DEFAULT '' NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);

CREATE TABLE tx_ptgsavoucher_voucher_encash (
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) DEFAULT '0' NOT NULL,
    crdate int(11) DEFAULT '0' NOT NULL,
    cruser_id int(11) DEFAULT '0' NOT NULL,
    deleted tinyint(4) DEFAULT '0' NOT NULL,
    hidden tinyint(4) DEFAULT '0' NOT NULL,
    voucher_uid int(11) DEFAULT '0' NOT NULL,
    order_wrapper_uid int(11) DEFAULT '0' NOT NULL,
    gsa_uid int(11) DEFAULT '0' NOT NULL,
    discount_percent double(12,4) DEFAULT '0.0000' NOT NULL,
    related_doc_no varchar(100) DEFAULT '' NOT NULL,
    article_uid int(11) DEFAULT '0' NOT NULL,
    amount double(12,2) DEFAULT '0.00' NOT NULL,
    encashed_date varchar(10) DEFAULT '' NOT NULL,
    
    PRIMARY KEY (uid),
    KEY parent (pid)
);