<p align="center">
<img width="150" height="150" src="https://github.com/CupidonSauce173/PigraidNotifications/blob/main/PigNotifIcon.png" />
</p>
<h1 align="center"> PigNotify </h1>
<p align="center">Join my discord: https://discord.gg/2QAPHbqrny </p>
<p align="center">This is a notification system designed for the now deleted Pigraid Network. </p>

#### Important Note

- For the users using the version 3.2.0, know that we switched from player username to player uuid. This means that you
  might have to reset your database. Using uuid is much more secure than player username. Thank you for your
  understanding.

| **Feature**                 | **State** | 
| --------------------------- |:----------:|
| MultiThreaded System        | ✔️ |
| Notification Object         | ✔️ |
| Simple API                  | ✔️ |
| Translation System          | ✔️ |
| Command Customization       | ✔️ |
| Automated MySQL Constructor | ✔️ |

### Prerequisites

- Working MySQL Server.
- PocketMine-MP 4.+

### Introduction

This is a notification system working with MySQL and is multi-threaded. The plugin will fetch notifications from the
MySQL server, look if they have been displayed and display the notifications to the users. The plugin contains a simple
API if you want to create third-party addons. This is a part of the Pigraid Network System.

### Notification Object

The notifications have few properties that you can play with. Here's the list:

| **Property** | **DataType** | **Description** |
| ------------ | :---------- | :------------- |
| $id          | Int          | Id of the notification. |
| $displayed   | Boolean      | If the notification has been displayed as message to the player. |
| $langKey     | String       | The message index from langKeys.ini |
| $varKeys     | Array        | Array of variables to change in the langKey. |
| $event       | String       | The event of the notification, like FRIEND_REQUEST |
| $player      | Player       | Target player (PMMP). |

You can get and set the properties of the notification whenever you want.

```php
# Note:
# NEVER use any ->set{property} AFTER creating a notification. This could lead to weird behavior. Only ->setDisplayed(true);.

# Get / Set id.
$notification->setId($id);
$notification->getId(); # Returns Int.

# Get / Set Player.
$notification->setPlayer($player);
$notification->getPlayer(); # Returns string/Uuid

# Get / Set Displayed.
$notification->setDisplayed(true|false);
$notification->hasBeenDisplayed(); # Returns Boolean.

# Get / Set langKey.
$notification->setLangKey($messageIndex);
$notification->getLangKey(); # Returns String.

# Get / Set varKeys.
$notification->setVarKeys(['target|CupidonSauce173','faction|Foo']);
$notification->getVarKeys(); # Returns Array.

# Get / Set Event.
$notification->setEvent($event);
$notification->getEvent($event); # Returns String.
```

### Config File

The configuration file allows you to modify pretty much any aspect of the plugin. You can set the command you want, and
it's aliases, the permission and if the players needs the permission to use the command. You can also set the delays
between checks from the database & if a notification has been displayed. Here's the list of settings you're allowed to
change from the config file.

```yml

# MySQL's information.
MySQL:
  database: notifications
  host: mysql_host
  username: mysql_username
  password: user_password
  port: 3306

# At how every x times the plugin will check for new notifications for the players (in seconds).
check-database-task: 2
# At how every x times the plugin will check if the notification has been displayed (if not, displays it to the player) (in seconds).
check-displayed-task: 2
# Prefix for the messages.
prefix: "Notify > "

# Set to false if you don't want to look if the user has the permission to use this command.
use-permission: false
# Permission to use this command.
permission: pig.notification.command
# Message if the player doesn't have the permission.
no-permission-message: You do not have the necessary permissions!
# Main command, /notification for example.
command-main: notification
# Aliases to use this command, /notif or /n for example.
command-aliases:
  - notif
  - n
```

### API

The plugin offers a small and simple API that you can use along the notifications methods. Here are all the methods from
the API.

First, you need to register the API.

```php

public PigNotify $api;

public function onEnable(){
   $this->api = $this->getServer()->getPluginManager()->getPlugin('PigraidNotifications');
}
```

Then, you can use the API like you wish.

