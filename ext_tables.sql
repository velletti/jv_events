#
# Table structure for table 'tx_jvevents_domain_model_event'
#
CREATE TABLE tx_jvevents_domain_model_event (

	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
  master_id int(11) unsigned DEFAULT '0' NOT NULL,

	event_type int(11) DEFAULT '0' NOT NULL,
	name varchar(255) DEFAULT '' NOT NULL,
	slug varchar(2048),
	teaser varchar(255) DEFAULT '' NOT NULL,
	description text NOT NULL,
	price double default '0' NOT NULL ,
	price_reduced double default '0' NOT NULL ,
	currency varchar(20) default '€' NOT NULL ,
	price_reduced_text varchar(255) DEFAULT '' NOT NULL,
	images int(11) unsigned NOT NULL default '0',
	files int(11) unsigned NOT NULL default '0',
	files_after_reg int(11) unsigned NOT NULL default '0',
	files_after_event int(11) unsigned NOT NULL default '0',
	teaser_image int(11) unsigned NOT NULL default '0',
	all_day tinyint(1) unsigned DEFAULT '0' NOT NULL,
	top_event tinyint(1) unsigned DEFAULT '0' NOT NULL,
	canceled tinyint(1) unsigned DEFAULT '0' NOT NULL,
	start_date int(11) DEFAULT '0' NOT NULL,
	start_time int(11) DEFAULT '0' NOT NULL,
	entry_time int(11) DEFAULT '0' NOT NULL,
	end_date int(11) DEFAULT '0' NOT NULL,
	end_time int(11) DEFAULT '0' NOT NULL,
	subevent int(11) DEFAULT '0' NOT NULL,

	url text,
	access text NOT NULL,

	with_registration tinyint(1) unsigned DEFAULT '0' NOT NULL,
	registration_pid int(11)  DEFAULT '0' NOT NULL,
	registration_form_pid int(11)  DEFAULT '0' NOT NULL,
	registration_url text,
	registration_until int(11) DEFAULT '0' NOT NULL,
	registration_access text NULL DEFAULT NULL,
	registration_gender int(11) DEFAULT '0' NOT NULL,
	registration_show_status tinyint(1) unsigned DEFAULT '0' NOT NULL,

    store_in_hubspot tinyint(1) unsigned DEFAULT '0' NOT NULL,
	sales_force_campaign_id varchar(60) DEFAULT '' NOT NULL,

	store_in_citrix tinyint(1) unsigned DEFAULT '0' NOT NULL,
	citrix_uid varchar(255) DEFAULT '' NOT NULL,
	store_in_sales_force tinyint(1) unsigned DEFAULT '0' NOT NULL,

	marketing_process_id varchar(255) DEFAULT '' NOT NULL,
	sales_force_record_type varchar(255) DEFAULT '' NOT NULL,
	sales_force_event_id varchar(255) DEFAULT '' NOT NULL,
	sales_force_session_id varchar(255) DEFAULT '' NOT NULL,
	available_seats int(11) DEFAULT '0' NOT NULL,
	available_waiting_seats int(11) DEFAULT '0' NOT NULL,
	registered_seats int(11) DEFAULT '0' NOT NULL,
	unconfirmed_seats int(11) DEFAULT '0' NOT NULL,
	notify_organizer tinyint(1) unsigned DEFAULT '0' NOT NULL,
	notify_registrant tinyint(1) unsigned DEFAULT '0' NOT NULL,
	subject_organizer varchar(255) DEFAULT '' NOT NULL,
	text_organizer text NULL DEFAULT NULL,
	subject_registrant varchar(255) DEFAULT '' NOT NULL,
	introtext_registrant text NULL DEFAULT NULL,
	introtext_registrant_confirmed text NULL DEFAULT NULL,
	text_registrant text NULL DEFAULT NULL,
	need_to_confirm tinyint(1) unsigned DEFAULT '0' NOT NULL,
	is_recurring tinyint(1) unsigned DEFAULT '0' NOT NULL,
	frequency int(11) DEFAULT '0' NOT NULL,
	freq_exception int(11) DEFAULT '0' NOT NULL,
	is_exception_for int(11) DEFAULT '0' NOT NULL,

	organizer int(11) unsigned DEFAULT '0',
	location int(11) unsigned DEFAULT '0',

	registrant int(11) unsigned DEFAULT '0' NOT NULL,

	event_category int(11) unsigned DEFAULT '0' NOT NULL,
	tags int(11) unsigned DEFAULT '0' NOT NULL,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,

	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	viewed int(11) unsigned DEFAULT '0' NOT NULL,
	starttime int(11) unsigned DEFAULT '0' NOT NULL,
	endtime int(11) unsigned DEFAULT '0' NOT NULL,



	t3ver_oid int(11) DEFAULT '0' NOT NULL,
	t3ver_id int(11) DEFAULT '0' NOT NULL,
	t3ver_wsid int(11) DEFAULT '0' NOT NULL,
	t3ver_label varchar(255) DEFAULT '' NOT NULL,
	t3ver_state tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_stage int(11) DEFAULT '0' NOT NULL,
	t3ver_count int(11) DEFAULT '0' NOT NULL,
	t3ver_tstamp int(11) DEFAULT '0' NOT NULL,
	t3ver_move_id int(11) DEFAULT '0' NOT NULL,
	sorting int(11) DEFAULT '0' NOT NULL,
	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumblob,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY master (master_id),
	 KEY slug (slug(250)),
	KEY t3ver_oid (t3ver_oid,t3ver_wsid),
	KEY index (start_date, start_time, sorting),
  KEY language (l10n_parent,sys_language_uid)

);

