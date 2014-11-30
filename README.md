League of Legends Replay Downloader CLI
===================================

This project provides you a simple command to easilly download a League of Legends replay game which still in process (ingame), like *lolking* or *op.gg* feature. Replays are stored in your server and can be watched at any time.  
All download files can be decoded to parse them.
It uses the library : https://github.com/EloGank/lol-replay-downloader

## Features

* A ready-to-use CLI
* **A ready-to-use Virtual Machine, using [Vagrant](https://www.vagrantup.com) (automatic installation)**
* **A built-in replay decoder for each files (chunks & keyframes)**
* **An asynchronous system, allow to download some replays at the same time and save the log into the replay folder**
* Download previous data if you start the download process after the start of the game
* Can wait for the start of the game if you start the download process too early
* Fully configurable


## Installation

You have two ways to install the repository. First, with a Virtual Machine, optional but recommended on Windows OS, and the second way, manually (but less than 2 minutes, I swear).

### Virtual Machine

If you want to install/try this project with a Virtual Machine and avoid the installation process if you haven't yet a PHP environment, [read the virtual machine documentation](./doc/installation_virtual_machine.md).
It will take you only one minute.

**If you want to install/try this project on a Windows system, I advice you to choose the Virtual Machine installation process. This project hasn't been tested on a Windows system.**

### Manually

Simply clone this project and run the `composer install` command.
If you don't know what is Composer, read the [dedicated documentation](./doc/installation_composer.md).

## Configuration

See all the available configurations [here](./config/config.yml.dist).

## How to use

This project provides you an unique command : `php console elogank:replay:download`
```
Usage:
 elogank:replay:download [--async] [--override] region game_id encryption_key

Arguments:
 region                The game region
 game_id               The game id
 encryption_key        The game encryption key

Options:
 --async               The replay download will be asynchronous
 --override            If exists, the replay folder will be override
 --help (-h)           Display this help message.
 --quiet (-q)          Do not output any message.
 --verbose (-v|vv|vvv) Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug.
 --version (-V)        Display this application version.
 --ansi                Force ANSI output.
 --no-ansi             Disable ANSI output.
 --no-interaction (-n) Do not ask any interactive question.
```

Example of output :

``` bash
Retrieve metas...                       OK
Validate game criterias...              OK
Retrieve last infos...                  OK
Download all previous chunks (28)...    OK
Download all previous keyframes (12)... OK

Download current game data :
Downloading chunk       29              OK
Downloading keyframe    13              OK
Downloading chunk       ...
```

## How to get the region, game id or encryption key ?

To get the region, is pretty easy : just look at the file `config/config.yml`, all region servers are listed. For example, if you play on the EUW server, just use the region `EUW1`.

### From an unofficial API

For the **game id** and the **encryption key**, it's a few harder. Indeed, the [official Riot API](https://developer.riotgames.com/) doesn't provide yet an API to retrieve this data.  
To get it, you have to use an unofficial API, like this : https://github.com/EloGank/lol-php-api, please see the route `game.retrieve_in_progress_spectator_game_info`. Note that using other route is not allowed, by the new Riot Terms of Use (see "Important notes" below).  

### From LoLNexus website

For testing purpose, you can simply go to spectating websites like [lolnexus](http://www.lolnexus.com), click on "Spectate" button on a game, and you'll have the region, game id & encryption key in the command line to launch the game, see the end of the line :

``` bash
"C:\Riot Games\League of Legends\RADS\solutions\lol_game_client_sln\releases\0.0.1.xx\deploy\League of Legends.exe" "8394" "LoLLauncher.exe" "" "spectator SERVER_ADDRESS ENCRYPTION_KEY GAME_ID REGION"
```

Example :

``` bash
"C:\Riot Games\League of Legends\RADS\solutions\lol_game_client_sln\releases\0.0.1.68\deploy\League of Legends.exe" "8394" "LoLLauncher.exe" "" "spectator 185.40.64.163:80 nwP+BEYqHgk4sElnU2uRogoxGPUw1dzE 1234567890 EUW1"
```

So, you can run the command :

``` bash
php console elogank:replay:download EUW1 1234567890 nwP+BEYqHgk4sElnU2uRogoxGPUw1dzE
```

### From LoLNexus parser

A LoLNexus PHP parser exists here : https://github.com/EloGank/lol-replay-downloader/blob/master/examples/utils/LoLNexusParser.php

Usage is simple : you juste have to select the region by calling `LoLNexusParser::parseRandom($regionId)` or `LoLNexusParser::parsePlayer($regionId, $playerName)` methods and it will bring you all parameters for running a command by calling `LoLNexusParser::getRegion()`, `LoLNexusParser::getGameId()` or `LoLNexusParser::getEncryptionKey()` methods.

Example is available here : https://github.com/EloGank/lol-replay-downloader/blob/master/examples/download-replay.php#L29-L45

## Important notes

According to the new Riot Terms of Use *(1st October 2014)*, using data from another source of their official API is **not** allowed. So using data by parsing decoded files is not allowed. This project provides a way to decode file only for teaching purpose.

**You can download a full game only if you start the download process before the ~8th ingame minute.** Otherwise, you won't have the start of the game.

## Reporting an issue or a feature request

Feel free to open an issue, fork this project or suggest an awesome new feature in the [issue tracker](https://github.com/EloGank/lol-replay-downloader-cli/issues).  

## Credit

See the list of [contributors](https://github.com/EloGank/lol-replay-downloader-cli/graphs/contributors).

## Licence

[MIT, more information](./LICENCE)

*This repository isn't endorsed by Riot Games and doesn't reflect the views or opinions of Riot Games or anyone officially involved in producing or managing League of Legends.  
League of Legends and Riot Games are trademarks or registered trademarks of Riot Games, Inc. League of Legends (c) Riot Games, Inc.*
