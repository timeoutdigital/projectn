CREATE TABLE event (id BIGINT AUTO_INCREMENT, review_date datetime, vendor_event_id VARCHAR(10) NOT NULL, name VARCHAR(255) NOT NULL, short_description TEXT, description TEXT, booking_url TEXT, url TEXT, price TEXT, rating FLOAT(18, 2), vendor_id BIGINT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX vendor_event_id_index_idx (vendor_event_id), INDEX vendor_id_idx (vendor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = INNODB;
CREATE TABLE event_category (id BIGINT AUTO_INCREMENT, name VARCHAR(50) NOT NULL, INDEX name_index_idx (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = INNODB;
CREATE TABLE event_media (id BIGINT AUTO_INCREMENT, ident VARCHAR(32) NOT NULL UNIQUE, url TEXT NOT NULL, mime_type VARCHAR(255) NOT NULL, file_last_modified VARCHAR(255), etag VARCHAR(255), content_length BIGINT, event_id BIGINT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX ident_index_idx (ident), INDEX event_id_idx (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = INNODB;
CREATE TABLE event_occurrence (id BIGINT AUTO_INCREMENT, vendor_event_occurrence_id VARCHAR(50) NOT NULL, booking_url TEXT, start_date DATE NOT NULL, start_time TIME, end_date DATE, end_time TIME, utc_offset VARCHAR(9) NOT NULL, event_id BIGINT NOT NULL, poi_id BIGINT NOT NULL, INDEX vendor_event_occurrence_id_index_idx (vendor_event_occurrence_id), INDEX event_id_idx (event_id), INDEX poi_id_idx (poi_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = INNODB;
CREATE TABLE event_property (id BIGINT AUTO_INCREMENT, lookup VARCHAR(50) NOT NULL, value TEXT NOT NULL, event_id BIGINT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX lookup_index_idx (lookup), INDEX event_id_idx (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = INNODB;
CREATE TABLE export_logger (id BIGINT AUTO_INCREMENT, vendor_id BIGINT NOT NULL, model ENUM('movie', 'poi', 'event') NOT NULL, total_time TIME NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX model_index_idx (model), INDEX vendor_id_idx (vendor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = INNODB;
CREATE TABLE export_logger_item (id BIGINT AUTO_INCREMENT, export_logger_id BIGINT NOT NULL, item_id BIGINT NOT NULL, vendor_item_id BIGINT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX item_id_index_idx (item_id), INDEX vendor_item_id_index_idx (vendor_item_id), INDEX export_logger_id_idx (export_logger_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = INNODB;
CREATE TABLE import_logger (id BIGINT AUTO_INCREMENT, total_inserts BIGINT NOT NULL, total_updates BIGINT NOT NULL, total_errors BIGINT NOT NULL, total_existing BIGINT NOT NULL, vendor_id BIGINT NOT NULL, type ENUM('movie', 'poi', 'event') NOT NULL, total_time TIME NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX vendor_id_idx (vendor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = INNODB;
CREATE TABLE import_logger_change (id BIGINT AUTO_INCREMENT, type ENUM('update', 'delete') NOT NULL, log TEXT NOT NULL, import_logger_id BIGINT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX import_logger_id_idx (import_logger_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = INNODB;
CREATE TABLE import_logger_error (id BIGINT AUTO_INCREMENT, trace TEXT NOT NULL, log TEXT NOT NULL, type TEXT NOT NULL, message TEXT NOT NULL, serialized_object TEXT, import_logger_id BIGINT NOT NULL, resolved TINYINT(1) DEFAULT '0' NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX import_logger_id_idx (import_logger_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = INNODB;
CREATE TABLE linking_event_category (id BIGINT AUTO_INCREMENT, event_category_id BIGINT NOT NULL, event_id BIGINT NOT NULL, INDEX event_id_idx (event_id), INDEX event_category_id_idx (event_category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = INNODB;
CREATE TABLE linking_event_category_mapping (id BIGINT AUTO_INCREMENT, event_category_id BIGINT NOT NULL, vendor_event_category_id BIGINT NOT NULL, INDEX vendor_event_category_id_idx (vendor_event_category_id), INDEX event_category_id_idx (event_category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = INNODB;
CREATE TABLE linking_movie_genre (id BIGINT AUTO_INCREMENT, movie_genre_id BIGINT NOT NULL, movie_id BIGINT NOT NULL, INDEX movie_genre_id_idx (movie_genre_id), INDEX movie_id_idx (movie_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = INNODB;
CREATE TABLE linking_poi_category (id BIGINT AUTO_INCREMENT, poi_category_id BIGINT NOT NULL, poi_id BIGINT NOT NULL, INDEX poi_category_id_idx (poi_category_id), INDEX poi_id_idx (poi_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = INNODB;
CREATE TABLE linking_poi_category_mapping (id BIGINT AUTO_INCREMENT, poi_category_id BIGINT NOT NULL, vendor_poi_category_id BIGINT NOT NULL, INDEX poi_category_id_idx (poi_category_id), INDEX vendor_poi_category_id_idx (vendor_poi_category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = INNODB;
CREATE TABLE linking_vendor_event_category (id BIGINT AUTO_INCREMENT, vendor_event_category_id BIGINT NOT NULL, event_id BIGINT NOT NULL, INDEX vendor_event_category_id_idx (vendor_event_category_id), INDEX event_id_idx (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = INNODB;
CREATE TABLE linking_vendor_poi_category (id BIGINT AUTO_INCREMENT, vendor_poi_category_id BIGINT NOT NULL, poi_id BIGINT NOT NULL, INDEX vendor_poi_category_id_idx (vendor_poi_category_id), INDEX poi_id_idx (poi_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = INNODB;
CREATE TABLE media (id BIGINT AUTO_INCREMENT, ident VARCHAR(32) NOT NULL UNIQUE, url TEXT NOT NULL, mime_type VARCHAR(255) NOT NULL, file_last_modified VARCHAR(255), etag VARCHAR(255), content_length BIGINT, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX ident_index_idx (ident), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = INNODB;
CREATE TABLE movie (id BIGINT AUTO_INCREMENT, vendor_id BIGINT NOT NULL, vendor_movie_id VARCHAR(25) NOT NULL, name VARCHAR(255) NOT NULL, plot TEXT, tag_line TEXT, review TEXT, url TEXT, director VARCHAR(255), writer VARCHAR(255), cast VARCHAR(255), age_rating VARCHAR(50), release_date VARCHAR(50), duration VARCHAR(50), country VARCHAR(50), language VARCHAR(50), aspect_ratio VARCHAR(50), sound_mix VARCHAR(50), company VARCHAR(50), rating DECIMAL(18, 1), utf_offset VARCHAR(9) NOT NULL, poi_id BIGINT, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX vendor_movie_id_index_idx (vendor_movie_id), INDEX vendor_id_idx (vendor_id), INDEX poi_id_idx (poi_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = INNODB;
CREATE TABLE movie_genre (id BIGINT AUTO_INCREMENT, genre VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX genre_index_idx (genre), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = INNODB;
CREATE TABLE movie_media (id BIGINT AUTO_INCREMENT, ident VARCHAR(32) NOT NULL UNIQUE, url TEXT NOT NULL, mime_type VARCHAR(255) NOT NULL, file_last_modified VARCHAR(255), etag VARCHAR(255), content_length BIGINT, movie_id BIGINT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX ident_index_idx (ident), INDEX movie_id_idx (movie_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = INNODB;
CREATE TABLE movie_property (id BIGINT AUTO_INCREMENT, lookup VARCHAR(50) NOT NULL, value TEXT NOT NULL, movie_id BIGINT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX lookup_index_idx (lookup), INDEX movie_id_idx (movie_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = INNODB;
CREATE TABLE poi (id BIGINT AUTO_INCREMENT, vendor_poi_id VARCHAR(25) NOT NULL, review_date datetime, local_language VARCHAR(10), poi_name VARCHAR(80), house_no VARCHAR(32), street VARCHAR(128) NOT NULL, city VARCHAR(32) NOT NULL, district VARCHAR(128), country VARCHAR(3) NOT NULL, additional_address_details TEXT, zips VARCHAR(16), longitude DECIMAL(12, 10), latitude DECIMAL(12, 10), email VARCHAR(128), url TEXT, phone VARCHAR(32), phone2 VARCHAR(32), fax VARCHAR(32), vendor_category VARCHAR(128), keywords TEXT, short_description TEXT, description TEXT, public_transport_links TEXT, price_information TEXT, openingtimes TEXT, star_rating TINYINT, rating TINYINT, provider TEXT, vendor_id BIGINT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX vendor_poi_id_index_idx (vendor_poi_id), INDEX vendor_id_idx (vendor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = INNODB;
CREATE TABLE poi_category (id BIGINT AUTO_INCREMENT, name VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX name_index_idx (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = INNODB;
CREATE TABLE poi_category_reference (parent_id BIGINT, child_id BIGINT, PRIMARY KEY(parent_id, child_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = INNODB;
CREATE TABLE poi_media (id BIGINT AUTO_INCREMENT, ident VARCHAR(32) NOT NULL UNIQUE, url TEXT NOT NULL, mime_type VARCHAR(255) NOT NULL, file_last_modified VARCHAR(255), etag VARCHAR(255), content_length BIGINT, poi_id BIGINT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX ident_index_idx (ident), INDEX poi_id_idx (poi_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = INNODB;
CREATE TABLE poi_property (id BIGINT AUTO_INCREMENT, lookup VARCHAR(50) NOT NULL, value TEXT NOT NULL, poi_id BIGINT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX lookup_index_idx (lookup), INDEX poi_id_idx (poi_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = INNODB;
CREATE TABLE user (id BIGINT AUTO_INCREMENT, vendor_id BIGINT NOT NULL, user_name VARCHAR(32) NOT NULL, user_reputation TINYINT, user_infomation TEXT NOT NULL, comments_relevance FLOAT(18, 2), specialty VARCHAR(128), created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX vendor_id_idx (vendor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = INNODB;
CREATE TABLE user_content (id BIGINT AUTO_INCREMENT, vendor_ucid VARCHAR(32) NOT NULL, comment_subject TEXT NOT NULL, comment_body TEXT NOT NULL, user_rating FLOAT(18, 2), user_id BIGINT NOT NULL, poi_id BIGINT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX user_id_idx (user_id), INDEX poi_id_idx (poi_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = INNODB;
CREATE TABLE vendor (id BIGINT AUTO_INCREMENT, city VARCHAR(15) NOT NULL, language VARCHAR(10) NOT NULL, time_zone VARCHAR(50) NOT NULL, inernational_dial_code VARCHAR(5), airport_code VARCHAR(3) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX city_index_idx (city), INDEX city_language_index_idx (city, language), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = INNODB;
CREATE TABLE vendor_event_category (id BIGINT AUTO_INCREMENT, name VARCHAR(255) NOT NULL, vendor_id BIGINT NOT NULL, INDEX name_index_idx (name), INDEX vendor_id_idx (vendor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = INNODB;
CREATE TABLE vendor_poi_category (id BIGINT AUTO_INCREMENT, name VARCHAR(255) NOT NULL, vendor_id BIGINT NOT NULL, INDEX name_index_idx (name), INDEX vendor_id_idx (vendor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = INNODB;
ALTER TABLE event ADD CONSTRAINT event_vendor_id_vendor_id FOREIGN KEY (vendor_id) REFERENCES vendor(id);
ALTER TABLE event_media ADD CONSTRAINT event_media_event_id_event_id FOREIGN KEY (event_id) REFERENCES event(id);
ALTER TABLE event_occurrence ADD CONSTRAINT event_occurrence_poi_id_poi_id FOREIGN KEY (poi_id) REFERENCES poi(id);
ALTER TABLE event_occurrence ADD CONSTRAINT event_occurrence_event_id_event_id FOREIGN KEY (event_id) REFERENCES event(id);
ALTER TABLE event_property ADD CONSTRAINT event_property_event_id_event_id FOREIGN KEY (event_id) REFERENCES event(id);
ALTER TABLE export_logger ADD CONSTRAINT export_logger_vendor_id_vendor_id FOREIGN KEY (vendor_id) REFERENCES vendor(id);
ALTER TABLE export_logger_item ADD CONSTRAINT export_logger_item_export_logger_id_export_logger_id FOREIGN KEY (export_logger_id) REFERENCES export_logger(id);
ALTER TABLE import_logger ADD CONSTRAINT import_logger_vendor_id_vendor_id FOREIGN KEY (vendor_id) REFERENCES vendor(id);
ALTER TABLE import_logger_change ADD CONSTRAINT import_logger_change_import_logger_id_import_logger_id FOREIGN KEY (import_logger_id) REFERENCES import_logger(id);
ALTER TABLE import_logger_error ADD CONSTRAINT import_logger_error_import_logger_id_import_logger_id FOREIGN KEY (import_logger_id) REFERENCES import_logger(id);
ALTER TABLE linking_event_category ADD CONSTRAINT linking_event_category_event_id_event_id FOREIGN KEY (event_id) REFERENCES event(id);
ALTER TABLE linking_event_category ADD CONSTRAINT linking_event_category_event_category_id_event_category_id FOREIGN KEY (event_category_id) REFERENCES event_category(id);
ALTER TABLE linking_event_category_mapping ADD CONSTRAINT lvvi FOREIGN KEY (vendor_event_category_id) REFERENCES vendor_event_category(id);
ALTER TABLE linking_event_category_mapping ADD CONSTRAINT leei FOREIGN KEY (event_category_id) REFERENCES event_category(id);
ALTER TABLE linking_movie_genre ADD CONSTRAINT linking_movie_genre_movie_id_movie_id FOREIGN KEY (movie_id) REFERENCES movie(id);
ALTER TABLE linking_movie_genre ADD CONSTRAINT linking_movie_genre_movie_genre_id_movie_genre_id FOREIGN KEY (movie_genre_id) REFERENCES movie_genre(id);
ALTER TABLE linking_poi_category ADD CONSTRAINT linking_poi_category_poi_id_poi_id FOREIGN KEY (poi_id) REFERENCES poi(id);
ALTER TABLE linking_poi_category ADD CONSTRAINT linking_poi_category_poi_category_id_poi_category_id FOREIGN KEY (poi_category_id) REFERENCES poi_category(id);
ALTER TABLE linking_poi_category_mapping ADD CONSTRAINT lvvi_1 FOREIGN KEY (vendor_poi_category_id) REFERENCES vendor_poi_category(id);
ALTER TABLE linking_poi_category_mapping ADD CONSTRAINT linking_poi_category_mapping_poi_category_id_poi_category_id FOREIGN KEY (poi_category_id) REFERENCES poi_category(id);
ALTER TABLE linking_vendor_event_category ADD CONSTRAINT lvvi_2 FOREIGN KEY (vendor_event_category_id) REFERENCES vendor_event_category(id);
ALTER TABLE linking_vendor_event_category ADD CONSTRAINT linking_vendor_event_category_event_id_event_id FOREIGN KEY (event_id) REFERENCES event(id);
ALTER TABLE linking_vendor_poi_category ADD CONSTRAINT lvvi_3 FOREIGN KEY (vendor_poi_category_id) REFERENCES vendor_poi_category(id);
ALTER TABLE linking_vendor_poi_category ADD CONSTRAINT linking_vendor_poi_category_poi_id_poi_id FOREIGN KEY (poi_id) REFERENCES poi(id);
ALTER TABLE movie ADD CONSTRAINT movie_vendor_id_vendor_id FOREIGN KEY (vendor_id) REFERENCES vendor(id);
ALTER TABLE movie ADD CONSTRAINT movie_poi_id_poi_id FOREIGN KEY (poi_id) REFERENCES poi(id);
ALTER TABLE movie_media ADD CONSTRAINT movie_media_movie_id_movie_id FOREIGN KEY (movie_id) REFERENCES movie(id);
ALTER TABLE movie_property ADD CONSTRAINT movie_property_movie_id_movie_id FOREIGN KEY (movie_id) REFERENCES movie(id);
ALTER TABLE poi ADD CONSTRAINT poi_vendor_id_vendor_id FOREIGN KEY (vendor_id) REFERENCES vendor(id);
ALTER TABLE poi_category_reference ADD CONSTRAINT poi_category_reference_child_id_poi_category_id FOREIGN KEY (child_id) REFERENCES poi_category(id);
ALTER TABLE poi_media ADD CONSTRAINT poi_media_poi_id_poi_id FOREIGN KEY (poi_id) REFERENCES poi(id);
ALTER TABLE poi_property ADD CONSTRAINT poi_property_poi_id_poi_id FOREIGN KEY (poi_id) REFERENCES poi(id);
ALTER TABLE user ADD CONSTRAINT user_vendor_id_vendor_id FOREIGN KEY (vendor_id) REFERENCES vendor(id);
ALTER TABLE user_content ADD CONSTRAINT user_content_user_id_user_id FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE;
ALTER TABLE user_content ADD CONSTRAINT user_content_poi_id_poi_id FOREIGN KEY (poi_id) REFERENCES poi(id) ON DELETE CASCADE;
ALTER TABLE vendor_event_category ADD CONSTRAINT vendor_event_category_vendor_id_vendor_id FOREIGN KEY (vendor_id) REFERENCES vendor(id);
ALTER TABLE vendor_poi_category ADD CONSTRAINT vendor_poi_category_vendor_id_vendor_id FOREIGN KEY (vendor_id) REFERENCES vendor(id);
CREATE TABLE category (id INT UNSIGNED AUTO_INCREMENT, parent_category_id INT UNSIGNED NOT NULL, name VARCHAR(255) NOT NULL, status TINYINT UNSIGNED DEFAULT '0' NOT NULL, name_url VARCHAR(255) DEFAULT '' NOT NULL, tagline TEXT, last_listing_date DATE, lft INT, rgt INT, level SMALLINT, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE = INNODB;
CREATE TABLE event (id INT UNSIGNED AUTO_INCREMENT, master_category_id INT UNSIGNED NOT NULL, default_venue_id INT UNSIGNED, title TEXT NOT NULL, title_sort TEXT NOT NULL, free TINYINT DEFAULT '0', image_id INT UNSIGNED, status INT UNSIGNED, recommended TINYINT UNSIGNED DEFAULT '0' NOT NULL, distinct_occurrences TINYINT DEFAULT '0' NOT NULL, travel TEXT NOT NULL, venue_prefix TEXT NOT NULL, search_priority INT UNSIGNED, source VARCHAR(15) DEFAULT '0', source_id INT UNSIGNED NOT NULL, source_event_id INT NOT NULL, date_start DATE NOT NULL, type TINYINT UNSIGNED DEFAULT '0' NOT NULL, title_url VARCHAR(255) DEFAULT '' NOT NULL, suitable_for_kids TINYINT UNSIGNED, seo_synopsis TEXT, annotation TEXT, phone TEXT, url TEXT, price TEXT, price_cheapest DECIMAL(10, 2), discount TINYINT, keywords TEXT, tags TEXT, date_end DATE, opening_times TEXT, booking_ahead TINYINT, rescheduled TINYINT, extra TINYINT, cancelled TINYINT, flickr_tag VARCHAR(255), advanced_text TEXT, date_created DATE, date_modified DATE, source_field VARCHAR(25), INDEX master_category_id_idx (master_category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE = INNODB;
CREATE TABLE event_category_mapping (category_id INT UNSIGNED, event_id INT UNSIGNED, annotation_behavior TINYINT UNSIGNED DEFAULT '1', annotation TEXT, master_category_id INT, PRIMARY KEY(category_id, event_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE = INNODB;
CREATE TABLE event_image (item_id INT UNSIGNED AUTO_INCREMENT, item_type VARCHAR(255), image_id INT UNSIGNED, sort_index INT UNSIGNED, PRIMARY KEY(item_id, item_type, image_id, sort_index)) DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE = INNODB;
CREATE TABLE occurrence (id INT UNSIGNED AUTO_INCREMENT, event_id INT UNSIGNED NOT NULL, venue_id INT UNSIGNED NOT NULL, date_start DATE NOT NULL, time_start TIME NOT NULL, date_end DATE NOT NULL, time_end TIME NOT NULL, annotation_behaviour TINYINT UNSIGNED, new TINYINT NOT NULL, last_chance TINYINT NOT NULL, recommended TINYINT UNSIGNED NOT NULL, source VARCHAR(15) NOT NULL, source_id INT UNSIGNED NOT NULL, search_grouping_id INT UNSIGNED DEFAULT '0' NOT NULL, seo_synopsis VARCHAR(255), title TEXT, annotation TEXT, price TEXT, notable_title VARCHAR(30), image_id INT, page_views BIGINT, flickr_tag VARCHAR(255), advanced_text TEXT, date_created DATE, date_modified DATE, INDEX event_id_idx (event_id), INDEX venue_id_idx (venue_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE = INNODB;
CREATE TABLE venue (id INT UNSIGNED AUTO_INCREMENT, neighbourhood_id INT UNSIGNED NOT NULL, name VARCHAR(255), address TEXT, postcode VARCHAR(20), latitude DECIMAL(9, 2) NOT NULL, longitude DECIMAL(9, 2) NOT NULL, status INT UNSIGNED NOT NULL, source_id INT UNSIGNED, event_count INT DEFAULT '0' NOT NULL, alt_name VARCHAR(255), building_name VARCHAR(255), travel TEXT, opening_times VARCHAR(255), url VARCHAR(255), phone VARCHAR(255), email VARCHAR(255), image_id INT, source VARCHAR(15), annotation TEXT, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE = INNODB;
CREATE TABLE venue_category_information (category_id INT UNSIGNED, venue_id INT UNSIGNED, annotation TEXT, price_export TEXT, telephone_export TEXT, times_export TEXT, url_export VARCHAR(255), food_served TINYINT, free_venue TINYINT, late_night TINYINT, PRIMARY KEY(category_id, venue_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE = INNODB;
ALTER TABLE event ADD CONSTRAINT event_master_category_id_category_id FOREIGN KEY (master_category_id) REFERENCES category(id);
ALTER TABLE event_category_mapping ADD CONSTRAINT event_category_mapping_event_id_event_id FOREIGN KEY (event_id) REFERENCES event(id);
ALTER TABLE event_category_mapping ADD CONSTRAINT event_category_mapping_category_id_category_id FOREIGN KEY (category_id) REFERENCES category(id);
ALTER TABLE occurrence ADD CONSTRAINT occurrence_venue_id_venue_id FOREIGN KEY (venue_id) REFERENCES venue(id);
ALTER TABLE occurrence ADD CONSTRAINT occurrence_event_id_event_id FOREIGN KEY (event_id) REFERENCES event(id);
ALTER TABLE venue_category_information ADD CONSTRAINT venue_category_information_venue_id_venue_id FOREIGN KEY (venue_id) REFERENCES venue(id);
ALTER TABLE venue_category_information ADD CONSTRAINT venue_category_information_category_id_category_id FOREIGN KEY (category_id) REFERENCES category(id);