#
# Table structure for table 'tx_jvevents_domain_model_subevent'
#
CREATE TABLE tx_jvevents_domain_model_subevent (

	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	event int(11) DEFAULT '0' NOT NULL,
	table_name varchar(255) DEFAULT 'tx_jvevents_domain_model_event' NOT NULL,

	all_day tinyint(1) unsigned DEFAULT '0' NOT NULL,
	start_date int(11) DEFAULT '0' NOT NULL,
	start_time int(11) DEFAULT '0' NOT NULL,
	starttime int(11) DEFAULT '0' NOT NULL,
	end_time int(11) DEFAULT '0' NOT NULL,
	endtime int(11) DEFAULT '0' NOT NULL,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,

	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,

	t3ver_oid int(11) DEFAULT '0' NOT NULL,
	t3ver_id int(11) DEFAULT '0' NOT NULL,
	t3ver_wsid int(11) DEFAULT '0' NOT NULL,
	t3ver_label varchar(255) DEFAULT '' NOT NULL,
	t3ver_state tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_stage int(11) DEFAULT '0' NOT NULL,
	t3ver_count int(11) DEFAULT '0' NOT NULL,
	t3ver_tstamp int(11) DEFAULT '0' NOT NULL,
	t3ver_move_id int(11) DEFAULT '0' NOT NULL,
	sorting int(11) DEFAULT '0' NOT NULL,

	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumblob,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY event (event),
	KEY t3ver_oid (t3ver_oid,t3ver_wsid),
 KEY language (l10n_parent,sys_language_uid)

);


#
# Table structure for table 'tx_jvevents_domain_model_organizer'
#
CREATE TABLE tx_jvevents_domain_model_organizer (

	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	name varchar(255) DEFAULT '' NOT NULL,
	slug varchar(2048),
	email varchar(255) DEFAULT '' NOT NULL,
	email_cc varchar(255) DEFAULT '' NOT NULL,
	link varchar(255) DEFAULT '' NOT NULL,

	charity_link varchar(255) DEFAULT '' NOT NULL,
	youtube_link varchar(255) DEFAULT '' NOT NULL,

	phone varchar(255) DEFAULT '' NOT NULL,
	sales_force_user_id varchar(255) DEFAULT '' NOT NULL,
	sales_force_user_id2 varchar(255) DEFAULT '' NOT NULL,
	sales_force_user_org varchar(3) DEFAULT '' NOT NULL,
	images int(11) unsigned NOT NULL default '0',
	teaser_image int(11) unsigned NOT NULL default '0',
	description text NOT NULL,
	registration_info text NOT NULL,

	access_groups text NOT NULL,
	access_users text NOT NULL,
	organizer_category int(11) unsigned DEFAULT '0' NOT NULL,
	tags int(11) unsigned DEFAULT '0' NOT NULL,
	latest_event int(11) DEFAULT '0' NOT NULL,
	lng varchar(255) DEFAULT '' NOT NULL,
	lat varchar(255) DEFAULT '' NOT NULL,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	starttime int(11) unsigned DEFAULT '0' NOT NULL,
	endtime int(11) unsigned DEFAULT '0' NOT NULL,

    module_sys_dmail_html tinyint(3) unsigned DEFAULT '1' NOT NULL,

	t3ver_oid int(11) DEFAULT '0' NOT NULL,
	t3ver_id int(11) DEFAULT '0' NOT NULL,
	t3ver_wsid int(11) DEFAULT '0' NOT NULL,
	t3ver_label varchar(255) DEFAULT '' NOT NULL,
	t3ver_state tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_stage int(11) DEFAULT '0' NOT NULL,
	t3ver_count int(11) DEFAULT '0' NOT NULL,
	t3ver_tstamp int(11) DEFAULT '0' NOT NULL,
	t3ver_move_id int(11) DEFAULT '0' NOT NULL,
	sorting int(11) DEFAULT '0' NOT NULL,

	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumblob,

	PRIMARY KEY (uid),
	KEY parent (pid),
	 KEY slug (slug(250)),
	KEY t3ver_oid (t3ver_oid,t3ver_wsid),
 KEY language (l10n_parent,sys_language_uid)

);

