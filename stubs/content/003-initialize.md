---
title: Initializing Your eBook
---

## Initializing the eBook

To get started, initialize your project directory using the `init` command. This command automatically creates the necessary configuration file, assets folder, and content folder for your Markdown files.

### Locally Installed Ibis Next

If you installed Ibis Next locally, launch the `init` command from your project directory:

~~~sh
./vendor/bin/ibis-next init
~~~

### Globally Installed Ibis Next
If you installed Ibis Next globally, run the `init` command inside an empty directory where you want to create your eBook:

~~~sh
ibis-next init
~~~

The `ibis-next init` command will generate the following files and directories:

- The `assets` directory, which contains theme files used for building the PDF and EPUB files. Since the process involves an intermediary step that temporarily converts the content to HTML, the theme files are in HTML and CSS to ensure the correct styling for the output files.
- The `assets/fonts` directory, where you can download and use your TrueType Font (TTF) files.
- The `assets/cover.jpg`, which is a sample book cover image.
- The `assets/images` directory, containing sample images used in the sample book.
- The `content` directory, which includes some sample Markdown files. You can edit these files or create new ones.
- The `ibis.php` file, which contains the Ibis Next configuration.

Configure your eBook by editing the `ibis.php` configuration file.

### Setting a specific directory

If you prefer to initialize a different empty directory (not the current one), use the `-d` option with the `init` command. For example:

~~~sh
ibis-next init -d ../some-other-directory
~~~

This is especially useful if you want to install Ibis Next once and manage multiple books in separate directories.
