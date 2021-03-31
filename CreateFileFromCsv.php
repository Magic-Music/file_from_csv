<?php
/**
 * Read from a csv and use the contents to fill placeholders in a string.
 * Each row in the csv will create a copy of the filled string in the output file.
 *
 * Details of the conversion or specified in a 'script' file from the script folder.
 * The script file specifies the input and output files, prefixes and suffixes, and
 * the 'statement' used to create output. The statement contains placeholders for values
 * from the csv.
 *
 * Scripts can also contain functions for calculating values.
 *
 * CLI input and output options can be used to override script filenames.
 *
 * Run a conversion from the cli:-
 * php CreateFileFromCsv.php [scriptname] [--append] [--verbose] [--sample[=x]] [--input=filename] [--output=filename] [--nonewline] [--newline]
 *
 * --append: Don't delete any existing file - append data
 * --verbose: output complete file content to screen (slow)
 * --sample[=x]: Send sample output to screen based on 1 [or x] rows from the csv
 * --input=filename: Override the input filename in the script
 * --output=filename: Override the output filename in the script
 * --newline/--nonewline: Override newline setting in script
 *
 * See samples in scripts/example folder for details of creating a conversion script
 *
 */
new CreateFileFromCsv();

class CreateFileFromCsv
{
    private $script;

    private $fileMode;

    private $sample=false;

    private $verbose=false;

    private $computed;

    private $marker;

    private $newline;

    private $input;

    private $output;

    private $inHandle;

    private $outHandle;

    public function __construct()
    {
        spl_autoload_register([$this, 'autoload']);

        $this->parseArgs();
        
        $this->marker = $this->script->placeMarker;
        $this->getComputedFunctions($this->script);
        $ffc = $this->script->getModel();
        $this->run($ffc);
    }

    public function autoload($class)
    {
        $classfile = $class . ".php";
        $it = new RecursiveDirectoryIterator(__DIR__);
        foreach(new RecursiveIteratorIterator($it) as $file) {
            $fileparts = explode(DIRECTORY_SEPARATOR,$file);
            $name=array_pop($fileparts);
            if(strcasecmp($name, $classfile) ==0) {
                include str_replace($classfile, $name, $file);
                return;
            }
        }

        die("\nERROR - Could not locate file for script with name $class\n\n");
    }

    /**
     * Main function to generate files.
     */
    private function run($ffc)
    {
        $count=0;

        if(!isset($this->newline)) {
            $this->newline = $ffc->newline() ? "\n" : '';
        }

        foreach ($ffc->files() as $files) {
            $this->openFiles($files);
            $heads = fgetcsv($this->inHandle);
            $headerFunction = $ffc->headerFunction();
            if($headerFunction) {
                $this->script->$headerFunction($heads,$ffc);
            }

            $this->output($ffc->prefix());

            while ($row = fgetcsv($this->inHandle)) {
                $rowArray = array_combine($heads, $row);
                foreach ($ffc->statements() as $statement) {
                    //Call replace until all the placeholders have been replaced
                    do {
                        $update = $this->replace($statement, $rowArray);
                    } while ($update !== false);

                    $this->output($statement, $count ? $ffc->glue() : false);
                    $count++;

                    if($this->sample && $this->sample == $count) {
                        //Stop after [sample] rows for a sample output
                        break 2;
                    }
                }
            }

            $this->output($ffc->suffix());

            fclose($this->outHandle);
            fclose($this->inHandle);
        }

        if($this->sample) {
            echo "\n\nSample complete\n\n";
        } else {
            echo "\n\nConversion complete\n\n";
        }
    }

    /**
     * Output string to file and/or screen depending on options
     * @param type $string
     */
    private function output($string, $glue = false)
    {
        $string = $glue . $string . $this->newline;

        if ($this->verbose) {
            echo $string;
        }

        if(!$this->sample) {
            fputs($this->outHandle, $string);
        }        
    }