#
# Table structure for table 'tx_jvevents_domain_model_location'
#
CREATE TABLE tx_jvevents_domain_model_location (

	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	name varchar(255) DEFAULT '' NOT NULL,
	slug varchar(2048),
	teaser_image int(11) unsigned NOT NULL default '0',
	street_and_nr varchar(255) DEFAULT '' NOT NULL,
	zip varchar(255) DEFAULT '' NOT NULL,
	city varchar(255) DEFAULT '' NOT NULL,
	country varchar(255) DEFAULT '' NOT NULL,
	lng double NOT NULL DEFAULT '0',
	lat double NOT NULL DEFAULT '0',
	link varchar(255) DEFAULT '' NOT NULL,
	email varchar(255) DEFAULT '' NOT NULL,
	phone varchar(255) DEFAULT '' NOT NULL,
	description text NOT NULL,
	organizer int(11) unsigned DEFAULT '0',
	location_category int(11) unsigned DEFAULT '0' NOT NULL,
  latest_event int(11) DEFAULT '0' NOT NULL,
  default_location SMALLINT(5) unsigned DEFAULT '0' NOT NULL,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	starttime int(11) unsigned DEFAULT '0' NOT NULL,
	endtime int(11) unsigned DEFAULT '0' NOT NULL,

	t3ver_oid int(11) DEFAULT '0' NOT NULL,
	t3ver_id int(11) DEFAULT '0' NOT NULL,
	t3ver_wsid int(11) DEFAULT '0' NOT NULL,
	t3ver_label varchar(255) DEFAULT '' NOT NULL,
	t3ver_state tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_stage int(11) DEFAULT '0' NOT NULL,
	t3ver_count int(11) DEFAULT '0' NOT NULL,
	t3ver_tstamp int(11) DEFAULT '0' NOT NULL,
	t3ver_move_id int(11) DEFAULT '0' NOT NULL,
	sorting int(11) DEFAULT '0' NOT NULL,

	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumblob,

	PRIMARY KEY (uid),
	KEY parent (pid),
	 KEY slug (slug(250)),
	KEY t3ver_oid (t3ver_oid,t3ver_wsid),
 KEY language (l10n_parent,sys_language_uid)

);

