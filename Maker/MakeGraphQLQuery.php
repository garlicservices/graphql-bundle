<?php

namespace Garlic\GraphQL\Maker;

use Garlic\GraphQL\Service\Helper\ClassFinder;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\FileManager;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\Question;
use Symfony\Bundle\MakerBundle\Validator;

/**
 * @author Maksym Churkyn <imaximius@gmail.com>
 */
final class MakeGraphQLQuery extends AbstractMaker
{
    /** @var string */
    private $classFinder
    
    /** @var string */;
    private $schemaNamespace;
    
    /** @var FileManager */
    private $fileManager;
    
    /**
     * MakeGraphQLQuery constructor.
     * @param ClassFinder $classFinder
     * @param $schemaNamespace
     * @param FileManager $fileManager
     */
    public function __construct(ClassFinder $classFinder, FileManager $fileManager, $schemaNamespace = '')
    {
        $this->classFinder = $classFinder;
        $this->schemaNamespace = $schemaNamespace;
        $this->fileManager = $fileManager;
    }


    /**
     * Return command name
     * @return string
     */
    public static function getCommandName(): string
    {
        return 'make:graphql:query';
    }

    /**
     * Configure command
     *
     * @param Command $command
     * @param InputConfiguration $inputConf
     */
    public function configureCommand(Command $command, InputConfiguration $inputConf)
    {
        $command
            ->setDescription('Create GraphQL query class')
            ->addArgument('name', InputArgument::REQUIRED, sprintf('The name of the Query class (e.g. <fg=yellow>%s</>)', Str::asClassName(Str::getRandomTerm())))
            ->addArgument('bound-class', InputArgument::REQUIRED, 'The name of Type or fully qualified class name that the new query will be bound to')
            ->addOption('is-mutation', null, InputOption::VALUE_NONE, 'Write query as Mutation');

        $inputConf->setArgumentAsNonInteractive('bound-class');
    }

    /**
     * Get necessary arguments from user
     *
     * @param InputInterface $input
     * @param ConsoleStyle $io
     * @param Command $command
     */
    public function interact(InputInterface $input, ConsoleStyle $io, Command $command)
    {
        $argument = $command->getDefinition()->getArgument('bound-class');
        $types = $this->classFinder->getClassesByPath("GraphQL/Type");

        $question = new Question($argument->getDescription());
        $question->setValidator(function ($answer) use ($types) {
            return Validator::existsOrNull($answer, $types);
        });

        $question->setAutocompleterValues($types);
        $question->setMaxAttempts(3);
        $input->setArgument('bound-class', $io->askQuestion($question));

        $isMutation = $io->confirm(
            'Do you want to make Query as Mutation?',
            false
        );

        $input->setOption('is-mutation', $isMutation);
    }
    
    /**
     * Generate file with GraphQL type
     *
     * @param InputInterface $input
     * @param ConsoleStyle $io
     * @param Generator $generator
     * @throws \Exception
     */
    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator)
    {
        $boundClass = $input->getArgument('bound-class');
        $isMutation = $input->getOption('is-mutation');

        $boundClassDetails = $generator->createClassNameDetails(
            $boundClass,
            'GraphQL\\Type\\'
        );

        $boundClassVars = [
            'bounded_class_name' => $boundClassDetails->getShortName(),
            'bounded_full_class_name' => $boundClassDetails->getFullName(),
        ];

        $boundObject = new $boundClassVars['bounded_full_class_name']();
    
        $resolver = [
            'namespace' => 'Query',
            'template' =>  'QueryResolver.tpl.php',
        ];
        
        if(!empty($isMutation)) {
            $resolver = [
                'namespace' => 'Mutation',
                'template' =>  'MutationResolver.tpl.php',
            ];
        }
    
        $suffix = $resolver['suffix'] ?? '';
        $queryClassNameDetails = $generator->createClassNameDetails(
            $input->getArgument('name').$suffix,
            'GraphQL\\'.$resolver['namespace'].'\\' . trim($boundClass, "Type")
        );

        $generator->generateClass(
            $queryClassNameDetails->getFullName(),
            dirname(dirname(__FILE__)) . '/Resources/skeleton/'.$resolver['template'],
            array_merge(
                [
                    'isMutation' => $isMutation,
                    'suffix' => $suffix,
                    'entityFullName' => $boundObject->getEntity(),
                    'entityName' => Str::getShortClassName($boundObject->getEntity()),
                    'serviceFullName' => (empty($serviceClassNameDetails))?:$serviceClassNameDetails->getFullName(),
                    'serviceName' => (empty($serviceClassNameDetails))?:$serviceClassNameDetails->getShortName(),
                ],
                $boundClassVars
            )
        );

        $generator->writeChanges();
    
        $this->register(
            $resolver['namespace'],
            [
                'fullName' => $queryClassNameDetails->getFullName(),
                'shortName' => $queryClassNameDetails->getShortName(),
            ],
            $generator
        );
    
        $this->writeSuccessMessage($io);
        $io->text(
            [
                'Next: Update and start using just generated classes.',
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
    
    /**
     * Register class in main query
     *
     * @param $type
     * @param $actions
     * @param Generator $generator
     * @throws \Exception
     */
    private function register($type, $actions, Generator $generator)
    {
        $classes = [
            'Query' =>[
                'namespace' => 'GraphQL\\Query',
                'template' => 'Query.tpl.php',
            ],
            'Mutation' =>[
                'namespace' => 'GraphQL\\Mutation',
                'template' => 'Mutation.tpl.php',
            ],
        ];
    
        $class = $classes[$type];
        $classNameDetails = $generator->createClassNameDetails(
            $type.'Type',
            $class['namespace']
        );
    
        $classFields = [];
        $fileName = $this->fileManager->getRelativePathForFutureClass($classNameDetails->getFullName());
        $classObjectFullName = $classNameDetails->getFullName();
        if(is_file($fileName)) {
            $classObject = new $classObjectFullName();
            foreach ($classObject->getFields() as $classField) {
                $classFields[] = [
                    'fullName' => get_class($classField),
                    'shortName' => Str::getShortClassName(get_class($classField))
                ];
            }

            $tmpFileName = $fileName.'.bak';
            rename($fileName, $tmpFileName);
        }
        
        $generator->generateClass(
            $classNameDetails->getFullName(),
            dirname(dirname(__FILE__)) . '/Resources/skeleton/' . $class['template'],
            [
                'fields' => array_merge($classFields, [$actions])
            ]
            
        );
        
        $generator->writeChanges();
        
        if(is_file($fileName) && isset($tmpFileName)) {
            unlink($tmpFileName);
        }
    }
}