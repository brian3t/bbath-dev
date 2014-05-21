<?php
class SimpleXMLExtended extends SimpleXMLElement
{
  public function addCData($cdata_text)
  {
    $node= dom_import_simplexml($this);
    $no = $node->ownerDocument;
    $node->appendChild($no->createCDATASection($cdata_text));
  }
}
/**
 * @author  Tony Tomov, (tony@trirand.com)
 * @copyright TriRand Ltd
 * @package jqGrid
 * @abstract Helper functions for the jqGrid package
 */
class jqGridUtils
{
    /**
     * Function for converting to an XML document.
     * Pass in a multi dimensional array or object and this recrusively loops through and builds up an XML document.
     *
     * @param array $data
     * @param string $rootNodeName - what you want the root node to be - defaultsto data.
     * @param SimpleXMLElement $xml - should only be used recursively
     * @return string XML
     */
    public static function toXml($data, $rootNodeName = 'root', $xml=null, $encoding='utf-8', $cdata=false)
    {
        // turn off compatibility mode as simple xml throws a wobbly if you don't.
        if (ini_get('zend.ze1_compatibility_mode') == 1)
        {
            ini_set ('zend.ze1_compatibility_mode', 0);
        }

        if ($xml == null)
        {
            $xml = new SimpleXMLExtended("<?xml version='1.0' encoding='".$encoding."'?><$rootNodeName />");
        }

        // loop through the data passed in.
        foreach($data as $key => $value)
        {
            // no numeric keys in our xml please!
            if (is_numeric($key))
            {
                // make string key...
                //return;
                $key = "row";
            }
            // if there is another array or object found recrusively call this function
            if (is_array($value) || is_object($value))
            {
                $node = $xml->addChild($key);
                // recrusive call.
                self::toXml($value, $rootNodeName, $node, $encoding, $cdata);
            }
            else
            {
                // add single node.
                $value = htmlspecialchars($value);
                if($cdata===true) {
                    $node = $xml->addChild($key);
                    $node->addCData($value);
                } else {
                    $xml->addChild($key,$value);
                }
            }

        }
        // pass back as string. or simple xml object if you want!
        return $xml->asXML();
    }
    /**
     * Quotes a javascript string.
     * After processing, the string can be safely enclosed within a pair of
     * quotation marks and serve as a javascript string.
     * @param string string to be quoted
     * @param boolean whether this string is used as a URL
     * @return string the quoted string
     */
    public static function quote($js,$forUrl=false)
    {
        if($forUrl)
            return strtr($js,array('%'=>'%25',"\t"=>'\t',"\n"=>'\n',"\r"=>'\r','"'=>'\"','\''=>'\\\'','\\'=>'\\\\'));
        else
            return strtr($js,array("\t"=>'\t',"\n"=>'\n',"\r"=>'\r','"'=>'\"','\''=>'\\\'','\\'=>'\\\\',"'"=>'\''));
    }

