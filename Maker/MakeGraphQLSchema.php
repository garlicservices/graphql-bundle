<?php

namespace Garlic\GraphQL\Maker;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

/**
 * @author Maksym Churkyn <imaximius@gmail.com>
 */
final class MakeGraphQLSchema extends AbstractMaker
{
    /** @var string */
    private $rootDir;
    
    /**
     * MakeGraphQLSchema constructor.
     * @param string $rootDir
     */
    public function __construct(string $rootDir)
    {
        $this->rootDir = $rootDir;
    }
    
    /**
     * Return command name
     * @return string
     */
    public static function getCommandName(): string
    {
        return 'make:graphql:init';
    }

    /**
     * Configure command
     *
     * @param Command $command
     * @param InputConfiguration $inputConf
     */
    public function configureCommand(Command $command, InputConfiguration $inputConf)
    {
        $command->setDescription('Init GraphQL classes and configs');
    }

    /**
     * Generate file with GraphQL type
     *
     * @param InputInterface $input
     * @param ConsoleStyle $io
     * @param Generator $generator
     */
    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator)
    {
        $classes = [
            'QueryType' =>[
                'namespace' => 'GraphQL\\Query',
                'template' => 'Query.tpl.php',
                'variables' => [
                    'fields' => []
                ]
            ],
            'MutationType' =>[
                'namespace' => 'GraphQL\\Mutation',
                'template' => 'Mutation.tpl.php',
                'variables' => [
                    'fields' => []
                ]
            ],
            'schema' =>[
                'namespace' => 'GraphQL',
                'template' =>'Schema.tpl.php',
                'variables' => []
            ],
        ];

        $configFile = dirname($this->rootDir).'/config/packages/graphql.yaml';
        if(!is_file($configFile)) {
            $generator->generateFile(
                $configFile,
                dirname(dirname(__FILE__)) . '/Resources/skeleton/graphql.tpl.yaml',
                []
            );
        }

        foreach ($classes as $name => $class) {
            $classNameDetails = $generator->createClassNameDetails(
                $name,
                $class['namespace']
            );

            $generator->generateClass(
                $classNameDetails->getFullName(),
                dirname(dirname(__FILE__)) . '/Resources/skeleton/' . $class['template'],
                $class['variables']
            );

            $generator->writeChanges();
        }

        $this->writeSuccessMessage($io);
        $io->text(
            [
                'Next: Create type (able to generate type from existing Entity).',
                'To create type use "maker:graphql:type"',
            ]
        );
    }

    /**
     * Configure maker dependencies
     *
     * @param DependencyBuilder $dependencies
     */
    public function configureDependencies(DependencyBuilder $dependencies)
    {
        $dependencies->addClassDependency(
            DoctrineBundle::class,
            'orm',
            false
        );
    }
}