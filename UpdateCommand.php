<?php

namespace larryli\lingao;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateCommand extends Command
{
    /**
     *
     */
    protected function configure()
    {
        $this->setName('update')
            ->setDescription('更新元老索引页面')
            ->addArgument(
                'path',
                InputArgument::REQUIRED,
                "小说路径");
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = $input->getArgument('path');
        $output->writeln("<comment>从路径 \"{$path}\" 收集索引数据：</comment>");

        $finder = Config::finder($path);
        foreach ($finder as $file) {
            $name = $file->getBasename();
            $output->write("分析 <comment>\"{$name}\"</comment>：");
            $parser = new Markdown($file->getContents());
            $parser->update();
            $output->writeln("<info>完成。</info>");
        }

        $output->writeln("<comment>重新生成索引页面：</comment>");
        foreach (Character::$characters as $character) {
            $output->write("生成 <comment>{$character->id}: '{$character->name}'</comment> 的索引页：");
            $filename = $path . "/_characters/{$character->id}.md";
            $content = $character->render();
            if ($content === null) {
                if (file_exists($filename)) {
                    unlink($filename);
                }
            } else {
                file_put_contents($filename, $content);
            }
            $output->writeln("<info>完成。</info>");
        }
    }
}