    /**
     * Encodes a PHP variable into javascript representation.
     *
     * Example:
     * <pre>
     * $options=array('key1'=>true,'key2'=>123,'key3'=>'value');
     * echo jqGridUtils::encode($options);
     * // The following javascript code would be generated:
     * // {'key1':true,'key2':123,'key3':'value'}
     * </pre>
     *
     * For highly complex data structures use {@link jsonEncode} and {@link jsonDecode}
     * to serialize and unserialize.
     *
     * @param mixed PHP variable to be encoded
     * @return string the encoded string
     */
    public static function encode($value)
    {
        if(is_string($value))
        {
            if(strpos($value,'js:')===0)
                return substr($value,3);
            else
                return '"'.self::quote($value).'"';
        }
        else if($value===null)
            return "null";
        else if(is_bool($value))
            return $value?"true":"false";
        else if(is_integer($value))
            return "$value";
        else if(is_float($value))
        {
            if($value===-INF)
                return 'Number.NEGATIVE_INFINITY';
            else if($value===INF)
                return 'Number.POSITIVE_INFINITY';
            else
                return "$value";
        }
        else if(is_object($value))
            return self::encode(get_object_vars($value));
        else if(is_array($value))
        {
            $es=array();
            if(($n=count($value))>0 && array_keys($value)!==range(0,$n-1))
            {
                foreach($value as $k=>$v)
                    $es[]='"'.self::quote($k).'":'.self::encode($v);
                return "{".implode(',',$es)."}";
            }
            else
            {
                foreach($value as $v)
                    $es[]=self::encode($v);
                return "[".implode(',',$es)."]";
            }
        }
        else
            return "";
    }
    /**
     *
     * Decodes json string to PHP array. The function is used
     * when the encoding is diffrent from utf-8
     * @param string $json string to decode
     * @return array
     */
    public static function decode($json)
    {
        $comment = false;
        $out = '$x=';

        for ($i=0; $i<strlen($json); $i++)
        {
            if (!$comment)
            {
                if ($json[$i] == '{')        $out .= ' array(';
                else if ($json[$i] == '}')    $out .= ')';
                else if ($json[$i] == '[')        $out .= ' array(';
                else if ($json[$i] == ']')    $out .= ')';
                else if ($json[$i] == ':')    $out .= '=>';
                else                         $out .= $json[$i];
            }
            else $out .= $json[$i];
            if ($json[$i] == '"')    $comment = !$comment;
        }
        eval($out . ';');
        return $x;
    }
    /**
     * Strip slashes from a varaible if PHP magic quotes are on
     * @param mixed $value to be striped
     * @return mixed
     */
    public static function Strip($value)
    {
        if(get_magic_quotes_gpc() != 0)
        {
            if(is_array($value))
                // is associative array
                if ( 0 !== count(array_diff_key($value, array_keys(array_keys($value)))) )
                {
                    foreach( $value as $k=>$v)
                        $tmp_val[$k] = stripslashes($v);
                    $value = $tmp_val;
                }
                else
                    for($j = 0; $j < sizeof($value); $j++)
                        $value[$j] = stripslashes($value[$j]);
            else
                $value = stripslashes($value);
        }
        return $value;
    }
    /**
     * Parses a $format and $date value and return the date formated via $newformat.
     * If the $newformat is not set return the timestamp. Support only numeric
     * date format as input, but the $new format can be any valid PHP date format
     * @param string $format the format of the date to be parsed
     * @param string $date the value of the data.
     * @param string $newformat the new format of the $date
     * @return mixed
     */
    public static function parseDate($format, $date, $newformat = '')
    {
        // Flag init
        $m = 1; $d = 1; $y = 1970; $h = 0; $i = 0; $s = 0;

        $format = trim(strtolower($format));
        $date = trim($date);
        $sep = '([\\\/:_;.\s-]{1})';

        $date   = preg_split($sep, $date);
        $format = preg_split($sep, $format);

        foreach($format as $key => $formatDate) {

        //  only numeric format as source
            if(isset ($date[$key])) {
                if(!preg_match('`^([0-9]{1,4})$`', $date[$key])) {
                    return FALSE;
                }
                $$formatDate = $date[$key];
            }
        }
        // prepare the time stamp
        $timestamp = mktime($h, $i, $s, $m, $d, $y);
        // return the value if @newformat is set
        if($newformat) return date($newformat, $timestamp);
        // else return the timestamp.
        return (integer)$timestamp;
    }
    /**
     * Return the value from POST or from GET
     * @param string $parameter_name
     * @param string $default_value
     * @return mixed
     */
    public static function GetParam($parameter_name, $default_value = "")
    {
        $parameter_value = "";
        if(isset($_POST[$parameter_name]))
            $parameter_value = self::Strip($_POST[$parameter_name]);
        else if(isset($_GET[$parameter_name]))
            $parameter_value = self::Strip($_GET[$parameter_name]);
        else
            $parameter_value = $default_value;
        return $parameter_value;
    }
    /**
     * "Extend" recursively array $a with array $b values (no deletion in $a, just added and updated values)
     * @param array $a
     * @param array $b
     * @return array
     */
    public static function array_extend($a, $b) {
        foreach($b as $k=>$v) {
            if( is_array($v) ) {
                if( !isset($a[$k]) ) {
                    $a[$k] = $v;
                } else {
                    $a[$k] = self::array_extend($a[$k], $v);
                }
            } else {
                $a[$k] = $v;
            }
        }
        return $a;
    }

    /**
     * Convert the php date string to Java Script date string
     * @param string $phpdate
     */
    public static function phpTojsDate ($phpdate)
    {
/*
 * Java Script
d  - day of month (no leading zero)
dd - day of month (two digit)
o  - day of year (no leading zeros)
oo - day of year (three digit)
D  - day name short
DD - day name long

m  - month of year (no leading zero)
mm - month of year (two digit)
M  - month name short
MM - month name long

y  - year (two digit)
yy - year (four digit)
*/

/* PHP
j - Day of the month without leading zeros
d - Day of the month, 2 digits with leading zeros
z - The day of the year (starting from 0)
no item found
D - A textual representation of a day, three letters
l - A full textual representation of the day of the week
 *
 n - Numeric representation of a month, without leading zeros
 m - Numeric representation of a month, with leading zeros
 M - A short textual representation of a month, three letters
 F - A full textual representation of a month, such as January or March
 *
 y - A two digit representation of a year
 Y - A full numeric representation of a year, 4 digits
 */
        //$str = $phpdate;
        str_replace('j', 'd', $phpdate);
        str_replace('d', 'dd', $phpdate);
        str_replace('z', 'o', $phpdate);
        str_replace('l', 'DD', $phpdate);

        str_replace('m', 'mm', $phpdate);
        str_replace('n', 'm', $phpdate);
        str_replace('F', 'MM', $phpdate);

        str_replace('Y', 'yy', $phpdate);

    }
}

?>