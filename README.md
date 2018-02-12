About the LINE Messaging API
===================

See the official API documentation for more information.
> **English** : https://developers.line.me/en/docs/

--
	Documents
-----

###Create Line Bot
> https://nuuneoi.com/blog/blog.php?read_id=882 

---------

###Line-Bot-SDK-php

###Installation
- Install the LINE Messaging API SDK using Composer.
>$ composer require linecorp/line-bot-sdk

###Getting started
- Create the bot client instance
- The bot client instance is a handler of the Messaging API.

``` 
$httpClient = new \LINE\LINEBot\HTTPClient\
CurlHTTPClient('< channel access token >');

$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => '< channel secret >']);
```
- The constructor of the bot client requires an instance of HTTPClient. This library provides CurlHTTPClient by default.

###Call API
You can call an API through the bot client instance.

- A very simple example:
```
$response = $bot->replyText('< reply token >', 'hello!');
```

- This procedure sends a message to the destination that is associated with < reply token >.

- A more advanced example:

```
$textMessageBuilder = new LINE\LINEBot\MessageBuilder
\TextMessageBuilder('hello');

$response = $bot->replyMessage('<reply token>'
,$textMessageBuilder);
if ($response->isSucceeded()) {
    echo 'Succeeded!';
    return;
}
```

// Failed
```
echo $response->getHTTPStatus().' '.$response-getRawBody();
```
LINEBot #replyMessage() takes the reply token and MessageBuilder. 

The method sends a message that is built by MessageBuilder to the destination.


#Push Code to Heroku

###Install the Heroku CLI
>https://devcenter.heroku.com/articles/heroku-cli

If you haven't already, log in to your Heroku account and follow the prompts to create a new SSH public key.

>$ heroku login

Clone the repository
Use Git to clone Folder Project's source code to your local machine.

>\$ heroku git:clone -a Folder Project
>$ cd Folder Project

Deploy your changes

Make some changes to the code you just cloned and deploy them to Heroku using Git.

>\$ git add .
>\$ git commit -am "make it better"
>$ git push heroku master