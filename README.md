# php-code-analyzer
Static code analyzer for php written in php.

To execute type this in your console in the project root directory:

    $ php public/index.php run path/to/php-files

As an example, you can use the code in data/code:

    $ php public/index.php run data/code 

To ignore folders or files you can specify a comma separated list of strings that will be matched into the complete filepath. If one of the strings matches, the file/folder will be ignored.
In this example all files in the folder "model" will be ignored:

    $ php public/index.php run --ignore="model/" data/code

To get a report of the found results type:

    $ php public/index.php report

