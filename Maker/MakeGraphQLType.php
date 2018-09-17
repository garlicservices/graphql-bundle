<?php

namespace Garlic\GraphQL\Maker;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Doctrine\DoctrineHelper;
use Symfony\Bundle\MakerBundle\FileManager;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Bundle\MakerBundle\Validator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\Question;

/**
 * @author Maksym Churkyn <imaximius@gmail.com>
 */
final class MakeGraphQLType extends AbstractMaker
{
    /** @var DoctrineHelper */
    private $entityHelper;
    /**
     * @var FileManager
     */
    private $fileManager;
    
    /**
     * MakeGraphQLTypeForm constructor.
     * @param DoctrineHelper $entityHelper
     * @param FileManager $fileManager
     */
    public function __construct(DoctrineHelper $entityHelper, FileManager $fileManager)
    {
        $this->entityHelper = $entityHelper;
        $this->fileManager = $fileManager;
    }

    /**
     * Return command name
     * @return string
     */
    public static function getCommandName(): string
    {
        return 'make:graphql:type';
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
            ->setDescription('Creates a new GraphQL type class')
            ->addArgument('name', InputArgument::OPTIONAL, sprintf('The name of the type class (e.g. <fg=yellow>%s</>)', Str::asClassName(Str::getRandomTerm())))
            ->addArgument('bound-class', InputArgument::OPTIONAL, 'The name of Entity or fully qualified model class name that the new type will be bound to (empty for none)')
            ->addOption('is-crud', null, InputOption::VALUE_NONE, 'Generates full CRUD queries and mutations list');

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
        if (null === $input->getArgument('bound-class')) {
            $argument = $command->getDefinition()->getArgument('bound-class');
            $entities = $this->entityHelper->getEntitiesForAutocomplete();
            $question = new Question($argument->getDescription());
            $question->setValidator(function ($answer) use ($entities) {
                return Validator::existsOrNull($answer, $entities);
            });

            $question->setAutocompleterValues($entities);
            $question->setMaxAttempts(3);
            $input->setArgument('bound-class', $io->askQuestion($question));
        }
    
        $isCrud = $io->confirm(
            'Would you like to make full CRUD queries and mutations?',
            false
        );
    
        $input->setOption('is-crud', $isCrud);
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
        $isCrud = $input->getOption('is-crud');
        
        $formClassNameDetails = $generator->createClassNameDetails(
            $input->getArgument('name'),
            'GraphQL\\Type',
            'Type'
        );

        $formFields = ['fieldFame'];
        $boundClass = $input->getArgument('bound-class');
        $uses = [];
        $boundClassDetails = $generator->createClassNameDetails(
            $boundClass,
            'Entity\\'
        );

        $doctrineEntityDetails = $this->entityHelper->createDoctrineDetails($boundClassDetails->getFullName());
        if (null !== $doctrineEntityDetails) {
            $formFields = $this->mapScalarFields($doctrineEntityDetails->getDisplayFields());

            foreach ($formFields as $fieldUse){
                $uses[$fieldUse['fieldType']] = $fieldUse['fieldType'];
            }
        }

        $boundClassVars = [
            'bounded_class_name' => $boundClassDetails->getShortName(),
            'bounded_full_class_name' => $boundClassDetails->getFullName(),
        ];
    
        $generator->generateClass(
            $formClassNameDetails->getFullName(),
            dirname(dirname(__FILE__)) . '/Resources/skeleton/Type.tpl.php',
            array_merge(
                [
                    'formFields' => $formFields,
                    'uses' => $uses
                ],
                $boundClassVars
            )
        );
    
        $generator->writeChanges();
        
        if(!empty($isCrud)) {
            $serviceClassNameDetails = $this->createService($boundClassVars, $boundClassVars, $input, $generator);
            $resolvers = [
                [
                    'namespace' => 'Query',
                    'suffix' => 'Find',
                    'template' =>  'Resolver.tpl.php',
                ],
                [
                    'namespace' => 'Mutation',
                    'suffix' => 'Create',
                    'template' =>  'Resolver.tpl.php',
                ],
                [
                    'namespace' => 'Mutation',
                    'suffix' => 'Update',
                    'template' =>  'Resolver.tpl.php',
                ],
                [
                    'namespace' => 'Mutation',
                    'suffix' => 'Delete',
                    'template' =>  'Resolver.tpl.php',
                ]
            ];
    
            $actions = [];
            foreach ($resolvers as $resolver) {
                $suffix = $resolver['suffix'] ?? '';
                $queryClassNameDetails = $generator->createClassNameDetails(
                    $input->getArgument('name').$suffix,
                    'GraphQL\\'.$resolver['namespace'].'\\' . $input->getArgument('name')
                );
        
                $generator->generateClass(
                    $queryClassNameDetails->getFullName(),
                    dirname(dirname(__FILE__)) . '/Resources/skeleton/'.$resolver['template'],
                    [
                        'suffix' => $suffix,
                        'entityFullName' => $boundClassDetails->getFullName(),
                        'entityName' => $boundClassDetails->getShortName(),
                        'serviceFullName' => (empty($serviceClassNameDetails))?:$serviceClassNameDetails->getFullName(),
                        'serviceName' => (empty($serviceClassNameDetails))?:$serviceClassNameDetails->getShortName(),
                        'isMutation' => ($resolver['namespace'] == 'Mutation') ? true : false,
                        'form_class_name' => $formClassNameDetails->getShortName(),
                        'form_full_class_name' => $formClassNameDetails->getFullName(),
                        'bounded_class_name' => $boundClassDetails->getShortName(),
                    ]
                );
        
                $actions[$resolver['namespace']][] = [
                    'fullName' => $queryClassNameDetails->getFullName(),
                    'shortName' => Str::getShortClassName($queryClassNameDetails->getFullName())
                ];
        
                $generator->writeChanges();
            }
    
            foreach ($actions as $type => $action){
                $this->registerQueries(
                    $type,
                    $action,
                    $generator
                );
            }
        }
        
        $this->writeSuccessMessage($io);
        $io->text(
            [
                'Next: Create custom query to your type and start using it.',
                'To create custom query or mutation use "maker:graphql:query"',
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
     * Map fields type and required
     *
     * @param array $fullFields
     * @return array
     */
    private function mapScalarFields(array $fullFields)
    {
        $fields = [];
        foreach ($fullFields as $field) {
            if (!empty($field['id'])) {
                $type = 'IdType';
            } else {
                switch ($field['type']) {
                    case "integer":
                        $type = 'IntType';
                        break;

                    case "datetime":
                        $type = 'DateTimeType';
                        break;

                    case "float":
                        $type = 'FloatType';
                        break;

                    case "boolean":
                        $type = 'BooleanType';
                        break;

                    case "date":
                        $type = 'DateType';
                        break;

                    case "timestamp":
                        $type = 'TimestampType';
                        break;

                    default:
                        $type = 'StringType';
                }
            }

            $fields[] = [
                'fieldName' => $field['fieldName'],
                'fieldType' => $type,
                'fieldRequired' => $field['nullable']
            ];
        }

        return $fields;
    }
    
    
    /**
     * Creates helper class
     *
     * @param $boundObject
     * @param $boundClassVars
     * @param InputInterface $input
     * @param Generator $generator
     * @return string
     */
    private function createService($boundObject, $boundClassVars, InputInterface $input, Generator $generator)
    {
        $serviceClassNameDetails = $generator->createClassNameDetails(
            $input->getArgument('name').'Service',
            'Service\\GraphQL'
        );
        
        $generator->generateClass(
            $serviceClassNameDetails->getFullName(),
            dirname(dirname(__FILE__)) . '/Resources/skeleton/Service.tpl.php',
            array_merge(
                [
                    'entityFullName' => $boundObject['bounded_full_class_name'],
                    'entityName' => $boundObject['bounded_class_name'],
                ],
                $boundClassVars
            )
        );
        
        $generator->writeChanges();
        
        return $serviceClassNameDetails;
    }
    
    /**
     * Register class in main query
     *
     * @param $type
     * @param $actions
     * @param Generator $generator
     * @throws \Exception
     */
    private function registerQueries($type, $actions, Generator $generator)
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
                'fields' => array_merge($classFields, $actions)
            ]
        
        );
        
        $generator->writeChanges();
        
        if(is_file($fileName) && isset($tmpFileName)) {
            unlink($tmpFileName);
        }
    }
}