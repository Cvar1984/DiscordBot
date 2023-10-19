<?php

include __DIR__ . '/vendor/autoload.php';

use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\WebSockets\Intents;
use Discord\WebSockets\Event;
use Discord\Builders\CommandBuilder;
use Discord\Builders\MessageBuilder;
use Discord\Parts\Interactions\Interaction;
use Discord\Parts\Interactions\Command\Option;
use Cvar1984\DiscordBot\Quran;

$env = parse_ini_file('.env');
$botToken = $env['BOT_TOKEN'];

$discord = new Discord([
    'token' => $botToken,
    'intents' => Intents::getDefaultIntents()
    //      | Intents::MESSAGE_CONTENT, // Note: MESSAGE_CONTENT is privileged, see https://dis.gd/mcfaq
]);

$discord->on('ready', function (Discord $discord) {
    /**
     * Make Application command with parameter
     * */
    $command = $discord->application->commands->create(
        CommandBuilder::new()
            ->setName('list_verse_by_chapter')
            ->setDescription('List all verse by chapter')
            ->addOption(
                (new Option($discord))
                    ->setName('chapter')
                    ->setDescription('Chapter')
                    ->setType(Option::NUMBER)
                    ->setRequired(true)
            )
            ->toArray()
    );

    //$discord->application->commands->save($command);

    /**
     * Listen Command
     */
    $discord->listenCommand('debug', function (Interaction $interaction) {
        $user = $interaction->data->resolved->users->first();
        $userDebug = $interaction->data->resolved->users->toArray();
        $userDebug = json_encode((array) $userDebug, JSON_PRETTY_PRINT);
        $interaction->respondWithMessage(MessageBuilder::new()->setContent("Pong! {$user} ```{$userDebug}```"));

    });

    $discord->listenCommand('list_verse_by_chapter', function (Interaction $interaction) {
        //var_dump($interaction->data);
        $chapter = $interaction->data->options['chapter']['value'];
        $arrayResult = Quran::listVerseByChapter($chapter);
        $terjemah = '';

        foreach($arrayResult as $result) {
            $terjemah .= $result['id'] . PHP_EOL;
        }

        $interaction->respondWithMessage(MessageBuilder::new()->setContent("{$terjemah}"));
    });

    /**
     * Delete Commands
     */
    $discord->application->commands->freshen()->then(function ($commands) use ($discord) {
        foreach ($commands as $command) {
            if ($command->name == 'debug') {
                //$discord->application->commands->delete($command);
            }
            echo $command, PHP_EOL;
        }
    });


    // Listen for messages.
    $discord->on(Event::MESSAGE_CREATE, function (Message $message, Discord $discord) {
        if ($message->author->bot) {
            return;
        }

        // $replyMessage = match ($message->content) {
        //     'ping', 'test' => 'pong',
        //     default => false,
        // };

        // if ($replyMessage) {
        //     $message->reply($replyMessage);
        // }

        echo "{$message->author->username}: {$message->content}", PHP_EOL;
        // Note: MESSAGE_CONTENT intent must be enabled to get the content if the bot is not mentioned/DMed.
    });
});

$discord->run();