<?php

namespace Garlic\GraphQL\Maker;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Doctrine\DoctrineHelper;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Bundle\MakerBundle\Validator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * @author Maksym Churkyn <imaximius@gmail.com>
 */
final class MakeGraphQLType extends AbstractMaker
{
    /** @var DoctrineHelper */
    private $entityHelper;

    /**
     * MakeGraphQLTypeForm constructor.
     * @param DoctrineHelper $entityHelper
     */
    public function __construct(DoctrineHelper $entityHelper)
    {
        $this->entityHelper = $entityHelper;
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
            ->addArgument('name', InputArgument::OPTIONAL, sprintf('The name of the type class (e.g. <fg=yellow>%sType</>)', Str::asClassName(Str::getRandomTerm())))
            ->addArgument('bound-class', InputArgument::OPTIONAL, 'The name of Entity or fully qualified model class name that the new form will be bound to (empty for none)');

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
        $formClassNameDetails = $generator->createClassNameDetails(
            $input->getArgument('name'),
            'GraphQL\\Type',
            'Type'
        );

        $formFields = ['fieldFame'];
        $boundClassVars = [];
        $boundClass = $input->getArgument('bound-class');
        $uses = [];
        if (null !== $boundClass) {
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
            ];
        }

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
        $this->writeSuccessMessage($io);
        $io->text(
            [
                'Next: Create query to your type and start using it.',
                'To create query use "maker:graphql:query"',
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
}