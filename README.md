# Fancy Mail
 This module is intended to help those seeking 
 a content moderation solution for content types. 
 Whenever a Node (aka Content Type) is added or changed,
 this module will notify the preconfigured email address of what was changed,
 and then allow the administrative user to 'Accept' or 'Delete' this revision of content.
  
  
 It will also automatically prevent any non-administrative user from changing the 
 default revision. Note: this excludes anonymous users due to issues with nodes not saving during cron runs, 
 so please make sure to turn off permissions for anonymous users on these content types. 
  