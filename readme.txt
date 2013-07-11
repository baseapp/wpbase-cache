=== WPbase Cache ===
Contributors: baseapp
Tags: cache,chaching,speed,performance,db cache,optimization,nginx,apc,varnish
Requires at least: 3.5
Tested up to: 3.5
Stable tag: trunk
Donate link:
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A wordpress plugin for managing various tasks including chaching, chache purging, updating components, providing instant state info etc.

== Description ==

Plugin is developed to compile all different plugins used for hosting wordpress on varnish + nginx + php-fpm + php-apc server stack. This plugin includes nginx-compatibility, db-cache-reloaded-fix for nginx and page cache. This plguin also support varnish cache management with given default.vcl 

== Installation ==

1. copy and paste contents of utils/varnish-default.vcl in your vcl file
2. copy and paste contents of utils/nginx-sample in your nginx vhosts file
3. restart both varnish and nginx
4. Put the plugin folder into [wordpress_dir]/wp-content/plugins/
5. Go into the WordPress admin interface and activate the plugin
6. Optional: go to the options page and configure the plugin

Before upgrade DEACTIVATE the plugin and then ACTIVATE and RECONFIGURE!

== Frequently Asked Questions ==

No FAQs avilable yet
    
== Screenshots ==

No screenshots are available.

== Changelog ==

= 0.0.1 =
First alpha version of plugin

== Upgrade Notice ==
No upgrades available yet
