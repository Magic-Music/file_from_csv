<?php
/**
 * The FfcModel is used by conversion scripts to supply 
 * parameters for the conversion. Scripts will instantiate 
 * an FfcModel, use the functions here to build the 
 * conversion, and return the model. 
 */

class FfcModel
{
    private $files = []; //[['input' => 'inputfilename','output' => 'outputfilename'],['input' => 'inputfilename','output' => 'outputfilename']]

    private $prefix = '';

    private $suffix = '';

    private $glue = '';

    private $statements=[];

    private $headerFunction;

    private $newline = true;

    public function addFilenames($input, $output = null)
    {
        if(is_array($input) && !$output) {
            $this->files = $input;
        } else {
            $this->files[] = [
                'input' => $input,
                'output' => $output
            ];
        }
        return $this;
    }

    public function files()
    {
        return $this->files;
    }

    public function addStatement($string)
    {
        $this->statements[] = $string;
        return $this;
    }

    public function statements()
    {
        return $this->statements;
    }

    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
        return $this;
    }

    public function prefix()
    {
        return $this->prefix;
    }

    public function setSuffix($suffix)
    {
        $this->suffix = $suffix;
        return $this;
    }

    public function suffix()
    {
        return $this->suffix;
    }

    public function addHeaderFunction($function)
    {
        $this->headerFunction=$function;
        return $this;
    }

    public function headerFunction()
    {
        return $this->headerFunction;
    }

    public function addGlue($glue)
    {
        $this->glue = $glue;
        return $this;
    }

    public function glue()
    {
        return $this->glue;
    }

    public function noNewLine()
    {
        $this->newline = false;
        return $this;
    }

    public function newline()
    {
        return $this->newline;
    }

}
