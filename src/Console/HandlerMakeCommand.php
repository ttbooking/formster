<?php

declare(strict_types=1);

namespace TTBooking\Formster\Console;

use Closure;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

use function Laravel\Prompts\suggest;

#[AsCommand(
    name: 'make:formster-handler',
    description: 'Create a new Formster property handler class',
)]
class HandlerMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:formster-handler';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Formster property handler class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Handler';

    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     *
     * @throws FileNotFoundException
     */
    protected function buildClass($name): string
    {
        /** @var string $type */
        $type = $this->option('type') ?? Str::beforeLast($this->getNameInput(), $this->type);

        if (! Str::startsWith($type, [$this->laravel->getNamespace(), 'Illuminate', '\\'])) {
            $type = $this->laravel->getNamespace().'Formster\\Types\\'.str_replace('/', '\\', $type);
        }

        return str_replace(
            ['{{ namespacedType }}', '{{ type }}', '{{ kebabType }}'],
            [trim($type, '\\'), $basename = class_basename($type), Str::kebab($basename)],
            parent::buildClass($name)
        );
    }

    /**
     * Get the desired class name from the input.
     */
    protected function getNameInput(): string
    {
        return Str::beforeLast(parent::getNameInput(), $this->type).$this->type;
    }

    /**
     * Determine if the class already exists.
     *
     * @param  string  $rawName
     */
    protected function alreadyExists($rawName): bool
    {
        return class_exists($rawName) || parent::alreadyExists($rawName);
    }

    /**
     * Get the stub file for the generator.
     */
    protected function getStub(): string
    {
        return $this->resolveStubPath('/stubs/handler.stub');
    }

    /**
     * Resolve the fully-qualified path to the stub.
     */
    protected function resolveStubPath(string $stub): string
    {
        return file_exists($customPath = $this->laravel->basePath(trim($stub, '/')))
            ? $customPath
            : __DIR__.$stub;
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace.'\Formster\Handlers';
    }

    /**
     * Get the console command options.
     *
     * @return list<array{0: string, 1?: string|list<string>, 2?: int, 3?: string, 4?: mixed, 5?: list<string>|Closure}>
     */
    protected function getOptions(): array
    {
        return [
            ['type', 't', InputOption::VALUE_REQUIRED, 'The type or class being handled'],
            ['force', 'f', InputOption::VALUE_NONE, 'Create the class even if the handler already exists'],
        ];
    }

    /**
     * Prompt for missing input arguments using the returned questions.
     *
     * @return array<string, string|array{0: string, 1?: string}|Closure(): mixed>
     */
    protected function promptForMissingArgumentsUsing(): array
    {
        return [
            'name' => ['What should the handler be named?', 'E.g. DateTimeHandler'],
        ];
    }

    /**
     * Interact further with the user if they were prompted for missing arguments.
     */
    protected function afterPromptingForMissingArguments(InputInterface $input, OutputInterface $output): void
    {
        if ($this->isReservedName($this->getNameInput()) || $this->didReceiveOptions($input)) {
            return;
        }

        if ($type = suggest('What type or class should be handled?', $this->possibleTypes())) {
            $input->setOption('type', $type);
        }
    }

    /**
     * Get a list of possible type names.
     *
     * @return list<string>
     */
    protected function possibleTypes(): array
    {
        $typePath = app_path('Formster/Types');

        if (! is_dir($typePath)) {
            return [];
        }

        /** @var Collection<string, SplFileInfo> $files */
        $files = collect(Finder::create()->files()->depth(0)->in($typePath));

        /** @var list<string> */
        return $files->map(static fn (SplFileInfo $file) => $file->getBasename('.php'))->sort()->values()->all();
    }
}
