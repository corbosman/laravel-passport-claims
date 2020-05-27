<?php

namespace CorBosman\Passport\Console;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;

class ClaimGenerator extends GeneratorCommand
{
    protected $name = 'claim:generate';
    protected $description = 'Create a new JWT Claim class';
    protected $type = 'Claim';

    public function getStub()
    {
        return __DIR__.'/stubs/claim.stub';
    }

    protected function getOptions()
    {
        return [
            ['force', null, InputOption::VALUE_NONE, 'Create the class even if the claim already exists'],
        ];
    }

}
