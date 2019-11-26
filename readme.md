# File From CSV
### A utility to convert csv files into sql/xml/anything

This script uses a _conversion script_ file to read rows from a csv and convert each row into an output in any required format

Invoke the script as follows
` php CreateFileFromCsv.php scriptname [--input=filename] [--output=filename] [--verbose] [--samples[=x]] `

- verbose outputs every statement to screen as it is being generated (slow)
- samples outputs to screen a sample using only the first [or first x] row(s)

Input and Output filenames can be specified in the script, and are overridden by the cli versions if provided


### Creating a script file
_See example folder for sample scripts. You can create subfolders in the scripts folder to store your scripts._

- Define a class with the same name as the file. The class should extend Script.

- Create a public function getModel() which should return an FfcModel with the required options set

- Create public functions for any calculated values.

  These functions will be available as \~placeholders\~
  alongside those in the ConversionFunctions class.
  The functions will be passed a header=>value array of
  the current csv row and should return a single value to
  use as the value to insert into the statement

  Alternatively, to pass a single value from the csv to a computed function
  use the syntax \~function:header\~
  E.G. \~language:locale\~ will return the uppercase value from the 'locale' column

- The statement set with 'addStatment' will have any \~placeholder\~
  replaced with the computed value or value from the matching
  column in the csv.

- addFilenames can either be ('input.file', 'output.file') or
  [
      ['input1.file' => 'output1.file],
      ['input2.file' => 'output2.file]
  ]