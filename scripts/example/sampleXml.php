<?php
/**
 * Sample file_to_csv script
 *
 * - Define a class with the same name as the file - extend Script.
 *
 * - Create a public function getModel() which should return
 *   an FfcModel with the required options set
 *
 * - Create public functions for any calculated values.
 *   These functions will be available as ~placeholders~
 *   alongside those in the ConversionFunctions class.
 *
 *   The functions will be passed a header=>value array of
 *   the current csv row and should return a single value to
 *   use as the value to insert into the statement
 * 
 *   Alternatively, to pass a single value from the csv to a computed function
 *   use the syntax ~function:header~
 *   E.G. ~language:locale~ will return the uppercase value from the 'locale' column
 *
 * - The statement set with 'addStatment' will have any ~placeholder~
 *   replaced with the computed value or value from the matching
 *   column in the csv.
 * 
 * - addFilenames can either be ('input.file', 'output.file') or
 *   [
 *       ['input1.file' => 'output1.file],
 *       ['input2.file' => 'output2.file]
 *   ]
 *
 *   Script files can be placed in subfolders.
 */

class sampleXml extends Script
{
    /**
     * Placemarker used to denote placeholder fields in the statement.
     * Normally set to '~' but you can override it here with another single character
     */
//    public $placeMarker='%';

    public function getModel()
    {
        return (new FfcModel())

            ->addFilenames('samplexml.csv', 'samplexml.xml') //Input file, Output file.

            ->setPrefix('<things>')

            /**
             * This statement expects 'name', 'locale' and 'created_at' in the csv.
             * 'language' and 'enLocale' use the computed functions in this class
             * 'territory' passes the current row value for the 'locale'
             *  column to the 'upper' function (in Script class)
             * 'created' column does a similar thing
             * 'updated_at' uses the 'datetime' function with no value passed which returns Y-m-d H:i:s now
             */

            ->addStatement(<<<XML
                <thing>
                    <name>~name~</name>
                    <country>~locale~</country>
                    <language>~language~</language>
                    <territory>~upper:locale~</territory>
                    <created>~iso8601:created_at~</created>
                    <updated_at>~datetime~</updated_at>
                    <locale>~enLocale~</locale>
                </thing>
                XML)

            ->setSuffix('</things>')
        ;
    }

    /**
     * Here are the 'computed value' conversion functions
     */

    public function enLocale($row)
    {
        return "en_" . $row['locale'];
    }

    public function language($row)
    {
        return substr($row['locale'], 0, 2);
    }
}
