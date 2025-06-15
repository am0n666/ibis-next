<?php

namespace Ibis\Commands;

use Ibis\Config;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class InitCommand extends Command
{
    private ?\Illuminate\Filesystem\Filesystem $disk = null;


    /**
     * Configure the command.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('init')
            ->addOption(
                'workingdir',
                'd',
                InputOption::VALUE_OPTIONAL,
                'The path of the working directory where `ibis.php` and `assets` directory will be created',
                '',
            )
            ->setDescription('Initialize a new project in the working directory (current dir by default).');
    }

    private function copyDirectory($src, $dst) {
        $dir = opendir($src);
        @mkdir($dst);
        while(false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                $srcPath = $src . DIRECTORY_SEPARATOR . $file;
                $dstPath = $dst . DIRECTORY_SEPARATOR . $file;
                if (is_dir($srcPath)) {
                    $this->copyDirectory($srcPath, $dstPath);
                } else {
                    copy($srcPath, $dstPath);
                }
            }
        }
        closedir($dir);
    }

    /**
     * Execute the command.
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @throws \Mpdf\MpdfException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->disk = new Filesystem();
        $io = new SymfonyStyle($input, $output);
        $io->title('Ibis Next - Init');

        $workingPath = $input->getOption('workingdir');
        if ($workingPath === "") {
            $workingPath = "./";
        } elseif (!is_dir($workingPath)) {
            $io->warning("The working directory " . $workingPath . " doesn't exists.");
            $confirm = $io->choice(
                'Do you want to create it (or exit, or use current dir)?',
                ["yes" => "Yes","exit" => "Exit", "current" => "Use the current directory"],
                "yes",
            );
            switch ($confirm) {
                case "yes":
                    mkdir($workingPath, recursive: true);
                    $io->text("Created the <strong>" . $workingPath . "</strong> directory.");
                    break;
                case "current":
                    $workingPath = "./";
                    $io->text("Using the " . $workingPath . " directory.");
                    break;
                default:
                    $io->text("Exit. bye");
                    return Command::INVALID;
            }
        }

        $ibisConfigPath = Config::buildPath(
            $workingPath,
            'ibis.php',
        );
        $contentPath = Config::buildPath(
            $workingPath,
            'content',
        );
        $assetsPath = Config::buildPath(
            $workingPath,
            'assets',
        );
        $io->section('Creating directory/files');

        $io->text('✨ Config and assets directory:');
        $io->text('    ' . $assetsPath);



        if ($this->disk->isDirectory($assetsPath)) {
            $io->newLine();
            $io->warning('Project already initialised!');
            return Command::INVALID;
        }

        $this->disk->makeDirectory(
            $assetsPath,
        );

        $this->disk->makeDirectory(
            Config::buildPath($assetsPath, 'fonts'),
        );
        $this->disk->makeDirectory(
            Config::buildPath($assetsPath, 'images'),
        );

        $assetsToCopy = [
            // 'fonts/Bookerly/bookerly.css',
            // 'fonts/Bookerly/Bookerly-Bold.eot',
            // 'fonts/Bookerly/Bookerly-Bold.ttf',
            // 'fonts/Bookerly/Bookerly-Bold.woff',
            // 'fonts/Bookerly/Bookerly-Bold.woff2',
            // 'fonts/Bookerly/Bookerly-BoldItalic.eot',
            // 'fonts/Bookerly/Bookerly-BoldItalic.ttf',
            // 'fonts/Bookerly/Bookerly-BoldItalic.woff',
            // 'fonts/Bookerly/Bookerly-BoldItalic.woff2',
            // 'fonts/Bookerly/BookerlyDisplay-Bold.eot',
            // 'fonts/Bookerly/BookerlyDisplay-Bold.ttf',
            // 'fonts/Bookerly/BookerlyDisplay-Bold.woff',
            // 'fonts/Bookerly/BookerlyDisplay-Bold.woff2',
            // 'fonts/Bookerly/BookerlyDisplay-BoldItalic.eot',
            // 'fonts/Bookerly/BookerlyDisplay-BoldItalic.ttf',
            // 'fonts/Bookerly/BookerlyDisplay-BoldItalic.woff',
            // 'fonts/Bookerly/BookerlyDisplay-BoldItalic.woff2',
            // 'fonts/Bookerly/BookerlyDisplay-Italic.eot',
            // 'fonts/Bookerly/BookerlyDisplay-Italic.ttf',
            // 'fonts/Bookerly/BookerlyDisplay-Italic.woff',
            // 'fonts/Bookerly/BookerlyDisplay-Italic.woff2',
            // 'fonts/Bookerly/BookerlyDisplay-Regular.eot',
            // 'fonts/Bookerly/BookerlyDisplay-Regular.ttf',
            // 'fonts/Bookerly/BookerlyDisplay-Regular.woff',
            // 'fonts/Bookerly/BookerlyDisplay-Regular.woff2',
            // 'fonts/Bookerly/Bookerly-Italic.eot',
            // 'fonts/Bookerly/Bookerly-Italic.ttf',
            // 'fonts/Bookerly/Bookerly-Italic.woff',
            // 'fonts/Bookerly/Bookerly-Italic.woff2',
            // 'fonts/Bookerly/Bookerly-Italic_1.eot',
            // 'fonts/Bookerly/Bookerly-Italic_1.ttf',
            // 'fonts/Bookerly/Bookerly-Italic_1.woff',
            // 'fonts/Bookerly/Bookerly-Italic_1.woff2',
            // 'fonts/Bookerly/Bookerly-Light.eot',
            // 'fonts/Bookerly/Bookerly-Light.ttf',
            // 'fonts/Bookerly/Bookerly-Light.woff',
            // 'fonts/Bookerly/Bookerly-Light.woff2',
            // 'fonts/Bookerly/Bookerly-LightItalic.eot',
            // 'fonts/Bookerly/Bookerly-LightItalic.ttf',
            // 'fonts/Bookerly/Bookerly-LightItalic.woff',
            // 'fonts/Bookerly/Bookerly-LightItalic.woff2',
            // 'fonts/Bookerly/Bookerly-LightItalic_1.eot',
            // 'fonts/Bookerly/Bookerly-LightItalic_1.ttf',
            // 'fonts/Bookerly/Bookerly-LightItalic_1.woff',
            // 'fonts/Bookerly/Bookerly-LightItalic_1.woff2',
            // 'fonts/Bookerly/Bookerly-Regular.eot',
            // 'fonts/Bookerly/Bookerly-Regular.ttf',
            // 'fonts/Bookerly/Bookerly-Regular.woff',
            // 'fonts/Bookerly/Bookerly-Regular.woff2',
            // 'fonts/GeorgiaPro/georgia-pro.css',
            // 'fonts/GeorgiaPro/GeorgiaPro-Black.eot',
            // 'fonts/GeorgiaPro/GeorgiaPro-Black.ttf',
            // 'fonts/GeorgiaPro/GeorgiaPro-Black.woff',
            // 'fonts/GeorgiaPro/GeorgiaPro-Black.woff2',
            // 'fonts/GeorgiaPro/GeorgiaPro-BlackItalic.eot',
            // 'fonts/GeorgiaPro/GeorgiaPro-BlackItalic.ttf',
            // 'fonts/GeorgiaPro/GeorgiaPro-BlackItalic.woff',
            // 'fonts/GeorgiaPro/GeorgiaPro-BlackItalic.woff2',
            // 'fonts/GeorgiaPro/GeorgiaPro-Bold.eot',
            // 'fonts/GeorgiaPro/GeorgiaPro-Bold.ttf',
            // 'fonts/GeorgiaPro/GeorgiaPro-Bold.woff',
            // 'fonts/GeorgiaPro/GeorgiaPro-Bold.woff2',
            // 'fonts/GeorgiaPro/GeorgiaPro-BoldItalic.eot',
            // 'fonts/GeorgiaPro/GeorgiaPro-BoldItalic.ttf',
            // 'fonts/GeorgiaPro/GeorgiaPro-BoldItalic.woff',
            // 'fonts/GeorgiaPro/GeorgiaPro-BoldItalic.woff2',
            // 'fonts/GeorgiaPro/GeorgiaPro-CondBlack.eot',
            // 'fonts/GeorgiaPro/GeorgiaPro-CondBlack.ttf',
            // 'fonts/GeorgiaPro/GeorgiaPro-CondBlack.woff',
            // 'fonts/GeorgiaPro/GeorgiaPro-CondBlack.woff2',
            // 'fonts/GeorgiaPro/GeorgiaPro-CondBlackItalic.eot',
            // 'fonts/GeorgiaPro/GeorgiaPro-CondBlackItalic.ttf',
            // 'fonts/GeorgiaPro/GeorgiaPro-CondBlackItalic.woff',
            // 'fonts/GeorgiaPro/GeorgiaPro-CondBlackItalic.woff2',
            // 'fonts/GeorgiaPro/GeorgiaPro-CondBold.eot',
            // 'fonts/GeorgiaPro/GeorgiaPro-CondBold.ttf',
            // 'fonts/GeorgiaPro/GeorgiaPro-CondBold.woff',
            // 'fonts/GeorgiaPro/GeorgiaPro-CondBold.woff2',
            // 'fonts/GeorgiaPro/GeorgiaPro-CondBoldItalic.eot',
            // 'fonts/GeorgiaPro/GeorgiaPro-CondBoldItalic.ttf',
            // 'fonts/GeorgiaPro/GeorgiaPro-CondBoldItalic.woff',
            // 'fonts/GeorgiaPro/GeorgiaPro-CondBoldItalic.woff2',
            // 'fonts/GeorgiaPro/GeorgiaPro-CondItalic.eot',
            // 'fonts/GeorgiaPro/GeorgiaPro-CondItalic.ttf',
            // 'fonts/GeorgiaPro/GeorgiaPro-CondItalic.woff',
            // 'fonts/GeorgiaPro/GeorgiaPro-CondItalic.woff2',
            // 'fonts/GeorgiaPro/GeorgiaPro-CondLight.eot',
            // 'fonts/GeorgiaPro/GeorgiaPro-CondLight.ttf',
            // 'fonts/GeorgiaPro/GeorgiaPro-CondLight.woff',
            // 'fonts/GeorgiaPro/GeorgiaPro-CondLight.woff2',
            // 'fonts/GeorgiaPro/GeorgiaPro-CondLightItalic.eot',
            // 'fonts/GeorgiaPro/GeorgiaPro-CondLightItalic.ttf',
            // 'fonts/GeorgiaPro/GeorgiaPro-CondLightItalic.woff',
            // 'fonts/GeorgiaPro/GeorgiaPro-CondLightItalic.woff2',
            // 'fonts/GeorgiaPro/GeorgiaPro-CondRegular.eot',
            // 'fonts/GeorgiaPro/GeorgiaPro-CondRegular.ttf',
            // 'fonts/GeorgiaPro/GeorgiaPro-CondRegular.woff',
            // 'fonts/GeorgiaPro/GeorgiaPro-CondRegular.woff2',
            // 'fonts/GeorgiaPro/GeorgiaPro-CondSemibold.eot',
            // 'fonts/GeorgiaPro/GeorgiaPro-CondSemibold.ttf',
            // 'fonts/GeorgiaPro/GeorgiaPro-CondSemibold.woff',
            // 'fonts/GeorgiaPro/GeorgiaPro-CondSemibold.woff2',
            // 'fonts/GeorgiaPro/GeorgiaPro-CondSemiboldItalic.eot',
            // 'fonts/GeorgiaPro/GeorgiaPro-CondSemiboldItalic.ttf',
            // 'fonts/GeorgiaPro/GeorgiaPro-CondSemiboldItalic.woff',
            // 'fonts/GeorgiaPro/GeorgiaPro-CondSemiboldItalic.woff2',
            // 'fonts/GeorgiaPro/GeorgiaPro-Italic.eot',
            // 'fonts/GeorgiaPro/GeorgiaPro-Italic.ttf',
            // 'fonts/GeorgiaPro/GeorgiaPro-Italic.woff',
            // 'fonts/GeorgiaPro/GeorgiaPro-Italic.woff2',
            // 'fonts/GeorgiaPro/GeorgiaPro-Light.eot',
            // 'fonts/GeorgiaPro/GeorgiaPro-Light.ttf',
            // 'fonts/GeorgiaPro/GeorgiaPro-Light.woff',
            // 'fonts/GeorgiaPro/GeorgiaPro-Light.woff2',
            // 'fonts/GeorgiaPro/GeorgiaPro-LightItalic.eot',
            // 'fonts/GeorgiaPro/GeorgiaPro-LightItalic.ttf',
            // 'fonts/GeorgiaPro/GeorgiaPro-LightItalic.woff',
            // 'fonts/GeorgiaPro/GeorgiaPro-LightItalic.woff2',
            // 'fonts/GeorgiaPro/GeorgiaPro-Regular.eot',
            // 'fonts/GeorgiaPro/GeorgiaPro-Regular.ttf',
            // 'fonts/GeorgiaPro/GeorgiaPro-Regular.woff',
            // 'fonts/GeorgiaPro/GeorgiaPro-Regular.woff2',
            // 'fonts/GeorgiaPro/GeorgiaPro-Semibold.eot',
            // 'fonts/GeorgiaPro/GeorgiaPro-Semibold.ttf',
            // 'fonts/GeorgiaPro/GeorgiaPro-Semibold.woff',
            // 'fonts/GeorgiaPro/GeorgiaPro-Semibold.woff2',
            // 'fonts/GeorgiaPro/GeorgiaPro-SemiboldItalic.eot',
            // 'fonts/GeorgiaPro/GeorgiaPro-SemiboldItalic.ttf',
            // 'fonts/GeorgiaPro/GeorgiaPro-SemiboldItalic.woff',
            // 'fonts/GeorgiaPro/GeorgiaPro-SemiboldItalic.woff2',
            // 'fonts/OpenSans/open-sans.css',
            // 'fonts/OpenSans/OpenSans-Bold.eot',
            // 'fonts/OpenSans/OpenSans-Bold.ttf',
            // 'fonts/OpenSans/OpenSans-Bold.woff',
            // 'fonts/OpenSans/OpenSans-Bold.woff2',
            // 'fonts/OpenSans/OpenSans-BoldItalic.eot',
            // 'fonts/OpenSans/OpenSans-BoldItalic.ttf',
            // 'fonts/OpenSans/OpenSans-BoldItalic.woff',
            // 'fonts/OpenSans/OpenSans-BoldItalic.woff2',
            // 'fonts/OpenSans/OpenSans-ExtraBold.eot',
            // 'fonts/OpenSans/OpenSans-ExtraBold.ttf',
            // 'fonts/OpenSans/OpenSans-ExtraBold.woff',
            // 'fonts/OpenSans/OpenSans-ExtraBold.woff2',
            // 'fonts/OpenSans/OpenSans-ExtraBoldItalic.eot',
            // 'fonts/OpenSans/OpenSans-ExtraBoldItalic.ttf',
            // 'fonts/OpenSans/OpenSans-ExtraBoldItalic.woff',
            // 'fonts/OpenSans/OpenSans-ExtraBoldItalic.woff2',
            // 'fonts/OpenSans/OpenSans-Italic.eot',
            // 'fonts/OpenSans/OpenSans-Italic.ttf',
            // 'fonts/OpenSans/OpenSans-Italic.woff',
            // 'fonts/OpenSans/OpenSans-Italic.woff2',
            // 'fonts/OpenSans/OpenSans-Light.eot',
            // 'fonts/OpenSans/OpenSans-Light.ttf',
            // 'fonts/OpenSans/OpenSans-Light.woff',
            // 'fonts/OpenSans/OpenSans-Light.woff2',
            // 'fonts/OpenSans/OpenSans-LightItalic.eot',
            // 'fonts/OpenSans/OpenSans-LightItalic.ttf',
            // 'fonts/OpenSans/OpenSans-LightItalic.woff',
            // 'fonts/OpenSans/OpenSans-LightItalic.woff2',
            // 'fonts/OpenSans/OpenSans-Regular.eot',
            // 'fonts/OpenSans/OpenSans-Regular.ttf',
            // 'fonts/OpenSans/OpenSans-Regular.woff',
            // 'fonts/OpenSans/OpenSans-Regular.woff2',
            // 'fonts/OpenSans/OpenSans-SemiBold.eot',
            // 'fonts/OpenSans/OpenSans-SemiBold.ttf',
            // 'fonts/OpenSans/OpenSans-SemiBold.woff',
            // 'fonts/OpenSans/OpenSans-SemiBold.woff2',
            // 'fonts/OpenSans/OpenSans-SemiBoldItalic.eot',
            // 'fonts/OpenSans/OpenSans-SemiBoldItalic.ttf',
            // 'fonts/OpenSans/OpenSans-SemiBoldItalic.woff',
            // 'fonts/OpenSans/OpenSans-SemiBoldItalic.woff2',
            'fonts',
            'cover.jpg',
            'cover-ibis.webp',
            'theme-dark.html',
            'theme-light.html',
            'style.css',
            'style-epub.css',
            'highlight.codeblock.min.css',
            'theme-html.html',
            'images/aside-examples.png',
            'images/ibis-next-cover.png',
            'images/ibis-next-setting-page-header.png',
        ];
        $dirAssetsStubs = Config::buildPath(
            __DIR__,
            '..',
            '..',
            'stubs/assets',
        );
        Config::buildPath(
            $dirAssetsStubs,
            'images',
            'fonts',
        );


        foreach ($assetsToCopy as $asset) {

            $assetStub = Config::buildPath(
                $dirAssetsStubs,
                $asset,
            );
            if (is_dir($assetStub)) {
				$io->text(sprintf("✨ Copying '%s' directory.", $asset));
				$this->copyDirectory($assetStub, Config::buildPath($assetsPath, $asset));
			}elseif(is_file($assetStub)) {
                copy(
                    $assetStub,
                    Config::buildPath($assetsPath, $asset),
                );
            } else {
                $io->warning(sprintf("File '%s' not found. I will skip this file.", $asset));
            }
        }

        $io->text('✨ content directory as:');
        $io->text('    ' . $contentPath . '');

        $this->disk->makeDirectory(
            $contentPath,
        );

        $this->disk->copyDirectory(
            Config::buildPath(
                __DIR__,
                '../../stubs/content',
            ),
            $contentPath,
        );

        $io->text('✨ Config file:');
        $io->text('    ' . $ibisConfigPath);

        $this->disk->put(
            $ibisConfigPath,
            $this->disk->get(Config::buildPath(
                __DIR__,
                '../../stubs/ibis.php',
            )),
        );

        $io->newLine();
        $io->success('✅ Done!');
        $io->note(
            'You can start building your content (markdown files) into the directory ' . $contentPath . PHP_EOL .
            "You can change the configuration, for example by changing the title, the cover etc. editing the file " . $ibisConfigPath,
        );

        return Command::SUCCESS;
    }
}
