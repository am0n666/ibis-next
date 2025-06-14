<?php

namespace Ibis\Commands;

use Ibis\Config;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use PHPePub\Core\EPub;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;

class BuildEpubCommand extends BaseBuildCommand
{
    /**
     * Configure the command.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('epub')

            ->addOption(
                'content',
                'c',
                InputOption::VALUE_OPTIONAL,
                'The path of the content directory',
                '',
            )
            ->addOption(
                'workingdir',
                'd',
                InputOption::VALUE_OPTIONAL,
                'The path of the working directory where `ibis.php` and `assets` directory are located',
                '',
            )
            ->setDescription('Generate the book in EPUB format.');
    }

    /**
     * Execute the command.
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @throws \Mpdf\MpdfException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->output = $output;
        $this->output->writeln('<info>✨ Building EPUB file ✨</info>');

        if (!$this->preExecute($input, $output)) {
            return Command::INVALID;
        }


        $this->ensureExportDirectoryExists($this->config->workingPath);


        $this->config->config["breakLevel"] = 1;
        $result = $this->buildEpub(
            $this->buildHtml($this->config->contentPath, $this->config->config, extractImages: true),
            $this->config->config,
            $this->config->workingPath,
        );

        $this->output->writeln('');
        if ($result) {

            $this->output->writeln('<info>Book Built Successfully!</info>');
        } else {
            $this->output->writeln('<error>Book Built Failed!</error>');
        }


        return Command::SUCCESS;
    }


    /**
     * @throws FileNotFoundException
     */
    protected function buildEpub(
        Collection $chapters,
        array $config,
        string $currentPath,
    ): bool {

        $content_start =
        "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"
        . "<html xmlns=\"http://www.w3.org/1999/xhtml\" xmlns:epub=\"http://www.idpf.org/2007/ops\">\n"
        . "<head>"
        . "<meta http-equiv=\"Default-Style\" content=\"text/html; charset=utf-8\" />\n"
        . "<link rel=\"stylesheet\" type=\"text/css\" href=\"codeblock.css\" />\n"
        . "<link rel=\"stylesheet\" type=\"text/css\" href=\"style.css\" />\n"
        . "<title>" . $this->config->title() . "</title>\n"
        . "</head>\n"
        . "<body>\n";
        $content_end = "</body></html>";

        $book = new EPub(EPub::BOOK_VERSION_EPUB3, "en", EPub::DIRECTION_LEFT_TO_RIGHT);
        $book->setIdentifier(md5($this->config->title() . " - " . $this->config->author()), EPub::IDENTIFIER_UUID);
        $book->setLanguage("en");
        $book->setDescription($this->config->title() . " - " . $this->config->author());
        $book->setTitle($this->config->title());
        //$book->setPublisher("John and Jane Doe Publications", "http://JohnJaneDoePublications.com/");
        $book->setAuthor($this->config->author(), $this->config->author());
        $book->setIdentifier($this->config->title() . "&amp;stamp=" . time(), EPub::IDENTIFIER_URI);
        //$book->setLanguage("en");

        $book->addCSSFile("style.css", "css1", $this->getStyle($this->config->workingPath, "style"));
        // https://cdn.jsdelivr.net/gh/highlightjs/cdn-release@9.18.5/build/styles/github-gist.min.css
        $book->addCSSFile(
            "codeblock.css",
            "css2",
            $this->getStyle($this->config->workingPath, "highlight.codeblock.min"),
        );

        $cover = $content_start . "<h1>" . $this->config->title() . "</h1>\n";
        if ($this->config->author()) {
            $cover .= "<h2>By: " . $this->config->author() . "</h2>\n";
        }

        $cover .= $content_end;
        $coverImage = "cover.jpg";
        if (array_key_exists("image", $config['cover'])) {
            $coverImage = $config['cover']['image'];
        }

        $pathCoverImage = Config::buildPath($currentPath, 'assets', $coverImage);
        if ($this->disk->isFile($pathCoverImage)) {
            $this->output->writeln('<fg=yellow>==></> Adding Book Cover ' . $pathCoverImage . ' ...');

            $coverPosition = $config['cover']['position'] ?? 'position: absolute; left:0; right: 0; top: -.2; bottom: 0;';
            $coverDimensions = $config['cover']['dimensions'] ?? 'width: 210mm; height: 297mm; margin: 0;';
            $book->setCoverImage($coverImage, file_get_contents($pathCoverImage), mime_content_type($pathCoverImage));
        }

        $book->addChapter("Cover", "Cover.html", $cover);
        $book->addChapter("Table of Contents", "TOC.xhtml", null, false, EPub::EXTERNAL_REF_IGNORE);

        /*
        $book->addFileToMETAINF("com.apple.ibooks.display-options.xml", "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<display_options>\n    <platform name=\"*\">\n        <option name=\"fixed-layout\">true</option>\n        <option name=\"interactive\">true</option>\n        <option name=\"specified-fonts\">true</option>\n    </platform>\n</display_options>");
        */
        $book->addCustomNamespace("dc", "http://purl.org/dc/elements/1.1/"); // StaticData::$namespaces["dc"]);


        foreach ($chapters as $key => $chapter) {
            $this->output->writeln('<fg=yellow>==></> ❇️ ' . $chapter["mdfile"] . ' ...');

            // fixing html
            $chapter["html"] = str_replace("</span> <span", "</span>&nbsp;<span", $chapter["html"]);
            $book->addChapter(
                chapterName: Arr::get($chapter, "frontmatter.title", "Chapter " . ($key + 1)),
                fileName: "Chapter" . $key . ".html",
                chapterData: $content_start . $chapter["html"] . $content_end,
                externalReferences: EPub::EXTERNAL_REF_ADD,
            );
            foreach (Arr::get($chapter, "images", []) as $idxImage => $markdownPathImage) {
                if (filter_var($markdownPathImage, FILTER_VALIDATE_URL)) {
                    continue;
                }

                $pathImage = $markdownPathImage;
                if (! $this->isAbsolutePath($markdownPathImage)) {
                    $pathImage = $this->config->contentPath . "/" . $markdownPathImage;
                }

                if (!file_exists($pathImage)) {
                    continue;
                }

                $book->addLargeFile(
                    $markdownPathImage,
                    "image-" . $key . "-" . $idxImage,
                    $pathImage,
                    mime_content_type($pathImage),
                );
            }

            //file_put_contents('export/' . "Chapter" . $key . " .html", $content_start . $chapter["html"] . $content_end);
        }

        $this->addFonts(
            book: $book,
            currentPath: $currentPath,
        );

        $book->buildTOC(
            title: "Index",
            addReferences: false,
            addToIndex: false,
        );

        $book->finalize();

        $epubFilename = Config::buildPath(
            $currentPath,
            "export",
            $this->config->outputFileName() . '.epub',
        );
        @$book->saveBook($epubFilename);


        $this->output->writeln('<fg=green>==></> EPUB file ' . $epubFilename . ' created');
        return true;
    }

    /**
     * @param $currentPath
     * @param $themeName
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    private function getStyle(string $currentPath, string $themeName)
    {
        return $this->disk->get($currentPath . sprintf('/assets/%s.css', $themeName));
    }

    private function addFonts(EPub $book, string $currentPath): void
    {
        $fontsDir = Config::buildPath($currentPath, 'assets', 'fonts');
        if (! $this->disk->isDirectory($fontsDir)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($fontsDir));
        foreach ($iterator as $file) {
            if ($file->isDir()) {
                continue;
            }

            $path = $file->getPathname();
            $relative = ltrim(substr($path, strlen($fontsDir)), '/');
            $internalPath = 'fonts/' . $relative;

            $book->addLargeFile(
                $internalPath,
                'font-' . md5($internalPath),
                $path,
                mime_content_type($path),
            );
        }
    }


}
