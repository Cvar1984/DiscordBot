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

function splitStringByCharacters($string, $maxCharacters = 2000) {
    $characterCounter = 0;
    $stringLength = strlen($string);
    $currentString = '';
    
    for ($x = 0; $x < $stringLength; $x++) {
      $currentString .= $string[$x];
      $characterCounter++;
  
      if ($characterCounter >= $maxCharacters) {
        $splitStrings[] = $currentString; // store the string
        $characterCounter = 0; // reset flags
        $currentString = ''; // reset string
      }
    }
  
    if (strlen($currentString) > 0) {
      $splitStrings[] = $currentString;
    }
    return $splitStrings;
  }

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
    $command = $discord->application->commands->create(
        CommandBuilder::new()
            ->setName('list_chapter')
            ->setDescription('List all chapter')
            ->toArray()
    );

    //$discord->application->commands->save($command);

    /**
     * Listen Command
     */

    $discord->listenCommand('list_verse_by_chapter', function (Interaction $interaction) {
        //var_dump($interaction->data);
        $chapter = $interaction->data->options['chapter']['value'];
        $arrayResult = Quran::listVerseByChapter($chapter);
        $terjemah = '';

        foreach($arrayResult as $result) {
            $terjemah .= "{$result['id']}\n\n";
        }

        $terjemahSplit = splitStringByCharacters($terjemah);
        $interaction->respondWithMessage(MessageBuilder::new()->setContent($terjemahSplit[0]));
        $sizeOfTerjemahSplit = sizeof($terjemahSplit);

        for($x = 1; $x < $sizeOfTerjemahSplit; $x++) {
            $interaction->sendFollowUpMessage(MessageBuilder::new()->setContent($terjemahSplit[$x]));
        }
        /**
        $chapterList = Quran::listChapter();
        $chapterAudio = $chapterList[($chapter - 1)]['audio'];
        $interaction->sendFollowUpMessage(MessageBuilder::new()->addFileFromContent("Chapter{$chapter}.mp3", file_get_contents($chapterAudio)));
        */
    });

    $discord->listenCommand('list_chapter', function (Interaction $interaction) {
        $chapterList = Quran::listChapter();
        foreach($chapterList as $chapter) {
            $list .= "{$chapter['nomor']}.{$chapter['nama']} - {$chapter['arti']}\n";
        }
        $splitList = splitStringByCharacters($list);
        $interaction->respondWithMessage(MessageBuilder::new()->setContent($splitList[0])); // send first 2000 chars
        $sizeOfSplitList = sizeof($splitList);
        
        for($x = 1; $x < $sizeOfSplitList; $x++) {
            $interaction->sendFollowUpMessage(MessageBuilder::new()->setContent($splitList[$x])); // follow up the rest of the chars
        }
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