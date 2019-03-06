<script>
    testcase = {
        "preconditions": {
            "sourceName": "definition-class-global.php"
        },
        "expectations": {
            "classDefinitions": {
                "foundClasses": [
                    {
                        "fqn": ["Foo"],
                        "type": "class"
                    }
                ]
            }
        }
    }
</script>
<?php

class Foo
{
    public function __construct(Bar $bar)
    {
        echo $bar;
    }
}
