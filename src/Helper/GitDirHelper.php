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
class GitDirHelper extends Helper
{
    /**
     * Helper name
     */
    const NAME = 'git_dir_get';

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * Get GIT directory
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param null|string     $optionDir
     * @return string
     */
    public function getGitDirectory(InputInterface $input, OutputInterface $output, $optionDir = null)
    {
        $dir = $optionDir;
        if (!$dir) {
            $dir = $this->getVcsDirectory();
        }
        if (!$dir) {
            $dir = $this->getCommandDir();
        }
        $validator = $this->getValidator();
        try {
            return $validator($dir);
        } catch (\Exception $e) {
        }

        return $this->askProjectDir($input, $output, $dir);
    }

    /**
     * Get VCS directory (GIT)
     *
     * @param string|null $path
     * @return string
     */
    public function getVcsDirectory($path = null)
    {
        //TODO Move to adapter
        $path = $path ?: getcwd();
        // @codingStandardsIgnoreStart
        return realpath(trim(`git -C $path rev-parse --show-toplevel 2>&1`));
        // @codingStandardsIgnoreEnd
    }

    /**
     * Get GIT directory (.git)
     *
     * @param string|null $path
     * @return string
     */
    public function getDotGitDirectory($path = null)
    {
        //TODO Move to adapter
        $path = $path ?: getcwd();
        // @codingStandardsIgnoreStart
        return realpath(trim(`git -C $path rev-parse --git-dir 2>&1`));
        // @codingStandardsIgnoreEnd
    }

    /**
     * Get CLI directory (pwd)
     *
     * @return string
     */
    public function getCommandDir()
    {
        //@startSkipCommitHooks
        return $_SERVER['PWD'];
        //@finishSkipCommitHooks
    }

    /**
     * Ask about GIT project root dir
     *
     * It will skip asking if system is able to identify it.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param string|null     $dir
     * @param SymfonyStyle    $style
     * @return string
     */
    public function askProjectDir(
        InputInterface $input,
        OutputInterface $output,
        $dir = null,
        SymfonyStyle $style = null
    ) {
        $question = $this->getSimpleQuestion()
            ->getQuestion('Please set your root project directory.', $dir);
        $question->setValidator(
            $this->getValidator()
        );

        $style = $style ?: new SymfonyStyle($input, $output);
        $dir = $style->askQuestion($question);

        return rtrim($dir, '\\/');
    }

    /**
     * Get GIT root directory validator
     *
     * @return \Closure
     */
    protected function getValidator()
    {
        $helper = $this;
        // @codingStandardsIgnoreStart
        return function ($dir) use ($helper) {
            $dir = rtrim($dir, '\\/');
            if (!is_dir($helper->getDotGitDirectory($dir))) {
                throw new LogicException('No information about git directory.');
            }

            return $dir;
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
