<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/console-helper/master/LICENSE
 */
namespace Rikby\Console\Helper\Shell;

use Symfony\Component\Console\Helper\Helper;

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
     * @param string $exception
     * @return string
     * @throws ShellException
     * @internal param bool $catchOutput
     */
    public function shellExec($command, $exceptionOnError = true, $exception = ShellException::class)
    {
        if (!$command) {
            throw new ShellException('Command cannot be empty.');
        }

        if (!strpos($command, '2>&1')) {
            $command .= ' 2>&1';
        }

        exec(trim(`$command`), $output, $this->lastStatus);

        $output = trim(implode(PHP_EOL, $output));

        if ($exceptionOnError && $this->hadError()) {
            $exception = $exception ?: ShellException::class;
            throw new $exception($output);
        }

        return $output;
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
     * Check a last executed command had an error
     *
     * @return int|null
     */
    public function hadError()
    {
        return null !== $this->getLastStatus() && 0 !== $this->getLastStatus();
    }
}
