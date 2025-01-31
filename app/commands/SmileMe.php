<?php
namespace App\Controllers\Commands;

use Core\Contracts\BaseCommand;

class SmileMe extends BaseCommand
{

    public $jokes = [
        "I told my wife she should embrace her mistakes. She gave me a hug.",
        "I'm reading a book on the history of glue. I just can't seem to put it
down.",
        "I'm friends with 25 letters of the alphabet. I don't know y.",
        "I told my computer I needed a break and now it won't stop sending me
vacation ads.",
        "I'm reading a book on anti-gravity. It's impossible to put down.",
        "I told my wife she should embrace her mistakes. She gave me a hug.",
        "I'm reading a book on the history of glue. I just can't seem to put it
down.",
        "I'm friends with 25 letters of the alphabet. I don't know y.",
        "I told my computer I needed a break and now it won't stop sending me
vacation ads.",
    ];
    public function handle()
    {
        $rand = rand(0, count($this->jokes) - 1);
        echo $this->jokes[$rand] . PHP_EOL;

    }
}
