[![Build Status](https://scrutinizer-ci.com/g/alexanderduring/php-code-analyzer/badges/build.png?b=master)](https://scrutinizer-ci.com/g/alexanderduring/php-code-analyzer/build-status/master) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/alexanderduring/php-code-analyzer/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/alexanderduring/php-code-analyzer/?branch=master)

# PHP Code Analyzer

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

If you call the IndexController via the browser you get a very debug-like dump of found usages of instantiations.