#
# Table structure for table 'tx_jvevents_domain_model_registrant'
#
CREATE TABLE tx_jvevents_domain_model_registrant (

	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	event int(11) unsigned DEFAULT '0' NOT NULL,
  other_events varchar(255) DEFAULT '' NOT NULL,

	fingerprint varchar(64) DEFAULT '' NOT NULL,
	title varchar(100) DEFAULT '' NOT NULL,
	first_name varchar(100) DEFAULT '' NOT NULL,
	last_name varchar(100) DEFAULT '' NOT NULL,
	name varchar(200) DEFAULT '' NOT NULL,
	email varchar(255) DEFAULT '' NOT NULL,
	gender varchar(10) DEFAULT '' NOT NULL,
	company varchar(100) DEFAULT '' NOT NULL,
	department varchar(100) DEFAULT '' NOT NULL,
	street_and_nr varchar(100) DEFAULT '' NOT NULL,
	zip varchar(10) DEFAULT '' NOT NULL,
	city varchar(100) DEFAULT '' NOT NULL,
	country varchar(3) DEFAULT '' NOT NULL,

	company2 varchar(100) DEFAULT '' NOT NULL,
	department2 varchar(100) DEFAULT '' NOT NULL,
	street_and_nr2 varchar(100) DEFAULT '' NOT NULL,
	zip2 varchar(10) DEFAULT '' NOT NULL,
	city2 varchar(100) DEFAULT '' NOT NULL,
	country2 varchar(3) DEFAULT '' NOT NULL,

	language varchar(3) DEFAULT '' NOT NULL,
	phone varchar(100) DEFAULT '' NOT NULL,
	additional_info text NOT NULL,
	privacy tinyint(1) unsigned DEFAULT '0' NOT NULL,
	newsletter tinyint(1) unsigned DEFAULT '0' NOT NULL,
	customer_id varchar(32) DEFAULT '' NOT NULL,
	profession varchar(100) DEFAULT '' NOT NULL,
	recall tinyint(1) unsigned DEFAULT '0' NOT NULL,
	contact_id varchar(255) DEFAULT '' NOT NULL,
	username varchar(255) DEFAULT '' NOT NULL,
	more1 text NOT NULL,
	more2 text NOT NULL,
	more3 text NOT NULL,
	more4 text NOT NULL,
	more5bool tinyint(1) unsigned DEFAULT '0' NOT NULL,
	more6int int(11) DEFAULT '0' NOT NULL,
	more7date int(11) DEFAULT '0' NOT NULL,
	more8file int(11) unsigned NOT NULL default '0',
	password varchar(32) DEFAULT '' NOT NULL,
  citrix_response varchar(255) DEFAULT '' NOT NULL,
  hubspot_response varchar(255) DEFAULT '' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	confirmed tinyint(4) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,

    module_sys_dmail_html tinyint(3) unsigned DEFAULT '1' NOT NULL,

	t3ver_oid int(11) DEFAULT '0' NOT NULL,
	t3ver_id int(11) DEFAULT '0' NOT NULL,
	t3ver_wsid int(11) DEFAULT '0' NOT NULL,
	t3ver_label varchar(255) DEFAULT '' NOT NULL,
	t3ver_state tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_stage int(11) DEFAULT '0' NOT NULL,
	t3ver_count int(11) DEFAULT '0' NOT NULL,
	t3ver_tstamp int(11) DEFAULT '0' NOT NULL,
	t3ver_move_id int(11) DEFAULT '0' NOT NULL,

	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumblob,
    starttime int(11) unsigned DEFAULT '0' NOT NULL,
	endtime int(11) unsigned DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY t3ver_oid (t3ver_oid,t3ver_wsid),
	KEY event (event),
	KEY cruser_id (cruser_id),
	KEY fingerprint (fingerprint),
 KEY language (l10n_parent,sys_language_uid)

);

#
# Table structure for table 'tx_jvevents_domain_model_category'
#
CREATE TABLE tx_jvevents_domain_model_category (

	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	title varchar(255) DEFAULT '' NOT NULL,
	description text NOT NULL,
	slug varchar(2048),
	type int(11) DEFAULT '0' NOT NULL,
	block_registration tinyint(1) unsigned DEFAULT '0' NOT NULL,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,

	t3ver_oid int(11) DEFAULT '0' NOT NULL,
	t3ver_id int(11) DEFAULT '0' NOT NULL,
	t3ver_wsid int(11) DEFAULT '0' NOT NULL,
	t3ver_label varchar(255) DEFAULT '' NOT NULL,
	t3ver_state tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_stage int(11) DEFAULT '0' NOT NULL,
	t3ver_count int(11) DEFAULT '0' NOT NULL,
	t3ver_tstamp int(11) DEFAULT '0' NOT NULL,
	t3ver_move_id int(11) DEFAULT '0' NOT NULL,
	sorting int(11) DEFAULT '0' NOT NULL,

	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumblob,

	PRIMARY KEY (uid),
	KEY parent (pid),
	 KEY slug (slug(250)),
	KEY t3ver_oid (t3ver_oid,t3ver_wsid),
 KEY language (l10n_parent,sys_language_uid)

);

