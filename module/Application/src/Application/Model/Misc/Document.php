<?php

namespace Misc;

/**
 * The class Document should be a replacement for hierarchical, indexed array.
 *
 * The limitations of indexed arrays are the following:
 *
 * 1) You want to know, if $array['foo']['bar']['baz'] == true
 *    but it is possible that this entry does not even exist.
 *
 * 2) You have an $array like in the example above, but you have a second array
 *    $keys = array('foo', 'bar', 'baz') and you want to do the same check like in 1)
 *
 * 3) You unset $array['foo']['bar']['baz']. If 'baz' was the only key left in 'bar'
 *    'bar' should be unset too. The same for 'foo'.
 *
 * 4) Like 3) with a keys array like in 2)
 *
 * 5) You want to get a handler to $array['foo']['bar']['baz'] like
 *
 *    $handler = $array['foo']['bar']['baz']
 *    if ($a > 0) {
 *        $handler->set($a);
 *    }
 *
 */
class Document
{

}