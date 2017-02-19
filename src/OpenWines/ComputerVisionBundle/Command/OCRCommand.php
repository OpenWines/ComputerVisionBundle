<?php

namespace OpenWines\ComputerVisionBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use League\Csv\Writer;

/**
 * OCRCommand
 *
 * @author    Ronan Guilloux <ronan.guilloux@gmail.com>
 * @copyright 2017 Ronan Guilloux
 * @license   MIT
 */
class OCRCommand extends ContainerAwareCommand
{

    /**
     * @var string Computer Vision API Key
     */
    protected $apiKey;

    /**
     * @var string images source: Single image or folder.
     */
    protected $source;

    /**
     * @var string language. Expects a BCP-47 language code ; default value is "unk".
     */
    protected $lang;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('cv:ocr')
            ->setDescription('Perform OCR on an image')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp(<<<EOT
This command allows you to perform Optical Character Recognition (OCR) method over an image,
to detect text in an image and extract recognized characters into a machine-usable character stream.
the OCR results are returned include include text, bounding box for regions, lines and words

Computer Vision API overview: https://www.microsoft.com/cognitive-services/en-us/computer-vision-api
Computer Vision API documentation: https://westus.dev.cognitive.microsoft.com/docs/services/56f91f2d778daf23d8ec6739/operations/56f91f2e778daf14a499e1fc
EOT
            );

        $this
            // configure an argument
            ->addArgument('source', InputArgument::REQUIRED, 'base64 encoded, URL, or absolute path. Single image or folder.')
            ->addArgument('lang', InputArgument::REQUIRED, 'The BCP-47 language code of the text to be detected in the image.The default value is "unk", then the service will auto detect the language of the text in the image.')
            ->addOption('output', 'o', InputArgument::OPTIONAL, 'The optional CSV file output path')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $files = $this->getContainer()->get('openwines_computer_vision.client')->process(
            $input->getArgument('source'),
            $input->getArgument('lang')
        );
        if($input->getOption('output')) {
            $writer = Writer::createFromPath(new \SplFileObject($input->getOption('output'), 'a+'), 'w');
            $writer->insertAll(new \ArrayIterator($files));
            $output->writeln(sprintf('CSV output written into %s', $input->getOption('output')));
        } else {
            foreach ($files as $file) {
                $output->writeln(sprintf('%s;%s', $file['source'], $file['text']));
            }
        }
    }
}
