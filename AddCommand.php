<?php

namespace larryli\lingao;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AddCommand extends Command
{
    /**
     *
     */
    protected function configure()
    {
        $this->setName('add')
            ->setDescription('在文章中增加索引')
            ->addArgument(
                'path',
                InputArgument::REQUIRED,
                "小说路径")
            ->addArgument(
                'id',
                InputArgument::REQUIRED,
                "元老 ID")
            ->addArgument(
                'names',
                InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
                "元老名字，可以使用提供多个（可选）")
            ->addOption(
                'start',
                's',
                InputOption::VALUE_OPTIONAL,
                '文章开始时间'
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $id = $input->getArgument('id');
        $character = Character::getCharacterById($id);
        if ($character === null) {
            $output->writeln("<error>{$id} 是无效的元老 ID。</error>");
            exit();
        }
        $names = $input->getArgument('names');
        if (empty($names)) {
            $names[] = $character->name;
        }
        $string = implode('、', $names);
        $replace = array_map(function ($name) use ($id) {
            return "[{$name}][{$id}]";
        }, $names);

        $path = $input->getArgument('path');
        $output->writeln("<comment>从路径 \"{$path}\" 增加下列关键字：</comment><question>{$string}</question><comment>到索引 {$id}。</comment>");

        $finder = Config::finder($path);

        $start = $input->getOption('start');
        if ($start !== null) {
            $start = strtotime($start);
            $string = date('Y-n-j', $start);
            $output->writeln("<comment>只处理 {$string} 之后的文章。</comment>");
            $finder->filter(function (\SplFileInfo $file) use ($start) {
                $cmp = Config::getCmpByPath($file->getBasename());
                if ($cmp[0] < $start) {
                    return false;
                }
                return true;
            });
        }

        foreach ($finder as $file) {
            $name = $file->getBasename();
            $output->write("索引 <comment>\"{$name}\"</comment>：");
            $parser = new Markdown($file->getContents());
            $parser->add($names, $replace);
            $parser->fix();
            file_put_contents($file->getRealPath(), $parser->render());
            $output->writeln("<info>完成。</info>");
        }
    }
}
