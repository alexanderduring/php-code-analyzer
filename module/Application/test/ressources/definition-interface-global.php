<script>
    testcase = {
        "preconditions": {
            "sourceName": "definition-interface-global.php"
        },
        "expectations": {
            "classDefinitions": {
                "foundClasses": [
                    {
                        "fqn": ["Bar"],
                        "type": "interface"
                    }
                ]
            }
        }
    }
</script>
<?php

interface Bar
{
    public function __construct(Bar $bar);
}
