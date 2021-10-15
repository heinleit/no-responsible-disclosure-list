<?php
namespace Dallgoot\Yaml;

use Dallgoot\Yaml\Dumper;

/**
 *  Convert PHP datatypes to a YAML string syntax
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    https://github.com/dallgoot/yaml
 */
class DumperHandlers
{
	
	private const ESCAPEES = ['\\', '\\\\', '\\"', '"',
                                     "\x00",  "\x01",  "\x02",  "\x03",  "\x04",  "\x05",  "\x06",  "\x07",
                                     "\x08",  "\x09",  "\x0a",  "\x0b",  "\x0c",  "\x0d",  "\x0e",  "\x0f",
                                     "\x10",  "\x11",  "\x12",  "\x13",  "\x14",  "\x15",  "\x16",  "\x17",
                                     "\x18",  "\x19",  "\x1a",  "\x1b",  "\x1c",  "\x1d",  "\x1e",  "\x1f",
                                     "\x7f",
                                     "\xc2\x85", "\xc2\xa0", "\xe2\x80\xa8", "\xe2\x80\xa9",
                               ];
    private const ESCAPED = ['\\\\', '\\"', '\\\\', '\\"',
                                     '\\0',   '\\x01', '\\x02', '\\x03', '\\x04', '\\x05', '\\x06', '\\a',
                                     '\\b',   '\\t',   '\\n',   '\\v',   '\\f',   '\\r',   '\\x0e', '\\x0f',
                                     '\\x10', '\\x11', '\\x12', '\\x13', '\\x14', '\\x15', '\\x16', '\\x17',
                                     '\\x18', '\\x19', '\\x1a', '\\e',   '\\x1c', '\\x1d', '\\x1e', '\\x1f',
                                     '\\x7f',
                                     '\\N', '\\_', '\\L', '\\P',
                              ];
							  
    private $dumper;

    public function __construct(Dumper $dumper)
    {
        $this->dumper = $dumper;
    }


    public function dumpScalar($dataType):string
    {
        if ($dataType === \INF) return '.inf';
        if ($dataType === -\INF) return '-.inf';
        $precision = "%.".$this->dumper->floatPrecision."F";
        switch (gettype($dataType)) {
            case 'boolean': return $dataType ? 'true' : 'false';
            case 'float': //fall through
            case 'double': return is_nan((double) $dataType) ? '.nan' : sprintf($precision, $dataType);
        }
        return $this->dumpString($dataType);
    }


    public function dumpCompound($compound, int $indent, bool $parentArray = false):string
    {
        if ($this->dumper->_compactMode) {
            return $this->dumpCompact($compound, $indent);
        } else {
            if (is_array($compound)) {
                if ($compound[0] instanceof YamlObject) {
                    return $this->dumper->dumpMultiDoc($compound);
                }
                $iterator = new \ArrayIterator($compound);
                $keyMask = '-';
                $refKeys = range(0, count($compound) - 1);
                if (array_keys($compound) !== $refKeys) {
                    $keyMask = '%s:';
                }
                return $this->dumper->iteratorToString($iterator, $keyMask, "\n", $indent, true, $parentArray);
            } elseif (is_object($compound) && !is_callable($compound)) {
                return $this->dumpObject($compound, $indent, $parentArray);
            }
        }
        throw new \Exception("Dumping Callable|Resource is not currently supported", 1);
    }

    private function dumpObject($object, int $indent, bool $parentArray = false):string
    {
        if ($object instanceof YamlObject) {
            return $this->dumper->dumpYamlObject($object);
        } elseif ($object instanceof Compact) {
            return $this->dumpCompact($object, $indent);
        } elseif ($object instanceof Tagged) {
            return $this->dumpTagged($object, $indent);
        } elseif ($object instanceof \DateTime) {
            return $object->format($this->dumper::DATE_FORMAT);
        } elseif (is_iterable($object)) {
            $iterator = $object;
        } else {
            $iterator = new \ArrayIterator(get_object_vars($object));
        }
		
        return $this->dumper->iteratorToString($iterator, '%s:', "\n", $indent, false, $parentArray);
    }



    /**
     * Dumps a Compact|mixed (representing an array or object) as the single-line format representation.
     * All values inside are assumed single-line as well.
     * Note: can NOT use JSON_encode because of possible reference calls or definitions as : '&abc 123', '*fre'
     * which would be quoted by json_encode
     *
     * @param mixed   $subject The subject
     * @param integer $indent  The indent
     *
     * @return string the string representation (JSON like) of the value
     */
    public function dumpCompact($subject, int $indent):string
    {
        $structureFormat = '{%s}';
        $keyMask = "%s: ";
        if (!is_array($subject) && !($subject instanceof \ArrayIterator)) {
            $source = get_object_vars($subject);
        } else {
            $max = count($subject);
            $objectAsArray = is_array($subject) ? $subject : $subject->getArrayCopy();
            $source = $objectAsArray;
            if (array_keys($objectAsArray) === range(0, $max - 1)) {
                $structureFormat = '[%s]';
                $keyMask = '';
            }
        }
        $previousCompactMode = $this->dumper->_compactMode;
        $this->dumper->_compactMode =  true;
        $result = $this->dumper->iteratorToString(new \ArrayIterator($source), $keyMask, ', ', $indent);
        $this->dumper->_compactMode = $previousCompactMode;
        return sprintf($structureFormat, $result);
    }
							  
    /**
     * Dumps a string. Protects it if needed
     *
     * @param      string  $str    The string
     *
     * @return     string  ( description_of_the_return_value )
     * @todo   implements checking and protection function
     */
    public function dumpString(string $str):string
    {
        //those characters must be escaped : - : ? { } [ ] # , & * ! > | ' " %
        // The “@” (#x40, at) and “`” (#x60, grave accent) are reserved for future use.
        // 5.4. Line Break Characters
        // Example 5.13. Escaped Characters

        //$str = json_encode(ltrim($str));
        //return strspn(substr($str,1,-1), "-:?{}[]#,&*!>|'\"%") > 0 ? $str : trim($str, '"');
		return str_replace(self::ESCAPEES, self::ESCAPED, $str);
    }

    public function dumpTagged(Tagged $obj, int $indent):string
    {
        $separator   = ' ';
        $valueIndent = 0;
        if (!is_scalar($obj->value) && !$this->dumper->_compactMode) {
            $separator = "\n";
            $valueIndent = $indent + $this->dumper::INDENT;
        }
        return $obj->tagName.$separator.$this->dumper->dump($obj->value, $valueIndent);
    }
}
