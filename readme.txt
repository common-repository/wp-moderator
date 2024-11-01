=== Moderator Plugin ===
Contributors: aheadzen
Tags: Moderator,post Moderator, post content check, filter post content
Requires at least : 3.0.0
Tested up to: 4.0
Stable tag: 1.0.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The plugin is like post content checker. Plugin will moderate the front end added posts content mean post added by users not from wp-admin.


== Description ==

If your site have added facility to add posts from front end by adding any plugin or your customization code for public user to insert posts. 
You should added any keyword or more than one keywords. If you store the post data by using wordpress action hook "wp_insert_post" and while post content get any of the single keyword added as per settings, it will marked as moderator and the post status will be set for Pending Review.
So if any one try to add spams or unnecessary text with post content, it will moderated and site should not be listed unwanted posts with content to display.
Also working for buddypress older version plugin - forum topic add, moderator filter action added.
The pluign added extra facilit to control new user insert post per day controll. Post title and content length control by words count and characters count.
On moderation of any post email notificaiton is sent as per email id added in settings.
The Black list facility is also a good one which will directly delete post that is blacklisted.


== Installation ==
1. Unzip and upload plugin folder to your /wp-content/plugins/ directory  OR Go to wp-admin > plugins > Add new Plugin & Upload plugin zip.
2. Go to wp-admin > Plugins(left menu) > Activate the plugin
3. See the plugin option link with plugin description on plugin activation page or directly access from wp-admin > Settings(left menu) > Moderator

== Screenshots ==
1. Plugin Activation
2. Plugin Settings
3. Post Pending status as per moderator settings

== Configuration ==

1. Go to wp-admin > Settings(left menu) > VOTER, manage settings as per you want.
2. Default will be up & down voting system so you can change it to like/unlike voting
3. new database table will be added to manage voting data, make sure you should add it manually in case of user security permission. 

== Changelog ==

= 1.0.0 =
* Fresh Public Release.

= 1.0.1 =
* Moderate/update all posts by click a button on plugin settings page - ADDED

= 1.0.2 =
* If any moderation for topic post - Topic was moderated and removed - SOLVED
* Added new filter for Topics & Topic posts so if any topic content is wrong, only Topic will moderated
* And if any post content is wrong only post will be moderated.


= 1.0.3 =
* If any moderation for topic post - Topic was moderated and removed - SOLVED
add_filter( 'group_forum_topic_title_before_save', array('ModeratorPluginClass','group_forum_topic_blacklist') );
add_filter( 'group_forum_topic_text_before_save',  array('ModeratorPluginClass','group_forum_topic_blacklist') );

= 1.0.4 =
* Message Email send only after every time perions interval, not on every time while user post private message - See settings from wp-admin > plugin settigs page in "Email" section.
* Enable or disable options
* time interval selection option
* module selection options

= 1.0.5 =
* email content & subject black list keyword feature added.