#
# Table structure for table 'tx_jvevents_domain_model_tag'
#
CREATE TABLE tx_jvevents_domain_model_tag (

	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	name varchar(255) DEFAULT '' NOT NULL,
	type int(11) DEFAULT '0' NOT NULL,
	tag_category int(11) unsigned DEFAULT '0' NOT NULL,
	nocopy tinyint(4) unsigned DEFAULT '0' NOT NULL,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	starttime int(11) unsigned DEFAULT '0' NOT NULL,
	endtime int(11) unsigned DEFAULT '0' NOT NULL,

	t3ver_oid int(11) DEFAULT '0' NOT NULL,
	t3ver_id int(11) DEFAULT '0' NOT NULL,
	t3ver_wsid int(11) DEFAULT '0' NOT NULL,
	t3ver_label varchar(255) DEFAULT '' NOT NULL,
	t3ver_state tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_stage int(11) DEFAULT '0' NOT NULL,
	t3ver_count int(11) DEFAULT '0' NOT NULL,
	t3ver_tstamp int(11) DEFAULT '0' NOT NULL,
	t3ver_move_id int(11) DEFAULT '0' NOT NULL,

	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumblob,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY t3ver_oid (t3ver_oid,t3ver_wsid),
 KEY language (l10n_parent,sys_language_uid)

);

#
# Table structure for table 'tx_jvevents_domain_model_registrant'
#
CREATE TABLE tx_jvevents_domain_model_registrant (

	event  int(11) unsigned DEFAULT '0' NOT NULL,

);

#
# Table structure for table 'tx_jvevents_event_category_mm'
#
CREATE TABLE tx_jvevents_event_category_mm (
	uid_local int(11) unsigned DEFAULT '0' NOT NULL,
	uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
	sorting int(11) unsigned DEFAULT '0' NOT NULL,
	sorting_foreign int(11) unsigned DEFAULT '0' NOT NULL,

	KEY uid_local (uid_local),
	KEY uid_foreign (uid_foreign)
);

#
# Table structure for table 'tx_jvevents_tag_category_mm'
#
CREATE TABLE tx_jvevents_tag_category_mm (
		 uid_local int(11) unsigned DEFAULT '0' NOT NULL,
		 uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
		 tablenames varchar(255) DEFAULT '' NOT NULL,
		 sorting int(11) unsigned DEFAULT '0' NOT NULL,
		 sorting_foreign int(11) unsigned DEFAULT '0' NOT NULL,

		 KEY uid_local (uid_local),
		 KEY uid_foreign (uid_foreign)
);

#
# Table structure for table 'tx_jvevents_event_tag_mm'
#
CREATE TABLE tx_jvevents_event_tag_mm (
	uid_local int(11) unsigned DEFAULT '0' NOT NULL,
	uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
	sorting int(11) unsigned DEFAULT '0' NOT NULL,
	sorting_foreign int(11) unsigned DEFAULT '0' NOT NULL,

	KEY uid_local (uid_local),
	KEY uid_foreign (uid_foreign)
);


#
# Table structure for table 'tx_jvevents_organizer_category_mm'
#
CREATE TABLE tx_jvevents_organizer_category_mm (
	uid_local int(11) unsigned DEFAULT '0' NOT NULL,
	uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
	sorting int(11) unsigned DEFAULT '0' NOT NULL,
	sorting_foreign int(11) unsigned DEFAULT '0' NOT NULL,

	KEY uid_local (uid_local),
	KEY uid_foreign (uid_foreign)
);

#
# Table structure for table 'tx_jvevents_organizer_tag_mm'
#
CREATE TABLE tx_jvevents_organizer_tag_mm (
	 uid_local int(11) unsigned DEFAULT '0' NOT NULL,
	 uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
	 sorting int(11) unsigned DEFAULT '0' NOT NULL,
	 sorting_foreign int(11) unsigned DEFAULT '0' NOT NULL,

	 KEY uid_local (uid_local),
	 KEY uid_foreign (uid_foreign)
);

#
# Table structure for table 'tx_jvevents_location_category_mm'
#
CREATE TABLE tx_jvevents_location_category_mm (
	uid_local int(11) unsigned DEFAULT '0' NOT NULL,
	uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
	sorting int(11) unsigned DEFAULT '0' NOT NULL,
	sorting_foreign int(11) unsigned DEFAULT '0' NOT NULL,

	KEY uid_local (uid_local),
	KEY uid_foreign (uid_foreign)
);

## EXTENSION BUILDER DEFAULTS END TOKEN - Everything BEFORE this line is overwritten with the defaults of the extension builder