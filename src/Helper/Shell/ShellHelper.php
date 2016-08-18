<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/console-helper/master/LICENSE
 */
namespace Rikby\Console\Helper\Shell;

use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Wrap helper for running shell commands
 *
 * @package Rikby\Console\Helper\Shell
 */
class ShellHelper extends Helper
{
    /**
     * Helper name
     */
    const NAME = 'shell';

    /**
     * Last returned status
     *
     * @var int|null
     */
    protected $lastStatus;

    /**
     * Input model
     *
     * @var OutputInterface
     */
    protected $output;

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * Execute command
     *
     * @param string $command
     * @param bool   $exceptionOnError
     * @param string $tail
     * @param string $exception
     * @return string
     * @throws ShellException
     * @internal param array $output
     */
    public function shellExec($command, $exceptionOnError = true, $tail = ' 2>&1', $exception = ShellException::class)
    {
        $command = $this->filterCommand($command, $tail);
        $this->showCommand($command);

        exec($command, $output, $this->lastStatus);

        $output = $this->filterOutput($output);
        $this->processError($output, $exceptionOnError, $exception);

        return $output;
    }

    /**
     * Check a last executed command had an error
     *
     * @return int|null
     */
    public function hadError()
    {
        return null !== $this->getLastStatus() && 0 !== $this->getLastStatus();
    }

    /**
     * Get status of a last executed command
     *
     * @return int|null
     */
    public function getLastStatus()
    {
        return $this->lastStatus;
    }

    /**
     * Set output
     *
     * @param OutputInterface $output
     * @return $this
     */
    public function setOutput($output)
    {
        $this->output = $output;

        return $this;
    }

    /**
     * Filter command string
     *
     * @param string $command
     * @param string $tail
     * @return string
     * @throws ShellException
     */
    protected function filterCommand($command, $tail)
    {
        $command = trim($command);
        if (!$command) {
            throw new ShellException('Command cannot be empty.');
        }

        if ($tail && !strpos($command, $tail)) {
            $command .= $tail;
        }

        return $command;
    }

    /**
     * Show command
     *
     * @param string $command
     * @return $this
     */
    protected function showCommand($command)
    {
        if ($this->isVeryVerbose()) {
            $this->output->writeln("command: $command");
        }

        return $this;
    }

    /**
     * Filter output
     *
     * @param array|null $output
     * @return string
     */
    protected function filterOutput($output)
    {
        if ($output) {
            $output = trim(implode(PHP_EOL, $output));
        }

        return $output;
    }

    /**
     * Process error in output
     *
     * @param string $output
     * @param bool   $exceptionOnError
     * @param string $exceptionClass
     * @return $this
     */
    protected function processError($output, $exceptionOnError = true, $exceptionClass = ShellException::class)
    {
        if ($exceptionOnError && $this->hadError()) {
            $exceptionClass = $exceptionClass ?: ShellException::class;
            throw new $exceptionClass(
                $output && $this->isVeryVerbose() ? $output : 'Cannot run command.'
            );
        }

        return $this;
    }

    /**
     * Is output verbose
     *
     * @return bool
     */
    protected function isVerbose()
    {
        return $this->output && $this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE;
    }

    /**
     * Is output very verbose
     *
     * @return bool
     */
    protected function isVeryVerbose()
    {
        return $this->output && $this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERY_VERBOSE;
    }
}
