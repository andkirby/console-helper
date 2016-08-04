<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/console-helper/master/LICENSE
 */
namespace Rikby\Console\Command;

use Rikby\Console\Helper\SimpleQuestionHelper;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Base command abstract class
 *
 * @package PreCommit\Command\Command
 */
abstract class AbstractCommand extends Command
{
    /**
     * Output
     *
     * @var OutputInterface
     */
    protected $output;

    /**
     * Input
     *
     * @var InputInterface
     */
    protected $input;

    /**
     * Input/Output model
     *
     * @var SymfonyStyle
     */
    protected $io;

    /**
     * Sets the application instance for this command.
     *
     * Set extra helper ProjectDir
     *
     * @param Application $application An Application instance
     * @throws \Exception
     * @api
     */
    public function setApplication(Application $application = null)
    {
        parent::setApplication($application);

        if (!$this->getHelperSet()) {
            throw new \Exception('Helper set is not set.');
        }
        $this->getHelperSet()->set(new SimpleQuestionHelper());
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        $this->io = new SymfonyStyle($input, $output);
    }

    /**
     * Configure command
     */
    protected function configure()
    {
        $this->configureCommand();
        $this->configureInput();
    }

    /**
     * Configure command
     */
    protected function configureInput()
    {
//        $this->addArgument(
//            'arg1',
//            InputArgument::OPTIONAL,
//            'About arg1.'
//        );
    }

    /**
     * Configure command
     */
    protected function configureCommand()
    {
//        $this->setName('test');
//        $this->setHelp(
//            'Text for help.'
//        );
//        $this->setDescription(
//            'Text for help.'
//        );
    }

    /**
     * Get question helper
     *
     * @return SimpleQuestionHelper
     */
    protected function getSimpleQuestion()
    {
        return $this->getHelperSet()->get('simple_question');
    }

    /**
     * Is output very verbose
     *
     * @return bool
     */
    protected function isVeryVerbose()
    {
        return $this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERY_VERBOSE;
    }

    /**
     * Is output verbose
     *
     * @return bool
     */
    protected function isVerbose()
    {
        return $this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE;
    }

    /**
     * Is output verbose
     *
     * @return bool
     */
    protected function isDebug()
    {
        return $this->output->getVerbosity() >= OutputInterface::VERBOSITY_DEBUG;
    }
}