    /**
     * Look for the next placeholder in the statement, find an associated 
     * computed function or csv header, replace the placeholder.
     *
     * @param  string $statement The current iteration of the statement being populated
     * @param  array $row The current csv row
     * @return int false if no placeholders found
     */
    private function replace(&$statement, $row)
    {
        $placeStart=strpos($statement, $this->marker);

        if ($placeStart !== false) {
            //Find the location of the next opening placeholder marker
            $placeEnd=strpos($statement, $this->marker, $placeStart+1);

            //Get the full placeholder including markers
            $placeHolder = substr($statement, $placeStart, ($placeEnd-$placeStart)+1);

            //Remove the markers to get the csv header/computed function name
            $header = str_replace($this->marker, '', $placeHolder);

            //If it's a function:value placeholder, split it and get the two parts
            if(strpos($header,':') !== false) {
                $headerParts = explode(':', $header);
                $header = $headerParts[0];
                $value = $row[$headerParts[1]];
            } else {
                //Otherwise pass in the full row array
                $value = $row;
            }

            if (in_array($header, $this->computed)) {
                //Computed function
                $cellValue = $this->script->$header($value);
            } else {
                //Csv Value
                $cellValue=trim($row[$header]) ?? null;
            }
            
            //Non-breaking spaces
            $cellValue=str_replace(['�',"\xa0"], '', $cellValue);

            //Update the statement (passed in by reference)
            $statement=str_replace($placeHolder, trim($cellValue), $statement);
        }

        return $placeStart;
    }

    /**
     * Parse the cli arguments and set config
     */
    private function parseArgs(){
        $args=$GLOBALS['argv'];
        $this->fileMode = 'w';
        
        if (count($args) < 2) {
            die("\nNo conversion script selected\n\nUsage: php CreateFileFromCsv.php updateShipping [--verbose] [--sample] [--append]\n\n");
        }

        $scriptName = $args[1];

        if($scriptName == 'list') {
            $this->listScripts();
            die("\n\n");
        }

        if (class_exists($scriptName, true)) {
            $this->script = new $scriptName();
        } else {
            die("\nCould not find script model of class $scriptName\n\n");
        }

        unset($args[0], $args[1]);
        
        foreach($args as $arg) {

            $a = explode('=', $arg);

            switch(strtolower($a[0])) {
                case '--verbose':
                    $this->verbose = true;
                    break;
                
                case '--sample':
                    if($a[1] ?? null && is_numeric($a[1])) {
                        $this->sample = $a[1];
                        echo "Number of samples: {$this->sample}";
                    } else {
                        $this->sample = 1;
                    }

                    $this->verbose = true;
                    break;
                
                case '--append':
                    $this->fileMode = 'a';
                    break;

                case '--input':
                    if($a[1]) {
                        $this->input = $a[1];
                    } else {
                        die("No file specified with --input");
                    }
                    break;

                case '--output':
                    if($a[1]) {
                        $this->output = $a[1];
                    } else {
                        die("No file specified with --output");
                    }
                    break;

                case '--nonewline':
                    $this->newline = '';
                    break;

                case '--newline':
                    $this->newline = "\n";
                    break;
            }
        }
    }

    /**
     * Get input and output filenames from cli or script
     * Check for existence
     * Open files and store handles
     * @param array $files
     */
    private function openFiles($files)
    {
            if(!file_exists('input')) {
                mkdir('input', 0777, true);
            }
            if(!file_exists('output')) {
                mkdir('input', 0777, true);
            }
            
            $inputFilename  = $this->input ?? $files['input'] ?? null;

            if(!$inputFilename) {
                die("\n\nNo input file specified\n\n");
            }

            if(!file_exists("input/" . $inputFilename)) {
                die("\n\nCould not find input file input/$inputFilename\n\n");
            }

            $outputFilename = $this->output ?? $files['output'];

            if(!$outputFilename) {
                die("\n\nNo output file specified\n\n");
            }

            echo "\n\nConverting $inputFilename to $outputFilename \n\n";

            $this->inHandle  = fopen("input/" . $inputFilename, 'r');
            $this->outHandle = fopen("output/" . $outputFilename, $this->fileMode);
    }

    /**
     * Get public functions in a conversion script and add to the array of computed functions
     * @param string $script The script name to parse
     */
    private function getComputedFunctions($script)
    {
        $ref = new ReflectionClass($script);
        $methods = $ref->getMethods(ReflectionMethod::IS_PUBLIC);
        foreach($methods as $method) {
            if($method->name != 'getModel') {
                $this->computed[]=$method->name;
            }
        }
    }

    private function listScripts()
    {
        echo "\n\nScripts available: \n\n";

        $it = new RecursiveDirectoryIterator(__DIR__ . "/scripts");
        foreach(new RecursiveIteratorIterator($it) as $file) {
            $fileparts = explode(DIRECTORY_SEPARATOR,$file);
            $name =  explode('.', array_pop($fileparts));
            if($name[0] == '.' || $name[0] == '..' || ($name[1] ?? null) != 'php' || in_array('example', $fileparts)) {
                continue;
            }
            echo $name[0] . "\n";
        }
    }
}