```php

# List of methods in the API.

# Will create a new notification.
$api->createNotification($uuid, $langKey, $event, $varKeys);
$api->createNotification($uuid, $langKey, $event);
# Will return a list of notification objects.
$api->getPlayerNotifications($xuid);
# Will delete one notification, must pass a notification object.
$api->deleteNotification($notification);
# Will delete a list of notifications, must pass an array of notification objects.
$api->deleteNotifications($notifications);
# Will return a string of readable text from a messageIndex with / without langKeys.
$api->getText($messageIndex, $langKeys);
$api->getText($messageIndex); #If the messageIndex doesn't require any changes (ex: %sender%)
# Will translate a notification using the getText method to a readable message. 
$api->translateNotification($notification);
$api->translateNotification($notification, false); #If you don't want the prefix.

# Examples of how to use the API.

public function onDeath(PlayerDeathEvent $event){
   $player = $event->getPlayer();
   $notifCount = count($this->api->getPlayerNotifications($player));
   $player->sendMessage("Don't forget, you have $notifCount notifications!");
}

public function onFriendRequestCreate(CustomEvent $event){
   $sender = $event->getPlayer()->getName(); #Sender
   $receiver = $event->getReceiver()->getName(); #Target
   $receiverUuid = $event->getReceiver()->getUniqueId()->toString(); #Target Uuid
   
   $this->api->createNotification(
     $receiverUuid, #Target Uuid
     'friend.request.send', #Index from the langKeys.ini
     'friendRequestCreation',  #Notification event.
     ["target|$receiver", "sender|$player"]); #Variables from the index (friend.request.send).
}
```

### How it works?

First, when the server first boosts, it will check if it can establish a MySQL connection, if it can't, it will close
the server. Otherwise, it will create (if the structure doesn't exist) the database and the table / columns. Then, a
repeatingTask will be started to check at every x amount of seconds all the notifications related to the OnlinePlayers.
It will exclude all already existing notifications in the server. Another repeatingTask is also ran to loop through all
the notifications in the server and see if they have been displayed. If not, it will get the notification to a readable
message (Translation System) and send a message to the target player and finally set the notification as "displayed".
When the player disconnects from the server, all the notifications that are related to that player will be destroyed
with the deleteNotification($notification) method.

### How to create a notification

To create a new notification, you will need to call the $api->createNotification(); method. It won't directly show to
the player that they received a notification, it will just create a new one in the database and will be waiting to get
created in the server.

Basically:

```
server -> API (createNotification) -> Database <- Checknotifications Task -> server -> notificationCheckTask -> API (translateNotification) -> player.
```

#### langKeys

In order to create notifications, you need to add keys to the langKeys.ini file. There is already an example in the file
but here's another one.

You want to create notifications for your plugins, this is how you would do it :

```ini

; start plugin section (place your indexes here)

; shop plugin
shop.item.bought = %buyer% bought your item!
shop.sale.ended = Your sale for the item : %item% has ended!
shop.banned = You have been banned from the market during %time% for %reason%!
shop.unbanned = You have been unbanned from the market!
shop.new.bid = %player% placed a bid on your item!
shop.auction.ended = Your auction for the "%item%" item ended!

; party plugin
party.invitation.received = You received an invitation to join %owner%'s party!
party.disband = Your party has been removed!

; friends plugin
friend.request.received = You received a friend request from %sender%.
friend.request.declined = %receiver% declined your friend request.
friend.request.accepted = %receiver% accepted your friend request!
friend.gift.received = You received a gift! %gift% from %friend%!
friend.purchase.made = You bought a %object%, you can send it to a friend!

; end plugin section

; plugin text (do not delete any index) 

; utils text
form.close.button = §lClose
message.no.notif = You have no notification!
message.no.perm = You do not have the necessary permissions!
message.command.description = Command to see all your notifications.
form.warn.notif = §rThis notification will be removed when you leave this page.
; title text
form.title = Notifications
; main form text
form.content.main = Notification system. You can see or delete all your notifications here.
form.notification.button = §lNotifications
form.notifications.button = §lNotifications [%count%]
form.clearAll.button = §lClear all
; notification list ui
form.content.list = Your notification list. Click on one for more information.
```

### Notes

- You don't need to handle the PlayerQuitEvent to destruct the notifications.
- You don't need to handle the join event to look if there are notifications for the player.
- it is **NOT** recommended setting a check value smaller than 2 seconds. This could lead to performances issues.
- Running your MySQL server in the same machine as your server is the best idea.
- **NEVER** set values in the notifications after they have been created, this could lead to weird behavior.
