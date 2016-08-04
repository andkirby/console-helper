<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/console-helper/master/LICENSE
 */

namespace Rikby\Console\Helper;

use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class PhpBinHelper
 *
 * @package Rikby\Console\Helper
 */
class PhpBinHelper extends Helper
{
    /**
     * Helper name
     */
    const NAME = 'php_bin_get';

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * Ask about PHP executable file
     *
     * @param SymfonyStyle    $style
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param string          $optionName
     * @param \Closure        $validator
     * @return array
     * @throws LogicException
     */
    public function askPhpPath(
        SymfonyStyle $style,
        InputInterface $input,
        OutputInterface $output,
        $optionName = 'php-binary',
        \Closure $validator = null
    ) {
        $validator = $validator ?: $this->getPhpValidator();

        $file = $input->getOption($optionName);
        if (!$file) {
            $file = $this->getSystemPhpPath();
        }

        $max = 3;
        $i = 0;
        while (!$file || !$validator($file, $output)) {
            if ($file) {
                $output->writeln('Given PHP executable file is not valid.');
            }
            $file = $style->askQuestion(
                $this->getSimpleQuestion()->getQuestion('Please set your PHP executable file', $file)
            );
            // @codingStandardsIgnoreStart
            if (++$i > $max) {
                throw new LogicException('Path to PHP executable file is not set.');
            }
            // @codingStandardsIgnoreEnd
        }

        return $file;
    }

    /**
     * Get system path to executable PHP file
     *
     * @return null|string
     */
    public function getSystemPhpPath()
    {
        $file = null;
        if (defined('PHP_BIN_DIR') && is_file(PHP_BIN_DIR.'/php')) {
            $file = PHP_BIN_DIR.'/php';
        } elseif (defined('PHP_BIN_DIR') && is_file(PHP_BIN_DIR.'/php.exe')) {
            $file = PHP_BIN_DIR.'/php.exe';
        } elseif (defined('PHP_BINARY') && is_file(PHP_BINARY)) {
            $file = PHP_BINARY;
        } elseif (getenv('PHP_BINARY') && is_file(getenv('PHP_BINARY'))) {
            $file = getenv('PHP_BINARY');
            // @codingStandardsIgnoreStart
        } elseif (isset($_SERVER['_']) && pathinfo($_SERVER['_'], PATHINFO_FILENAME) == 'php') {
            $file = $_SERVER['_'];
            // @codingStandardsIgnoreEnd
        } elseif (is_file('/usr/local/bin/php')) {
            //try to check Unix system php file
            $file = '/usr/local/bin/php';
        }
        if ($file) {
            $file = str_replace('/', DIRECTORY_SEPARATOR, $file);
        }

        return $file;
    }

    /**
     * Get PHP binary file validator
     *
     * @return callable
     */
    protected function getPhpValidator()
    {
        // @codingStandardsIgnoreStart
        return function ($file, OutputInterface $output = null) {
            if (is_file($file)) {
                //@startSkipCommitHooks
                $test = `$file -r "echo 'Test passed.';" 2>&1`;
                //@finishSkipCommitHooks
                if ($output && $output->getVerbosity() >= OutputInterface::VERBOSITY_VERY_VERBOSE) {
                    $output->writeln(
                        'PHP test output: '.PHP_EOL.$test
                    );
                }

                return 0 === strpos($test, 'Test passed.');
            }

            return false;
        };
        // @codingStandardsIgnoreEnd
    }

    /**
     * Get question helper
     *
     * @return SimpleQuestionHelper
     */
    protected function getSimpleQuestion()
    {
        if (!$this->getHelperSet()->has('simple_question')) {
            $this->getHelperSet()->set(new SimpleQuestionHelper());
        }

        return $this->getHelperSet()->get('simple_question');
    }
}
