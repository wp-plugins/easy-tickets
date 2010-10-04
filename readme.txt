=== Easy Tickets ===
Contributors: paxmanpwnz
Donate link: 
Tags: simple, support, ticket, ticketing, system
Requires at least: 3.0
Tested up to: 3.01
Stable tag: trunk

Plugin that enables simple support ticket system.

== Description ==

ET allows your users to submit their support tickets for a product/problem/etc via modified native comment form, to be displayed on a predefined
 blog page. The fields that can be posted are: ticket summay and ticket description. Administrator can then
 approve ticket and set its type (Bug, Enhancement, Feature request, Patch), Status (Open, Assigned, Won't fix, Closed)
 and Priority (Low, Medium, High). This plugin uses custom post type functionality, for providing clean and simple 
 interface for editing, even by Quick Edit. For displaying submitted data, a new page with a custom page template must be created;
 an example is enclosed. For deterring spam, all data that are submitted by users who are not administrators,
  get verified by Akismet and reCaptcha, if present and activated. Should you wan't to use your own submit form, 
  a sample how to do so is provided.

== Installation ==

1. Upload zipped folder to the `/wp-content/plugins/` directory
2. Activate the plugin (Easy Tickets) through the 'Plugins' menu in WordPress
3. Create new page template, namig it `easy-tickets-page.php` and adding the `/*
Template Name: Tickets
*/` header, then fill it with loop data as shown in the enclosed `easy-tickets-page-example.php` file.
Create a Wordpress Page, and set 'Tickets' its template.  
4. If your theme does not use native comment form, insert in the said created page template
the example form from the provided `easy-tickets-form-example.php` file. 

== Screenshots ==

1. Ticket post type view with quick edit settings
2. Ticket edit interface with options and comment meta boxes
3. Example display of the tickets front end page, with submit form
4. Display of the ticket data and its comments

== Changelog ==

= 1.0 =
* Initial version
