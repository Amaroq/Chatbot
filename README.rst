===========
Chatbot 2.0
===========

A Chatbot written in PHP for my Chat based on WoltLab Community Framework.

This Chatbot provides functions, that are not included in the Chat-Core. It 
accesses the Chat by HTTP-Requests. The Bot is modular and easy to extend.

Features
========

* Full PHP 5-OOP
* Modular
* On-The-Fly loading, unloading and reloading modules
* High Performance 

Usage
=====

First start
-----------

Before the first start you have to prepare the bot a bit. You have to tell it
which login-data it should use by creating a config-directory in the directory
where the chatbot.php is. In the config-directory create a file namend 
userdata.php with the following contents::

    <?php
	define('SERVER', 'http://your-server.com/forum/index.php'); // Path to the index.php of the board
	define('ID', 2); // UserID of the bot-user
	define('NAME', 'Chatbot'); // Username of the bot-user
	define('HASH', '7421afc131519d342cd7ab097acfb20ef0143693'); // the login cookie of the bot
	define('PREFIX', 'wcf_'); // The cookie-prefix of the board
	define('API_KEY', '1234'); // The API-Key, if you have one, otherwise remove this line
    ?>
	
After that you can run the bot on the Commandline-Interpreter (CLI) of PHP by
the following command::
    
	./observe.sh

	
The bot will create needed directories and forks itself afterwards. From that
time there will always be two processes. After that you will have to grant
yourself Operator privilegies (OP). You can do that by typing the following command
into the CLI-STDIN::
    
	!level YourUsername 500
	
The bot will now parse the message internally and grants you OP. From now on you can 
configure it fully via special commands.


Best regards,
		Tim
