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
    protected $lastStatus = null;

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
     * @param bool   $catchOutput
     * @return string
     * @throws ShellException
     */
    public function shellExec($command, $exceptionOnError = true, $catchOutput = true)
    {
        if ($catchOutput) {
            $command .= ' 2>&1';
        }

        exec(trim(`$command`), $output, $return);
        $this->lastStatus = $return;

        $output = implode(PHP_EOL, $output);

        if ($exceptionOnError && 0 !== $return) {
            throw new ShellException($output);
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
}
